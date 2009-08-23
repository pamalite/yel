<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/member_sign_up_page.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        if (isset($_GET['id']) && isset($_GET['candidate'])) {
            redirect_to('https://'. $GLOBALS['root']. '/members/sign_up.php?referee='. $_GET['referee']. '&member='. $_GET['member']);
        } else {
            redirect_to('https://'. $GLOBALS['root']. '/members/sign_up.php');
        }
        exit();
    }
}

if (isset($_SESSION['yel']['member']) && 
    !empty($_SESSION['yel']['member']['id']) && 
    !empty($_SESSION['yel']['member']['sid']) && 
    !empty($_SESSION['yel']['member']['hash'])) {
    redirect_to('home.php');
}

if (isset($_SESSION['yel']['employer']) && 
    !empty($_SESSION['yel']['employer']['id']) && 
    !empty($_SESSION['yel']['employer']['sid']) && 
    !empty($_SESSION['yel']['employer']['hash'])) {
    redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/employers/index.php');
}

if (!isset($_SESSION['yel']['member'])) {
    $_SESSION['yel']['member']['id'] = "";
    $_SESSION['yel']['member']['sid'] = "";
    $_SESSION['yel']['member']['hash'] = "";
    //redirect_to('login.php');
}

$referred_referee = '';
$referred_member = '';
if (isset($_GET['referee']) && isset($_GET['member'])) {
    $referred_member = $_GET['member'];
    $referred_referee = $_GET['referee'];
}

$home = new MemberSignUpPage($referred_referee, $referred_member);

if (isset($_GET['error'])) {
    $home->set_error($_GET['error']);
}

$home->header(array('root_dir' => '../', 
                    'title' => 'Member Sign Up'));
$home->insert_member_sign_up_css();
$home->insert_member_sign_up_scripts();
$home->insert_inline_scripts();
$home->show($_SESSION['yel']['sign_up']);
$home->footer();

?>
