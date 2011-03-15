<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

// give all the shortlisted referrals 5 stars

$mysqli = Database::connect();


$query = "UPDATE referrals SET rating = 5 
          WHERE id IN (SELECT id 
          FROM referrals
          WHERE shortlisted_on IS NOT NULL AND 
          shortlisted_on <> '0000-00-00 00:00:00')";
if ($mysqli->execute($query)) {
    echo 'Error: Unable to rate 5 stars for shortlisted candidates.<br/><br/>';
    exit();
}

echo 'Finish';
?>
