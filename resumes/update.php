<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/resumes/download.php');
        exit();
    } 
}

if (!isset($_POST['id']) || !isset($_POST['payload'])) {
    echo '';
    exit();
}

$extracted_text = $_POST['payload'];
$mysqli = Database::connect();

$query = "UPDATE resume_index SET 
          file_text = '". sanitize($_POST['payload']). "' 
          WHERE resume = ". $_POST['id'];
if ($mysqli->execute($query) === false) {
    echo 'ko';
    exit();
}

$query = "UPDATE resumes SET needs_indexing = FALSE WHERE 
          id = ". $_POST['id'];
$mysqli->execute($query);

echo 'ok';
exit();
?>