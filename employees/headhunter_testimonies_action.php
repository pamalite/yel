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
    $order_by = 'referrals.referred_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT referrals.id, referrals.member AS member_email_addr, 
              referrals.referee AS referee_email_addr, members.phone_num AS member_phone_num, 
              referees.phone_num AS referee_phone_num, jobs.id AS job_id, jobs.title AS job_title, 
              CONCAT(members.lastname, ', ', members.firstname) AS member, 
              CONCAT(referees.lastname, ', ', referees.firstname) AS referee, 
              DATE_FORMAT(referrals.referred_on, '%e %b, %Y') AS formatted_referred_on 
              FROM referrals 
              INNER JOIN jobs ON jobs.id = referrals.job
              INNER JOIN members ON members.email_addr = referrals.member 
              INNER JOIN members AS referees ON referees.email_addr = referrals.referee 
              INNER JOIN branches ON members.country = branches.country AND 
              branches.id = ". $_SESSION['yel']['employee']['branch']['id']. " 
              WHERE need_approval = 'Y' 
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
        $result[$i]['member'] = htmlspecialchars_decode($row['member']);
        $result[$i]['referee'] = htmlspecialchars_decode($row['referee']);
    }
    
    $response = array('testimonies' => array('testimony' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_testimony') {
    $mysqli = Database::connect();
    
    $query = "SELECT jobs.title AS job_title, 
              CONCAT(members.lastname, ', ', members.firstname) AS member, 
              CONCAT(referees.lastname, ', ', referees.firstname) AS referee, 
              referrals.testimony AS testimony_texts
              FROM referrals 
              INNER JOIN jobs ON jobs.id = referrals.job
              INNER JOIN members ON members.email_addr = referrals.member 
              INNER JOIN members AS referees ON referees.email_addr = referrals.referee
              WHERE referrals.id = ". $_POST['id']. " LIMIT 1";
    $result = $mysqli->query($query);
    $result[0]['member'] = htmlspecialchars_decode($result[0]['member']);
    $result[0]['referee'] = htmlspecialchars_decode($result[0]['referee']);
    $result[0]['testimony_texts'] = htmlspecialchars_decode(stripslashes($result[0]['testimony_texts']));
    
    $response = array('testimony' => array('details' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'approve_testimony') {
    $mysqli = Database::connect();
    
    $query = "UPDATE referrals SET
              need_approval = 'N', 
              testimony = '". htmlspecialchars(addslashes($_POST['testimony'])). "' 
              WHERE id = ". $_POST['id'];
    if ($mysqli->execute($query) === false) {
        echo 'ko';
    } else {
        echo 'ok';
    }
    
    exit();
}
?>
