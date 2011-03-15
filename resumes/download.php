<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";

// if ($GLOBALS['protocol'] == 'https') {
//     if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
//         redirect_to('https://'. $GLOBALS['root']. '/resumes/download.php');
//         exit();
//     } 
// }

if (!isset($_GET['id']) || !isset($_GET['hash'])) {
    echo '';
    exit();
}

$resume = new Resume('', $_GET['id']);
$resume_file = $resume->getFileInfo();
$file = $GLOBALS['resume_dir']. "/". $_GET['id']. ".". $_GET['hash'];

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
    echo '';
    exit();
}
?>