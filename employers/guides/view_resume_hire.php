<?php
session_start();

require_once dirname(__FILE__). "/../../private/lib/utilities.php";
require_once dirname(__FILE__). "/../../private/lib/classes/pages/view_resume_hire_guide_page.php";

$welcome = new ViewResumeHireGuidePage ();
$welcome->header(array('title' => 'How to View Resumes &amp; Hire Candidates?'));
$welcome->insert_guide_css();
$welcome->insert_guide_scripts();
$welcome->show();
?>