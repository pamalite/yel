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


?>