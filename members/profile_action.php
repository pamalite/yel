<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id'])) {
    echo "ko";
    exit();
    //redirect_to('login.php');
}

$xml_dom = new XMLDOM();

if (!isset($_POST['action'])) {
    redirect_to('profile.php');
}

if ($_POST['action'] == 'save_profile') {
    if (!isset($_POST['id']) || !isset($_POST['phone_num']) ||
        !isset($_POST['zip']) || !isset($_POST['country']) || 
        !isset($_POST['forget_password_question']) || !isset($_POST['forget_password_answer'])) {
        echo 'ko';
        exit();
    }
    
    $member = new Member($_POST['id'], $_SESSION['yel']['member']['sid']);

    $data = array();
    $data['forget_password_question'] = $_POST['forget_password_question'];
    $data['forget_password_answer'] = $_POST['forget_password_answer'];
    $data['phone_num'] = $_POST['phone_num'];
    $data['email_addr'] = $_POST['email_addr'];
    $data['zip'] = $_POST['zip'];
    $data['country'] = $_POST['country'];

    if (isset($_POST['password'])) {
        $data['password'] = $_POST['password'];
    }

    $data['address'] = $_POST['address'];
    $data['state'] = $_POST['state'];

    if (!$member->update($data)) {
        echo 'ko';
        exit();
    }
    
    if (isset($_POST['industries'])) {
        $industries = explode(',', $_POST['industries']);
        if (!$member->saveIndustries($industries)) {
            echo 'ko';
            exit();
        }
    }
    
    if (array_key_exists('password', $data)) {
        $lines = file(dirname(__FILE__). '/../private/mail/member_password_reset.txt');
        $message = '';
        foreach($lines as $line) {
            $message .= $line;
        }

        $message = str_replace('%temp_password_line%', '', $message);
        $subject = "Member Password Reset";
        $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";

        mail($_POST['email_addr'], $subject, $message, $headers);
    }

    echo 'ok';
}

if ($_POST['action'] == 'save_bank') {
    $member = new Member($_POST['id']);
    
    if ($member->saveBankAccount($_POST['bank'], $_POST['account'], $_POST['bank_id']) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'save_highlights') {
    $member = new Member($_POST['id']);
    
    $data = array();
    $data['like_newsletter'] = 'N';
    $data['filter_jobs'] = 'N';
    if ($_POST['like_newsletter'] == 'Y') {
        $data['like_newsletter'] = 'Y';
        
        if (isset($_POST['filter_jobs'])) {
            $data['filter_jobs'] = $_POST['filter_jobs'];
        } else {
            $data['filter_jobs'] = 'N';
        }
    } 
    
    if (!$member->update($data)) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'unsubscribe') {
    $member = new Member($_POST['id']);

    $data = array();
    $data['active'] = 'N';
    
    if (!$member->update($data)) {
        echo 'ko';
        exit();
    }
    
    $query = "INSERT INTO member_unsubscribes SET 
              member = '". $_POST['id']. "', 
              unsubscribed_on = '". now(). "', 
              reason = '". $_POST['reason']. "'";
    $mysqli = Database::connect();
    $mysqli->execute($query);
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'upload') {
    $member = new Member($_POST['id']);
    
    $data = array();
    $data['FILE'] = array();
    $data['FILE']['type'] = $_FILES['my_file']['type'];
    $data['FILE']['size'] = $_FILES['my_file']['size'];
    $data['FILE']['name'] = str_replace(array('\'', '"', '\\'), '', basename($_FILES['my_file']['name']));
    $data['FILE']['tmp_name'] = $_FILES['my_file']['tmp_name'];
    
    if ($member->savePhoto($data) === false) {
        ?><script type="text/javascript">top.stop_upload(<?php echo "0"; ?>);</script><?php
        exit();
    }
    
    ?><script type="text/javascript">top.stop_upload(<?php echo "1"; ?>);</script><?php
    exit();
}
?>