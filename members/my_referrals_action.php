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
    
    $query = "SELECT referrals.id, employers.name AS employer, jobs.id AS job_id, jobs.title, 
              jobs.potential_reward, branches.currency, member_referees.id AS referee_id, 
              CONCAT(members.lastname, ', ', members.firstname) AS candidate, jobs.description, 
              DATE_FORMAT(referrals.referred_on, '%e %b, %Y') AS formatted_referred_on, 
              DATE_FORMAT(referrals.referee_acknowledged_on, '%e %b, %Y') AS formatted_acknowledged_on, 
              DATE_FORMAT(referrals.employer_agreed_terms_on, '%e %b, %Y') AS formatted_employer_agreed_terms_on, 
              referrals.referred_on, referrals.referee_acknowledged_on, referrals.employer_agreed_terms_on 
              FROM referrals 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN branches ON branches.id = employers.branch 
              LEFT JOIN members ON members.email_addr = referrals.referee 
              LEFT JOIN member_referees ON member_referees.member = referrals.member AND 
              member_referees.referee = referrals.referee 
              WHERE referrals.member = '". $_POST['id']. "' AND 
              member_referees.member = '". $_POST['id']. "' AND 
              (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
              (referrals.work_commence_on IS NULL OR referrals.work_commence_on = '0000-00-00 00:00:00') AND 
              (referrals.referee_acknowledged_others_on IS NULL OR referrals.referee_acknowledged_others_on = '0000-00-00 00:00:00') AND
              (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
              (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') 
              UNION 
              SELECT '0', employers.name, jobs.id, jobs.title, 
              jobs.potential_reward, branches.currency, '0', 
              member_invites.referee_email, jobs.description, 
              DATE_FORMAT(member_invites.invited_on, '%e %b, %Y'), 
              NULL, 
              NULL, 
              member_invites.invited_on, NULL, NULL 
              FROM member_invites 
              LEFT JOIN jobs ON jobs.id = member_invites.referred_job 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN branches ON branches.id = employers.branch 
              LEFT JOIN members ON members.email_addr = member_invites.referee_email 
              WHERE member_invites.member = '". $_POST['id']. "' AND 
              (member_invites.signed_up_on IS NULL OR member_invites.signed_up_on = '0000-00-00 00:00:00') 
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
        $result[$i]['candidate'] = htmlspecialchars_decode($row['candidate']);
        $result[$i]['potential_reward'] = number_format($row['potential_reward'], 2, '.', ', ');
    }
    
    $response = array('referrals' => array('referral' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_rewards') {
    $order_by = 'employed_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT referrals.id, employers.name AS employer, jobs.id AS job_id, jobs.title, jobs.description, 
              referrals.total_reward, branches.currency, member_referees.id AS referee_id, 
              CONCAT(members.lastname, ', ', members.firstname) AS candidate, 
              DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on, 
              DATE_FORMAT(referrals.work_commence_on, '%e %b, %Y') AS formatted_work_commence_on, 
              SUM(referral_rewards.reward) AS paid_reward 
              FROM referrals 
              LEFT JOIN referral_rewards ON referral_rewards.referral = referrals.id 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN branches ON branches.id = employers.branch 
              LEFT JOIN members ON members.email_addr = referrals.referee 
              LEFT JOIN member_referees ON member_referees.member = referrals.member AND 
              member_referees.referee = referrals.referee 
              WHERE referrals.member = '". $_POST['id']. "' AND 
              (referrals.employed_on IS NOT NULL AND referrals.employed_on <> '0000-00-00 00:00:00') AND 
              (referrals.work_commence_on IS NOT NULL AND referrals.work_commence_on <> '0000-00-00 00:00:00') AND 
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
        echo "ko";
        exit();
    }
    
    foreach ($result as $key=>$row) {
        $result[$key]['description'] = htmlspecialchars_decode($row['description']);
        $result[$key]['title'] = htmlspecialchars_decode($row['title']);
        $result[$key]['candidate'] = htmlspecialchars_decode($row['candidate']);
        $result[$key]['total_reward'] = number_format($row['total_reward'], 2, '.', ', ');
        $result[$key]['paid_reward'] = number_format($row['paid_reward'], 2, '.', ', ');
    }
    
    $response = array('rewards' => array('reward' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_total_rewards_earned') {
    $query = "SELECT IFNULL(SUM(referrals.total_reward), 0) AS reward_earned, 
              branches.currency 
              FROM referrals 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN branches ON branches.id = employers.branch 
              WHERE referrals.member = '". $_POST['id']. "'
              GROUP BY branches.currency 
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
    $i = 0;
    foreach ($result as $row) {
        if ($row['reward_earned'] > 0) {
            $rewards[$i]['reward_earned'] = number_format($row['reward_earned'], 2, '.', ', ');
            $rewards[$i]['currency'] = $row['currency'];
            $i++;
        }
    }
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('rewards' => array('reward' => $rewards)));
    exit();
}

if ($_POST['action'] == 'mark_all_rewards_viewed') {
    $query = "UPDATE referrals SET 
              reward_counted = TRUE
              WHERE reward_counted = FALSE AND 
              member = '". $_POST['id']. "'";
    $mysql = Database::connect();
    $mysql->execute($query);
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

if ($_POST['action'] == 'get_hide_banner') {
    $query = "SELECT pref_value FROM member_banners 
              WHERE member = '". $_POST['id']. "' AND pref_key = 'hide_my_referrals_banner' LIMIT 1";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if (is_null($result)) {
        echo '0';
    } else {
        echo $result[0]['pref_value']; 
    }
    
    exit();
}

if ($_POST['action'] == 'set_hide_banner') {
    $query = "SELECT id FROM member_banners 
              WHERE member = '". $_POST['id']. "' AND pref_key = 'hide_my_referrals_banner' LIMIT 1";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if ($result[0]['id'] > 0) {
        $query = "UPDATE member_banners SET pref_value = '". $_POST['hide']. "' WHERE id = ". $result[0]['id'];
    } else {
        $query = "INSERT INTO member_banners SET 
                  id = 0,
                  pref_key = 'hide_my_referrals_banner', 
                  pref_value = '". $_POST['hide']. "',
                  member = '". $_POST['id']. "'";
    }
    
    $mysqli->execute($query);
    
    exit();
}
?>
