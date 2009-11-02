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
    $order_by = 'prvileged_referral_buffers.referred_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $employee = new Employee($_POST['id']);
    $branch = $employee->get_branch();
    $member = 'team.'. strtolower($branch[0]['country_code']). '@yellowelevator.com';
    
    $query = "SELECT jobs.id, jobs.title, employers.name AS employer, industries.industry, 
              members.email_addr AS candidate_email, members.phone_num, 
              resumes.name AS resume, privileged_referral_buffers.resume AS resume_id, 
              CONCAT(members.firstname, ', ', members.lastname) AS candidate, 
              DATE_FORMAT(privileged_referral_buffers.referred_on, '%e %b, %Y') AS formatted_referred_on 
              FROM privileged_referral_buffers 
              LEFT JOIN jobs ON jobs.id = privileged_referral_buffers.job 
              LEFT JOIN members ON members.email_addr = privileged_referral_buffers.referee 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN industries ON industries.id = jobs.industry 
              LEFT JOIN resumes ON resumes.id = privileged_referral_buffers.resume 
              WHERE privileged_referral_buffers.member = '". $member. "' 
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
        $result[$i]['candidate'] = htmlspecialchars_decode($row['candidate']);
    }
    
    $response = array('referrals' => array('referral' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_in_process') {
    $order_by = 'referrals.referred_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $employee = new Employee($_POST['id']);
    $branch = $employee->get_branch();
    $member = 'team.'. strtolower($branch[0]['country_code']). '@yellowelevator.com';
    
    $query = "SELECT referrals.id, employers.name AS employer, jobs.id AS job_id, jobs.title AS title, 
              CONCAT(members.lastname, ', ', members.firstname) AS candidate, industries.industry, 
              members.email_addr AS candidate_email, members.phone_num, 
              resumes.name AS resume, referrals.resume AS resume_id, 
              DATE_FORMAT(referrals.referred_on, '%e %b, %Y') AS formatted_referred_on, 
              DATE_FORMAT(referrals.employer_agreed_terms_on, '%e %b, %Y') AS formatted_agreed_terms_on 
              FROM referrals 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN industries ON industries.id = jobs.industry 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN members ON members.email_addr = referrals.member 
              LEFT JOIN resumes ON resumes.id = referrals.resume 
              WHERE ((referrals.referee_confirmed_hired_on IS NOT NULL AND referrals.referee_confirmed_hired_on <> '0000-00-00 00:00:00') OR 
              (referrals.employed_on IS NOT NULL AND referrals.employed_on <> '0000-00-00 00:00:00')) AND
              (referrals.employer_removed_on IS NULL OR referrals.employer_removed_on = '0000-00-00 00:00:00') AND 
              (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') AND 
              referrals.member = '". $member. "' 
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
        $result[$i]['candidate'] = htmlspecialchars_decode($row['candidate']);
    }
    
    $response = array('referrals' => array('referral' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_employed') {
    $order_by = 'referrals.employed_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $employee = new Employee($_POST['id']);
    $branch = $employee->get_branch();
    $member = 'team.'. strtolower($branch[0]['country_code']). '@yellowelevator.com';
    
    $query = "SELECT invoices.id AS invoice, referrals.id AS referral_id, currencies.symbol AS currency, 
              referrals.total_reward, referrals.job AS job_id, jobs.title, employers.name AS employer, 
              referrals.member AS member_id, referrals.employed_on, industries.industry, 
              CONCAT(members.lastname, ', ', members.firstname) AS candidate, 
              members.phone_num AS candidate_phone_num, members.email_addr AS candidate_email, 
              resumes.name AS resume, referrals.resume AS resume_id, 
              CONCAT(recommenders.lastname, ', ', recommenders.firstname) AS recommender, 
              recommenders.phone_num AS recommender_phone_num, recommenders.email_addr AS recommender_email, 
              recommender_tokens.token AS recommender_token, 
              DATE_FORMAT(referrals.referred_on, '%e %b, %Y') AS formatted_referred_on, 
              DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on, 
              DATE_FORMAT(invoices.paid_on, '%e %b, %Y') AS formatted_invoice_paid_on, 
              DATE_FORMAT(recommender_tokens.presented_on, '%e %b, %Y') AS formatted_token_presented_on, 
              DATEDIFF(referrals.guarantee_expire_on, NOW()) AS guarantee_expire_in  
              FROM referrals 
              LEFT JOIN invoice_items ON invoice_items.item = referrals.id 
              LEFT JOIN invoices ON invoices.id = invoice_items.invoice 
              LEFT JOIN referral_rewards ON referral_rewards.referral = referrals.id 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN industries ON industries.id = jobs.industry 
              LEFT JOIN members ON members.email_addr = referrals.referee 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN currencies ON currencies.country_code = employers.country 
              LEFT JOIN recommenders ON recommenders.email_addr = members.recommender 
              LEFT JOIN resumes ON resumes.id = referrals.resume 
              LEFT JOIN recommender_tokens ON recommender_tokens.referral = referrals.id AND 
              recommender_tokens.recommender = recommenders.email_addr 
              WHERE -- referrals.member = '". $member. "' AND 
              invoices.type = 'R' AND 
              (referrals.employed_on IS NOT NULL AND referrals.employed_on <> '0000-00-00 00:00:00') AND 
              (referrals.employer_removed_on IS NULL OR referrals.employer_removed_on = '0000-00-00 00:00:00') AND 
              (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
              (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') 
              GROUP BY referrals.id 
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
        $result[$i]['padded_invoice'] = pad($row['invoice'], 11, '0');
        $result[$i]['recommender'] = htmlspecialchars_decode(stripslashes(desanitize($row['recommender'])));
        $result[$i]['candidate'] = htmlspecialchars_decode(stripslashes(desanitize($row['candidate'])));
    }
    
    $response = array('referrals' => array('referral' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_rejected') {
    $order_by = 'referrals.referred_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $employee = new Employee($_POST['id']);
    $branch = $employee->get_branch();
    $member = 'team.'. strtolower($branch[0]['country_code']). '@yellowelevator.com';
    
    $query = "SELECT referrals.id, jobs.id AS job_id, jobs.title, employers.name AS employer, industries.industry, 
              members.email_addr AS candidate_email, members.phone_num, 
              resumes.name AS resume, referrals.resume AS resume_id, 
              CONCAT(members.firstname, ', ', members.lastname) AS candidate, 
              DATE_FORMAT(referrals.referred_on, '%e %b, %Y') AS formatted_referred_on 
              FROM referrals 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN industries ON industries.id = jobs.industry 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN members ON members.email_addr = referrals.member 
              LEFT JOIN resumes ON resumes.id = referrals.resume 
              WHERE ((referrals.employer_removed_on IS NOT NULL AND referrals.employer_removed_on <> '0000-00-00 00:00:00') OR 
              (referrals.referee_rejected_on IS NOT NULL AND referrals.referee_rejected_on <> '0000-00-00 00:00:00')) AND 
              (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
              referrals.member = '". $member. "' 
              ORDER BY ". $_POST['order_by'];
    // echo $query;
    // exit();
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
        $result[$i]['candidate'] = htmlspecialchars_decode($row['candidate']);
    }
    
    $response = array('referrals' => array('referral' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_testimony_from_referrals') {
    $query = "SELECT testimony FROM referrals WHERE id = ". $_POST['id'];
    
    $mysqli = Database::connect();
    if ($result = $mysqli->query($query)) {
        header('Content-type: text/xml');
        echo $xml_dom->get_xml_from_array(array('testimony' => htmlspecialchars_decode(desanitize($result[0]['testimony']))));
        exit();
    }
    
    echo "ko";
    exit();
}

if ($_POST['action'] == 'get_testimony_from_buffer') {
    $employee = new Employee($_POST['id']);
    $branch = $employee->get_branch();
    $member = 'team.'. strtolower($branch[0]['country_code']). '@yellowelevator.com';
    
    $query = "SELECT testimony FROM privileged_referral_buffers WHERE 
              member = '". $member. "' AND 
              referee = '". $_POST['referee']. "' AND 
              job = ". $_POST['job'];
    
    $mysqli = Database::connect();
    if ($result = $mysqli->query($query)) {
        header('Content-type: text/xml');
        echo $xml_dom->get_xml_from_array(array('testimony' => htmlspecialchars_decode(desanitize($result[0]['testimony']))));
        exit();
    }
    
    echo "ko";
    exit();
}
?>
