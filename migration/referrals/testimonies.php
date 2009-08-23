<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "SELECT referrer_testimony.suggestid, referrer_testimony.testimony
          from yel_dev.referrer_testimony
          left join yel2_dev.referrals on referrer_testimony.suggestid = referrals.id";
$mysqli = Database::connect();
if ($testimonies = $mysqli->query($query)) {
    foreach ($testimonies as $testimony) {
        $text = (is_null($testimony['testimony']) || empty($testimony['testimony'])) ? 'NULL' : "'". $testimony['testimony']. "'";
        $query = "UPDATE referrals SET testimony = ". $text. " WHERE id = ". $testimony['suggestid'];
        if (!$mysqli->execute($query)) {
            echo "ko";
            exit();
        }
    } 
    
    echo "ok";
    exit();
} 

echo "ko";

?>
