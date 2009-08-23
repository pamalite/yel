<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "INSERT INTO employer_fees SELECT 
          0, 
          hirerid, 
          service_fee, 
          premier_fee, 
          discount, 
          25.00, 
          salary_start, 
          salary_end, 
          guarantee_months 
          from yel_dev.hirer_fees";
$mysqli = Database::connect();
if ($mysqli->execute($query)) {
    echo "ok";
} else {
    echo "ko";
}
?>
