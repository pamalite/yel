<?php
require_once "../private/lib/utilities.php";

function print_array($_array) {
    echo "<pre>";
    print_r($_array);
    echo "</pre><br><br>";
}

$rid = 'pamalite@gmail.com';
$uid = 'sui.cheng.wong@d-pomelo.com';
$eid = 'acme123';
$resume_id = '1';

?><p style="font-weight: bold;">Show all requests... </p><p><?php

print_array(ReferralRequests::get_all());

?></p><p style="font-weight: bold;">Add a request... </p><p><?php
$data = array();
$data['member'] = $uid;
$data['referrer'] = $rid;
$data['job'] = 2;
$data['resume'] = $resume_id;
$data['requested_on'] = now();
$request = 0;
if ($request = ReferralRequests::create($data)) {
    echo "This request has an ID of <b>". $request. "</b><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Add another request... </p><p><?php
$data = array();
$data['referrer'] = $rid;
$data['member'] = 'ommali2@gmail.com';
$data['job'] = 4;
$data['resume'] = $resume_id;
$data['requested_on'] = now();
$request = 0;
if ($request = ReferralRequests::create($data)) {
    echo "This request has an ID of <b>". $request. "</b><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Add a request with multiple referrers... </p><p><?php
$referrers = array();
$referrers[0]['id'] = 'pamalite@gmail.com';
$referrers[0]['firstname'] = 'Albert';
$referrers[0]['lastname'] = 'Einstein';

$referrers[1]['id'] = 'tyetyetye23@yahoo.com';
$referrers[1]['firstname'] = 'Albert';
$referrers[1]['lastname'] = 'Einstein 1';

$referrers[2]['id'] = 'tyetyetye24@yahoo.com';
$referrers[2]['firstname'] = 'Albert';
$referrers[2]['lastname'] = 'Einstein 2';

$referrers[3]['id'] = 'tyetyetye25@yahoo.com';
$referrers[3]['firstname'] = 'Albert';
$referrers[3]['lastname'] = 'Einstein 3';

$data = array();
$data['member'] = $rid;
$data['referrer'] = $referrers;
$data['job'] = 135;
$data['resume'] = $resume_id;
$data['requested_on'] = now();
$request = false;
if ($request = ReferralRequests::create_multiple($data)) {
    echo "This request were <b>successfully</b> send.<br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Show all requests... </p><p><?php

print_array(ReferralRequests::get_all());

?></p><p style="font-weight: bold;">Referrer confirms for referral 1... </p><p><?php
$data = array();
$data['id'] = 1;
$data['referrer_acknowledged_on'] = now();

if (ReferralRequests::update($data)) {
    ReferralRequests::close_similar_requests_with_id(1);
    print_array(ReferralRequests::get($data['id']));
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Referrer confirms for referral 4... </p><p><?php
$data = array();
$data['id'] = 4;
$data['referrer_acknowledged_on'] = now();

if (ReferralRequests::update($data)) {
    ReferralRequests::close_similar_requests_with_id(4);
    print_array(ReferralRequests::get($data['id']));
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Show all requests... </p><p><?php

print_array(ReferralRequests::get_all());

?></p>
