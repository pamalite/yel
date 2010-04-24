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
    
    $pos = stripos($job_id, '?');
    if ($pos !== false) {
        $job_id = substr($job_id, 0, $pos);
    }
}

// 2. Generate page
$search = '';
if (isset($_SESSION['yel']['member']) && 
    !empty($_SESSION['yel']['member']['id']) && 
    !empty($_SESSION['yel']['member']['sid']) && 
    !empty($_SESSION['yel']['member']['hash'])) {
    $job_page = new JobPage($_SESSION['yel']['member'], $job_id, $criteria);
} else {
    $job_page = new JobPage(NULL, $job_id, $criteria);
}

if (isset($_SESSION['yel']['employee']) && 
    !empty($_SESSION['yel']['employee']['uid']) || 
    !empty($_SESSION['yel']['employee']['id']) || 
    !empty($_SESSION['yel']['employee']['sid']) || 
    !empty($_SESSION['yel']['employee']['hash'])) {
    $job_page->is_employee_viewing();
}

$criteria = array(
    'columns' => 'jobs.title, jobs.alternate_employer, employers.name', 
    'joins' => 'employers ON employers.id = jobs.employer', 
    'match' => 'jobs.id = '. $job_id, 
    'limit' => '1'
);

$job = new Job();
$result = $job->find($criteria);
$job_title = (!is_null($result[0]['title'])) ? $result[0]['title'] : 'Unknown Job';
$employer = (!is_null($result[0]['name'])) ? $result[0]['name'] : 'Unknown Employer';
if (!is_null($result[0]['alternate_employer'])) {
    $employer = $result[0]['alternate_employer'];
}

$show_popup = '';
if (isset($_GET['refer'])) {
    $show_popup = 'refer';
} else if (isset($_GET['apply'])) {
    $show_popup = 'apply';
}

$job_page->header(array(
    'override_title' => true,
    'title' => $employer. ' - '. $job_title
));
$job_page->insert_job_css();
$job_page->insert_job_scripts();
$job_page->insert_inline_scripts($show_popup);
$job_page->show($from_search);
$job_page->footer();
?>