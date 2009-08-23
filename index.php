<?php
session_start();
require_once "private/lib/utilities.php";

// 1. Test database connection
$mysqli = Database::connect();
if (!$mysqli->ping()) {
    redirect_to('errors/temporarily_down.php');
}

// 2. Check whether session has been initialized
if (!isset($_SESSION['yel'])) {
    initialize_session();
}

// 3. Show welcome page
redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/welcome.php');
?>
