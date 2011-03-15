<?php
require_once "../private/lib/utilities.php";

function print_array($array) {
    echo "<pre>";
    print_r($array);
    echo "</pre><br><br>";
}

?><p style="font-weight: bold;">Get all jobs... </p><p><?php
$job = new Job();
$jobs = $job->find(array('columns' => 'id'));

echo "There are ". count($jobs). " jobs in the database.<br><br>";

?></p><p style="font-weight: bold;">Add a new job... </p><p><?php
$job = new Job();
$date = date('Y-m-d H:i:s');
$expiry_date = sql_date_add($date, 30, 'day');
$data = array();
$data['employer'] = 'acme124';
$data['industry'] = '2';
$data['country'] = 'MY';
$data['currency'] = 'MYR';
$data['salary'] = '3200';
$data['salary_negotiable'] = 'N';
$data['created_on'] = $date;
$data['expire_on'] = $expiry_date;
$data['title'] = "Some lame job";
$data['description'] = "blah blah... some job descriptions goes here";

$first_job_id = 0;
if ($job->create($data)) {
    $first_job_id = $job->getId();
    echo "This job gets the ID of <b>". $job->getId(). "</b><br><br>";
    print_array($job->get());
} else {
    echo "failed";
    exit();
}
?></p><p style="font-weight: bold;">Add another new job... </p><p><?php
$job = new Job();
$date = date('Y-m-d H:i:s');
$expiry_date = sql_date_add($date, 30, 'day');
$data = array();
$data['employer'] = 'acme123';
$data['industry'] = '2';
$data['country'] = 'SG';
$data['currency'] = 'MYR';
$data['salary'] = '3200';
$data['salary_negotiable'] = 'N';
$data['created_on'] = $date;
$data['expire_on'] = $expiry_date;
$data['title'] = "Some lame job";
$data['description'] = "blahlelelll blah... some job descriptions goes here";

if ($job->create($data)) {
    echo "This job gets the ID of <b>". $job->getId(). "</b><br><br>";
    print_array($job->get());
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Get all jobs... </p><p><?php
$jobs = $job->find(array('columns' => 'id'));

echo "There are ". count($jobs). " jobs in the database.<br><br>";

?></p><p style="font-weight: bold;">Update 1st job... </p><p><?php
echo $first_job_id. '<br/>';
$job = new Job($first_job_id);
$data = array();
$data['country'] = 'HK';
$data['currency'] = 'HKD';
$data['salary'] = '5562';
$data['salary_negotiable'] = 'Y';
$data['description'] = "wllwh kwhhwf wpejf[w wopj blahlelelll blah... some job descriptions goes here";

if ($job->update($data)) {
    print_array($job->get());
} else {
    echo "failed";
    exit();
}
?></p><?php
?>