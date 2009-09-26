<?php
session_start();

require_once "private/lib/utilities.php";
require_once "private/lib/classes/pages/faq_page.php";

$welcome = new FaqPage ();
$welcome->header(array('title' => 'Frequently Asked Questions'));
$welcome->insert_faq_css();
$welcome->insert_faq_scripts();
$welcome->show();
$welcome->footer();
?>