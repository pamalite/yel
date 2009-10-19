<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

session_start();
$today = now();
//$today = '0000-00-00 00:00:00'; // use this to temporarily bypass the date
$query = "SELECT COUNT(referrals.id) AS num_referrals
          FROM referrals 
          LEFT JOIN jobs ON jobs.id = referrals.job 
          LEFT JOIN resumes ON resumes.id = referrals.resume 
          WHERE jobs.employer = '". $_SESSION['yel']['employer']['id']. "' AND 
          (resumes.deleted = 'N' AND resumes.private = 'N') AND
          (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
          (referrals.member_confirmed_on IS NOT NULL AND referrals.member_confirmed_on <> '0000-00-00 00:00:00') AND 
          (referrals.employer_agreed_terms_on IS NULL OR referrals.employer_agreed_terms_on = '0000-00-00 00:00:00') AND 
          -- (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
          referrals.employer_removed_on IS NULL AND 
          (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00')";
$mysqli = Database::connect();
$result = $mysqli->query($query);
echo $result[0]['num_referrals'];
exit();
?>