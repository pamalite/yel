<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

header('Content-type: text/xml');
$xml_dom = new XMLDOM ();
$response = array();
$result = array();

$result = Job::find(array(
    'columns' => 'employers.id, employers.name',
    'joins' => 'employers ON employers.id = jobs.employer',
    'match' => 'employers.active = \'Y\' AND jobs.closed = \'N\' AND jobs.expire_on >= NOW()',
    'group' => 'employers.id',
    'order' => 'employers.name'
));

$i = 0;
foreach ($result as $row) {
    $response[$i]['id'] = $row['id'];
    $response[$i]['name'] = $row['name'];
    $i++;
}

$xml_array = array('employers' => array('employer' => $response)); 

echo $xml_dom->get_xml_from_array($xml_array);
?>