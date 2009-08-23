<?php
require_once "../private/lib/utilities.php";

$xml_seed = new XMLDOM();
$xml_seed->load_from_uri("http://localhost/yel2/employers/seed.php");
$data = $xml_seed->get_assoc(array('id', 'seed'));
$sid = $data[0]['id'];
$seed = $data[0]['seed'];
$uid = 'acme123';
$password = 'acme123';
$hash = sha1($uid. md5($password). $seed);

echo $sid. ": ". $seed. "<br>";
echo $hash. "<br>";

$employer = new Employer($uid, $sid);

?><p style="font-weight: bold;">Logging in... </p><p><?php

if ($employer->is_registered($hash)) {
    if ($employer->session_set($hash)) {
        echo "Success";
    }
} 

if (!$employer->is_logged_in($hash)) {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Update my details... </p><p><?php
echo "Before...<br><br>";
echo "<pre>";
print_r($employer->get());
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

if (!$employer->is_logged_in($hash)) {
    echo "failed";
    exit();
} else {
    echo "Yup!";
}

?></p><p style="font-weight: bold;">Display the fees I agreed...</p><p><?php

echo "<pre>";
print_r($employer->get_fees());
echo "</pre><br><br>";

?></p><p style="font-weight: bold;">Display the extras I agreed...</p><p><?php

echo "<pre>";
print_r($employer->get_extras());
echo "</pre><br><br>";

?></p><?php
?>