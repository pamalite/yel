<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "SELECT symbol, country_code, NULL, rate from yel_dev.currency_1";
$mysqli = Database::connect();
$currencies = $mysqli->query($query);
$i = 0;
foreach ($currencies as $currency) {
    switch (strtoupper($currency['symbol'])) {
        case 'MYR':
            $currencies[$i]['currency'] = 'Malaysia Ringgit';
            break;
        case 'SGD':
            $currencies[$i]['currency'] = 'Singapore Dollar';
            break;
        case 'HKD':
            $currencies[$i]['currency'] = 'Hong Kong Dollar';
            break;
        case 'AUD':
            $currencies[$i]['currency'] = 'Australia Dollar';
            break;
        case 'USD':
            $currencies[$i]['currency'] = 'US Dollar';
            break;
    }
    $i++;
}

$query = "INSERT INTO currencies VALUES ";
$i = 0;
foreach ($currencies as $currency) {
    $query .= "('". $currency['symbol']. "', '". $currency['country_code']. "', '". $currency['currency']. "', ". $currency['rate']. ")";
    
    if ($i < count($currencies)-1) {
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
