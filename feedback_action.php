<?php
require_once dirname(__FILE__). "/private/lib/utilities.php";

session_start();

$_SESSION['yel']['feedback']['firstname'] = $_POST['firstname'];
$_SESSION['yel']['feedback']['lastname'] = $_POST['lastname'];
$_SESSION['yel']['feedback']['email_addr'] = $_POST['email_addr'];
$_SESSION['yel']['feedback']['country'] = $_POST['country'];
$_SESSION['yel']['feedback']['feedback'] = $_POST['feedback'];

if (!isset($_POST['email_addr']) || !isset($_POST['country']) || !isset($_POST['firstname']) || 
    !isset($_POST['lastname']) || !isset($_POST['security_code']) || !isset($_POST['feedback'])) {
    redirect_to('feedback.php');
}

// NOTE: Remember to comment this out during coding. 
if ($_POST['security_code'] != $_SESSION['security_code']) {
    redirect_to('feedback.php?error=1');
}

$fullname = desanitize($_SESSION['yel']['feedback']['firstname']. ', '. $_SESSION['yel']['feedback']['lastname']);
$country = Country::getCountryFrom($_SESSION['yel']['feedback']['country']);

// Send email to feedback@yellowelevator.com
$mail_lines = file('private/mail/feedback.txt');
$message = '';
foreach ($mail_lines as $line) {
    $message .= $line;
}

$message = str_replace('%name%', $fullname, $message);
$message = str_replace('%country%', $country, $message);
$message = str_replace('%feedback%', $_SESSION['yel']['feedback']['feedback'], $message);
$subject = "Feedback from ". $fullname;
$headers = 'From: '. $_SESSION['yel']['feedback']['email_addr']. "\n";
mail('feedback@yellowelevator.com', $subject, $message, $headers);

redirect_to('feedback.php?success=1');
?>
