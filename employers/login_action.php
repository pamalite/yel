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

$_SESSION['yel']['employer']['id'] = $id;
$_SESSION['yel']['employer']['hash'] = $hash;
$_SESSION['yel']['employer']['sid'] = $sid;

$employer = new Employer($id, $sid);
if (!$employer->isActive()) {
    $_SESSION['yel']['employer']['hash'] = "";
    $response['errors'] = array(
        'error' => 'The provided credentials are marked as inactive or suspended.&nbsp;<br/>&nbsp;Please contact your account manager for further assistance.'
    );
    echo $xml_dom->get_xml_from_array($response);
    exit();
    //redirect_to('login.php?invalid=1');
}

if (!$employer->isRegistered($hash)) {
    $_SESSION['yel']['employer']['hash'] = "";
    $response['errors'] = array(
        'error' => 'The provided credentials are invalid. Please try again.'
    );
    echo $xml_dom->get_xml_from_array($response);
    exit();
    //redirect_to('login.php?invalid=1');
} 

if (!$employer->setSessionWith($hash)) {
    $_SESSION['yel']['employer']['hash'] = "";
    $response['errors'] = array(
        'error' => 'bad_login'
    );
    echo $xml_dom->get_xml_from_array($response);
    exit();
    //redirect_to('../errors/failed_login.php?dir=employers');
}

$response['login'] = array('status' => 'ok');
echo $xml_dom->get_xml_from_array($response);
//redirect_to('home.php');
?>
