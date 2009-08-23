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
    
    $query = "SELECT referrals.id, employers.name AS employer, jobs.title, jobs.description, 
              referrals.resume, resumes.name AS resume_name, 
              CONCAT(members.lastname, ', ', members.firstname) AS referrer, 
              DATE_FORMAT(referrals.employer_agreed_terms_on, '%e %b, %Y') AS formatted_employer_agreed_terms_on, 
              DATE_FORMAT(referrals.referee_acknowledged_on, '%e %b, %Y') AS formatted_referee_acknowledged_on 
              FROM referrals 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN members ON members.email_addr = referrals.member 
              LEFT JOIN resumes ON resumes.id = referrals.resume 
              WHERE referrals.referee = '". $_POST['id']. "' AND 
              (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
              (referrals.referee_confirmed_hired_on IS NULL OR referrals.referee_confirmed_hired_on = '0000-00-00 00:00:00') AND 
              (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') AND 
              (referrals.referee_acknowledged_others_on IS NULL OR referrals.referee_acknowledged_others_on = '0000-00-00 00:00:00') AND
              (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
              referrals.employer_rejected_on IS NULL 
              ORDER BY ". $order_by;
    
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }
    
    if (!$result) {
        echo "ko";
        exit();
    }
    
    foreach($result as $i=>$row) {
        $result[$i]['description'] = htmlspecialchars_decode($row['description']);
        $result[$i]['title'] = htmlspecialchars_decode($row['title']);
        $result[$i]['referrer'] = htmlspecialchars_decode($row['referrer']);
    }
    
    $response = array('referrals' => array('referral' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'confirm_hire') {
    $data = array();
    $data['id'] = $_POST['id'];
    $data['referee_confirmed_hired_on'] = now();
    
    if (!Referral::update($data)) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}
?>
