<?php
session_start();

require_once "private/lib/utilities.php";
require_once "private/lib/classes/pages/about_page.php";

$welcome = new AboutPage ();
$welcome->header(array('title' => 'About Us'));
$welcome->insert_about_css();
$welcome->insert_about_scripts();
$welcome->show();
$welcome->footer();
?>