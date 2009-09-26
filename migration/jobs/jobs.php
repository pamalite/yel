<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "INSERT INTO jobs 
SELECT 
jobpostid, 
hirerid, 
career_catid, 
if ((char_length(work_country) < 2), 'MY', 
    (
        if ((char_length(work_country) > 2), 'MY', 
            (
                if ((work_country is null), 'MY', ucase(work_country))
            )
        )
    )
) as country, 
currency, 
salary_frm, 
salary_negotiable, 
reward, 
if ((job_status = 'C'), 'Y', 'N') as closed,
indate,
if ((expdate is null), sql_date_add(indate, interval 30 day), expdate) as expiry,
if ((publish = 'R'), 'Y', 'N') as premium_only,
work_location,
jobtitle,
concat(jobscope, ' ', jobreq) as description, 
if ((closed_jobpostid = 0), NULL, closed_jobpostid), 
NULL
from yel_dev.jobpost";

$mysqli = Database::connect();
if ($mysqli->execute($query)) {
    echo "ok";
} else {
    echo "ko";
}

?>