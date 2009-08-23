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
    $order_by = 'referred_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT referrals.id, employers.name AS employer, jobs.title AS title, 
              CONCAT(referrers.lastname, ', ', referrers.firstname) AS referrer, 
              CONCAT(candidates.lastname, ', ', candidates.firstname) AS candidate,  
              employers.id AS employer_id, candidates.email_addr AS candidate_email, 
              DATE_FORMAT(referrals.referred_on, '%e %b, %Y') AS formatted_referred_on, 
              DATE_FORMAT(referrals.referee_acknowledged_on, '%e %b, %Y') AS formatted_acknowledged_on, 
              DATE_FORMAT(referrals.employer_agreed_terms_on, '%e %b, %Y') AS formatted_agreed_terms_on, 
              DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on, 
              DATE_FORMAT(referrals.work_commence_on, '%e %b, %Y') AS formatted_commence_on, 
              DATE_FORMAT(referrals.referee_confirmed_hired_on, '%e %b, %Y') AS formatted_confirmed_on, 
              DATE_FORMAT(referrals.employment_contract_received_on, '%e %b, %Y') AS formatted_coe_received_on 
              FROM referrals 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN employees ON employers.registered_by = employees.id 
              LEFT JOIN members AS referrers ON referrers.email_addr = referrals.member 
              LEFT JOIN members AS candidates ON candidates.email_addr = referrals.referee 
              WHERE  (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
              ((referrals.referee_confirmed_hired_on IS NOT NULL AND referrals.referee_confirmed_hired_on <> '0000-00-00 00:00:00') OR 
              (referrals.employed_on IS NOT NULL AND referrals.employed_on <> '0000-00-00 00:00:00')) AND
              (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
              (referrals.employer_rejected_on IS NULL OR referrals.employer_rejected_on = '0000-00-00 00:00:00') AND 
              (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') AND 
              employees.branch = ". $_SESSION['yel']['employee']['branch']['id']. " 
              ORDER BY ". $_POST['order_by'];
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
        $result[$i]['referrer'] = htmlspecialchars_decode($row['referrer']);
        $result[$i]['candidate'] = htmlspecialchars_decode($row['candidate']);
    }
    
    $response = array('referrals' => array('referral' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'confirm_coe_reception') {
    $data = array();
    $data['id'] = $_POST['id'];
    $data['employment_contract_received_on'] = now();
    
    if (!Referral::update($data)) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_candidate_contact') {
    $query = "SELECT CONCAT(lastname, ', ', firstname) AS name,  
              phone_num AS candidate_phone, email_addr AS candidate_email 
              FROM members 
              WHERE email_addr = '". $_POST['id']. "'";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    
    $response = array('contacts' => $result);
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_employer_contact') {
    $query = "SELECT contact_person, name, 
              phone_num AS employer_phone, email_addr AS employer_email 
              FROM employers 
              WHERE id = '". $_POST['id']. "'";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    
    $response = array('contacts' => $result);
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

?>
