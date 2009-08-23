<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();
$xml_dom = new XMLDOM();
$response = array();

if (!isset($_POST['action'])) {
    header('Content-type: text/xml');
    if (!isset($_POST['id']) && !isset($_POST['hash']) && !isset($_POST['sid'])) {
        $response['errors'] = array(
            'error' => 'Email and Password fields cannot be empty.'
        );
        echo $xml_dom->get_xml_from_array($response);
        exit();
        //redirect_to('login.php');
    }

    $id = $_POST['id'];
    $hash = $_POST['hash'];
    $sid = $_POST['sid'];

    $_SESSION['yel']['member']['id'] = $id;
    $_SESSION['yel']['member']['hash'] = $hash;
    $_SESSION['yel']['member']['sid'] = $sid;

    $member = new Member($id, $sid);
    if (!$member->is_active()) {
        $_SESSION['yel']['member']['hash'] = "";
        $response['errors'] = array(
            'error' => 'The provided credentials are marked as inactive or suspended.&nbsp;<br/>&nbsp;Please contact the administrator for further assistance.'
        );
        echo $xml_dom->get_xml_from_array($response);
        exit();
        //redirect_to('login.php?invalid=1');
    }
    
    if (!$member->is_registered($hash)) {
        $_SESSION['yel']['member']['hash'] = "";
        $response['errors'] = array(
            'error' => 'The provided credentials are invalid. Please try again.'
        );
        echo $xml_dom->get_xml_from_array($response);
        exit();
        //redirect_to('login.php?invalid=1');
    } 

    if (!$member->session_set($hash)) {
        $_SESSION['yel']['member']['hash'] = "";
        $response['errors'] = array(
            'error' => 'bad_login'
        );
        echo $xml_dom->get_xml_from_array($response);
        exit();
        //redirect_to('../errors/failed_login.php?dir=members');
    }
    
    $response['login'] = array('status' => 'ok');
    echo $xml_dom->get_xml_from_array($response);
    //redirect_to('home.php');
    exit();
}

if ($_POST['action'] == 'get_password_hint') {
    $query = "SELECT password_reset_questions.* 
              FROM password_reset_questions 
              LEFT JOIN members ON password_reset_questions.id = members.forget_password_question 
              WHERE members.email_addr = '". $_POST['email_addr']. "' LIMIT 1"; 
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
    
    header('Content-type: text/xml');
    $response = array('reset_password' => array('id' => $result[0]['id'], 'hint' => $result[0]['question']));
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'reset_password') {
    $is_valid = false;
    $query = "SELECT forget_password_answer FROM members 
              WHERE members.email_addr = '". $_POST['email_addr']. "' LIMIT 1"; 
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if (strtoupper($result[0]['forget_password_answer']) == strtoupper($_POST['answer'])) {
        $is_valid = true;
    }
    
    if ($is_valid) {
        $temp_password = generate_random_string_of(6);
        $data = array();
        $data['password'] = md5($temp_password);

        $member = new Member($_POST['email_addr']);
        if (!$member->update($data, true)) {
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
        
        echo (mail($_POST['email_addr'], $subject, $message, $headers)) ? 'ok' : 'ko';
        exit();
    } else {
        echo 'bad';
        exit();
    }
}

?>
