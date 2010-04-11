<?php
session_start();

require_once "private/lib/utilities.php";
require_once "private/lib/classes/pages/search_page.php";

// 1. Check whether session has been initialized
if (!isset($_SESSION['yel'])) {
    redirect_to('index.php');
}

// 1.5 Check whether a new search session had been initiated
$criteria = array();
if (isset($_POST['industry']) && isset($_POST['keywords']) && isset($_POST['employer'])) {
    $criteria = $_POST;
    $_SESSION['yel']['job_search']['criteria'] = $_POST;
} else if (isset($_GET['industry']) && isset($_GET['keywords']) && isset($_GET['employer'])) {
    $criteria = $_GET;
    $_SESSION['yel']['job_search']['criteria'] = $_GET;
} else {
    $criteria = $_SESSION['yel']['job_search']['criteria'];
}

// 2. Generate page
$search = '';
if (isset($_SESSION['yel']['member']) && 
    !empty($_SESSION['yel']['member']['id']) && 
    !empty($_SESSION['yel']['member']['sid']) && 
    !empty($_SESSION['yel']['member']['hash'])) {
    $search = new SearchPage($_SESSION['yel']['member'], $criteria);
} else {
    $search = new SearchPage(NULL, $criteria);
}
$search->header(array('title' => 'Searched Jobs'));
$search->insert_search_css();
$search->insert_search_scripts();
$search->insert_inline_scripts();
$search->show();
$search->footer();
?>