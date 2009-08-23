<?php
require_once "../private/lib/utilities.php";

$xml_seed = new XMLDOM();
$xml_seed->load_from_uri("http://localhost/yel2/members/seed.php");
$data = $xml_seed->get_assoc(array('id', 'seed'));
$sid = $data[0]['id'];
$seed = $data[0]['seed'];
$uid = 'pamalite@gmail.com';
$password = 'testuser';
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

?></p><p style="font-weight: bold;">Update my details... </p><p><?php
echo "Before...<br><br>";
echo "<pre>";
print_r($member->get());
echo "</pre><br><br>";

$data = array();
$data['password'] = md5('new_password');
$data['forget_password_answer'] = 'my mum is my mum';
$data['phone_num'] = '+614-0537-5314';
$data['address'] = '75/210 Grote Street';
$data['state'] = 'South Australia';
$data['zip'] = '2000';
$data['country'] = 'AU';
$data['like_newsletter'] = 'N';

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

if (!$member->is_logged_in($hash)) {
    echo "failed";
    exit();
} else {
    echo "Yup!";
}
?></p><p style="font-weight: bold;">Add a bank...</p><?php

if ($member->create_bank('HSBC', '12345')) {
    echo "Success";
} else {
    echo "failed";
    exit();
}

echo "<pre>";
print_r($member->get_banks());
echo "</pre><br><br>";

?></p><p style="font-weight: bold;">Add another bank...</p><?php

if ($member->create_bank('CITIBANK', '333-666AB-QQ5D6A')) {
    echo "Success";
} else {
    echo "failed";
    exit();
}

echo "<pre>";
print_r($member->get_banks());
echo "</pre><br><br>";

?></p><p style="font-weight: bold;">Update the second bank...</p><?php
$banks = $member->get_banks();

if ($member->update_bank($banks[1]['id'], 'CIMB', '888-888-8888')) {
    echo "Success";
} else {
    echo "failed";
    exit();
}

echo "<pre>";
print_r($member->get_banks());
echo "</pre><br><br>";

?></p><p style="font-weight: bold;">Delete the first bank...</p><?php
$banks = $member->get_banks();

if ($member->delete_bank($banks[0]['id'])) {
    echo "Success";
} else {
    echo "failed";
    exit();
}

echo "<pre>";
print_r($member->get_banks());
echo "</pre><br><br>";

?></p><p style="font-weight: bold;">Who are in my networks?</p><?php
$referees = $member->get_referees();
$networks = $member->get_networks();

if (count($referees) == 0) {
    echo "You have no one in your network.<br><br>";
} else {
    echo "<pre>";
    print_r($referees);
    echo "</pre><br><br>";
    
}


if (count($networks) == 0) {
    echo "You have no network.<br><br>";
} else {
    echo "<pre>";
    print_r($networks);
    echo "</pre><br><br>";
    
}

?></p><p style="font-weight: bold;">Add a member into my referees list</p><?php

if ($member->create_referee('lim.ah.bah01@gmail.com')) {
    echo "Success";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Add another member into my referees list</p><?php

if ($member->create_referee('kali.chan@gmail.com')) {
    $referees = $member->get_referees();
    echo "<pre>";
    print_r($referees);
    echo "</pre><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Let's say the referees approved the references...</p><?php
$mysqli = Database::connect();
$query = "SELECT id FROM member_referees WHERE member = '". $uid. "' AND approved = 'N'";
$result = $mysqli->query($query);

if ($member->approve_reference($result[0]['id'])) {
    if ($member->approve_reference($result[1]['id'])) {
        $referees = $member->get_referees();
        echo "<pre>";
        print_r($referees);
        echo "</pre><br><br>";
    }
} 

if (count($referees) == 0) {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Hide lim ah bah... </p><?php

if ($member->hide_referee('lim.ah.bah01@gmail.com', true)) {
    $referees = $member->get_referees();
    echo "<pre>";
    print_r($referees);
    echo "</pre><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Tag kali chan into a network... </p><?php
$referee = $member->get_referee_id_from_member_id('kali.chan@gmail.com');

if ($member->add_referee_into_network($referee, 1)) {
    $networks = $member->get_networks();
    echo "<pre>";
    print_r($networks);
    echo "</pre><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Tag kali chan into another network... </p><?php

if ($member->add_referee_into_network($referee, 2)) {
    $networks = $member->get_networks();
    echo "<pre>";
    print_r($networks);
    echo "</pre><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Tag lim ah bah into the 2nd network... </p><?php
$referee = $member->get_referee_id_from_member_id('lim.ah.bah01@gmail.com');

if ($member->add_referee_into_network($referee, 2)) {
    $networks = $member->get_networks();
    echo "<pre>";
    print_r($networks);
    echo "</pre><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Show the referees of the 1st network... </p><?php
if ($result = $member->get_referees_from_network(1)) {
    echo "<pre>";
    print_r($result);
    echo "</pre><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Show the referees of the 2nd network... </p><?php
if ($result = $member->get_referees_from_network(2)) {
    echo "<pre>";
    print_r($result);
    echo "</pre><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Retag lim ah bah into the 1st network... </p><?php
$referee = $member->get_referee_id_from_member_id('lim.ah.bah01@gmail.com');
$delete_ok = $member->delete_referee_from_network($referee, 2);
$add_ok = $member->add_referee_into_network($referee, 1);

if ($delete_ok && $add_ok) {
    $networks = $member->get_networks();
    echo "<pre>";
    print_r($networks);
    echo "</pre><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Again, show the referees of the 1st network... </p><?php
if ($result = $member->get_referees_from_network(1)) {
    echo "<pre>";
    print_r($result);
    echo "</pre><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Again, show the referees of the 2nd network... </p><?php
if ($result = $member->get_referees_from_network(2)) {
    echo "<pre>";
    print_r($result);
    echo "</pre><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Delete kali chan from list of referees... </p><?php
if ($member->delete_referee('kali.chan@gmail.com')) {
    $networks = $member->get_networks();
    $referees = $member->get_referees();
    echo "<pre>";
    print_r($networks);
    echo "</pre><br><br>";
    echo "<pre>";
    print_r($referees);
    echo "</pre><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Delete lim ah bah from list of referees... </p><?php
if ($member->delete_referee('lim.ah.bah01@gmail.com')) {
    $networks = $member->get_networks();
    $referees = $member->get_referees();
    echo "<pre>";
    print_r($networks);
    echo "</pre><br><br>";
    echo "<pre>";
    print_r($referees);
    echo "</pre><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><?php
?>