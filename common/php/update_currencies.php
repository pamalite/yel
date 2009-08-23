<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

log_activity('Initializing currency updater...', 'yellowel_currency_rate_updater.log');

$new_rates = array();

log_activity('Read eurofxref-daily.xml file from Europe Central Bank...', 'yellowel_currency_rate_updater.log');
//Read eurofxref-daily.xml file in memory 
$XMLContent= file("http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml");
//the file is updated daily between 14:15 and 15:00 CET (which is 09:00 MYT)

log_activity('Deciphering the rates and currencies...', 'yellowel_currency_rate_updater.log');
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

log_activity('Updating rates...', 'yellowel_currency_rate_updater.log');
foreach ($symbols as $symbol) {
    $query = "UPDATE currencies SET 
              rate = ". $new_rates[$symbol['symbol']]. " 
              WHERE symbol = '". $symbol['symbol']. "'";
    if (!$mysqli->execute($query)) {
        $errors = $mysqli->error();
        log_activity('Error cannot update currency: '. $symbol, 'yellowel_currency_rate_updater.log');
        log_activity($errors['errno']. ': '. $errors['error'], 'yellowel_currency_rate_updater.log');
        exit();
    }
}

log_activity('Task completed. Goodbye!', 'yellowel_currency_rate_updater.log');
?>
