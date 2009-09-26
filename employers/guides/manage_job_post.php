<?php
session_start();

require_once dirname(__FILE__). "/../../private/lib/utilities.php";
require_once dirname(__FILE__). "/../../private/lib/classes/pages/manage_job_post_guide_page.php";

$welcome = new ManageJobPostGuidePage ();
$welcome->header(array('title' => 'How to Create &amp; Publish a Job Ad?'));
$welcome->insert_guide_css();
$welcome->insert_guide_scripts();
$welcome->show();
?>