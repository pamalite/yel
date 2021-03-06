<?php
// General settings
$COMPANYNAME = 'Yellow Elevator';
$protocol = 'http'; // change this https when launched
$root = $_SERVER['SERVER_NAME']. "/yel";
$root_path = $_SERVER['DOCUMENT_ROOT']. "/yel";
$data_path = "/Users/pamalite/yel3_files/db";
$forex_feed = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml'; // the file is updated daily between 14:15 and 15:00 CET
$openinviter_path = '/../../openinviter';

// LinkedIn settings
$linkedin_api_key = 'R2lTi8uK9ROT8_1AfK_h5KWRBuMpycGGxGw0Dqn6_aXbHlRYwA-vqTe1eozKzAUo';
$linkedin_secret_key = 'LsgYZUxq5lAcmrXaKJMgPckN3R9pnfqI29kHybevAKCCDdz3_YjzITkGtKbTkZiY';

// Geographical settings
$default_country_code = "MY";
$default_country = "Malaysia";
$default_currency_symbol = "MYR";
$maxmind_geoip_data_file = $data_path. "/maxmind/geoip.dat";

// Search settings
$default_results_per_page = 10;

// Resume upload settings
$resume_dir = $data_path. "/resumes";
$buffered_resume_dir = $data_path. "/buffered_resumes";
$resume_size_limit = "1048576 "; // ~1MB
$allowable_resume_types = array("text/plain",
                                "text/html",
                                "application/pdf",
                                "application/msword");

// Photo upload settings
$photo_dir = $data_path. "/photos";
$max_photo_resolution = array('width' => 200, 'height' => 220);
$photo_size_limit = "153600"; // ~150KB
$allowable_photo_types = array("image/gif",
                               "image/jpeg",
                               "image/png",
                               "image/tiff",
                               "image/bmp");

?>
