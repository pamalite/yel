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
    $order_by = 'members.joined desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT members.email_addr AS member_email_addr, members.phone_num AS member_phone_num, 
              concat(members.firstname, ', ', members.lastname) AS candidate_name, 
              date_format(members.joined_on, '%e %b, %Y') AS formatted_joined_on, 
              recommenders.email_addr AS recommender_email_addr, 
              recommenders.phone_num AS recommender_phone_num, 
              concat(recommenders.firstname, ', ', recommenders.lastname) AS recommender_name  
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
        $result[$i]['candidate'] = htmlspecialchars_decode($row['candidate']);
        $result[$i]['recommender'] = htmlspecialchars_decode($row['recommender']);
    }
    
    $response = array('candidates' => array('candidate' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}
?>
