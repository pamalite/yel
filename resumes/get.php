<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";

// if ($GLOBALS['protocol'] == 'https') {
//     if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
//         redirect_to('https://'. $GLOBALS['root']. '/resumes/get.php');
//         exit();
//     } 
// }

$xml_dom = new XMLDOM();
$mysqli = Database::connect();

// 1. get all the resume IDs which are needs re-indexing
$query = "SELECT id, file_hash FROM resumes 
          WHERE needs_indexing = TRUE AND 
          file_type = 'application/msword' AND 
          deleted = 'N'";

$result = $mysqli->query($query);
if ($result === false) {
    echo 'ko';
    exit();
}

if (count($result) <= 0 || is_null($result)) {
    echo '0';
    exit();
}

// 2. put them into array for XML parsing
$response = array();
foreach ($result as $i=>$row) {
    $resume = array(
        'id' => $row['id'],
        'hash' => $row['file_hash']
    );
    
    $response['resume'][] = $resume;
}

// 3. return
header('Content-type: text/xml');
echo $xml_dom->get_xml_from_array(array('resumes' => $response));
?>