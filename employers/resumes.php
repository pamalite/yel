<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/employer_resumes_page.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/employers/home.php');
        exit();
    }
}

if (!isset($_SESSION['yel']['employer']) || 
    empty($_SESSION['yel']['employer']['id']) || 
    empty($_SESSION['yel']['employer']['sid']) || 
    empty($_SESSION['yel']['employer']['hash'])) {
    redirect_to('login.php');
}

$page = new EmployerResumesPage($_SESSION['yel']['employer']);
$page->header(array('root_dir' => '../', 
                    'title' => 'Resumes'));
$page->insert_employer_resumes_css();
$page->insert_employer_resumes_scripts();
$page->insert_inline_scripts();
$page->show();
$page->footer();
?>