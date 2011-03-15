<?php
session_start();

require_once "private/lib/utilities.php";
require_once "private/lib/classes/pages/contact_page.php";

$has_captcha_error = false;
if (isset($_GET['err'])) {
    $has_captcha_error = true;
}
$page = new ContactPage($has_captcha_error);
$page->header(array('title' => 'Contact Us'));
$page->insert_contact_css();
$page->insert_contact_scripts();
$page->insert_inline_scripts();
$page->show();
$page->footer();
?>