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
    $order_by = 'purchased_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT employer AS employer_id, employers.name AS employer, 
              employer_slots_purchases.transaction_id, 
              employer_slots_purchases.number_of_slot, 
              employer_slots_purchases.price_per_slot, 
              employer_slots_purchases.total_amount, 
              employer_slots_purchases.on_hold, 
              currencies.symbol AS currency, 
              DATE_FORMAT(employer_slots_purchases.purchased_on, '%e %b, %Y') AS formatted_purchased_on 
              FROM employer_slots_purchases 
              INNER JOIN employers ON employers.id = employer_slots_purchases.employer 
              INNER JOIN currencies ON currencies.country_code = employers.country 
              INNER JOIN employees ON employers.registered_by = employees.id 
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
        $result[$i]['employer'] = htmlspecialchars_decode($row['employer']);
    }
    
    $response = array('purchases' => array('purchase' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'confirm_payment') {
    $employer = new Employer($_POST['id']);
    
    $mysqli = Database::connect();
    $query = "SELECT number_of_slot FROM employer_slots_purchases 
              WHERE employer = '". $employer->id(). "' AND transaction_id = '". $_POST['txn_id']. "' LIMIT 1";
    $result = $mysqli->query($query);
    
    $employer->add_slots($result[0]['number_of_slot']);
    
    $query = "UPDATE employer_slots_purchases SET 
              transaction_id = '". $_POST['payment_id']. "', 
              purchased_on = NOW(), 
              on_hold = 0 
              WHERE employer = '". $employer->id(). "' AND transaction_id = '". $_POST['txn_id']. "'";
    if ($mysqli->execute($query) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}
?>
