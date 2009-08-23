<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

initialize_session();
$_SESSION['yel']['member']['id'] = "";
$_SESSION['yel']['member']['hash'] = "";
$_SESSION['yel']['member']['sid'] = "";

redirect_to('login.php');
?>
