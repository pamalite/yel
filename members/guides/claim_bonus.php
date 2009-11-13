<?php
session_start();

require_once dirname(__FILE__). "/../../private/lib/utilities.php";
require_once dirname(__FILE__). "/../../private/lib/classes/pages/claim_bonus_guide_page.php";

$welcome = new ClaimBonusGuidePage ();
$welcome->header(array('title' => 'How to Claim My Bonuses?'));
$welcome->insert_guide_css();
$welcome->insert_guide_scripts();
$welcome->show();
?>