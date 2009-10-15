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
    
    $query = "SELECT members.email_addr AS member_email_addr, members.phone_num AS member_phone_num, 
              CONCAT(members.firstname, ', ', members.lastname) AS candidate_name, 
              DATE_FORMAT(members.joined_on, '%e %b, %Y') AS formatted_joined_on, 
              recommenders.email_addr AS recommender_email_addr, 
              recommenders.phone_num AS recommender_phone_num, 
              CONCAT(recommenders.firstname, ', ', recommenders.lastname) AS recommender_name  
              FROM members
              LEFT JOIN recommenders ON recommenders.email_addr = members.recommender 
              WHERE members.added_by = ". $_POST['id']. " 
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
    $query = "SELECT members.firstname AS member_firstname, members.lastname AS member_lastname, 
              members.phone_num AS member_phone_num, countries.country, members.zip, 
              recommenders.email_addr AS recommender_email_addr, 
              recommenders.firstname AS recommender_firstname, recommenders.lastname AS recommender_lastname, 
              recommenders.phone_num AS recommender_phone_num, 
              DATE_FORMAT(members.joined_on, '%e %b, %Y') AS formatted_joined_on 
              FROM members 
              LEFT JOIN countries ON countries.country_code = members.country 
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

if ($_POST['action'] == 'add_new_candidate') {
    $recommender_industries_adding_error = false;
    
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
            $recommender_data['added_by'] = $_POST['id'];
            $recommender_data['added_on'] = $joined_on;
            
            if ($recommender->create($recommender_data)) {
                $industries = explode(',', $_POST['recommender_industries']);
                if (!$recommender->add_to_industries($industries)) {
                    $recommender_industries_adding_error = true;
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
        $member_data['active'] = 'N';
        $member_data['invites_available'] = '10';
        
        if ($member_data['like_newsletter'] == 'Y') {
            $member_data['filte_jobs'] = 'Y';
        }
        
        $member_data['added_by'] = $_POST['id'];
        $member_data['recommender'] = $_POST['recommender_email_addr'];
        
        if ($member->create($member_data)) {
            // Create activation token and email
            $activation_id = microtime(true);
            $query = "INSERT INTO member_activation_tokens SET 
                      id = '". $activation_id. "', 
                      member = '". $_POST['member_email_addr']. "', 
                      joined_on = '". $joined_on. "'";
            if ($mysqli->execute($query)) {
                $mail_lines = file('../private/mail/member_activation_with_password.txt');
                $message = '';
                foreach ($mail_lines as $line) {
                    $message .= $line;
                }
                
                $recommender = htmlspecialchars_decode($_POST['recommender_firstname']). ', '. htmlspecialchars_decode($_POST['recommender_lastname']);
                $message = str_replace('%recommender%', $recommender, $message);
                $message = str_replace('%recommender_email_addr%', $_POST['recommender_email_addr'], $message);
                $message = str_replace('%activation_id%', $activation_id, $message);
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
            } else {
                echo '-4';  // failed to create token
                exit();
            }
        } else {
            echo '-3'; // failed to create member
            exit();
        }
    } else {
        echo '-2'; // member already exists
        exit();
    }
    
    if ($recommender_industries_adding_error) {
        echo '-5';
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
    $data['name'] = $_FILES['my_file']['name'];
    $data['private'] = 'N';
    
    if ($_POST['id'] == '0') {
        $resume = new Resume($_POST['resume_member_email_addr']);
        if (!$resume->create($data)) {
            ?>
                <script type="text/javascript">window.top.window.stop_upload(<?php echo '0'; ?>);</script>
            <?php
            exit();
        }
    } else {
        $resume = new Resume($_POST['resume_member_email_addr'], $_POST['id']);
        $is_update = true;
        if (!$resume->update($data)) {
            ?>
                <script type="text/javascript">window.top.window.stop_upload(<?php echo '0'; ?>);</script>
            <?php
            exit();
        }
    }
    
    $data = array();
    $data['FILE'] = array();
    $data['FILE']['type'] = $_FILES['my_file']['type'];
    $data['FILE']['size'] = $_FILES['my_file']['size'];
    $data['FILE']['name'] = $_FILES['my_file']['name'];
    $data['FILE']['tmp_name'] = $_FILES['my_file']['tmp_name'];
    
    if (!$resume->upload_file($data, $is_update)) {
        $query = "DELETE FROM resume_index WHERE resume = ". $resume->id(). ";
                  DELETE FROM resumes WHERE id = ". $resume->id();
        $mysqli = Database::connect();
        $mysqli->transact($query);
        ?><script type="text/javascript">window.top.window.stop_upload(<?php echo "0"; ?>);</script><?php
        exit();
    }
    
    ?><script type="text/javascript">window.top.window.stop_upload(<?php echo "1"; ?>);</script><?php
    exit();
}
?>
