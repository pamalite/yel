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
    
    $query = "SELECT invoices.id AS invoice, referrals.id AS referral, currencies.symbol AS currency, 
              referrals.total_reward, jobs.title, employers.name AS employer, 
              referrals.member AS member_id, referrals.employed_on, 
              CONCAT(members.lastname, ', ', members.firstname) AS member, 
              DATE_FORMAT(referrals.employment_contract_received_on, '%e %b, %Y') AS formatted_contract_received_on, 
              DATE_FORMAT(referrals.referee_confirmed_hired_on, '%e %b, %Y') AS formatted_referee_confirmed_on, 
              DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on 
              FROM referrals 
              LEFT JOIN invoice_items ON invoice_items.item = referrals.id 
              LEFT JOIN invoices ON invoices.id = invoice_items.invoice 
              LEFT JOIN referral_rewards ON referral_rewards.referral = referrals.id 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN members ON members.email_addr = referrals.member 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN currencies ON currencies.country_code = employers.country 
              LEFT JOIN employees ON employers.registered_by = employees.id 
              WHERE invoices.type = 'R' AND 
              (invoices.paid_on IS NOT NULL AND invoices.paid_on <> '0000-00-00 00:00:00') AND 
              (referrals.employed_on IS NOT NULL AND referrals.employed_on <> '0000-00-00 00:00:00') AND 
              (referrals.employer_removed_on IS NULL OR referrals.employer_removed_on = '0000-00-00 00:00:00') AND 
              (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
              (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') AND 
              (referrals.guarantee_expire_on <= CURDATE() OR referrals.guarantee_expire_on IS NULL) AND 
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
        $paid = ReferralReward::get_sum_paid_of_referral($row['referral']);
        if ($paid[0]['amount'] <= 0 || is_null($paid)) {
            //$row['member'] = htmlspecialchars_decode($row['member']);
            $row['padded_invoice'] = pad($row['invoice'], 11, '0');
            $row['total_reward'] = number_format($row['total_reward'], 2, '.', ', ');
            
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

if ($_POST['action'] == 'get_partially_paid') {
    $order_by = 'last_paid_on DESC';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT invoices.id AS invoice, referrals.id AS referral, currencies.symbol AS currency, 
              referrals.total_reward, jobs.title, employers.name AS employer, 
              referrals.member AS member_id, referrals.employed_on, 
              MAX(referral_rewards.paid_on) AS last_paid_on, 
              CONCAT(members.lastname, ', ', members.firstname) AS member, 
              DATE_FORMAT(referrals.employment_contract_received_on, '%e %b, %Y') AS formatted_contract_received_on, 
              DATE_FORMAT(referrals.referee_confirmed_hired_on, '%e %b, %Y') AS formatted_referee_confirmed_on, 
              DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on, 
              DATE_FORMAT(MAX(referral_rewards.paid_on), '%e %b, %Y') AS formatted_last_paid_on  
              FROM referrals 
              LEFT JOIN invoice_items ON invoice_items.item = referrals.id 
              LEFT JOIN invoices ON invoices.id = invoice_items.invoice 
              LEFT JOIN referral_rewards ON referral_rewards.referral = referrals.id 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN members ON members.email_addr = referrals.member 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN currencies ON currencies.country_code = employers.country 
              LEFT JOIN employees ON employers.registered_by = employees.id 
              WHERE invoices.type = 'R' AND 
              (invoices.paid_on IS NOT NULL AND invoices.paid_on <> '0000-00-00 00:00:00') AND 
              (referrals.employed_on IS NOT NULL AND referrals.employed_on <> '0000-00-00 00:00:00') AND 
              (referrals.employer_removed_on IS NULL OR referrals.employer_removed_on = '0000-00-00 00:00:00') AND 
              (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
              (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') AND 
              (referrals.guarantee_expire_on <= CURDATE() OR referrals.guarantee_expire_on IS NULL) AND 
              employees.branch = ". $_SESSION['yel']['employee']['branch']['id']. " 
              GROUP BY invoice 
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
        if ($paid[0]['amount'] > 0 && $paid[0]['amount'] < $row['total_reward']) {
            //$row['member'] = htmlspecialchars_decode($row['member']);
            $row['padded_invoice'] = pad($row['invoice'], 11, '0');
            $row['total_reward'] = number_format($row['total_reward'], 2, '.', ', ');
            $row['paid'] = number_format($paid[0]['amount'], 2, '.', ', ');
            
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
    
    $query = "SELECT invoices.id AS invoice, referrals.id AS referral, currencies.symbol AS currency, 
              referrals.total_reward, jobs.title, employers.name AS employer, 
              referrals.member AS member_id, referrals.employed_on, 
              MAX(referral_rewards.paid_on) AS fully_paid_on, 
              CONCAT(members.lastname, ', ', members.firstname) AS member, 
              DATE_FORMAT(referrals.employment_contract_received_on, '%e %b, %Y') AS formatted_contract_received_on, 
              DATE_FORMAT(referrals.referee_confirmed_hired_on, '%e %b, %Y') AS formatted_referee_confirmed_on, 
              DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on, 
              DATE_FORMAT(MAX(referral_rewards.paid_on), '%e %b, %Y') AS formatted_fully_paid_on  
              FROM referrals 
              LEFT JOIN invoice_items ON invoice_items.item = referrals.id 
              LEFT JOIN invoices ON invoices.id = invoice_items.invoice 
              LEFT JOIN referral_rewards ON referral_rewards.referral = referrals.id 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN members ON members.email_addr = referrals.member 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN currencies ON currencies.country_code = employers.country 
              LEFT JOIN employees ON employers.registered_by = employees.id 
              WHERE invoices.type = 'R' AND 
              (invoices.paid_on IS NOT NULL AND invoices.paid_on <> '0000-00-00 00:00:00') AND 
              (referrals.employed_on IS NOT NULL AND referrals.employed_on <> '0000-00-00 00:00:00') AND 
              (referrals.employer_removed_on IS NULL OR referrals.employer_removed_on = '0000-00-00 00:00:00') AND 
              (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
              (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') AND 
              (referrals.guarantee_expire_on <= CURDATE() OR referrals.guarantee_expire_on IS NULL) AND 
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
        $paid = ReferralReward::get_sum_paid_of_referral($row['referral']);
        if ($paid[0]['amount'] >= $row['total_reward']) {
            //$row['member'] = htmlspecialchars_decode($row['member']);
            $row['padded_invoice'] = pad($row['invoice'], 11, '0');
            $row['total_reward'] = number_format($row['total_reward'], 2, '.', ', ');
            $row['paid'] = number_format($paid[0]['amount'], 2, '.', ', ');
            
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
    $data['reward'] = $_POST['amount'];
    $data['paid_on'] = now();
    $data['paid_through'] = $_POST['payment_mode'];
    $data['bank'] = ($_POST['bank'] == '0' || empty($_POST['bank'])) ? 'NULL' : $_POST['bank'];
    $data['cheque'] = $_POST['cheque'];
    $data['receipt'] = $_POST['receipt'];
    
    if (!ReferralReward::create($data)) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_payment_history') {
    $order_by = 'referral_rewards.paid_on ASC';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT referral_rewards.id, referral_rewards.reward, referral_rewards.paid_through, 
              referral_rewards.cheque, referral_rewards.receipt, 
              DATE_FORMAT(referral_rewards.paid_on, '%e %b, %Y') AS formatted_paid_on,  
              CONCAT(member_banks.bank, ' (', member_banks.account, ')') AS bank 
              FROM referral_rewards 
              LEFT JOIN member_banks ON member_banks.id = referral_rewards.bank 
              WHERE referral_rewards.referral = ". $_POST['id']. " 
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
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('payments' => array('payment' => $result)));
    exit();
}

if ($_POST['action'] == 'get_payment_plan') {
    $months = 6;
    $total_reward = str_replace(array(',', ', '), '', $_POST['total_reward']);
    $monthly_amount = round(($total_reward / $months), 2);
    $new_total_reward = ($monthly_amount * $months);
    $remainder = ($total_reward - $new_total_reward);
    $final_monthly_amount = ($monthly_amount + $remainder);
    
    $employed_on = $_POST['employed_on'];
    $plans = array();
    $due_days = 30;
    for($i=0; $i < $months; $i++) {
        $plans[$i]['due_day'] = $due_days;
        $plans[$i]['due_on'] = sql_date_format(sql_date_add($employed_on, $due_days, 'day'));
        if ($i == ($months-1)) {
            $plans[$i]['amount'] = $final_monthly_amount;
        } else {
            $plans[$i]['amount'] = $monthly_amount;
        }
        
        $due_days += 30;
    }
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('plans' => array('plan' => $plans)));
    exit();
}
?>
