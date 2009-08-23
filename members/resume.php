<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/members/resume.php?id='. $_GET['id']. '&member='. $_GET['member']);
        exit();
    }
}

if (!isset($_SESSION['yel']['member'])) {
    $_SESSION['yel']['member']['id'] = "";
    $_SESSION['yel']['member']['sid'] = "";
    $_SESSION['yel']['member']['hash'] = "";
    redirect_to('login.php');
}

if (isset($_SESSION['yel']['member']) && 
    empty($_SESSION['yel']['member']['id']) && 
    empty($_SESSION['yel']['member']['sid']) && 
    empty($_SESSION['yel']['member']['hash'])) {
    redirect_to('home.php');
}

if (!isset($_GET['id'])) {
    redirect_to('login.php');
}


$resume = new Resume($_GET['member'], $_GET['id']);
$file = $resume->get_file();

header('Content-type: '. $file['type']);
header('Content-Disposition: attachment; filename="'. $file['name'].'"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-length: '. $file['size']);
ob_clean();
flush();
readfile($GLOBALS['resume_dir']. "/". $_GET['id']. ".". $file['hash']);

?>
