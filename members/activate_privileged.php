<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/members/activtate.php?id='. $_GET['id']);
        exit();
    }
}

if (!isset($_GET['id'])) {
    echo 'Activation failed: No token.';
    exit();
} 

if (empty($_GET['id'])) {
    echo 'Activation failed: Token is empty.';
    exit();
}

$activation_id = $_GET['id'];
$query = "SELECT COUNT(*) AS awaits_activation 
          FROM member_activation_tokens 
          WHERE id = '". $activation_id. "' LIMIT 1";
$mysqli = Database::connect();
$result = $mysqli->query($query);
if ($result[0]['awaits_activation'] == '0') {
    echo 'Activation failed: Cannot find token.';
    exit();
}

$query = "SELECT member 
          FROM member_activation_tokens 
          WHERE id = '". $activation_id. "' LIMIT 1";
$result = $mysqli->query($query);
$email_addr = $result[0]['member'];

// Check whether member is non-privileged
$query = "SELECT recommender FROM members WHERE email_addr = '". $email_addr. "' LIMIT 1";
$result = $mysqli->query($query);
if (is_null($result) || empty($result)) {
    redirect_to('https://'. $GLOBALS['root']. '/members/activtate.php?id='. $_GET['id']);
    exit();
}

$member = new Member($email_addr);

$data = array();
$data['active'] = 'Y';

if (!$member->update($data)) {
    echo 'Activation failed: Cannot activate member.';
    exit();
}

$query = "DELETE FROM member_activation_tokens 
          WHERE id = '". $activation_id. "'";
$mysqli->execute($query);

$mail_lines = file('../private/mail/member_welcome.txt');
$message = '';
foreach ($mail_lines as $line) {
    $message .= $line;
}

$message = str_replace('%member_name%', $member->get_name(), $message);
$message = str_replace('%email_addr%', $member->id(), $message);
$message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
$message = str_replace('%root%', $GLOBALS['root'], $message);
$subject = "Welcome to YellowElevator.com";
$headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
mail($member->id(), $subject, $message, $headers);

// $handle = fopen('/tmp/email_to_'. $member->id(). '.txt', 'w');
// fwrite($handle, 'Subject: '. $subject. "\n\n");
// fwrite($handle, $message);
// fclose($handle);

// continue all bufferred referrals
$query = "SELECT * FROM privileged_referral_buffers WHERE referee = '". $member->id(). "'";
$result = $mysqli->query($query);
if (!empty($result)) {
    $referrals = $result;
    $query = '';
    foreach ($result as $row) {
        $query .= "INSERT INTO referrals SET 
                   `member` = '". $row['member']. "',
                   `referee` = '". $row['referee']. "',
                   `job` = ". $row['job']. ",
                   `resume` = ". $row['resume']. ",
                   `referred_on` = '". $row['referred_on']. "',
                   `referee_acknowledged_on` = '". $row['referee_acknowledged_on']. "',
                   `member_confirmed_on` = '". $row['member_confirmed_on']. "',
                   `member_read_resume_on` = '". $row['member_read_resume_on']. "',
                   `testimony` = '". addslashes($row['testimony']). "';";
    }
    
    if ($mysqli->transact($query)) {
        $query = "DELETE FROM privileged_referral_buffers WHERE referee = '". $member->id(). "'";
        $mysqli->execute($query);
        
        // send email to employers
        $query = "SELECT employers.email_addr, employers.name AS employer, 
                  jobs.id AS job_id, jobs.title AS job_title, 
                  jobs.contact_carbon_copy 
                  FROM jobs 
                  LEFT JOIN employers ON employers.id = jobs.employer 
                  WHERE jobs.closed = 'N' AND jobs.expire_on >= NOW() AND 
                  employers.like_instant_notification = 1 AND 
                  jobs.id IN (";
        foreach ($referrals as $i=>$referral) {
            if ($i == 0) {
                $query .= $referral['job'];
            } else {
                $query .= ", ". $referral['job'];
            }
        }
        $query .= ")";
        
        $result = $mysqli->query($query);
        if (!empty($result)) {
            $lines = file(dirname(__FILE__). '/../private/mail/employer_multiple_new_referrals.txt');
            $subject = "Multiple new application for multiple positions";
            
            $employers = array();
            foreach ($result as $row) {
                if (array_key_exists($row['email_addr'], $employers)) {
                    $employers[$row['email_addr']]['jobs'][$row['job_id']] = $row['job_title'];
                } else {
                    $employers[$row['email_addr']]['name'] = $row['employer'];
                    $employers[$row['email_addr']]['jobs'][$row['job_id']] = $row['job_title'];
                    if (!empty($row['contact_carbon_copy']) && !is_null($row['contact_carbon_copy'])) {
                        $employers[$row['email_addr']]['contact_carbon_copy'] = $row['contact_carbon_copy'];
                    }
                }
            }
            
            foreach ($employers as $email_addr=>$employer) {
                // gather the jobs
                $positions = '';
                $i = 0;
                foreach ($employer['jobs'] as $id=>$job_title) {
                    $positions .= '- ['. $id. '] '. $job_title;
                    
                    if ($i < count($employers['jobs'])-1) {
                        $positions .= "\n";
                    }
                    $i++;
                }
                
                // prepare and send email
                $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
                if (array_key_exists('contact_carbon_copy', $employer)) {
                    $headers .= 'Cc: '. $employer['contact_carbon_copy']. "\n";
                }
                $message = '';
                foreach($lines as $line) {
                    $message .= $line;
                }

                $message = str_replace('%company%', desanitize($employer['name']), $message);
                $message = str_replace('%positions%', desanitize($positions), $message);
                $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
                $message = str_replace('%root%', $GLOBALS['root'], $message);
                mail($email_addr, $subject, $message, $headers);
                
                // $handle = fopen('/tmp/email_to_'. $email_addr. '.txt', 'w');
                // fwrite($handle, 'Subject: '. $subject. "\n\n");
                // fwrite($handle, $message);
                // fclose($handle);
            }
        }
    }
}

redirect_to('login.php?signed_up=activated');
?>
