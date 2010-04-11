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
    redirect_to('home.php');
}

if ($_POST['action'] == 'save_census_answers') {
    $data = array();
    $data['hrm_gender'] = desanitize($_POST['gender']);
    $data['hrm_ethnicity'] = desanitize($_POST['ethnicity']);
    $data['hrm_birthdate'] = desanitize($_POST['birthdate']);
    
    $member = new Member($_POST['id']);
    if ($member->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'save_answers') {
    $data = array();
    $data['is_active_seeking_job'] = ($_POST['is_active_seeking_job'] == '1') ? true : false;
    
    if ($data['is_active_seeking_job']) {
        $data['seeking'] = sanitize($_POST['seeking']);
        $data['expected_salary'] = sanitize($_POST['expected_salary']);
        $data['expected_salary_end'] = sanitize($_POST['expected_salary_end']);
        $data['can_travel_relocate'] = sanitize($_POST['can_travel_relocate']);
        $data['reason_for_leaving'] = sanitize($_POST['reason_for_leaving']);
        $data['current_position'] = sanitize($_POST['current_position']);
        $data['current_salary'] = sanitize($_POST['current_salary']);
        $data['current_salary_end'] = sanitize($_POST['current_salary_end']);
        $data['notice_period'] = sanitize($_POST['notice_period']);
    } else {
        $data['seeking'] = 'NULL';
        $data['expected_salary'] = 0;
        $data['expected_salary_end'] = 0;
        $data['can_travel_relocate'] = 'NULL';
        $data['reason_for_leaving'] = 'NULL';
        $data['current_position'] = 'NULL';
        $data['current_salary'] = 0;
        $data['current_salary_end'] = 0;
        $data['notice_period'] = 'NULL';
    }
    
    $member = new Member($_POST['id']);
    if ($member->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}
?>