<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$jobs = Job::find(array(
    'columns' => 'id, country, currency, state, title, description'
    )
);

$i = 0;
$query = "INSERT INTO job_index VALUES ";
foreach ($jobs as $job) {
    $query .= "(0, ". $job['id']. ", '". $job['country']. "', 
                '". $job['currency']. "', '". $job['state']. "', '". $job['title']. "', 
                '". $job['description']. "')";
    if ($i < count($jobs)-1) {
        $query .= ", ";
    }
    
    $i++;
}

$mysqli = Database::connect();
if ($mysqli->execute($query)) {
    echo "ok";
} else {
    echo "ko";
}
?>