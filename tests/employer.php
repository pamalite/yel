<?php
require_once "../private/lib/utilities.php";

$data = Seed::generateSeed();
$sid = $data['login']['id'];
$seed = $data['login']['seed'];
$uid = 'acme123';
$password = 'acme123';
$hash = sha1($uid. md5($password). $seed);

echo $sid. ": ". $seed. "<br>";
echo $hash. "<br>";

$employer = new Employer($uid, $sid);

?><p style="font-weight: bold;">Logging in... </p><p><?php

if ($employer->isRegistered($hash)) {
    if ($employer->setSessionWith($hash)) {
        echo "Success";
    }
} 

if (!$employer->isLoggedIn($hash)) {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Update my details... </p><p><?php
$old_data = $employer->get();
echo "Before...<br><br>";
echo "<pre>";
print_r($old_data);
echo "</pre><br><br>";

$data = array();
$data['password'] = md5('new_passwd');
$data['name'] = 'Avatar';
$data['phone_num'] = '+618-8463-2238';
$data['address'] = 'Lala Land';
$data['zip'] = '1100';
$data['state'] = 'Penang';
$data['website_url'] = $GLOBALS['protocol']. '://www.google.com';
$data['about'] = 'nothing about this company';

$new_employer = array();
if ($employer->update($data)) {
    $new_employer = $employer->get();
    echo "<pre>";
    print_r($new_employer);
    echo "</pre><br><br>";
    $hash = sha1($uid. $new_employer[0]['password']. $seed);
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Still logged in after a password change?</p><p><?php

if (!$employer->isLoggedIn($hash)) {
    echo "failed";
    exit();
} else {
    echo "Yup!";
}

?></p><p style="font-weight: bold;">Change back my details... </p><p><?php
echo "Before...<br><br>";
echo "<pre>";
print_r($new_employer);
echo "</pre><br><br>";

$data = array();
$data['password'] = $old_data[0]['password'];
$data['name'] = $old_data[0]['name'];
$data['phone_num'] = $old_data[0]['phone_num'];
$data['address'] = $old_data[0]['address'];
$data['zip'] = $old_data[0]['zip'];
$data['state'] = $old_data[0]['state'];
$data['website_url'] = $old_data[0]['website_url'];
$data['about'] = $old_data[0]['about'];

if ($employer->update($data)) {
    echo "<pre>";
    print_r($employer->get());
    echo "</pre><br><br>";
    $hash = sha1($uid. $old_data[0]['password']. $seed);
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Still logged in after a password change?</p><p><?php

if (!$employer->isLoggedIn($hash)) {
    echo "failed";
    exit();
} else {
    echo "Yup!";
}

?></p><p style="font-weight: bold;">Display the fees I agreed...</p><p><?php

echo "<pre>";
print_r($employer->getFees());
echo "</pre><br><br>";

?></p><p style="font-weight: bold;">Get all employers...</p><p><?php
$criteria = array(
    'columns' => 'id'
);

$result = $employer->find($criteria);
$employers = array();
foreach ($result as $row) {
    $employers[] = new Employer($row['id']);
}

$employers_list = array();
$i = 0;
foreach ($employers as $emp) {
    $employers_list[$i]['id'] = $emp->getId();
    $employers_list[$i]['name'] = $emp->getName();
    $branch = $emp->getAssociatedBranch();
    $employers_list[$i]['branch'] = $branch[0]['country_name'];
    
    $i++;
}

echo "<pre>";
print_r($employers_list);
echo "</pre><br><br>";

?></p><?php
?>