<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

session_start();

$xml_dom = new XMLDOM();
$counts = array();

$query = "SELECT COUNT(referrals.id) AS num_responses 
          FROM referrals 
          LEFT JOIN jobs ON jobs.id = referrals.job 
          LEFT JOIN member_referees ON member_referees.member = referrals.member AND 
          member_referees.referee = referrals.referee 
          WHERE referrals.member = '". $_SESSION['yel']['member']['id']. "' AND 
          member_referees.member = '". $_SESSION['yel']['member']['id']. "' AND 
          referrals.response_counted = false AND 
          (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
          (referrals.work_commence_on IS NULL OR referrals.work_commence_on = '0000-00-00 00:00:00') AND 
          (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
          (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
          (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') AND 
          (referrals.employer_agreed_terms_on IS NULL OR referrals.employer_agreed_terms_on = '0000-00-00 00:00:00') 
          AND jobs.closed = 'N' AND jobs.expire_on >= NOW()";
      
$mysqli = Database::connect();
$result = $mysqli->query($query);
if (!count($result) <= 0 && !is_null($result)) {
    $counts['num_responses'] = $result[0]['num_responses'];
} else {
    $counts['num_responses'] = '0';
}

$query = "SELECT COUNT(referrals.id) AS num_views 
          FROM referrals 
          LEFT JOIN jobs ON jobs.id = referrals.job 
          LEFT JOIN member_referees ON member_referees.member = referrals.member AND 
          member_referees.referee = referrals.referee 
          WHERE referrals.member = '". $_SESSION['yel']['member']['id']. "' AND 
          member_referees.member = '". $_SESSION['yel']['member']['id']. "' AND 
          referrals.view_counted = false AND 
          (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
          (referrals.work_commence_on IS NULL OR referrals.work_commence_on = '0000-00-00 00:00:00') AND 
          (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
          (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
          (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') AND 
          (referrals.employer_agreed_terms_on IS NOT NULL AND referrals.employer_agreed_terms_on <> '0000-00-00 00:00:00') 
          AND jobs.closed = 'N' AND jobs.expire_on >= NOW()";

$result = $mysqli->query($query);
if (!count($result) <= 0 && !is_null($result)) {
    $counts['num_views'] = $result[0]['num_views'];
} else {
    $counts['num_views'] = '0';
}

$query = "SELECT COUNT(referrals.id) AS num_rewards 
          FROM referrals 
          LEFT JOIN jobs ON jobs.id = referrals.job 
          LEFT JOIN member_referees ON member_referees.member = referrals.member AND 
          member_referees.referee = referrals.referee 
          WHERE referrals.member = '". $_SESSION['yel']['member']['id']. "' AND 
          member_referees.member = '". $_SESSION['yel']['member']['id']. "' AND
          referrals.reward_counted = false AND 
          (referrals.employed_on IS NOT NULL AND referrals.employed_on <> '0000-00-00 00:00:00') AND 
          (referrals.work_commence_on IS NOT NULL AND referrals.work_commence_on <> '0000-00-00 00:00:00') AND 
          (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') 
          AND jobs.closed = 'N' AND jobs.expire_on >= NOW()";

$result = $mysqli->query($query);
if (!count($result) <= 0 && !is_null($result)) {
    $counts['num_rewards'] = $result[0]['num_rewards'];
} else {
    $counts['num_rewards'] = '0';
}

header('Content-type: text/xml');
echo $xml_dom->get_xml_from_array(array('counts' => $counts));
exit();
?>