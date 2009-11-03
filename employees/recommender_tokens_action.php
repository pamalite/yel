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
    $order_by = 'recommender_tokens.presented_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT recommender_tokens.token AS token_presented, recommenders.phone_num, recommenders.email_addr, 
              CONCAT(recommenders.lastname, ', ', recommenders.firstname) AS recommender, 
              DATE_FORMAT(recommender_tokens.presented_on, '%e %b, %Y') AS formatted_presented_on 
              FROM recommender_tokens 
              LEFT JOIN recommenders ON recommenders.email_addr = recommender_tokens.recommender 
              LEFT JOIN employees ON recommenders.added_by = employees.id 
              WHERE employees.branch = ". $_SESSION['yel']['employee']['branch']['id']. " 
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
        $result[$i]['recommender'] = htmlspecialchars_decode(stripslashes(desanitize($row['recommender'])));
    }
    
    $response = array('tokens' => array('token' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

?>
