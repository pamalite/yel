<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/employee_token_rewards_page.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/employees/token_rewards.php');
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

$home = new EmployeeTokenRewardsPage($_SESSION['yel']['employee']);
$home->header(array('root_dir' => '../', 
                    'title' => 'Token Rewards'));
$home->insert_employee_token_rewards_css();
$home->insert_employee_token_rewards_scripts();
$home->insert_inline_scripts();
$home->show();
$home->footer();
?>
