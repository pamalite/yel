<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id'])) {
    echo "ko";
    exit();
    //redirect_to('login.php');
}

$xml_dom = new XMLDOM();

if ($_POST['action'] == 'get_recent_referred_jobs') {
    $order_by = 'referred_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT referrals.id, industries.industry, jobs.id AS job_id, jobs.title, 
              resumes.id AS resume_id, resumes.name AS resume_label, resumes.file_hash, 
              CONCAT(members.lastname, ', ', members.firstname) AS referrer, 
              DATE_FORMAT(referrals.referred_on, '%e %b, %Y') AS formatted_referred_on, 
              employers.name AS employer 
              FROM referrals 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN industries ON industries.id = jobs.industry 
              LEFT JOIN members ON members.email_addr = referrals.member 
              LEFT JOIN member_referees ON member_referees.member = referrals.member AND 
              member_referees.referee = referrals.referee 
              LEFT JOIN resumes ON resumes.id = referrals.resume 
              WHERE referrals.referee = '". $_POST['id']. "' AND 
              member_referees.approved = 'Y' AND 
              (referrals.referee_acknowledged_on IS NULL OR referrals.referee_acknowledged_on = '0000-00-00 00:00:00') AND 
              (referrals.referee_acknowledged_others_on IS NULL OR referrals.referee_acknowledged_others_on = '0000-00-00 00:00:00') AND
              (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
              (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
              (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') 
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
    
    $response = array('referrals' => array('referral' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_job_info') {
    $query = "SELECT jobs.*, industries.industry, employers.name AS employer, countries.country AS country_name
              FROM jobs 
              LEFT JOIN industries ON industries.id = jobs.industry 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN countries ON countries.country_code = jobs.country 
              WHERE jobs.id = ". $_POST['id']. " LIMIT 1";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if (!$result) {
        echo "ko";
        exit();
    }
    
    $result[0]['description'] = htmlspecialchars_decode($result[0]['description']);
    $result[0]['salary'] = number_format($result[0]['salary'], 2, '.', ', ');
    $result[0]['salary_end'] = number_format($result[0]['salary_end'], 2, '.', ', ');
    $result[0]['potential_reward'] = number_format($result[0]['potential_reward'], 2, '.', ', ');
    $response = array('job' => $result[0]);
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'has_resumes') {
    $query = "SELECT COUNT(*) AS has_resumes FROM resumes 
              WHERE member = '". $_POST['id']. "' AND 
              private = 'N' AND 
              deleted = 'N' ";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if ($result[0]['has_resumes'] != '0') {
        echo '1';
        exit();
    }
    
    echo '0';
    exit();
}

if ($_POST['action'] == 'acknowledge_job') {
    $mysqli = Database::connect();
    
    if (!isset($_POST['resume_attached'])) {
        $query = "SELECT jobs.acceptable_resume_type 
                  FROM referrals 
                  LEFT JOIN jobs ON jobs.id = referrals.job 
                  WHERE referrals.id = ". $_POST['id']. " LIMIT 1";
        $result = $mysqli->query($query);
        $acceptable_resume_type = $result[0]['acceptable_resume_type'];

        $query = "SELECT file_hash FROM resumes WHERE id = ". $_POST['resume']. " LIMIT 1";
        $result = $mysqli->query($query);
        $file_hash = $result[0]['file_hash'];

        if ($acceptable_resume_type != 'A') {
            if ($acceptable_resume_type == 'O' && !is_null($file_hash)) {
                // online resume only
                echo '-1';
                exit();
            } else if ($acceptable_resume_type == 'F' && is_null($file_hash)) {
                // online resume only
                echo '-2';
                exit();
            }
        }
    }
    
    $timestamp = now();
    $data = array();
    $data['id'] = $_POST['id'];
    $data['resume'] = $_POST['resume'];
    $data['referee_acknowledged_on'] = $timestamp;
        
    if (!Referral::update($data)) {
        echo 'ko';
        exit();
    }
    
    $query = "SELECT member_confirmed_on FROM referrals WHERE id = ". $data['id']. " LIMIT 1";
    $result = $mysqli->query($query);
    if (!is_null($result[0]['member_confirmed_on']) && !empty($result[0]['member_confirmed_on'])) {
        $query = "SELECT employers.like_instant_notification, employers.email_addr, 
                  employers.name, jobs.title, jobs.contact_carbon_copy 
                  FROM referrals 
                  LEFT JOIN jobs ON jobs.id = referrals.job 
                  LEFT JOIN employers ON employers.id = jobs.employer 
                  WHERE referrals.id = ". $data['id']. " LIMIT 1";
        $result = $mysqli->query($query);
        if ($result[0]['like_instant_notification'] == '1') {
            $employer = $result[0]['name'];
            $job = $result[0]['title'];

            $lines = file(dirname(__FILE__). '/../private/mail/employer_new_referral.txt');
            $message = '';
            foreach($lines as $line) {
                $message .= $line;
            }

            $message = str_replace('%company%', desanitize($employer), $message);
            $message = str_replace('%job%', desanitize($job), $message);
            $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
            $message = str_replace('%root%', $GLOBALS['root'], $message);
            $subject = "New application for ". desanitize($job). " position";
            $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
            if (!empty($result[0]['contact_carbon_copy']) && !is_null($result[0]['contact_carbon_copy'])) {
                $headers .= 'Cc: '. $result[0]['contact_carbon_copy']. "\n";
            }
            mail($result[0]['email_addr'], $subject, $message, $headers);

            // $handle = fopen('/tmp/email_to_'. $result[0]['email_addr']. '.txt', 'w');
            // fwrite($handle, 'Subject: '. $subject. "\n\n");
            // fwrite($handle, $message);
            // fclose($handle);
        }
    }
    
    if (!Referral::close_similar_referrals_with_id($_POST['id'])) {
        echo 'ko';
        exit();
    }
    
    $query = "SELECT CONCAT(members.firstname, ', ', members.lastname) AS member, 
              CONCAT(referees.firstname, ', ', referees.lastname) AS candidate, 
              referrals.member AS member_email, referrals.referee AS candidate_email,
              employers.name AS employer, jobs.title AS job 
              FROM referrals 
              LEFT JOIN members ON members.email_addr = referrals.member 
              LEFT JOIN members AS referees ON referees.email_addr = referrals.referee 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN employers ON employers.id = jobs.employer 
              WHERE referrals.id = ". $_POST['id']. " LIMIT 1";
    $result = $mysqli->query($query);
    $member = $result[0]['member'];
    $candidate = $result[0]['candidate'];
    $member_email = $result[0]['member_email'];
    $candidate_email = $result[0]['candidate_email'];
    $employer = $result[0]['employer'];
    $job = $result[0]['job'];
    
    $lines = file(dirname(__FILE__). '/../private/mail/candidate_acknowledge_job.txt');
    $message = '';
    foreach($lines as $line) {
        $message .= $line;
    }
    
    $message = str_replace('%referrer_name%', htmlspecialchars_decode($member), $message);
    $message = str_replace('%candidate_name%', htmlspecialchars_decode($candidate), $message);
    $message = str_replace('%candidate_email_addr%', $candidate_email, $message);
    $message = str_replace('%employer%', htmlspecialchars_decode($employer), $message);
    $message = str_replace('%job%', htmlspecialchars_decode($job), $message);
    $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
    $message = str_replace('%root%', $GLOBALS['root'], $message);
    $subject = htmlspecialchars_decode($candidate). ' accepted the '. htmlspecialchars_decode($job). ' position';
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($member_email, $subject, $message, $headers);

    // $handle = fopen('/tmp/email_to_'. $member_email. '.txt', 'w');
    // fwrite($handle, 'Subject: '. $subject. "\n\n");
    // fwrite($handle, $message);
    // fclose($handle);
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'reject_job') {
    $data = array();
    $data['id'] = $_POST['id'];
    $data['referee_rejected_on'] = now();
    
    if (!Referral::update($data)) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_approvals') {
    $order_by = 'referees.referred_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT member_referees.id, member AS email_addr, 
              CONCAT(members.lastname, ', ', members.firstname) AS member_name, 
              DATE_FORMAT(referred_on, '%e %b, %Y') AS formatted_referred_on 
              FROM member_referees 
              LEFT JOIN members ON members.email_addr = member_referees.member 
              WHERE referee = '". $_POST['id']. "' AND approved = 'N' AND rejected = 'N' 
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
    echo $xml_dom->get_xml_from_array(array('approvals' => array('approval' =>$result)));
    exit();
}

if ($_POST['action'] == 'approve_contact') {
    $query = "UPDATE member_referees SET
              approved = 'Y' 
              WHERE id = ". $_POST['id']. "; 
              INSERT INTO member_referees SET 
              member = '". $_POST['member']. "', 
              referee = '". $_POST['contact']. "', 
              referred_on = NOW(), 
              approved = 'Y'";
    $mysqli = Database::connect();
    $result = $mysqli->transact($query);
    if (!$result) {
        echo 'ko';
        exit();
    }
    
    $member = new Member($_POST['member']);
    $referee = new Member($_POST['contact']);
    $mail_lines = file('../private/mail/member_approved.txt');
    $message = '';
    foreach ($mail_lines as $line) {
        $message .= $line;
    }

    $message = str_replace('%member_name%', $member->get_name(), $message);
    $message = str_replace('%referee_name%', $referee->get_name(), $message);
    $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
    $message = str_replace('%root%', $GLOBALS['root'], $message);
    $subject = desanitize($member->get_name()). " has approved your request";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($referee->id(), $subject, $message, $headers);
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'reject_contact') {
    $query = "UPDATE member_referees SET
              approved = 'N', rejected = 'Y' 
              WHERE id = ". $_POST['id'];
    $mysqli = Database::connect();
    $result = $mysqli->execute($query);
    if (!$result) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_rewards') {
    $order_by = 'employed_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT referrals.id, industries.industry, jobs.id AS job_id, jobs.title, 
              referrals.total_reward, currencies.symbol AS currency, member_referees.id AS referee_id, 
              CONCAT(members.lastname, ', ', members.firstname) AS candidate, 
              DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on, 
              DATE_FORMAT(referrals.work_commence_on, '%e %b, %Y') AS formatted_work_commence_on, 
              SUM(referral_rewards.reward) AS paid_reward 
              FROM referrals 
              LEFT JOIN referral_rewards ON referral_rewards.referral = referrals.id 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN currencies ON currencies.country_code = employers.country 
              LEFT JOIN industries ON industries.id = jobs.industry 
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
        $result[$key]['total_reward'] = number_format($row['total_reward'], 2, '.', ', ');
        $result[$key]['paid_reward'] = number_format($row['paid_reward'], 2, '.', ', ');
    }
    
    $response = array('rewards' => array('reward' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_acknowledgements') {
    $order_by = 'referee_acknowledged_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT referrals.id, employers.name AS employer, jobs.id AS job_id, jobs.title, 
              jobs.potential_reward, jobs.currency, member_referees.id AS referee_id, 
              CONCAT(members.lastname, ', ', members.firstname) AS candidate, 
              DATE_FORMAT(referrals.referred_on, '%e %b, %Y') AS formatted_referred_on, 
              DATE_FORMAT(referrals.referee_acknowledged_on, '%e %b, %Y') AS formatted_acknowledged_on 
              FROM referrals 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN employers ON employers.id = jobs.employer 
              LEFT JOIN members ON members.email_addr = referrals.referee 
              LEFT JOIN member_referees ON member_referees.member = referrals.member AND 
              member_referees.referee = referrals.referee 
              WHERE referrals.member = '". $_POST['id']. "' AND 
              member_referees.member = '". $_POST['id']. "' AND 
              (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
              (referrals.work_commence_on IS NULL OR referrals.work_commence_on = '0000-00-00 00:00:00') AND 
              (referrals.referee_acknowledged_on IS NOT NULL OR referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
              (referrals.referee_acknowledged_others_on IS NULL OR referrals.referee_acknowledged_others_on = '0000-00-00 00:00:00') AND
              (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
              (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') 
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
        $result[$key]['potential_reward'] = number_format($row['potential_reward'], 2, '.', ', ');
    }
    
    $response = array('acknowledgements' => array('acknowledgement' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_counts') {
    $counts = array();
    $counts['referrals'] = 0;
    $counts['approvals'] = 0;
    $counts['rewards'] = 0;
    $counts['responses'] = 0;
    
    $mysqli = Database::connect();
    
    // 1. Count referred
    $query = "SELECT COUNT(referrals.id) AS num_referrals 
              FROM referrals 
              LEFT JOIN member_referees ON member_referees.member = referrals.member AND 
              member_referees.referee = referrals.referee 
              WHERE referrals.referee = '". $_POST['id']. "' AND 
              member_referees.approved = 'Y' AND 
              (referrals.referee_acknowledged_on IS NULL OR referrals.referee_acknowledged_on = '0000-00-00 00:00:00') AND 
              (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
              (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
              (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00')";
    $result = $mysqli->query($query);
    $counts['referrals'] = $result[0]['num_referrals'];
    
    // 2. Count approvals
    $query = "SELECT COUNT(id) AS num_approvals 
              FROM member_referees 
              WHERE referee = '". $_POST['id']. "' AND approved = 'N' AND rejected = 'N'";
    $result = $mysqli->query($query);
    $counts['approvals'] = $result[0]['num_approvals'];
    
    // 3. Count rewards 
    $query = "SELECT COUNT(referrals.id) AS num_rewards 
              FROM referrals 
              LEFT JOIN member_referees ON member_referees.member = referrals.member AND 
              member_referees.referee = referrals.referee 
              WHERE referrals.member = '". $_POST['id']. "' AND 
              (referrals.employed_on IS NOT NULL AND referrals.employed_on <> '0000-00-00 00:00:00') AND 
              (referrals.work_commence_on IS NOT NULL AND referrals.work_commence_on <> '0000-00-00 00:00:00') AND 
              (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00')";
    $result = $mysqli->query($query);
    $counts['rewards'] = $result[0]['num_rewards'];
    
    // 4. Count responses
    /*$query = "SELECT COUNT(referrals.id) AS num_responses 
              FROM referrals 
              LEFT JOIN member_referees ON member_referees.member = referrals.member AND 
              member_referees.referee = referrals.referee 
              WHERE referrals.member = '". $_POST['id']. "' AND 
              member_referees.member = '". $_POST['id']. "' AND 
              (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
              (referrals.work_commence_on IS NULL OR referrals.work_commence_on = '0000-00-00 00:00:00') AND 
              (referrals.referee_acknowledged_on IS NOT NULL OR referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
              (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
              (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00')";
    $result = $mysqli->query($query);
    $counts['responses'] = $result[0]['num_responses'];
    */
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('counts' => $counts));
    exit();
}

if ($_POST['action'] == 'get_hide_banner') {
    $query = "SELECT pref_value FROM member_banners 
              WHERE member = '". $_POST['id']. "' AND pref_key = 'hide_welcome_banner' LIMIT 1";
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
              WHERE member = '". $_POST['id']. "' AND pref_key = 'hide_welcome_banner' LIMIT 1";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if ($result[0]['id'] > 0) {
        $query = "UPDATE member_banners SET pref_value = '". $_POST['hide']. "' WHERE id = ". $result[0]['id'];
    } else {
        $query = "INSERT INTO member_banners SET 
                  id = 0,
                  pref_key = 'hide_welcome_banner', 
                  pref_value = '". $_POST['hide']. "',
                  member = '". $_POST['id']. "'";
    }
    
    $mysqli->execute($query);
    
    exit();
}

if ($_POST['action'] == 'get_completeness_status') {
    $mysqli = Database::connect();
    $query = "SELECT members.checked_profile, bank.has_bank, cv.has_resume, photo.has_photo 
              FROM members, 
              (SELECT COUNT(*) AS has_bank FROM member_banks WHERE member = '". $_POST['id']. "') bank, 
              (SELECT COUNT(*) AS has_resume FROM resumes WHERE member = '". $_POST['id']. "' AND deleted = 'N') cv, 
              (SELECT COUNT(*) AS has_photo FROM member_photos WHERE member = '". $_POST['id']. "') photo 
              WHERE members.email_addr = '". $_POST['id']. "'";
    $result = $mysqli->query($query);
    if (is_null($result) || empty($result) || !$result) {
        echo '0';
        exit();
    }
    
    $response = array();
    $response['checked_profile'] = ($result[0]['checked_profile'] == 'Y') ? '1' : '0';
    $response['has_bank'] = ($result[0]['has_bank'] > 0) ? '1' : '0';
    $response['has_resume'] = ($result[0]['has_resume'] > 0) ? '1' : '0';
    $response['has_photo'] = ($result[0]['has_photo'] > 0) ? '1' : '0';
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('completeness' => $response));
}
?>
