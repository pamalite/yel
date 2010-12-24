<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id'])) {
    redirect_to('profile.php');
}

$employer = new Employer($_POST['id'], $_SESSION['yel']['employer']['sid']);

$data = array();
$data['password'] = $_POST['password'];
$data['is_new'] = '0';

if (!$employer->update($data)) {
    echo "ko";
    exit();
}

if (array_key_exists('password', $data)) {
    $lines = file(dirname(__FILE__). '/../private/mail/employer_password_reset.txt');
    $message = '';
    foreach($lines as $line) {
        $message .= $line;
    }

    $subject = "Employer Password Reset";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($_POST['email_addr'], $subject, $message, $headers);
}

echo "ok";
?>
