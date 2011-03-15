<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/member_profile_page.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/members/profile.php');
        exit();
    }
}

if (!isset($_SESSION['yel']['member']) || 
    empty($_SESSION['yel']['member']['id']) || 
    empty($_SESSION['yel']['member']['sid']) || 
    empty($_SESSION['yel']['member']['hash'])) {
    redirect_to('login.php');
}

$home = new MemberProfilePage($_SESSION['yel']['member']);

if (isset($_GET['error'])) {
    $home->set_error($_GET['error']);
}

$home->header(array('root_dir' => '../', 
                    'title' => 'Profile'));
$home->insert_member_profile_css();
$home->insert_member_profile_scripts();
$home->insert_inline_scripts();
$home->show();
$home->footer();
?>
