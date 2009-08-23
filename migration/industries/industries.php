<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "INSERT INTO industries SELECT * FROM yel_dev.career_category";
$mysqli = Database::connect();
if ($mysqli->execute($query)) {
    echo "ok";
} else {
    echo "ko";
}
?>
