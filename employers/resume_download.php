<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_SESSION['yel']['employer']) || 
    empty($_SESSION['yel']['employer']['id']) || 
    empty($_SESSION['yel']['employer']['sid']) || 
    empty($_SESSION['yel']['employer']['hash'])) {
    echo "An illegal attempt to view resume has been detected.";
    exit();
}

$resume = new Resume(0, $_GET['id']);
$cover = $resume->get();

if ($cover[0]['private'] == 'Y') {
    echo 'Sorry, the candidate had decided to lock the resume from public viewing.';
    exit();
}

if (!is_null($cover[0]['file_name'])) {
    $file = $resume->get_file();
    
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Expires: -1');
    header('Content-Description: File Transfer');
    header('Content-Length: ' . $file['size']);
    header('Content-Disposition: attachment; filename="' . $file['name'].'"');
    header('Content-type: '. $file['type']);
    ob_clean();
    flush();
    readfile($GLOBALS['resume_dir']. "/". $_GET['id']. ".". $file['hash']);
} else {
    redirect_to('https://'. $GLOBALS['root']. '/employers/resume.php?id='. $_GET['id']);
}

exit();
?>
