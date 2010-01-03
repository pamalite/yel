<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

$txn_log_file = '/var/log/paypal_ipn_txn.log';
$error_log_file = '/var/log/paypal_ipn_err.log';

if (isset($_POST['txn_id']) && isset($_POST['custom'])) {
    $employer_id = '';
    $price = '';
    $qty = '';
    $txn_id = $_POST['txn_id'];
    $amount = $_POST['mc_gross'];
    $purchased_on = now();
    
    // check whether does it has the 3 important variable
    $vars = explode('&', $_POST['custom']);
    if (count($vars) == 3) {
        foreach ($vars as $var) {
            $pass_thru = explode('=', $var);
            switch ($pass_thru[0]) {
                case 'employer':
                    $employer_id = $pass_thru[1];
                    break;
                case 'price':
                    $price = $pass_thru[1];
                    break;
                case 'qty':
                    $qty = $pass_thru[1];
                    break;
                default:
                    $handle = fopen($error_log_file, 'a');
                    fwrite($handle, date('Y-m-d H:i:s'). ' Invalid pass-thru variable found.'. "\n");
                    fclose($handle);
                    echo 'ko - Invalid pass-thru variable found.';
                    exit();
            }
        }
    } else {
        $handle = fopen($error_log_file, 'a');
        fwrite($handle, date('Y-m-d H:i:s'). ' Invalid _POST[custom] count.'. "\n");
        fclose($handle);
        echo 'ko - Invalid _POST[custom] count.';
        exit();
    }
    
    $employer = new Employer($employer_id);
    $mysqli = Database::connect();
    
    // get the billing email
    $query = "SELECT branches.country 
              FROM branches 
              INNER JOIN employees ON branches.id = employees.branch 
              INNER JOIN employers ON employees.id = employers.registered_by 
              WHERE employers.id = '". $employer_id. "' LIMIT 1";
    $result = $mysqli->query($query);
    $billing_email = 'billing.my@yellowelevator.com';
    if (!is_null($result[0]['country']) && !empty($result[0]['country'])) {
        $billing_email = 'billing.'. strtolower($result[0]['country']). '@yellowelevator.com';
    }
    
    // 1. Notify ourselves about Paypal Transaction
    $lines = file('../private/mail/paypal_payment_notification.txt');
    $message = '';
    foreach ($lines as $line) {
        $message .= $line;
    }
    
    $message = str_replace('%txn_id%', $txn_id, $message);
    $message = str_replace('%employer_id%', $employer_id, $message);
    $message = str_replace('%currency%', $_POST['mc_currency'], $message);
    $message = str_replace('%amount%', number_format($amount, '2', '.', ', '), $message);
    $message = str_replace('%qty%', $qty, $message);
    $message = str_replace('%price%', $price, $message);
    $message = str_replace('%purchased_on%', $purchased_on, $message);
    $subject = 'Paypal Transaction on '. $purchased_on;
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($billing_email, $subject, $message, $headers);

    // $handle = fopen('/tmp/email_to_'. $billing_email. '.txt', 'w');
    // fwrite($handle, 'Subject: '. $subject. "\n\n");
    // fwrite($handle, $message);
    // fclose($handle);    
    
    // 2. Update the purchase history
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
    fwrite($handle, date('Y-m-d H:i:s'). ' Either _POST[txn_id] or _POST[custom] is not found.'. "\n");
    fclose($handle);

    // $handle = fopen('/tmp/ipn_feedback.txt', 'w');
    // foreach ($_POST as $key=>$value) {
    //     fwrite($handle, '['. $key. '] => '. $value. "\n");
    // }
    // fclose($handle);
    
    echo 'ko - Either _POST[txn_id] or _POST[custom] is not found.';
    exit();
}
echo 'ok';
?>
