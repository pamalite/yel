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
    $order_by = 'employed_on ASC';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT referrals.id AS referral, currencies.symbol AS currency, referrals.total_token_reward, 
              jobs.title, employers.name AS employer, 
              referrals.referee AS candidate_id, referrals.employed_on, 
              CONCAT(members.lastname, ', ', members.firstname) AS candidate, 
              DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on, 
              DATE_FORMAT(referrals.referee_confirmed_hired_on, '%e %b, %Y') AS formatted_referee_confirmed_on 
              FROM referrals 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN members ON members.email_addr = referrals.member 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN currencies ON currencies.country_code = employers.country 
              LEFT JOIN employees ON employers.registered_by = employees.id 
              WHERE (referrals.employed_on IS NOT NULL AND referrals.employed_on <> '0000-00-00 00:00:00') AND 
              (referrals.member_confirmed_on IS NOT NULL AND referrals.member_confirmed_on <> '0000-00-00 00:00:00') AND 
              (referrals.total_token_reward IS NOT NULL AND referrals.total_token_reward > 0) AND 
              (referrals.employer_removed_on IS NULL OR referrals.employer_removed_on = '0000-00-00 00:00:00') AND 
              (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
              (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') AND 
              -- (referrals.guarantee_expire_on <= CURDATE() OR referrals.guarantee_expire_on IS NULL) AND 
              employees.branch = ". $_SESSION['yel']['employee']['branch']['id']. " AND 
              referrals.id NOT IN (SELECT referral FROM referral_token_rewards) 
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
    
    $i = 0;
    $rewards = array();
    foreach($result as $row) {
        $paid = ReferralReward::get_sum_paid_of_referral($row['referral']);
        if ($paid[0]['amount'] <= 0 || is_null($paid)) {
            //$row['member'] = htmlspecialchars_decode($row['member']);
            $row['padded_invoice'] = pad($row['invoice'], 11, '0');
            $row['total_token_reward'] = number_format($row['total_token_reward'], 2, '.', ', ');
            
            $rewards[$i] = $row;
            $i++;
        }
    }
    
    if (count($rewards) <= 0 || is_null($rewards)) {
        echo '0';
        exit();
    }
    
    $response = array('rewards' => array('reward' => $rewards));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_fully_paid') {
    $order_by = 'fully_paid_on DESC';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT referrals.id AS referral, currencies.symbol AS currency, referrals.total_token_reward, 
              jobs.title, employers.name AS employer, referral_token_rewards.paid_through, 
              referral_token_rewards.cheque, referral_token_rewards.receipt, 
              member_banks.bank, member_banks.account, 
              referrals.referee AS candidate_id, referrals.employed_on, 
              referral_token_rewards.paid_on AS fully_paid_on, 
              CONCAT(members.lastname, ', ', members.firstname) AS candidate, 
              DATE_FORMAT(referrals.referee_confirmed_hired_on, '%e %b, %Y') AS formatted_referee_confirmed_on, 
              DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on, 
              DATE_FORMAT(referral_token_rewards.paid_on, '%e %b, %Y') AS formatted_fully_paid_on  
              FROM referrals 
              LEFT JOIN referral_token_rewards ON referral_token_rewards.referral = referrals.id 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN members ON members.email_addr = referrals.member 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN currencies ON currencies.country_code = employers.country 
              LEFT JOIN employees ON employers.registered_by = employees.id 
              LEFT JOIN member_banks ON member_banks.id = referral_token_rewards.bank 
              WHERE (referrals.employed_on IS NOT NULL AND referrals.employed_on <> '0000-00-00 00:00:00') AND 
              (referrals.member_confirmed_on IS NOT NULL AND referrals.member_confirmed_on <> '0000-00-00 00:00:00') AND 
              (referral_token_rewards.paid_on IS NOT NULL AND referral_token_rewards.paid_on <> '0000-00-00 00:00:00') AND 
              (referrals.total_token_reward IS NOT NULL AND referrals.total_token_reward > 0) AND 
              (referrals.employer_removed_on IS NULL OR referrals.employer_removed_on = '0000-00-00 00:00:00') AND 
              (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
              (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') AND 
              -- (referrals.guarantee_expire_on <= CURDATE() OR referrals.guarantee_expire_on IS NULL) AND 
              employees.branch = ". $_SESSION['yel']['employee']['branch']['id']. " 
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
    
    $i = 0;
    $rewards = array();
    foreach($result as $row) {
        $row['total_token_reward'] = number_format($row['total_token_reward'], 2, '.', ', ');
        $rewards[$i] = $row;
    }
    
    if (count($rewards) <= 0 || is_null($rewards)) {
        echo '0';
        exit();
    }
    
    $response = array('rewards' => array('reward' => $rewards));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_member_name') {
    $query = "SELECT CONCAT(lastname, ', ', firstname) AS fullname 
              FROM members 
              WHERE email_addr = '". $_POST['id']. "'";
    $mysqli = Database::connect();
    $fullname = $mysqli->query($query);
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('member' => array('fullname' => $fullname[0]['fullname'])));
    exit();
}

if ($_POST['action'] == 'get_banks') {
    $member = new Member($_POST['id']);
    $result = $member->get_banks();
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }
    
    if (!$result) {
        echo 'ko';
        exit();
    }
    
    $response = array('bank_accounts' => array('bank_account' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'confirm_payment') {
    $data = array();
    $data['referral'] = $_POST['id'];
    $data['token'] = $_POST['amount'];
    $data['paid_on'] = now();
    $data['paid_through'] = $_POST['payment_mode'];
    $data['bank'] = ($_POST['bank'] == '0' || empty($_POST['bank'])) ? 'NULL' : $_POST['bank'];
    $data['cheque'] = $_POST['cheque'];
    $data['receipt'] = $_POST['receipt'];
    
    if (!ReferralTokenReward::create($data)) {
        echo 'ko';
        exit();
    }
    
    $query = "SELECT referrals.referee AS email_addr, 
              CONCAT(members.firstname, ', ', members.lastname) AS candidate, 
              jobs.title AS job, employers.name AS employer, currencies.symbol AS currency 
              FROM referrals 
              LEFT JOIN members ON members.email_addr = referrals.referee 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN currencies ON currencies.country_code = employers.country 
              WHERE referrals.id = ". $_POST['id']. " LIMIT 1";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    $candidate_email = $result[0]['email_addr'];
    $candidate = htmlspecialchars_decode($result[0]['candidate']);
    $job = htmlspecialchars_decode($result[0]['job']);
    $employer = htmlspecialchars_decode($result[0]['employer']);
    $token = number_format($_POST['amount'], 2, '.', ',');
    $currency = $result[0]['currency'];
    
    $lines = file('../private/mail/candidate_token_note.txt');
    $message = '';
    foreach ($lines as $line) {
        $message .= $line;
    }
    
    $message = str_replace('%company%', $employer, $message);
    $message = str_replace('%job%', $job, $message);
    $message = str_replace('%candidate%', $candidate, $message);
    $message = str_replace('%token%', $token, $message);
    $message = str_replace('%currency%', $currency, $message);
    $subject = 'Congratulations! Your bonus for the '. $job. ' position has been awarded';
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($candidate_email, $subject, $message, $headers);
    
    /*$handle = fopen('/tmp/ref_email_to_'. $candidate_email. '.txt', 'w');
    fwrite($handle, 'Subject: '. $subject. "\n\n");
    fwrite($handle, $message);
    fclose($handle);*/
    
    echo 'ok';
    exit();
}

?>
