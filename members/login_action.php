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
    redirect_to('login.php');
}

if ($_POST['action'] == 'reset_password') {
    $member = new Member($_POST['id']);
    $result = $member->get();
    
    if (!is_null($result) && !empty($result)) {
        $temp_password = generate_random_string_of(6);
        $data = array();
        $data['password'] = md5($temp_password);
        $member->setAdmin(true);
        
        if (!$member->update($data)) {
            echo 'ko';
            exit();
        }
        
        $lines = file(dirname(__FILE__). '/../private/mail/member_password_reset.txt');
        $message = '';
        foreach($lines as $line) {
            $message .= $line;
        }

        $temp_password_line = "Temporary password: ". $temp_password. "\n";
        $message = str_replace('%temp_password_line%', $temp_password_line, $message);
        $subject = "Member Password Reset";
        $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
        
        mail($member->getId(), $subject, $message, $headers);
        
        // $handle = fopen('/tmp/email_to_'. $member->getId(). '.txt', 'w');
        // fwrite($handle, 'Subject: '. $subject. "\n\n");
        // fwrite($handle, $message);
        // fclose($handle);
        
        echo 'ok';
        exit();
    }
    
    echo 'bad';
    exit();
}

if ($_POST['action'] == 'login') {
    $id = $_POST['id'];
    $hash = $_POST['hash'];
    $sid = $_POST['sid'];
    
    $_SESSION['yel']['member']['id'] = $id;
    $_SESSION['yel']['member']['hash'] = $hash;
    $_SESSION['yel']['member']['sid'] = $sid;
    
    header('Content-type: text/xml');
    
    $member = new Member($id, $sid);
    if (!$member->isActive()) {
        $_SESSION['yel']['member']['hash'] = '';
        $response['errors'] = array(
            'error' => 'The provided credentials are marked as inactive or suspended.&nbsp;<br/>&nbsp;Please contact your administrator for further assistance.'
        );
        echo $xml_dom->get_xml_from_array($response);
        exit();
    }
    
    if (!$member->isRegistered($hash)) {
        $_SESSION['yel']['member']['hash'] = "";
        $response['errors'] = array(
            'error' => 'The provided credentials are invalid. Please try again.'
        );
        echo $xml_dom->get_xml_from_array($response);
        exit();
    } 
    
    if (!$member->setSessionWith($hash)) {
        $_SESSION['yel']['member']['hash'] = "";
        $response['errors'] = array(
            'error' => 'bad_login'
        );
        echo $xml_dom->get_xml_from_array($response);
        exit();
    }
    
    $response['login'] = array('status' => 'ok');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'linkedin_login') {
    $id = $_POST['id'];
    $hash = $_POST['hash'];
    $sid = $_POST['sid'];
    $linkedin_id = $_POST['linkedin_id'];
    
    if (empty($sid)) {
        $seed = Seed::generateSeed();
        $hash = sha1($id. md5($linkedin_id). $seed['login']['seed']);
        $sid = $seed['login']['id'];
    }
    
    $_SESSION['yel']['member']['id'] = $id;
    $_SESSION['yel']['member']['hash'] = $hash;
    $_SESSION['yel']['member']['sid'] = $sid;
    $_SESSION['yel']['member']['linkedin_id'] = $linkedin_id;
    
    header('Content-type: text/xml');
    
    $member = new Member($id, $sid);
    // 1. find whether this member exists, from the ID
    $criteria = array(
        'columns' => "COUNT(*) AS is_exists", 
        'match' => "email_addr = '". $id. "'"
    );
    
    $result = $member->find($criteria);
    if ($result[0]['is_exists'] != '1') {
        // sign the member up
        $joined_on = today();
        $data = array();
        $data['password'] = md5(generate_random_string_of(6));
        $data['phone_num'] = '0';
        $data['firstname'] = $_POST['linkedin_firstname'];
        $data['lastname'] = $_POST['linkedin_lastname'];
        $data['linkedin_id'] = $linkedin_id;
        $data['joined_on'] = $joined_on;
        $data['updated_on'] = $joined_on;
        $data['active'] = 'Y';
        $data['checked_profile'] = 'Y';
        
        if (is_null($data['firstname']) || empty($data['firstname']) || 
            is_null($data['lastname']) || empty($data['lastname'])) {
            $data['firstname'] = 'Unknown';
            $data['lastname'] = 'Unknown';
        }
        
        if ($member->create($data) === false) {
            $_SESSION['yel']['member']['hash'] = "";
            $response['errors'] = array(
                'error' => 'create_error'
            );
            echo $xml_dom->get_xml_from_array($response);
            exit();
        } 
    } else {
        // reverse check by looking for linkedin_id from id.
        // if it is empty, then update. 
        // if it is not a match with the supplied linkedin_id, then error out
        $stored_linkedin_id = $member->getLinkedInId();
        if ($stored_linkedin_id !== false && is_null($stored_linkedin_id)) {
            // update
            $data = array();
            $data['linkedin_id'] = $linkedin_id;
            $member->setAdmin(true);
            if ($member->update($data) === false) {
                $_SESSION['yel']['member']['hash'] = "";
                $response['errors'] = array(
                    'error' => 'update_error'
                );
                echo $xml_dom->get_xml_from_array($response);
                exit();
            }
        } else {
            // matched?
            if ($stored_linkedin_id != $linkedin_id) {
                $_SESSION['yel']['member']['hash'] = "";
                $response['errors'] = array(
                    'error' => 'hacking_detected'
                );
                echo $xml_dom->get_xml_from_array($response);
                exit();
            }
        }
    }
    
    // 2. set session and go
    if (!$member->setSessionWith($hash)) {
        $_SESSION['yel']['member']['hash'] = "";
        $response['errors'] = array(
            'error' => 'bad_login'
        );
        echo $xml_dom->get_xml_from_array($response);
        exit();
    }
    
    $response['login'] = array('status' => 'ok');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'linkedin_auth') {
    $member = new Member(); 
    $email = $member->getEmailFromLinkedIn($_POST['id']);
    
    if ($email === false) {
        echo 'ko';
    } else {
        echo $email;
    }
    
    exit();
}
?>