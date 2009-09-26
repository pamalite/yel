<?php
session_start();

require_once dirname(__FILE__). "/../../private/lib/utilities.php";
require_once dirname(__FILE__). "/../../private/lib/classes/pages/apply_job_guide_page.php";

$welcome = new ApplyJobGuidePage ();
$welcome->header(array('title' => 'How to Apply for a Job Position?'));
$welcome->insert_guide_css();
$welcome->insert_guide_scripts();
$welcome->show();
?>