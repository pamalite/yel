<?php
require_once "../private/lib/utilities.php";

$mysqli = Database::connect();
$query = "SELECT recommender FROM members WHERE email_addr = 'pamalite@gmail.com.' LIMIT 1";
$result = $mysqli->query($query);
echo '<pre>Has recommender: ';
print_r((empty($result)) ? 'NO' : 'YES');
echo '</pre>';
?>
