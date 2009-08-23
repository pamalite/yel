<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "INSERT INTO invoices SELECT 
          invoiceid, 
          invoiceissuedate,
          invoicetype,
          hirerid,
          payablebydate,
          pay_date,
          pay_mode,
          pay_id
          from yel_dev.invoice_closed";
$mysqli = Database::connect();
if ($mysqli->execute($query)) {
    echo "ok";
} else {
    echo "ko";
}
?>
