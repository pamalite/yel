<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";
require_once dirname(__FILE__). "/../private/lib/recaptchalib.php";

session_start();

if (!isset($_POST['action'])) {
    redirect_to('sign_up.php');
}

if ($_POST['action'] == 'sign_up') {
    // echo 'ok';
    // exit();
    
    // verify captcha first
    $privatekey = '6LdwqsASAAAAAEJESjRalI-y5sjko4b82nMLC5mH';
    $resp = recaptcha_check_answer ($privatekey,
                                    'yellowelevator.com',
                                    $_POST["recaptcha_challenge"],
                                    $_POST["recaptcha_response"]);
    if (!$resp->is_valid) {
        echo 'ko - captcha';
        exit();
    }
    
    if (!isset($_POST['email_addr']) || !isset($_POST['phone_num']) || 
        !isset($_POST['forget_password_question']) || !isset($_POST['forget_password_answer'])) {
        echo 'ko - empty_fields';
        exit();
    }
    
    // 1. Check whether the e-mail has been taken. If taken, then inform user to use another.
    $member = new Member();
    $criteria = array(
        'columns' => "COUNT(*) AS id_used",
        'match' => "email_addr = '". $_POST['email_addr']. "'"
    );
    $result = $member->find($criteria);
    $inactive = false;
    if ($result[0]['id_used'] != '0') {
        // 1.1 Check whether this e-mail was previously unsubscribed or not active.
        $member = new Member($_POST['email_addr']);
        if ($member->isActive()) {
            echo 'ko - email_taken';
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
    $data['password'] = md5($_POST['password']);
    $data['forget_password_question'] = $_POST['forget_password_question'];
    $data['forget_password_answer'] = $_POST['forget_password_answer'];
    $data['phone_num'] = $_POST['phone_num'];
    $data['joined_on'] = $joined_on;
    $data['updated_on'] = $joined_on;
    $data['active'] = 'N';
    $data['checked_profile'] = 'Y';
    
    if (!$inactive) {
        if ($member->create($data) === false) {
            echo 'ko - error_create';
        }
    } else {
        if ($member->update($data, true) === false) {
            echo 'ko - error_update';
        }
    }
    
    // 3. Create activation token and email
    $activation_id = microtime(true);
    $mysqli = Database::connect();
    $query = "INSERT INTO member_activation_tokens SET 
              id = '". $activation_id. "', 
              member = '". $_POST['email_addr']. "', 
              joined_on = '". $joined_on. "'";
    if ($mysqli->execute($query) === false) {
        echo 'ko - error_activation';
        exit();
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
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'add_job_profile') {
    $member = new Member($_POST['email_addr']);
    
    $data = array();
    // $data['specialization'] = $_POST['specialization'];
    $data['position_title'] = $_POST['position_title'];
    $data['position_superior_title'] = $_POST['position_superior_title'];
    $data['organization_size'] = $_POST['organization_size'];
    $data['work_from'] = $_POST['work_from'];
    $data['work_to'] = $_POST['work_to'];
    $data['employer'] = $_POST['employer'];
    $data['employer_description'] = $_POST['emp_desc'];
    $data['employer_specialization'] = $_POST['emp_specialization'];
    
    if ($member->addJobProfile($data) === false) {
        echo 'ko - error_job_profile';
        exit();
    }
    
    $data = array();
    $data['hrm_gender'] = $_POST['gender'];
    $data['hrm_ethnicity'] = $_POST['ethnicity'];
    $data['hrm_birthdate'] = $_POST['birthdate'];
    $data['total_work_years'] = $_POST['total_work_years'];
    $data['seeking'] = $_POST['seeking'];
    
    // if ($_POST['pref_job_loc_1'] > 0) {
    //     $data['preferred_job_location_1'] = $_POST['pref_job_loc_1'];
    // }
    // 
    // if ($_POST['pref_job_loc_2'] > 0) {
    //     $data['preferred_job_location_2'] = $_POST['pref_job_loc_2'];
    // }
    
    if ($member->update($data) === false) {
        echo 'ko - error_update';
        exit();
    }
    
    echo 'ok';
    exit();
}

?>