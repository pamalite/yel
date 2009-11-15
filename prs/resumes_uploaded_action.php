<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id'])) {
    echo "ko";
    exit();
    //redirect_to('login.php');
}

$xml_dom = new XMLDOM();

if (!isset($_POST['action'])) {
    $order_by = 'ucr.added_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT jobs.title AS job, ucr.file_name AS resume_label, ucr.job_id, 
              employers.name AS job_employer, industries.industry AS job_industry, 
              ucr.file_hash, ucr.candidate_phone_num AS candidate_phone_num, 
              ucr.referrer_phone_num AS referrer_phone_num, ucr.candidate_email_addr AS candidate_email_addr, 
              ucr.referrer_email_addr AS referrer_email_addr, 
              ucr.candidate_zip AS candidate_zip, ucr.referrer_zip AS referrer_zip, 
              candidate_countries.country AS candidate_country, 
              referrer_countries.country AS referrer_country, 
              CONCAT(ucr.candidate_firstname, ', ', ucr.candidate_lastname) AS candidate, 
              CONCAT(ucr.referrer_firstname, ', ', ucr.referrer_lastname) AS referrer, 
              DATE_FORMAT(ucr.added_on, '%e %b, %Y') AS formatted_added_on 
              FROM users_contributed_resumes AS ucr 
              LEFT JOIN jobs ON jobs.id = ucr.job_id 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN industries ON industries.id = jobs.industry 
              LEFT JOIN countries AS candidate_countries ON candidate_countries.country_code = ucr.candidate_country 
              LEFT JOIN countries AS referrer_countries ON referrer_countries.country_code = ucr.referrer_country 
              ORDER BY ". $_POST['order_by'];
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if (count($result) <= 0 || is_null($result) || empty($result)) {
        echo '0';
        exit();
    }
    
    if (!$result) {
        echo 'ko';
        exit();
    }
    
    foreach($result as $i=>$row) {
        $result[$i]['candidate'] = htmlspecialchars_decode(html_entity_decode(stripslashes(desanitize($row['candidate']))));
        $result[$i]['referrer'] = htmlspecialchars_decode(html_entity_decode(stripslashes(desanitize($row['referrer']))));
    }
    
    $response = array('uploaded_resumes' => array('uploaded_resume' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'add_to_privileged') {
    $recommender_industries_adding_error = false;
    $default_contact_adding_error = false;
    
    $mysqli = Database::connect();
    $joined_on = today();
    
    // get the buffer record
    $query = "SELECT * FROM users_contributed_resumes WHERE 
              job_id = ". $_POST['job_id']. " AND 
              candidate_email_addr = '". $_POST['candidate_email']. "' AND 
              referrer_email_addr = '". $_POST['recommender_email']. "' LIMIT 1";
    $result = $mysqli->query($query);
    if (empty($result[0]) || is_null($result[0])) {
        echo '-1'; // cannot find buffer record
        exit();
    }
    $buffer = $result[0];
    
    // create recommender
    $recommender = new Recommender($_POST['recommender_email']);    
    $query = "SELECT COUNT(*) AS id_used FROM recommenders WHERE email_addr = '". $_POST['recommender_email']. "'";
    $result = $mysqli->query($query);
    if ($result[0]['id_used'] == '0') {
        $recommender_data = array();
        $recommender_data['firstname'] = $buffer['referrer_firstname'];
        $recommender_data['lastname'] = $buffer['referrer_lastname'];
        $recommender_data['phone_num'] = $buffer['referrer_phone_num'];
        $recommender_data['remarks'] = sanitize($_POST['recommender_remarks']);
        $recommender_data['region'] = sanitize($_POST['recommender_region']);
        $recommender_data['added_by'] = $_POST['id'];
        $recommender_data['added_on'] = $joined_on;
        
        if ($recommender->create($recommender_data)) {
            $industries = explode(',', $_POST['recommender_industries']);
            if (!empty($industries)) {
                if (!$recommender->add_to_industries($industries)) {
                    $recommender_industries_adding_error = true;
                }
            }
        } else {
            echo '-2'; // failed to create new recommender
            exit();
        }
    } else {
        // update the industries
        $industries = explode(',', $_POST['recommender_industries']);
        if (!empty($industries)) {
            $query = "DELETE FROM recommender_industries WHERE recommender = '". $_POST['recommender_email']. "'";
            $mysqli->execute($query);
            if (!$recommender->add_to_industries($industries)) {
                $recommender_industries_adding_error = true;
            }
        }
    }
    
    // create member
    $query = "SELECT COUNT(*) AS id_used FROM members WHERE email_addr = '". $_POST['candidate_email']. "'";
    $result = $mysqli->query($query);
    if ($result[0]['id_used'] == '0') {
        $new_password = generate_random_string_of(6);
        $member = new Member($_POST['candidate_email']);
        $member_data = array();
        $member_data['firstname'] = $buffer['candidate_firstname'];
        $member_data['lastname'] = $buffer['candidate_lastname'];
        $member_data['password'] = md5($new_password);
        $member_data['forget_password_question'] = 1;
        $member_data['forget_password_answer'] = '(System generated)';
        $member_data['phone_num'] = $buffer['candidate_phone_num'];
        $member_data['zip'] = $buffer['candidate_zip'];
        $member_data['country'] = $buffer['candidate_country'];
        $member_data['joined_on'] = $joined_on;
        $member_data['active'] = 'N';
        $member_data['invites_available'] = '10';
        $member_data['remarks'] = sanitize($_POST['candidate_remarks']);
        $member_data['like_newsletter'] = 'Y';
        $member_data['filte_jobs'] = 'Y';
        $member_data['added_by'] = $_POST['id'];
        $member_data['recommender'] = $_POST['recommender_email'];
        
        if ($member->create($member_data)) {
            // Create activation token and email
            $activation_id = microtime(true);
            $query = "INSERT INTO member_activation_tokens SET 
                      id = '". $activation_id. "', 
                      member = '". $_POST['candidate_email']. "', 
                      joined_on = '". $joined_on. "'";
            if ($mysqli->execute($query)) {
                $mail_lines = file('../private/mail/member_activation_with_password.txt');
                $message = '';
                foreach ($mail_lines as $line) {
                    $message .= $line;
                }
                
                $recommender = htmlspecialchars_decode($buffer['referrer_firstname']). ', '. htmlspecialchars_decode($buffer['referrer_lastname']);
                $message = str_replace('%recommender%', $recommender, $message);
                $message = str_replace('%recommender_email_addr%', $_POST['recommender_email'], $message);
                $message = str_replace('%activation_id%', $activation_id, $message);
                $message = str_replace('%password%', $new_password, $message);
                $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
                $message = str_replace('%root%', $GLOBALS['root'], $message);
                $subject = "Member Activation Required";
                $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
                // mail($buffer['candidate_email'], $subject, $message, $headers);
                            
                $handle = fopen('/tmp/email_to_'. $buffer['candidate_email']. '_token.txt', 'w');
                fwrite($handle, 'Subject: '. $subject. "\n\n");
                fwrite($handle, $message);
                fclose($handle);
                
                // add yellow elevator as default contact and pre-approve
                $employee = new Employee($_POST['id']);
                $branch = $employee->get_branch();
                
                $query = "INSERT INTO member_referees SET 
                          `member` = '". $member->id(). "', 
                          `referee` = 'team.". strtolower($branch[0]['country_code']). "@yellowelevator.com', 
                          `referred_on` = NOW(), 
                          `approved` = 'Y'; 
                          INSERT INTO member_referees SET 
                          `referee` = '". $member->id(). "', 
                          `member` = 'team.". strtolower($branch[0]['country_code']). "@yellowelevator.com', 
                          `referred_on` = NOW(), 
                          `approved` = 'Y'";
                if (!$mysqli->transact($query)) {
                    $default_contact_adding_error = true;
                }
            } else {
                echo '-4';  // failed to create token
                exit();
            }
        } else {
            echo '-3'; // failed to create member
            exit();
        }
    } else {
        echo '-5'; // member already exists
        exit();
    }
    
    // create resume 
    $resume = new Resume($_POST['candidate_email']);
    $data = array();
    $data['modified_on'] = $buffer['added_on'];
    $data['name'] = $buffer['file_name'];
    $data['private'] = 'N';
    if (!$resume->create($data)) {
        echo '-9';
        exit();
    }
    
    $file_hash = generate_random_string_of(6);
    $new_name = $resume->id(). '.'. $file_hash;
    if (rename($GLOBALS['buffered_resume_dir']. '/'. $buffer['file_hash'], $GLOBALS['resume_dir']. '/'. $new_name)) {
        $data = array();
        $data['file_hash'] = $file_hash;
        $data['file_name'] = $buffer['file_name'];
        $data['file_type'] = $buffer['file_type'];
        $data['file_size'] = $buffer['file_size'];
        if (!$resume->update($data)) {
            echo '-10';
            exit();
        }
        
        $query = "DELETE FROM users_contributed_resumes WHERE 
                  job_id = ". $_POST['job_id']. " AND 
                  candidate_email_addr = '". $_POST['candidate_email']. "' AND 
                  referrer_email_addr = '". $_POST['recommender_email']. "'";
        $mysqli->execute($query);
    }
    
    if ($recommender_industries_adding_error && $default_contact_adding_error) {
        echo'-6';
    } else if ($recommender_industries_adding_error && !$default_contact_adding_error) {
        echo '-7';
    } else if (!$recommender_industries_adding_error && $default_contact_adding_error) {
        echo '-8';
    } else {
        echo '0';
    }
    exit();
}
?>
