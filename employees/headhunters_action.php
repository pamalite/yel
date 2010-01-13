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
    
    $query = "SELECT members.email_addr, members.phone_num, countries.country, 
              CONCAT(members.lastname, ', ', members.firstname) AS fullname, 
              DATE_FORMAT(members.joined_on, '%e %b, %Y') AS formatted_joined_on 
              FROM members 
              INNER JOIN countries ON countries.country_code = members.country 
              WHERE members.individual_headhunter = 'Y' 
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

?>
