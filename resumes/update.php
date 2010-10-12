<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";

date_default_timezone_set('Asia/Kuala_Lumpur');

// if ($GLOBALS['protocol'] == 'https') {
//     if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
//         redirect_to('https://'. $GLOBALS['root']. '/resumes/download.php');
//         exit();
//     } 
// }

if (!isset($_POST['id']) || !isset($_POST['payload'])) {
    echo '';
    exit();
}

$extracted_text = htmlspecialchars_decode(stripslashes($_POST['payload']));
$mysqli = Database::connect();

$handle = fopen('/var/log/yellowel_resumes_update.log', 'a');
fwrite($handle, date('Y-m-d h:i:s'). ' resume '. $_POST['id']. ' needs update'. "\n");
fclose($handle);

$query = "UPDATE resume_index SET 
          file_text = '". addslashes($extracted_text). "' 
          WHERE resume = ". $_POST['id'];
if ($mysqli->execute($query) === false) {
    echo 'ko';
    exit();
}

$handle = fopen('/var/log/yellowel_resumes_update.log', 'a');
fwrite($handle, date('Y-m-d h:i:s'). ' resume '. $_POST['id']. ' updated'. "\n");
fclose($handle);

$query = "UPDATE resumes SET needs_indexing = FALSE WHERE 
          id = ". $_POST['id'];
$mysqli->execute($query);

echo 'ok';
exit();
?>