<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/member_referral_requests_page.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/members/referral_requests.php');
        exit();
    }
}

if (!isset($_SESSION['yel']['member']) || 
    empty($_SESSION['yel']['member']['id']) || 
    empty($_SESSION['yel']['member']['sid']) || 
    empty($_SESSION['yel']['member']['hash'])) {
    redirect_to('login.php');
}

$home = new MemberReferralRequestsPage($_SESSION['yel']['member']);
$home->header(array('root_dir' => '../', 
                    'title' => 'Referral Requests'));
$home->insert_member_referral_requests_css();
$home->insert_member_referral_requests_scripts();
$home->insert_inline_scripts();
$home->show();
$home->footer();
?>
