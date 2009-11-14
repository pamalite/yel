<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id'])) {
    echo "ko";
    exit();
    //redirect_to('login.php');
}

$xml_dom = new XMLDOM();

if (!isset($_POST['action'])) {
    $order_by = 'ucr.added_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT jobs.title AS job, ucr.file_name AS resume_label, ucr.job_id, 
              ucr.file_hash, ucr.candidate_phone_num AS candidate_phone_num, 
              ucr.referrer_phone_num AS referrer_phone_num, ucr.candidate_email_addr AS candidate_email_addr, 
              ucr.referrer_email_addr AS referrer_email_addr, 
              ucr.candidate_zip AS candidate_zip, ucr.referrer_zip AS referrer_zip, 
              candidate_countries.country AS candidate_country, 
              referrer_countries.country AS referrer_country, 
              CONCAT(ucr.candidate_firstname, ', ', ucr.candidate_lastname) AS candidate, 
              CONCAT(ucr.referrer_firstname, ', ', ucr.referrer_lastname) AS referrer, 
              DATE_FORMAT(ucr.added_on, '%e %b, %Y') AS formatted_added_on 
              FROM users_contributed_resumes AS ucr 
              LEFT JOIN jobs ON jobs.id = ucr.job_id 
              LEFT JOIN countries AS candidate_countries ON candidate_countries.country_code = ucr.candidate_country 
              LEFT JOIN countries AS referrer_countries ON referrer_countries.country_code = ucr.referrer_country 
              ORDER BY ". $_POST['order_by'];
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if (count($result) <= 0 || is_null($result) || empty($result)) {
        echo '0';
        exit();
    }
    
    if (!$result) {
        echo 'ko';
        exit();
    }
    
    foreach($result as $i=>$row) {
        $result[$i]['candidate'] = htmlspecialchars_decode(html_entity_decode(stripslashes(desanitize($row['candidate']))));
        $result[$i]['referrer'] = htmlspecialchars_decode(html_entity_decode(stripslashes(desanitize($row['referrer']))));
    }
    
    $response = array('uploaded_resumes' => array('uploaded_resume' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

?>
