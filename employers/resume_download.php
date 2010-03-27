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

exit();
?>
