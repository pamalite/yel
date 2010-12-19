<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";
require_once dirname(__FILE__). "/../private/config/job_profile.inc";

session_start();

$xml_dom = new XMLDOM();

if (!isset($_POST['id'])) {
    redirect_to('home.php');
}

if (!isset($_POST['action'])) {
    redirect_to('home.php');
}

if ($_POST['action'] == 'save_census_answers') {
    $data = array();
    $data['hrm_gender'] = desanitize($_POST['gender']);
    $data['hrm_ethnicity'] = desanitize($_POST['ethnicity']);
    $data['hrm_birthdate'] = desanitize($_POST['birthdate']);
    $data['updated_on'] = date('Y-m-d');
    
    $member = new Member($_POST['id']);
    if ($member->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'save_is_active_job_seeker') {
    $data = array();
    $data['is_active_seeking_job'] = (strtoupper($_POST['choice']) == 'YES') ? true : false;
    $data['updated_on'] = date('Y-m-d');
    
    $member = new Member($_POST['id']);
    if ($member->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'save_travel_relocate') {
    $data = array();
    $data['can_travel_relocate'] = (strtoupper($_POST['choice']) == 'YES') ? "Y" : "N";
    $data['updated_on'] = date('Y-m-d');
    
    $member = new Member($_POST['id']);
    if ($member->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}


if ($_POST['action'] == 'save_seeking') {
    $data = array();
    $data['seeking'] = $_POST['text'];
    $data['updated_on'] = date('Y-m-d');
    
    $member = new Member($_POST['id']);
    if ($member->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'save_reason_for_leaving') {
    $data = array();
    $data['reason_for_leaving'] = $_POST['text'];
    $data['updated_on'] = date('Y-m-d');
    
    $member = new Member($_POST['id']);
    if ($member->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'save_current_job_desc') {
    $data = array();
    $data['current_position'] = $_POST['text'];
    $data['updated_on'] = date('Y-m-d');
    
    $member = new Member($_POST['id']);
    if ($member->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'save_notice_period') {
    $data = array();
    $data['notice_period'] = is_numeric($_POST['text']) ? $_POST['text'] : "0";
    $data['updated_on'] = date('Y-m-d');
    
    $member = new Member($_POST['id']);
    if ($member->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'save_current_salary') {
    $data = array();
    $data['current_salary_currency'] = $_POST['currency'];
    $data['current_salary'] = $_POST['start'];
    $data['current_salary_end'] = $_POST['end'];
    $data['updated_on'] = date('Y-m-d');
    
    $member = new Member($_POST['id']);
    if ($member->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'save_expected_salary') {
    $data = array();
    $data['expected_salary_currency'] = $_POST['currency'];
    $data['expected_salary'] = $_POST['start'];
    $data['expected_salary_end'] = $_POST['end'];
    
    $member = new Member($_POST['id']);
    if ($member->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'save_job_loc_pref') {
    $data = array();
    $data['preferred_job_location_'. $_POST['pref']] = $_POST['country'];
    $data['updated_on'] = date('Y-m-d');
    
    $member = new Member($_POST['id']);
    if ($member->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'save_job_profile') {
    $data = array();
    $data['specialization'] = $_POST['specialization'];
    $data['position_title'] = $_POST['position_title'];
    $data['position_superior_title'] = $_POST['superior_title'];
    $data['organization_size'] = $_POST['organization_size'];
    $data['employer'] = $_POST['employer'];
    $data['employer_description'] = $_POST['emp_desc'];
    $data['employer_specialization'] = $_POST['emp_specialization'];
    $data['work_from'] = $_POST['work_from'];
    $data['work_to'] = $_POST['work_to'];
    
    $member = new Member($_POST['member']);
    if ($_POST['id'] == 0) {
        // new ---> add
        if ($member->addJobProfile($data) === false) {
            echo 'ko';
            exit();
        }
    } else {
        // existing ---> update
        if ($member->saveJobProfile($_POST['id'], $data) === false) {
            echo 'ko';
            exit();
        }
    }
    
    $data = array();
    $data['updated_on'] = date('Y-m-d');
    if ($member->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_job_profile') {
    $criteria = array(
        'columns' => "*", 
        'joins' => "member_job_profiles ON member_job_profiles.member = members.email_addr", 
        'match' => "member_job_profiles.id = ". $_POST['id']
    );
    
    $member = new Member();
    $result = $member->find($criteria);
    
    $response = array('job_profile' => $result);
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'remove_job_profile') {
    $member = new Member();
    
    if ($member->removeJobProfile($_POST['id']) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}
?>