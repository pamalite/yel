<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/employer_login_page.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/employers/login.php');
        exit();
    }
}

if (isset($_SESSION['yel']['member']) && 
    !empty($_SESSION['yel']['member']['id']) && 
    !empty($_SESSION['yel']['member']['sid']) && 
    !empty($_SESSION['yel']['member']['hash'])) {
    redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/members/index.php');
}

if (isset($_SESSION['yel']['employer']) && 
    !empty($_SESSION['yel']['employer']['id']) && 
    !empty($_SESSION['yel']['employer']['sid']) && 
    !empty($_SESSION['yel']['employer']['hash'])) {
    redirect_to('home.php');
}

if (!isset($_SESSION['yel']['employer'])) {
    $_SESSION['yel']['employer']['id'] = "";
    $_SESSION['yel']['employer']['sid'] = "";
    $_SESSION['yel']['employer']['hash'] = "";
    //redirect_to('login.php');
}

$login = new EmployerLoginPage();
$login->header(array('root_dir' => '../', 
                     'title' => 'Employer Sign In'));
$login->insert_employer_login_css();
$login->insert_employer_login_scripts();
if (isset($_GET['invalid'])) {
    $login->show("Invalid login detected! Please try again.");
} else {
    $login->show();
}
$login->footer();

?>