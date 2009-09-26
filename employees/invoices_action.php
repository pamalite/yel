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
    $paid_on_clause = "invoices.paid_on IS NULL";
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    if (isset($_POST['paid_invoices'])) {
        $paid_on_clause = "invoices.paid_on IS NOT NULL";
    }
    
    $query = "SELECT invoices.id, invoices.type, invoices.payable_by, employers.name AS employer, 
              SUM(invoice_items.amount) AS amount_payable, currencies.symbol AS currency, 
              DATE_FORMAT(invoices.issued_on, '%e %b, %Y') AS formatted_issued_on, 
              DATE_FORMAT(invoices.payable_by, '%e %b, %Y') AS formatted_payable_by,
              DATE_FORMAT(invoices.paid_on, '%e %b, %Y') AS formatted_paid_on 
              FROM invoices 
              LEFT JOIN employers ON employers.id = invoices.employer 
              LEFT JOIN invoice_items ON invoice_items.invoice = invoices.id 
              LEFT JOIN employees ON employers.registered_by = employees.id 
              LEFT JOIN currencies ON currencies.country_code = employers.country 
              WHERE ". $paid_on_clause. " AND 
              employees.branch = ". $_SESSION['yel']['employee']['branch']['id']. " 
              GROUP BY invoices.id 
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
    
    $today = today();
    foreach($result as $i=>$row) {
        $result[$i]['padded_id'] = pad($row['id'], 11, '0');
        $result[$i]['amount_payable'] = number_format($row['amount_payable'], 2, '.', ', ');
        $delta = sql_date_diff($today, $row['payable_by']);
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

if ($_POST['action'] == 'confirm_payment') {
    $data = array();
    $data['id'] = $_POST['id'];
    $data['paid_on'] = $_POST['paid_on'];
    $data['paid_through'] = $_POST['paid_through'];
    $data['paid_id'] = $_POST['paid_id'];
    
    if (!Invoice::update($data)) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}
?>
