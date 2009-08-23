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

redirect_to('login.php?signed_up=activated');
?>
