<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/employer_referrals_page.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/employers/referrals.php');
        exit();
    }
}

if (!isset($_SESSION['yel']['employer']) || 
    empty($_SESSION['yel']['employer']['id']) || 
    empty($_SESSION['yel']['employer']['sid']) || 
    empty($_SESSION['yel']['employer']['hash'])) {
    redirect_to('login.php');
}

$job = 0;
if (isset($_GET['job'])) {
    $job = (!empty($_GET['job'])) ? $_GET['job'] : 0 ;
}

$home = new EmployerReferralsPage($_SESSION['yel']['employer']);
$home->header(array('root_dir' => '../', 
                    'title' => 'Referrals'));
$home->insert_employer_referrals_css();
$home->insert_employer_referrals_scripts();
$home->insert_inline_scripts($job);
$home->show();
$home->footer();
?>
