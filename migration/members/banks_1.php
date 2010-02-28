<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "SELECT * FROM member_banks";
$mysqli = Database::connect();
$accounts = $mysqli->query($query);

$current_member = '';
$account_ids = array();
foreach ($accounts as $account) {
    if ($account['member'] != $current_member) {
        $current_member = $account['member'];
    } else {
        $account_ids[] = $account['id'];
    }
}

$query = "DELETE FROM member_banks WHERE id IN (". implode(', ', $account_ids). ")";
echo $query. '<br/><br/>';
// $mysqli->execute($account_ids);

echo "ok";
?>
