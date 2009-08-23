<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

header('Content-type: text/xml');
$xml_dom = new XMLDOM ();
$response = array();

$result = Country::get_all();
$i = 0;
foreach ($result as $row) {
    $response[$i]['country_code'] = $row['country_code'];
    $response[$i]['name'] = $row['country'];
    $i++;
}

$xml_array = array('countries' => array('country' => $response));
echo $xml_dom->get_xml_from_array($xml_array);
?>
