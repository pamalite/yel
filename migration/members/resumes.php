<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "INSERT INTO resumes SELECT 
          0,
          email_addr, 
          'Untitled' as name, 
          'N', 
          moddate, 
          cover_note, 
          NULL, 
          NULL, 
          NULL, 
          NULL 
          from yel_dev.prospect";
$mysqli = Database::connect();
if ($mysqli->execute($query)) {
    echo "ok";
} else {
    echo "ko";
}
?>
