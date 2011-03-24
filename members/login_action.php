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
?>