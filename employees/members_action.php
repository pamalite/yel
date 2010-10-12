<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

function create_member_from($_email_addr, $_fullname, $_phone) {
    if (empty($_email_addr) || empty($_fullname) || empty($_phone)) {
        return false;
    }
    
    $password = generate_random_string_of(6);
    $timestamp = now();
    $data = array();
    $data['phone_num'] = $_phone;
    $data['firstname'] = $_fullname;
    $data['lastname'] = $data['firstname'];
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
    
    $subject = 'New Membership from Yellow Elevator';
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    
    mail($_email_addr, $subject, $message, $headers);
    
    // $handle = fopen('/tmp/email_to_'. $_email_addr. '.txt', 'w');
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

if ($_POST['action'] == 'get_members') {
    $order_by = 'members.joined_on desc';

    $employee = new Employee($_POST['id']);
    $branch = $employee->getBranch();

    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }

    $criteria = array(
        'columns' => "members.email_addr, members.phone_num, members.active, members.phone_num,
                      members.address, members.state, members.zip, countries.country, 
                      DATE_FORMAT(members.joined_on, '%e %b, %Y') AS formatted_joined_on, 
                      DATE_FORMAT(member_sessions.last_login, '%e %b, %Y') AS formatted_last_login, 
                      CONCAT(members.lastname, ', ', members.firstname) AS member, 
                      CONCAT(employees.lastname, ', ', employees.firstname) AS employee",
        'joins' => "member_sessions ON member_sessions.member = members.email_addr, 
                    employees ON employees.id = members.added_by, 
                    countries ON countries.country_code = members.country", 
        'match' => "employees.branch = ". $branch[0]['id'] ." AND 
                    members.email_addr <> 'initial@yellowelevator.com' AND 
                    members.email_addr NOT LIKE 'team%@yellowelevator.com'",
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

    foreach($result as $i=>$row) {
        $result[$i]['member'] = htmlspecialchars_decode($row['member']);
    }

    $response = array('members' => array('a_member' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_applications') {
    $order_by = 'referral_buffers.requested_on desc';
    $filter_by = "referral_buffers.referrer_email LIKE '%'";
    
    //$employee = new Employee($_POST['id']);
    //$branch = $employee->getBranch();

    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    if (isset($_POST['filter'])) {
        if ($_POST['filter'] == 'self_applied') {
            $filter_by = "referral_buffers.referrer_email LIKE 'team%@yellowelevator.com'";
        } else if ($_POST['filter'] == 'referred') {
            $filter_by = "referral_buffers.referrer_email NOT LIKE 'team%@yellowelevator.com'";
        } 
    }
    
    $criteria = array(
        'columns' => "referral_buffers.id, referral_buffers.candidate_email, 
                      referral_buffers.candidate_phone, referral_buffers.candidate_name, 
                      referral_buffers.referrer_email, referral_buffers.referrer_phone, 
                      referral_buffers.referrer_name, 
                      referral_buffers.existing_resume_id, referral_buffers.resume_file_hash, 
                      IF(referral_buffers.notes IS NULL OR referral_buffers.notes = '', 0, 1) AS has_notes,
                      IF(members.email_addr IS NULL, 0, 1) AS is_member,
                      DATE_FORMAT(referral_buffers.requested_on, '%e %b, %Y') AS formatted_requested_on", 
        'joins' => "members ON members.email_addr = referral_buffers.candidate_email",
        'match' => $filter_by, 
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

    foreach($result as $i=>$row) {
        $result[$i]['referrer_name'] = htmlspecialchars_decode(stripslashes($row['referrer_name']));
        $result[$i]['candidate_name'] = htmlspecialchars_decode(stripslashes($row['candidate_name']));
    }

    $response = array('applications' => array('application' => $result));
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
    $subject = "Employer Password Reset";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    // mail($member->getEmailAddress(), $subject, $message, $headers);
    
    $handle = fopen('/tmp/email_to_'. $member->getEmailAddress(). '.txt', 'w');
    fwrite($handle, 'Subject: '. $subject. "\n\n");
    fwrite($handle, $message);
    fclose($handle);
    
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
    $subject = "Employer Password Reset";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    // mail($member->getEmailAddress(), $subject, $message, $headers);
    
    $handle = fopen('/tmp/email_to_'. $member->getEmailAddress(). '.txt', 'w');
    fwrite($handle, 'Subject: '. $subject. "\n\n");
    fwrite($handle, $message);
    fclose($handle);
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_notes') {
    $referral_buffer = new ReferralBuffer($_POST['id']);
    $record = $referral_buffer->get();
    echo htmlspecialchars_decode(stripslashes($record[0]['notes']));
    exit();
}

if ($_POST['action'] == 'update_notes') {
    $data['notes'] = sanitize(stripslashes($_POST['notes']));

    $referral_buffer = new ReferralBuffer($_POST['id']);
    if ($referral_buffer->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'delete_application') {
    $referral_buffer = new ReferralBuffer($_POST['id']);
    if ($referral_buffer->delete() === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'sign_up') {
    // 1. get the buffer record
    $buffer = new ReferralBuffer($_POST['id']);
    $buffer_result = $buffer->get();
    
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
                                   $buffer_result[0]['referrer_phone']) === false) {
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
    if (isset($_POST['resolution'])) {
        if ($_POST['resolution'] == 'buffered') {
            if (create_member_from($buffer_result[0]['candidate_email'], 
                                   $buffer_result[0]['candidate_name'], 
                                   $buffer_result[0]['candidate_phone']) === false) {
                echo 'ko:member';
                exit();
            }
        }
    } else {
        if (create_member_from($buffer_result[0]['candidate_email'], 
                               $buffer_result[0]['candidate_name'], 
                               $buffer_result[0]['candidate_phone']) === false) {
            echo 'ko:member';
            exit();
        }
    }
    
    $existing_notes = htmlspecialchars_decode(stripslashes($member->getNotes()));
    $member->saveNotes($existing_notes. "\n\n". $buffer_result[0]['notes']);
    
    // 4. create connection
    $connection_is_success = true;
    if ($needs_to_be_connected && $referrer_successfully_created) {
        $connection_is_success = $referrer->addReferee($buffer_result[0]['candidate_email']);
    }
    
    // 5. move resume
    $resume_successfully_moved = false;
    if (!is_null($buffer_result[0]['existing_resume_id'])) {
        $resume_successfully_moved = true;
    } else {
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
        
        $original_file = $GLOBALS['buffered_resume_dir']. '/'. $_POST['id']. '.'. $buffer_result[0]['resume_file_hash'];
        if ($resume->copyFrom($original_file, $buffer_result[0]['resume_file_text']) === false) {
            echo 'ko:resume_copy';
            exit();
        }
        
        $resume_successfully_moved = true;
    }
    
    // 6. delete referralbuffer
    $buffer->delete();
    
    echo $buffer_result[0]['candidate_email'];
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
    $data = array();
    $data['requested_on'] = now();
    $data['referrer_email'] = $_POST['referrer_email'];
    if ($_POST['referrer_is_yel'] == '0') {
        $data['referrer_name'] = (empty($_POST['referrer_name']) ? "NULL" : $_POST['referrer_name']);
        $data['referrer_phone'] = (empty($_POST['referrer_phone']) ? "NULL" : $_POST['referrer_phone']);;
    }
    
    $data['candidate_email'] = (empty($_POST['candidate_email']) ? "NULL" : $_POST['candidate_email']);;
    $data['candidate_name'] = (empty($_POST['candidate_name']) ? "NULL" : $_POST['candidate_name']);;
    $data['candidate_phone'] = (empty($_POST['candidate_phone']) ? "NULL" : $_POST['candidate_phone']);;
    $data['notes'] = (empty($_POST['notes']) ? "NULL" : $_POST['notes']);;
    
    $buffer = new ReferralBuffer();
    if ($buffer->create($data) === false) {
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

if ($_POST['action'] == 'edit_referrer_phone') {
    $data = array();
    $data['referrer_phone'] = $_POST['phone'];
    
    $buffer = new ReferralBuffer($_POST['id']);
    if ($buffer->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'edit_referrer_email') {
    $data = array();
    $data['referrer_email'] = $_POST['email'];
    
    $buffer = new ReferralBuffer($_POST['id']);
    if ($buffer->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}
?>