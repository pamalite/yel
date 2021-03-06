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

$handle = fopen('/var/log/yellowel_buffered_resumes_update.log', 'a');
fwrite($handle, date('Y-m-d h:i:s'). ' buffered_resume '. $_POST['id']. ' needs update'. "\n");
fclose($handle);

$query = "UPDATE referral_buffers SET 
          resume_file_text = '". addslashes($extracted_text). "' 
          WHERE id = ". $_POST['id'];
if ($mysqli->execute($query) === false) {
    $handle = fopen('/var/log/yellowel_buffered_resumes_update.log', 'a');
    fwrite($handle, date('Y-m-d h:i:s'). ' buffered_resume '. $_POST['id']. ' failed to update'. "\n");
    fclose($handle);
    
    echo 'ko';
    exit();
}

$handle = fopen('/var/log/yellowel_buffered_resumes_update.log', 'a');
fwrite($handle, date('Y-m-d h:i:s'). ' buffered_resume '. $_POST['id']. ' updated'. "\n");
fclose($handle);

// $query = "UPDATE referral_buffers SET needs_indexing = FALSE WHERE 
//           id = ". $_POST['id'];
// $mysqli->execute($query);

echo 'ok';
exit();
?>