<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id']) || !isset($_POST['name']) || 
    !isset($_POST['phone_num']) || !isset($_POST['email_addr']) || 
    !isset($_POST['contact_person']) || !isset($_POST['zip']) ||
    !isset($_POST['country'])) {
    echo "ko";
    exit();
    //redirect_to('login.php');
}

$employer = new Employer($_POST['id'], $_SESSION['yel']['employer']['sid']);

$data = array();
$data['name'] = $_POST['name'];
$data['phone_num'] = $_POST['phone_num'];
$data['fax_num'] = $_POST['fax_num'];
$data['email_addr'] = $_POST['email_addr'];
$data['contact_person'] = $_POST['contact_person'];
$data['zip'] = $_POST['zip'];
$data['country'] = $_POST['country'];

if (isset($_POST['password'])) {
    $data['password'] = $_POST['password'];
}

$data['address'] = $_POST['address'];
$data['state'] = $_POST['state'];
$data['website_url'] = $_POST['website_url'];
if (substr($_POST['website_url'], 0, 4) != 'http') {
    $data['website_url'] = 'http://'. $_POST['website_url'];
}

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
