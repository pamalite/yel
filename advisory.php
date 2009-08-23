<?php
session_start();

require_once "private/lib/utilities.php";
require_once "private/lib/classes/pages/advisory_page.php";

$welcome = new AdvisoryPage ();
$welcome->header(array('title' => 'Members of the Advisory Board'));
$welcome->insert_advisory_css();
$welcome->insert_advisory_scripts();
$welcome->show();
$welcome->footer();
?>