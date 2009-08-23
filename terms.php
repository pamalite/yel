<?php
session_start();

require_once "private/lib/utilities.php";
require_once "private/lib/classes/pages/terms_page.php";

$welcome = new TermsPage ();
$welcome->header(array('title' => 'Terms &amp; Conditions'));
$welcome->insert_terms_css();
$welcome->insert_terms_scripts();
$welcome->show();
$welcome->footer();
?>