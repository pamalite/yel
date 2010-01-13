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
    $query = "SELECT number_of_slot, price_per_slot, total_amount, 
              DATE_FORMAT(purchased_on, '%e %b, %Y') AS formatted_purchased_on 
              FROM employer_slots_purchases 
              WHERE employer = '". $_POST['id']. "' AND on_hold = 0 
              ORDER BY purchased_on DESC";
    
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
    
    foreach ($result as $i=>$row) {
        $result[$i]['price_per_slot'] = number_format($row['price_per_slot'], 2, '.', ', ');
        $result[$i]['total_amount'] = number_format($row['total_amount'], 2, '.', ', ');;
    }
    
    $response = array('purchases' => array('purchase' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_slots_left') {
    $mysqli = Database::connect();
    $query = "SELECT YEAR(joined_on) AS joined_year, MONTH(joined_on) AS joined_month 
              FROM employers WHERE id = '". $_POST['id']. "'";
    $result = $mysqli->query($query);
    
    $is_prior = false;
    $is_expired = false;
    if ($result[0]['joined_year'] < 2010)  {
    // if (($result[0]['joined_year'] < 2010) || 
    //     ($result[0]['joined_year'] == 2010 && $result[0]['joined_month'] < 3)) {
        $is_prior = true;
        $query = "SELECT DATEDIFF(NOW(), DATE_ADD(joined_on, INTERVAL 1 YEAR)) AS expired 
                  FROM employers WHERE id = '". $_POST['id']. "'";
        $result = $mysqli->query($query);
        
        if ($result[0]['expired'] > 0) {
            $is_expired = true;
        }
    } 
    
    if (($is_prior && $is_expired) || (!$is_prior && !$is_expired)) {
        $employer = new Employer($_POST['id']);
        $result = $employer->get_slots_left();
    } else {
        echo '-1';
        exit();
    }
    
    $response = array('slots_info' => array('slots' => $result[0]['slots'], 
                                            'expired' => $result[0]['expired'], 
                                            'expire_on' => $result[0]['formatted_expire_on']));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'buy_slots') {
    $purchased_on = now();
    
    if ($_POST['payment_method'] == 'cheque') {
        // 1. add the slots to employer_slots_purchases as on_hold = true
        $pending_id = 'pend.'. generate_random_string_of(15);
        $query = "INSERT INTO employer_slots_purchases SET 
                  employer = '". $_POST['id']. "', 
                  transaction_id = '". $pending_id. "', 
                  purchased_on = '". $purchased_on. "', 
                  price_per_slot = ". $_POST['price']. ", 
                  number_of_slot = ". $_POST['qty']. ", 
                  total_amount = ". $_POST['amount']. ", 
                  on_hold = 1";
        $mysqli = Database::connect();
        if ($mysqli->execute($query) === true) {
            // 2. send payment instructions to employer
            $query = "SELECT name AS company, email_addr FROM employers WHERE id = '". $_POST['id']. "'";
            $result = $mysqli->query($query);
            $company = $result[0]['company'];
            $email_addr = $result[0]['email_addr'];
            
            $lines = file('../private/mail/employer_payment_instructions.txt');
            $message = '';
            foreach ($lines as $line) {
                $message .= $line;
            }
            
            $message = str_replace('%pending_id%', $pending_id, $message);
            $message = str_replace('%employer%', htmlspecialchars_decode($company), $message);
            $message = str_replace('%amount%', number_format($_POST['amount'], '2', '.', ', '), $message);
            $message = str_replace('%qty%', $_POST['qty'], $message);
            $message = str_replace('%currency%', $_POST['currency'], $message);
            $message = str_replace('%price%', $_POST['price'], $message);
            $message = str_replace('%purchased_on%', $purchased_on, $message);
            $subject = 'Job Slots Purchase Payment Instructions for Cheques/Money Order/Bank Transfer';
            $headers = 'From: YellowElevator.com <sales@yellowelevator.com>' . "\n";
            mail($email_addr, $subject, $message, $headers);

            /*$handle = fopen('/tmp/email_to_'. $email_addr. '.txt', 'w');
            fwrite($handle, 'Subject: '. $subject. "\n\n");
            fwrite($handle, $message);
            fclose($handle);*/
            
            echo '-1';
        } else {
            echo 'ko';
        }
        exit();
    }
    
    echo 'ok';
    exit();
}
?>
