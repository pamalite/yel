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
$referral = new Referral();

?><p style="font-weight: bold;">Show all referrals... </p><p><?php
$criteria = array(
    'columns' => '*',
    'order' => 'referred_on DESC',
    'limit' => '1'
);
print_array($referral->find($criteria));

?></p><p style="font-weight: bold;">Add a referral... </p><p><?php
$ref_1 = 0;
$ref_2 = 0;
$ref_3 = 0;

$data = array();
$data['member'] = $uid;
$data['referee'] = $rid;
$data['job'] = 159;
$data['referred_on'] = now();

if ($referral->create($data) !== false) {
    $ref_1 = $referral->getId();
    echo "This referral has an ID of <b>". $ref_1. "</b><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Add another referral... </p><p><?php
$data = array();
$data['member'] = $uid;
$data['referee'] = $rid;
$data['job'] = 160;
$data['referred_on'] = now();

if ($referral->create($data) !== false) {
    $ref_2 = $referral->getId();
    echo "This referral has an ID of <b>". $ref_2. "</b><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Add another referral... </p><p><?php
$data = array();
$data['member'] = $rid;
$data['referee'] = $uid;
$data['job'] = 161;
$data['referred_on'] = now();
$testimony = array('Known member for 3 years.', 'Good in cartooning', 'Good people skills');
$data['testimony'] = Referral::serializeTestimony($testimony);

if ($referral->create($data) !== false) {
    $ref_3 = $referral->getId();
    echo "This referral has an ID of <b>". $ref_3. "</b><br><br>";
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Show all referrals... </p><p><?php
$criteria = array(
    'columns' => '*',
    'order' => 'referred_on DESC',
    'limit' => '5'
);
print_array($referral->find($criteria));

?></p><p style="font-weight: bold;">Referee confirms for referral 3... </p><p><?php
$referral = new Referral($ref_3);
$data = array();
$data['referee_acknowledged_on'] = now();
$data['resume'] = 1;

if ($referral->update($data) !== false) {
    print_array($referral->get());
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Referee confirms for referral 1... </p><p><?php
$referral = new Referral($ref_1);
$data = array();
$data['referee_acknowledged_on'] = now();
$data['resume'] = 103;

if ($referral->update($data) !== false) {
    print_array($referral->get());
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Employer decides to shortlist referral 3... </p><p><?php
$referral = new Referral($ref_3);
$data = array();
$data['employer_agreed_terms_on'] = now(); // This should be done before the shortlisting.
$data['shortlisted_on'] = now();

if ($referral->update($data) !== false) {
    print_array($referral->get());
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Employer decides to employ referral 3... </p><p><?php
$data = array();
$data['employed_on'] = now(); 
$data['work_commence_on'] = today();
$data['salary_per_annum'] = 36000.00;
$data['total_reward'] = $referral->calculateRewardFrom($data['salary_per_annum']);

if ($referral->update($data)) {
    print_array($referral->get());
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Remove the test referrals... </p><p><?php
$query = "DELETE FROM referrals WHERE id IN (". $ref_1. ", ". $ref_2. ", ". $ref_3. ")";
echo $query;

$mysqli = Database::connect();
print_r($mysqli->execute($query));
?></p><?php
?>
