<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "SELECT id, employer, salary, salary_end 
          FROM jobs 
          WHERE closed = 'N'";
$mysqli = Database::connect();
$jobs = $mysqli->query($query);

foreach ($jobs as $job) {
    $salary_end = $job['salary_end'];
    if ($salary_end <= 0 || is_null($salary_end)) {
        $salary_end = $job['salary'];
    } elseif ($salary_end < $job['salary']) {
        $salary_end = $job['salary'];
    }
    
    $data = array();
    $data['potential_reward'] = Job::calculate_potential_reward_from($salary_end, $job['employer']);
    
    $a_job = new Job($job['id']);
    if ($a_job->update($data)) {
        echo "Updated job ". $job['id']. "\n";
    } else {
        echo "Not updated job ". $job['id']. "\n";
    }
}
?>