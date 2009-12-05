<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id'])) {
    echo "ko";
    exit();
    //redirect_to('login.php');
}

$xml_dom = new XMLDOM();
$order_by = 'modified_on desc';

if (isset($_POST['order_by'])) {
    $order_by = $_POST['order_by'];
}

$criteria = array(
    'columns' => 'id, name, DATE_FORMAT(modified_on, \'%e %b, %Y\') AS modified_date, file_hash',
    'order' => $order_by,
    'match' => 'member = \''. $_POST['id']. '\' AND deleted = \'N\''
);

$resumes = Resume::find($criteria);
$response = array(
    'resumes' => array('resume' => $resumes)
);

header('Content-type: text/xml');
echo $xml_dom->get_xml_from_array($response);
?>
