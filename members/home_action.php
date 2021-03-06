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

if ($_POST['action'] == 'save_career_summary') {
    $data = array();
    $data['is_active_seeking_job'] = $_POST['is_active'];
    $data['contact_me_for_opportunities'] = $_POST['contact_me'];
    $data['seeking'] = $_POST['seeking'];
    $data['expected_salary_currency'] = $_POST['expected_sal_currency'];
    $data['expected_salary'] = $_POST['expected_sal'];
    $data['expected_total_annual_package'] = $_POST['expected_total_annual_package'];
    $data['can_travel_relocate'] = $_POST['can_travel'];
    $data['reason_for_leaving'] = $_POST['reason_leaving'];
    $data['current_position'] = $_POST['current_pos'];
    $data['current_salary_currency'] = $_POST['current_sal_currency'];
    $data['current_salary'] = $_POST['current_sal'];
    $data['current_total_annual_package'] = $_POST['current_total_annual_package'];
    $data['preferred_job_location_1'] = sql_nullify($_POST['pref_job_loc_1']);
    $data['preferred_job_location_2'] = sql_nullify($_POST['pref_job_loc_2']);
    $data['notice_period'] = is_numeric($_POST['notice_period']) ? $_POST['notice_period'] : "0";
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
    // $data['specialization'] = $_POST['specialization'];
    $data['position_title'] = $_POST['position_title'];
    $data['position_superior_title'] = $_POST['superior_title'];
    $data['organization_size'] = $_POST['organization_size'];
    $data['employer'] = $_POST['employer'];
    $data['employer_specialization'] = $_POST['emp_specialization'];
    $data['work_from'] = $_POST['work_from'];
    $data['work_to'] = $_POST['work_to'];
    $data['summary'] = $_POST['job_summary'];
    
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
    
    foreach ($result as $i=>$row) {
        $result[$i]['summary'] = htmlspecialchars_decode(stripslashes($row['summary']));
    }
    
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

if ($_POST['action'] == 'upload') {
    $resume = NULL;
    $member = new Member($_POST['member']);
    $is_update = false;
    $data = array();
    $data['modified_on'] = now();
    $data['name'] = str_replace(array('\'', '"', '\\'), '', basename($_FILES['my_resume_file']['name']));
    $data['private'] = 'N';
    
    if ($_POST['id'] == '0') {
        $resume = new Resume($member->getId());
        if (!$resume->create($data)) {
            redirect_to('home.php?error=2');
            exit();
        }
    } else {
        $resume = new Resume($member->getId(), $_POST['id']);
        $is_update = true;
        if (!$resume->update($data)) {
            redirect_to('home.php?error=3');
            exit();
        }
    }
    
    $data = array();
    $data['FILE'] = array();
    $data['FILE']['type'] = $_FILES['my_resume_file']['type'];
    $data['FILE']['size'] = $_FILES['my_resume_file']['size'];
    $data['FILE']['name'] = str_replace(array('\'', '"', '\\'), '', basename($_FILES['my_resume_file']['name']));
    $data['FILE']['tmp_name'] = $_FILES['my_resume_file']['tmp_name'];
    
    if ($resume->uploadFile($data, $is_update) === false) {
        $query = "DELETE FROM resume_index WHERE resume = ". $resume->getId(). ";
                  DELETE FROM resumes WHERE id = ". $resume->getId();
        $mysqli = Database::connect();
        $mysqli->transact($query);
        redirect_to('home.php?error=4');
        exit();
    }
    
    redirect_to('home.php');
    exit();
}

if ($_POST['action'] == 'import_linkedin') {
    $member = new Member($_POST['id']);
    
    // get the current seeking field to be concatenated
    $criteria = array(
        'columns' => "seeking",
        'match' => "email_addr = '". $_POST['id']. "'", 
        'limit' => "1"
    );
    $result = $member->find($criteria);
    $seeking = $result[0]['seeking'];
    
    if (is_null($seeking) || empty($seeking)) {
        $seeking = $_POST['seeking'];
    } else {
        $seeking = htmlspecialchars_decode(stripslashes($seeking)). "\n\n". $_POST['seeking'];
    }
    
    // update career profile
    $data = array();
    $data['seeking'] = $seeking;
    $data['updated_on'] = date('Y-m-d');
    $data['imported_on'] = $data['updated_on'];
    
    if ($member->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    // import job profiles
    $positions_xml = '<?xml version="1.0" encoding="UTF-8"?>'. "\n";
    $positions_xml .= $_POST['positions'];
    
    if (!$xml_dom->load_from_xml($positions_xml)) {
        echo 'ko';
        exit();
    }
    
    $mysqli = Database::connect();
    $nodes = $xml_dom->get('position');
    foreach ($nodes as $node) {
        $data = array();
        foreach ($node->childNodes as $child) {
            switch ($child->nodeName) {
                case 'title':
                    $data['position_title'] = sql_nullify($child->nodeValue);
                    break;
                case 'employer':
                    $data['employer'] = sql_nullify($child->nodeValue);
                    break;
                case 'employer_industry':
                    // match to YE industry
                    $query = "SELECT industries.id 
                              FROM industries 
                              LEFT JOIN linkedin_industry_map ON industries.industry = linkedin_industry_map.ye_industry
                              WHERE linkedin_industry_map.linkedin_industry = '". $child->nodeValue. "' 
                              LIMIT 1";
                    $industry_results = $mysqli->query($query);
                    if (is_null($industry_results) || empty($industry_results)) {
                        $data['employer_specialization'] = 'NULL';
                    } else {
                        $data['employer_specialization'] = $industry_results[0]['id'];
                    }
                    break;
                case 'summary':
                    $data['summary'] = sql_nullify($child->nodeValue);
                    break;
                case 'work_from':
                    $data['work_from'] = sql_nullify($child->nodeValue);
                    break;
                case 'work_to':
                    $data['work_to'] = sql_nullify($child->nodeValue);
                    break;
            }
        }
        
        // print_r($data);
        if ($member->addJobProfile($data) === false) {
            echo 'ko';
            exit();
        }
    }
    
    echo 'ok';
    exit();
}
?>