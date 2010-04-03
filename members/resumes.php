<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/member_resumes_page.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/members/resumes.php');
        exit();
    }
}

if (!isset($_SESSION['yel']['member']) || 
    empty($_SESSION['yel']['member']['id']) || 
    empty($_SESSION['yel']['member']['sid']) || 
    empty($_SESSION['yel']['member']['hash'])) {
    redirect_to('login.php');
}

$home = new MemberResumesPage($_SESSION['yel']['member']);
$home->header(array('root_dir' => '../', 
                    'title' => 'Resumes'));
$home->insert_member_resumes_css();
$home->insert_member_resumes_scripts();
$home->insert_inline_scripts();
$home->show();
$home->footer();
?>