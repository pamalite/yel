<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "INSERT INTO security_clearances SELECT 
          * from yel_business.security_clearance";
$mysqli = Database::connect();
if ($mysqli->execute($query)) {
    echo "ok";
} else {
    echo "ko";
}
?>
