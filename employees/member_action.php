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
    $match = "referrals.referee = '". $_POST['id']. "'";
    if (!empty($_POST['filter'])) {
        $match .= "AND ";
        switch ($_POST['filter']) {
            case 'employed':
                $match .= "(referrals.employed_on IS NOT NULL AND referrals.employed_on <> '0000-00-00') AND 
                           (referrals.employer_rejected_on IS NULL OR referrals.employer_rejected_on = '0000-00-00') AND 
                           (referrals.employer_removed_on IS NULL OR referrals.employer_removed_on = '0000-00-00')";
                break;
            case 'rejected':
                $match .= "(referrals.employer_rejected_on IS NOT NULL AND referrals.employer_rejected_on <> '0000-00-00')";
                break;
            case 'removed':
                $match .= "(referrals.employer_removed_on IS NOT NULL AND referrals.employer_removed_on <> '0000-00-00')";
                break;
            case 'viewed':
                $match .= "(referrals.employer_agreed_terms_on IS NOT NULL AND referrals.employer_agreed_terms_on <> '0000-00-00') AND 
                           (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00') AND 
                           (referrals.employer_rejected_on IS NULL OR referrals.employer_rejected_on = '0000-00-00') AND 
                           (referrals.employer_removed_on IS NULL OR referrals.employer_removed_on = '0000-00-00')";
                break;
            case 'not_viewed':
                $match .= "(referrals.employer_agreed_terms_on IS NULL OR referrals.employer_agreed_terms_on = '0000-00-00') AND 
                           (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00') AND 
                           (referrals.employer_rejected_on IS NULL OR referrals.employer_rejected_on = '0000-00-00') AND 
                           (referrals.employer_removed_on IS NULL OR referrals.employer_removed_on = '0000-00-00')";
                break;
        }
    }
    
    $criteria = array(
        'columns' => "referrals.id, referrals.member AS referrer, 
                      jobs.title AS job, jobs.id AS job_id, 
                      employers.name AS employer, employers.id AS employer_id, 
                      referrals.resume AS resume_id, resumes.file_name, 
                      CONCAT(members.lastname, ', ', members.firstname) AS referrer_name, 
                      DATE_FORMAT(referrals.referred_on, '%e %b, %Y') AS formatted_referred_on, 
                      DATE_FORMAT(referrals.employer_agreed_terms_on, '%e %b, %Y') AS formatted_employer_agreed_terms_on, 
                      DATE_FORMAT(referrals.employer_rejected_on, '%e %b, %Y') AS formatted_employer_rejected_on, 
                      DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on, 
                      DATE_FORMAT(referrals.employer_removed_on, '%e %b, %Y') AS formatted_employer_removed_on, 
                      IF(referrals.testimony IS NULL OR referrals.testimony = '', '0', '1') AS has_testimony, 
                      IF(referrals.employer_remarks IS NULL OR referrals.employer_remarks = '', '0', '1') AS has_employer_remarks", 
        'joins' => "members ON members.email_addr = referrals.member, 
                    jobs ON jobs.id = referrals.job, 
                    employers ON employers.id = jobs.employer, 
                    resumes ON resumes.id = referrals.resume", 
        'match' => $match,
        'order' => $_POST['order_by']
    );
    
    $referral = new Referral();
    $result = $referral->find($criteria);
    
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
    $yel_email = 'team.'. strtolower($branch['country']). '@yellowelevator.com';
    
    $member = $yel_email;
    if (!empty($_POST['referrer'])) {
        $member = $_POST['referrer'];
    }
    $referee = $_POST['id'];
    $resume = $_POST['resume'];
    $job_ids = explode(',', $_POST['jobs']);
    
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
                            job = ". $job. " LIMIT 1"
            );
            $result = $referral->find($criteria);
            
            if (is_null($result) || count($result) <= 0 || $result === false) {
                $failed_jobs[] = $job;
                continue;
            } 

            $data_1 = $data;
            $data_1['id'] = $result[0]['id'];
            if ($referral->update($data_1) === false) {
                $failed_jobs[] = $job;
            }
        }
    }
    
    if (!empty($failed_jobs) && count($failed_jobs) > 0) {        
        $criteria = array(
            "columns" => "jobs.id, jobs.title, employers.id, employers.name AS employer, 
                          jobs.expire_on", 
            "joins" => "employers ON employers.id = jobs.id", 
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
        "columns" => "jobs.title, employers.id AS employer_id" 
        "joins" => "employers ON employers.id = jobs.id", 
        "match" => "jobs.id IN (". implode(',', $jobs). ")"
    );
    $job = new Job();
    $result = $job->find($criteria);
    
    $employer = new Employer($result[0]['employer_id']);
    
    $mail_lines = file('../private/mail/employee_new_job_application.txt');
    $message = '';
    foreach ($mail_lines as $line) {
        $message .= $line;
    }
    
    $job_list = '';
    $i = 0;
    foreach ($result as $row) {
        $job_list .= '- '. htmlspecialchars_decode(stripslashes($row[$i]['title'])). "\n";
        $i++;
    }
    
    $message = str_replace('%company%', htmlspecialchars_decode(stripslashes($employer->getName())), $message);
    $message = str_replace('%jobs%', $job_list, $message);
    $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
    $message = str_replace('%root%', $GLOBALS['root'], $message);
    
    // 2. get the selected resume file
    $criteria = array(
        "columns" => "file_name, file_hash, file_size, file_type", 
        "match" => "id = ". $resume. " LIMIT 1"
    );
    $member_resume = new Resume();
    $result = $member_resume->find($criteria);
    $original_filename = explode('.', $result[0]['file_name']);
    $extension = $original_filename[1];
    $resume_file_raw = $resume. '.'. $result[0]['file_hash'];
    $attached_filename = $resume. '.'. $extension;
    $filetype = $result[0]['file_type'];
    $attachment = chunk_split(base64_encode(file_get_contents($GLOBALS['resume_dir']. '/'. $attached_filename)));
    
    // 3. make mail with attachment
    $subject = 'New Applicant from YellowElevator';
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
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
    // mail($employer->getEmailAddress(), $subject, $body, $headers);
    mail('ken.sng.wong@yellowelevator.com', $subject, $body, $headers);
    
    $handle = fopen('/tmp/email_to_'. $employer->getEmailAddress(). '.txt', 'w');
    fwrite($handle, 'Subject: '. $subject. "\n\n");
    fwrite($handle, $body);
    fclose($handle);
    
    echo 'ok';
    exit();
}
?>