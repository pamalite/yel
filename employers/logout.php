<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

initialize_session();
$_SESSION['yel']['employer']['id'] = "";
$_SESSION['yel']['employer']['hash'] = "";
$_SESSION['yel']['employer']['sid'] = "";
redirect_to('login.php');
?>
