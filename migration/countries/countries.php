<?php
require_once dirname(__FILE__)."/../../private/lib/utilities.php";

$countries_to_show = array('MY', 'SG', 'AU');
$query = "SELECT * FROM yel_dev.countrylist_1";
$mysqli = Database::connect();
$countries = $mysqli->query($query);
$i = 0;
foreach ($countries as $country) {
    $countries[$i]['name'] = trim($country['name']);
    $i++;
}

$query = "INSERT INTO countries VALUES ";
$i = 0;
foreach ($countries as $country) {
    $show_in_list = in_array($country['country_code']) ? 'Y' : 'N';
    $query .= "('". $country['country_code']. "', '". $country['name']. "', NULL, '". $show_in_list. "')";
    
    if ($i < count($countries)-1) {
        $query .= ", ";
    }
    
    $i++;
}

if ($mysqli->execute($query)) {
    echo "ok";
} else {
    echo "ko";
}
?>