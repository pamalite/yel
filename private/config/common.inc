<?php
// General settings
$COMPANYNAME = 'Yellow Elevator';
$protocol = 'http'; // change this https when launched
$root = $_SERVER['SERVER_NAME']. "/yel";
$root_path = $_SERVER['DOCUMENT_ROOT']. "/yel";
$data_path = "/Users/pamalite/yel2_files/db";
$forex_feed = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml'; // the file is updated daily between 14:15 and 15:00 CET
$openinviter_path = '/../../openinviter';

// Geographical settings
$default_country_code = "MY";
$default_country = "Malaysia";
$default_currency_symbol = "MYR";
$maxmind_geoip_data_file = $data_path. "/maxmind/geoip.dat";

// Search settings
$default_results_per_page = 20;

// Resume upload settings
$resume_dir = $data_path. "/resumes";
$resume_size_limit = "2097152"; // ~2MB
$allowable_resume_types = array("text/plain",
                                "text/html",
                                "application/pdf",
                                "application/msword",
                                "application/rtf");

// Photo upload settings
$photo_dir = $data_path. "/photos";
$max_photo_resolution = array('width' => 1024, 'height' => 1024);
$photo_size_limit = "2097152"; // ~2MB
$allowable_photo_types = array("image/gif",
                               "image/jpeg",
                               "image/png",
                               "image/tiff",
                               "image/bmp");

// Email settings
$invitation_referrer_email = $root_path. "/etc/invitation_email_referrer.txt";
$welcome_referrer_email = $root_path. "/etc/welcome_email_referrer.html";
$referrer_signup_url = $protocol. "://". $root. "/members/signup.php?e=[referrer_email]&r=[premium_logonid]";
$referred_prospect_email = "etc/referred_email_prospect.html";
$referrer_password_changed_email = $root_path. "/etc/password_changed_referrer.txt";
$prospect_password_changed_email = $root_path. "/etc/password_changed_prospect.txt";
$confirmed_hiring_url = $protocol. "://". $root. "/members/respond.php?sid=[sid]&code=[code]";
?>
