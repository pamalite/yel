<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "SELECT 
          rewards.suggestid, 
          reward_payments.payment, 
          reward_payments.pay_date, 
          reward_payments.pay_mode, 
          reward_payments.pay_id 
          from yel_dev.reward_payments
          left join yel_dev.rewards on rewards.reward_id = reward_payments.reward_id 
          left join yel_dev.hirer_confirms on hirer_confirms.suggestid = rewards.suggestid 
          left join yel_dev.referrer on referrer.userid = hirer_confirms.userid 
          where reward_payments.account_number is null or reward_payments.account_number = ''";
$mysqli = Database::connect();
$payments = array();
if ($payments = $mysqli->query($query)) {
    foreach ($payments as $payment) {
        $query = "INSERT INTO referral_rewards SET 
                  referral = ". $payment['suggestid']. ", 
                  reward = ". $payment['payment']. ", 
                  paid_on = '". $payment['pay_date']. "', 
                  paid_through = '". $payment['pay_mode']. "', ";
        
        if ($payment['pay_mode'] == 'CHQ') {
            $query .= "cheque = '". $payment['pay_id']. "' ";
        } else {
            $query .= "receipt = '". $payment['pay_id']. "' ";
        }
        
        if (!$mysqli->execute($query)) {
            echo "ko - referral_rewards_not_banks";
            exit();
        }
    }
    
    echo "ok";
    exit();
} 

if (count($payments) == 0 && !$payments) {
    echo "ok - empty";
    exit();
}


echo "ko";

?>
