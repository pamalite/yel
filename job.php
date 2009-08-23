<?php
session_start();

require_once "private/lib/utilities.php";
require_once "private/lib/classes/pages/job_page.php";

// 1. Check whether session has been initialized
if (!isset($_SESSION['yel'])) {
    initialize_session();
}

$criteria = array();
$from_search = false;
if (isset($_SESSION['yel']['job_search']['criteria'])) {
    $criteria = $_SESSION['yel']['job_search']['criteria'];
    $from_search = true;
}

$job_id = 0;
if (isset($_GET['id'])) {
    $job_id = $_GET['id'];
} else {
    $url_elements = explode('/', $_SERVER['REQUEST_URI']);
    if (!empty($url_elements[count($url_elements)-1])) {
        $job_id = $url_elements[count($url_elements)-1];
    } else {
        $job_id = $url_elements[count($url_elements)-2];
    }
}

// 2. Generate page
$search = '';
if (isset($_SESSION['yel']['member']) && 
    !empty($_SESSION['yel']['member']['id']) && 
    !empty($_SESSION['yel']['member']['sid']) && 
    !empty($_SESSION['yel']['member']['hash'])) {
    $job = new JobPage($_SESSION['yel']['member'], $job_id, $criteria);
} else {
    $job = new JobPage(NULL, $job_id, $criteria);
}

$mysqli = Database::connect();
$query = "SELECT jobs.title, employers.name 
          FROM jobs 
          LEFT JOIN employers ON employers.id = jobs.employer 
          WHERE jobs.id = ". $job_id. " LIMIT 1";
$result = $mysqli->query($query);
$job_title = 'Unknown Job';
$employer = 'Unknown Employer';
if (!is_null($result) && count($result) >= 0) {
    $job_title = $result[0]['title'];
    $employer = $result[0]['name'];
}

$job->header(array(
    'override_title' => true,
    'title' => $employer. ' - '. $job_title
));
$job->insert_job_css();
$job->insert_job_scripts();
$job->insert_inline_scripts();
$job->show($from_search);
$job->footer();
?>