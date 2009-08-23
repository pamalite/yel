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

?></p>