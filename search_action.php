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
    $raw_referees = explode('|', $_POST['referee']);
    $referees = array();
    foreach ($raw_referees as $i=>$raw_referee) {
        $referees[$i]['email_addr'] = $raw_referee;
        $referees[$i]['is_contact'] = true;
        $referees[$i]['is_member'] = true;
    }
    
    // Cannot refer one self!!
    if (strtoupper($_POST['id']) == strtoupper($_POST['referee'])) {
        echo "ko";
        exit();
    }
    
    //$_POST['testimony'] = sanitize($_POST['testimony']);
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
        foreach ($referees as $i=>$referee) {
            $query = "SELECT COUNT(*) AS is_referee 
                      FROM member_referees 
                      WHERE member = '". $_POST['id']. "' AND
                      referee = '". $referee['email_addr']. "'";
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
                    
                    $referees[$i]['is_contact'] = false;
                } else {
                    // Just create the invite and wait for the member to sign-up.
                    $query = "INSERT INTO member_invites SET 
                              referee_email = '". $referee['email_addr']. "', 
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
                    
                    $position = '- '. htmlspecialchars_decode($job). ' by '. htmlspecialchars_decode($employer);
                    $message = str_replace('%member_name%', htmlspecialchars_decode($member->get_name()), $message);
                    $message = str_replace('%member_email_addr%', $member->id(), $message);
                    $message = str_replace('%referee_email_addr%', $referee['email_addr'], $message);
                    $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
                    $message = str_replace('%root%', $GLOBALS['root'], $message);
                    $message = str_replace('%positions%', $position, $message);
                    $subject = "You Have Been Referred";
                    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
                    mail($referee['email_addr'], $subject, $message, $headers);

                    $referees[$i]['is_contact'] = false;
                    $referees[$i]['is_member'] = false;
                }
            }
        }
    } 
    
    $data = array();
    $data['member'] = $_POST['id'];
    $data['job'] = $_POST['job'];
    $data['referred_on'] = now();
    foreach ($referees as $referee) {
        if ($referee['is_member']) {
            $data['referee'] = $referee['email_addr'];
            
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
            
            $position = '- '. htmlspecialchars_decode($job). ' at '. htmlspecialchars_decode($employer);
            $message = str_replace('%member_name%', htmlspecialchars_decode($member->get_name()), $message);
            $message = str_replace('%member_email_addr%', $member->id(), $message);
            $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
            $message = str_replace('%root%', $GLOBALS['root'], $message);
            $message = str_replace('%positions%', $position, $message);
            $subject = htmlspecialchars_decode($member->get_name()). " referred you to a job!";
            $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
            mail($referee['email_addr'], $subject, $message, $headers);

            /*$handle = fopen('/tmp/email_to_'. $_POST['referee']. '.txt', 'w');
            fwrite($handle, 'Subject: '. $subject. "\n\n");
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

    $query = "SELECT jobs.title, employers.name, jobs.acceptable_resume_type, 
              branches.country AS branch_country 
              FROM jobs 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN branches ON branches.id = employers.branch 
              WHERE jobs.id = ". $_POST['job']. " LIMIT 1";
    $result = $mysqli->query($query);
    $acceptable_resume_type = $result[0]['acceptable_resume_type'];
    $job_title = $result[0]['title'];
    $employer_name = $result[0]['name'];
    $branch = $result[0]['branch_country'];
    $branch_email = 'team.'. strtolower($branch). '@yellowelevator.com';
    
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
            $raw_message = str_replace('%job%', htmlspecialchars_decode($job_title), $raw_message);
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
        
        if ($_POST['from'] == 'yel') {
            $referrers = array();
            $referrers[0] = $branch_email;
        }
        
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

                        $position = htmlspecialchars_decode($job_title). ' at '. htmlspecialchars_decode($employer_name);

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
                        $raw_message = str_replace('%job%', htmlspecialchars_decode($job_title), $raw_message);
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
                        // pre-approve for the candidate 
                        $query = "SELECT id FROM member_referees 
                                  WHERE member = '". $member->id(). "' AND 
                                  referee = '". $branch_email. "' AND 
                                  approved = 'N' LIMIT 1";
                        $result = $mysqli->query($query);
                        if (!is_null($result) && !empty($result)) {
                            $referee_id = $result[0]['id'];
                            $query = "UPDATE member_referees SET
                                      approved = 'Y' 
                                      WHERE id = ". $referee_id. "; 
                                      INSERT INTO member_referees SET 
                                      member = '". $branch_email. "', 
                                      referee = '". $member->id(). "', 
                                      referred_on = NOW(), 
                                      approved = 'Y'";
                            $mysqli->transact($query);
                        }
                        
                        $lines = file(dirname(__FILE__). '/private/mail/candidate_refer_request.txt');
                        $message = '';
                        foreach($lines as $line) {
                            $message .= $line;
                        }

                        $message = str_replace('%member_name%', htmlspecialchars_decode(desanitize($member->get_name())), $message);
                        $message = str_replace('%member_email_addr%', $member->id(), $message);
                        $message = str_replace('%branch_email_addr%', $branch_email, $message);
                        $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
                        $message = str_replace('%root%', $GLOBALS['root'], $message);
                        $message = str_replace('%job%', htmlspecialchars_decode($job_title), $message);
                        $message = str_replace('%employer%', htmlspecialchars_decode(desanitize($employer_name)), $message);
                        $subject = htmlspecialchars_decode(desanitize($member->get_name())). " needs to be referred to a job!";
                        $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
                        mail($branch_email, $subject, $message, $headers);

                        // $handle = fopen('/tmp/email_to_'. $branch_email. '.txt', 'w');
                        // fwrite($handle, 'Subject: '. $subject. "\n\n");
                        // fwrite($handle, $message);
                        // fclose($handle);
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

if ($_POST['action'] == 'quick_refer') {
    $is_from_contacts = ($_POST['qr_candidate_email_from_list'] != '0') ? true : false;
    $candidate_email = ($is_from_contacts) ? $_POST['qr_candidate_email_from_list'] : $_POST['qr_candidate_email'];
    $candidate_email = sanitize($candidate_email);
    
    if (strtoupper($candidate_email) == strtoupper($_POST['id'])) {
        ?><script type="text/javascript">top.stop_quick_refer_upload('-1');</script><?php
        exit();
    }
    
    $today = now();
    $member = new Member($_POST['id'], $_SESSION['yel']['member']['sid']);
    $mysqli = Database::connect();
    
    // 1. get the job details for email purposes
    $query = "SELECT jobs.title, employers.name 
              FROM jobs 
              LEFT JOIN employers ON employers.id = jobs.employer 
              WHERE jobs.id = ". $_POST['qr_job_id']. " LIMIT 1";
    $result = $mysqli->query($query);
    $job = 'Unknown Job';
    $employer = 'Unknown Employer';
    if (count($result) > 0 && !is_null($result)) {
        $job = $result[0]['title'];
        $employer = $result[0]['name'];
    }
    
    // 2. construct testimony
    $testimony = 'Experiences and Skillsets:<br/>'. sanitize($_POST['testimony_answer_1']). '<br/><br/>';
    $testimony .= 'Meet Requirements: '. $_POST['meet_req']. '<br/>Additional Comments:<br/>'. sanitize($_POST['testimony_answer_2']). '<br/><br/>';
    $testimony .= 'Personality/Work Attitude:<br/>'. sanitize($_POST['testimony_answer_3']). '<br/><br/>';
    $testimony .= 'Additional Recommendations: '. ((empty($_POST['testimony_answer_4'])) ? 'None provided' : sanitize($_POST['testimony_answer_4']));
    
    // 3. check whether candidate email is already in the system
    $is_friend = true;
    $query = "SELECT COUNT(*) AS is_referee 
              FROM member_referees 
              WHERE member = '". $member->id(). "' AND
              referee = '". $candidate_email. "'";
    $result = $mysqli->query($query);
    if ($result[0]['is_referee'] <= 0) {
        // not a friend
        $is_friend = false;
        
        $query = "SELECT COUNT(*) AS is_member 
                  FROM members 
                  WHERE email_addr = '". $candidate_email. "'";
        $result = $mysqli->query($query);
        if ($result[0]['is_member'] >= 1) {
            // The given email is a member, but not in the member's candidates list.
            // - Will need to wait for approval before the referral can be viewed.
            if (!$member->create_referee($candidate_email)) {
                ?><script type="text/javascript">top.stop_quick_refer_upload('-2');</script><?php
                exit();
            }
        } else {
            // Just create the invite and wait for the member to sign-up.
            // 1. create membership with active = 'N'
            $new_member = new Member($candidate_email);
            $new_password = generate_random_string_of(6);
            $data = array();
            $data['password'] = md5($new_password);
            $data['firstname'] = sanitize($_POST['qr_candidate_firstname']);
            $data['lastname'] = sanitize($_POST['qr_candidate_lastname']);
            $data['phone_num'] = sanitize($_POST['qr_candidate_phone']);
            $data['zip'] = sanitize($_POST['qr_candidate_zip']);
            $data['country'] = sanitize($_POST['qr_candidate_country']);
            $data['forget_password_question'] = 1;
            $data['forget_password_answer'] = 'system generated';
            $data['joined_on'] = $today;
            $data['active'] = 'N';
            $data['invites_available'] = '10';
            $data['checked_profile'] = 'N';
            $data['like_newsletter'] = 'N';
            $data['filter_jobs'] = 'N';
            
            if (!$new_member->create($data)) {
                ?><script type="text/javascript">top.stop_quick_refer_upload('-3');</script><?php
                exit();
            }
            
            $activation_id = microtime(true);
            $query = "INSERT INTO member_activation_tokens SET 
                      id = '". $activation_id. "', 
                      member = '". $candidate_email. "', 
                      joined_on = '". $today. "'";
            if (!$mysqli->execute($query)) {
                ?><script type="text/javascript">top.stop_quick_refer_upload('-4');</script><?php
                exit();
            }
            
            // 2. create an upload resume
            $data = array();
            $data['modified_on'] = $today;
            $data['name'] = $_FILES['qr_my_file']['name'];
            $data['private'] = 'N';
            $resume = new Resume($candidate_email);
            if (!$resume->create($data)) {
                ?><script type="text/javascript">top.stop_quick_refer_upload('-5');</script><?php
                exit();
            }
            
            // upload resume file
            $data = array();
            $data['FILE'] = array();
            $data['FILE']['type'] = $_FILES['qr_my_file']['type'];
            $data['FILE']['size'] = $_FILES['qr_my_file']['size'];
            $data['FILE']['name'] = $_FILES['qr_my_file']['name'];
            $data['FILE']['tmp_name'] = $_FILES['qr_my_file']['tmp_name'];
            if (!$resume->upload_file($data)) {
                $query = "DELETE FROM resume_index WHERE resume = ". $resume->id(). ";
                          DELETE FROM resumes WHERE id = ". $resume->id();
                $mysqli->transact($query);
                ?><script type="text/javascript">top.stop_quick_refer_upload('-6');</script><?php
                exit();
            }
            
            // 3. add candidate to contact
            if (!$member->create_referee($candidate_email)) {
                ?><script type="text/javascript">top.stop_quick_refer_upload('-2');</script><?php
                exit();
            }
            
            // 4. make referral, pre-approve referrer side only
            $data = array();
            $data['member'] = $member->id();
            $data['job'] = $_POST['qr_job_id'];
            $data['referred_on'] = $today;
            $data['referee'] = $candidate_email;
            $data['member_confirmed_on'] = $today;
            $data['member_read_resume_on'] = $today;
            $data['testimony'] = $testimony;
            $data['resume'] = $resume->id();

            if (!Referral::create($data)) {
                ?><script type="text/javascript">top.stop_quick_refer_upload('-7');</script><?php
                exit();
            }
            
            // 5. send invitation email with login details and activation
            $lines = file(dirname(__FILE__). '/private/mail/member_quick_referred_new.txt');
            $message = '';
            foreach($lines as $line) {
                $message .= $line;
            }
            
            $position = '- '. htmlspecialchars_decode($job). ' by '. htmlspecialchars_decode($employer);
            $message = str_replace('%member_name%', htmlspecialchars_decode($member->get_name()), $message);
            $message = str_replace('%member_email_addr%', $member->id(), $message);
            $message = str_replace('%referee_email_addr%', $candidate_email, $message);
            $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
            $message = str_replace('%root%', $GLOBALS['root'], $message);
            $message = str_replace('%positions%', $position, $message);
            $message = str_replace('%activation_id%', $activation_id, $message);
            $message = str_replace('%password%', $new_password, $message);
            $subject = "You Have Been Referred";
            $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
            mail($candidate_email, $subject, $message, $headers);
            
            // $handle = fopen('/tmp/email_to_'. $candidate_email. '.txt', 'w');
            // fwrite($handle, 'Subject: '. $subject. "\n\n");
            // fwrite($handle, $message);
            // fclose($handle);
            
            ?><script type="text/javascript">top.stop_quick_refer_upload('1');</script><?php
            exit();
        }
    }
    
    // both are friends, or assuming
    // 1. create resume record
    $data = array();
    $data['modified_on'] = $today;
    $data['name'] = $_FILES['qr_my_file']['name'];
    $data['private'] = 'N';
    $resume = new Resume($candidate_email);
    if (!$resume->create($data)) {
        ?><script type="text/javascript">top.stop_quick_refer_upload('-5');</script><?php
        exit();
    }
    
    // 1.1 upload resume file
    $data = array();
    $data['FILE'] = array();
    $data['FILE']['type'] = $_FILES['qr_my_file']['type'];
    $data['FILE']['size'] = $_FILES['qr_my_file']['size'];
    $data['FILE']['name'] = $_FILES['qr_my_file']['name'];
    $data['FILE']['tmp_name'] = $_FILES['qr_my_file']['tmp_name'];
    if (!$resume->upload_file($data)) {
        $query = "DELETE FROM resume_index WHERE resume = ". $resume->id(). ";
                  DELETE FROM resumes WHERE id = ". $resume->id();
        $mysqli->transact($query);
        ?><script type="text/javascript">top.stop_quick_refer_upload('-6');</script><?php
        exit();
    }
    
    // 2. create referral with pre-approvals from the referrer's side
    $data = array();
    $data['member'] = $member->id();
    $data['job'] = $_POST['qr_job_id'];
    $data['referred_on'] = $today;
    $data['referee'] = $candidate_email;
    $data['member_confirmed_on'] = $today;
    $data['member_read_resume_on'] = $today;
    $data['testimony'] = $testimony;
    $data['resume'] = $resume->id();
    
    if (!Referral::create($data)) {
        ?><script type="text/javascript">top.stop_quick_refer_upload('-7');</script><?php
        exit();
    }
    
    $lines = file(dirname(__FILE__). '/private/mail/member_referred.txt');
    if (!$is_friend) {
        $lines = file(dirname(__FILE__). '/private/mail/member_referred_approval.txt');
    }
    $message = '';
    foreach($lines as $line) {
        $message .= $line;
    }
    
    $position = '- '. htmlspecialchars_decode($job). ' at '. htmlspecialchars_decode($employer);
    $message = str_replace('%member_name%', htmlspecialchars_decode($member->get_name()), $message);
    $message = str_replace('%member_email_addr%', $member->id(), $message);
    $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
    $message = str_replace('%root%', $GLOBALS['root'], $message);
    $message = str_replace('%positions%', $position, $message);
    $subject = htmlspecialchars_decode($member->get_name()). " referred you to a job!";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($candidate_email, $subject, $message, $headers);
    
    // $handle = fopen('/tmp/email_to_'. $candidate_email. '.txt', 'w');
    // fwrite($handle, 'Subject: '. $subject. "\n\n");
    // fwrite($handle, $message);
    // fclose($handle);
    
    ?><script type="text/javascript">top.stop_quick_refer_upload('0');</script><?php
    exit();
}

if ($_POST['action'] == 'quick_upload') {
    $today = now();
    $mysqli = Database::connect();
    
    // 1. create a buffer
    $data = array();
    $data['job_id'] = $_POST['qu_job_id'];
    $data['referrer_email_addr'] = sanitize($_POST['qu_referrer_email']);
    $data['candidate_email_addr'] = sanitize($_POST['qu_candidate_email']);
    $data['referrer_phone_num'] = sanitize($_POST['qu_referrer_phone']);
    $data['referrer_firstname'] = sanitize($_POST['qu_referrer_firstname']);
    $data['referrer_lastname'] = sanitize($_POST['qu_referrer_lastname']);
    $data['referrer_zip'] = sanitize($_POST['qu_referrer_zip']);
    $data['referrer_country'] = sanitize($_POST['qu_referrer_country']);
    $data['candidate_phone_num'] = sanitize($_POST['qu_candidate_phone']);
    $data['candidate_firstname'] = sanitize($_POST['qu_candidate_firstname']);
    $data['candidate_lastname'] = sanitize($_POST['qu_candidate_lastname']);
    $data['candidate_zip'] = sanitize($_POST['qu_candidate_zip']);
    $data['candidate_country'] = sanitize($_POST['qu_candidate_country']);
    $data['added_on'] = $today;
    
    $i = 0;
    $query = "INSERT INTO users_contributed_resumes SET ";
    foreach ($data as $key => $value) {
        $query .= "`". $key. "` = '". $value. "'";
        
        if ($i < count($data)-1) {
            $query .= ", ";
        }
        
        $i++;
    }
    
    if (!$mysqli->execute($query)) {
        ?><script type="text/javascript">top.stop_quick_upload('-2');</script><?php
        exit();
    }
    
    // 2. upload file
    $resume_file = $_FILES['qu_my_file'];
    
    if ($resume_file['size'] > $GLOBALS['resume_size_limit']) {
        ?><script type="text/javascript">top.stop_quick_upload('-1');</script><?php
        exit();
    }
    
    $is_allowed_type = false;
    foreach ($GLOBALS['allowable_resume_types'] as $mime_type) {
        if ($resume_file['type'] == $mime_type) {
            $is_allowed_type = true;
            break;
        }
    }
    
    if (!$is_allowed_type) {
        ?><script type="text/javascript">top.stop_quick_upload('-1');</script><?php
        exit();
    }
    
    $data = array();
    $data['file_name'] = basename($resume_file['name']);
    $data['file_hash'] = generate_random_string_of(3). '.'. generate_random_string_of(6);
    $data['file_type'] = $resume_file['type'];
    $data['file_size'] = $resume_file['size'];
    $resume_file['new_name'] = $data['file_hash'];
    if (move_uploaded_file($resume_file['tmp_name'], $GLOBALS['buffered_resume_dir']. '/'. $resume_file['new_name']) === false) {
        ?><script type="text/javascript">top.stop_quick_upload('-1');</script><?php
        exit();
    }
    
    $i = 0;
    $query = "UPDATE users_contributed_resumes SET ";
    foreach ($data as $key => $value) {
        $query .= "`". $key. "` = '". $value. "'";
        
        if ($i < count($data)-1) {
            $query .= ", ";
        }
        
        $i++;
    }
    $query .= " WHERE job_id = ". $_POST['qu_job_id']. " AND 
               referrer_email_addr = '". sanitize($_POST['qu_referrer_email']). "' AND 
               candidate_email_addr = '". sanitize($_POST['qu_candidate_email']). "'";
    if (!$mysqli->execute($query)) {
        ?><script type="text/javascript">top.stop_quick_upload('-3');</script><?php
        exit();
    }
    
    ?><script type="text/javascript">top.stop_quick_upload('0');</script><?php
    exit();
}
?>
