<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/prs_resumes_page.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/prs/resumes.php');
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

$candidate_id = '';
if (isset($_GET['candidate'])) {
    $candidate_id = $_GET['candidate'];
}

$home = new PrsResumesPage($_SESSION['yel']['employee']);
$home->header(array('root_dir' => '../', 
                    'title' => 'Other Resumes'));
$home->insert_prs_resumes_css();
$home->insert_prs_resumes_scripts();
$home->insert_inline_scripts($candidate_id);
$home->show();
$home->footer();
?>