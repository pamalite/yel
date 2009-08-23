<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

header('Content-type: text/xml');
$xml_dom = new XMLDOM ();
$response = array();

if (isset($_POST['type'])) {
    $result = array();
    
    if ($_POST['type'] = 'main') {
        $result = Industry::get_main();
    } else { // $_POST['type'] = 'sub'
        $result = Indsutry::get_sub_industries_of($_POST['id']);
    }
    
    $i = 0;
    foreach ($result as $row) {
        $response[$i]['id'] = $row['id'];
        $response[$i]['name'] = $row['industry'];
        $i++;
    }
    
    $xml_array = array('industries' => array('industry' => $response));
    
} else {
    
    $industries = array();
    $mains = Industry::get_main();

    $i = 0;
    foreach ($mains as $main) {
        $industries[$i]['id'] = $main['id'];
        $industries[$i]['name'] = $main['industry'];
        $industries[$i]['main'] = 'Y';

        $subs = Industry::get_sub_industries_of($main['id']);
        foreach ($subs as $sub) {
            $i++;

            $industries[$i]['id'] = $sub['id'];
            $industries[$i]['name'] = $sub['industry'];
            $industries[$i]['main'] = 'N';
        }
        $i++;
    }

    $xml_array = array('industries' => array('industry' => $industries));
}

echo $xml_dom->get_xml_from_array($xml_array);
?>