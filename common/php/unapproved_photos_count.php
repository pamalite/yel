<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

session_start();

$xml_dom = new XMLDOM();
$total = 0; 

$query = "SELECT COUNT(id) AS num_photos 
          FROM member_photos
          WHERE approved = 'N'";
      
$mysqli = Database::connect();
$result = $mysqli->query($query);
if (count($result) <= 0 && is_null($result)) {
    echo '0';
} else {
    echo $result[0]['num_photos'];
}

exit();
?>