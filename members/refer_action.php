<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id'])) {
    echo "ko";
    exit();
    //redirect_to('login.php');
}

$xml_dom = new XMLDOM();
$filter_by = '0';

if (isset($_POST['filter_by'])) {
    $filter_by = $_POST['filter_by'];
}

if ($_POST['action'] == 'get_networks') {
    $member = new Member($_POST['member'], $_SESSION['yel']['member']['sid']);
    $networks = $member->get_networks();
    $response = array('networks' => array('network' => $networks));
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_candidates') {
    $member = new Member($_POST['id'], $_SESSION['yel']['member']['sid']);
    $result = $member->get_referees("member_referees.referred_on DESC", $filter_by);
    foreach ($result as $key=>$row) {
        $result[$key]['networks'] = '';
    }

    $networks = $member->get_networks();
    $mysqli = Database::connect();
    foreach ($networks as $network) {
        $query = "SELECT referee FROM member_networks_referees WHERE network = ". $network['id'];
        $referees = $mysqli->query($query);
        foreach ($referees as $referee) {
            foreach ($result as $key=>$row) {
                if ($row['id'] == $referee['referee']) {
                    $result[$key]['networks'] .= $network['industry']. ';';
                }
            }
        }
    } 

    $response = array(
        'candidates' => array('candidate' => $result)
    );

    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_saved_jobs') {
    $job_filter_by = '0';
    if (isset($_POST['job_filter_by'])) {
        $job_filter_by = $_POST['job_filter_by'];
    }
    
    $member = new Member($_POST['id'], $_SESSION['yel']['member']['sid']);
    $result = $member->get_saved_jobs_with_filter($job_filter_by);
    $response = array(
        'saved_jobs' => array('saved_job' => $result)
    );

    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_job_description') {
    $criteria = array(
        'columns' => 'jobs.id, jobs.title, jobs.description, jobs.currency, industries.industry, 
                      jobs.potential_reward, countries.country, jobs.state, employers.name AS employer', 
        'joins' => 'industries ON industries.id = jobs.industry, 
                    countries ON countries.country_code = jobs.country, 
                    employers ON employers.id = jobs.employer', 
        'match' => 'jobs.id = '. $_POST['id'],
        'limit' => '1'
    );
    
    $job = Job::find($criteria);
    $job[0]['description'] = htmlspecialchars_decode($job[0]['description']);
    $job[0]['potential_reward'] = number_format($job[0]['potential_reward'], 2, '.', ', ');
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('job' => $job[0]));
    exit();
}

if ($_POST['action'] == 'make_referral') {
    $jobs = explode('|', $_POST['job']);
    
    // Cannot refer one self!!
    if (strtoupper($_POST['id']) == strtoupper($_POST['referee'])) {
        echo "ko";
        exit();
    }
    
    $from = $_POST['from'];
    $return = 'ok';
    $member = new Member($_POST['id'], $_SESSION['yel']['member']['sid']);
    
    $has_banks = false;
    $banks = $member->get_banks();
    if (count($banks) > 0 && !is_null($banks)) {
        $has_banks = true;
    }
    
    $tmp_jobs = array();
    $mysqli = Database::connect();
    foreach ($jobs as $key=>$value) {
        $query = "SELECT jobs.title, employers.name 
                  FROM jobs 
                  LEFT JOIN employers ON employers.id = jobs.employer 
                  WHERE jobs.id = ". $value. " LIMIT 1";
        $result = $mysqli->query($query);
        $tmp_jobs[$key]['id'] = $value;
        $tmp_jobs[$key]['job'] = 'Unknown Job';
        $tmp_jobs[$key]['employer'] = 'Unknown Employer';
        if (count($result) > 0 && !is_null($result)) {
            $tmp_jobs[$key]['job'] = $result[0]['title'];
            $tmp_jobs[$key]['employer'] = $result[0]['name'];
        }
    }
    $jobs = $tmp_jobs;
    
    if ($from == 'email') {
        $query = "SELECT COUNT(*) AS is_referee 
                  FROM member_referees 
                  WHERE member = '". $_POST['id']. "' AND
                  referee = '". $_POST['referee']. "'";
        $mysqli = Database::connect();
        $result = $mysqli->query($query);
        if ($result[0]['is_referee'] <= 0) {
            $query = "SELECT COUNT(*) AS is_member 
                      FROM members 
                      WHERE email_addr = '". $_POST['referee']. "'";
            $result = $mysqli->query($query);
            if ($result[0]['is_member'] >= 1) {
                // The given email is a member, but not in the member's candidates list.
                // - Will need to wait for approval before the referral can be viewed.
                if (!$member->create_referee($_POST['referee'])) {
                    echo "-900";
                    exit();
                }
                
                $return = '-2';
            } else {
                // Just create the invite and wait for the member to sign-up.
                $query = '';
                $i=0;
                foreach ($jobs as $job) {
                    $query .= "INSERT INTO member_invites SET 
                               referee_email = '". $_POST['referee']. "', 
                               member = '". $_POST['id']. "', 
                               invited_on = '". now(). "', 
                               referred_job = ". $job['id']. ", 
                               testimony = '". $_POST['testimony']. "'";
                    
                    if ($i < count($jobs)-1) {
                        $query .= "; ";
                    }
                    
                    $i++;
                }
                
                if (!$mysqli->transact($query)) {
                    echo "-901";
                    exit();
                }
                
                $lines = file(dirname(__FILE__). '/../private/mail/member_referred_new.txt');
                $message = '';
                foreach($lines as $line) {
                    $message .= $line;
                }
                
                $positions = '';
                $i = 0;
                foreach ($jobs as $job) {
                    $positions .= '- '. desanitize($job['job']). ' at '. desanitize($job['employer']);
                    
                    if ($i < count($jobs)-1) {
                        $positions .= "\n";
                    }
                    
                    $i++;
                }
                
                $message = str_replace('%member_name%', htmlspecialchars_decode(desanitize($member->get_name())), $message);
                $message = str_replace('%member_email_addr%', $member->id(), $message);
                $message = str_replace('%referee_email_addr%', $_POST['referee'], $message);
                $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
                $message = str_replace('%root%', $GLOBALS['root'], $message);
                $message = str_replace('%positions%', $positions, $message);
                $subject = "You Have Been Referred";
                $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
                mail($_POST['referee'], $subject, $message, $headers);
                
                echo '-3';
                exit();
            }
        }
    } 
    
    $referral_id = 0;
    $data = array();
    if (isset($_POST['referral_id'])) {
        $data['id'] = $_POST['referral_id'];
        $data['testimony'] = $_POST['testimony'];
        $data['member_confirmed_on'] = now();
        $data['member_rejected_on'] = 'NULL';
        $referral_id = $data['id'];
    } else {
        $data['member'] = $_POST['id'];
        $data['job'] = $jobs;
        $data['referred_on'] = now();
        $data['member_confirmed_on'] = $data['referred_on'];
        $data['member_rejected_on'] = 'NULL';
        $data['testimony'] = $_POST['testimony'];
        $data['referee'] = $_POST['referee'];
    }
    
    if (isset($_POST['request'])) {
        $data['job'] = $jobs[0]['id'];
        $data['resume'] = $_POST['resume'];
        $data['referee_acknowledged_on'] = $_POST['requested_on'];
        
        $query = "SELECT referrer_read_resume_on FROM referral_requests WHERE id = ". $_POST['request_id']. " LIMIT 1";
        $result = $mysqli->query($query);
        $data['member_read_resume_on'] = $result[0]['referrer_read_resume_on'];
        
        $referral_id = Referral::create($data);
        if ($referral_id === false || $referral_id <= 0) {
            echo 'ko';
            exit();
        } 
        
        if (!Referral::close_similar_referrals_with_id($referral_id)) {
            echo 'ko';
            exit();
        }
    } elseif (isset($_POST['referral_id'])) {
        if (Referral::update($data) !== false) {
            if (!Referral::close_similar_referrals_with_id($referral_id)) {
                echo 'ko';
                exit();
            }
        } else {
            echo 'ko';
            exit();
        }
    } else {
        if (!Referral::create_multiple($data)) {
            echo 'ko';
            exit();
        }
    }
    
    $positions = '';
    $i = 0;
    if (isset($_POST['request']) || isset($_POST['referral_id'])) {
        $positions = desanitize($jobs[0]['job']). ' at '. desanitize($jobs[0]['employer']);
    } else {
        foreach ($jobs as $job) {
            $positions .= '- '. desanitize($job['job']). ' at '. desanitize($job['employer']);

            if ($i < count($jobs)-1) {
                $positions .= "\n";
            }

            $i++;
        }
    }
    
    $lines = file(dirname(__FILE__). '/../private/mail/member_referred.txt');
    if ($from == 'email') {
        $lines = file(dirname(__FILE__). '/../private/mail/member_referred_approval.txt');
    }
    
    if (isset($_POST['request']) || isset($_POST['referral_id'])) {
        $lines = file(dirname(__FILE__). '/../private/mail/member_referred_acknowledged.txt');
    }
    
    $message = '';
    foreach($lines as $line) {
        $message .= $line;
    }
    
    if (isset($_POST['request'])) {
        $message = str_replace('%requested_on%', $data['referee_acknowledged_on'], $message);
    }
    
    $message = str_replace('%member_name%', htmlspecialchars_decode(desanitize($member->get_name())), $message);
    $message = str_replace('%member_email_addr%', $member->id(), $message);
    $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
    $message = str_replace('%root%', $GLOBALS['root'], $message);
    $message = str_replace('%positions%', $positions, $message);
    $subject = htmlspecialchars_decode(desanitize($member->get_name())). " has screened and submitted your resume for the ". htmlspecialchars_decode($job['job']). " position";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($_POST['referee'], $subject, $message, $headers);
    
    /*$handle = fopen('/tmp/ref_email_to_'. $_POST['referee']. '.txt', 'w');
    fwrite($handle, 'Subject: '. $subject. "\n\n");
    fwrite($handle, $message);
    fclose($handle);*/
    
    if (isset($_POST['request']) || isset($_POST['referral_id'])) {
        $query = "SELECT employers.like_instant_notification, employers.email_addr, 
                  employers.name AS employer_name, jobs.title AS job_title, 
                  jobs.contact_carbon_copy 
                  FROM referrals 
                  LEFT JOIN jobs ON jobs.id = referrals.job 
                  LEFT JOIN employers ON employers.id = jobs.employer 
                  WHERE referrals.id = ". $referral_id. " LIMIT 1";
        $result = $mysqli->query($query);
        if ($result[0]['like_instant_notification'] == '1') {
            $employer = $result[0]['employer_name'];
            $job = $result[0]['job_title'];

            $lines = file(dirname(__FILE__). '/../private/mail/employer_new_referral.txt');
            $message = '';
            foreach($lines as $line) {
                $message .= $line;
            }

            $message = str_replace('%company%', desanitize($employer), $message);
            $message = str_replace('%job%', desanitize($job), $message);
            $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
            $message = str_replace('%root%', $GLOBALS['root'], $message);
            $subject = "New application for ". desanitize($job). " position";
            $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
            if (!empty($result[0]['contact_carbon_copy']) && !is_null($result[0]['contact_carbon_copy'])) {
                $headers .= 'Cc: '. $result[0]['contact_carbon_copy']. "\n";
            }
            mail($result[0]['email_addr'], $subject, $message, $headers);

            /*$handle = fopen('/tmp/email_to_'. $result[0]['email_addr']. '.txt', 'w');
            fwrite($handle, 'Subject: '. $subject. "\n\n");
            fwrite($handle, 'Headers: '. $headers. "\n\n");
            fwrite($handle, $message);
            fclose($handle);*/
        }
    }
    echo $return;
    exit();
}

if ($_POST['action'] == 'has_banks') {
    $member = new Member($_POST['id'], $_SESSION['yel']['member']['sid']);
    $banks = $member->get_banks();
    if (is_null($banks) || count($banks) <= 0) {
        echo '0';
        exit();
    }
    
    echo '1';
    exit();
}

if ($_POST['action'] == 'referred_already') {
    echo (!Referral::already_referred($_POST['id'], $_POST['candidate'], $_POST['job'])) ? '0' : '1';
    exit();
}

?>
