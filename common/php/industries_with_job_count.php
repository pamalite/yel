<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

session_start();

$xml_dom = new XMLDOM ();
$response = array();

if (isset($_POST['type'])) {
    $result = array();
    
    if ($_POST['type'] = 'main') {
        $result = Industry::get_main_with_job_count($_SESSION['yel']['member'], $_SESSION['yel']['country_code']);
    } else { // $_POST['type'] = 'sub'
        $result = Indsutry::get_sub_industries_with_job_count_of($_POST['id'],
                                                                 $_SESSION['yel']['member'], 
                                                                 $_SESSION['yel']['country_code']);
    }
    
    $i = 0;
    foreach ($result as $row) {
        $response[$i]['id'] = $row['id'];
        $response[$i]['name'] = $row['industry'];
        $response[$i]['job_count'] = $row['job_count'];
        $i++;
    }
    
    $xml_array = array('industries' => array('industry' => $response));
    
} else {
    $industries = array();
    $mains = Industry::get_main_with_job_count($_SESSION['yel']['member'], $_SESSION['yel']['country_code']);
    $i = 0;
    foreach ($mains as $main) {
        $industries[$i]['id'] = $main['id'];
        $industries[$i]['name'] = $main['industry'];
        $industries[$i]['main'] = 'Y';
        $industries[$i]['job_count'] = $main['job_count'];
        
        $subs = Industry::get_sub_industries_with_job_count_of($main['id'], 
                                                               $_SESSION['yel']['member'], 
                                                               $_SESSION['yel']['country_code']);
        foreach ($subs as $sub) {
            $i++;
            $industries[$i]['id'] = $sub['id'];
            $industries[$i]['name'] = $sub['industry'];
            $industries[$i]['main'] = 'N';
            $industries[$i]['job_count'] = $sub['job_count'];
        }
        
        $i++;
    }
     
    $xml_array = array('industries' => array('industry' => $industries));
    
}

header('Content-type: text/xml');
echo $xml_dom->get_xml_from_array($xml_array);
?>