<?php
require_once dirname(__FILE__). "/private/lib/utilities.php";
require_once dirname(__FILE__). "/private/lib/recaptchalib.php";

session_start();

// 1. store into session
$_SESSION['yel']['contact_us']['contact_name'] = $_POST['contact_name'];
$_SESSION['yel']['contact_us']['company_name'] = $_POST['company_name'];
$_SESSION['yel']['contact_us']['email_addr'] = $_POST['email_addr'];
$_SESSION['yel']['contact_us']['phone_num'] = $_POST['phone_num'];
$_SESSION['yel']['contact_us']['subject'] = $_POST['subject'];
$_SESSION['yel']['contact_us']['message'] = $_POST['message'];
$_SESSION['yel']['contact_us']['kind'] = $_POST['category'];

// 2. check captcha
$privatekey = '6LdwqsASAAAAAEJESjRalI-y5sjko4b82nMLC5mH';
$resp = recaptcha_check_answer ($privatekey,
                                'yellowelevator.com',
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);
if (!$resp->is_valid) {
    redirect_to('contact.php?err=1');
    exit();
}

// 3. prepare email
$subject = $_POST['subject'];
$reply_to = $_POST['contact_name']. ' <'. $_POST['email_addr']. '>';
$send_to = 'sales.my@yellowelevator.com';
if ($_POST['category'] == 'tech') {
    $send_to = 'support@yellowelevator.com';
} else if ($_POST['category'] == 'billing') {
    $send_to = 'billing.my@yellowelevator.com';
}

$message = 'Contact Name: '. $reply_to. "\n\n";
$message .= 'Company Name: '. $_POST['company_name']. "\n\n";
$message .= 'Telephone: '. $_POST['phone_num']. "\n\n";
$message .= 'Subject: '. $_POST['subject']. "\n\n";
$message .= 'Message:'. "\n\n". $_POST['message']. "\n\n";

// 4. send email
$headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
$headers .= 'Reply-To: ' . $reply_to. "\n";
mail($send_to, $subject, $message, $headers);

// $handle = fopen('/tmp/email_to_'. $send_to. '_contactus.txt', 'w');
// fwrite($handle, 'Subject: '. $subject. "\n\n");
// fwrite($handle, $message);
// fclose($handle);

// 5. send confirmation to sender
$confirm_msg = 'Hi '. $_POST['contact_name']. ", \n\n";
$confirm_msg .= 'Your message has been received and we will get back to you shortly.'. "\n\n";
$confirm_msg .= "--- begin message ---\n\n";
$confirm_msg .= $message. "\n\n";
$confirm_msg .= "--- end message ---\n\n";

$subject = 'YellowElevator.com: Message Received';
$headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
mail($_POST['email_addr'], $subject, $confirm_msg, $headers);

// $handle = fopen('/tmp/email_to_'. $_POST['email_addr']. '_confirm.txt', 'w');
// fwrite($handle, 'Subject: '. $subject. "\n\n");
// fwrite($handle, $confirm_msg);
// fclose($handle);

redirect_to('welcome.php');
exit();
?>