<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id']) || !isset($_POST['firstname']) || 
    !isset($_POST['phone_num']) || !isset($_POST['alternate_email']) || 
    !isset($_POST['lastname']) || !isset($_POST['zip']) ||
    !isset($_POST['country'])) {
    echo "ko";
    exit();
    //redirect_to('login.php');
}

$employee = new Employee($_POST['id'], $_SESSION['yel']['employee']['sid']);

$data = array();
$data['firstname'] = $_POST['firstname'];
$data['lastname'] = $_POST['lastname'];
$data['phone_num'] = $_POST['phone_num'];
$data['alternate_email'] = $_POST['alternate_email'];
$data['mobile'] = $_POST['mobile'];
$data['zip'] = $_POST['zip'];
$data['country'] = $_POST['country'];
$data['state'] = $_POST['state'];
$data['address'] = $_POST['address'];

if (isset($_POST['password'])) {
    $data['password'] = $_POST['password'];
}

if (!$employee->update($data)) {
    echo 'ko';
    exit();
}

echo 'ok';
?>
