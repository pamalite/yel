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
    $member = new Member($_POST['referee']);
    $response =  array('resume' => $member->get());

    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'delete') {
    if (!isset($_POST['payload'])) {
        echo "ko";
        exit();
    }
    $member = new Member($_POST['member'], $_SESSION['yel']['member']['sid']);
    $xml_dom->load_from_xml($_POST['payload']);
    $referees = $xml_dom->get('id');
    foreach ($referees as $id) {
        if (!$member->delete_referee($id->nodeValue)) {
            echo "ko";
            exit();
        }
    }
    
    echo "ok";
    exit();
}

if ($_POST['action'] == 'add_network') {
    $member = new Member($_POST['member'], $_SESSION['yel']['member']['sid']);
    $network_id = 0;
    if (!($network_id = $member->create_network($_POST['industry']))) {
        echo "ko";
        exit();
    }
    
    echo $network_id;
    exit();
}

if ($_POST['action'] == 'add_referees_into_network') {
    if (!isset($_POST['payload'])) {
        echo "ko";
        exit();
    }
    $member = new Member($_POST['member'], $_SESSION['yel']['member']['sid']);
    $xml_dom->load_from_xml($_POST['payload']);
    $referees = $xml_dom->get('id');
    foreach ($referees as $id) {
        if (!$member->add_referee_into_network($id->nodeValue, $_POST['network'])) {
            echo "ko";
            exit();
        }
    }
    
    echo "ok";
    exit();
}

if ($_POST['action'] == 'delete_referee_from_network') {
    $member = new Member($_POST['member'], $_SESSION['yel']['member']['sid']);
    if (!$member->delete_referee_from_network($_POST['id'], $_POST['network'])) {
        echo "ko";
        exit();
    }
    
    echo "ok";
    exit();
}


if ($_POST['action'] == 'delete_networks') {
    if (!isset($_POST['payload'])) {
        echo "ko";
        exit();
    }
    $member = new Member($_POST['member'], $_SESSION['yel']['member']['sid']);
    $xml_dom->load_from_xml($_POST['payload']);
    $networks = $xml_dom->get('id');
    foreach ($networks as $id) {
        if (!$member->delete_network($id->nodeValue)) {
            echo "ko";
            exit();
        }
    }
    
    echo "ok";
    exit();
}

if ($_POST['action'] == 'get_networks') {
    $member = new Member($_POST['member'], $_SESSION['yel']['member']['sid']);
    $networks = $member->get_networks();
    if (count($networks) <= 0 || is_null($networks)) {
        echo '0';
        exit();
    } 
    
    if (!$networks) {
        echo 'ko';
        exit();
    }
    
    $response = array('networks' => array('network' => $networks));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'create_referee') {
    $member = new Member($_POST['member'], $_SESSION['yel']['member']['sid']);
    if (!$member->create_referee($_POST['referee'])) {
        echo "ko";
        exit();
    }
    
    $referee = new Member($_POST['referee']);
    $mail_lines = file('../private/mail/member_approval.txt');
    $message = '';
    foreach ($mail_lines as $line) {
        $message .= $line;
    }

    $message = str_replace('%member_name%', $member->get_name(), $message);
    $message = str_replace('%referee_name%', $referee->get_name(), $message);
    $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
    $message = str_replace('%root%', $GLOBALS['root'], $message);
    $subject = desanitize($member->get_name()). " added you as a contact. Your approval is required.";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($referee->id(), $subject, $message, $headers);

    echo "ok";
    exit();
}

if ($_POST['action'] == 'get_referee_networks') {
    $query = "SELECT member_networks.id AS network_id, industries.industry FROM industries 
              LEFT JOIN member_networks ON industries.id = member_networks.industry 
              LEFT JOIN member_networks_referees ON member_networks.id = member_networks_referees.network 
              WHERE member_networks_referees.referee = ". $_POST['id'];
    
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if (count($result) >= 0) {
        $response = array('networks' => array('network' => $result));
        header('Content-type: text/xml');
        echo $xml_dom->get_xml_from_array($response);
        exit();
    }
    
    echo "ko";
    exit();
}

if ($_POST['action'] == 'get_referee_contacts') {
    $query = "SELECT members.email_addr, members.phone_num FROM members 
              LEFT JOIN member_referees ON members.email_addr = member_referees.referee 
              WHERE member_referees.id = ". $_POST['id']. " LIMIT 1";
    
    $mysqli = Database::connect();
    if ($result = $mysqli->query($query)) {
        $response = array('referee' => $result);
        header('Content-type: text/xml');
        echo $xml_dom->get_xml_from_array($response);
        exit();
    }
    
    echo "ko";
    exit();
}

if ($_POST['action'] == 'get_reward_earned') {
    $query = "SELECT referrals.total_reward, currencies.symbol AS currency 
              FROM referrals 
              LEFT JOIN member_referees ON referrals.referee = member_referees.referee 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN currencies ON currencies.country_code = employers.country 
              AND referrals.member = member_referees.member 
              WHERE member_referees.id = ". $_POST['id']. " AND 
              (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00')";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if (count($result) <= 0 || is_null($result)) {
        $response = array('reward_earned' => '0.00');
        header('Content-type: text/xml');
        echo $xml_dom->get_xml_from_array($response);
        exit();
    }
    
    if (!$result) {
        echo "ko";
        exit();
    }
    
    $member = new Member($_POST['member']);
    $to_currency = Currency::symbol_from_country_code($member->get_country_code());
    $total = 0.00;
    foreach ($result as $row) {
        $from_currency = $row['currency'];
        $amount = $row['total_reward'];
        $total += Currency::convert_amount_from_to($from_currency, $to_currency, $amount);
    }
    $response = array('reward_earned' => number_format($total, 2, '.', ', '));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_currency_symbol') {
    $member = new Member($_POST['member']);
    if ($symbol = Currency::symbol_from_country_code($member->get_country_code())) {
        header('Content-type: text/xml');
        echo $xml_dom->get_xml_from_array(array('symbol' => $symbol));
        exit();
    }
    
    echo "ko";
    exit();
    
}

if ($_POST['action'] == 'get_candidate_histories') {
    $order_by = 'referred_on desc';

    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT referrals.id, jobs.title, employers.name, currencies.symbol AS currency, 
              DATE_FORMAT(referrals.referred_on, '%e %b, %Y') AS formatted_referred_on, 
              DATE_FORMAT(referrals.referee_acknowledged_on, '%e %b, %Y') AS formatted_referee_acknowledged_on, 
              DATE_FORMAT(referrals.referee_acknowledged_others_on, '%e %b, %Y') AS formatted_referee_acknowledged_others_on, 
              DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on, 
              DATE_FORMAT(referrals.work_commence_on, '%e %b, %Y') AS formatted_work_commence_on, 
              referrals.total_reward, SUM(referral_rewards.reward) AS paid_reward 
              FROM referrals 
              LEFT JOIN referral_rewards ON referral_rewards.referral = referrals.id 
              LEFT JOIN members ON members.email_addr = referrals.referee 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN currencies ON currencies.country_code = employers.country 
              LEFT JOIN member_referees ON referrals.member = member_referees.member AND 
              referrals.referee = member_referees.referee
              WHERE member_referees.id = ". $_POST['id']. " AND 
              (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') 
              GROUP BY referrals.id 
              ORDER BY ". $order_by;

    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if ($result) {
        foreach ($result as $key=>$row) {
            if (!empty($result[$key]['total_reward']) && !is_null($result[$key]['total_reward'])) {
                $result[$key]['total_reward'] = number_format($result[$key]['total_reward'], 2, '.', ', ');
            } else {
                $result[$key]['total_reward'] = '0.00';
            }
            
            if (!empty($result[$key]['[paid_reward']) && !is_null($result[$key]['paid_reward'])) {
                $result[$key]['paid_reward'] = number_format($result[$key]['paid_reward'], 2, '.', ', ');
            } else {
                $result[$key]['paid_reward'] = '0.00';
            }
        }
        
        header('Content-type: text/xml');
        echo $xml_dom->get_xml_from_array(array('histories' => array('history' => $result)));
        exit();
    }
    
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }
    
    echo 'ko';
    exit();
}

if ($_POST['action'] == 'get_testimony') {
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

if ($_POST['action'] == 'get_total_rewards_earned') {
    $query = "SELECT IFNULL(SUM(referrals.total_reward), 0) AS reward_earned, 
              currencies.symbol AS currency 
              FROM referrals 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN currencies ON currencies.country_code = employers.country
              WHERE referrals.member = '". $_POST['id']. "'
              GROUP BY currencies.symbol 
              ORDER BY reward_earned";
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
    
    $rewards = array();
    foreach ($result as $row) {
        if ($row['reward_earned'] > 0) {
            $rewards[]['reward_earned'] = number_format($row['reward_earned'], 2, '.', ', ');
            $rewards[]['currency'] = $row['currency'];
        }
    }
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('rewards' => array('reward' => $rewards)));
    exit();
}
?>
