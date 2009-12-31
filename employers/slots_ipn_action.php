<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

$txn_log_file = '/var/log/paypal_ipn_txn.log';
$error_log_file = '/var/log/paypal_ipn_err.log';

if (isset($_POST['txn_id']) && 
    isset($_GET['employer']) && 
    isset($_GET['price']) && 
    isset($_GET['qty'])) {
    $txn_id = $_POST['txn_id'];
    $employer_id = $_GET['employer'];
    $price = $_GET['price'];
    $qty = $_GET['qty'];
    $amount = $_POST['mc_gross'];
    $purchased_on = now();
    
    $employer = new Employer($employer_id);
    
    // 1. Notify ourselves about Paypal Transaction
    $lines = file('../private/mail/paypal_payment_notification.txt');
    $message = '';
    foreach ($lines as $line) {
        $message .= $line;
    }
    
    $message = str_replace('%txn_id%', $txn_id, $message);
    $message = str_replace('%employer_id%', $employer_id, $message);
    $message = str_replace('%currency%', Currency::symbol_from_country_code($employer->get_country_code()), $message);
    $message = str_replace('%amount%', number_format($amount, '2', '.', ', '), $message);
    $message = str_replace('%qty%', $qty, $message);
    $message = str_replace('%price%', $price, $message);
    $message = str_replace('%purchased_on%', $purchased_on, $message);
    $subject = 'Paypal Transaction on '. $purchased_on;
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail('sui.cheng.wong@yellowelevator.com', $subject, $message, $headers);

    /*$handle = fopen('/tmp/email_to_ken.sng.wong@yellowelevator.com.txt', 'w');
    fwrite($handle, 'Subject: '. $subject. "\n\n");
    fwrite($handle, $message);
    fclose($handle);*/
    
    // 2. Update the purchase history
    $mysqli = Database::connect();
    $query = "INSERT INTO employer_slots_purchases SET 
              employer = '". $employer_id. "', 
              transaction_id = '". $txn_id. "', 
              purchased_on = '". $purchased_on. "', 
              price_per_slot = ". $price. ", 
              number_of_slot = ". $qty. ", 
              total_amount = ". $amount. ", 
              on_hold = 0";
    if ($mysqli->execute($query) === false) {
        $handle = fopen($error_log_file, 'a');
        fwrite($handle, date('Y-m-d H:i:s'). ' Unable to log transaction (txn_id: '. $txn_id. ') for employer '. $employer_id. '.'. "\n");
        fclose($handle);
        exit();
    }
    
    $handle = fopen($txn_log_file, 'a');
    fwrite($handle, date('Y-m-d H:i:s'). ' Logged transaction (txn_id: '. $txn_id. ') for employer '. $employer_id. '.'. "\n");
    fclose($handle);
    
    // 3. Update the slots
    if ($employer->add_slots($qty) === false) {
        $handle = fopen($error_log_file, 'a');
        fwrite($handle, date('Y-m-d H:i:s'). ' Unable to add '. $qty. ' slots for employer '. $employer_id. '.'. "\n");
        fclose($handle);
        exit();
    }
    
    $handle = fopen($txn_log_file, 'a');
    fwrite($handle, date('Y-m-d H:i:s'). ' Added '. $qty. ' slots for employer '. $employer_id. '.'. "\n");
    fclose($handle);
} else {
    $handle = fopen($error_log_file, 'a');
    fwrite($handle, date('Y-m-d H:i:s'). ' txn_id not found.'. "\n");
    fclose($handle);
}
echo 'ok';
?>
