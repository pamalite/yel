<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$new_rates = array();

//Read eurofxref-daily.xml file in memory 
$XMLContent= file("http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml");
//the file is updated daily between 14:15 and 15:00 CET

foreach ($XMLContent as $line) {
    if (ereg("currency='([[:alpha:]]+)'",$line,$currencyCode)) {
        if (ereg("rate='([[:graph:]]+)'",$line,$rate)) {
            $new_rates[$currencyCode[1]] = $rate[1];
        }
    }
}
$mysqli = Database::connect();
$query = "SELECT DISTINCT symbol FROM currencies";
$symbols = $mysqli->query($query);

foreach ($symbols as $symbol) {
    $query = "UPDATE currencies SET 
              rate = ". $new_rates[$symbol['symbol']]. " 
              WHERE symbol = '". $symbol['symbol']. "'";
    if (!$mysqli->execute($query)) {
        echo 'ko - cannot update currency '. $symbol;
        exit();
    }
}

echo 'ok';
?>
