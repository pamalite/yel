<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start ();

// Check whether the required session has been created. 
// If not, create it. The user may come to this page directly. 
if (!isset($_SESSION['yel'])) {
    initialize_session();
}

// Check whether the employer session has been created.
// If not, redirect it to the login page. 
if (!isset($_SESSION['yel']['employer'])) {
    redirect_to('login.php');
} 

// Check whether the id and sha1 sessions have been set. 
// If not, redirect it to the login page. 
if (empty($_SESSION['yel']['employer']['id']) || 
    empty($_SESSION['yel']['employer']['hash']) || 
    empty($_SESSION['yel']['employer']['sid'])) {
    redirect_to('login.php');
} 

// Check whether the employer is logged in
$employer = new Employer($_SESSION['yel']['employer']['id'], $_SESSION['yel']['employer']['sid']);

if (!$employer->is_logged_in($_SESSION['yel']['employer']['hash'])) {
    redirect_to('login.php');
}

// All checks seem to be good.
redirect_to('home.php');
?>
