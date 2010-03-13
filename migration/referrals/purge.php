<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

// purge resumes that are old

$mysqli = Database::connect();

// 1. Get all referrals and their age
$query = "SELECT id, referred_on, datediff(now(), referred_on) AS age, 
          referee_acknowledged_on, shortlisted_on, employed_on 
          FROM referrals
          ORDER BY age DESC";
$result = $mysqli->query($query);

if (is_null($result) || empty($result)) {
    echo 'Error: No referrals found.<br/><br/>';
    exit();
}

// 2. Remove the referrals if any of the conditions are met
// a. age >= 366 && referee_acknowledged_on is NULL or empty
// b. age >= 366 && shortlisted_on and employed_on are NULLs or empty's

$referrals = $result;
$failures = array();
foreach ($referrals as $referral) {
    if ($referral['age'] >= 366) {
        $query = 'DELETE FROM referrals WHERE id = ';
        if (is_null($referral['referee_acknowledged_on']) || 
            empty($referral['referee_acknowledged_on'])) {
            // condition (a)
            $query .= $referral['id'];
        } else if ((is_null($referral['shortlisted_on']) || 
                   empty($referral['shortlisted_on'])) && 
                   (is_null($referral['employed_on']) || 
                   empty($referral['employed_on']))) {
            // condition (b)
            $query .= $referral['id'];
        } else {
            echo 'Skipping referral ID: '. $referral['id']. ' &gt;&gt; ACKed/Shortlisted/Employed<br/>';
        }
        
        if (substr($query, strlen($query)-2, 1) != '=') {
            echo $query. '<br/>';
            if ($mysqli->execute($query) === false) {
                $failures[] = $referral['id'];
            }
        } 
    } else {
        echo 'Skipping referral ID: '. $referral['id']. ' &gt;&gt; Not expired<br/>';
    }
    
    echo '<br/>';
}

// 3. Report failures
if (!is_null($failures) && !empty($failures)) {
    echo 'There are failed deletions: <br/>';
    echo 'SELECT * FROM referrals WHERE id IN ('. implode(', ', $failures). ');. <br/><br/>';
} else {
    echo 'All successfully purged!<br/><br/>';
}

echo 'Finish';
?>
