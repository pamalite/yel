<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

session_start();

$xml_dom = new XMLDOM();
$counts = array();

$query = "SELECT referrals.id AS referral 
          FROM referrals 
          LEFT JOIN invoice_items ON invoice_items.item = referrals.id 
          LEFT JOIN invoices ON invoices.id = invoice_items.invoice 
          LEFT JOIN jobs ON jobs.id = referrals.job 
          LEFT JOIN employers ON employers.id = jobs.employer 
          LEFT JOIN employees ON employers.registered_by = employees.id 
          WHERE invoices.type = 'R' AND 
          (invoices.paid_on IS NOT NULL AND invoices.paid_on <> '0000-00-00 00:00:00') AND 
          (referrals.employed_on IS NOT NULL AND referrals.employed_on <> '0000-00-00 00:00:00') AND 
          (referrals.member_confirmed_on IS NOT NULL AND referrals.member_confirmed_on <> '0000-00-00 00:00:00') AND 
          (referrals.total_token_reward IS NOT NULL AND referrals.total_token_reward > 0) AND 
          (referrals.employer_removed_on IS NULL OR referrals.employer_removed_on = '0000-00-00 00:00:00') AND 
          (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
          (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') AND 
          (referrals.guarantee_expire_on <= CURDATE() OR referrals.guarantee_expire_on IS NULL) AND 
          employees.branch = ". $_SESSION['yel']['employee']['branch']['id']. " AND 
          referrals.id NOT IN (SELECT referral FROM referral_token_rewards) 
          GROUP BY referrals.id";
      
$mysqli = Database::connect();
$result = $mysqli->query($query);
echo (!count($result) <= 0 && !is_null($result)) ? count($result) : '0';
exit();
?>