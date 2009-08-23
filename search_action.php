<?php
require_once dirname(__FILE__). "/private/lib/utilities.php";

session_start();

$xml_dom = new XMLDOM();

if (!isset($_POST['action'])) {
    $job_search = new JobSearch();

    $criteria = array();
    $criteria['order_by'] = 'relevance desc';
    $criteria['industry'] = 0;
    $criteria['employer'] = '';
    $criteria['country_code'] = $GLOBALS['default_country_code'];
    $criteria['limit'] = $GLOBALS['default_results_per_page'];
    $criteria['offset'] = 0;
    $criteria['keywords'] = $_POST['keywords'];
    
    $_SESSION['yel']['job_search']['criteria'] = array();
    $_SESSION['yel']['job_search']['criteria']['order_by'] = 'relevance desc';
    $_SESSION['yel']['job_search']['criteria']['industry'] = 0;
    $_SESSION['yel']['job_search']['criteria']['employer'] = '';
    $_SESSION['yel']['job_search']['criteria']['country_code'] = $GLOBALS['default_country_code'];
    $_SESSION['yel']['job_search']['criteria']['limit'] = $GLOBALS['default_results_per_page'];
    $_SESSION['yel']['job_search']['criteria']['offset'] = 0;
    $_SESSION['yel']['job_search']['criteria']['keywords'] = $_POST['keywords'];

    if (isset($_POST['order_by'])) {
        $criteria['order_by'] = $_POST['order_by'];
        $_SESSION['yel']['job_search']['criteria']['order_by'] = $_POST['order_by'];
    }

    if (isset($_POST['industry'])) {
        $criteria['industry'] = $_POST['industry'];
        $_SESSION['yel']['job_search']['criteria']['industry'] = $_POST['industry'];
    }
    
    if (isset($_POST['employer'])) {
        $criteria['employer'] = $_POST['employer'];
        $_SESSION['yel']['job_search']['criteria']['employer'] = $_POST['employer'];
    }
    
    if (isset($_POST['country_code'])) {
        $criteria['country_code'] = $_POST['country_code'];
        $_SESSION['yel']['job_search']['criteria']['country_code'] = $_POST['country_code'];
    }

    if (isset($_POST['limit'])) {
        $criteria['limit'] = $_POST['limit'];
        $_SESSION['yel']['job_search']['criteria']['limit'] = $_POST['limit'];
    }

    if (isset($_POST['offset'])) {
        $criteria['offset'] = $_POST['offset'];
        $_SESSION['yel']['job_search']['criteria']['offset'] = $_POST['offset'];
    }
    
    $result = $job_search->search_using($criteria);
    if ($result == 0) {
        echo "0";
        exit();
    }

    if (!$result) {
        echo "ko";
        exit();
    }
    
    $total_results = $job_search->total_results();
    $current_page = '1';
    if ($criteria['offset'] > 0) {
        $current_page = ceil($criteria['offset'] / $criteria['limit']) + 1;
    }
    
    $result[0]['changed_country_code'] = 0;
    if ($job_search->country_code_changed()) {
        $result[0]['changed_country_code'] = 1;
    } 
    
    foreach($result as $i=>$row) {
        $result[$i]['description'] = htmlspecialchars_decode($row['description']);
        $result[$i]['title'] = htmlspecialchars_decode($row['title']);
        $result[$i]['salary'] = number_format($row['salary'], 2, '.', ', ');
        $result[$i]['salary_end'] = number_format($row['salary_end'], 2, '.', ', ');
        $result[$i]['potential_reward'] = number_format($row['potential_reward'], 2, '.', ', ');
        $result[$i]['total_results'] = $total_results;
        $result[$i]['current_page'] = $current_page;
    }

    $response = array('results' => array('result' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_job_info') {
    $criteria = array(
        'columns' => 'jobs.*, currencies.symbol AS currency_symbol, industries.industry AS full_industry, 
                      countries.country AS country_name, employers.name AS employer_name, 
                      DATE_FORMAT(jobs.created_on, \'%e %b, %Y %k:%i:%s\') AS formatted_created_on, 
                      DATE_FORMAT(jobs.expire_on, \'%e %b, %Y %k:%i:%s\') AS formatted_expire_on, 
                      DATEDIFF(NOW(), jobs.expire_on) AS expired',
        'joins' => 'industries ON industries.id = jobs.industry, 
                    countries ON countries.country_code = jobs.country, 
                    employers ON employers.id = jobs.employer, 
                    currencies ON currencies.country_code = employers.country', 
        'match' => 'jobs.id = \''. $_POST['id']. '\''
    );

    $jobs = Job::find($criteria);
    $job = array();

    foreach ($jobs[0] as $key => $value) {
        $job[$key] = $value;
    }

    $job['description'] = htmlspecialchars_decode($job['description']);
    $job['potential_reward'] = number_format($job['potential_reward'], 2, '. ', ', ');
    $job['salary'] = number_format($job['salary'], 2, '. ', ', ');
    $job['salary_end'] = number_format($job['salary_end'], 2, '. ', ', ');
    $job['state'] = ucwords($job['state']);
    
    $response =  array('job' => $job);

    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'save_job_to_bin') {
    $mysqli = Database::connect();
    $query = "SELECT COUNT(*) AS saved_before FROM member_saved_jobs 
              WHERE member = '". $_POST['member']. "' AND job = ". $_POST['id'];
    $result = $mysqli->query($query);
    if ($result[0]['saved_before'] >= 1) {
        echo "-1";
        exit();
    }
    
    $query = "INSERT INTO member_saved_jobs SET 
              member = '". $_POST['member']. "', 
              job = ". $_POST['id']. ", 
              saved_on = '". now(). "'";
    if (!$mysqli->execute($query)) {
        echo "ko";
        exit();
    }
    
    echo "ok";
    exit();
}

if ($_POST['action'] == 'get_candidates') {
    $member = new Member($_POST['id'], $_SESSION['yel']['member']['sid']);
    $result = $member->get_referees("referee_name ASC", $_POST['filter_by']);
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

if ($_POST['action'] == 'make_referral') {
    // Cannot refer one self!!
    if (strtoupper($_POST['id']) == strtoupper($_POST['referee'])) {
        echo "ko";
        exit();
    }
    
    $_POST['testimony'] = sanitize($_POST['testimony']);
    $from = $_POST['from'];
    $return = 'ok';
    $member = new Member($_POST['id'], $_SESSION['yel']['member']['sid']);
    $query = "SELECT jobs.title, employers.name 
              FROM jobs 
              LEFT JOIN employers ON employers.id = jobs.employer 
              WHERE jobs.id = ". $_POST['job']. " LIMIT 1";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    $job = 'Unknown Job';
    $employer = 'Unknown Employer';
    if (count($result) > 0 && !is_null($result)) {
        $job = $result[0]['title'];
        $employer = $result[0]['name'];
    }
    
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
                $query = "INSERT INTO member_invites SET 
                          referee_email = '". $_POST['referee']. "', 
                          member = '". $_POST['id']. "', 
                          invited_on = '". now(). "', 
                          referred_job = ". $_POST['job']. ", 
                          testimony = '". $_POST['testimony']. "'";
                if (!$mysqli->execute($query)) {
                    echo "-901";
                    exit();
                }
                
                $lines = file(dirname(__FILE__). '/private/mail/member_referred_new.txt');
                $message = '';
                foreach($lines as $line) {
                    $message .= $line;
                }
                
                $position = '- '. desanitize($job). ' by '. desanitize($employer);
                $message = str_replace('%member_name%', $member->get_name(), $message);
                $message = str_replace('%member_email_addr%', $member->id(), $message);
                $message = str_replace('%referee_email_addr%', $_POST['referee'], $message);
                $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
                $message = str_replace('%root%', $GLOBALS['root'], $message);
                $message = str_replace('%positions%', $position, $message);
                $subject = "You Have Been Referred";
                $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
                mail($_POST['referee'], $subject, $message, $headers);
                
                echo '-3';
                exit();
            }
        }
    } 
    
    $data = array();
    $data['member'] = $_POST['id'];
    $data['job'] = $_POST['job'];
    $data['referred_on'] = now();
    $data['testimony'] = $_POST['testimony'];
    $data['referee'] = $_POST['referee'];
    
    
    if (!Referral::create($data)) {
        echo 'ko';
        exit();
    }
    
    $lines = file(dirname(__FILE__). '/private/mail/member_referred.txt');
    if ($from == 'email') {
        $lines = file(dirname(__FILE__). '/private/mail/member_referred_approval.txt');
    }
    $message = '';
    foreach($lines as $line) {
        $message .= $line;
    }
    
    $position = '- '. desanitize($job). ' at '. desanitize($employer);
    $message = str_replace('%member_name%', $member->get_name(), $message);
    $message = str_replace('%member_email_addr%', $member->id(), $message);
    $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
    $message = str_replace('%root%', $GLOBALS['root'], $message);
    $message = str_replace('%positions%', $position, $message);
    $subject = desanitize($member->get_name()). " referred you to a job!";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($_POST['referee'], $subject, $message, $headers);
    
    /*$handle = fopen('/tmp/email_to_'. $_POST['referee']. '.txt', 'w');
    fwrite($handle, 'Subject: '. $subject. "\n\n");
    fwrite($handle, $message);
    fclose($handle);*/
    
    echo $return;
    exit();
}

if ($_POST['action'] == 'has_banks') {
    $member = new Member($_POST['id'], $_SESSION['yel']['member']['sid']);
    $banks = $member->get_banks();
    if (count($banks) <= 0 || is_null($banks)) {
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

if ($_POST['action'] == 'refer_me') {
    $member = new Member($_POST['id'], $_SESSION['yel']['member']['sid']);
    $mysqli = Database::connect();
    
    $job_title = '';
    $employer_name = '';

    $query = "SELECT jobs.title, employers.name, jobs.acceptable_resume_type 
              FROM jobs 
              LEFT JOIN employers ON employers.id = jobs.employer 
              WHERE jobs.id = ". $_POST['job']. " LIMIT 1";
    $result = $mysqli->query($query);
    $acceptable_resume_type = $result[0]['acceptable_resume_type'];
    $job_title = $result[0]['title'];
    $employer_name = $result[0]['name'];

    $query = "SELECT file_hash FROM resumes WHERE id = ". $_POST['resume']. " LIMIT 1";
    $result = $mysqli->query($query);
    $file_hash = $result[0]['file_hash'];

    if ($acceptable_resume_type != 'A') {
        if ($acceptable_resume_type == 'O' && !is_null($file_hash)) {
            // online resume only
            echo '-1';
            exit();
        } else if ($acceptable_resume_type == 'F' && is_null($file_hash)) {
            // uploaded resume only
            echo '-2';
            exit();
        }
    }
    
    $referrers = explode('|', $_POST['referrer']);
    $data = array();
    $data['member'] = $_POST['id'];
    $data['job'] = $_POST['job'];
    $data['resume'] = $_POST['resume'];
    $data['requested_on'] = now();
    
    if ($_POST['from'] == 'contacts') {
        $tmp_referrers = array();
        foreach ($referrers as $key=>$value) {
            $query = "SELECT CONCAT(lastname, ', ', firstname) AS fullname 
                      FROM members 
                      WHERE email_addr = '". $value. "' LIMIT 1";
            $result = $mysqli->query($query);
            $tmp_referrers[$key]['id'] = $value;
            $tmp_referrers[$key]['fullname'] = 'Unknown';
            if (count($result) > 0 && !is_null($result)) {
                $tmp_referrers[$key]['fullname'] = $result[0]['fullname'];
            }
        }
        $referrers = $tmp_referrers;
        $data['referrer'] = $referrers;

        if (ReferralRequests::create_multiple($data)) {
            $lines = file(dirname(__FILE__). '/private/mail/candidate_referral_request.txt');
            $raw_message = '';
            foreach($lines as $line) {
                $raw_message .= $line;
            }

            $raw_message = str_replace('%member_name%', htmlspecialchars_decode(desanitize($member->get_name())), $raw_message);
            $raw_message = str_replace('%member_email_addr%', $member->id(), $raw_message);
            $raw_message = str_replace('%protocol%', $GLOBALS['protocol'], $raw_message);
            $raw_message = str_replace('%root%', $GLOBALS['root'], $raw_message);
            $raw_message = str_replace('%job%', desanitize($job_title), $raw_message);
            $raw_message = str_replace('%employer%', htmlspecialchars_decode(desanitize($employer_name)), $raw_message);
            $subject = htmlspecialchars_decode(desanitize($member->get_name())). " requested for your referral!";
            $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";

            foreach ($referrers as $referrer) {
                $message = str_replace('%referrer_name%', htmlspecialchars_decode(desanitize($referrer['fullname'])), $raw_message);

                mail($referrer['id'], $subject, $message, $headers);

                /*$handle = fopen('/tmp/email_to_'. $referrer['id']. '.txt', 'w');
                fwrite($handle, 'Subject: '. $subject. "\n\n");
                fwrite($handle, $message);
                fclose($handle);*/
            }
        } else {
            echo 'ko';
            exit();
        }
    }
    
    if ($_POST['from'] == 'others' || $_POST['from'] == 'yel') {
        $has_errors = array();
        
        foreach ($referrers as $referrer) {
            $data['referrer'] = $referrer;
            $cannot_add_referee = false;
            
            $query = "SELECT COUNT(*) AS is_referee 
                      FROM member_referees 
                      WHERE member = '". $_POST['id']. "' AND
                      referee = '". $referrer. "'";
            $mysqli = Database::connect();
            $result = $mysqli->query($query);
            if ($result[0]['is_referee'] <= 0) {
                $query = "SELECT COUNT(*) AS is_member 
                          FROM members 
                          WHERE email_addr = '". $referrer. "'";
                $result = $mysqli->query($query);
                if ($result[0]['is_member'] >= 1) {
                    // The given email is a member, but not in the member's candidates list.
                    // - Will need to wait for approval before the request can be viewed.
                    if (!$member->create_referee($referrer)) {
                        $has_errors['create_referee'] = true;
                    }
                } else {
                    // Just create the invite and wait for the member to sign-up.
                    $query = "INSERT INTO referrer_invites SET 
                              referrer_email = '". $referrer. "', 
                              member = '". $member->id(). "', 
                              invited_on = '". $data['requested_on']. "', 
                              requested_job = ". $data['job']. ", 
                              resume = ". $data['resume'];
                    
                    if (!$mysqli->execute($query)) {
                        $has_errors['referrer_invites'] = true;
                        $cannot_add_referee = true;
                    } else {
                        $lines = file(dirname(__FILE__). '/private/mail/member_requested_new.txt');
                        $message = '';
                        foreach($lines as $line) {
                            $message .= $line;
                        }

                        $position = desanitize($job_title). ' at '. desanitize($employer_name);

                        $message = str_replace('%member_name%', htmlspecialchars_decode(desanitize($member->get_name())), $message);
                        $message = str_replace('%member_email_addr%', $member->id(), $message);
                        $message = str_replace('%referrer_email_addr%', $referrer, $message);
                        $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
                        $message = str_replace('%root%', $GLOBALS['root'], $message);
                        $message = str_replace('%position%', $position, $message);
                        $subject = "Your Reference Requested";
                        $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
                        mail($referrer, $subject, $message, $headers);

                        /*$handle = fopen('/tmp/email_to_'. $referrer. '.txt', 'w');
                        fwrite($handle, 'Subject: '. $subject. "\n\n");
                        fwrite($handle, $message);
                        fclose($handle);*/
                    }
                    
                    continue;
                }
            } 
            
            // For existing members, add the request.
            if (!$cannot_add_referee) {
                $query = "SELECT CONCAT(lastname, ', ', firstname) AS fullname 
                          FROM members 
                          WHERE email_addr = '". $referrer. "' LIMIT 1";
                $result = $mysqli->query($query);
                $referrer_name = $result[0]['fullname'];

                if (ReferralRequests::create($data)) {
                    if ($_POST['from'] == 'others') {
                        $lines = file(dirname(__FILE__). '/private/mail/candidate_referral_request.txt');
                        $raw_message = '';
                        foreach($lines as $line) {
                            $raw_message .= $line;
                        }

                        $raw_message = str_replace('%member_name%', htmlspecialchars_decode(desanitize($member->get_name())), $raw_message);
                        $raw_message = str_replace('%member_email_addr%', $member->id(), $raw_message);
                        $raw_message = str_replace('%protocol%', $GLOBALS['protocol'], $raw_message);
                        $raw_message = str_replace('%root%', $GLOBALS['root'], $raw_message);
                        $raw_message = str_replace('%job%', desanitize($job_title), $raw_message);
                        $raw_message = str_replace('%employer%', htmlspecialchars_decode(desanitize($employer_name)), $raw_message);
                        $subject = htmlspecialchars_decode(desanitize($member->get_name())). " needs to be referred to a job!";
                        $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";

                        foreach ($referrers as $referrer) {
                            $message = str_replace('%referrer_name%', htmlspecialchars_decode(desanitize($referrer_name)), $raw_message);

                            mail($referrer, $subject, $message, $headers);

                            /*$handle = fopen('/tmp/email_to_'. $referrer. '.txt', 'w');
                            fwrite($handle, 'Subject: '. $subject. "\n\n");
                            fwrite($handle, $message);
                            fclose($handle);*/
                        }
                    } else if ($_POST['from'] == 'yel') {
                        $lines = file(dirname(__FILE__). '/private/mail/candidate_refer_request.txt');
                        $message = '';
                        foreach($lines as $line) {
                            $message .= $line;
                        }

                        $message = str_replace('%member_name%', htmlspecialchars_decode(desanitize($member->get_name())), $message);
                        $message = str_replace('%member_email_addr%', $member->id(), $message);
                        $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
                        $message = str_replace('%root%', $GLOBALS['root'], $message);
                        $message = str_replace('%job%', desanitize($job_title), $message);
                        $message = str_replace('%employer%', htmlspecialchars_decode(desanitize($employer_name)), $message);
                        $subject = htmlspecialchars_decode(desanitize($member->get_name())). " needs to be referred to a job!";
                        $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
                        mail('initial@yellowelevator.com', $subject, $message, $headers);

                        /*$handle = fopen('/tmp/email_to_initial.txt', 'w');
                        fwrite($handle, 'Subject: '. $subject. "\n\n");
                        fwrite($handle, $message);
                        fclose($handle);*/
                    }
                    
                } else {
                    $has_errors['referral_requests_create'] = true;
                }
            }
        }
    }
    
    echo (count($has_errors) > 0) ? 'ko' : 'ok';
    exit();
}
?>
