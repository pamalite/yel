<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id'])) {
    redirect_to('members.php');
}

if (!isset($_POST['action'])) {
    redirect_to('members.php');
}

$xml_dom = new XMLDOM();

if ($_POST['action'] == 'get_members') {
    $order_by = 'members.joined_on desc';

    $employee = new Employee($_POST['id']);
    $branch = $employee->getBranch();

    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }

    $criteria = array(
        'columns' => "members.email_addr, members.phone_num, members.active, members.phone_num,
                      members.address, members.state, members.zip, countries.country, 
                      DATE_FORMAT(members.joined_on, '%e %b, %Y') AS formatted_joined_on, 
                      DATE_FORMAT(member_sessions.last_login, '%e %b, %Y') AS formatted_last_login, 
                      CONCAT(members.lastname, ', ', members.firstname) AS member, 
                      CONCAT(employees.lastname, ', ', employees.firstname) AS employee",
        'joins' => "member_sessions ON member_sessions.member = members.email_addr, 
                    employees ON employees.id = members.added_by, 
                    countries ON countries.country_code = members.country", 
        'match' => "employees.branch = ". $branch[0]['id'] ." AND 
                    members.email_addr <> 'initial@yellowelevator.com'",
        'order' => $order_by
    );

    $member = new Member();
    $result = $member->find($criteria);

    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }

    if (!$result) {
        echo 'ko';
        exit();
    }

    foreach($result as $i=>$row) {
        $result[$i]['member'] = htmlspecialchars_decode($row['member']);
    }

    $response = array('members' => array('a_member' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'deactivate') {
    $data = array();
    $data['active'] = 'N';
    
    $member = new Member($_POST['id']);
    $member->setAdmin(true);
    if ($member->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'activate') {
    $new_password = generate_random_string_of(6);
    $data = array();
    $data['active'] = 'Y';
    $data['password'] = md5($new_password);
    
    $member = new Employer($_POST['id']);
    $member->setAdmin(true);
    if ($member->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    $lines = file(dirname(__FILE__). '/../private/mail/member_password_reset_admin.txt');
    $message = '';
    foreach($lines as $line) {
        $message .= $line;
    }
    
    $message = str_replace('%temporary_password%', $new_password, $message);
    $subject = "Employer Password Reset";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    // mail($member->getEmailAddress(), $subject, $message, $headers);
    
    $handle = fopen('/tmp/email_to_'. $member->getEmailAddress(). '.txt', 'w');
    fwrite($handle, 'Subject: '. $subject. "\n\n");
    fwrite($handle, $message);
    fclose($handle);
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'reset_password') {
    $new_password = generate_random_string_of(6);
    $data = array();
    $data['password'] = md5($new_password);
    $member = new Member($_POST['id']);
    $member->setAdmin(true);
    if ($member->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    $lines = file(dirname(__FILE__). '/../private/mail/member_password_reset_admin.txt');
    $message = '';
    foreach($lines as $line) {
        $message .= $line;
    }
    
    $message = str_replace('%temporary_password%', $new_password, $message);
    $subject = "Employer Password Reset";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    // mail($member->getEmailAddress(), $subject, $message, $headers);
    
    $handle = fopen('/tmp/email_to_'. $member->getEmailAddress(). '.txt', 'w');
    fwrite($handle, 'Subject: '. $subject. "\n\n");
    fwrite($handle, $message);
    fclose($handle);
    
    echo 'ok';
    exit();
}
?>
