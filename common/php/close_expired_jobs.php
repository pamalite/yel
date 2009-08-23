<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

log_activity('Initializing Job Closure...', 'yellowel_job_closure.log');

$mysqli = Database::connect();
$now = now();

// 1. Get the job ID where the expire_on <= now() is over and closed = 'N'.
log_activity('Getting the jobs where the expire_on <= now() is over and closed = \'N\'.', 'yellowel_job_closure.log');
$query = "SELECT id 
          FROM jobs 
          WHERE expire_on <= '". $now. "' AND 
          closed = 'N'";
$jobs = $mysqli->query($query);

if ($jobs === false) {
    $errors = $mysqli->error();
    log_activity('Error on querying: '. $errors['errno']. ': '. $errors['error'], 'yellowel_job_closure.log');
    log_activity('Unable to complete task!', 'yellowel_job_closure.log');
    exit();
}

// 2. For each job set closed = 'Y'
log_activity('Entering main loop...', 'yellowel_job_closure.log');
$jobs_string = '';
foreach ($jobs as $i=>$job) {
    $jobs_string .= $job['id'];
    
    if ($i < count($jobs)-1) {
        $jobs_string .= ', ';
    }
}

$query = "UPDATE jobs SET closed = 'Y' WHERE id IN (". $jobs_string. ")";
if (!$mysqli->execute($query)) {
    $errors = $mysqli->error();
    log_activity('Error on executing: '. $errors['errno']. ': '. $errors['error'], 'yellowel_job_closure.log');
    log_activity('Unable to complete task!', 'yellowel_job_closure.log');
    exit();
}

log_activity('Task completed. Goodbye!', 'yellowel_job_closure.log');
?>
