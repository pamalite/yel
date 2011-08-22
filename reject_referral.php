<?php
require_once dirname(__FILE__). '/private/lib/utilities.php';

if (!isset($_GET['id'])) {
    redirect_to('welcome.php');
}

$referral_buffer = new ReferralBuffer($_GET['id']);

// check whether the buffer has been updated before
// does the buffer exists?
$result = $referral_buffer->get();
if ($result === false || is_null($result) || empty($result)) {
    // no
    redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/index.php');
    exit();
}

// already responded?
if (!is_null($result[0]['candidate_response']) && 
    !is_null($result[0]['candidate_responded_on'])) {
    // yes
    redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/index.php');
    exit();
}

// proceed
$data['candidate_response'] = 'no';
$data['candidate_responded_on'] = now();
$referral_buffer->update($data);
redirect_to($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/index.php');
exit();
?>