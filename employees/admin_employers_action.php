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
    $order_by = 'employers.joined_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT employers.id, employers.name, employers.active, 
              DATE_FORMAT(employers.joined_on, '%e %b, %Y') AS formatted_joined_on, 
              (366 - DATEDIFF(NOW(), employers.joined_on)) AS days_left 
              FROM employers 
              LEFT JOIN employees ON employers.registered_by = employees.id 
              WHERE employees.branch = ". $_SESSION['yel']['employee']['branch']['id']. " AND
              employers.active = 'Y' 
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
        $result[$i]['employer'] = htmlspecialchars_decode($row['employer']);
    }
    
    $response = array('employers' => array('employer' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'reset_password') {
    $new_password = generate_random_string_of(6);
    $data = array();
    $data['password'] = md5($new_password);
    $employer = new Employer($_POST['id']);
    if (!$employer->update($data, true)) {
        echo "ko";
        exit();
    }
    
    $query = "SELECT email_addr FROM employers WHERE id = '". $_POST['id']. "' LIMIT 1";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    $lines = file(dirname(__FILE__). '/../private/mail/employer_password_reset_admin.txt');
    $message = '';
    foreach($lines as $line) {
        $message .= $line;
    }
    
    $message = str_replace('%user_id%', $_POST['id'], $message);
    $message = str_replace('%temporary_password%', $new_password, $message);
    $subject = "Employer Password Reset";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($result[0]['email_addr'], $subject, $message, $headers);
    
    echo 'ok';
    exit();
}

?>
