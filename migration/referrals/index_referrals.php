<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

// make index of referrals from their notes and testimony

$mysqli = Database::connect();

// 1. Get all referrals and their notes and testimony
$query = "SELECT id, testimony
          FROM referrals";
$result = $mysqli->query($query);

if (is_null($result) || empty($result)) {
    echo 'Error: No referrals found.<br/><br/>';
    exit();
}

// 2. Remove the html tags
$sanitized_referrals = array();
foreach ($result as $i=>$row) {
    $row['testimony'] = htmlspecialchars_decode($row['testimony']);
    $row['testimony'] = str_replace('<br/>', "\n", $row['testimony']);
    $row['testimony'] = strip_tags($row['testimony']);
    $sanitized_referrals[$i] = $row;
}

// 3. Insert into index
$query = "INSERT INTO referral_index VALUES ";
foreach ($sanitized_referrals as $i=>$referral) {
    $query .= "(". $referral['id']. ", '". $referral['testimony']. "')";
    if ($i < count($sanitized_referrals)-1) {
        $query .= ", ";
    }
}
$mysqli->execute($query);

echo 'Finish';
?>
