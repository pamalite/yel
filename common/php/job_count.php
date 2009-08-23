<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

header('Content-type: text/xml');
$xml_dom = new XMLDOM ();
$response = Job::find(array(
        'columns' => 'COUNT(*) AS jobcount', 
        'match' => 'jobs.closed = \'N\' AND jobs.expire_on >= NOW()' 
    )
);

$response[0]['jobcount'] = number_format($response[0]['jobcount'], 0, ".", ",");

$xml_array = array('jobs' => $response);
echo $xml_dom->get_xml_from_array($xml_array);

?>