<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/employee_login_page.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/employees/login.php');
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
    redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/employers/index.php');
}

if (!isset($_SESSION['yel']['employee'])) {
    $_SESSION['yel']['employee']['uid'] = "";
    $_SESSION['yel']['employee']['id'] = "";
    $_SESSION['yel']['employee']['sid'] = "";
    $_SESSION['yel']['employee']['hash'] = "";
    //redirect_to('login.php');
}

$login = new EmployeeLoginPage();
$login->header(array('root_dir' => '../', 
                     'title' => 'Employee Login'));
$login->insert_employee_login_css();
$login->insert_employee_login_scripts();
if (isset($_GET['invalid'])) {
    $login->show("Invalid login detected! Please try again.");
} else {
    $login->show();
}
$login->footer();
?>