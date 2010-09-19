<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

session_start();

if (!isset($_POST['id'])) {
    redirect_to('rewards.php');
}

if (!isset($_POST['action'])) {
    redirect_to('rewards.php');
}


$xml_dom = new XMLDOM();

function get_rewards($_is_paid = false, $_order_by) {
    $criteria = array(
        'columns' => "invoices.id AS invoice, referrals.id AS referral, referrals.total_reward,
                      referrals.job AS job_id, currencies.symbol AS currency, jobs.title, 
                      referrals.member AS member_id, referrals.employed_on, 
                      employers.name AS employer, members.phone_num, 
                      CONCAT(members.lastname, ', ', members.firstname) AS member, 
                      DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on, 
                      (SUM(referral_rewards.reward) / 3) AS paid_reward", 
        'joins' => "invoice_items ON invoice_items.item = referrals.id, 
                    invoices ON invoices.id = invoice_items.invoice, 
                    referral_rewards ON referral_rewards.referral = referrals.id, 
                    jobs ON jobs.id = referrals.job, 
                    members ON members.email_addr = referrals.member, 
                    employers ON employers.id = jobs.employer, 
                    currencies ON currencies.country_code = employers.country",
        'match' => "invoices.type = 'R' AND 
                    (invoices.paid_on IS NOT NULL AND invoices.paid_on <> '0000-00-00 00:00:00') AND 
                    (referrals.employed_on IS NOT NULL AND referrals.employed_on <> '0000-00-00 00:00:00') AND 
                    (referrals.employer_removed_on IS NULL OR referrals.employer_removed_on = '0000-00-00 00:00:00') AND 
                    (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
                    (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') AND 
                    (referrals.guarantee_expire_on <= CURDATE() OR referrals.guarantee_expire_on IS NULL)                        ", 
        'group' => "referrals.id", 
        'order' => $_order_by,
        'having' => "(paid_reward < referrals.total_reward OR paid_reward IS NULL)"
    );
    
    if ($_is_paid) {
        $criteria['columns'] .= ", referral_rewards.gift, DATE_FORMAT(MAX(referral_rewards.paid_on), '%e %b, %Y') AS formatted_paid_on";
        $criteria['having'] = "(paid_reward >= referrals.total_reward OR referral_rewards.gift IS NOT NULL)";
    } else {
        $criteria['match'] .= "AND (referral_rewards.gift IS NULL OR referral_rewards.gift = '')";
    }
    
    $referral = new Referral();
    return $referral->find($criteria);
}

if ($_POST['action'] == 'get_new_rewards') {
    $order_by = 'referrals.employed_on ASC';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $rewards = get_rewards(false, $order_by);
    
    if (count($rewards) <= 0 || is_null($rewards)) {
        echo '0';
        exit();
    }
    
    if (!$rewards) {
        echo 'ko';
        exit();
    }
    
    foreach ($rewards as $i=>$row) {
        $rewards[$i]['member'] = htmlspecialchars_decode(stripslashes($row['member']));
        $rewards[$i]['employer'] = htmlspecialchars_decode(stripslashes($row['employer']));
        $rewards[$i]['title'] = htmlspecialchars_decode(stripslashes($row['title']));
        $rewards[$i]['padded_invoice'] = pad($row['invoice'], 11, '0');
        $rewards[$i]['total_reward'] = number_format($row['total_reward'], 2, '.', ', ');
        $rewards[$i]['paid_reward'] = number_format($row['paid_reward'], 2, '.', ', ');
    }
    
    $response = array('rewards' => array('reward' => $rewards));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_paid_rewards') {
    $order_by = 'invoices.paid_on DESC';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $rewards = get_rewards(true, $order_by);
    
    if (count($rewards) <= 0 || is_null($rewards)) {
        echo '0';
        exit();
    }
    
    if (!$rewards) {
        echo 'ko';
        exit();
    }
    
    foreach ($rewards as $i=>$row) {
        $rewards[$i]['member'] = htmlspecialchars_decode(stripslashes($row['member']));
        $rewards[$i]['employer'] = htmlspecialchars_decode(stripslashes($row['employer']));
        $rewards[$i]['title'] = htmlspecialchars_decode(stripslashes($row['title']));
        $rewards[$i]['padded_invoice'] = pad($row['invoice'], 11, '0');
        $rewards[$i]['total_reward'] = number_format($row['total_reward'], 2, '.', ', ');
        $rewards[$i]['paid_reward'] = number_format($row['paid_reward'], 2, '.', ', ');
        $rewards[$i]['gift'] = htmlspecialchars_decode(stripslashes($row['gift']));
    }
    
    $response = array('rewards' => array('reward' => $rewards));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}


if ($_POST['action'] == 'get_reward_details') {
    $criteria = array(
        'columns' => "currencies.symbol AS currency, 
                      referrals.total_reward, referrals.member AS member_id, 
                      CONCAT(members.lastname, ', ', members.firstname) AS member", 
        'joins' => "jobs ON jobs.id = referrals.job, 
                    members ON members.email_addr = referrals.member, 
                    employers ON employers.id = jobs.employer, 
                    currencies ON currencies.country_code = employers.country", 
        'match' => "referrals.id = ". $_POST['id'], 
        'limit' => "1"
        
    );
    
    $referral = new Referral();
    $result = $referral->find($criteria);
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('reward' => $result));
    exit();
}

if ($_POST['action'] == 'get_banks') {
    $member = new Member($_POST['id']);
    $result = $member->getBankAccount();
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }
    
    if (!$result) {
        echo '0';
        exit();
    }
    
    $response = array('bank_accounts' => array('bank_account' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'award') {
    $data = array();
    $data['referral'] = $_POST['id'];
    $data['paid_on'] = now();
    
    if ($_POST['award_mode'] == 'gift') {
        $data['reward'] = '0.00';
        $data['bank'] = 'NULL';
        $data['paid_through'] = 'NULL';
        $data['gift'] = sanitize($_POST['gift']);
    } else {
        $data['reward'] = $_POST['amount'];
        $data['paid_through'] = $_POST['payment_mode'];
        $data['bank'] = ($_POST['bank'] == '0' || empty($_POST['bank'])) ? 'NULL' : $_POST['bank'];
        $data['receipt'] = $_POST['receipt'];
        $data['gift'] = 'NULL';
    }
    
    if (!ReferralReward::create($data)) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

?>