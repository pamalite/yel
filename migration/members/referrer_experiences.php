<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "INSERT INTO resumes SELECT distinct
          0, 
          referrer.email_addr, 
          'Untitled', 
          'Y', 
          now(), 
          null, null, null, null, null
          from yel_dev.referrer_experience 
          left join yel_dev.referrer on referrer.userid = referrer_experience.userid";
$mysqli = Database::connect();
if ($mysqli->execute($query)) {
    $query = "INSERT INTO resume_work_experiences SELECT 
              0, 
              resumes.id, 
              referrer_experience.career_catid, 
              concat(referrer_experience.work_frm, '-01-01') as work_frm, 
              if ((referrer_experience.work_to is not null), 
                 concat(referrer_experience.work_to, '-01-01'), 
                 null) as work_to, 
              referrer_experience.work_place, 
              referrer_experience.role,
              referrer_experience.work_summary 
              from yel_dev.referrer_experience
              left join yel_dev.referrer on referrer.userid = referrer_experience.userid 
              left join yel2_dev.resumes on resumes.member = referrer.email_addr";
    if (!$mysqli->execute($query)) {
        echo "ko";
        exit();
    }
    
    echo "ok";
} else {
    echo "ko";
}
?>
