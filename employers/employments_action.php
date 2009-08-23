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
    $order_by = 'employed_on desc';

    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }

    $query = "SELECT referrals.id, industries.industry, jobs.title, jobs.description, referrals.resume, 
              CONCAT(members.lastname, ', ', members.firstname) AS candidate, 
              DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on, 
              DATE_FORMAT(referrals.work_commence_on, '%e %b, %Y') AS formatted_work_commence_on 
              FROM referrals 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN members ON members.email_addr = referrals.referee 
              LEFT JOIN industries ON industries.id = jobs.industry 
              WHERE jobs.employer = '". $_POST['id']. "' AND 
              (referrals.employed_on IS NOT NULL AND referrals.employed_on <> '0000-00-00 00:00:00') AND 
              referrals.employer_rejected_on IS NULL AND 
              (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00')
              GROUP BY referrals.id 
              ORDER BY ". $order_by;
    
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }
    
    if (!$result) {
        echo 'ko';
        exit();
    }
    
    foreach($result as $i=>$row) {
        $result[$i]['description'] = htmlspecialchars_decode($row['description']);
        $result[$i]['title'] = htmlspecialchars_decode($row['title']);
        $result[$i]['candidate'] = htmlspecialchars_decode($row['candidate']);
    }
    
    $response = array('referrals' => array('referral' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

?>
