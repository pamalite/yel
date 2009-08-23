<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "SELECT 
          hirer_confirms.suggestid as id, 
          referrer.email_addr as member, 
          prospect.email_addr as referee, 
          hirer_confirms.jobpostid as job, 
          NULL as resume, 
          hirer_confirms.date_suggested as referred, 
          hirer_confirms.date_interested as ack, 
          NULL as confirmed, 
          if ((hirer_confirms.hirer_verified = 1), now(), NULL) as agreed, 
          NULL as shortlisted, 
          NULL as contract_received, 
          hirer_confirms.date_hired as employed, 
          hirer_confirms.date_commence as commence, 
          hirer_confirms.hired_salary as salary, 
          hirer_confirms.reward, 
          referrer_testimony.testimony 
          from yel_dev.hirer_confirms
          left join yel_dev.referrer on referrer.userid = hirer_confirms.userid 
          left join yel_dev.prospect on prospect.prospectid = hirer_confirms.prospectid 
          left join yel_dev.referrer_testimony on referrer_testimony.suggestid = hirer_confirms.suggestid";
$mysqli = Database::connect();
if ($confirms = $mysqli->query($query)) {
    $query = "SELECT member, referee, job FROM referrals";
    if ($referrals = $mysqli->query($query)) {
        $query = "INSERT INTO referrals VALUES ";
        $i = 0;
        foreach ($confirms as $confirm) {
            $found = false;
            foreach ($referrals as $referral) {
                if ($confirm['member'] == $referral['member'] &&
                    $confirm['referee'] == $referral['referee'] && 
                    $confirm['job'] == $referral['job']) {
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $query .= "(". $confirm['id']. ", 
                            '". $confirm['member']. "', 
                            '". $confirm['referee']. "', 
                            ". $confirm['job']. ", 
                            NULL, 
                            '". $confirm['referred']. "', 
                            '". $confirm['ack']. "', 
                            NULL, 
                            '". $confirm['agreed']. "', 
                            NULL, 
                            NULL, 
                            '". $confirm['employed']. "', 
                            '". $confirm['commence']. "', 
                            ". $confirm['salary']. ", 
                            ". $confirm['reward']. ", 
                            '". $confirm['testimony']. "')";
                if ($i < count($confirms)-1) {
                    $query .= ", ";
                }
            } 
            
            $i++;
        }
        echo $query."<br><br>";
        if ($mysqli->execute($query)) {
            echo "ok";
            exit();
        }
    } 
} 

echo "ko";

?>
