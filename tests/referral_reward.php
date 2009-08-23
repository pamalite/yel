<?php
require_once "../private/lib/utilities.php";

function print_array($_array) {
    echo "<pre>";
    print_r($_array);
    echo "</pre><br><br>";
}

$referral = 4;

?><p style="font-weight: bold;">Show all rewards... </p><p><?php

print_array(ReferralReward::get_all());

?></p><p style="font-weight: bold;">Add a reward... </p><p><?php
$data = array();
$data['referral'] = $referral;
$data['reward'] = 50;
$data['paid_on'] = now();
$data['paid_through'] = 'IBT';
$data['bank'] = 1;

$reward = 0;
if ($reward = ReferralReward::create($data)) {
    echo "This reward has an ID of <b>". $reward. "</b><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Add another reward... </p><p><?php
$data = array();
$data['referral'] = $referral;
$data['reward'] = 50;
$data['paid_on'] = now();
$data['paid_through'] = 'IBT';
$data['bank'] = 2;

$reward = 0;
if ($reward = ReferralReward::create($data)) {
    echo "This reward has an ID of <b>". $reward. "</b><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Add another reward... </p><p><?php
$data = array();
$data['referral'] = $referral;
$data['reward'] = 50;
$data['paid_on'] = now();
$data['paid_through'] = 'IBT';
$data['bank'] = 3;

$reward = 0;
if ($reward = ReferralReward::create($data)) {
    echo "This reward has an ID of <b>". $reward. "</b><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Add another reward... </p><p><?php
$data = array();
$data['referral'] = $referral;
$data['reward'] = 1.5;
$data['paid_on'] = now();
$data['paid_through'] = 'CHQ';
$data['cheque'] = 'Q35HTXX9';

$reward = 0;
if ($reward = ReferralReward::create($data)) {
    echo "This reward has an ID of <b>". $reward. "</b><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Show all rewards... </p><p><?php

print_array(ReferralReward::get_all());

?></p><p style="font-weight: bold;">Get total rewards paid so far... </p><p><?php
$rewards = array();
$total = 0;

if ($rewards = ReferralReward::get_all_of_referral($referral)) {
    foreach ($rewards as $row) {
        $total += $row['reward'];
    }
    
    echo "The total paid so far is <b>". number_format($total, 2, ".", ","). "</b><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><?php
?>
