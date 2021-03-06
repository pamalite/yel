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
    $order_by = 'members.joined_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT members.email_addr AS member_email_addr, members.phone_num AS member_phone_num, members.remarks, 
              CONCAT(employees.firstname, ', ', employees.lastname) AS employee, 
              CONCAT(members.firstname, ', ', members.lastname) AS candidate_name, 
              DATE_FORMAT(members.joined_on, '%e %b, %Y') AS formatted_joined_on, 
              recommenders.email_addr AS recommender_email_addr, 
              recommenders.phone_num AS recommender_phone_num, 
              CONCAT(recommenders.firstname, ', ', recommenders.lastname) AS recommender_name  
              FROM members 
              LEFT JOIN recommenders ON recommenders.email_addr = members.recommender 
              LEFT JOIN employees ON members.added_by = employees.id 
              WHERE employees.branch = ". $_SESSION['yel']['employee']['branch']['id']. " AND 
              members.email_addr <> 'initial@yellowelevator.com' 
              ORDER BY ". $_POST['order_by'];
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }
    
    if (!$result) {
        echo 'ko';
        exit();
    }
    
    foreach($result as $i=>$row) {
        $result[$i]['candidate_name'] = htmlspecialchars_decode(html_entity_decode(stripslashes(desanitize($row['candidate_name']))));
        $result[$i]['recommender_name'] = htmlspecialchars_decode(html_entity_decode(stripslashes(desanitize($row['recommender_name']))));
    }
    
    $response = array('candidates' => array('candidate' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_profile') {
    $query = "SELECT members.firstname AS member_firstname, members.lastname AS member_lastname, members.remarks, 
              members.phone_num AS member_phone_num, members.country, members.zip, members.checked_profile, 
              recommenders.email_addr AS recommender_email_addr, 
              recommenders.firstname AS recommender_firstname, recommenders.lastname AS recommender_lastname, 
              recommenders.phone_num AS recommender_phone_num, 
              DATE_FORMAT(members.joined_on, '%e %b, %Y') AS formatted_joined_on 
              FROM members 
              LEFT JOIN recommenders ON recommenders.email_addr = members.recommender 
              WHERE members.email_addr = '". $_POST['id']. "'";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    
    $profile = array();
    foreach ($result[0] as $key => $value) {
        $profile[$key] = $value;
        
        if (stripos($key, 'firstname') !== false || stripos($key, 'lastname') !== false) {
            $profile[$key] = htmlspecialchars_decode(html_entity_decode(stripslashes(desanitize($value))));
        }
    }

    $response =  array('profile' => $profile);

    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'save_remark') {
    $member = new Member($_POST['id']);
    $data = array();
    $data['remarks'] = sanitize($_POST['remark']);
    if ($member->update($data)) {
        echo 'ok';
    } else {
        echo 'ko';
    }
    
    exit();
}

if ($_POST['action'] == 'get_resumes') {
    $order_by = 'modified_on desc';

    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }

    $criteria = array(
        'columns' => 'id, name, private, DATE_FORMAT(modified_on, \'%e %b, %Y\') AS modified_date, file_hash, file_name',
        'order' => $order_by,
        'match' => 'member = \''. $_POST['id']. '\' AND deleted = \'N\''
    );

    $resumes = Resume::find($criteria);
    $response = array(
        'resumes' => array('resume' => $resumes)
    );

    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'save_profile') {
    $member = new Member($_POST['email_addr']);
    
    $data = array();
    $data['firstname'] = sanitize($_POST['firstname']);
    $data['lastname'] = sanitize($_POST['lastname']);
    $data['phone_num'] = $_POST['phone_num'];
    $data['zip'] = $_POST['zip'];
    $data['country'] = $_POST['country'];
    
    if (!$member->update($data)) {
        echo 'ko';
        exit();
    }
    
    // TODO: Update recommenders (TBD)
    
    echo 'ok';
    exit();
}


if ($_POST['action'] == 'add_new_candidate') {
    $recommender_industries_adding_error = false;
    $default_contact_adding_error = false;
    
    $mysqli = Database::connect();
    $joined_on = today();
    
    // check recommender
    $recommender = new Recommender($_POST['recommender_email_addr']);
    if ($_POST['recommender_from'] == 'new') {
        // create recommender
        $query = "SELECT COUNT(*) AS id_used FROM recommenders WHERE email_addr = '". $_POST['recommender_email_addr']. "'";
        $result = $mysqli->query($query);
        if ($result[0]['id_used'] == '0') {
            $recommender_data = array();
            $recommender_data['firstname'] = sanitize($_POST['recommender_firstname']);
            $recommender_data['lastname'] = sanitize($_POST['recommender_lastname']);
            $recommender_data['phone_num'] = $_POST['recommender_phone_num'];
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
                echo '-1'; // failed to create new recommender
                exit();
            }
        } else {
            // update the industries
            $industries = explode(',', $_POST['recommender_industries']);
            $query = "DELETE FROM recommender_industries WHERE recommender = '". $_POST['recommender_email_addr']. "'";
            $mysqli->execute($query);
            if (!$recommender->add_to_industries($industries)) {
                $recommender_industries_adding_error = true;
            }
        }
    }
    
    // check member
    $query = "SELECT COUNT(*) AS id_used FROM members WHERE email_addr = '". $_POST['member_email_addr']. "'";
    $result = $mysqli->query($query);
    if ($result[0]['id_used'] == '0') {
        $new_password = generate_random_string_of(6);
        $member = new Member($_POST['member_email_addr']);
        $member_data = array();
        $member_data['firstname'] = sanitize($_POST['member_firstname']);
        $member_data['lastname'] = sanitize($_POST['member_lastname']);
        $member_data['password'] = md5($new_password);
        $member_data['forget_password_question'] = 1;
        $member_data['forget_password_answer'] = '(System generated)';
        $member_data['phone_num'] = $_POST['member_phone_num'];
        $member_data['zip'] = $_POST['member_zip'];
        $member_data['country'] = $_POST['member_country'];
        $member_data['joined_on'] = $joined_on;
        $member_data['active'] = 'Y';
        $member_data['invites_available'] = '10';
        $member_data['remarks'] = sanitize($_POST['member_remarks']);
        $member_data['like_newsletter'] = 'Y';
        $member_data['filter_jobs'] = 'Y';
        $member_data['added_by'] = $_POST['id'];
        $member_data['recommender'] = $_POST['recommender_email_addr'];
        
        if ($member->create($member_data)) {
            // Create activation token and email
            // $activation_id = microtime(true);
            // $query = "INSERT INTO member_activation_tokens SET 
            //           id = '". $activation_id. "', 
            //           member = '". $_POST['member_email_addr']. "', 
            //           joined_on = '". $joined_on. "'";
            // if ($mysqli->execute($query)) {
                $mail_lines = file('../private/mail/member_activation_with_password.txt');
                $message = '';
                foreach ($mail_lines as $line) {
                    $message .= $line;
                }
                
                $recommender = htmlspecialchars_decode($_POST['recommender_firstname']). ', '. htmlspecialchars_decode($_POST['recommender_lastname']);
                $message = str_replace('%recommender%', $recommender, $message);
                $message = str_replace('%recommender_email_addr%', $_POST['recommender_email_addr'], $message);
                $message = str_replace('%password%', $new_password, $message);
                $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
                $message = str_replace('%root%', $GLOBALS['root'], $message);
                $subject = "Member Activation Required";
                $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
                mail($_POST['member_email_addr'], $subject, $message, $headers);
                            
                // $handle = fopen('/tmp/email_to_'. $_POST['member_email_addr']. '_token.txt', 'w');
                // fwrite($handle, 'Subject: '. $subject. "\n\n");
                // fwrite($handle, $message);
                // fclose($handle);
                
                // add yellow elevator as default contact and pre-approve
                $employee = new Employee($_POST['user_id']);
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
            // } else {
            //     echo '-4';  // failed to create token
            //     exit();
            // }
        } else {
            echo '-3'; // failed to create member
            exit();
        }
    } else {
        echo '-2'; // member already exists
        exit();
    }
    
    if ($recommender_industries_adding_error && $default_contact_adding_error) {
        echo'-7';
    } else if ($recommender_industries_adding_error && !$default_contact_adding_error) {
        echo '-5';
    } else if (!$recommender_industries_adding_error && $default_contact_adding_error) {
        echo '-6';
    } else {
        echo '0';
    }
    exit();
}

if ($_POST['action'] == 'upload_resume') {
    $resume = NULL;
    $is_update = false;
    $data = array();
    $data['modified_on'] = now();
    $data['name'] = str_replace(array('\'', '"', '\\'), '', basename($_FILES['my_file']['name']));
    $data['private'] = 'N';
    
    if ($_POST['id'] == '0') {
        $resume = new Resume($_POST['resume_member_email_addr']);
        if (!$resume->create($data)) {
            ?>
                <script type="text/javascript">top.stop_upload(<?php echo '0'; ?>);</script>
            <?php
            exit();
        }
    } else {
        $resume = new Resume($_POST['resume_member_email_addr'], $_POST['id']);
        $is_update = true;
        if (!$resume->update($data)) {
            ?>
                <script type="text/javascript">top.stop_upload(<?php echo '0'; ?>);</script>
            <?php
            exit();
        }
    }
    
    $data = array();
    $data['FILE'] = array();
    $data['FILE']['type'] = $_FILES['my_file']['type'];
    $data['FILE']['size'] = $_FILES['my_file']['size'];
    $data['FILE']['name'] = str_replace(array('\'', '"', '\\'), '', basename($_FILES['my_file']['name']));
    $data['FILE']['tmp_name'] = $_FILES['my_file']['tmp_name'];
    
    if (!$resume->upload_file($data, $is_update)) {
        $query = "DELETE FROM resume_index WHERE resume = ". $resume->id(). ";
                  DELETE FROM resumes WHERE id = ". $resume->id();
        $mysqli = Database::connect();
        $mysqli->transact($query);
        ?><script type="text/javascript">top.stop_upload(<?php echo "0"; ?>);</script><?php
        exit();
    }
    
    ?><script type="text/javascript">top.stop_upload(<?php echo "1"; ?>);</script><?php
    exit();
}

if ($_POST['action'] == 'get_jobs') {
    $mysqli = Database::connect();
    $query = "SELECT jobs.id, jobs.title, jobs.description, industries.industry, jobs.currency, jobs.salary, 
              employers.name AS employer
              FROM jobs
              LEFT JOIN industries ON industries.id = jobs.industry 
              LEFT JOIN employers ON employers.id = jobs.employer 
              WHERE jobs.closed = 'N' AND jobs.expire_on >= NOW()";
    if ($_POST['filter_by'] != '0') {
        // $query .= " AND (jobs.industry = ". $_POST['filter_by'] ." OR industries.parent_id = ". $_POST['filter_by']. ")";
        $query .= " WHERE jobs.employer = '". $_POST['filter_by']. "'";
    }
    $result = $mysqli->query($query);
    if (is_null($result) || empty($result)) {
        echo '0';
        exit();
    }
    
    foreach ($result as $i=>$row) {
        $result[$i]['title'] = desanitize($row['title']);
        $result[$i]['employer'] = desanitize($row['employer']);
        $result[$i]['description'] = htmlspecialchars_decode(html_entity_decode(desanitize($row['description'])));
        $result[$i]['salary'] = number_format($row['salary'], 2, '.', ', ');
    }
    
    $response = array('jobs' => array('job' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'make_referral') {
    $job_id = sanitize($_POST['job']);
    $testimony = $_POST['testimony'];
    
    $employee = new Employee($_POST['id']);
    $branch = $employee->get_branch();
    $member = 'team.'. strtolower($branch[0]['country_code']). '@yellowelevator.com';
    $testimony = sanitize('Yellow Elevator Contact:<br/>'. $employee->get_name(). ' ['. $employee->email_address(). ']<br/><br/>'. $testimony);
    
    $mysqli = Database::connect();
    $job = array();
    $query = "SELECT jobs.title, employers.name, employers.email_addr, employers.like_instant_notification, 
              jobs.contact_carbon_copy 
              FROM jobs 
              LEFT JOIN employers ON employers.id = jobs.employer 
              WHERE jobs.id = ". $job_id. " LIMIT 1";
    $result = $mysqli->query($query);
    $job['id'] = $job_id;
    $job['job'] = 'Unknown Job';
    $job['employer'] = 'Unknown Employer';
    $job['employer_email_addr'] = '';
    $job['employer_notify_now'] = false;
    if (count($result) > 0 && !empty($result)) {
        $job['job'] = $result[0]['title'];
        $job['employer'] = $result[0]['name'];
        $job['employer_email_addr'] = $result[0]['email_addr'];
        $job['employer_notify_now'] = ($result[0]['like_instant_notification'] == '1') ? true : false;
        if (!empty($result[0]['contact_carbon_copy']) && !is_null($result[0]['contact_carbon_copy'])) {
            $job['contact_carbon_copy'] = $result[0]['contact_carbon_copy'];
        }
    }
    
    // check whether are both the member and referee friend
    $query = "SELECT COUNT(*) AS is_friend 
              FROM member_referees 
              WHERE (member = '". $member. "' AND 
              referee = '". $_POST['referee']. "') OR 
              (referee = '". $member. "' AND 
              member = '". $_POST['referee']. "')";
    $result = $mysqli->query($query);
    if ($result[0]['is_friend'] <= 0) {
        $query = "INSERT INTO member_referees SET 
                  `member` = '". $_POST['referee']. "', 
                  `referee` = '". $member. "', 
                  `referred_on` = NOW(), 
                  `approved` = 'Y'; 
                  INSERT INTO member_referees SET 
                  `referee` = '". $_POST['referee']. "', 
                  `member` = '". $member. "', 
                  `referred_on` = NOW(), 
                  `approved` = 'Y'";
        if (!$mysqli->transact($query)) {
            echo '-1';
            exit();
        }
    } else if ($result[0]['is_friend'] == 1) {
        // candidate may want to do their own referral instead
        echo '-2';
        exit();
    }
    
    $today = now();
    $data = array();
    $data['member'] = $member;
    $data['job'] = $job_id;
    $data['referred_on'] = $today;
    $data['member_confirmed_on'] = $today;
    $data['member_rejected_on'] = 'NULL';
    $data['testimony'] = $testimony;
    $data['referee'] = $_POST['referee'];
    $data['resume'] = $_POST['resume'];
    $data['member_read_resume_on'] = $today;
    $data['referee_acknowledged_on'] = $today;
    
    // check whether do we have consent to refer now
    // $query = "SELECT active FROM members WHERE email_addr = '". $_POST['referee']. "' LIMIT 1";
    // $result = $mysqli->query($query);
    //if ($result[0]['active'] == 'Y') {
        // has consent - refer it
        if (!Referral::create($data)) {
            echo 'ko';
            exit();
        }
        
        if ($job['employer_notify_now']) {
            $lines = file(dirname(__FILE__). '/../private/mail/employer_new_referral.txt');
            $message = '';
            foreach($lines as $line) {
                $message .= $line;
            }

            $message = str_replace('%company%', desanitize($job['employer']), $message);
            $message = str_replace('%job%', desanitize($job['job']), $message);
            $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
            $message = str_replace('%root%', $GLOBALS['root'], $message);
            $subject = "New application for ". desanitize($job['job']). " position";
            $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
            if (array_key_exists('contact_carbon_copy', $job)) {
                $headers .= 'Cc: '. $job['contact_carbon_copy']. "\n";
            }
            mail($job['employer_email_addr'], $subject, $message, $headers);
            
            // $handle = fopen('/tmp/email_to_'. $job['employer_email_addr']. '.txt', 'w');
            // fwrite($handle, 'Subject: '. $subject. "\n\n");
            // fwrite($handle, 'Header: '. $headers. "\n\n");
            // fwrite($handle, $message);
            // fclose($handle);
        }
    // } else {
    //     // no consent - buffer it
    //     $query = "INSERT INTO privileged_referral_buffers SET ";
    //     $i = 0;
    //     foreach ($data as $key=>$value) {
    //         if (!is_numeric($value)) {
    //             $value = "'". $value. "'";
    //         }
    //         
    //         if ($key != 'member_rejected_on') {
    //             if ($i == 0) {
    //                 $query .= "`". $key. "` = ". $value;
    //             } else {
    //                 $query .= ", `". $key. "` = ". $value;
    //             }
    //             $i++;
    //         }
    //     }
    //     
    //     if (!$mysqli->execute($query)) {
    //         echo 'ko';
    //         exit();
    //     }
    // }
    
    $position = '- '. $job['job']. ' at '. $job['employer'];
    $lines = file(dirname(__FILE__). '/../private/mail/privileged_member_referred.txt');
    $message = '';
    foreach($lines as $line) {
        $message .= $line;
    }
    
    $message = str_replace('%member_name%', htmlspecialchars_decode(desanitize($employee->get_name())), $message);
    $message = str_replace('%member_email_addr%', $employee->email_address(), $message);
    $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
    $message = str_replace('%root%', $GLOBALS['root'], $message);
    $message = str_replace('%position%', $position, $message);
    $subject = htmlspecialchars_decode(desanitize($employee->get_name())). " has screened and submitted your resume for the ". htmlspecialchars_decode($job['job']). " position";
    $headers = 'From: '. str_replace(',', '', htmlspecialchars_decode(desanitize($employee->get_name()))). ' <'. $employee->email_address(). '>' . "\n";
    mail($_POST['referee'], $subject, $message, $headers);
    
    // $handle = fopen('/tmp/ref_email_to_'. $_POST['referee']. '.txt', 'w');
    // fwrite($handle, 'Subject: '. $subject. "\n\n");
    // fwrite($handle, $message);
    // fclose($handle);
    
    echo '0';
    exit();
}

?>
