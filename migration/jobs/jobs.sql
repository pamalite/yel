insert into jobs
select 
0, 
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
concat(jobscope, ' ', jobreq) as description 
from yel_dev.jobpost;
