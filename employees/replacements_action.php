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
    
    $now = now();
    $query = "SELECT invoices.id AS invoice, referrals.id AS referral, 
              jobs.title, employers.name AS employer, employers.contact_person, 
              employers.email_addr AS employer_email, employers.phone_num, 
              CONCAT(members.lastname, ', ', members.firstname) AS member, 
              CONCAT(referees.lastname, ', ', referees.firstname) AS referee, 
              DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on 
              FROM referrals 
              LEFT JOIN invoice_items ON invoice_items.item = referrals.id 
              LEFT JOIN invoices ON invoices.id = invoice_items.invoice 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN members ON members.email_addr = referrals.member 
              LEFT JOIN members AS referees ON referees.email_addr = referrals.referee 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN employees ON employers.registered_by = employees.id 
              WHERE invoices.type = 'R' AND 
              (invoices.paid_on IS NOT NULL AND invoices.paid_on <> '0000-00-00 00:00:00') AND 
              (referrals.employed_on IS NOT NULL AND referrals.employed_on <> '0000-00-00 00:00:00') AND 
              (referrals.employer_removed_on IS NULL OR referrals.employer_removed_on = '0000-00-00 00:00:00') AND 
              (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
              (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') AND 
              (referrals.replaced_on IS NULL OR referrals.replaced_on = '0000-00-00 00:00:00') AND 
              referrals.replaced_referral IS NULL AND 
              referrals.work_commence_on <= '". $now. "' AND 
              (referrals.guarantee_expire_on > '". $now. "' OR referrals.guarantee_expire_on IS NULL) AND 
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
    
    foreach($result as $i=>$row) {
        $result[$i]['padded_invoice'] = pad($row['invoice'], 11, '0');
    }
    
    $response = array('replacements' => array('replacement' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'authorize_replacement') {
    $today = now();
    $data = array();
    $data['id'] = $_POST['id'];
    $data['replacement_authorized_on'] = $today;
    
    if (!Referral::update($data)) {
        echo 'ko';
        exit();
    }
    
    $query = "SELECT job FROM referrals WHERE id = ". $_POST['id']. " LIMIT 1";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    
    $data = array();
    $data['expire_on'] = sql_date_add($today, 30, 'day');
    $data['closed'] = 'N';
    $data['for_replacement'] = 'Y';
    $job = new Job($result[0]['job']);
    if (!$job->update($data)) {
        echo 'ko';
        exit();
    }    
    
    $query = "SELECT employers.name AS employer, industries.industry, jobs.title, 
              referrals.member AS member_email_addr, referrals.referee AS referee_email_addr, 
              CONCAT(members.lastname, ', ', members.firstname) AS member, 
              CONCAT(referees.lastname, ', ', referees.firstname) AS referee, 
              DATE_FORMAT(referrals.referred_on, '%e %b, %Y') AS formatted_referred_on, 
              DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on, 
              DATE_FORMAT(referrals.replacement_authorized_on, '%e %b, %Y') AS formatted_replacement_authorized_on 
              FROM referrals 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN industries ON industries.id = jobs.industry 
              LEFT JOIN members ON members.email_addr = referrals.member 
              LEFT JOIN members AS referees ON referees.email_addr = referrals.referee 
              WHERE referrals.id = ". $_POST['id']. " LIMIT 1";
    $result = $mysqli->query($query);
    
    $lines = file(dirname(__FILE__). '/../private/mail/member_replacement.txt');
    $message = '';
    foreach($lines as $line) {
        $message .= $line;
    }

    $message = str_replace('%member%', htmlspecialchars_decode($result[0]['member']), $message);
    $message = str_replace('%job_title%', htmlspecialchars_decode($result[0]['title']), $message);
    $message = str_replace('%employer%', htmlspecialchars_decode($result[0]['employer']), $message);
    $message = str_replace('%industry%', $result[0]['industry'], $message);
    $message = str_replace('%candidate%', htmlspecialchars_decode($result[0]['referee']), $message);
    $message = str_replace('%referee_email_addr%', $result[0]['referee_email_addr'], $message);
    $message = str_replace('%referred_on%', $result[0]['formatted_referred_on'], $message);
    $message = str_replace('%employed_on%', $result[0]['formatted_employed_on'], $message);
    $message = str_replace('%authorized_on%', $result[0]['formatted_replacement_authorized_on'], $message);
    $subject = "Referred Contact Removed From Employment";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($result[0]['member_email_addr'], $subject, $message, $headers);
    
    echo 'ok';
    exit();
}
?>
