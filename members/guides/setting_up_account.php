<?php
session_start();

require_once dirname(__FILE__). "/../../private/lib/utilities.php";
require_once dirname(__FILE__). "/../../private/lib/classes/pages/setting_up_account_guide_page.php";

$welcome = new SettingUpAccountGuidePage ();
$welcome->header(array('title' => 'Setting Up My Account'));
$welcome->insert_guide_css();
$welcome->insert_guide_scripts();
$welcome->show();
?>