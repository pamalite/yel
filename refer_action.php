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
        $tags = array('email_addr', 'phone_num', 'name', 'social', 'current_position', 'current_employer');
        $candidates = $dom->get_assoc($tags);
        
        foreach ($candidates as $i=>$candidate) {
            $candidates[$i]['name'] = sanitize(stripslashes($candidate['name']));
            $candidates[$i]['current_position'] = sanitize(stripslashes($candidate['current_position']));
            $candidates[$i]['current_employer'] = sanitize(stripslashes($candidate['current_employer']));
            
            if (is_null($candidate['social']) || empty($candidate['social'])) {
                $candidates[$i]['social'] = 'NULL';
            } else {
                $candidates[$i]['social'] = strtolower($candidate['social']);
            }
        }
    }
    
    return $candidates;
}

if (!isset($_POST['job_id'])) {
    redirect_to('welcome.php');
}

if (isset($_POST['headhunter_id'])) {
    // headhunter referral
    // create
    $data = array();
    $data['member'] = $_POST['headhunter_id'];
    $data['job'] = $_POST['job_id'];
    $data['referred_on'] = date('Y-m-d H:i:s');
    $data['cover_note'] = $_POST['candidate_cover_note'];
    
    $referral = new HeadhunterReferral();
    if ($referral->create($data) === false) {
        redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/job/'. $_POST['job_id']. '?error=1');
        exit();
    }
    
    // upload file
    $data = array();
    $data['FILE'] = array();
    $data['FILE']['type'] = $_FILES['candidate_resume']['type'];
    $data['FILE']['size'] = $_FILES['candidate_resume']['size'];
    $data['FILE']['name'] = str_replace(array('\'', '"', '\\'), '', basename($_FILES['candidate_resume']['name']));
    $data['FILE']['tmp_name'] = $_FILES['candidate_resume']['tmp_name'];
    
    if ($referral->uploadFile($data) === false) {
        redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/job/'. $_POST['job_id']. '?error=1');
        exit();
    }
    
    // send email to employer
    $criteria = array(
        'columns' => "jobs.title, jobs.contact_carbon_copy, jobs.employer AS employer_id, 
                      employers.email_addr, employers.name AS employer",
        'joins' => "employers ON employers.id = jobs.employer",
        'match' => "jobs.id = ". $_POST['job_id'],
        'limit' => "1"
    );
    $job = new Job();
    $result = $job->find($criteria);
    $job_name = $result[0]['title'];
    $employer = array(
       'email_addr' => $result[0]['email_addr'],
       'name' => $result[0]['employer'],
       'id' => $result[0]['employer_id'],
       'cc' => $result[0]['contact_carbon_copy']
    );
    
    $mail_lines = file('private/mail/employer_headhunter_referral.txt');
    $message = '';
    foreach ($mail_lines as $line) {
        $message .= $line;
    }
    
    $message = str_replace('%job%', htmlspecialchars_decode(stripslashes($job_name)), $message);
    $message = str_replace('%referred_on%', date('M j, Y'), $message);
    
    if (!empty($_POST['candidate_cover_note'])) {
        $cover_note = '<br/>Also, the consultant has provided the following cover note for this candidate:<br/>';
        $cover_note .= '<br/>'. str_replace(array("\r\n", "\r", "\n"), '<br/>', $_POST['candidate_cover_note']). '<br/>';
        $message = str_replace('%message%', $cover_note, $message);
    }
    
    $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
    $message = str_replace('%root%', $GLOBALS['root'], $message);
    $message = str_replace('%referral_id%', $referral->getId(), $message);
    
    $file_info = $referral->getFileInfo();
    $file = $GLOBALS['headhunter_resume_dir']. '/'. $referral->getId(). '.'. $file_info['file_hash'];
    $attached_filename = $file_info['file_name'];
    $attachment = chunk_split(base64_encode(file_get_contents($file)));
    $subject = 'Potential Candidate for the '. $job_name. ' position';
    
    $headers = 'From: admin@yellowelevator.com'. "\n";
    // $headers .= 'Cc: '. $yel_email. "\n";
    $headers .= 'MIME-Version: 1.0'. "\n";
    $headers .= 'Content-Type: multipart/mixed; boundary="yel_mail_sep_'. $attached_filename. '";'. "\n\n";
    
    $body = '--yel_mail_sep_'. $attached_filename. "\n";
    $body .= 'Content-Type: multipart/alternative; boundary="yel_mail_sep_alt_'. $attached_filename. '"'. "\n";
    $body .= '--yel_mail_sep_alt_'. $attached_filename. "\n";
    $body .= 'Content-Type: text/html; charset="iso-8859-1"'. "\n";
    $body .= 'Content-Transfer-Encoding: 7bit"'. "\n";
    
    $body .= $message. "\n";
    $body .= '--yel_mail_sep_alt_'. $attached_filename. "--\n\n";
    $body .= '--yel_mail_sep_'. $attached_filename. "\n";
    $body .= 'Content-Type: '. $file_info['file_type']. '; name="'. $attached_filename. '"'. "\n";
    $body .= 'Content-Transfer-Encoding: base64'. "\n";
    $body .= 'Content-Disposition: attachment'. "\n";
    $body .= $attachment. "\n";
    $body .= '--yel_mail_sep_'. $attached_filename. "--\n\n";
    
    $employer_emails = $employer['email_addr'];
    if (!empty($employer['cc']) && !is_null($employer['cc'])) {
        $hr_contacts = explode(',', $employer['cc']);
        foreach ($hr_contacts as $i => $hr_contact) {
            $hr_contacts[$i] = trim($hr_contact);
        }
        
        $employer_emails = implode(',', $hr_contacts);
    }
    mail($employer_emails, $subject, $body, $headers);
    
    // $handle = fopen('/tmp/email_to_'. $employer_emails. '.txt', 'w');
    // fwrite($handle, 'To: '. $employer_emails. "\n\n");
    // fwrite($handle, 'Header: '. $headers. "\n\n");
    // fwrite($handle, 'Subject: '. $subject. "\n\n");
    // fwrite($handle, $body);
    // fclose($handle);
    
    redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/job/'. $_POST['job_id']. '?success=2');
    exit();
}

// normal referral
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

// loop through the number of candidates
$has_error = false;
$error_candidates = array();
foreach ($candidates as $i=>$candidate) {
    $data['candidate_email'] = $candidate['email_addr'];
    $data['candidate_phone'] = $candidate['phone_num'];
    $data['candidate_name'] = $candidate['name'];
    $data['job'] = $job->getId();
    $data['via_social_connection'] = $candidate['social'];
    $data['current_position'] = $candidate['current_position'];
    $data['current_employer'] = $candidate['current_employer'];
    $data['referrer_remarks'] = 'NULL';
    
    $referral_buffer = new ReferralBuffer();
    $buffer_id = $referral_buffer->create($data);
    if ($buffer_id === false) {
        $has_error = true;
        $error_candidates[] = array(
            'email_addr' => $candidate['email_addr'], 
            'name' => $candidate['name']
        );
        continue;
    }
    
    // Send a yes/no email to each candidate. reveal the name of the referrer if is_reveal_name is set to 1
    $mail_lines = file(dirname(__FILE__). '/private/mail/new_referral_confirm.txt');
    $message = '';
    foreach ($mail_lines as $line) {
        $message .= $line;
    }
    
    $criteria = array(
        'columns' => 'jobs.*, industries.industry AS full_industry, 
                      employers.name AS employer_name, branches.currency',
        'joins' => 'industries ON industries.id = jobs.industry, 
                    countries ON countries.country_code = jobs.country, 
                    employers ON employers.id = jobs.employer, 
                    employees ON employees.id = employers.registered_by, 
                    branches ON branches.id = employees.branch', 
        'match' => "jobs.id = ". $job->getId(), 
        'limit' => "1"
    );
    $job_result = $job->find($criteria);
    
    $message = str_replace('%requested_on%', date('M j, Y'), $message);
    $message = str_replace('%job_title%', $job->getTitle(), $message);
    $message = str_replace('%job_id%', $job->getId(), $message);
    $message = str_replace('%employer%', $job_result[0]['employer_name'], $message);
    $message = str_replace('%buffer_id%', $buffer_id, $message);
    $message = str_replace('%candidate_name%', htmlspecialchars_decode(stripslashes($candidate['name'])), $message);
    $message = str_replace('%industry%', $job_result[0]['full_industry'], $message);
    
    $currency = $job_result[0]['currency'];
    $salary_range = $currency. ' $'. number_format($job_result[0]['salary']);
    if (!is_null($job_result[0]['salary_end']) && $job_result[0]['salary_end'] > 0) {
        $salary_range .= ' - '. number_format($job_result[0]['salary_end']);
    }
    $message = str_replace('%salary_range%', $salary_range, $message);
    
    $message = str_replace('%reward%', $currency. ' $'. number_format($job_result[0]['potential_reward']), $message);
    
    $total_potential_reward = $job_result[0]['potential_reward'];
    $potential_token_reward = $total_potential_reward * 0.05;
    $potential_reward = $total_potential_reward - $potential_token_reward;
    $message = str_replace('%bonus%', $currency. ' $'. number_format($potential_token_reward), $message);
    
    $job_desc = str_replace(array("\n", "\r", "\r\n"), '<br/>', $job_result[0]['description']);
    $message = str_replace('%job_desc%', htmlspecialchars_decode(stripslashes($job_desc)), $message);
    
    $referrer_name = 'A friend of yours';
    $referrer_name_subject = $referrer_name;
    if ($referrer['is_reveal_name'] == '1') {
        $referrer_name = htmlspecialchars_decode(stripslashes($referrer['name']));
        $referrer_name_subject = $referrer_name;
        $referrer_name .= ' ('. $referrer['email_addr']. ')';
    }
    $message = str_replace('%referrer_name%', $referrer_name, $message);
    $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
    $message = str_replace('%root%', $GLOBALS['root'], $message);
    $subject = $referrer_name_subject. " recommended you for the ". $job->getTitle(). " position";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    $headers .= 'Reply-To: '. $referrer['email_addr']. "\n";
    $headers .= 'MIME-Version: 1.0'. "\n";
    $headers .= 'Content-Type: text/html; charset="iso-8859-1"'. "\n";
    mail($candidate['email_addr'], $subject, $message, $headers);
    
    // $handle = fopen('/tmp/email_to_'. $candidate['email_addr']. '.txt', 'w');
    // fwrite($handle, 'Subject: '. $subject. "\n\n");
    // fwrite($handle, $message);
    // fclose($handle);
    
    // Send email to team.xx@yellowelevator.com
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

    $candidate_current_position = htmlspecialchars_decode(stripslashes($candidate['current_position']));
    $candidate_current_position .= '('. htmlspecialchars_decode(stripslashes($candidate['current_employer'])). ')';

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

    $subject = "New Referral for ". $job->getTitle(). " position";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($branch_email, $subject, $message, $headers);

    // $handle = fopen('/tmp/email_to_'. $branch_email. '.txt', 'w');
    // fwrite($handle, 'Subject: '. $subject. "\n\n");
    // fwrite($handle, $message);
    // fclose($handle);
}

// handle error candidates
if ($has_error) {
    $payload = '';
    $i = 0;
    foreach ($error_candidates as $error_candidate) {
        $payload .= $error_candidate['email_addr'];
        
        if ($i < count($error_candidates)-1) {
            $payload .= ',';
        }
    }
    
    echo $payload;
    exit();
}

echo 'ok';
exit();
?>