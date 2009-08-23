<?php
session_start();

require_once "private/lib/utilities.php";
require_once "private/lib/classes/pages/feedback_page.php";

$welcome = new FeedbackPage ();

if (isset($_GET['error'])) {
    $welcome->set_error($_GET['error']);
} elseif (isset($_GET['success'])) {
    $welcome->set_success();
}

$welcome->header(array('title' => 'Feedback'));
$welcome->insert_feedback_css();
$welcome->insert_feedback_scripts();
$welcome->insert_inline_scripts();
$welcome->show($_SESSION['yel']['feedback']);
$welcome->footer();
?>