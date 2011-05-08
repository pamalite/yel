<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

initialize_session();
$_SESSION['yel']['member']['id'] = "";
$_SESSION['yel']['member']['hash'] = "";
$_SESSION['yel']['member']['sid'] = "";

// if LinkedIn detected, then use linkedin logout
if (isset($_SESSION['yel']['member']['linkedin_id'])) {
    if (!empty($_SESSION['yel']['member']['linkedin_id'])) {
        redirect_to('https://www.linkedin.com/secure/login?session_full_logout=&trk=hb_signout');
        exit();
    }
}

redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']);
?>
