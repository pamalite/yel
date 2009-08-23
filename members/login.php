<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/member_login_page.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/members/login.php');
        exit();
    } 
}

if (isset($_SESSION['yel']['member']) && 
    !empty($_SESSION['yel']['member']['id']) && 
    !empty($_SESSION['yel']['member']['sid']) && 
    !empty($_SESSION['yel']['member']['hash'])) {
    redirect_to('home.php');
}

if (isset($_SESSION['yel']['employer']) && 
    !empty($_SESSION['yel']['employer']['id']) && 
    !empty($_SESSION['yel']['employer']['sid']) && 
    !empty($_SESSION['yel']['employer']['hash'])) {
    redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/employers/index.php');
}

if (!isset($_SESSION['yel']['member'])) {
    $_SESSION['yel']['member']['id'] = "";
    $_SESSION['yel']['member']['sid'] = "";
    $_SESSION['yel']['member']['hash'] = "";
    //redirect_to('login.php');
}

$job = '';
if (isset($_GET['job'])) {
    $job = $_GET['job'];
}
$login = new MemberLoginPage($job);
$login->header(array('root_dir' => '../', 
                     'title' => 'Members Login'));
$login->insert_member_login_css();
$login->insert_member_login_scripts();
if (isset($_GET['invalid'])) {
    $login->show("Invalid login detected! Please try again.");
} else {
    if (isset($_GET['signed_up'])) {
        if ($_GET['signed_up'] == 'success') {
            $login->insert_inline_scripts('success');
        } else {
            $login->insert_inline_scripts('activated');
        }
    } else {
        $login->insert_inline_scripts();
    }
    $login->show();
}
$login->footer();
?>