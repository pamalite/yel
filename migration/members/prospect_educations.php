<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "INSERT INTO resume_educations SELECT 
          0, 
          resumes.id, 
          edu_category.edu_major, 
          prospect_education.edu_to, 
          prospect_education.instituition,
          'MY' 
          from yel_dev.prospect_education
          left join yel_dev.prospect on prospect.prospectid = prospect_education.prospectid 
          left join yel2_dev.resumes on resumes.member = prospect.email_addr 
          left join yel_dev.edu_category on edu_category.edu_majorid = prospect_education.edu_majorid";
$mysqli = Database::connect();
if ($mysqli->execute($query)) {
    echo "ok";
} else {
    echo "ko";
}
?>
