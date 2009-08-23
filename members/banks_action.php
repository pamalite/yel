<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id'])) {
    echo 'ko';
    exit();
    //redirect_to('login.php');
}

$xml_dom = new XMLDOM();

if (!isset($_POST['action'])) {
    $order_by = 'bank asc';

    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }

    $query = "SELECT * FROM member_banks 
              WHERE member = '". $_POST['id']. "' AND 
              in_used = 'Y' 
              ORDER BY ". $order_by;
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
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('bank_accounts' => array('bank_account' => $result)));
    exit();
}

if ($_POST['action'] == 'save_bank') {
    $member = new Member($_POST['member']);
    
    if ($_POST['id'] == '0') {
        if (!$member->create_bank($_POST['bank'], $_POST['account'])) {
            echo 'ko';
            exit();
        }
    } else {
        if (!$member->update_bank($_POST['id'], $_POST['bank'], $_POST['account'])) {
            echo 'ko';
            exit();
        }
    }
    
    echo 'ok';
    exit();     
}

if ($_POST['action'] == 'delete_bank') {
    $xml_dom->load_from_xml($_POST['payload']);
    $banks = $xml_dom->get('id');
    $query = "UPDATE member_banks SET in_used = 'N' WHERE id IN (";
    $i = 0;
    foreach ($banks as $id) {
        $query .= $id->nodeValue;
        
        if ($i < $banks->length-1) {
            $query .= ", ";
        }
        
        $i++;
    }
    $query .= ")";
    
    $mysqli = Database::connect();
    if (!$mysqli->execute($query)) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}
?>
