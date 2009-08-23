<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/employer_home_page.php";

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

$home = new EmployerHomePage($_SESSION['yel']['employer']);
$home->header(array('root_dir' => '../', 
                     'title' => 'Employer Home'));
$home->insert_employer_home_css();
$home->insert_employer_home_scripts();
$home->insert_inline_scripts();
$home->show();
$home->footer();
?>
