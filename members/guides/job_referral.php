<?php
session_start();

require_once dirname(__FILE__). "/../../private/lib/utilities.php";
require_once dirname(__FILE__). "/../../private/lib/classes/pages/job_referral_guide_page.php";

$welcome = new JobReferralGuidePage ();
$welcome->header(array('title' => 'How to Make a Job Referral?'));
$welcome->insert_guide_css();
$welcome->insert_guide_scripts();
$welcome->show();
?>