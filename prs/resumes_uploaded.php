<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/prs_resumes_uploaded_page.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/prs/resumes_uploaded.php');
        exit();
    }
}

if (!isset($_SESSION['yel']['employee']) || 
    empty($_SESSION['yel']['employee']['uid']) || 
    empty($_SESSION['yel']['employee']['id']) || 
    empty($_SESSION['yel']['employee']['sid']) || 
    empty($_SESSION['yel']['employee']['hash'])) {
    redirect_to('login.php');
}

$home = new PrsUploadedResumesPage($_SESSION['yel']['employee']);
$home->header(array('root_dir' => '../', 
                    'title' => 'Uploaded Resumes'));
$home->insert_prs_uploaded_resumes_css();
$home->insert_prs_uploaded_resumes_scripts();
$home->insert_inline_scripts();
$home->show();
$home->footer();
?>
