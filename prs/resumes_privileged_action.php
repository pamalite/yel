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
        $result[$i]['candidate'] = htmlspecialchars_decode($row['candidate']);
        $result[$i]['recommender'] = htmlspecialchars_decode($row['recommender']);
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
            $profile[$key] = htmlspecialchars_decode(html_entity_decode(desanitize($value)));
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

?>
