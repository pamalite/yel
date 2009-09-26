<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id'])) {
    echo "ko";
    exit();
    //redirect_to('login.php');
}

$xml_dom = new XMLDOM();

if ($_POST['action'] == 'get_recent_referrals') {
    $query = "SELECT industries.industry, jobs.id, jobs.title, 
              COUNT(referrals.id) AS num_referrals, jobs.description, 
              DATE_FORMAT(jobs.created_on, '%e %b, %Y') AS formatted_created_on, 
              DATE_FORMAT(jobs.expire_on, '%e %b, %Y') AS formatted_expire_on  
              FROM referrals 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN industries ON industries.id = jobs.industry 
              WHERE jobs.employer = '". $_POST['id']. "' AND 
              (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
              (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
              referrals.employer_removed_on IS NULL 
              GROUP BY referrals.job 
              ORDER BY num_referrals DESC, referrals.referee_acknowledged_on DESC 
              LIMIT 10";
    
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
    
    $response = array('referrals' => array('referral' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_new_invoices') {
    $query = "SELECT id, type, payable_by, 
              DATE_FORMAT(issued_on, '%e %b, %Y') AS formatted_issued_on, 
              DATE_FORMAT(payable_by, '%e %b, %Y') AS formatted_payable_by,
              DATE_FORMAT(paid_on, '%e %b, %Y') AS formatted_paid_on 
              FROM invoices 
              WHERE employer = '". $_POST['id']. "' AND paid_on IS NULL 
              ORDER BY issued_on DESC";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if (count($result) <= 0 || is_null($result)) {
        echo "0";
        exit();
    }
    
    if (!$result) {
        echo "ko";
        exit();
    }
    
    $today = today();
    foreach($result as $i=>$row) {
        $result[$i]['padded_id'] = pad($row['id'], 11, '0');
        $delta = sql_date_diff($today, $row['payable_by']);
        if ($delta > 0) {
            $result[$i]['expired'] = 'expired';
        } else if ($delta == 0) {
            $result[$i]['expired'] = 'nearly';
        } else {
            $result[$i]['expired'] = 'no';
        }
    }
    
    $response = array('invoices' => array('invoice' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}
