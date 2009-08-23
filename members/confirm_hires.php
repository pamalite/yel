<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/member_confirm_hires_page.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/members/confirm_hires.php');
        exit();
    }
}

if (!isset($_SESSION['yel']['member']) || 
    empty($_SESSION['yel']['member']['id']) || 
    empty($_SESSION['yel']['member']['sid']) || 
    empty($_SESSION['yel']['member']['hash'])) {
    redirect_to('login.php');
}

$home = new MemberConfirmHiresPage($_SESSION['yel']['member']);
$home->header(array('root_dir' => '../', 
                    'title' => 'Jobs Applied'));
$home->insert_member_confirm_hires_css();
$home->insert_member_confirm_hires_scripts();
$home->insert_inline_scripts();
$home->show();
$home->footer();
?>
