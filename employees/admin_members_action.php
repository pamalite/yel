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
    $order_by = 'joined_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT email_addr, active, 
              CONCAT(lastname, ', ', firstname) AS fullname, 
              DATE_FORMAT(joined_on, '%e %b, %Y') AS formatted_joined_on 
              FROM members 
              -- WHERE active = 'Y' OR active = 'S' 
              WHERE email_addr NOT LIKE 'team.%@yellowelevator.com' AND 
              email_addr <> 'initial@yellowelevator.com' 
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
        $result[$i]['fullname'] = htmlspecialchars_decode($row['fullname']);
    }
    
    $response = array('members' => array('member' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'reset_password') {
    $new_password = generate_random_string_of(6);
    $data = array();
    $data['password'] = md5($new_password);
    $member = new Member($_POST['id']);
    if (!$member->update($data, true)) {
        echo "ko";
        exit();
    }
    
    $lines = file(dirname(__FILE__). '/../private/mail/member_password_reset_admin.txt');
    $message = '';
    foreach($lines as $line) {
        $message .= $line;
    }
    
    $message = str_replace('%temporary_password%', $new_password, $message);
    $subject = "Member Password Reset";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($_POST['id'], $subject, $message, $headers);
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'activate') {
    $member = new Member($_POST['id']);
    $data = array();
    $data['password'] = md5($member->id());
    $data['active'] = 'Y';
        if (!$member->update($data, true)) {
        echo "ko";
        exit();
    }
    
    $lines = file(dirname(__FILE__). '/../private/mail/member_reactivated_admin.txt');
    $message = '';
    foreach($lines as $line) {
        $message .= $line;
    }
    
    $subject = "Membership Re-activated";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($_POST['id'], $subject, $message, $headers);
    
    // $handle = fopen('/tmp/email_to_'. $_POST['id']. '.txt', 'w');
    // fwrite($handle, 'Subject: '. $subject. "\n\n");
    // fwrite($handle, $message);
    // fclose($handle);
    
    echo 'ok';
    exit();
}

?>
