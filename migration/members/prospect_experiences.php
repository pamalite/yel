<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "INSERT INTO resume_work_experiences SELECT 
          0, 
          resumes.id, 
          prospect_experience.career_catid, 
          concat(prospect_experience.work_frm, '-01-01') as work_frm, 
          if ((prospect_experience.work_to is not null), 
               concat(prospect_experience.work_to, '-01-01'), 
               null) as work_to, 
          prospect_experience.work_place, 
          prospect_experience.role,
          prospect_experience.work_summary 
          from yel_dev.prospect_experience
          left join yel_dev.prospect on prospect.prospectid = prospect_experience.prospectid 
          left join yel2_dev.resumes on resumes.member = prospect.email_addr";
$mysqli = Database::connect();
if ($mysqli->execute($query)) {
    echo "ok";
} else {
    echo "ko";
}
?>
