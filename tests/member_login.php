<?php
require_once "../private/lib/utilities.php";

$xml_seed = new XMLDOM();
$xml_seed->load_from_uri("http://localhost/yel2/members/seed.php");
$data = $xml_seed->get_assoc(array('id', 'seed'));
$sid = $data[0]['id'];
$seed = $data[0]['seed'];
$uid = 'pamalite@gmail.com';
$password = 'new_password';
$hash = sha1($uid. md5($password). $seed);

echo $sid. ": ". $seed. "<br>";
echo $hash. "<br>";

?><p style="font-weight: bold;">Logging in... </p><p><?php
$member = new Member($uid, $sid);
if ($member->is_registered($hash)) {
    if ($member->session_set($hash)) {
        echo "Success";
    }
} 

if (!$member->is_logged_in($hash)) {
    echo "failed";
    exit();
}

?></p><?php
?>