<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start ();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/employees/index.php');
        exit();
    }
}

// Check whether the required session has been created. 
// If not, create it. The user may come to this page directly. 
if (!isset($_SESSION['yel'])) {
    initialize_session();
}

// Check whether the employer session has been created.
// If not, redirect it to the login page. 
if (!isset($_SESSION['yel']['employee'])) {
    redirect_to('login.php');
} 

// Check whether the id and sha1 sessions have been set. 
// If not, redirect it to the login page. 
if (empty($_SESSION['yel']['employee']['uid']) || 
    empty($_SESSION['yel']['employee']['id']) || 
    empty($_SESSION['yel']['employee']['hash']) || 
    empty($_SESSION['yel']['employee']['sid'])) {
    redirect_to('login.php');
} 

// Check whether the employer is logged in
$employee = new Employee($_SESSION['yel']['employee']['uid'], $_SESSION['yel']['employee']['sid']);

if (!$employee->is_logged_in($_SESSION['yel']['employee']['hash'])) {
    redirect_to('login.php');
}

// All checks seem to be good.
redirect_to('home.php');
?>
