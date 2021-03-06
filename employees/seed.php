<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

header('Content-type: text/xml');
$xml_dom = new XMLDOM();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        $response['errors'] = array(
            'error' => 'insecure'
        );
        echo $xml_dom->get_xml_from_array($response);
        exit();
    }
}

/*
    Generate the seed and return the ID and SEED in XML format. 
    A seed and a seed id are required for web-based authentication.
    The SHA1 hash is generated by concatenating id, md5(password) and seed. 
*/

$response = Seed::generateSeed();
if ($response === false) {
    // Return an error XML if there is a problem. 
    $response['errors'] = array(
        'error' => 'An error occured while generating seed.'
    );
}

echo $xml_dom->get_xml_from_array($response);

?>