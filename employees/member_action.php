<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";
require_once dirname(__FILE__). "/../private/config/job_profile.inc";

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
    $data['updated_on'] = $today;
    
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
        mail($_POST['email_addr'], $subject, $message, $headers);
        
        // $handle = fopen('/tmp/email_to_'. $_POST['email_addr']. '.txt', 'w');
        // fwrite($handle, 'Subject: '. $subject. "\n\n");
        // fwrite($handle, $message);
        // fclose($handle);
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
        $data['is_yel_uploaded'] = '1';
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
    
    if (empty($result) || count($result) <= 0) {
        echo '0';
        exit();
    }
    
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
    
    if (empty($result) || count($result) <= 0) {
        echo '0';
        exit();
    }
    
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

if ($_POST['action'] == 'add_referrer') {
    $member = new Member($_POST['id']);
    $referrers = explode(';', $_POST['referrers']);
    foreach ($referrers as $referrer) {
        $member->addReferrer(trim($referrer));
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'add_referee') {
    $member = new Member($_POST['id']);
    $referees = explode(';', $_POST['referees']);
    foreach ($referees as $referee) {
        $member->addReferee(trim($referee));
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_applications') {
    $order_by = "applied_on DESC";
    if (!empty($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $member = new Member($_POST['id']);
    $result = $member->getAllAppliedJobs($order_by);
    
    if (empty($result) || count($result) <= 0) {
        echo '0';
        exit();
    }
    
    foreach($result as $i=>$row) {
        foreach($row as $col=>$value) {
            $result[$i][$col] = htmlspecialchars_decode(stripslashes($value));
        }
    }
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('applications' => array('application' => $result)));
    exit();
}

if ($_POST['action'] == 'get_testimony') {
    $criteria = array(
        'columns' => "testimony", 
        'match' => "id = ". $_POST['id'], 
        'limit' => "1"
    );
    
    $referral = new Referral();
    $result = $referral->find($criteria);
    $testimony = htmlspecialchars_decode(str_replace("\n", '<br/>', $result[0]['testimony']));
    
    echo $testimony;
    exit();
}

if ($_POST['action'] == 'get_job_desc') {
    $criteria = array(
        'columns' => "description", 
        'match' => "id = ". $_POST['id'], 
        'limit' => "1"
    );
    
    $job = new Job();
    $result = $job->find($criteria);
    $job_desc = htmlspecialchars_decode(str_replace("\n", '<br/>', $result[0]['description']));
    
    echo $job_desc;
    exit();
}

if ($_POST['action'] == 'get_employer_remarks') {
    $criteria = array(
        'columns' => "employer_remarks", 
        'match' => "id = ". $_POST['id'], 
        'limit' => "1"
    );
    
    $referral = new Referral();
    $result = $referral->find($criteria);
    $remarks = str_replace("\n", '<br/>', stripslashes($result[0]['employer_remarks']));
    
    echo $remarks;
    exit();
}

if ($_POST['action'] == 'get_filtered_jobs') {
    $criteria = array(
        'columns' => "jobs.id, jobs.title, industries.industry, 
                     DATE_FORMAT(jobs.expire_on, '%e %b, %Y') AS formatted_expire_on", 
        'joins' => "industries ON industries.id = jobs.industry", 
        //'match' => "jobs.employer = '". $_POST['id']. "' AND jobs.expire_on >= now()"
        'match' => "jobs.employer = '". $_POST['id']. "'"
    );
    
    $job = new Job();
    $result = $job->find($criteria);
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('jobs' => array('job' => $result)));
    exit(); 
}

if ($_POST['action'] == 'apply_job') {
    $employee = new Employee($_POST['employee']);
    $branch = $employee->getBranch();
    $yel_email = 'team.'. strtolower($branch[0]['country']). '@yellowelevator.com';
    
    $member = $yel_email;
    if (!empty($_POST['referrer'])) {
        $member = $_POST['referrer'];
    }
    $referee = $_POST['id'];
    $resume = $_POST['resume'];
    $job_ids = explode(',', $_POST['jobs']);
    $job_ids = array_unique($job_ids);
    
    $referral = new Referral();
    $timestamp = date('Y-m-d h:i:s');
    $failed_jobs = array();
    $data = array();
    $data['member'] = $member;
    $data['referee'] = $referee;
    $data['resume'] = $resume;
    $data['referred_on'] = $timestamp;
    $data['referee_acknowledged_on'] = $timestamp;
    $data['member_confirmed_on'] = $timestamp;
    $data['member_read_resume_on'] = $timestamp;
    $data['job'] = 0;
    foreach($job_ids as $job) {
        $data['job'] = $job;
        if ($referral->create($data) === false) {
            $criteria = array(
                "columns" => "id", 
                "match" => "member = '". $member. "' AND 
                            referee = '". $referee. "' AND 
                            job = ". $job,
                "limit" => "1"
            );
            $result = $referral->find($criteria);
            
            if (is_null($result) || count($result) <= 0 || $result === false) {
                $failed_jobs[] = $job;
                continue;
            } 
            
            $existing_referral = new Referral($result[0]['id']);
            if ($existing_referral->update($data) === false) {
                $failed_jobs[] = $job;
            }
        }
    }
    
    if (!empty($failed_jobs) && count($failed_jobs) > 0) {        
        $criteria = array(
            "columns" => "jobs.id, jobs.title, employers.id, employers.name AS employer, 
                          jobs.expire_on", 
            "joins" => "employers ON employers.id = jobs.employer", 
            "match" => "jobs.id IN (". implode(',', $failed_jobs). ")"
        );
        
        $job = new Job();
        $result = $job->find($criteria);
        
        header('Content-type: text/xml');
        echo $xml_dom->get_xml_from_array(array('failed_jobs' => array('job' => $result)));
        exit();
    }
    
    // send email to employer and CC to team.XX@yellowelevator.com
    // 1. setup the mail message contents
    $jobs = array();
    if (empty($failed_jobs)) {
        $jobs = $job_ids;
    } else {
        foreach ($job_ids as $job) {
            if (!in_array($job, $job_ids)) {
                $job[] = $job;
            }
        }
    }
    
    $criteria = array(
        "columns" => "jobs.title, employers.id AS employer_id", 
        "joins" => "employers ON employers.id = jobs.employer", 
        "match" => "jobs.id IN (". implode(',', $jobs). ")"
    );
    $job = new Job();
    $result = $job->find($criteria);
    
    $has_many_jobs = false;
    if (count($jobs) > 1) {
        $has_many_jobs = true;
    }
    
    $employer = new Employer($result[0]['employer_id']);
    
    $mail_lines = file('../private/mail/employer_new_job_application.txt');
    $message = '';
    foreach ($mail_lines as $line) {
        $message .= $line;
    }
    
    $job_list = '';
    $i = 0;
    foreach ($result as $row) {
        $job_list .= '- '. htmlspecialchars_decode(stripslashes($row['title'])). "\n";
        $i++;
    }
    
    $message = str_replace('%company%', htmlspecialchars_decode(stripslashes($employer->getName())), $message);
    $message = str_replace('%jobs%', $job_list, $message);
    $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
    $message = str_replace('%root%', $GLOBALS['root'], $message);
    
    // 2. get the selected resume file
    $criteria = array(
        "columns" => "file_name, file_hash, file_size, file_type", 
        "match" => "id = ". $resume, 
        "limit" => "1"
    );
    $member_resume = new Resume();
    $result = $member_resume->find($criteria);
    $original_filename = explode('.', $result[0]['file_name']);
    $extension = $original_filename[1];
    $resume_file_raw = $resume. '.'. $result[0]['file_hash'];
    $attached_filename = $resume. '.'. $extension;
    $filetype = $result[0]['file_type'];
    $attachment = chunk_split(base64_encode(file_get_contents($GLOBALS['resume_dir']. '/'. $resume_file_raw)));
    
    // 3. make mail with attachment
    $subject = '';
    if ($has_many_jobs) {
        $subject = 'Multiple Jobs Applications - Resume #'. $_POST['resume'];
    } else {
        $subject = trim(substr($job_list, 2)). ' - Resume #'. $_POST['resume'];
    }
    //$headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    $headers = 'From: '. $employee->getEmailAddress(). "\n";
    $headers .= 'Cc: '. $yel_email. "\n";
    $headers .= 'MIME-Version: 1.0'. "\n";
    $headers .= 'Content-Type: multipart/mixed; boundary="yel_mail_sep_'. $attached_filename. '";'. "\n\n";
    
    $body = '--yel_mail_sep_'. $attached_filename. "\n";
    $body .= 'Content-Type: multipart/alternative; boundary="yel_mail_sep_alt_'. $attached_filename. '"'. "\n";
    $body .= '--yel_mail_sep_alt_'. $attached_filename. "\n";
    $body .= 'Content-Type: text/plain; charset="iso-8859-1"'. "\n";
    $body .= 'Content-Transfer-Encoding: 7bit"'. "\n";
    
    $body .= $message. "\n";
    $body .= '--yel_mail_sep_alt_'. $attached_filename. "--\n\n";
    $body .= '--yel_mail_sep_'. $attached_filename. "\n";
    $body .= 'Content-Type: '. $filetype. '; name="'. $attached_filename. '"'. "\n";
    $body .= 'Content-Transfer-Encoding: base64'. "\n";
    $body .= 'Content-Disposition: attachment'. "\n";
    $body .= $attachment. "\n";
    $body .= '--yel_mail_sep_'. $attached_filename. "--\n\n";
    $send = mail($employer->getEmailAddress(), $subject, $body, $headers);
    
    // $handle = fopen('/tmp/email_to_'. $employer->getEmailAddress(). '.txt', 'w');
    // fwrite($handle, 'To: '. $employer->getEmailAddress(). "\n\n");
    // fwrite($handle, 'Header: '. $headers. "\n\n");
    // fwrite($handle, 'Subject: '. $subject. "\n\n");
    // fwrite($handle, $body);
    
    if ($send === false) {
        fwrite($handle, 'not send due to errors');
    }
    
    fclose($handle);
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'save_career') {
    $data = array();
    $data['is_active_seeking_job'] = $_POST['is_seeking'] == '1';
    $data['can_travel_relocate'] = $_POST['can_travel'];
    $data['seeking'] = $_POST['seeking'];
    $data['reason_for_leaving'] = $_POST['reason_leaving'];
    $data['current_position'] = $_POST['current_position'];
    $data['notice_period'] = $_POST['notice_period'];
    $data['total_work_years'] = $_POST['total_years'];
    $data['expected_salary_currency'] = $_POST['expected_currency'];
    $data['expected_salary'] = $_POST['expected_salary'];
    $data['expected_salary_end'] = $_POST['expected_salary_end'];
    $data['current_salary_currency'] = $_POST['current_currency'];
    $data['current_salary'] = $_POST['current_salary'];
    $data['current_salary_end'] = $_POST['current_salary_end'];
    $data['preferred_job_location_1'] = (empty($_POST['pref_job_loc_1'])) ? 'NULL' : $_POST['pref_job_loc_1'];
    $data['preferred_job_location_2'] = (empty($_POST['pref_job_loc_2'])) ? 'NULL' : $_POST['pref_job_loc_2'];
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
    $member->update($data);
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_job_profiles') {
    $criteria = array(
        'columns' => "member_job_profiles.id, member_job_profiles.position_title, 
                      member_job_profiles.position_superior_title, 
                      member_job_profiles.employer, member_job_profiles.employer_description, 
                      industries.industry AS specialization, 
                      employer_industries.industry AS employer_specialization, 
                      DATE_FORMAT(member_job_profiles.work_from, '%b, %Y') AS formatted_work_from, 
                      DATE_FORMAT(member_job_profiles.work_to, '%b, %Y') AS formatted_work_to", 
        'joins' => "member_job_profiles ON member_job_profiles.member = members.email_addr, 
                    industries ON industries.id = member_job_profiles.specialization, 
                    industries AS employer_industries ON employer_industries.id = member_job_profiles.employer_specialization",
        'match' => "members.email_addr = '". $_POST['id']. "'",
        'having' => "member_job_profiles.id IS NOT NULL",
        'order' => "work_from DESC"
    );
    
    $member = new Member();
    $result = $member->find($criteria);
    
    if (is_null($result) || empty($result) || count($result) <= 0) {
        echo '0';
        exit();
    }
    
    if ($result === false) {
        echo 'ko';
        exit();
    }
    
    $emp_descs = $GLOBALS['emp_descs'];
    foreach ($result as $i => $row) {
        $result[$i]['employer'] = htmlspecialchars_decode(stripslashes($row['employer']));
        $result[$i]['employer_description'] = $emp_descs[$row['employer_description']];
    }
    
    $response = array('job_profiles' => array('job_profile' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
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