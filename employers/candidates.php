<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/employer_candidates_page.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/employers/candidates.php');
        exit();
    }
}

if (!isset($_SESSION['yel']['employer']) || 
    empty($_SESSION['yel']['employer']['id']) || 
    empty($_SESSION['yel']['employer']['sid']) || 
    empty($_SESSION['yel']['employer']['hash'])) {
    redirect_to('login.php');
}

$section = 'search';
if (isset($_POST['page'])) {
    $section = $_POST['page'];
} else if (isset($_GET['page'])) {
    $section = $_GET['page'];
}


$page = new EmployerCandidatesPage($_SESSION['yel']['employer']);
$page->set_page($section);
$page->header(array('root_dir' => '../', 
                    'title' => 'Candidates'));
$page->insert_employer_candidates_css();
$page->insert_employer_candidates_scripts();
$page->insert_inline_scripts();
$page->show();
$page->footer();
?>