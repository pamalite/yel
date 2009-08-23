<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "INSERT INTO invoice_items SELECT 
          0, 
          invoiceid, 
          item, 
          itemdesc, 
          amount 
          from yel_dev.invoice_item";
$mysqli = Database::connect();
if ($mysqli->execute($query)) {
    echo "ok";
} else {
    echo "ko";
}
?>
