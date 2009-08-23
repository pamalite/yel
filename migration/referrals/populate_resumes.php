<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "SELECT id, referee FROM referrals";
$mysqli = Database::connect();
if ($referees = $mysqli->query($query)) {
    foreach ($referees as $referee) {
        $query = "SELECT id FROM resumes WHERE member = '". $referee['referee']. "' LIMIT 1";
        if ($resumes = $mysqli->query($query)) {
            if (!is_null($resumes) && count($resumes) > 0) {
                $query = "UPDATE referrals SET resume = ". $resumes[0]['id']. " WHERE id = ". $referee['id'];
                if (!$mysqli->execute($query)) {
                    echo "ko - resume update";
                    exit();
                }
            }
        } else {
            echo "ko - resume select";
            exit();
        }
    } 
    
    echo "ok";
    exit();
} 

echo "ko";

?>
