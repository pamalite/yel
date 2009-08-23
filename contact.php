<?php
session_start();

require_once "private/lib/utilities.php";
require_once "private/lib/classes/pages/contact_page.php";

$welcome = new ContactPage ();
$welcome->header(array('title' => 'Contact Us'));
$welcome->insert_contact_css();
$welcome->insert_contact_scripts();
$welcome->show();
$welcome->footer();
?>