<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "SELECT 
          rewards.suggestid, 
          referrer.email_addr, 
          reward_payments.bank, 
          reward_payments.account_number, 
          reward_payments.payment, 
          reward_payments.pay_date, 
          reward_payments.pay_mode, 
          reward_payments.pay_id 
          from yel_dev.reward_payments
          left join yel_dev.rewards on rewards.reward_id = reward_payments.reward_id 
          left join yel_dev.hirer_confirms on hirer_confirms.suggestid = rewards.suggestid 
          left join yel_dev.referrer on referrer.userid = hirer_confirms.userid 
          where reward_payments.account_number is not null and reward_payments.account_number <> ''";
$mysqli = Database::connect();
$bank_accounts = array();
if ($bank_accounts = $mysqli->query($query)) {
    foreach ($bank_accounts as $bank_account) {
        $query = "INSERT INTO member_banks SET 
                  bank = '". $bank_account['bank']. "', 
                  account = '". $bank_account['account_number']. "', 
                  member = '". $bank_account['email_addr']. "' ";
        if (($id = $mysqli->execute($query, true)) > 0) {
            $query = "INSERT INTO referral_rewards SET 
                      referral = ". $bank_account['suggestid']. ", 
                      reward = ". $bank_account['payment']. ", 
                      paid_on = '". $bank_account['pay_date']. "', 
                      paid_through = '". $bank_account['pay_mode']. "', 
                      bank = ". $id. ", 
                      receipt = '". $bank_account['pay_id']. "'";
            if (!$mysqli->execute($query)) {
                echo "ko - referral_rewards_and_banks";
                exit();
            }
        }
    }
    
    echo "ok";
    exit();
} 

if (count($bank_accounts) == 0 && !$bank_accounts) {
    echo "ok - empty";
    exit();
}

echo "ko";

?>
