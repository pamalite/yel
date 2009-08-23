<?php
session_start();

require_once "private/lib/utilities.php";
require_once "private/lib/classes/pages/tour_page.php";

$welcome = new TourPage ();
$welcome->header(array('title' => 'Take a Tour'));
$welcome->insert_tour_css();
$welcome->insert_tour_scripts();
$welcome->show();
$welcome->footer();
?>