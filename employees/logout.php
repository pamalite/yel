<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

initialize_session();
$_SESSION['yel']['employee']['uid'] = "";
$_SESSION['yel']['employee']['id'] = "";
$_SESSION['yel']['employee']['hash'] = "";
$_SESSION['yel']['employee']['sid'] = "";
$_SESSION['yel']['employee']['branch'] = "";
$_SESSION['yel']['employee']['business_groups'] = "";
$_SESSION['yel']['employee']['security_clearances'] = "";
$_SESSION['yel']['employee']['dev'] = "";

redirect_to('login.php');
?>
