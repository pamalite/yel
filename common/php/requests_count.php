<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

session_start();

$xml_dom = new XMLDOM();
$total = 0; 

$query = "SELECT COUNT(referral_requests.id) AS num_requests 
          FROM referral_requests 
          LEFT JOIN jobs ON jobs.id = referral_requests.job 
          LEFT JOIN member_referees ON member_referees.member = referral_requests.referrer AND 
          member_referees.referee = referral_requests.member
          WHERE referral_requests.referrer = '". $_SESSION['yel']['member']['id']. "' AND 
          member_referees.member = '". $_SESSION['yel']['member']['id']. "' AND 
          referral_requests.rejected = 'N' AND 
          referral_requests.requests_counted = FALSE AND 
          (referral_requests.referrer_acknowledged_on IS NULL OR referral_requests.referrer_acknowledged_on = '0000-00-00 00:00:00') AND 
          (referral_requests.acknowledged_by_others_on IS NULL OR referral_requests.acknowledged_by_others_on = '0000-00-00 00:00:00')
          AND jobs.closed = 'N' AND jobs.expire_on >= NOW()";
      
$mysqli = Database::connect();
$result = $mysqli->query($query);
if (count($result) <= 0 && is_null($result)) {
    echo '0';
} else {
    echo $result[0]['num_requests'];
}

exit();
?>