<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

session_start();

$total = 0; 

$query = "SELECT COUNT(referral_requests.id) AS num_requests 
          FROM referral_requests 
          LEFT JOIN jobs ON jobs.id = referral_requests.job 
          LEFT JOIN member_referees ON member_referees.member = referral_requests.referrer AND 
          member_referees.referee = referral_requests.member
          WHERE referral_requests.referrer = '". $_SESSION['yel']['member']['id']. "' AND 
          member_referees.member = '". $_SESSION['yel']['member']['id']. "' AND 
          referral_requests.rejected = 'N' AND 
          -- referral_requests.requests_counted = FALSE AND 
          (referral_requests.referrer_read_resume_on IS NULL OR referral_requests.referrer_read_resume_on = '0000-00-00 00:00:00') AND 
          (referral_requests.referrer_acknowledged_on IS NULL OR referral_requests.referrer_acknowledged_on = '0000-00-00 00:00:00') AND 
          (referral_requests.acknowledged_by_others_on IS NULL OR referral_requests.acknowledged_by_others_on = '0000-00-00 00:00:00')
          AND jobs.closed = 'N' AND jobs.expire_on >= NOW()";
      
$mysqli = Database::connect();
$result = $mysqli->query($query);
if (count($result) <= 0 && is_null($result)) {
    $total += 0;
} else {
    $total += $result[0]['num_requests'];
}

$query = "SELECT COUNT(referrals.id) AS num_requests 
          FROM referrals 
          LEFT JOIN jobs ON jobs.id = referrals.job 
          LEFT JOIN member_referees ON member_referees.member = referrals.member AND 
          member_referees.referee = referrals.referee
          WHERE referrals.member = '". $_SESSION['yel']['member']['id']. "' AND 
          member_referees.member = '". $_SESSION['yel']['member']['id']. "' AND 
          -- referrals.request_counted = FALSE AND 
          (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
          (referrals.member_read_resume_on IS NULL OR referrals.member_read_resume_on = '0000-00-00 00:00:00') AND 
          (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
          (referrals.member_confirmed_on IS NULL OR referrals.member_confirmed_on = '0000-00-00 00:00:00') 
          AND jobs.closed = 'N' AND jobs.expire_on >= NOW()";

$result = $mysqli->query($query);
if (count($result) <= 0 && is_null($result)) {
  $total += 0;
} else {
  $total += $result[0]['num_requests'];
}

echo $total;
exit();
?>