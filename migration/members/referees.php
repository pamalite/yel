<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "INSERT INTO member_referees SELECT 
          0,
          referrer.email_addr, 
          prospect.email_addr, 
          prosprefer.indate, 
          prosprefer.hidden,
          'Y'
          from 
          yel_dev.prosprefer 
          left join yel_dev.prospect  on prospect.prospectid = prosprefer.prospectid 
          left join yel_dev.referrer on referrer.userid = prosprefer.userid";
$mysqli = Database::connect();
if ($mysqli->execute($query)) {
    echo "ok";
} else {
    echo "ko";
}
?>
