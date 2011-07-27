<?php
require_once dirname(__FILE__). '/private/lib/utilities.php';

session_start();

function parse_candidates($_xml_str) {
    if (empty($_xml_str)) {
        return null;
    }
    
    $candidates = array();
    $dom = new XMLDOM();
    $xml_dom = $dom->load_from_xml($_xml_str);
    if (!empty($xml_dom)) {
        $tags = array('email_addr', 'phone_num', 'name', 'current_position', 'current_employer');
        $candidates = $dom->get_assoc($tags);
        
        foreach ($candidates as $i=>$candidate) {
            $candidates[$i]['name'] = sanitize(stripslashes($candidate['name']));
            $candidates[$i]['current_position'] = sanitize(stripslashes($candidate['current_position']));
            $candidates[$i]['current_employer'] = sanitize(stripslashes($candidate['current_employer']));
        }
    }
    
    return $candidates;
}

if (!isset($_POST['job_id'])) {
    redirect_to('welcome.php');
}

// 1. initialize the parameters
$referrer = array();
$referrer['email_addr'] = sanitize($_POST['referrer_email']);
$referrer['phone_num'] = sanitize($_POST['referrer_phone']);
$referrer['name'] = sanitize(stripslashes($_POST['referrer_name']));
$referrer['is_reveal_name'] = ($_POST['is_reveal_name'] == '1') ? '1' : '0';

$candidates = parse_candidates($_POST['payload']);

$job_id = $_POST['job_id'];
$job = new Job($job_id);

$today = now();
// 2. store the contacts
$data = array();
$data['requested_on'] = $today; 
$data['referrer_email'] = $referrer['email_addr'];
$data['referrer_phone'] = $referrer['phone_num'];
$data['referrer_name'] = $referrer['name'];
$data['is_reveal_name'] = $referrer['is_reveal_name'];

$via_social = 'NULL';
if (!$_POST['via_social'] == '') {
    $via_social = $_POST['via_social'];
}
$data['via_social_connection'] = $via_social;

// loop through the number of candidates
$has_error = false;
$error_candidates = array();
foreach ($candidates as $i=>$candidate) {
    $data['candidate_email'] = $candidate['email_addr'];
    $data['candidate_phone'] = $candidate['phone_num'];
    $data['candidate_name'] = $candidate['name'];
    $data['job'] = $job->getId();
    // $data['referrer_remarks'] = '<b>Current Position:</b><br/>'. $_POST['candidate_current_pos']. '<br/><br/><b>Current Employer:</b><br/>'. $_POST['candidate_current_emp']. '<br/><br/><b>Other Remarks:</b><br/>'. str_replace(array("\r\n", "\r", "\n"), '<br/>', $_POST['candidate_remarks']);
    $data['referrer_remarks'] = '<b>Current Position:</b><br/>'. $candidate['current_position']. '<br/><br/><b>Current Employer:</b><br/>'. $candidate['current_employer'];
    
    $referral_buffer = new ReferralBuffer();
    $buffer_id = $referral_buffer->create($data);
    if ($buffer_id === false) {
        $has_error = true;
        $error_candidates[] = array(
            'email_addr' => $candidate['email_addr'], 
            'name' => $candidate['name']
        );
    }
    
    // TODO: Send a yes/no email to each candidate. reveal the name of the referrer if is_reveal_name is set to 1
    
}

// TODO: handle error candidates
// redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/job/'. $job->getId(). '?error=1');
// exit();


// TODO: Send to team.my for reference


exit();
// 2. check any files to upload
$has_resume = 'NO';
$file_path = '';
$resume_text = '';
if (!empty($_FILES['candidate_resume']['name'])) {
    $type = $_FILES['candidate_resume']['type'];
    $size = $_FILES['candidate_resume']['size'];
    $name = $_FILES['candidate_resume']['name'];
    $temp = $_FILES['candidate_resume']['tmp_name'];
    
    if ($size <= $GLOBALS['resume_size_limit'] && $size > 0) {
        foreach ($GLOBALS['allowable_resume_types'] as $mime_type) {
            if ($type == $mime_type) {
                $hash = generate_random_string_of(6);
                $new_name = $buffer_id. ".". $hash;
                $file_path = $GLOBALS['buffered_resume_dir']. "/". $new_name;
                
                if (move_uploaded_file($temp, $file_path)) {
                    $data = array();
                    $data['resume_file_name'] = $name;
                    $data['resume_file_type'] = $type;
                    $data['resume_file_hash'] = $hash;
                    $data['resume_file_size'] = $size;
                    
                    if ($referral_buffer->update($data)) {
                        if ($type == 'application/msword') {
                            $data['needs_indexing'] = '1';
                            if ($referral_buffer->update($data) === true) {
                                $has_resume = 'YES';
                            } else {
                                @unlink($file_path);
                            }
                            break;
                        }
                        
                        switch ($type) {
                            case 'text/plain':
                                $tmp = file_get_contents($file_path);
                                $resume_text = sanitize($tmp);
                                break;
                            case 'text/html':
                                $tmp = file_get_contents($file_path);
                                $resume_text = sanitize(strip_tags($tmp));
                                break;
                            case 'application/pdf':
                                $cmd = "/usr/local/bin/pdftotext ". $file_path. " /tmp/". $new_name;
                                shell_exec($cmd);
                                $tmp = file_get_contents('/tmp/'. $new_name);
                                $resume_text = sanitize($tmp);
                                
                                if (!empty($tmp)) {
                                    unlink('/tmp/'. $new_name);
                                }
                                break;
                            case 'application/msword':
                                // $tmp = Resume::getTextFromMsword($file_path);
                                // if (empty($tmp)) {
                                //     $tmp = Resume::getTextFromRTF($file_path);
                                // }
                                // $resume_text = sanitize($tmp);
                                break;
                        }
                        
                        if (!empty($resume_text)) {
                            $keywords = preg_split("/[\s,]+/", $resume_text);
                            $resume_text = '';
                            foreach ($keywords as $i=>$keyword) {
                                $resume_text .= $keyword;
                                
                                if ($i < count($keywords)-1) {
                                    $resume_text .= ' ';
                                }
                            }
                            
                            $data['resume_file_text'] = sanitize(stripslashes($resume_text));
                            if ($referral_buffer->update($data) === true) {
                                $has_resume = 'YES';
                            } else {
                                @unlink($file_path);
                            }
                            break;
                        }
                    }
                }
            }
        }
    } else {
        redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/job/'. $job->getId(). '?error=2');
        exit();
    }
}

// 4 Send email to team.xx@yellowelevator.com
$country_code = strtolower($_SESSION['yel']['country_code']);
if (isset($_SESSION['yel']['member']['id']) && 
    !empty($_SESSION['yel']['member']['id'])) {
    $member = new Member($_SESSION['yel']['member']['id']);
    $country_code = strtolower($member->getCountry());
}
//$branch_email = 'team.'. $country_code. '@yellowelevator.com';
$branch_email = 'team.my@yellowelevator.com';

// get employer info
$criteria = array(
    'criteria' => "jobs.employer, employers.name AS employer_name", 
    'joins' => "employers ON employers.id = jobs.employer", 
    'match' => "jobs.id = ". $job->getId(), 
    'limit' => "1"
);

$result = $job->find($criteria);
$employer_id = $result[0]['employer'];
$employer_name = $result[0]['employer_name'];

$mail_lines = file(dirname(__FILE__). '/private/mail/new_referral.txt');
$message = '';
foreach ($mail_lines as $line) {
    $message .= $line;
}

$candidate_current_position = '(None provided)';
if (!empty($_POST['candidate_current_pos'])) {
    $candidate_current_position = htmlspecialchars_decode(stripslashes($_POST['candidate_current_pos']));
}

if (!empty($_POST['candidate_current_emp'])) {
    $candidate_current_position .= '('. htmlspecialchars_decode(stripslashes($_POST['candidate_current_emp'])). ')';
}

$message = str_replace('%employer_id%', $employer_id, $message);
$message = str_replace('%employer%', $employer_name, $message);
$message = str_replace('%referrer%', htmlspecialchars_decode(stripslashes($referrer['name'])), $message);
$message = str_replace('%candidate%', htmlspecialchars_decode(stripslashes($candidate['name'])), $message);
$message = str_replace('%candidate_phone%', $candidate['phone_num'], $message);
$message = str_replace('%referrer_email%', $referrer['email_addr'], $message);
$message = str_replace('%candidate_email%', $candidate['email_addr'], $message);
$message = str_replace('%candidate_current_position%', $candidate_current_position, $message);
$message = str_replace('%request_on%', $today, $message);
$message = str_replace('%job_title%', $job->getTitle(), $message);
$message = str_replace('%has_resume%', $has_resume, $message);

$subject = "New Referral for ". $job->getTitle(). " position";
$headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
mail($branch_email, $subject, $message, $headers);

// $handle = fopen('/tmp/email_to_'. $branch_email. '.txt', 'w');
// fwrite($handle, 'Subject: '. $subject. "\n\n");
// fwrite($handle, $message);
// fclose($handle);

redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/job/'. $job->getId(). '?success=1');
exit();
?>