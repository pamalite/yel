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
    redirect_to('invoices.php');
}

if ($_POST['action'] == 'get_invoices') {
    $order_by = 'issued_on desc';
    $paid_on_clause = "paid_on IS NULL";
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    if (isset($_POST['paid_invoices'])) {
        $paid_on_clause = "paid_on IS NOT NULL";
    }
    
    $criteria = array(
        'columns' => "id, type, DATEDIFF(payable_by, now()) AS expired, 
                      DATE_FORMAT(issued_on, '%e %b, %Y') AS formatted_issued_on, 
                      DATE_FORMAT(payable_by, '%e %b, %Y') AS formatted_payable_by,
                      DATE_FORMAT(paid_on, '%e %b, %Y') AS formatted_paid_on",
        'match' => "employer = '". $_POST['id']. "' AND ". $paid_on_clause, 
        'order' => $order_by
    );
    
    $result = Invoice::find($criteria);
    if (is_null($result) || empty($result)) {
        echo '0';
        exit();
    }
    
    if ($result === false) {
        echo 'ko';
        exit();
    }
    
    foreach ($result as $i=>$row) {
        $result[$i]['padded_id'] = pad($row['id'], 11, '0');
        
        $type = 'Others';
        switch ($row['type']) {
            case 'R':
                $type = 'Service Fee';
                break;
            case 'J':
                $type = 'Subscription';
                break;
            case 'P':
                $type = 'Job Posting';
                break;
        }
        $result[$i]['type'] = $type;
    }
    
    $response = array('invoices' => array('invoice' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}
?>