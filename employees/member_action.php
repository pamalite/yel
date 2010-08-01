<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id'])) {
    redirect_to('member.php');
}

if (!isset($_POST['action'])) {
    redirect_to('member.php');
}

$xml_dom = new XMLDOM();

if ($_POST['action'] == 'save_profile') {
    $today = now();
    
    $mode = 'update';
    if ($_POST['id'] == '0') {
        $mode = 'create';
    }
    
    $employee = new Employee($_POST['employee']);
    $branch = $employee->getBranch();
    
    $data = array();
    $data['firstname'] = $_POST['firstname'];
    $data['lastname'] = $_POST['lastname'];
    $data['phone_num'] = $_POST['phone_num'];
    $data['address'] = $_POST['address'];
    $data['state'] = $_POST['state'];
    $data['zip'] = $_POST['zip'];
    $data['country'] = $_POST['country'];
    $data['citizenship'] = $_POST['citizenship'];
    $data['hrm_gender'] = $_POST['hrm_gender'];
    $data['hrm_ethnicity'] = $_POST['hrm_ethnicity'];
    $data['hrm_birthdate'] = $_POST['hrm_birthdate'];
    
    $member = NULL;
    if ($mode == 'update') {
        $member = new Member($_POST['id']);
        if (!$member->update($data)) {
            echo 'ko';
            exit();
        }
    } else {
        $member = new Member($_POST['email_addr']);
        
        $new_password = generate_random_string_of(6);
        $hash = md5($new_password);
        $data['password'] = $hash;
        $data['forget_password_question'] = '1';
        $data['forget_password_answer'] = 'system picked';
        $data['added_by'] = $employee->getId();
        $data['joined_on'] = $today;
        $data['active'] = 'Y';
        $data['invites_available'] = '10';
        
        if ($member->create($data) === false) {
            echo 'ko';
            exit();
        }
        
        $lines = file(dirname(__FILE__). '/../private/mail/member_welcome_with_password.txt');
        $message = '';
        foreach($lines as $line) {
            $message .= $line;
        }

        $message = str_replace('%member%', $_POST['firstname']. ', '. $_POST['lastname'], $message);
        $message = str_replace('%email_addr%', $_POST['email_addr'], $message);
        $message = str_replace('%temporary_password%', $new_password, $message);
        $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
        $message = str_replace('%root%', $GLOBALS['root'], $message);
        $subject = "Welcome To Yellow Elevator!";
        $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
        // mail($_POST['email_addr'], $subject, $message, $headers);
        
        $handle = fopen('/tmp/email_to_'. $_POST['email_addr']. '.txt', 'w');
        fwrite($handle, 'Subject: '. $subject. "\n\n");
        fwrite($handle, $message);
        fclose($handle);
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'approve_photo') {
    $member = new Member($_POST['id']);
    $member->setAdmin(true);
    $photo_info = $member->getPhotoFileInfo();
    if ($member->approvePhoto($photo_info['id']) === false) {
        echo 'ko';
    } else {
        echo 'ok';
    }
    
    exit();
}

if ($_POST['action'] == 'reject_photo') {
    $member = new Member($_POST['id']);
    $member->setAdmin(true);
    $photo_info = $member->getPhotoFileInfo();
    if ($member->deletePhoto($photo_info['id']) === false) {
        echo 'ko';
    } else {
        echo 'ok';
    }
    
    exit();
}

if ($_POST['action'] == 'upload_resume') {
    $resume = NULL;
    $member = new Member($_POST['member']);
    $is_update = false;
    $data = array();
    $data['modified_on'] = now();
    $data['name'] = str_replace(array('\'', '"', '\\'), '', basename($_FILES['my_file']['name']));
    $data['private'] = 'N';
    
    if ($_POST['id'] == '0') {
        $resume = new Resume($member->getId());
        if (!$resume->create($data)) {
            redirect_to('member.php?member_email_addr='. $member->getId(). '&page=resumes&error=1');
            exit();
        }
    } else {
        $resume = new Resume($member->getId(), $_POST['id']);
        $is_update = true;
        if (!$resume->update($data)) {
            redirect_to('member.php?member_email_addr='. $member->getId(). '&page=resumes&error=2');
            exit();
        }
    }
    
    $data = array();
    $data['FILE'] = array();
    $data['FILE']['type'] = $_FILES['my_file']['type'];
    $data['FILE']['size'] = $_FILES['my_file']['size'];
    $data['FILE']['name'] = str_replace(array('\'', '"', '\\'), '', basename($_FILES['my_file']['name']));
    $data['FILE']['tmp_name'] = $_FILES['my_file']['tmp_name'];
    
    if ($resume->uploadFile($data, $is_update) === false) {
        $query = "DELETE FROM resume_index WHERE resume = ". $resume->getId(). ";
                  DELETE FROM resumes WHERE id = ". $resume->getId();
        $mysqli = Database::connect();
        $mysqli->transact($query);
        redirect_to('member.php?member_email_addr='. $member->getId(). '&page=resumes&error=3');
        exit();
    }
    
    redirect_to('member.php?member_email_addr='. $member->getId(). '&page=resumes');
    exit();
}

if ($_POST['action'] == 'get_jobs') {
    $employer = new Employer($_POST['id']);
    
    $result = $employer->getJobs($_POST['order']);
    
    if (is_null($result) || empty($result)) {
        echo '0';
        exit();
    }
    
    if ($result === false) {
        echo 'ko';
        exit();
    }
    
    foreach ($result as $i=>$row) {
        $result[$i]['title'] = htmlspecialchars_decode(stripslashes($row['title']));
    }
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('jobs' => array('job' => $result)));
    exit();
}

if ($_POST['action'] == 'save_notes') {
    $member = new Member($_POST['id']);
    
    $data = array();
    $data['is_active_seeking_job'] = $_POST['is_active_seeking_job'];
    $data['seeking'] = addslashes($_POST['seeking']);
    $data['expected_salary'] = $_POST['expected_salary'];
    $data['expected_salary_end'] = $_POST['expected_salary_end'];
    $data['can_travel_relocate'] = $_POST['can_travel_relocate'];
    $data['reason_for_leaving'] = addslashes($_POST['reason_for_leaving']);
    $data['current_position'] = addslashes($_POST['current_position']);
    $data['current_salary'] = $_POST['current_salary'];
    $data['current_salary_end'] = $_POST['current_salary_end'];
    $data['notice_period'] = $_POST['notice_period'];
    
    if ($member->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    if ($member->saveNotes($_POST['notes']) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_job') {
    $job = new Job($_POST['id']);
    $result = $job->get();
    
    $result[0]['title'] = htmlspecialchars_decode(stripslashes($result[0]['title']));
    $result[0]['description'] = htmlspecialchars_decode(stripslashes($result[0]['description']));
    $result[0]['description'] = str_replace('<br/>', "\n", $result[0]['description']);
    
    $criteria = array(
        'columns' => "job_index.state",
        'joins' => "job_index ON job_index.job = jobs.id",
        'match' => "jobs.id = ". $_POST['id'],
        'limit' => "1"
    );
    $tmp = $job->find($criteria);
    $result[0]['state'] = $tmp[0]['state'];
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('job' => $result));
    exit();
}

if ($_POST['action'] == 'get_referees') {
    $member = new Member($_POST['id']);
    $result = $member->getReferees();
    
    foreach($result as $i=>$row) {
        foreach($row as $col=>$value) {
            $result[$i][$col] = stripslashes($value);
        }
    }
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('candidates' => array('candidate' => $result)));
    exit();
}

if ($_POST['action'] == 'get_referrers') {
    $member = new Member($_POST['id']);
    $result = $member->getReferrers();
    
    foreach($result as $i=>$row) {
        foreach($row as $col=>$value) {
            $result[$i][$col] = stripslashes($value);
        }
    }
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('referrers' => array('record' => $result)));
    exit();
    
}

if ($_POST['action'] == 'remove_referee') {
    $member = new Member($_POST['id']);
    if (!$member->removeReferee($_POST['referee'])) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'remove_referrer') {
    $member = new Member($_POST['id']);
    if (!$member->removeReferrer($_POST['referrer'])) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}
?>