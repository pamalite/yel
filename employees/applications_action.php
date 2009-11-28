<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

function replace_characters($_description) {
    // Strip newline characters.
    $_description = str_replace(chr(10), " ", $_description);
    $_description = str_replace(chr(13), " ", $_description);
    // Replace single quotes.
    $_description = str_replace(chr(145), chr(39), $_description);
    $_description = str_replace(chr(146), chr(39), $_description);
    // Return the result.
    return $_description;
}

function compare_total($a, $b) {
    if ($a == $b) {
        return 0;
    }
    
    return ($a < $b) ? -1 : 1;
}

session_start();

if (!isset($_POST['id'])) {
    echo "ko";
    exit();
    //redirect_to('login.php');
}

$xml_dom = new XMLDOM();

if (!isset($_POST['action'])) {
    $use_sort = false;
    $order_by = 'num_referred desc';
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT employers.id, employers.name, 
              COUNT(jobs.id) AS num_open, 
              (SELECT COUNT(referrals.id) 
              FROM referrals 
              LEFT JOIN jobs ON jobs.id = referrals.job
              WHERE (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND
              (referrals.employer_agreed_terms_on IS NULL OR referrals.employer_agreed_terms_on = '0000-00-00 00:00:00') AND
              (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND
              -- (referrals.member_read_resume_on IS NOT NULL AND referrals.member_read_resume_on <> '0000-00-00 00:00:00') AND  
              jobs.employer = employers.id
              ) AS num_referred, 
              (SELECT COUNT(referrals.id) 
              FROM referrals 
              LEFT JOIN jobs ON jobs.id = referrals.job
              WHERE (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND
              (referrals.employer_agreed_terms_on IS NOT NULL AND referrals.employer_agreed_terms_on <> '0000-00-00 00:00:00') AND  
              jobs.employer = employers.id
              ) AS num_kiv  
              FROM employers 
              LEFT JOIN employees ON employees.id = employers.registered_by 
              LEFT JOIN jobs ON jobs.employer = employers.id AND 
              (jobs.expire_on >= CURDATE() AND jobs.closed = 'N') 
              WHERE employees.branch = ". $_SESSION['yel']['employee']['branch']['id']. " 
              GROUP BY employers.id 
              ORDER BY ". $order_by;
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }
    
    foreach ($result as $i=>$row) {
        $result[$i]['name'] = htmlspecialchars_decode(desanitize($row['name']));
    }
    
    $xml_dom = new XMLDOM();
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('employers' => array('employer' => $result)));
    exit();
}

if ($_POST['action'] == 'get_jobs') {
    $order_by = 'num_referred desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT jobs.id, industries.industry AS industry, jobs.title, 
              DATE_FORMAT(jobs.created_on, '%e %b, %Y') AS created_on, 
              DATE_FORMAT(jobs.expire_on, '%e %b, %Y') AS expire_on, 
              COUNT(referrals.id) AS num_referred, 
              COUNT(kivs.id) AS num_kiv 
              FROM jobs 
              LEFT JOIN referrals ON referrals.job = jobs.id AND 
              (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND
              (referrals.employer_agreed_terms_on IS NULL OR referrals.employer_agreed_terms_on = '0000-00-00 00:00:00') AND
              (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') 
              -- AND (referrals.member_read_resume_on IS NOT NULL AND referrals.member_read_resume_on <> '0000-00-00 00:00:00')
              LEFT JOIN referrals AS kivs ON kivs.job = jobs.id AND 
              (kivs.employed_on IS NULL OR kivs.employed_on = '0000-00-00 00:00:00') AND
              (kivs.employer_agreed_terms_on IS NOT NULL AND kivs.employer_agreed_terms_on <> '0000-00-00 00:00:00')
              LEFT JOIN industries ON industries.id = jobs.industry 
              WHERE jobs.employer = '". $_POST['id']. "' AND jobs.closed = 'N' 
              GROUP BY jobs.id 
              ORDER BY ". $order_by;
    $mysqli = Database::connect();
    $jobs = $mysqli->query($query);
    
    $response = array(
        'jobs' => array('job' => $jobs)
    );
    
    $xml_dom = new XMLDOM();
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_referrals') {
    $order_by = 'referred_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT referrals.id, resumes.file_hash, resumes.name AS resume_name, referrals.resume AS resume_id, 
              members.email_addr AS candidate_email_addr, 
              members.phone_num AS candidate_phone_num, 
              CONCAT(members.lastname, ', ', members.firstname) AS candidate_name, 
              referrers.email_addr AS referrer_email_addr, 
              referrers.phone_num AS referrer_phone_num, 
              CONCAT(referrers.lastname, ', ', referrers.firstname) AS referrer_name, 
              DATE_FORMAT(referrals.referred_on, '%e %b, %Y') AS formatted_referred_on, 
              DATE_FORMAT(referrals.employer_agreed_terms_on, '%e %b, %Y') AS formatted_employer_viewed_on 
              FROM referrals 
              LEFT JOIN resumes ON resumes.id = referrals.resume 
              LEFT JOIN members ON members.email_addr = referrals.referee 
              LEFT JOIN members AS referrers ON referrers.email_addr = referrals.member 
              WHERE referrals.job = ". $_POST['id']. " AND 
              (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
              (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') 
              -- AND (referrals.member_read_resume_on IS NOT NULL AND referrals.member_read_resume_on <> '0000-00-00 00:00:00') 
              ORDER BY ". $order_by;
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    
    $response = array(
        'referrals' => array('referral' => $result)
    );
    
    $xml_dom = new XMLDOM();
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_employer_name') {
    $query = "SELECT name FROM employers WHERE id = '". $_POST['id']. "' LIMIT 1";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    
    $xml_dom = new XMLDOM();
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('employer' => array('name' => $result[0]['name'])));
    exit();
}

if ($_POST['action'] == 'get_job_title') {
    $query = "SELECT title FROM jobs WHERE id = ". $_POST['id']. " LIMIT 1";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    
    $xml_dom = new XMLDOM();
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('job' => array('title' => $result[0]['title'])));
    exit();
}
?>
