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
    $order_by = 'requested_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT referral_requests.id, employers.name AS employer, jobs.id AS job_id, jobs.title, 
              jobs.potential_reward, jobs.currency, referral_requests.resume, 
              referral_requests.member AS candidate_id, referral_requests.requested_on, 
              CONCAT(members.lastname, ', ', members.firstname) AS candidate, jobs.description, 
              DATE_FORMAT(referral_requests.requested_on, '%e %b, %Y') AS formatted_requested_on, 
              referral_requests.referrer_read_resume_on AS read_resume, 
              '1' AS is_request 
              FROM referral_requests 
              LEFT JOIN jobs ON jobs.id = referral_requests.job 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN members ON members.email_addr = referral_requests.member 
              LEFT JOIN member_referees ON member_referees.member = referral_requests.referrer AND 
              member_referees.referee = referral_requests.member
              WHERE referral_requests.referrer = '". $_POST['id']. "' AND 
              member_referees.member = '". $_POST['id']. "' AND 
              referral_requests.rejected = 'N' AND 
              (referral_requests.referrer_acknowledged_on IS NULL OR referral_requests.referrer_acknowledged_on = '0000-00-00 00:00:00') AND 
              (referral_requests.acknowledged_by_others_on IS NULL OR referral_requests.acknowledged_by_others_on = '0000-00-00 00:00:00') 
              AND (jobs.closed = 'N' AND jobs.expire_on >= NOW()) 
              UNION 
              SELECT referrals.id, employers.name, jobs.id, jobs.title, 
              jobs.potential_reward, jobs.currency, referrals.resume, 
              referrals.referee, referrals.referee_acknowledged_on, 
              CONCAT(referees.lastname, ', ', referees.firstname), jobs.description, 
              DATE_FORMAT(referrals.referee_acknowledged_on, '%e %b, %Y'), 
              referrals.member_read_resume_on, 
              '0' 
              FROM referrals 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN members AS referees ON referees.email_addr = referrals.referee 
              LEFT JOIN member_referees ON member_referees.member = referrals.member AND 
              member_referees.referee = referrals.referee
              WHERE referrals.member = '". $_POST['id']. "' AND 
              member_referees.member = '". $_POST['id']. "' AND 
              (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
              referrals.resume IS NOT NULL AND 
              (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
              (referrals.member_confirmed_on IS NULL OR referrals.member_confirmed_on = '0000-00-00 00:00:00') AND 
              (referrals.member_rejected_on IS NULL OR referrals.member_rejected_on = '0000-00-00 00:00:00') 
              AND (jobs.closed = 'N' AND jobs.expire_on >= NOW()) 
              ORDER BY ". $order_by;

    if ($_POST['id'] == 'initial@yellowelevator.com') {
        $query = "SELECT referral_requests.id, employers.name AS employer, jobs.id AS job_id, jobs.title, 
                  jobs.potential_reward, jobs.currency, referral_requests.resume, 
                  referral_requests.member AS candidate_id, referral_requests.requested_on, 
                  CONCAT(members.lastname, ', ', members.firstname) AS candidate, jobs.description, 
                  DATE_FORMAT(referral_requests.requested_on, '%e %b, %Y') AS formatted_requested_on, 
                  '1' AS is_request 
                  FROM referral_requests 
                  LEFT JOIN jobs ON jobs.id = referral_requests.job 
                  LEFT JOIN employers ON employers.id = jobs.employer 
                  LEFT JOIN members ON members.email_addr = referral_requests.member 
                  WHERE referral_requests.referrer = 'initial@yellowelevator.com' AND 
                  referral_requests.rejected = 'N' AND 
                  (referral_requests.referrer_acknowledged_on IS NULL OR referral_requests.referrer_acknowledged_on = '0000-00-00 00:00:00') AND 
                  (referral_requests.acknowledged_by_others_on IS NULL OR referral_requests.acknowledged_by_others_on = '0000-00-00 00:00:00') 
                  AND (jobs.closed = 'N' AND jobs.expire_on >= NOW()) 
                  ORDER BY ". $order_by;
    }
    
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
        $result[$i]['description'] = htmlspecialchars_decode(html_entity_decode($row['description']));
        $result[$i]['title'] = htmlspecialchars_decode($row['title']);
        $result[$i]['candidate'] = htmlspecialchars_decode($row['candidate']);
        $result[$i]['potential_reward'] = number_format($row['potential_reward'], 2, '.', ', ');
    }
    
    $response = array('requests' => array('request' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'close_request') {
    $data = array();
    $data['id'] = $_POST['id'];
    $data['referrer_acknowledged_on'] = now();
    
    ReferralRequests::update($data);
    ReferralRequests::close_similar_requests_with($data['id']);
    
    exit();
}

if ($_POST['action'] == 'reject_request') {
    $query = '';
    if ($_POST['is_request'] == '1') {
        $query = "UPDATE referral_requests SET rejected = 'Y' WHERE id = ". sanitize($_POST['id']);
    } else {
        $query = "UPDATE referrals SET 
                  member_rejected_on = NOW(), member_confirmed_on = NULL 
                  WHERE id = ". sanitize($_POST['id']);
    }
    $mysqli = Database::connect();
    $mysqli->execute($query);
}

if ($_POST['action'] == 'read_resume') {
    $query = '';
    if ($_POST['is_request'] == '1') {
        $query = "UPDATE referral_requests SET referrer_read_resume_on = NOW() WHERE id = ". sanitize($_POST['id']);
    } else {
        $query = "UPDATE referrals SET member_read_resume_on = NOW() WHERE id = ". sanitize($_POST['id']);
    }
    $mysqli = Database::connect();
    if ($mysqli->execute($query)) {
        echo '1';
        exit();
    }
    
    echo '0';
    exit();
}

?>
