<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id'])) {
    redirect_to('payments.php');
}

if (!isset($_POST['action'])) {
    redirect_to('payments.php');
}

function get_payments($_is_invoice = true, $_order = "invoices.issued_on", 
                      $_employer_to_filter = '') {
    $criteria = array(
        "columns" => "invoices.id, invoices.type, invoices.payable_by,
                      employers.name AS employer, employers.contact_person, employers.email_addr, 
                      employers.fax_num, employers.phone_num,
                      SUM(invoice_items.amount) AS amount_payable, currencies.symbol AS currency, 
                      DATE_FORMAT(invoices.issued_on, '%e %b, %Y') AS formatted_issued_on, 
                      DATE_FORMAT(invoices.payable_by, '%e %b, %Y') AS formatted_payable_by,
                      DATE_FORMAT(invoices.paid_on, '%e %b, %Y') AS formatted_paid_on", 
        "joins" => "employers ON employers.id = invoices.employer, 
                    branches ON branches.id = employers.branch, 
                    invoice_items ON invoice_items.invoice = invoices.id, 
                    currencies ON currencies.country_code = branches.country", 
        "group" => "invoices.id", 
        "order" => $_order
    );
    
    if ($_is_invoice) {
        $criteria['match'] = "invoices.paid_on IS NULL";
    } else {
        $criteria['columns'] .= ", invoices.paid_through, invoices.paid_id";
        $criteria['match'] = "invoices.paid_on IS NOT NULL";
    }
    
    if (!empty($_employer_to_filter)) {
        $criteria['match'] .= " AND invoices.employer = '". $_employer_to_filter. "'";
    }
    
    return Invoice::find($criteria);
}

$xml_dom = new XMLDOM();

if ($_POST['action'] == 'get_invoices') {
    $result = get_payments(true, $_POST['order_by'], $_POST['filter']);
    
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
        $result[$i]['employer'] = htmlspecialchars_decode(stripslashes($row['employer']));
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

if ($_POST['action'] == 'get_receipts') {
    $result = get_payments(false, $_POST['order_by'], $_POST['filter']);
    
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
        $result[$i]['employer'] = htmlspecialchars_decode(stripslashes($row['employer']));
        $result[$i]['padded_id'] = pad($row['id'], 11, '0');
        $result[$i]['amount_payable'] = number_format($row['amount_payable'], 2, '.', ', ');
    }
    
    $response = array('receipts' => array('receipt' => $result));
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
