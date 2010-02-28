<?php
require_once "../private/lib/utilities.php";

// $xml_seed = new XMLDOM();
// $xml_seed->load_from_uri("http://localhost/yel/members/seed.php");
// $data = $xml_seed->get_assoc(array('id', 'seed'));
// $sid = $data[0]['id'];
// $seed = $data[0]['seed'];
$data = Seed::generateSeed();
$sid = $data['login']['id'];
$seed = $data['login']['seed'];
$uid = 'pamalite@gmail.com';
$password = 'testuser';
$hash = sha1($uid. md5($password). $seed);

echo $sid. ": ". $seed. "<br>";
echo $hash. "<br>";

?><p style="font-weight: bold;">Logging in... </p><p><?php
$member = new Member($uid, $sid);
if ($member->isRegistered($hash)) {
    if ($member->setSessionWith($hash)) {
        echo "Success";
    }
} 

if (!$member->isLoggedIn($hash)) {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Update my details... </p><p><?php
echo "Before...<br><br>";
echo "<pre>";
print_r($member->get());
echo "</pre><br><br>";

$data = array();
$data['password'] = md5('new_password');

$new_member = array();

if ($member->update($data)) {
    $new_member = $member->get();
    echo "<pre>";
    print_r($new_member);
    echo "</pre>";
    $hash = sha1($uid. $new_member[0]['password']. $seed);
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Still logged in after a password change?</p><p><?php

if (!$member->isLoggedIn($hash)) {
    echo "failed";
    exit();
} else {
    echo "Yup!";
}

?></p><p style="font-weight: bold;">Update my details again... </p><p><?php
echo "Before...<br><br>";
echo "<pre>";
print_r($member->get());
echo "</pre><br><br>";

$data = array();
$data['password'] = md5('testuser');

$new_member = array();

if ($member->update($data)) {
    $new_member = $member->get();
    echo "<pre>";
    print_r($new_member);
    echo "</pre>";
    $hash = sha1($uid. $new_member[0]['password']. $seed);
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Still logged in after a password change?</p><p><?php

if (!$member->isLoggedIn($hash)) {
    echo "failed";
    exit();
} else {
    echo "Yup!";
}

?></p><p style="font-weight: bold;">Add a bank...</p><?php

if ($member->saveBankAccount('HSBC', '12345')) {
    echo "Success";
} else {
    echo "failed";
    exit();
}

echo "<pre>";
print_r($member->getBankAccount());
echo "</pre><br><br>";

?></p><p style="font-weight: bold;">Update bank...</p><?php

if ($member->saveBankAccount('CITIBANK', '3331111-666AB-QQ5D6A')) {
    echo "Success";
} else {
    echo "failed";
    exit();
}

echo "<pre>";
print_r($member->getBankAccount());
echo "</pre><br><br>";

?></p><?php
?>
