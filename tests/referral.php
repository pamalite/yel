<?php
require_once "../private/lib/utilities.php";

function print_array($_array) {
    echo "<pre>";
    print_r($_array);
    echo "</pre><br><br>";
}

$uid = 'pamalite@gmail.com';
$rid = 'sui.cheng.wong@d-pomelo.com';
$eid = 'acme123';

?><p style="font-weight: bold;">Show all referrals... </p><p><?php

print_array(Referral::get_all());

?></p><p style="font-weight: bold;">Add a referral... </p><p><?php
$data = array();
$data['member'] = $uid;
$data['referee'] = $rid;
$data['job'] = 2;
$data['referred_on'] = now();
$referral = 0;
if ($referral = Referral::create($data)) {
    echo "This referral has an ID of <b>". $referral. "</b><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Add another referral... </p><p><?php
$data = array();
$data['member'] = $uid;
$data['referee'] = $rid;
$data['job'] = 4;
$data['referred_on'] = now();
$referral = 0;
if ($referral = Referral::create($data)) {
    echo "This referral has an ID of <b>". $referral. "</b><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Add another referral... </p><p><?php
$data = array();
$data['member'] = $rid;
$data['referee'] = $uid;
$data['job'] = 2;
$data['referred_on'] = now();
$referral = 0;
if ($referral = Referral::create($data)) {
    echo "This referral has an ID of <b>". $referral. "</b><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Add another referral... </p><p><?php
$data = array();
$data['member'] = $rid;
$data['referee'] = $uid;
$data['job'] = 4;
$data['referred_on'] = now();
$testimony = array('Known member for 3 years.', 'Good in cartooning', 'Good people skills');
$data['testimony'] = Referral::serialize_testimony($testimony);

$referral = 0;
if ($referral = Referral::create($data)) {
    echo "This referral has an ID of <b>". $referral. "</b><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Show all referrals... </p><p><?php

print_array(Referral::get_all());

?></p><p style="font-weight: bold;">Referee confirms for referral 3... </p><p><?php
$data = array();
$data['id'] = 3;
$data['referee_acknowledged_on'] = now();
$data['resume'] = 1;

if (Referral::update($data)) {
    print_array(Referral::get($data['id']));
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Referee confirms for referral 4... </p><p><?php
$data = array();
$data['id'] = 4;
$data['referee_acknowledged_on'] = now();
$data['resume'] = 103;

if (Referral::update($data)) {
    print_array(Referral::get($data['id']));
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Employer decides to shortlist referral 4... </p><p><?php
$data = array();
$data['id'] = 4;
$data['employer_agreed_terms_on'] = now(); // This should be done before the shortlisting.
$data['shortlisted_on'] = now();

if (Referral::update($data)) {
    print_array(Referral::get($data['id']));
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Employer decides to employ referral 4... </p><p><?php
$data = array();
$data['id'] = 4;
$data['employed_on'] = now(); 
$data['work_commence_on'] = today();
$data['salary_per_annum'] = 36000.00;
$data['total_reward'] = Referral::calculate_total_reward_from($data['salary_per_annum'], 'acme123');

if (Referral::update($data)) {
    print_array(Referral::get($data['id']));
} else {
    echo "failed";
    exit();
}

?></p><?php
?>
