<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/member_candidates_page.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        if (isset($_GET['id']) && isset($_GET['candidate'])) {
            redirect_to('https://'. $GLOBALS['root']. '/members/candidates.php?id='. $_GET['id']. '&candidate='. $_GET['candidate']);
        } else {
            redirect_to('https://'. $GLOBALS['root']. '/members/candidates.php');
        }
        exit();
    }
}

if (!isset($_SESSION['yel']['member']) || 
    empty($_SESSION['yel']['member']['id']) || 
    empty($_SESSION['yel']['member']['sid']) || 
    empty($_SESSION['yel']['member']['hash'])) {
    redirect_to('login.php');
}

$home = new MemberCandidatesPage($_SESSION['yel']['member']);
$home->header(array('root_dir' => '../', 
                    'title' => 'Contacts'));
$home->insert_member_candidates_css();
$home->insert_member_candidates_scripts();

if (isset($_GET['id']) && isset($_GET['candidate'])) {
    $home->insert_inline_scripts($_GET['id'], $_GET['candidate']);
} else {
    $home->insert_inline_scripts();
}

$home->show();
$home->footer();
?>
