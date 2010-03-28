<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/member_home_page.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/members/home.php');
        exit();
    }
}

if (!isset($_SESSION['yel']['member']) || 
    empty($_SESSION['yel']['member']['id']) || 
    empty($_SESSION['yel']['member']['sid']) || 
    empty($_SESSION['yel']['member']['hash'])) {
    redirect_to('login.php');
}

if (isset($_GET['job'])) {
    redirect_to('../job/'. $_GET['job']);
}

$home = new MemberHomePage($_SESSION['yel']['member']);
$home->header(array('root_dir' => '../', 
                     'title' => 'Home'));
$home->insert_member_home_css();
$home->insert_member_home_scripts();
$home->insert_inline_scripts();
$home->show();
$home->footer();
?>
