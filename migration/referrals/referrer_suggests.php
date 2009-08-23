<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "INSERT INTO referrals SELECT 
          referrer_suggests.suggestid, 
          referrer.email_addr, 
          prospect.email_addr, 
          referrer_suggests.jobpostid, 
          NULL, 
          referrer_suggests.date_suggested, 
          referrer_suggests.date_interested, 
          NULL, 
          if ((referrer_suggests.hirer_verified = 1), now(), NULL) as agreed, 
          NULL, 
          NULL, 
          NULL, 
          NULL, 
          1, 
          0, 
          referrer_testimony.testimony,
          NULL, 
          'N' 
          from yel_dev.referrer_suggests
          left join yel_dev.referrer on referrer.userid = referrer_suggests.userid 
          left join yel_dev.prospect on prospect.prospectid = referrer_suggests.prospectid 
          left join yel_dev.referrer_testimony on referrer_testimony.suggestid = referrer_suggests.suggestid";
$mysqli = Database::connect();
if ($mysqli->execute($query)) {
    echo "ok";
} else {
    echo "ko";
}
?>
