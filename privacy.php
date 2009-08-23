<?php
session_start();

require_once "private/lib/utilities.php";
require_once "private/lib/classes/pages/privacy_page.php";

$welcome = new PrivacyPage ();
$welcome->header(array('title' => 'Privacy Policy'));
$welcome->insert_privacy_css();
$welcome->insert_privacy_scripts();
$welcome->show();
$welcome->footer();
?>