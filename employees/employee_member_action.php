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
        
        $lines = file(dirname(__FILE__). '/../private/mail/member_welcome.txt');
        $message = '';
        foreach($lines as $line) {
            $message .= $line;
        }

        $message = str_replace('%member%', $_POST['firstname']. ', '. $_POST['lastname'], $message);
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
?>