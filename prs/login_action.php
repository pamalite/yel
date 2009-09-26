<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();
$xml_dom = new XMLDOM();
$response = array();
header('Content-type: text/xml');
if (!isset($_POST['id']) && !isset($_POST['hash']) && !isset($_POST['sid'])) {
    $response['errors'] = array(
        'error' => 'Login ID and Password fields cannot be empty.'
    );
    echo $xml_dom->get_xml_from_array($response);
    exit();
    //redirect_to('login.php');
}

$id = $_POST['id'];
$hash = $_POST['hash'];
$sid = $_POST['sid'];

$_SESSION['yel']['employee']['uid'] = $id;
$_SESSION['yel']['employee']['id'] = $id;
$_SESSION['yel']['employee']['hash'] = $hash;
$_SESSION['yel']['employee']['sid'] = $sid;

$employee = new Employee($id, $sid);
if (!$employee->is_registered($hash)) {
    $_SESSION['yel']['employee']['hash'] = "";
    $response['errors'] = array(
        'error' => 'The provided credentials are invalid. Please try again.'
    );
    echo $xml_dom->get_xml_from_array($response);
    exit();
    //redirect_to('login.php?invalid=1');
} 

if (!$employee->session_set($hash)) {
    $_SESSION['yel']['employee']['hash'] = "";
    $response['errors'] = array(
        'error' => 'bad_login'
    );
    echo $xml_dom->get_xml_from_array($response);
    exit();
    //redirect_to('../errors/failed_login.php?dir=employers');
}

$branch_datas = $employee->get_branch();
foreach ($branch_datas[0] as $key=>$value) {
    $_SESSION['yel']['employee']['branch'][$key] = $value;
}

$business_groups = $employee->get_business_groups();
foreach ($business_groups as $i=>$group) {
    $_SESSION['yel']['employee']['business_groups'][$i] = array();
    foreach ($group as $key=>$value) {
        $_SESSION['yel']['employee']['business_groups'][$i][$key] = $value;
    }
}

foreach ($_SESSION['yel']['employee']['business_groups'] as $i=>$group) {
    $clearances = BusinessGroup::get_security_clearance($group['security_clearance']);
    $_SESSION['yel']['employee']['security_clearances'][$i] = array();
    foreach ($clearances[0] as $key=>$value) {
        $_SESSION['yel']['employee']['security_clearances'][$i][$key] = $value;
    }
}

$response['login'] = array('status' => 'ok');
echo $xml_dom->get_xml_from_array($response);
//redirect_to('home.php');
?>
