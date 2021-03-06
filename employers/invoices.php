<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/employer_invoices_page.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/employers/invoices.php');
        exit();
    }
}

if (!isset($_SESSION['yel']['employer']) || 
    empty($_SESSION['yel']['employer']['id']) || 
    empty($_SESSION['yel']['employer']['sid']) || 
    empty($_SESSION['yel']['employer']['hash'])) {
    redirect_to('login.php');
}

$home = new EmployerInvoicesPage($_SESSION['yel']['employer']);
$home->header(array('root_dir' => '../', 
                    'title' => 'Invoices & Receipts'));
$home->insert_employer_invoices_css();
$home->insert_employer_invoices_scripts();
$home->insert_inline_scripts();
$home->show();
$home->footer();
?>
