<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "INSERT INTO branches SELECT 
          id, 
          name, 
          found_date, 
          concat(addr_line1, ' ', addr_line2, ' ', addr_line3) as address, 
          city, 
          poscode, 
          'MY', 
          phone, 
          currency_symbol
          from yel_business.business_entity";
$mysqli = Database::connect();
if ($mysqli->execute($query)) {
    echo "ok";
} else {
    echo "ko";
}
?>
