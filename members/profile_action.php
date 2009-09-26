<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['action'])) {
    if (!isset($_POST['email_addr']) || !isset($_POST['phone_num']) ||
        !isset($_POST['zip']) || !isset($_POST['country']) || 
        !isset($_POST['forget_password_question']) || !isset($_POST['forget_password_answer'])) {
        echo "ko";
        exit();
        //redirect_to('login.php');
    }

    $member = new Member($_POST['email_addr'], $_SESSION['yel']['member']['sid']);

    $data = array();
    $data['primary_industry'] = $_POST['primary_industry'];
    $data['secondary_industry'] = $_POST['secondary_industry'];
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
    $data['like_newsletter'] = $_POST['like_newsletter'];
    $data['filter_jobs'] = $_POST['filter_jobs'];

    if (!$member->update($data)) {
        echo "ko";
        exit();
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

    echo "ok";
}

if ($_POST['action'] == 'unsubscribe') {
    $member = new Member($_POST['email_addr']);

    $data = array();
    $data['active'] = 'N';
    
    if (!$member->update($data)) {
        echo 'ko';
        exit();
    }
    
    $query = "INSERT INTO member_unsubscribes SET 
              member = '". $_POST['email_addr']. "', 
              unsubscribed_on = '". now(). "', 
              reason = '". $_POST['reason']. "'";
    $mysqli = Database::connect();
    $mysqli->execute($query);
    
    echo 'ok';
    exit();
}
?>
