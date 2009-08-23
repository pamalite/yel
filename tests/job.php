<?php
require_once "../private/lib/utilities.php";

function print_array($array) {
    echo "<pre>";
    print_r($array);
    echo "</pre><br><br>";
}

?><p style="font-weight: bold;">Get all jobs... </p><p><?php
$jobs = Job::get_all();

echo "There are ". count($jobs). " jobs in the database.<br><br>";

?></p><p style="font-weight: bold;">Add a new job... </p><p><?php
$job = new Job();
$date = date('Y-m-d H:i:s');
$mysqli = Database::connect();
$result = $mysqli->query("SELECT DATE_ADD('". $date. "', INTERVAL 30 DAY) AS expiry_date");
$expiry_date = $result[0]['expiry_date'];
$data = array();
$data['employer'] = 'acme124';
$data['industry'] = '2';
$data['country'] = 'MY';
$data['currency'] = 'MYR';
$data['salary'] = '3200';
$data['salary_negotiable'] = 'N';
$data['potential_reward'] = '250';
$data['created_on'] = $date;
$data['expire_on'] = $expiry_date;
$data['title'] = "Some lame job";
$data['description'] = "blah blah... some job descriptions goes here";

if ($job->create($data)) {
    echo "This job gets the ID of <b>". $job->id(). "</b><br><br>";
    print_array($job->get());
} else {
    echo "failed";
    exit();
}
?></p><p style="font-weight: bold;">Add another new job... </p><p><?php
$job = new Job();
$date = date('Y-m-d H:i:s');
$mysqli = Database::connect();
$result = $mysqli->query("SELECT DATE_ADD('". $date. "', INTERVAL 30 DAY) AS expiry_date");
$expiry_date = $result[0]['expiry_date'];
$data = array();
$data['employer'] = 'acme123';
$data['industry'] = '2';
$data['country'] = 'SG';
$data['currency'] = 'SGD';
$data['salary'] = '3200';
$data['salary_negotiable'] = 'N';
$data['potential_reward'] = '250';
$data['created_on'] = $date;
$data['expire_on'] = $expiry_date;
$data['title'] = "Some lame job";
$data['description'] = "blahlelelll blah... some job descriptions goes here";

if ($job->create($data)) {
    echo "This job gets the ID of <b>". $job->id(). "</b><br><br>";
    print_array($job->get());
} else {
    echo "failed";
    exit();
}
?></p><p style="font-weight: bold;">Get all jobs... </p><p><?php
$jobs = Job::get_all();

echo "There are ". count($jobs). " jobs in the database.<br><br>";

?></p><p style="font-weight: bold;">Update 1st job... </p><p><?php
$job = new Job($jobs[0]['id']);
$data = array();
$data['country'] = 'HK';
$data['currency'] = 'HKD';
$data['salary'] = '5562';
$data['salary_negotiable'] = 'Y';
$data['potential_reward'] = '550';
$data['description'] = "wllwh kwhhwf wpejf[w wopj blahlelelll blah... some job descriptions goes here";

if ($job->update($data)) {
    print_array($job->get());
} else {
    echo "failed";
    exit();
}
?></p><?php
?>