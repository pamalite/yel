<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/members/activtate.php?id='. $_GET['id']);
        exit();
    }
}

if (!isset($_GET['id'])) {
    echo 'Activation failed: No token.';
    exit();
} 

if (empty($_GET['id'])) {
    echo 'Activation failed: Token is empty.';
    exit();
}

$activation_id = $_GET['id'];
$query = "SELECT COUNT(*) AS awaits_activation 
          FROM member_activation_tokens 
          WHERE id = '". $activation_id. "' LIMIT 1";
$mysqli = Database::connect();
$result = $mysqli->query($query);
if ($result[0]['awaits_activation'] == '0') {
    echo 'Activation failed: Cannot find token.';
    exit();
}

$query = "SELECT member 
          FROM member_activation_tokens 
          WHERE id = '". $activation_id. "' LIMIT 1";
$result = $mysqli->query($query);
$email_addr = $result[0]['member'];

// Check whether member is non-privileged
$query = "SELECT recommender FROM members WHERE email_addr = '". $email_addr. "' LIMIT 1";
$result = $mysqli->query($query);
if (is_null($result) || empty($result)) {
    redirect_to('https://'. $GLOBALS['root']. '/members/activtate.php?id='. $_GET['id']);
    exit();
}

$member = new Member($email_addr);

$data = array();
$data['active'] = 'Y';

if (!$member->update($data)) {
    echo 'Activation failed: Cannot activate member.';
    exit();
}

$query = "DELETE FROM member_activation_tokens 
          WHERE id = '". $activation_id. "'";
$mysqli->execute($query);

$mail_lines = file('../private/mail/member_welcome.txt');
$message = '';
foreach ($mail_lines as $line) {
    $message .= $line;
}

$message = str_replace('%member_name%', $member->get_name(), $message);
$message = str_replace('%email_addr%', $member->id(), $message);
$message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
$message = str_replace('%root%', $GLOBALS['root'], $message);
$subject = "Welcome to YellowElevator.com";
$headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
mail($_POST['email_addr'], $subject, $message, $headers);

// continue all bufferred referrals
$query = "SELECT * FROM privileged_referral_buffers WHERE referee = '". $member->id(). "'";
$result = $mysqli->query($query);
if (!is_null($result) && !empty($result)) {
    $query = '';
    foreach ($result as $row) {
        $query .= "INSERT INTO referrals SET 
                   `member` = '". $row['member']. "',
                   `referee` = '". $row['referee']. "',
                   `job` = ". $row['job']. ",
                   `resume` = ". $row['resume']. ",
                   `referred_on` = '". $row['referred_on']. "',
                   `referee_acknowledged_on` = '". $row['referee_acknowledged_on']. "',
                   `member_confirmed_on` = '". $row['member_confirmed_on']. "',
                   `member_read_resume_on` = '". $row['member_read_resume_on']. "',
                   `testimony` = '". $row['testimony']. "';";
    }
    
    if ($mysqli->transact($query)) {
        $query = "DELETE FROM privileged_referral_buffers WHERE referee = '". $member->id(). "'";
        $mysqli->execute($query);
    }
}

redirect_to('login.php?signed_up=activated');
?>
