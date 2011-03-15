<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id'])) {
    redirect_to('profile.php');
}

if (!isset($_POST['action'])) {
    redirect_to('profile.php');
}

if ($_POST['action'] == 'save_password') {
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
}

if ($_POST['action'] == 'save_profile') {
    $employer = new Employer($_POST['id'], $_SESSION['yel']['employer']['sid']);

    $data = array();
    $data['email_addr'] = $_POST['email_addr'];
    $data['contact_person'] = $_POST['contact_person'];
    $data['phone_num'] = $_POST['phone_num'];
    $data['fax_num'] = $_POST['fax_num'];
    $data['address'] = $_POST['address'];
    $data['state'] = $_POST['state'];
    $data['zip'] = $_POST['zip'];
    $data['country'] = $_POST['country'];
    $data['website_url'] = $_POST['website_url'];
    $data['about'] = $_POST['summary'];

    if (!$employer->update($data)) {
        echo "ko";
        exit();
    }
    
    echo "ok";
}
?>
