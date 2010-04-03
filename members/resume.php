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

$resume = new Resume('', $_GET['id']);
$resume_file = $resume->getFileInfo();
$file = $GLOBALS['resume_dir']. "/". $_GET['id']. ".". $resume_file['file_hash'];

if (file_exists($file)) {
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Expires: -1');
    header('Content-Description: File Transfer');
    header('Content-Length: ' . $resume_file['file_size']);
    header('Content-Disposition: attachment; filename="' . $resume_file['file_name'].'"');
    header('Content-type: '. $resume_file['file_type']);
    ob_clean();
    flush();
    readfile($file);
} else {
    ?>
    <html>
    <body>
    <?php
    echo 'Sorry, the resume file seemed to be missing. Please contact us at <a href="mailto: support@yellowelevator.com">support@yellowelevator.com</a>.';
    ?>
    </body>
    </html>
    <?php
}
?>