<?php
require_once "../private/lib/utilities.php";

$data = Seed::generateSeed();
$sid = $data['login']['id'];
$seed = $data['login']['seed'];
$uid = '200801021';
$password = 'testuser';

$employee = new Employee($uid, $sid);
$id = $employee->getId();
$hash = sha1($id. md5($password). $seed);

echo $sid. " : ". $seed. "<br>";
echo $hash. "<br>";

?><p style="font-weight: bold;">Logging in... </p><p><?php

if ($employee->isRegistered($hash)) {
    if ($employee->setSessionWith($hash)) {
        echo "Success";
    }
} 

if (!$employee->isLoggedIn($hash)) {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Update my details... </p><p><?php
$old_data = $employee->get();

echo "Before...<br><br>";
echo "<pre>";
print_r($old_data);
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
    $hash = sha1($id. $new_employee[0]['password']. $seed);
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Still logged in after a password change?</p><p><?php

if (!$employee->isLoggedIn($hash)) {
    echo "failed";
    exit();
} else {
    echo "Yup!";
}

?></p><p style="font-weight: bold;">Change back my details... </p><p><?php
echo "Before...<br><br>";
echo "<pre>";
print_r($new_employee);
echo "</pre><br><br>";

$data = array();
$data['password'] = $old_data[0]['password'];
$data['phone_num'] = $old_data[0]['phone_num'];
$data['address'] = $old_data[0]['address'];
$data['zip'] = $old_data[0]['zip'];
$data['state'] = $old_data[0]['state'];

if ($employee->update($data)) {
    echo "<pre>";
    print_r($employee->get());
    echo "</pre><br><br>";
    $hash = sha1($id. $old_data[0]['password']. $seed);
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Get clearances </p><p><?php
$clearances = $employee->getClearances();
echo "<pre>";
print_r($clearances);
echo "</pre><br><br>";

if (in_array('employers_create', $clearances)) {
    echo 'Can create employers.<br/><br/>';
}

if (in_array('invoices_view', $clearances)) {
    echo 'Can view invoices.<br/><br/>';
}

if (in_array('_view', $clearances)) {
    echo 'Has clearance for unknown activity.<br/><br/>';
}


?></p>
