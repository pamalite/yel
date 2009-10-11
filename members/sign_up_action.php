<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

$invited = false;
$_SESSION['yel']['sign_up']['firstname'] = $_POST['firstname'];
$_SESSION['yel']['sign_up']['lastname'] = $_POST['lastname'];
$_SESSION['yel']['sign_up']['primary_industry'] = $_POST['primary_industry'];
$_SESSION['yel']['sign_up']['secondary_industry'] = $_POST['secondary_industry'];
$_SESSION['yel']['sign_up']['email_addr'] = $_POST['email_addr'];
$_SESSION['yel']['sign_up']['forget_question'] = $_POST['forget_password_question'];
$_SESSION['yel']['sign_up']['forget_answer'] = $_POST['forget_password_answer'];
$_SESSION['yel']['sign_up']['phone_num'] = $_POST['phone_num'];
$_SESSION['yel']['sign_up']['address'] = $_POST['address'];
$_SESSION['yel']['sign_up']['state'] = $_POST['state'];
$_SESSION['yel']['sign_up']['zip'] = $_POST['zip'];
$_SESSION['yel']['sign_up']['country'] = $_POST['country'];
$_SESSION['yel']['sign_up']['like_newsletter'] = ($_POST['like_newsletter']) ? 'Y' : 'N';

if (!empty($_POST['member']) && !empty($_POST['referee'])) {
    $invited = true;
} 

if (!isset($_POST['email_addr']) || !isset($_POST['phone_num']) || !isset($_POST['zip']) || 
    !isset($_POST['country']) || !isset($_POST['password']) || !isset($_POST['firstname']) || 
    !isset($_POST['lastname']) || !isset($_POST['security_code']) || 
    !isset($_POST['forget_password_question']) || !isset($_POST['forget_password_answer'])) {
    if ($invited) {
        redirect_to('sign_up.php?referee='. $_POST['referee']. '&member='. $_POST['member']);
    } else {
        redirect_to('sign_up.php');
    }
}

// NOTE: Remember to comment this out during coding. 
if ($_POST['security_code'] != $_SESSION['security_code']) {
    if ($invited) {
        redirect_to('sign_up.php?error=2&referee='. $_POST['referee']. '&member='. $_POST['member']. '&job='. $_POST['job']);
    } else {
        redirect_to('sign_up.php?error=2');
    }
}

// 1. Check whether the e-mail has been taken. If taken, then inform user to use another.
$inactive = false;
$query = "SELECT COUNT(*) AS id_used FROM members WHERE email_addr = '". $_POST['email_addr']. "'";
$mysqli = Database::connect();
$result = $mysqli->query($query);
if ($result[0]['id_used'] != '0') {
    // 1.1 Check whether this e-mail was previously unsubscribed or not active.
    $query = "SELECT active FROM members WHERE email_addr = '". $_POST['email_addr']. "'";
    $result = $mysqli->query($query);
    if ($result[0]['active'] != 'N') {
        redirect_to('sign_up.php?error=1');
    } else {
        $inactive = true;
    }
}

// 2. Create the member.
$joined_on = today();
$member = new Member($_POST['email_addr']);

$data = array();
$data['firstname'] = $_POST['firstname'];
$data['lastname'] = $_POST['lastname'];
$data['primary_industry'] = $_POST['primary_industry'];
$data['secondary_industry'] = $_POST['secondary_industry'];
$data['password'] = md5($_POST['password']);
$data['forget_password_question'] = $_POST['forget_password_question'];
$data['forget_password_answer'] = $_POST['forget_password_answer'];
$data['phone_num'] = $_POST['phone_num'];
$data['zip'] = $_POST['zip'];
$data['country'] = $_POST['country'];
$data['address'] = $_POST['address'];
$data['state'] = $_POST['state'];
$data['like_newsletter'] = $_SESSION['yel']['sign_up']['like_newsletter'];
$data['joined_on'] = $joined_on;
$data['active'] = 'N';
$data['invites_available'] = '10';

if ($data['like_newsletter'] == 'Y') {
    $data['filte_jobs'] = 'Y';
}

if (!$inactive) {
    if (!$member->create($data)) {
        $data['email_addr'] = $_POST['email_addr'];
        redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/errors/failed_to_create_member.php');
    }
} else {
    if (!$member->update($data, true)) {
        redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/errors/failed_to_update_member.php');
    }
}

// 3. Check whether the member has been invited. 
// - If yes, 
// - a. for each distinct invite create a member_referee record and approved them by default.
// - b. for each distinct invite create a referral record.

$query = "SELECT DISTINCT member, invited_on FROM member_invites 
          WHERE referee_email = '". $_POST['email_addr']. "' AND 
          (signed_up_on IS NULL OR signed_up_on = '0000-00-00 00:00:00')";
$mysqli = Database::connect();
$result = $mysqli->query($query);
if (count($result) > 0 && !is_null($result)) {
    foreach ($result as $row) {
        $queries = "INSERT INTO member_referees SET 
                    member = '". $row['member']. "', 
                    referee = '". $_POST['email_addr']. "', 
                    referred_on = '". $row['invited_on']. "', 
                    approved = 'Y'; 
                    INSERT INTO member_referees SET 
                    member = '". $_POST['email_addr']. "', 
                    referee = '". $row['member']. "', 
                    referred_on = '". $row['invited_on']. "', 
                    approved = 'Y'";
        $mysqli = Database::connect();
        if (!$mysqli->transact($queries)) {
            redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/errors/failed_to_create_member.php');
            //echo $query;
            //exit();
        }
    }
    
    $query = "SELECT member, referred_job, invited_on, testimony FROM member_invites 
              WHERE referee_email = '". $_POST['email_addr']. "' AND 
              (signed_up_on IS NULL OR signed_up_on = '0000-00-00 00:00:00')";
    $result = $mysqli->query($query);
    if (!$result) {
        redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/errors/failed_to_create_member.php');
        //echo $query;
        //exit();
    }

    foreach ($result as $i=>$row) {
        $data = array();
        $data['member'] = $row['member'];
        $data['referee'] = $_POST['email_addr'];
        $data['job'] = $row['referred_job'];
        $data['referred_on'] = $row['invited_on'];
        $data['testimony'] = $row['testimony'];

        if (!Referral::create($data)) {
            redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/errors/failed_to_create_member.php');
            //echo "cannot refer";
            //exit();
        }

        $query = "UPDATE member_invites SET 
                  signed_up_on = '". now(). "' 
                  WHERE referee_email = '". $_POST['email_addr']. "' AND 
                  member = '". $row['member']. "' AND 
                  referred_job = ". $row['referred_job'];
        if (!$mysqli->execute($query)) {
            redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/errors/failed_to_create_member.php');
            //echo $query;
            //exit();
        }
    }
} 

// 3.5 Check whether the member has been requestedly invited. 
// - If yes, 
// - a. for each distinct invite create a member_referee record and approved them by default, if has not been done.
// - b. for each distinct invite create a referral request record.

$query = "SELECT DISTINCT member, invited_on FROM referrer_invites 
          WHERE referrer_email = '". $_POST['email_addr']. "' AND 
          (signed_up_on IS NULL OR signed_up_on = '0000-00-00 00:00:00')";
$mysqli = Database::connect();
$result = $mysqli->query($query);
if (count($result) > 0 && !is_null($result)) {
    foreach ($result as $row) {
        $mysqli = Database::connect();
        
        $query = "SELECT COUNT(*) AS connections FROM member_referees 
                  WHERE ((member = '". $row['member']. "' AND referee = '". $_POST['email_addr']. "') OR 
                  (member = '".  $_POST['email_addr']. "' AND referee = '". $row['member']. "')) AND
                  approved = 'Y'";
        
        $result = $mysqli->query($query);
        if ($result[0]['connections'] <= 0) {
            $queries = "INSERT INTO member_referees SET 
                        member = '". $row['member']. "', 
                        referee = '". $_POST['email_addr']. "', 
                        referred_on = '". $row['invited_on']. "', 
                        approved = 'Y'; 
                        INSERT INTO member_referees SET 
                        member = '". $_POST['email_addr']. "', 
                        referee = '". $row['member']. "', 
                        referred_on = '". $row['invited_on']. "', 
                        approved = 'Y'";

            if (!$mysqli->transact($queries)) {
                redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/errors/failed_to_create_member.php');
                //echo $query;
                //exit();
            }
        }
    }
    
    $query = "SELECT member, requested_job, invited_on, resume FROM referrer_invites 
              WHERE referrer_email = '". $_POST['email_addr']. "' AND 
              (signed_up_on IS NULL OR signed_up_on = '0000-00-00 00:00:00')";
    $result = $mysqli->query($query);
    if (!$result) {
        redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/errors/failed_to_create_member.php');
        //echo $query;
        //exit();
    }

    foreach ($result as $i=>$row) {
        $data = array();
        $data['member'] = $row['member'];
        $data['referrer'] = $_POST['email_addr'];
        $data['job'] = $row['requested_job'];
        $data['requested_on'] = $row['invited_on'];
        $data['resume'] = $row['resume'];

        if (!ReferralRequests::create($data)) {
            redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/errors/failed_to_create_member.php');
            //echo "cannot request";
            //exit();
        }

        $query = "UPDATE referrer_invites SET 
                  signed_up_on = '". now(). "' 
                  WHERE referrer_email = '". $_POST['email_addr']. "' AND 
                  member = '". $row['member']. "' AND 
                  requested_job = ". $row['requested_job'];
        if (!$mysqli->execute($query)) {
            redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/errors/failed_to_create_member.php');
            //echo $query;
            //exit();
        }
    }
} 


// 4. Create activation token and email
$activation_id = microtime(true);
$query = "INSERT INTO member_activation_tokens SET 
          id = '". $activation_id. "', 
          member = '". $_POST['email_addr']. "', 
          joined_on = '". $joined_on. "'";
$mysqli = Database::connect();
if (!$mysqli->execute($query)) {
    redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/errors/failed_to_create_member.php');
    //echo $query;
    //exit();
}

$mail_lines = file('../private/mail/member_activation.txt');
$message = '';
foreach ($mail_lines as $line) {
    $message .= $line;
}

$message = str_replace('%activation_id%', $activation_id, $message);
$message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
$message = str_replace('%root%', $GLOBALS['root'], $message);
$subject = "Member Activation Required";
$headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
mail($_POST['email_addr'], $subject, $message, $headers);

// $handle = fopen('/tmp/email_to_'. $_POST['email_addr']. '_token.txt', 'w');
// fwrite($handle, 'Subject: '. $subject. "\n\n");
// fwrite($handle, $message);
// fclose($handle);

redirect_to('login.php?signed_up=success');
?>
