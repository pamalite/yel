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
    $order_by = 'requested_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT candidates_with_no_contacts.id, candidates_with_no_contacts.resume, 
              candidates_with_no_contacts.member, jobs.title AS job_title, jobs.description, 
              CONCAT(members.lastname, ', ', members.firstname) AS fullname, members.phone_num, 
              DATE_FORMAT(candidates_with_no_contacts.requested_on, '%e %b, %Y') AS formatted_requested_on 
              FROM candidates_with_no_contacts 
              LEFT JOIN members ON members.email_addr = candidates_with_no_contacts.member 
              LEFT JOIN jobs ON jobs.id = candidates_with_no_contacts.job 
              WHERE (jobs.expire_on >= NOW() AND jobs.closed = 'N') 
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
        $result[$i]['description'] = htmlspecialchars_decode($row['description']);
    }
    
    $response = array('requests' => array('request' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}
?>
