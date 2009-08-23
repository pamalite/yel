<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/member_saved_jobs_page.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/members/saved_jobs.php');
        exit();
    }
}

if (!isset($_SESSION['yel']['member']) || 
    empty($_SESSION['yel']['member']['id']) || 
    empty($_SESSION['yel']['member']['sid']) || 
    empty($_SESSION['yel']['member']['hash'])) {
    redirect_to('login.php');
}

$home = new MemberSavedJobsPage($_SESSION['yel']['member']);
$home->header(array('root_dir' => '../', 
                    'title' => 'Saved Jobs'));
$home->insert_member_saved_jobs_css();
$home->insert_member_saved_jobs_scripts();
$home->insert_inline_scripts();
$home->show();
$home->footer();
?>
