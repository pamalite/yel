<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start ();

$job = '';
if (isset($_GET['job'])) {
    $job = '?job='. $_GET['job'];
}

// Check whether the required session has been created. 
// If not, create it. The user may come to this page directly. 
if (!isset($_SESSION['yel'])) {
    initialize_session();
}

// Check whether the member session has been created.
// If not, redirect it to the login page. 
if (!isset($_SESSION['yel']['member'])) {
    redirect_to('login.php'. $job);
} 

// Check whether the id and sha1 sessions have been set. 
// If not, redirect it to the login page. 
if (empty($_SESSION['yel']['member']['id']) || 
    empty($_SESSION['yel']['member']['hash']) || 
    empty($_SESSION['yel']['member']['sid'])) {
    redirect_to('login.php'. $job);
} 

// Check whether the member is logged in
$member = new Member($_SESSION['yel']['member']['id'], $_SESSION['yel']['member']['sid']);

if (!$member->is_logged_in($_SESSION['yel']['member']['hash'])) {
    redirect_to('login.php'. $job);
}

// All checks seem to be good.
redirect_to('home.php');
?>
