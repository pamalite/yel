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
    $data['referrer_remarks'] = '<b>Current Position:</b><br/>'. $candidate['current_position']. '<br/><br/><b>Current Employer:</b><br/>'. $candidate['current_employer'];
    
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
        'columns' => "employers.name", 
        'joins' => "employers ON employers.id = jobs.employer", 
        'match' => "jobs.id = ". $job->getId(), 
        'limit' => "1"
    );
    $job_result = $job->find($criteria);
    
    $message = str_replace('%requested_on%', date('M j, Y'), $message);
    $message = str_replace('%job_title%', $job->getTitle(), $message);
    $message = str_replace('%job_id%', $job->getId(), $message);
    $message = str_replace('%employer%', $job_result[0]['name'], $message);
    $message = str_replace('%buffer_id%', $buffer_id, $message);
    $message = str_replace('%candidate_name%', htmlspecialchars_decode(stripslashes($candidate['name'])), $message);
    $referrer_name = 'A friend of yours';
    if ($referrer['is_reveal_name'] == '1') {
        $referrer_name = htmlspecialchars_decode(stripslashes($referrer['name']));
        $referrer_name .= ' ('. $referrer['email_addr']. ')';
    }
    $message = str_replace('%referrer_name%', $referrer_name, $message);
    $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
    $message = str_replace('%root%', $GLOBALS['root'], $message);
    $subject = $referrer_name. " recommended you for the ". $job->getTitle(). " position";
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