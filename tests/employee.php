<?php
require_once "../private/lib/utilities.php";

$xml_seed = new XMLDOM();
$xml_seed->load_from_uri("http://localhost/yel2/employees/seed.php");
$data = $xml_seed->get_assoc(array('id', 'seed'));
$sid = $data[0]['id'];
$seed = $data[0]['seed'];
$uid = '200801021';
$password = 'testuser';
$id = Employee::extract($uid);
$hash = sha1($id['id']. md5($password). $seed);

echo $sid. ": ". $seed. "<br>";
echo $hash. "<br>";

$employee = new Employee($uid, $sid);

?><p style="font-weight: bold;">Logging in... </p><p><?php

if ($employee->is_registered($hash)) {
    if ($employee->session_set($hash)) {
        echo "Success";
    }
} 

if (!$employee->is_logged_in($hash)) {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Update my details... </p><p><?php
echo "Before...<br><br>";
echo "<pre>";
print_r($employee->get());
echo "</pre><br><br>";

$data = array();
$data['password'] = md5('new_passwd');
$data['phone_num'] = '+618-8463-2238';
$data['address'] = 'Lala Land';
$data['zip'] = '1100';
$data['state'] = 'Penang';

$new_employee = array();
if ($employee->update($data)) {
    $new_employee = $employee->get();
    echo "<pre>";
    print_r($new_employee);
    echo "</pre><br><br>";
    $hash = sha1($id['id']. $new_employee[0]['password']. $seed);
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Still logged in after a password change?</p><p><?php

if (!$employee->is_logged_in($hash)) {
    echo "failed";
    exit();
} else {
    echo "Yup!";
}

?></p>