<?php
session_start();

require_once "private/lib/utilities.php";
require_once "private/lib/classes/pages/welcome_page.php";

// 1. Check whether session has been initialized
if (!isset($_SESSION['yel'])) {
    redirect_to('index.php');
}

if (isset($_SESSION['yel']['employer'])) {
    //redirect_to('employers/index.php');
}

if (isset($_SESSION['yel']['member'])) {
    //redirect_to('members/index.php');
}

// 1.5 Log visitor
$mysqli = Database::connect();
$gi = geoip_open($GLOBALS['maxmind_geoip_data_file'], GEOIP_STANDARD);
$country = geoip_country_code_by_addr($gi, $_SERVER['REMOTE_ADDR']);
geoip_close($gi);

if (empty($country) || is_null($country)) {
    $country = '??';
}

$http_referer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : "";
$query = "INSERT INTO visitors SET 
          ip_address = '". $_SERVER['REMOTE_ADDR']. "', 
          country = '". $country. "', 
          visited_on = NOW(), 
          user_agent = '". $_SERVER['HTTP_USER_AGENT']. "', 
          http_referer = '". $http_referer . "'";
$mysqli->execute($query);

// 2. Generate page
$welcome = new WelcomePage ();
$welcome->header(array('title' => 'Welcome'));
$welcome->insert_welcome_css();
$welcome->insert_welcome_scripts();
$welcome->insert_inline_scripts();
// $welcome->insert_linkedin_jsscript();
$welcome->show();
$welcome->footer();
?>