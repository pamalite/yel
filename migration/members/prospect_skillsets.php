<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "INSERT INTO resume_skills SELECT 
          0, 
          resumes.id, 
          prospect_skillsets.subskills
          from yel_dev.prospect_skillsets 
          left join yel_dev.prospect on prospect.prospectid = prospect_skillsets.prospectid 
          left join yel2_dev.resumes on resumes.member = prospect.email_addr";
$mysqli = Database::connect();
if ($mysqli->execute($query)) {
    echo "ok";
} else {
    echo "ko";
}
?>
