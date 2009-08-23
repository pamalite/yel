<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "SELECT email_addr, bankname, banknumber from yel_dev.referrer";
$mysqli = Database::connect();
$banks = $mysqli->query($query);
$banks_to_create = array();
$i = 0;
foreach ($banks as $bank) {
    if (!is_null($bank['bankname']) && !is_null($bank['banknumber'])) {
        $banks_to_create[$i] = $bank;
        $i = 0;
    }
}

if ($i > 0) {
    $i = 0;
    $query = "INSERT INTO member_banks VALUES ";
    foreach ($banks_to_create as $bank) {
        $query .= "(0, '". $bank['email_addr']. "', '". $bank['bankname']."', '". $bank['banknumber']. "')";
        
        if ($i < count($banks_to_create)-1) {
            $query .= ", ";
        }
        
        $i++;
    }
    if (!$mysqli->execute($query)) {
        echo "ko";
        exit();
    }
} 

echo "ok";
?>
