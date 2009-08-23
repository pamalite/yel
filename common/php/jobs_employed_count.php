<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

session_start();

$query = "SELECT COUNT(referrals.id) AS num_jobs_employed 
          FROM referrals 
          WHERE referrals.referee = '". $_SESSION['yel']['member']['id']. "' AND 
          (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
          (referrals.employer_agreed_terms_on IS NOT NULL OR referrals.employer_agreed_terms_on <> '0000-00-00 00:00:00') AND 
          (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
          referrals.employer_rejected_on IS NULL";
$mysqli = Database::connect();
$result = $mysqli->query($query);
echo $result[0]['num_jobs_employed'];
?>