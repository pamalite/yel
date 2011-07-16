<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";
require_once dirname(__FILE__). "/../private/lib/classes/member_search.php";
require_once dirname(__FILE__). "/../employers/credit_note.php";
require_once dirname(__FILE__). "/../employers/general_invoice.php";

session_start();

date_default_timezone_set('Asia/Kuala_Lumpur');

function create_member_from($_email_addr, $_fullname, $_phone, $_employee_id) {
    if (empty($_email_addr) || empty($_fullname) || empty($_phone)) {
        return false;
    }
    
    $employee = new Employee($_employee_id);
    
    $password = generate_random_string_of(6);
    $timestamp = now();
    
    $lastname = '(n/a)';
    $firstname = $_fullname;
    $names = explode(',', $_fullname);
    if (count($names) > 1) {
        $lastname = trim($names[0]);
        $firstname = trim($names[1]);
    }
    
    $data = array();
    $data['phone_num'] = $_phone;
    $data['firstname'] = $firstname;
    $data['lastname'] = $lastname;
    $data['password'] = md5($password);
    $data['forget_password_question'] = '1';
    $data['forget_password_answer'] = '(System Generated)';
    $data['joined_on'] = $timestamp;
    $data['active'] = 'Y';
    $data['like_newsletter'] = 'N';
    
    $member = new Member($_email_addr);
    $member->setAdmin(true);
    if ($member->create($data) === false) {
        return false;
    }
    
    // send email out
    $mail_lines = file('../private/mail/member_sign_up_with_password.txt');
    $message = '';
    foreach ($mail_lines as $line) {
        $message .= $line;
    }
    
    $message = str_replace('%password%', $password, $message);
    $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
    $message = str_replace('%root%', $GLOBALS['root'], $message);
    
    //$subject = '['. $_email_addr. '] New Membership from Yellow Elevator';
    $subject = 'New Membership from Yellow Elevator';
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    if (!is_null($employee)) {
        $emp_email = $employee->getEmailAddress();
        if ($emp_email !== false && !empty($emp_email)) {
            $headers .= 'Reply-To: '. $emp_email. "\n";
        }
    }
    $headers .= 'Cc: team.my@yellowelevator.com'. "\n";
    
    mail($_email_addr, $subject, $message, $headers);
    //mail('team.my@yellowelevator.com', $subject, $message, $headers);
    
    // $file_name = '/tmp/email_to_'. $_email_addr. '.txt';
    // if (file_exists($file_name)) {
    //     $file_name .= '.'. generate_random_string_of(6). '.txt';
    // }
    // $handle = fopen($file_name, 'w');
    // fwrite($handle, 'Header: '. $headers. "\n\n");
    // fwrite($handle, 'Subject: '. $subject. "\n\n");
    // fwrite($handle, $message);
    // fclose($handle);
    
    return true;
}

if (!isset($_POST['id'])) {
    redirect_to('members.php');
}

if (!isset($_POST['action'])) {
    redirect_to('members.php');
}

$xml_dom = new XMLDOM();

if ($_POST['action'] == 'get_jobs') {
    $employer_ids = explode(',', $_POST['employer_ids']);
    
    if (count($employer_ids) > 0) {
        foreach ($employer_ids as $i=>$id) {
            $employer_ids[$i] = trim($id);
        }
    } else {
        echo '0';
        exit();
    }
    
    $employers = '';
    $j = 0;
    foreach ($employer_ids as $i=>$id) {
        $employers .= "'". $id. "'";
        if ($j < count($employer_ids)-1) {
            $employers .= ', ';
        }
        $j++;
    }
    
    if (empty($employers)) {
        echo '0';
        exit();
    }
    
    $criteria = array(
        'columns' => "jobs.title AS job_title, jobs.id, jobs.employer, jobs.deleted, 
                      COUNT(referral_buffers.id) AS buf_count, 
                      COUNT(member_jobs.id) AS app_count",
        'joins' => "referral_buffers ON referral_buffers.job = jobs.id, 
                    member_jobs ON member_jobs.job = jobs.id", 
        'match' => "jobs.employer IN (". $employers. ")",
        'group' => "jobs.id", 
        'order' => "jobs.title"
    );
    
    $job = new Job();
    $result = $job->find($criteria);
    
    if ($result === false) {
        echo 'ko';
        exit();
    }
    
    if (is_null($result) || empty($result)) {
        echo '0';
        exit();
    }
    
    $jobs = array();
    foreach($result as $i=>$row) {
        if ($row['deleted'] == '1' && ($row['buf_count'] + $row['app_count']) <= 0) {
            continue;
        }
        
        $row['job_title'] = htmlspecialchars_decode(stripslashes($row['job_title']));
        $jobs[] = $row;
    }
    
    $response = array('jobs' => array('job' => $jobs));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_new_applicants') {
    $order_by = "referral_buffers.requested_on desc";
    $show_only = "referral_buffers.referrer_email LIKE '%'";
    $filter_by = "";
    $page = 1;
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    if (isset($_POST['show_only'])) {
        if ($_POST['show_only'] == 'self_applied') {
            $show_only = "referral_buffers.referrer_email LIKE 'team%@yellowelevator.com'";
        } else if ($_POST['show_only'] == 'referred') {
            $show_only = "referral_buffers.referrer_email NOT LIKE 'team%@yellowelevator.com'";
        } 
    }
    
    $match = $show_only;
    
    if (!isset($_POST['non_attached'])) {
        $employers_str = "";
        $jobs_str = "";
        if (isset($_POST['jobs'])) {
            $match .= " AND referral_buffers.job IN (". trim($_POST['jobs']). ")";
        } elseif (isset($_POST['employers'])) {
            $employers = explode(',', $_POST['employers']);
            foreach ($employers as $i=>$id) {
                $employers[$i] = "'". trim($id). "'";
            }
            $employers_str = implode(',', $employers);
            $match .= " AND jobs.employer IN (". $employers_str. ")";
        }
    }
    
    if (isset($_POST['page'])) {
        $page = $_POST['page'];
    }
     
    $criteria = array(
        'columns' => "referral_buffers.id, referral_buffers.candidate_email, 
                      referral_buffers.candidate_phone, referral_buffers.candidate_name, 
                      referral_buffers.referrer_email, referral_buffers.referrer_name, 
                      referral_buffers.referrer_phone, 
                      referral_buffers.existing_resume_id, referral_buffers.resume_file_hash, 
                      referral_buffers.progress_notes, referral_buffers.referrer_remarks, 
                      IF(members.email_addr IS NULL, 0, 1) AS is_member,
                      jobs.title AS job, jobs.employer,  
                      DATE_FORMAT(referral_buffers.requested_on, '%e %b, %Y') AS formatted_requested_on, 
                      DATE_FORMAT(referral_buffers.remind_on, '%e %b, %Y') AS formatted_remind_on, 
                      DATEDIFF(referral_buffers.remind_on, NOW()) AS days_left, 
                      (SELECT COUNT(buf.id) 
                       FROM referral_buffers AS buf 
                       WHERE buf.candidate_email = referral_buffers.candidate_email) AS num_jobs_attached, 
                      referral_buffers.current_employer, referral_buffers.current_position", 
        'joins' => "members ON members.email_addr = referral_buffers.candidate_email, 
                    jobs ON jobs.id = referral_buffers.job, 
                    employers ON employers.id = jobs.employer",
        'match' => $match, 
        'order' => $order_by
    );
    
    $referral_buffer = new ReferralBuffer();
    $result = $referral_buffer->find($criteria);
    
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }

    if ($result === false) {
        echo 'ko';
        exit();
    }
    
    $total_pages = ceil(count($result) / $GLOBALS['default_results_per_page']);
    if ($page > $total_pages) {
        $page = $total_pages;
    }
    
    $offset = 0;
    if ($page > 1) {
        $offset = ($page-1) * $GLOBALS['default_results_per_page'];
        $offset = ($offset < 0) ? 0 : $offset;
    }
    
    $criteria['limit'] = $offset. ", ". $GLOBALS['default_results_per_page'];
    $result = $referral_buffer->find($criteria);
    foreach($result as $i=>$row) {
        $result[$i]['referrer_name'] = htmlspecialchars_decode(stripslashes($row['referrer_name']));
        $result[$i]['candidate_name'] = htmlspecialchars_decode(stripslashes($row['candidate_name']));
        $result[$i]['progress_notes'] = htmlspecialchars_decode(stripslashes($row['progress_notes']));
        $result[$i]['referrer_remarks'] = trim(htmlspecialchars_decode(stripslashes($row['referrer_remarks'])));
        $result[$i]['current_employer'] = htmlspecialchars_decode(stripslashes($row['current_employer']));
        $result[$i]['current_position'] = htmlspecialchars_decode(stripslashes($row['current_position']));
    }
    
    $response = array(
        'new_applicants' => array(
            'total_pages' => $total_pages,
            'new_applicant' => $result
        )
    );
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_applicants') {
    $order_by = "member_jobs.applied_on DESC";
    $filter_by = "";
    $page = 1;
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $match = "member_jobs.id IS NOT NULL AND 
              members.email_addr <> 'initial@yellowelevator.com' AND 
              members.email_addr NOT LIKE 'team%@yellowelevator.com'";
    if (!isset($_POST['non_attached'])) {
        $employers_str = "";
        $jobs_str = "";
        if (isset($_POST['jobs'])) {
            $match .= " AND member_jobs.job IN (". trim($_POST['jobs']). ")";
        } elseif (isset($_POST['employers'])) {
            $employers = explode(',', $_POST['employers']);
            foreach ($employers as $i=>$id) {
                $employers[$i] = "'". trim($id). "'";
            }
            $employers_str = implode(',', $employers);
            $match .= " AND jobs.employer IN (". $employers_str. ")";
        }
    }
    
    if (isset($_POST['resume_id'])) {
        $match .= " AND (member_jobs.`resume` = ". $_POST['resume_id']. " OR referrals.`resume` = ". $_POST['resume_id']. ")";
    }
    
    if (isset($_POST['page'])) {
        $page = $_POST['page'];
    }
    
    $criteria = array(
        'columns' => "member_jobs.id AS member_job_id, members.email_addr, members.phone_num, 
                      member_jobs.progress_notes, referrals.id AS ref_id, 
                      CONCAT(members.lastname, ', ', members.firstname) AS member_name, 
                      member_jobs.job AS job_id, jobs.title AS job_title, jobs.employer AS employer_id, 
                      referrals.resume AS resume_id, resumes.name AS resume_name, 
                      member_jobs.resume AS app_resume_id, resumes_1.name AS app_resume_name, 
                      DATE_FORMAT(referrals.referred_on, '%e %b, %Y') AS formatted_referred_on, 
                      DATE_FORMAT(member_jobs.applied_on, '%e %b, %Y') AS formatted_applied_on, 
                      DATE_FORMAT(referrals.employer_agreed_terms_on, '%e %b, %Y') AS formatted_employer_agreed_terms_on,
                      DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on,
                      DATE_FORMAT(referrals.employer_rejected_on, '%e %b, %Y') AS formatted_employer_rejected_on, 
                      DATE_FORMAT(member_jobs.remind_on, '%e %b, %Y') AS formatted_remind_on, 
                      DATEDIFF(member_jobs.remind_on, NOW()) AS days_left, 
                      (SELECT COUNT(id) 
                       FROM resumes WHERE member = members.email_addr 
                       AND is_yel_uploaded = TRUE) AS num_yel_resumes,
                      (SELECT COUNT(id) 
                       FROM resumes WHERE member = members.email_addr 
                       AND is_yel_uploaded = FALSE) AS num_self_resumes,
                      (SELECT COUNT(id) 
                       FROM member_jobs WHERE member = members.email_addr) AS num_attached_jobs",
        'joins' => "member_jobs ON member_jobs.member = members.email_addr, 
                    jobs ON jobs.id = member_jobs.job, 
                    referrals ON referrals.referee = member_jobs.member 
                        AND referrals.job = member_jobs.job, 
                    resumes ON resumes.id = referrals.resume, 
                    resumes AS resumes_1 ON resumes_1.id = member_jobs.resume", 
        'match' => $match,
        'order' => $order_by
    );
    
    $member = new Member();
    $result = $member->find($criteria);
    
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }

    if (!$result) {
        echo 'ko';
        exit();
    }
    
    $total_pages = ceil(count($result) / $GLOBALS['default_results_per_page']);
    if ($page > $total_pages) {
        $page = $total_pages;
    }
    
    $offset = 0;
    if ($page > 1) {
        $offset = ($page-1) * $GLOBALS['default_results_per_page'];
        $offset = ($offset < 0) ? 0 : $offset;
    }
    
    $criteria['limit'] = $offset. ", ". $GLOBALS['default_results_per_page'];
    $result = $member->find($criteria);
    
    foreach($result as $i=>$row) {
        $result[$i]['member_name'] = htmlspecialchars_decode(stripslashes($row['member_name']));
        $result[$i]['progress_notes'] = htmlspecialchars_decode(stripslashes($row['progress_notes']));
    }

    $response = array(
        'applicants' => array(
            'total_pages' => $total_pages,
            'applicant' => $result
        )
    );
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_members') {
    $member_search = new MemberSearch();
    $order_by = "members.lastname ASC";
    $page = 1;
    
    $criteria = array();
    if ($_POST['show_all'] == '0') {
        if (!empty($_POST['email'])) {
            $criteria['email'] = $_POST['email'];
        }
        
        if (!empty($_POST['name'])) {
            $criteria['name'] = $_POST['name'];
        }
        
        if (!empty($_POST['position'])) {
            $criteria['position'] = $_POST['position'];
        }
        
        if (!empty($_POST['employer'])) {
            $criteria['employer'] = $_POST['employer'];
        }
        
        if (!empty($_POST['total_work_years']) && $_POST['total_work_years'] >= 1) {
            $criteria['total_work_years'] = $_POST['total_work_years'];
        }
        
        // if (!empty($_POST['notice_period']) && $_POST['notice_period'] >= 1) {
        //     $criteria['notice_period'] = $_POST['notice_period'];
        // }
        
        if (!empty($_POST['specialization']) && $_POST['specialization'] >= 1) {
            $criteria['specialization'] = $_POST['specialization'];
        }
        
        if (!empty($_POST['emp_desc']) && $_POST['emp_desc'] >= 1) {
            $criteria['emp_desc'] = $_POST['emp_desc'];
        }
        
        // if (!empty($_POST['emp_specialization']) && $_POST['emp_specialization'] >= 1) {
        //     $criteria['emp_spec'] = $_POST['emp_spec'];
        // }
        // 
        // if (!empty($_POST['exp_sal_start']) && $_POST['exp_sal_start'] >= 1) {
        //     $criteria['expected_salary']['start'] = $_POST['exp_sal_start'];
        //     
        //     if ($_POST['exp_sal_currency'] != '0') {
        //         $criteria['expected_salary']['currency'] = $_POST['exp_sal_currency'];
        //     } else {
        //         $criteria['expected_salary']['currency'] = '';
        //     }
        //     
        //     if (!empty($_POST['exp_sal_end']) && $_POST['exp_sal_end'] >= 1) {
        //         $criteria['expected_salary']['end'] = $_POST['exp_sal_end'];
        //     }
        // }
        
        if (!empty($_POST['seeking'])) {
            $criteria['seeking_keywords'] = $_POST['seeking'];
        }
    }
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $criteria['order_by'] = $order_by;
    $criteria['limit'] = $GLOBALS['default_results_per_page'];
    
    if (isset($_POST['page'])) {
        $page = $_POST['page'];
    }
    
    $offset = 0;
    if ($page > 1) {
        $offset = ($page-1) * $GLOBALS['default_results_per_page'];
        $offset = ($offset < 0) ? 0 : $offset;
    }
    $criteria['offset'] = $offset;
    
    $result = $member_search->search_using($criteria);
    if (is_null($result) || count($result) <= 0) {
        echo '0';
        exit();
    }

    if ($result === false) {
        echo 'ko';
        exit();
    }
    
    // get last login date
    foreach ($result as $i=>$row) {
        $sub_criteria_last_login = array(
            'columns' => "DATE_FORMAT(member_sessions.last_login, '%e %b, %Y') AS formatted_last_login", 
            'joins' => "member_sessions ON member_sessions.member = members.email_addr", 
            'match' => "members.email_addr = '". $row['email_addr']. "'", 
            'order' => "member_sessions.last_login DESC", 
            'limit' => "1"
        );
        
        $member = new Member();
        $sub_result_last_login = $member->find($sub_criteria_last_login);
        if (!is_null($sub_result_last_login) && count($sub_result_last_login) > 0) {
            $result[$i]['last_login'] = $sub_result_last_login[0]['formatted_last_login'];
        } else {
            $result[$i]['last_login'] = '';
        }
    }
    
    // get the job_profile
    foreach ($result as $i=>$row) {
        $sub_criteria = array(
            'columns' => "member_job_profiles.member, member_job_profiles.position_title, 
                          member_job_profiles.employer, 
                          date_format(member_job_profiles.work_from, '%b, %Y') AS formatted_work_from, 
                          date_format(member_job_profiles.work_to, '%b, %Y') AS formatted_work_to", 
            'joins' => "member_job_profiles ON member_job_profiles.member = members.email_addr", 
            'match' => "member_job_profiles.member IS NOT NULL AND 
                        members.email_addr = '". $row['email_addr']. "'", 
            'order' => "member_job_profiles.member, member_job_profiles.work_from DESC", 
            'limit' => "1"
        );
        
        $member = new Member();
        $sub_result = $member->find($sub_criteria);
        if (count($sub_result) > 0 && !is_null($sub_result)) {
            $sub_row = $sub_result[0];
            $result[$i]['position_title'] = htmlspecialchars_decode(stripslashes($sub_row['position_title']));
            $result[$i]['employer'] = htmlspecialchars_decode(stripslashes($sub_row['employer']));
            $result[$i]['formatted_work_from'] = $sub_row['formatted_work_from'];
            $result[$i]['formatted_work_to'] = $sub_row['formatted_work_to'];
        }
    }
    
    foreach($result as $i=>$row) {
        $result[$i]['member_name'] = htmlspecialchars_decode(stripslashes($row['member_name']));
        // $result[$i]['expected_salary'] = number_format($row['expected_salary'], 2, '.', ',');
        // 
        // if (!empty($row['expected_salary_end']) && !is_null($row['expected_salary_end']) &&
        //     $row['expected_salary_end'] > 0) {
        //     $result[$i]['expected_salary_end'] = number_format($row['expected_salary_end'], 2, '.', ',');
        // } else {
        //     $result[$i]['expected_salary'] = '';
        // }
    }

    $response = array(
        'members' => array(
            'total_pages' => ceil($member_search->total_results() / $criteria['limit']),
            'member' => $result
        )
    );
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'deactivate') {
    $data = array();
    $data['active'] = 'N';
    
    $member = new Member($_POST['id']);
    $member->setAdmin(true);
    if ($member->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'activate') {
    $new_password = generate_random_string_of(6);
    $data = array();
    $data['active'] = 'Y';
    $data['password'] = md5($new_password);
    
    $member = new Employer($_POST['id']);
    $member->setAdmin(true);
    if ($member->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    $lines = file(dirname(__FILE__). '/../private/mail/member_password_reset_admin.txt');
    $message = '';
    foreach($lines as $line) {
        $message .= $line;
    }
    
    $message = str_replace('%temporary_password%', $new_password, $message);
    $subject = "Member Password Reset";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($member->getEmailAddress(), $subject, $message, $headers);
    
    // $handle = fopen('/tmp/email_to_'. $member->getEmailAddress(). '.txt', 'w');
    // fwrite($handle, 'Subject: '. $subject. "\n\n");
    // fwrite($handle, $message);
    // fclose($handle);
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'reset_password') {
    $new_password = generate_random_string_of(6);
    $data = array();
    $data['password'] = md5($new_password);
    $member = new Member($_POST['id']);
    $member->setAdmin(true);
    if ($member->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    $lines = file(dirname(__FILE__). '/../private/mail/member_password_reset_admin.txt');
    $message = '';
    foreach($lines as $line) {
        $message .= $line;
    }
    
    $message = str_replace('%temporary_password%', $new_password, $message);
    $subject = "Member Password Reset";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($member->getEmailAddress(), $subject, $message, $headers);
    
    // $handle = fopen('/tmp/email_to_'. $member->getEmailAddress(). '.txt', 'w');
    // fwrite($handle, 'Subject: '. $subject. "\n\n");
    // fwrite($handle, $message);
    // fclose($handle);
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_notes') {
    if ($_POST['is_app'] == '1') {
        $referral_buffer = new ReferralBuffer($_POST['id']);
        $record = $referral_buffer->get();
        echo htmlspecialchars_decode(stripslashes($record[0]['notes']));
    } else {
        $member = new Member($_POST['id']);
        echo htmlspecialchars_decode(stripslashes($member->getNotes()));
    }
    exit();
}

if ($_POST['action'] == 'update_notes') {
    if ($_POST['is_app'] == '1') {
        $data['notes'] = sanitize(stripslashes($_POST['notes']));
        $referral_buffer = new ReferralBuffer($_POST['id']);
        if ($referral_buffer->update($data) === false) {
            echo 'ko';
            exit();
        }
    } else {
        $member = new Member($_POST['id']);
        if ($member->saveNotes($_POST['notes']) === false) {
            echo 'ko';
            exit();
        }
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_progress_notes') {
    $progress_notes = '';
    if ($_POST['is_buffer'] == '1') {
        $criteria = array(
            'columns' => "progress_notes",
            'match' => "id = ". $_POST['id'],
            'limit' => "1"
        );
        $buffer = new ReferralBuffer();
        $result = $buffer->find($criteria);
        $progress_notes = htmlspecialchars_decode(stripslashes($result[0]['progress_notes']));
    } else {
        $member = new Member();
        $progress_notes = htmlspecialchars_decode(stripslashes($member->getProgressNotes($_POST['id'])));
    }
    
    if (empty($progress_notes)) {
        echo "--- ". date('Y-m-d H:i'). " ---\n";
    } else {
        echo $progress_notes. "\n\n--- ". date('Y-m-d H:i'). " ---\n";
    }
    exit();
}

if ($_POST['action'] == 'update_progress_notes') {
    if ($_POST['is_buffer'] == '1') {
        $data = array();
        $data['progress_notes'] = $_POST['notes'];
        
        $buffer = new ReferralBuffer($_POST['id']);
        if ($buffer->update($data) === false) {
            echo 'ko';
            exit();
        }
    } else {
        $member = new Member();
        if ($member->saveProgressNotes($_POST['id'], $_POST['notes']) === false) {
            echo 'ko';
            exit();
        }
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'delete_application') {
    if (isset($_POST['is_buffer'])) {
        $referral_buffer = new ReferralBuffer($_POST['id']);
        if ($referral_buffer->delete() === false) {
            echo 'ko';
            exit();
        }
    } else {
        $member = new Member();
        if ($member->removeAppliedJob($_POST['id']) === false) {
            echo 'ko';
            exit();
        }
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'sign_up') {
    // 0. get all related buffer records
    $referral_buffer = new ReferralBuffer($_POST['id']);
    $result = $referral_buffer->get();
    $candidate_email = $result[0]['candidate_email'];
    
    $criteria = array(
        'columns' => "*", 
        'match' => "candidate_email = '". $result[0]['candidate_email']. "'"
    );
    $buffers = $referral_buffer->find($criteria);
    
    foreach ($buffers as $a_buffer) {
        // 1. get the buffer record
        $buffer = new ReferralBuffer($a_buffer['id']);
        $buffer_result = $buffer->get();
        
        $criteria = array(
            'columns' => "jobs.title AS job_title", 
            'joins' => "jobs ON jobs.id = referral_buffers.job", 
            'match' => "referral_buffers.id = ". $a_buffer['id'],
            'limit' => "1"
        );
        $job_result = $buffer->find($criteria);
        
        // 2. check whether a referrer is needed
        $referrer_successfully_created = false;
        $needs_to_be_connected = false;
        $referrer_email = explode('@', $buffer_result[0]['referrer_email']);
        if ($referrer_email[1] != 'yellowelevator.com') {
            $needs_to_be_connected = true;

            // referrer is already a member?
            $member = new Member($buffer_result[0]['referrer_email']);
            $result = $member->get();
            if (is_null($result) || count($result) <= 0) {
                if (create_member_from($buffer_result[0]['referrer_email'], 
                                       $buffer_result[0]['referrer_name'], 
                                       $buffer_result[0]['referrer_phone'], 
                                       $_POST['employee_id']) === false) {
                    $needs_to_be_connected = false;
                    $referrer_successfully_created = false;
                } else {
                    $referrer_successfully_created = true;
                }
            } else {
                $referrer_successfully_created = true;
            }
        }

        $referrer = new Member($buffer_result[0]['referrer_email']);

        // 3. create member account
        // member already exists?
        $member = new Member($buffer_result[0]['candidate_email']);
        $result = $member->get();
        if (is_null($result) || count($result) <= 0) {
            if (create_member_from($buffer_result[0]['candidate_email'], 
                                   $buffer_result[0]['candidate_name'], 
                                   $buffer_result[0]['candidate_phone'], 
                                   $_POST['employee_id']) === false) {
                echo 'ko:member';
                exit();
            }
        }
        
        // 3.5 save the notes by appending them
        // $existing_notes = htmlspecialchars_decode(stripslashes($member->getNotes()));
        // $member->saveNotes($existing_notes. "\n\n[(". $buffer_result[0]['job']. ") ". $job_result[0]['job_title']. "] ". $buffer_result[0]['notes']);

        // 4. create connection
        $connection_is_success = true;
        if ($needs_to_be_connected && $referrer_successfully_created) {
            $connection_is_success = $referrer->addReferee($buffer_result[0]['candidate_email']);
        }

        // 5. move resume
        $resume = null;
        $resume_successfully_moved = false;
        if (!is_null($buffer_result[0]['existing_resume_id']) && 
            is_null($buffer_result[0]['resume_file_hash'])) {
            // used pre-uploaded resumes
            $resume = new Resume($buffer_result[0]['candidate_email'], $buffer_result[0]['existing_resume_id']);
            $resume_successfully_moved = true;
        } else if (is_null($buffer_result[0]['existing_resume_id']) && 
                   !is_null($buffer_result[0]['resume_file_hash'])) {
            // move buffered resume
            $resume = new Resume($buffer_result[0]['candidate_email']);
            $new_hash = generate_random_string_of(6);
            $data = array();
            $data['private'] = 'N';
            $data['modified_on'] = $buffer_result[0]['requested_on'];
            $data['name'] = $buffer_result[0]['resume_file_name'];
            $data['file_name'] = $data['name'];
            $data['file_size'] = $buffer_result[0]['resume_file_size'];
            $data['file_type'] = $buffer_result[0]['resume_file_type'];
            $data['file_hash'] = $new_hash;
        
            if ($resume->create($data) === false) {
                echo 'ko:resume';
                exit();
            }
        
            $original_file = $GLOBALS['buffered_resume_dir']. '/'. $a_buffer['id']. '.'. $buffer_result[0]['resume_file_hash'];
            if ($resume->copyFrom($original_file, $buffer_result[0]['resume_file_text']) === false) {
                echo 'ko:resume_copy';
                exit();
            }
            
            $resume_successfully_moved = true;
        }
        
        // 6. store the jobs applied
        if (!is_null($buffer_result[0]['job'])) {
            $data = array();
            $data['applied_on'] = $buffer_result[0]['requested_on'];
            $data['job'] = $buffer_result[0]['job'];
            $data['progress_notes'] = 'NULL';
            if (!is_null($buffer_result[0]['progress_notes'])) {
                $data['progress_notes'] = htmlspecialchars_decode(stripslashes($buffer_result[0]['progress_notes']));
            } 
            
            if ($referrer_email[1] != 'yellowelevator.com') {
                $data['referrer'] = $referrer->getId();
            }
            
            if (!is_null($resume)) {
                $data['resume'] = $resume->getId();
            }
            
            $member->addJobApplied($data);
        }
        
        // 7. delete referralbuffer
        $buffer->delete();
    }
    
    echo $candidate_email;
    exit();
}

if ($_POST['action'] == 'check_member') {
    $buffer = new ReferralBuffer($_POST['id']);
    $buffer_result = $buffer->get();
    
    $member = new Member($buffer_result[0]['candidate_email']);
    $result = $member->get();
    if (is_null($result) || count($result) <= 0) {
        echo '0';
        exit();
    }
    
    // there is a conflict
    $conflicts = array(
        'buffered' => array(
            'name' => htmlspecialchars_decode(stripslashes($buffer_result[0]['candidate_name'])),
            'phone' => $buffer_result[0]['candidate_phone'],
            'created_on' => $buffer_result[0]['requested_on']
        ),
        'existing' => array(
            'name' => htmlspecialchars_decode(stripslashes($result[0]['firstname']. ', '. $result[0]['lastname'])),
            'phone' => $result[0]['phone_num'],
            'created_on' => $result[0]['joined_on']
        )
    );
    
    $response = array('conflicts' => $conflicts);
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'add_new_application') {
    $is_success = true;
    
    $employee = new Employee($_POST['id']);
    $branch = $employee->getBranch();
    $yel_email = 'team.'. strtolower($branch[0]['country']). '@yellowelevator.com';
    
    $data = array();
    $data['requested_on'] = now();
    $data['referrer_email'] = $yel_email;
    if ($_POST['referrer_is_yel'] == '0') {
        $data['referrer_email'] = $_POST['referrer_email'];
        $data['referrer_name'] = (empty($_POST['referrer_name']) ? "NULL" : $_POST['referrer_name']);
        $data['referrer_phone'] = (empty($_POST['referrer_phone']) ? "NULL" : $_POST['referrer_phone']);;
    }
    
    $data['candidate_email'] = (empty($_POST['candidate_email']) ? "NULL" : $_POST['candidate_email']);
    $data['candidate_name'] = (empty($_POST['candidate_name']) ? "NULL" : $_POST['candidate_name']);
    $data['candidate_phone'] = (empty($_POST['candidate_phone']) ? "NULL" : $_POST['candidate_phone']);
    $data['current_position'] = (empty($_POST['current_pos']) ? "NULL" : $_POST['current_pos']);
    $data['current_employer'] = (empty($_POST['current_emp']) ? "NULL" : $_POST['current_emp']);
    $data['progress_notes'] = (empty($_POST['notes']) ? "NULL" : $_POST['notes']);
    
    $jobs = explode(',', $_POST['jobs']);
    
    $buffer = new ReferralBuffer();
    foreach ($jobs as $job) {
        $data['job'] = $job;
        if ($buffer->create($data) === false) {
            $is_success = false;
        }
    }
    
    if (!$is_success) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'edit_candidate_phone') {
    $data = array();
    $data['candidate_phone'] = $_POST['phone'];
    
    $buffer = new ReferralBuffer($_POST['id']);
    if ($buffer->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'edit_candidate_email') {
    $data = array();
    $data['candidate_email'] = $_POST['email'];
    
    $buffer = new ReferralBuffer($_POST['id']);
    if ($buffer->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_referrer') {
    $criteria = array(
        'columns' => "candidate_name, referrer_name, referrer_email, referrer_phone", 
        'match' => "id = ". $_POST['id'],
        'limit' => "1"
    );
    
    $referral_buffer = new ReferralBuffer();
    $result = $referral_buffer->find($criteria);
    
    if (is_null($result) || empty($result) || $result === false) {
        echo 'ko';
        exit();
    }
    
    $result[0]['candidate_name'] = htmlspecialchars_decode(stripslashes($result[0]['candidate_name']));
    $result[0]['referrer_name'] = htmlspecialchars_decode(stripslashes($result[0]['referrer_name']));
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('referrer' => $result));
    exit();
}

if ($_POST['action'] == 'save_referrer') {
    $data = array();
    $data['referrer_name'] = $_POST['referrer_name'];
    $data['referrer_email'] = $_POST['referrer_email'];
    $data['referrer_phone'] = $_POST['referrer_phone'];
    
    $referral_buffer = new ReferralBuffer($_POST['id']);
    if ($referral_buffer->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_other_jobs') {
    $result = array();
    if ($_POST['is_app'] == '1') {
        $match = "referral_buffers.job IS NOT NULL";
        if (isset($_POST['candidate_email'])) {
            $match .= " AND referral_buffers.candidate_email = '". $_POST['candidate_email']. "'";
        } else if (isset($_POST['candidate_name'])) {
            $match .= " AND referral_buffers.candidate_name LIKE '". $_POST['candidate_name']. "'";
        }

        $criteria = array(
            'columns' => "jobs.title AS job, employers.name AS employer,
                          DATE_FORMAT(referral_buffers.requested_on, '%e %b, %Y') AS formatted_requested_on", 
            'joins' => "jobs ON jobs.id = referral_buffers.job, 
                        employers ON employers.id = jobs.employer", 
            'match' => $match, 
            'order' => "referral_buffers.requested_on DESC, jobs.title"
        );

        $referral_buffer = new ReferralBuffer();
        $result = $referral_buffer->find($criteria);
    } else {
        $member = new Member($_POST['email_addr']);
        $result = $member->getJobsApplied();
    }
    
    if ($result === false || is_null($result)  || empty($result)) {
        echo 'ko';
        exit();
    }
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('applications' => array('application' => $result)));
    exit();
}

if ($_POST['action'] == 'apply_jobs') {
    $member = new Member($_POST['id']);
    
    $data = array();
    $data['applied_on'] = today();
    
    $jobs = explode(',', $_POST['jobs']);
    $failed_jobs = array();
    foreach ($jobs as $job) {
        $data['job'] = trim($job);
        
        if ($member->addJobApplied($data) === false) {
            $failed_jobs[] = $data['job'];
        }
    }
    
    if (!empty($failed_jobs)) {
        $jobs_str = implode(',', $failed_jobs);
        
        $job = new Job();
        $criteria = array(
            'columns' => "jobs.title, employers.name AS employer, 
                          DATE_FORMAT(jobs.expire_on, '%e %b, %Y) AS expire_on", 
            'joins' => "employers ON employers.id = jobs.employer", 
            'match' => "jobs.id IN (". $jobs_str. ")"
        );
        
        header('Content-type: text/xml');
        echo $xml_dom->get_xml_from_array(array('jobs' => array('job' => $result)));
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_referral_details') {
    $criteria = array(
        'columns' => "jobs.title, jobs.employer, currencies.symbol, 
                      CONCAT(members.lastname, ', ', members.firstname) AS referee", 
        'joins' => "jobs ON jobs.id = referrals.job, 
                    members ON members.email_addr = referrals.referee, 
                    currencies ON currencies.country_code = jobs.country",
        'match' => "referrals.id = ". $_POST['id'], 
        'limit' => "1"
    );
    
    $referral = new Referral();
    $result = $referral->find($criteria);
    
    $employer = new Employer($result[0]['employer']);
    $branch = $employer->getAssociatedBranch();
    
    $response = array(
        'title' => $result[0]['title'],
        'employer' => $result[0]['employer'],
        'referee' => $result[0]['referee'], 
        'currency' => Currency::getSymbolFromCountryCode($branch[0]['country'])
    );
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('referral' => $response));
    exit();
}

if ($_POST['action'] == 'confirm_employed') {
    $work_commence_on = $_POST['work_commence_on'];
    $is_replacement = false;
    $is_free_replacement = false;
    $previous_referral = '0';
    $previous_invoice = '0';
    
    $employee = new Employee($_POST['employee_id']);
    $employee_email = $employee->getEmailAddress();
    if (is_null($employee_email) || empty($employee_email) || $employee_email === false) {
        $employee_email = '';
    }
    
    // 1. Update the referral to employed
    $referral = new Referral($_POST['id']);
    
    $criteria = array(
        'columns' => "jobs.id AS job_id, jobs.title, jobs.employer, 
                      referrals.member, referrals.referee", 
        'joins' => "jobs ON jobs.id = referrals.job", 
        'match' => "referrals.id = ". $_POST['id'], 
        'limit' => "1"
    );
    
    $result = $referral->find($criteria);
    $employer = new Employer($result[0]['employer']);
    $candidate = new Member($result[0]['referee']);
    $member = new Member($result[0]['member']);
    $job = array(
        'id' => $result[0]['job_id'],
        'title' => htmlspecialchars_decode(stripslashes($result[0]['title']))
    );
    
    $salary = $_POST['salary'];
    $irc_id = ($member->isIRC()) ? $member->getId() : NULL;
    $total_reward = $referral->calculateRewardFrom($salary, $irc_id);
    $total_token_reward = $total_reward * 0.30;
    $total_reward_to_referrer = $total_reward - $total_token_reward;
    
    $data = array();
    $data['employed_on'] = $_POST['employed_on'];
    $data['work_commence_on'] = $work_commence_on;
    $data['salary_per_annum'] = $salary;
    $data['total_reward'] = $total_reward_to_referrer;
    $data['total_token_reward'] = $total_token_reward;
    $data['guarantee_expire_on'] = $referral->getGuaranteeExpiryDateWith($salary, $work_commence_on);
    
    // 1.1 Check whether the reward is 0.00 or NULL. If it is, then the employer account is not ready. 
    if ($data['total_reward'] <= 0.00 || 
        $data['guarantee_expire_on'] == '0000-00-00 00:00:00' || 
        is_null($data['guarantee_expire_on'])) {
        echo '-1';
        exit();
    }
    
    if ($referral->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    // 2. Generate an Reference Invoice
    // 2.1 Check whether this job is a replacement for a previous failed referral. 
    $criteria = array(
        'columns' => 'id',
        'match' => "job = ". $job['id']. " AND 
                    (replacement_authorized_on IS NOT NULL AND replacement_authorized_on <> '0000-00-00 00:00:00') AND 
                    (replaced_on IS NULL OR replaced_on = '0000-00-00 00:00:00') AND 
                    replaced_referral IS NULL", 
        'limit' => '1'
    );
    $result = $referral->find($criteria);
    
    if (count($result) > 0 && !is_null($result)) {
        $is_replacement = true;
        $previous_referral = $result[0]['id'];
    }
    
    // 2.2 Get all the fees, discounts and extras and calculate accordingly.
    $fees = $employer->getFees();
    $payment_terms_days = $employer->getPaymentTermsInDays();
    
    $subtotal = $discount = $extra_charges = 0.00;
    foreach($fees as $fee) {
        if ($salary >= $fee['salary_start'] && 
            ($salary <= $fee['salary_end'] || $fee['salary_end'] == 0)) {
            $discount = -($salary * ($fee['discount'] / 100.00));
            $subtotal = ($salary * ($fee['service_fee'] / 100.00));
            break;
        }
    }
    $new_total_fee = $subtotal + $discount;
    
    // 2.2.1 If this is a replacement, re-calculate accordingly by taking the previously invoiced amount.
    $credit_amount = 0;
    if ($is_replacement) {
        // 2.2.1a If it is a replacement, get the previously invoiced amount.
        $criteria = array(
            'columns' => 'invoices.id, SUM(invoice_items.amount) AS amount_payable', 
            'joins' => 'invoice_items ON invoice_items.invoice = invoices.id', 
            'match' => "invoices.type ='R' AND invoices.is_copy = FALSE AND 
                        invoice_items.item = ". $previous_referral, 
            'group' => 'invoices.id'
        );
        $result = Invoice::find($query);
        $amount_payable = $result[0]['amount_payable'];
        $previous_invoice = $result[0]['id'];
        
        // 2.2.1b Get the difference.
        $amount_difference = $new_total_fee - $amount_payable;
        
        // 2.2.1c If the difference in fees is more than zero, then use the amount difference. 
        if (round($amount_difference, 2) <= 0) {
            $subtotal = $discount = $extra_charges = 0.00;
            $is_free_replacement = true;
            $credit_amount = abs($amount_difference);
        } else {
            $discount = $extra_charges = 0.00;
            $subtotal = $amount_difference;
        }
    }
    
    // 2.3 Generate the invoice
    $issued_on = date('j M, Y');
    $data = array();
    $data['issued_on'] = now();
    $data['type'] = 'R';
    $data['employer'] = $employer->getId();
    $data['payable_by'] = sql_date_add($data['issued_on'], $payment_terms_days, 'day');
    
    if ($is_free_replacement) {
        $data['paid_on'] = $data['issued_on'];
        $data['paid_through'] = 'CSH';
        $data['paid_id'] = 'FREE_REPLACEMENT';
    }
    
    $invoice = Invoice::create($data);
    if (!$invoice) {
        echo 'ko';
        exit();
    }
    
    $referral_desc = 'Reference fee for ['. $job['id']. '] '. $job['title']. ' of '. $candidate->getFullName();
    if ($is_free_replacement) {
        $referral_desc = 'Free replacement for Invoice: '. pad($previous_invoice, 11, '0');
    } 
    
    if ($is_replacement && !$is_free_replacement) {
        $referral_desc = 'Replacement fee for Invoice: '. pad($previous_invoice, 11, '0');
    }
    
    $item_added = Invoice::addItem($invoice, $subtotal, $referral->getId(), $referral_desc);
    if (!$item_added) {
        echo "ko";
        exit();
    }
    
    if (!$is_free_replacement) {
        $item_added = Invoice::addItem($invoice, $discount, $referral->getId(), 'Discount');
        if (!$item_added) {
            echo "ko";
            exit();
        }

        $item_added = Invoice::addItem($invoice, $extra_charges, $referral->getId(), 'Extra charges');
        if (!$item_added) {
            echo "ko";
            exit();
        }
        
        $data_copy = $data;
        $data_copy['is_copy'] = '1';
        $invoice_copy = Invoice::create($data_copy);
        if (!$invoice_copy) {
            echo 'ko';
            exit();
        }
        
        $item_copy_added = Invoice::addItem($invoice_copy, $subtotal, $referral->getId(), 'Job referral fee');
        $item_copy_added = Invoice::addItem($invoice_copy, $discount, $referral->getId(), 'Discount');
        $item_copy_added = Invoice::addItem($invoice_copy, $extra_charges, $referral->getId(), 'Extra charges');
        
        // generate and send invoice
        $filename = generate_random_string_of(8). '.'. generate_random_string_of(8);
        $filename_copy = generate_random_string_of(8). '.'. generate_random_string_of(8);
        $branch = $employer->getAssociatedBranch();
        $sales = 'sales.'. strtolower($branch[0]['country']). '@yellowelevator.com';
        $currency = $branch[0]['currency'];

        $items = Invoice::getItems($invoice);
        $items_copy = Invoice::getItems($invoice_copy);
        $amount_payable = 0.00;
        foreach($items as $i=>$item) {
            $amount_payable += $item['amount'];
            $items[$i]['amount'] = number_format($item['amount'], 2, '.', ', ');
        }
        $amount_payable = number_format($amount_payable, 2, '.', ', ');
        $invoice_or_receipt = 'Invoice';
        
        
        // generate for HR
        $pdf = new GeneralInvoice();
        $pdf->AliasNbPages();
        $pdf->SetAuthor('Yellow Elevator. This invoice was automatically generated. Signature is not required.');
        $pdf->SetTitle($GLOBALS['COMPANYNAME']. ' - Invoice '. pad($invoice, 11, '0'));
        $pdf->SetInvoiceType('R', $invoice_or_receipt);
        $pdf->SetCurrency($currency);
        $pdf->SetBranch($branch);
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFillColor(54, 54, 54);
        $pdf->Cell(60, 5, "Invoice Number",1,0,'C',1);
        $pdf->Cell(1);
        $pdf->Cell(33, 5, "Issuance Date",1,0,'C',1);
        $pdf->Cell(1);
        $pdf->Cell(33, 5, "Payable By",1,0,'C',1);
        $pdf->Cell(1);
        $pdf->Cell(0, 5, "Amount Payable (". $currency. ")",1,0,'C',1);
        $pdf->Ln(6);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(60, 5, pad($invoice, 11, '0'),1,0,'C');
        $pdf->Cell(1);
        $pdf->Cell(33, 5, $issued_on, 1,0,'C');
        $pdf->Cell(1);
        $pdf->Cell(33, 5, format_date($data['payable_by']),1,0,'C');
        $pdf->Cell(1);
        $pdf->Cell(0, 5, $amount_payable,1,0,'C');
        $pdf->Ln(6);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(60, 5, "User ID",1,0,'C',1);
        $pdf->Cell(1);
        $pdf->Cell(0, 5, "Employer Name",1,0,'C',1);
        $pdf->Ln(6);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(60, 5, $employer->getId(),1,0,'C');
        $pdf->Cell(1);
        $pdf->Cell(0, 5, $employer->getName(),1,0,'C');
        $pdf->Ln(10);
        
        $table_header = array("No.", "Item", "Amount (". $currency. ")");
        $pdf->FancyTable($table_header, $items, $amount_payable);
        
        $pdf->Ln(13);
        $pdf->SetFont('','I');
        $pdf->Cell(0, 0, "This invoice was automatically generated. Signature is not required.", 0, 0, 'C');
        $pdf->Ln(6);
        $pdf->Cell(0, 5, "Payment Notice",'LTR',0,'C');
        $pdf->Ln();
        $pdf->Cell(0, 5, "- Payment shall be made payable to ". $branch[0]['branch']. ".", 'LR', 0, 'C');
        $pdf->Ln();
        $pdf->Cell(0, 5, "- To facilitate the processing of the payment, please write down the invoice number(s) on your cheque(s)/payment slip(s)", 'LBR', 0, 'C');
        $pdf->Ln(10);
        $pdf->Cell(0, 0, "E. & O. E.", 0, 0, 'C');
        
        $pdf->Close();
        $pdf->Output($GLOBALS['data_path']. '/general_invoices/'. $filename. '.pdf', 'F');
        
        // generate general invoice
        $pdf = new GeneralInvoice();
        $pdf->AliasNbPages();
        $pdf->SetAuthor('Yellow Elevator. This invoice was automatically generated. Signature is not required.');
        $pdf->SetTitle($GLOBALS['COMPANYNAME']. ' - Invoice '. pad($invoice_copy, 11, '0'));
        $pdf->SetInvoiceType('R', $invoice_or_receipt);
        $pdf->SetCurrency($currency);
        $pdf->SetBranch($branch);
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFillColor(54, 54, 54);
        $pdf->Cell(60, 5, "Invoice Number",1,0,'C',1);
        $pdf->Cell(1);
        $pdf->Cell(33, 5, "Issuance Date",1,0,'C',1);
        $pdf->Cell(1);
        $pdf->Cell(33, 5, "Payable By",1,0,'C',1);
        $pdf->Cell(1);
        $pdf->Cell(0, 5, "Amount Payable (". $currency. ")",1,0,'C',1);
        $pdf->Ln(6);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(60, 5, pad($invoice_copy, 11, '0'),1,0,'C');
        $pdf->Cell(1);
        $pdf->Cell(33, 5, $issued_on, 1,0,'C');
        $pdf->Cell(1);
        $pdf->Cell(33, 5, format_date($data['payable_by']),1,0,'C');
        $pdf->Cell(1);
        $pdf->Cell(0, 5, $amount_payable,1,0,'C');
        $pdf->Ln(6);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(60, 5, "User ID",1,0,'C',1);
        $pdf->Cell(1);
        $pdf->Cell(0, 5, "Employer Name",1,0,'C',1);
        $pdf->Ln(6);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(60, 5, $employer->getId(),1,0,'C');
        $pdf->Cell(1);
        $pdf->Cell(0, 5, $employer->getName(),1,0,'C');
        $pdf->Ln(10);
        
        $table_header = array("No.", "Item", "Amount (". $currency. ")");
        $pdf->FancyTable($table_header, $items_copy, $amount_payable);
        
        $pdf->Ln(13);
        $pdf->SetFont('','I');
        $pdf->Cell(0, 0, "This invoice was automatically generated. Signature is not required.", 0, 0, 'C');
        $pdf->Ln(6);
        $pdf->Cell(0, 5, "Payment Notice",'LTR',0,'C');
        $pdf->Ln();
        $pdf->Cell(0, 5, "- Payment shall be made payable to ". $branch[0]['branch']. ".", 'LR', 0, 'C');
        $pdf->Ln();
        $pdf->Cell(0, 5, "- To facilitate the processing of the payment, please write down the invoice number(s) on your cheque(s)/payment slip(s)", 'LBR', 0, 'C');
        $pdf->Ln(10);
        $pdf->Cell(0, 0, "E. & O. E.", 0, 0, 'C');
        
        $pdf->Close();
        $pdf->Output($GLOBALS['data_path']. '/general_invoices/'. $filename_copy. '.pdf', 'F');
        
        $attachment = chunk_split(base64_encode(file_get_contents($GLOBALS['data_path']. '/general_invoices/'. $filename. '.pdf')));
        $attachment_copy = chunk_split(base64_encode(file_get_contents($GLOBALS['data_path']. '/general_invoices/'. $filename_copy. '.pdf')));

        $subject = "Notice of Invoice ". pad($invoice, 11, '0');
        $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
        if (!empty($employee_email)) {
            $headers .= 'Reply-To: '. $employee_email. "\n";
        }
        $headers .= 'Bcc: '. $sales. "\n";
        $headers .= 'MIME-Version: 1.0'. "\n";
        $headers .= 'Content-Type: multipart/mixed; boundary="yel_mail_sep_'. $filename. '";'. "\n\n";

        $body = '--yel_mail_sep_'. $filename. "\n";
        $body .= 'Content-Type: multipart/alternative; boundary="yel_mail_sep_alt_'. $filename. '"'. "\n";
        $body .= '--yel_mail_sep_alt_'. $filename. "\n";
        $body .= 'Content-Type: text/plain; charset="iso-8859-1"'. "\n";
        $body .= 'Content-Transfer-Encoding: 7bit"'. "\n";
        
        $mail_lines = file('../private/mail/employer_general_invoice.txt');
        $message = '';
        foreach ($mail_lines as $line) {
            $message .= $line;
        }

        $message = str_replace('%employer%', $employer->getName(), $message);
        $message = str_replace('%invoice%', pad($invoice, 11, '0'), $message);
        $message = str_replace('%issued_on%', $issued_on, $message);
        $message = str_replace('%payable_by%', format_date($data['payable_by']), $message);
        $message = str_replace('%amount%', $amount_payable, $message);
        $message = str_replace('%currency%', $currency, $message);
        
        $body .= $message. "\n";
        $body .= '--yel_mail_sep_alt_'. $filename. "--\n\n";
        $body .= '--yel_mail_sep_'. $filename. "\n";
        $body .= 'Content-Type: application/pdf; name="yel_invoice_'. pad($invoice, 11, '0'). '.pdf"'. "\n";
        $body .= 'Content-Transfer-Encoding: base64'. "\n";
        $body .= 'Content-Disposition: attachment'. "\n";
        $body .= $attachment. "\n";
        $body .= '--yel_mail_sep_'. $filename. "\n";
        $body .= 'Content-Type: application/pdf; name="yel_invoice_'. pad($invoice_copy, 11, '0'). '.pdf"'. "\n";
        $body .= 'Content-Transfer-Encoding: base64'. "\n";
        $body .= 'Content-Disposition: attachment'. "\n";
        $body .= $attachment_copy. "\n";
        $body .= '--yel_mail_sep_'. $filename. "--\n\n";
        mail($employer->getEmailAddress(), $subject, $body, $headers);
        
        // $handle = fopen('/tmp/email_to_'. $employer->getEmailAddress(). '.txt', 'w');
        // fwrite($handle, 'To: '. $employer->getEmailAddress(). "\n\n");
        // fwrite($handle, 'Header: '. $headers. "\n\n");
        // fwrite($handle, 'Subject: '. $subject. "\n\n");
        // fwrite($handle, $body);
        
        unlink($GLOBALS['data_path']. '/general_invoice/'. $filename. '.pdf');
        unlink($GLOBALS['data_path']. '/general_invoice/'. $filename_copy. '.pdf');
    } else {
        if ($credit_amount > 0) {
            $credit_note_desc = 'Refund of balance for Invoice: '. pad($previous_invoice, 11, '0');
            $filename = generate_random_string_of(8). '.'. generate_random_string_of(8);
            $expire_on = sql_date_add($issued_on, 30, 'day');
            
            Invoice::accompanyCreditNoteWith($previous_invoice, $invoice, $issued_on, $credit_amount);
            
            $branch = $employer->getAssociatedBranch();
            $sales = 'sales.'. strtolower($branch[0]['country']). '@yellowelevator.com';
            $branch[0]['address'] = str_replace(array("\r\n", "\r"), "\n", $branch[0]['address']);
            $branch['address_lines'] = explode("\n", $branch[0]['address']);
            $currency = Currency::getSymbolFromCountryCode($branch[0]['country']);
            
            $pdf = new CreditNote();
            $pdf->AliasNbPages();
            $pdf->SetAuthor('Yellow Elevator. This credit note was automatically generated. Signature is not required.');
            $pdf->SetTitle($GLOBALS['COMPANYNAME']. ' - Credit Note '. pad($invoice, 11, '0'));
            $pdf->SetRefundAmount($credit_amount);
            $pdf->SetDescription($credit_note_desc);
            $pdf->SetCurrency($currency);
            $pdf->SetBranch($branch);
            $pdf->AddPage();
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFillColor(54, 54, 54);
            $pdf->Cell(60, 5, "Credit Note Number",1,0,'C',1);
            $pdf->Cell(1);
            $pdf->Cell(33, 5, "Issuance Date",1,0,'C',1);
            $pdf->Cell(1);
            $pdf->Cell(33, 5, "Creditable By",1,0,'C',1);
            $pdf->Cell(1);
            $pdf->Cell(0, 5, "Amount Creditable (". $currency. ")",1,0,'C',1);
            $pdf->Ln(6);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(60, 5, pad($invoice, 11, '0'),1,0,'C');
            $pdf->Cell(1);
            $pdf->Cell(33, 5, $issued_on,1,0,'C');
            $pdf->Cell(1);
            $pdf->Cell(33, 5, $expire_on,1,0,'C');
            $pdf->Cell(1);
            $pdf->Cell(0, 5, number_format($credit_amount, 2, '.', ','),1,0,'C');
            $pdf->Ln(6);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(60, 5, "User ID",1,0,'C',1);
            $pdf->Cell(1);
            $pdf->Cell(0, 5, "Employer Name",1,0,'C',1);
            $pdf->Ln(6);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(60, 5, $employer->getId(),1,0,'C');
            $pdf->Cell(1);
            $pdf->Cell(0, 5, $employer->getName(),1,0,'C');
            $pdf->Ln(10);
            
            $table_header = array("No.", "Item", "Amount (". $currency. ")");
            $pdf->FancyTable($table_header);
            
            $pdf->Ln(13);
            $pdf->SetFont('','I');
            $pdf->Cell(0, 0, "This credit note was automatically generated. Signature is not required.", 0, 0, 'C');
            $pdf->Ln(6);
            $pdf->Cell(0, 5, "Refund Notice",'LTR',0,'C');
            $pdf->Ln();
            $pdf->Cell(0, 5, "- Refund will be made payable to ". $employer->getName(). ". ", 'LR', 0, 'C');
            $pdf->Ln();
            $pdf->Cell(0, 5, "- To facilitate the refund process, please inform us of any discrepancies.", 'LBR', 0, 'C');
            $pdf->Ln(10);
            $pdf->Cell(0, 0, "E. & O. E.", 0, 0, 'C');
            $pdf->Close();
            $pdf->Output($GLOBALS['data_path']. '/credit_notes/'. $filename. '.pdf', 'F');
            
            $attachment = chunk_split(base64_encode(file_get_contents($GLOBALS['data_path']. '/credit_notes/'. $filename. '.pdf')));

            $subject = "Balance Refund Notice of Invoice ". pad($previous_invoice, 11, '0');
            $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
            if (!empty($employee_email)) {
                $headers .= 'Reply-To: '. $employee_email. "\n";
            }
            $headers .= 'Bcc: '. $sales. "\n";
            $headers .= 'MIME-Version: 1.0'. "\n";
            $headers .= 'Content-Type: multipart/mixed; boundary="yel_mail_sep_'. $filename. '";'. "\n\n";

            $body = '--yel_mail_sep_'. $filename. "\n";
            $body .= 'Content-Type: multipart/alternative; boundary="yel_mail_sep_alt_'. $filename. '"'. "\n";
            $body .= '--yel_mail_sep_alt_'. $filename. "\n";
            $body .= 'Content-Type: text/plain; charset="iso-8859-1"'. "\n";
            $body .= 'Content-Transfer-Encoding: 7bit"'. "\n";
            
            $mail_lines = file('../private/mail/employer_credit_note.txt');
            $message = '';
            foreach ($mail_lines as $line) {
                $message .= $line;
            }

            $message = str_replace('%company%', $employer->getName(), $message);
            $message = str_replace('%previous_invoice%', pad($previous_invoice, 11, '0'), $message);
            $message = str_replace('%new_invoice%', pad($invoice, 11, '0'), $message);
            $message = str_replace('%job_title%', $job_title, $message);
            
            $body .= $message. "\n";
            $body .= '--yel_mail_sep_alt_'. $filename. "--\n\n";
            $body .= '--yel_mail_sep_'. $filename. "\n";
            $body .= 'Content-Type: application/pdf; name="yel_credit_note_'. pad($invoice, 11, '0'). '.pdf"'. "\n";
            $body .= 'Content-Transfer-Encoding: base64'. "\n";
            $body .= 'Content-Disposition: attachment'. "\n";
            $body .= $attachment. "\n";
            $body .= '--yel_mail_sep_'. $filename. "--\n\n";
            mail($employer->getEmailAddress(), $subject, $body, $headers);

            unlink($GLOBALS['data_path']. '/credit_notes/'. $filename. '.pdf');
        }
    }
    
    // 2.4 If it is a replacement, update both referrals to disable future replacements.
    if ($is_replacement) {
        $mysqli = Database::connect();
        $queries = "UPDATE referrals SET 
                    replaced_on = '". now(). "', 
                    replaced_referral = ". $referral->getId(). " 
                    WHERE id = ". $previous_referral. "; 
                    UPDATE referrals SET 
                    guarantee_expire_on = '". $work_commence_on. "', 
                    replacement_authorized_on = NULL, 
                    replaced_on = '". now(). "', 
                    replaced_referral = ". $referral->getId(). " 
                    WHERE id = ". $referral->getId();
        if (!$mysqli->transact($queries)) {
            echo 'ko';
            exit();
        }
    }
    
    // 3. Send a notification
    $mail_lines = file('../private/mail/member_reward.txt');
    $message = '';
    foreach ($mail_lines as $line) {
        $message .= $line;
    }

    $message = str_replace('%member_name%', $member->getFullName(), $message);
    $message = str_replace('%referee_name%', $candidate->getFullName(), $message);
    $message = str_replace('%employer%', $employer->getName(), $message);
    $message = str_replace('%job_title%', $job['title'], $message);
    $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
    $message = str_replace('%root%', $GLOBALS['root'], $message);
    $subject = desanitize($candidate->getFullName()). " was successfully employed!";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($member->getId(), $subject, $message, $headers);
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_referrer_remarks') {
    $referral_buffer = new ReferralBuffer($_POST['id']);
    $record = $referral_buffer->get();
    echo trim(htmlspecialchars_decode(stripslashes($record[0]['referrer_remarks'])));
    exit();
}

if ($_POST['action'] == 'get_remind_on') {
    $result = array();
    
    if ($_POST['is_buffer'] == '1') {
        $criteria = array(
            'columns' => "DATEDIFF(remind_on, NOW()) AS days_left",
            'match' => "id = ". $_POST['id'],
            'limit' => "1"
        );
        $buffer = new ReferralBuffer();
        $result = $buffer->find($criteria);
    } else {
        $criteria = array(
            'columns' => "DATEDIFF(member_jobs.remind_on, NOW()) AS days_left",
            'joins' => "member_jobs ON members.email_addr = member_jobs.member", 
            'match' => "member_jobs.id = ". $_POST['id'],
            'limit' => "1"
        );
        $member = new Member();
        $result = $member->find($criteria);
    }
    
    echo $result[0]['days_left'];
    exit();
}

if ($_POST['action'] == 'set_reminder') {
    if ($_POST['is_buffer'] == '1') {
        $data = array();
        $data['remind_on'] = sql_date_add(now(), $_POST['days'], 'day');
        $buffer = new ReferralBuffer($_POST['id']);
        if ($buffer->update($data) === false) {
            echo 'ko';
            exit();
        }
    } else {
        $member = new Member();
        if ($member->setReminder($_POST['id'], sql_date_add(now(), $_POST['days'], 'day')) === false) {
            echo 'ko';
            exit();
        }
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'reset_reminder') {
    if ($_POST['is_buffer'] == '1') {
        $data = array();
        $data['remind_on'] = 'NULL';
        $buffer = new ReferralBuffer($_POST['id']);
        if ($buffer->update($data) === false) {
            echo 'ko';
            exit();
        }
    } else {
        $member = new Member();
        if ($member->resetReminder($_POST['id']) === false) {
            echo 'ko';
            exit();
        }
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'bulk_add_new_applicants') {
    $employee = new Employee($_POST['id']);
    $branch = $employee->getBranch();
    $yel_email = 'team.'. strtolower($branch[0]['country']). '@yellowelevator.com';
    
    if (move_uploaded_file($_FILES['csv_file']['tmp_name'], "/tmp/". basename($_FILES['csv_file']['tmp_name']))) {
        $handle = fopen("/tmp/". basename($_FILES['csv_file']['tmp_name']), 'r');
        if ($handle !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 6) {
                    continue;
                }

                $data = array();
                $data['requested_on'] = now();
                $data['referrer_email'] = $yel_email;
                $data['candidate_email'] = sql_nullify($row[0]);
                $data['candidate_name'] = sql_nullify($row[1]);
                $data['candidate_phone'] = sql_nullify($row[2]);
                $data['current_position'] = sql_nullify($row[3]);
                $data['current_employer'] = sql_nullify($row[4]);
                $data['progress_notes'] = sql_nullify($row[5]);
                
                $jobs = explode(',', $_POST['bulk_new_applicant_jobs']);
                $buffer = new ReferralBuffer();
                foreach ($jobs as $job) {
                    $data['job'] = $job;
                    $buffer->create($data);
                }
            }
        }
        fclose($handle);
        
        @unlink("/tmp/". basename($_FILES['csv_file']['tmp_name']));
    }
    
    redirect_to('members.php');
    exit();
}

if ($_POST['action'] == 'bulk_add_new_candidates') {
    $employee = new Employee($_POST['id']);
    $branch = $employee->getBranch();
    $yel_email = 'team.'. strtolower($branch[0]['country']). '@yellowelevator.com';
    
    // 1. convert from CSV to array
    $candidates = array();
    $joined_on = now();
    if (move_uploaded_file($_FILES['members_csv_file']['tmp_name'], "/tmp/". basename($_FILES['members_csv_file']['tmp_name']))) {
        $handle = fopen("/tmp/". basename($_FILES['members_csv_file']['tmp_name']), 'r');
        if ($handle !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 11) {
                    continue;
                }
                
                $candidate = array();
                $candidate['joined_on'] = $joined_on;
                $candidate['updated_on'] = $joined_on;
                $candidate['added_by'] = $employee->getId();
                $candidate['email_addr'] = $row[0];
                $candidate['firstname'] = $row[1];
                $candidate['lastname'] = $row[2];
                $candidate['phone_num'] = $row[3];
                $candidate['citizenship'] = strtoupper($row[4]);
                $candidate['total_work_years'] = sql_nullify($row[5]);
                $candidate['position_title'] = sql_nullify($row[6]);
                $candidate['work_from'] = sql_nullify($row[7]);
                $candidate['work_to'] = sql_nullify($row[8]);
                $candidate['employer'] = sql_nullify($row[9]);
                $candidate['employer_specialization'] = sql_nullify($row[10]);
                
                $candidates[] = $candidate;
            }
        }
        fclose($handle);
        
        @unlink("/tmp/". basename($_FILES['members_csv_file']['tmp_name']));
    } else {
        redirect_to('members.php?page=members&error=c1');
        exit();
    }
    
    // 2. add candidates by creating passwords and send out email
    $failed_members = array();
    $members = array();
    
    foreach ($candidates as $candidate) {
        $member = new Member($candidate['email_addr']);
        
        $data = array();
        $data['firstname'] = $candidate['firstname'];
        $data['lastname'] = $candidate['lastname'];
        $data['phone_num'] = $candidate['phone_num'];
        $data['citizenship'] = $candidate['citizenship'];
        $data['total_work_years'] = $candidate['total_work_years'];
        $data['updated_on'] = $candidate['updated_on'];
        $data['joined_on'] = $candidate['joined_on'];
        $data['added_by'] = $candidate['added_by'];
        
        $new_password = generate_random_string_of(6);
        $hash = md5($new_password);
        $data['password'] = $hash;
        $data['forget_password_question'] = '1';
        $data['forget_password_answer'] = 'system picked';
        $data['active'] = 'Y';
        $data['invites_available'] = '10';
        
        if ($member->create($data) === false) {
            $failed_members[] = $candidate['email_addr'];
            continue;
        }
        
        $lines = file(dirname(__FILE__). '/../private/mail/member_welcome_with_password.txt');
        $message = '';
        foreach($lines as $line) {
            $message .= $line;
        }

        $message = str_replace('%member%', $data['firstname']. ', '. $data['lastname'], $message);
        $message = str_replace('%email_addr%', $candidate['email_addr'], $message);
        $message = str_replace('%temporary_password%', $new_password, $message);
        $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
        $message = str_replace('%root%', $GLOBALS['root'], $message);
        $subject = 'YellowElevator.com Job Recruitment Agency ('. $data['firstname']. ', '. $data['lastname']. ')' ;
        $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
        $headers .= 'Cc: team.my@yellowelevator.com'. "\n";
        // mail($candidate['email_addr'], $subject, $message, $headers);
        
        $handle = fopen('/tmp/email_to_'. $candidate['email_addr']. '.txt', 'w');
        fwrite($handle, 'Subject: '. $subject. "\n\n");
        fwrite($handle, $message);
        fclose($handle);
        
        $members[] = $candidate;
    }
    
    // 3. add career profiles and experiences
    $failed_careers = array();
    
    if (!empty($members)) {
        foreach ($members as $candidate) {
            $member = new Member($candidate['email_addr']);
            
            $data = array();
            $data['position_title'] = $candidate['position_title'];
            $data['employer'] = $candidate['employer'];
            $data['employer_specialization'] = $candidate['employer_specialization'];
            $data['work_from'] = $candidate['work_from'];
            $data['work_to'] = $candidate['work_to'];
            
            if ($member->addJobProfile($data) === false) {
                $failed_careers[] = $candidate['email_addr'];
                continue;
            }
        }
    } else {
        redirect_to('members.php?page=members&error=c2');
        exit();
    }
    
    // 4. send the failed members to the executive
    if (!empty($failed_members) || !empty($failed_careers)) {
        $subject = 'Failure to bulk add these email addresses';
        $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
        
        $message = '';
        if (!empty($failed_members)) {
            $message = 'The following candidates failed to be added:'. "\n\n";
            
            foreach ($failed_members as $f_member) {
                $message .= $f_member. "\n";
            }
        } else {
            $message = 'ALL candidates were successfully added!!!'. "\n\n";
        }
        
        $message .= "\n";
        if (!empty($failed_careers)) {
            $message .= 'The following candidates failed have their career profiles added:'. "\n\n";
            
            foreach ($failed_careers as $f_career) {
                $message .= $f_career. "\n";
            }
        } else {
            $message = 'ALL candidates career profiles were successfully added!!!'. "\n\n";
        }
        
        // mail('team.my@yellowelevator.com', $subject, $message, $headers);
        
        $handle = fopen('/tmp/email_to_team.yel'. '.txt', 'w');
        fwrite($handle, 'Subject: '. $subject. "\n\n");
        fwrite($handle, $message);
        fclose($handle);
        
        redirect_to('members.php?page=members&error=c3');
        exit();
    }
    
    redirect_to('members.php?page=members');
    exit();
}

?>