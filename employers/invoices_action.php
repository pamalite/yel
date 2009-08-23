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
    $order_by = 'issued_on desc';
    $paid_on_clause = "paid_on IS NULL";
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    if (isset($_POST['paid_invoices'])) {
        $paid_on_clause = "paid_on IS NOT NULL";
    }
    
    $query = "SELECT id, type, payable_by, 
              DATE_FORMAT(issued_on, '%e %b, %Y') AS formatted_issued_on, 
              DATE_FORMAT(payable_by, '%e %b, %Y') AS formatted_payable_by,
              DATE_FORMAT(paid_on, '%e %b, %Y') AS formatted_paid_on 
              FROM invoices 
              WHERE employer = '". $_POST['id']. "' AND ". $paid_on_clause. "
              ORDER BY ". $_POST['order_by'];
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if (count($result) <= 0 || is_null($result)) {
        echo "0";
        exit();
    }
    
    if (!$result) {
        echo "ko";
        exit();
    }
    
    $today = today();
    foreach($result as $i=>$row) {
        $result[$i]['padded_id'] = pad($row['id'], 11, '0');
        $delta = date_diff($today, $row['payable_by']);
        if ($delta > 0) {
            $result[$i]['expired'] = 'expired';
        } else if ($delta == 0) {
            $result[$i]['expired'] = 'nearly';
        } else {
            $result[$i]['expired'] = 'no';
        }
    }
    
    $response = array('invoices' => array('invoice' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

?>
