<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

$is_linkedin = false;
if (isset($_SESSION['yel']['member']['linkedin_id'])) {
    $is_linkedin = true;
}

initialize_session();
$_SESSION['yel']['member']['id'] = "";
$_SESSION['yel']['member']['hash'] = "";
$_SESSION['yel']['member']['sid'] = "";

// if LinkedIn detected, then use linkedin logout
if ($is_linkedin) {
    // redirect_to('https://www.linkedin.com/secure/login?session_full_logout=&trk=hb_signout');
    redirect_to('linkedin_logout.html');
    exit();
}

redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']);
?>
