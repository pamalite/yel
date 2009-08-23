<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";
require_once dirname(__FILE__). "/credit_note.php";

session_start();

function get_suggested_candidates($_job_id, $_referral_id_only = false) {
    $order_by = 'score desc';

    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $mysqli = Database::connect();
    $query = "SELECT description FROM job_index WHERE job = ". $_job_id. " LIMIT 1";
    $result = $mysqli->query($query);
    if (count($result) <= 0 || is_null($result)) {
        echo "0";
        exit();
    }
    
    if (!$result) {
        echo "ko";
        exit();
    }
    
    $job_description = $result[0]['description'];
    
    $query = "SELECT referrals.resume, resumes.file_hash 
              FROM referrals 
              LEFT JOIN resumes ON resumes.id = referrals.resume 
              WHERE referrals.job = ". $_job_id. " AND 
              (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
              -- (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
              referrals.employer_rejected_on IS NULL AND 
              (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00')";
    $result = $mysqli->query($query);
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }
    
    if (is_null($result[0]['resume'])) {
        echo '0';
        exit();
    }
    
    if (!$result) {
        echo 'ko';
        exit();
    }
    
    $resumes_list = '';
    $non_file_resumes_list = '';
    $i = 0;
    foreach($result as $row) {
        $resumes_list .= $row['resume'];
        
        if ($i < count($result)-1) {
            $resumes_list .= ', ';
        }
        
        $i++;
    }
    
    $i = 0;
    foreach($result as $row) {
        if (empty($row['file_hash']) || is_null($row['file_hash'])) {
            $non_file_resumes_list .= $row['resume'];
            
            if ($i < count($result)-1) {
                $non_file_resumes_list .= ', ';
            }
        }
        
        $i++;
    }
    
    if (empty($non_file_resumes_list)) {
        return array();
    }
    
    $match_against = "MATCH (resume_index.cover_note, 
                             resume_index.skill, 
                             resume_index.technical_skill, 
                             resume_index.qualification, 
                             resume_index.work_summary) 
                      AGAINST ('". $job_description. "')";
    $max_score_temp_table = "(SELECT MAX(". $match_against. ") AS max_score 
                              FROM resume_index 
                              WHERE resume IN (". $non_file_resumes_list. ")
                              LIMIT 1) AS scores";
    $columns = "CONCAT(members.lastname,', ', members.firstname) AS referrer, 
                CONCAT(referees.lastname,', ', referees.firstname) AS candidate,
                DATE_FORMAT(referrals.referred_on, '%e %b, %Y') AS formatted_referred_on, 
                DATE_FORMAT(referrals.referee_acknowledged_on, '%e %b, %Y') AS formatted_acknowledged_on, 
                DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on, 
                referrals.resume, referrals.id, referrals.testimony, referrals.shortlisted_on, 
                referrals.employer_agreed_terms_on, 
                referrals.used_suggested, ". $match_against. " AS score, scores.max_score";
    if ($_referral_id_only) {
      $columns = "referrals.id";
    }
    $query= "SELECT ". $columns. " 
             FROM resume_index 
             LEFT JOIN referrals ON referrals.resume = resume_index.resume 
             LEFT JOIN members ON members.email_addr = referrals.member 
             LEFT JOIN members AS referees ON referees.email_addr = referrals.referee 
             , ". $max_score_temp_table. "
             WHERE ". $match_against. " AND 
             referrals.job = ". $_POST['id']. " AND 
             referrals.resume IN (". $non_file_resumes_list. ") AND
             (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
             (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
             referrals.employer_rejected_on IS NULL AND 
             (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00')";
    if (!$_referral_id_only) {
        $query .= " ORDER BY ". $order_by;
    }
    $result = $mysqli->query($query);
    
    return $result;
}

if (!isset($_POST['id'])) {
    echo "ko";
    exit();
    //redirect_to('login.php');
}

$xml_dom = new XMLDOM();

if (!isset($_POST['action'])) {
    $order_by = 'num_referrals desc';

    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $today = now();
    //$today = '0000-00-00 00:00:00'; // use this to temporarily bypass the date
    $query = "SELECT industries.industry, jobs.id, jobs.title, jobs.created_on, 
              jobs.expire_on, COUNT(referrals.id) AS num_referrals, jobs.description  
              FROM referrals 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              LEFT JOIN industries ON industries.id = jobs.industry 
              WHERE jobs.employer = '". $_POST['id']. "' AND 
              (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
              -- (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
              referrals.employer_rejected_on IS NULL AND 
              (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00')
              GROUP BY referrals.job 
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
    
    foreach ($result as $i=>$row) {
        $result[$i]['description'] = htmlspecialchars_decode(desanitize($row['description']));
        $result[$i]['new_referrals_count'] = '0';
    }
    
    $query = "SELECT jobs.id, COUNT(referrals.id) AS num_new_referrals 
              FROM referrals 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              WHERE jobs.employer = '". $_POST['id']. "' AND 
              (referrals.employer_agreed_terms_on IS NULL OR referrals.employer_agreed_terms_on = '0000-00-00 00:00:00') AND 
              (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
              -- (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
              referrals.employer_rejected_on IS NULL AND 
              (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00')
              GROUP BY referrals.job";
    
    $new_referrals = $mysqli->query($query);
    if (count($new_referrals) > 0 && !is_null($new_referrals)) {
        foreach ($new_referrals as $new_referral) {
            foreach ($result as $i=>$row) {
                if ($row['id'] == $new_referral['id']) {
                    $result[$i]['new_referrals_count'] = $new_referral['num_new_referrals'];
                    break;
                }
            }
        }
    }
    
    $response = array('referrals' => array('referral' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_description') {
    $job = new Job($_POST['id']);
    $result = $job->get();
    $response = array(
        'job' => array(
            'title' => $result[0]['title'],
            'description' => htmlspecialchars_decode($result[0]['description'])
        )
    );
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_salary') {
    $query = "SELECT salary FROM jobs WHERE id = ". $_POST['id'];
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('job' => array('salary' => number_format($result[0]['salary'], 2, '.', ','))));
    exit();
}

if ($_POST['action'] == 'get_referred_candidates') {
    $order_by = 'referred_on desc';
    $filter_by = '';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    if (isset($_POST['filter_by'])) {
        $filter_by = 'resumes.file_hash IS '. $_POST['filter_by']. ' AND ';
    }
    
    $result = get_suggested_candidates($_POST['id'], true);
    $recommended_query = '';
    $recommended = '';
    foreach ($result as $i=>$referral_id) {
        $recommended .= $referral_id['id'];
        
        if ($i < (count($result)-1)) {
            $recommended .= ',';
        }
    }
    
    if (!empty($recommended)) {
        $recommended_query = "AND referrals.id NOT IN (". $recommended. ") ";
    }
    
    $query= "SELECT CONCAT(members.lastname,', ', members.firstname) AS referrer, 
             CONCAT(referees.lastname,', ', referees.firstname) AS candidate,
             DATE_FORMAT(referrals.referred_on, '%e %b, %Y') AS formatted_referred_on, 
             DATE_FORMAT(referrals.referee_acknowledged_on, '%e %b, %Y') AS formatted_acknowledged_on, 
             DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on, 
             referrals.resume, referrals.id, referrals.testimony, referrals.shortlisted_on, referrals.used_suggested,
             referrals.employer_agreed_terms_on 
             FROM referrals 
             LEFT JOIN members ON members.email_addr = referrals.member 
             LEFT JOIN members AS referees ON referees.email_addr = referrals.referee 
             LEFT JOIN resumes ON resumes.id = referrals.resume 
             WHERE referrals.job = ". $_POST['id']. " AND ". $filter_by. "
             (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
             -- (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
             referrals.employer_rejected_on IS NULL AND 
             (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') ". $recommended_query. " 
             ORDER BY ". $order_by;
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if (count($result) <= 0 || is_null($result)) {
        echo "0";
        exit();
    }
    
    if (!$result) {
        echo "ko";
        exit();
    }
    
    foreach ($result as $i=>$row) {
        $result[$i]['testimony'] = htmlize(desanitize($row['testimony']));
        if (is_null($row['employer_agreed_terms_on']) || $row['employer_agreed_terms_on'] == '0000-00-00 00:00:00') {
            $result[$i]['employer_agreed_terms_on'] = '-1';
        }
    }
    
    $response = array('referrals' => array('referral' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_suggested_candidates') {
    $result = get_suggested_candidates($_POST['id']);
    if (count($result) <= 0 || is_null($result)) {
        echo "0";
        exit();
    }

    if (!$result) {
        echo "ko";
        exit();
    }
    
    foreach ($result as $key => $row) {
        $result[$key]['score_percentage'] = ($row['score'] / $row['max_score']) * 100;
        if (is_null($row['employer_agreed_terms_on']) || $row['employer_agreed_terms_on'] == '0000-00-00 00:00:00') {
            $result[$key]['employer_agreed_terms_on'] = '-1';
        }
    }
    
    $response = array('referrals' => array('referral' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_shortlisted_candidates') {
    $order_by = 'referrals.shortlisted_on desc';

    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query= "SELECT CONCAT(members.lastname,', ', members.firstname) AS referrer, 
             CONCAT(referees.lastname,', ', referees.firstname) AS candidate,
             DATE_FORMAT(referrals.referred_on, '%e %b, %Y') AS formatted_referred_on, 
             DATE_FORMAT(referrals.referee_acknowledged_on, '%e %b, %Y') AS formatted_acknowledged_on, 
             DATE_FORMAT(referrals.shortlisted_on, '%e %b, %Y') AS formatted_shortlisted_on, 
             DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on, 
             referrals.resume, referrals.id, referrals.testimony, referrals.shortlisted_on, referrals.used_suggested, 
             referrals.employer_agreed_terms_on 
             FROM referrals 
             LEFT JOIN members ON members.email_addr = referrals.member 
             LEFT JOIN members AS referees ON referees.email_addr = referrals.referee 
             WHERE referrals.job = ". $_POST['id']. " AND 
             (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
             -- (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
             (referrals.shortlisted_on IS NOT NULL AND referrals.shortlisted_on <> '0000-00-00 00:00:00') AND
             referrals.employer_rejected_on IS NULL AND 
             (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00')
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
    
    foreach ($result as $i=>$row) {
        $result[$i]['testimony'] = htmlspecialchars_decode($row['testimony']);
        if (is_null($row['employer_agreed_terms_on']) || $row['employer_agreed_terms_on'] == '0000-00-00 00:00:00') {
            $result[$i]['employer_agreed_terms_on'] = '-1';
        }
    }
    
    $response = array('referrals' => array('referral' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_verify_resume_viewing') {
    $query = "SELECT employer_agreed_terms_on FROM referrals WHERE id = ". $_POST['id'];
    $mysqli = Database::connect();
    if ($result = $mysqli->query($query)) {
        if (is_null($result[0]['employer_agreed_terms_on'])) {
            echo '0';
            exit();
        }
        
        echo '1';
        exit();
    } 
    
    echo "ko";
    exit();
}

if ($_POST['action'] == 'set_verify_resume_viewing') {
    $data = array();
    $data['id'] = $_POST['id'];
    $data['employer_agreed_terms_on'] = now();
    
    if (Referral::update($data)) {
        $query = "SELECT referrals.referee AS email, jobs.title AS job_title, employers.name AS employer_name, 
                  resumes.name AS resume_name, 
                  CONCAT(members.lastname,', ', members.firstname) AS referee 
                  FROM referrals 
                  LEFT JOIN members ON members.email_addr = referrals.referee 
                  LEFT JOIN jobs ON jobs.id = referrals.job 
                  LEFT JOIN employers ON employers.id = jobs.employer 
                  LEFT JOIN resumes ON resumes.id = referrals.resume 
                  WHERE referrals.id = ". $data['id']. " LIMIT 1";
        $mysqli = Database::connect();
        $result = $mysqli->query($query);
        $referee_email = $result[0]['email'];
        $referee_name = $result[0]['referee'];
        $employer = $result[0]['employer_name'];
        $job = $result[0]['job_title'];
        $resume = $result[0]['resume_name'];
        
        $lines = file(dirname(__FILE__). '/../private/mail/employer_viewed_resume.txt');
        $message = '';
        foreach($lines as $line) {
           $message .= $line;
        }
        
        $message = str_replace('%referee%', htmlspecialchars_decode(desanitize($referee_name)), $message);
        $message = str_replace('%employer%', htmlspecialchars_decode(desanitize($employer)), $message);
        $message = str_replace('%job%', htmlspecialchars_decode(desanitize($job)), $message);
        $message = str_replace('%resume%', htmlspecialchars_decode(desanitize($resume)), $message);
        $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
        $message = str_replace('%root%', $GLOBALS['root'], $message);
        $subject = desanitize($employer). " has viewed your resume";
        $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
        mail($referee_email, $subject, $message, $headers);
        
        /*$handle = fopen('/tmp/email_to_'. $referee_email. '.txt', 'w');
        fwrite($handle, 'Subject: '. $subject. "\n\n");
        fwrite($handle, $message);
        fclose($handle);*/
           
        echo "ok";
        exit();
    }
    
    echo "ko";
    exit();
}

if ($_POST['action'] == 'reject_candidates') {
    if (!isset($_POST['payload'])) {
        echo "ko";
        exit();
    }
    
    $xml_dom->load_from_xml($_POST['payload']);
    $candidates = $xml_dom->get('id');
    $query = "UPDATE referrals SET employer_rejected_on = NOW() WHERE id IN (";
    if ($_POST['used_suggested'] == 'Y') {
        $query = "UPDATE referrals SET employer_rejected_on = NOW(), used_suggested = 'Y' WHERE id IN (";
    }
    $i = 0;
    foreach ($candidates as $candidate) {
        $query .= $candidate->nodeValue;
        
        if ($i < $candidates->length-1) {
            $query .= ", ";
        }
        
        $i++;
    }
    $query .= ")";
    
    $mysqli = Database::connect();
    
    if (!$mysqli->execute($query)) {
        echo "ko";
        exit();
    }
    
    echo "ok";
    exit();
}

if ($_POST['action'] == 'shortlist_referral') {
    $data = array();
    $data['id'] = $_POST['id'];
    $data['shortlisted_on'] = now();
    $data['used_suggested'] = $_POST['used_suggested'];
    
    if (!Referral::update($data)) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'unshortlist_referral') {
    $data = array();
    $data['id'] = $_POST['id'];
    $data['shortlisted_on'] = 'NULL';
    
    if (!Referral::update($data)) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_suggested_candidates_count') {
    $result = get_suggested_candidates($_POST['id'], true);
    echo (($result === false) ? '0' : count($result));
    exit();
}

if ($_POST['action'] == 'get_referred_candidates_count') {
    $result = get_suggested_candidates($_POST['id'], true);
    $recommended_query = '';
    $recommended = '';
    if (count($result) > 0 && $result !== false ) {
        foreach ($result as $i=>$referral_id) {
            $recommended .= $referral_id['id'];

            if ($i < (count($result)-1)) {
                $recommended .= ',';
            }
        }
    }
    
    if (!empty($recommended)) {
        $recommended_query = "AND referrals.id NOT IN (". $recommended. ") ";
    }
    
    $query= "SELECT COUNT(referrals.id) AS number_of_referred 
             FROM referrals 
             LEFT JOIN members ON members.email_addr = referrals.member 
             LEFT JOIN members AS referees ON referees.email_addr = referrals.referee 
             LEFT JOIN resumes ON resumes.id = referrals.resume 
             WHERE referrals.job = ". $_POST['id']. " AND 
             (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
             -- (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
             referrals.employer_rejected_on IS NULL AND 
             (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') ". $recommended_query;
             
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    echo (($result === false) ? '0' : $result[0]['number_of_referred']);
    exit();
}

if ($_POST['action'] == 'get_shortlisted_candidates_count') {
    $query= "SELECT COUNT(referrals.id) AS number_of_shortlisted 
             FROM referrals 
             LEFT JOIN members ON members.email_addr = referrals.member 
             LEFT JOIN members AS referees ON referees.email_addr = referrals.referee 
             WHERE referrals.job = ". $_POST['id']. " AND 
             (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
             -- (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
             (referrals.shortlisted_on IS NOT NULL AND referrals.shortlisted_on <> '0000-00-00 00:00:00') AND
             referrals.employer_rejected_on IS NULL AND 
             (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00')";
             
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    echo (($result === false) ? '0' : $result[0]['number_of_shortlisted']);
    exit();
}

if ($_POST['action'] == 'employ_candidate') {
    $today = $_POST['commence'];
    $mysqli = Database::connect();
    $is_replacement = false;
    $is_free_replacement = false;
    $previous_referral = '0';
    $previous_invoice = '0';
    
    // 1. Update the referral to employed
    $query = "SELECT referrals.employer_agreed_terms_on, 
              referrals.member, referrals.referee, jobs.title 
              FROM referrals 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              WHERE referrals.id = ". $_POST['id'];
    $not_agreed_terms_yet = false;
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if (empty($result[0]['employer_agreed_terms_on']) || is_null($result[0]['employer_agreed_terms_on'])) {
        $not_agreed_terms_yet = true;
    }
    
    $member = new Member($result[0]['member']);
    $referee = new Member($result[0]['referee']);
    $job_title = $result[0]['title'];
    
    $data = array();
    $data['id'] = $_POST['id'];
    $data['employed_on'] = now();
    $data['work_commence_on'] = $_POST['commence'];
    $data['salary_per_annum'] = $_POST['salary'];
    $data['total_reward'] = Referral::calculate_total_reward_from($_POST['salary'], $_POST['employer']);
    $data['used_suggested'] = $_POST['used_suggested'];
    $data['guarantee_expire_on'] = Referral::get_guarantee_expiry_date_from($_POST['salary'], $_POST['employer'], $today);
    if ($not_agreed_terms_yet) {
        $data['employer_agreed_terms_on'] = $data['employed_on'];
    }
    
    // 1.1 Check whether the reward is 0.00 or NULL. If it is, then the employer account is not ready. 
    if ($data['total_reward'] <= 0.00 || 
        $data['guarantee_expire_on'] == '0000-00-00 00:00:00' || 
        is_null($data['guarantee_expire_on'])) {
        echo '-1';
        exit();
    }
    
    if (!Referral::update($data)) {
        echo "ko";
        exit();
    }
    
    // 2. Generate an Reference Invoice
    // 2.1 Check whether this job is a replacement for a previous failed referral. 
    // 2.1.1 Get the job details.
    $query = "SELECT referrals.job, jobs.title 
              FROM referrals 
              LEFT JOIN jobs ON jobs.id = referrals.job 
              WHERE referrals.id = ". $_POST['id'];
    $job = $mysqli->query($query);
    
    // 2.1.2 Check for replacement.
    $query = "SELECT id 
              FROM referrals 
              WHERE job = ". $job[0]['job']. " AND 
              (replacement_authorized_on IS NOT NULL AND replacement_authorized_on <> '0000-00-00 00:00:00') AND 
              (replaced_on IS NULL OR replaced_on = '0000-00-00 00:00:00') AND 
              replaced_referral IS NULL 
              LIMIT 1";
    $result = $mysqli->query($query);
    if (count($result) > 0 && !is_null($result)) {
        $is_replacement = true;
        $previous_referral = $result[0]['id'];
    }
    
    // 2.2 Get all the fees, discounts and extras and calculate accordingly.
    $employer = new Employer($_POST['employer']);
    $fees = $employer->get_fees();
    $extras = $employer->get_extras();
    $payment_terms_days = $employer->get_payment_terms_days();
    
    $subtotal = $discount = $extra_charges = 0.00;
    foreach($fees as $fee) {
        if ($_POST['salary'] >= $fee['salary_start'] && ($_POST['salary'] <= $fee['salary_end'] || $fee['salary_end'] == 0)) {
            $discount = -($_POST['salary'] * ($fee['discount'] / 100.00));
            //$subtotal = ($_POST['salary'] * (($fee['service_fee'] + $fee['premier_fee']) / 100.00));
            $subtotal = ($_POST['salary'] * ($fee['service_fee'] / 100.00));
            break;
        }
    }
    
    foreach($extras as $extra) {
        $extra_charges = $extra_charges + $extra['charges'];
    }
    
    $new_total_fee = $subtotal + $discount + $extra_charges;
    $credit_amount = 0;
    // 2.2.1 If this is a replacement, re-calculate accordingly by taking the previously invoiced amount.
    if ($is_replacement) {
        // 2.2.1a If it is a replacement, get the previously invoiced amount.
        $query = "SELECT invoices.id, SUM(invoice_items.amount) AS amount_payable 
                  FROM invoices 
                  LEFT JOIN invoice_items ON invoice_items.invoice = invoices.id 
                  WHERE invoices.type = 'R' AND 
                  invoice_items.item = ". $previous_referral. " 
                  GROUP BY invoices.id";
        $result = $mysqli->query($query);
        $amount_payable = $result[0]['amount_payable'];
        $previous_invoice = $result[0]['id'];
        
        // 2.2.1b Get the difference.
        $amount_difference = $new_total_fee - $amount_payable;
        
        // 2.2.1c If the difference in fees is more than zero, then use the amount difference. 
        if (round($amount_difference, 2) <= 0) {
            $subtotal = $discount = $extra_charges = 0.00;
            $is_free_replacement = true;
            $credit_amount = abs($amount_difference);
        } else {
            $discount = $extra_charges = 0.00;
            $subtotal = $amount_difference;
        }
    }
    
    // 2.3 Generate the invoice
    $data = array();
    $data['issued_on'] = today();
    $data['type'] = 'R';
    $data['employer'] = $_POST['employer'];
    $data['payable_by'] = date_add($data['issued_on'], $payment_terms_days, 'day');
    
    if ($is_free_replacement) {
        $data['paid_on'] = $data['issued_on'];
        $data['paid_through'] = 'CSH';
        $data['paid_id'] = 'FREE_REPLACEMENT';
    }
    
    $invoice = Invoice::create($data);
    if (!$invoice) {
        echo "ko";
        exit();
    }
    
    $referral_desc = 'Reference fee for ['. $job[0]['job']. '] '. $job[0]['title'];
    if ($is_free_replacement) {
        $referral_desc = 'Free replacement for Invoice: '. pad($previous_invoice, 11, '0');
    } 
    
    if ($is_replacement && !$is_free_replacement) {
        $referral_desc = 'Replacement fee for Invoice: '. pad($previous_invoice, 11, '0');
    }
    
    $item_added = Invoice::add_item($invoice, $subtotal, $_POST['id'], $referral_desc);
    if (!$item_added) {
        echo "ko";
        exit();
    }
    
    if (!$is_free_replacement) {
        $item_added = Invoice::add_item($invoice, $discount, $_POST['id'], 'Discount');
        if (!$item_added) {
            echo "ko";
            exit();
        }

        $item_added = Invoice::add_item($invoice, $extra_charges, $_POST['id'], 'Extra charges');
        if (!$item_added) {
            echo "ko";
            exit();
        }
    } else {
        if ($credit_amount > 0) {
            $credit_note_desc = 'Refund of balance for Invoice: '. pad($previous_invoice, 11, '0');
            $filename = generate_random_string_of(8). '.'. generate_random_string_of(8);
            $issued_on = today();
            $expire_on = date_add($issued_on, 30, 'day');
            
            Invoice::accompany_credit_note_with($previous_invoice, $invoice, $issued_on, $credit_amount);
            
            $query = "SELECT currencies.symbol 
                      FROM currencies 
                      LEFT JOIN employers ON currencies.country_code = employers.country 
                      WHERE employers.id = '". $employer->id(). "' LIMIT 1";
            $mysqli = Database::connect();
            $result = $mysqli->query($query);
            $currency = '???';
            if (count($result) > 0 && !is_null($result)) {
                $currency = $result[0]['symbol'];
            }
            
            $branch = $employer->get_branch();
            $branch[0]['address'] = str_replace(array("\r\n", "\r"), "\n", $branch[0]['address']);
            $branch['address_lines'] = explode("\n", $branch[0]['address']);
            
            $pdf = new CreditNote();
            $pdf->AliasNbPages();
            $pdf->SetAuthor('Yellow Elevator. This credit note was automatically generated. Signature is not required.');
            $pdf->SetTitle($GLOBALS['COMPANYNAME']. ' - Credit Note '. pad($invoice, 11, '0'));
            $pdf->SetRefundAmount($credit_amount);
            $pdf->SetDescription($credit_note_desc);
            $pdf->SetCurrency($currency);
            $pdf->SetBranch($branch);
            $pdf->AddPage();
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFillColor(54, 54, 54);
            $pdf->Cell(60, 5, "Credit Note Number",1,0,'C',1);
            $pdf->Cell(1);
            $pdf->Cell(33, 5, "Issuance Date",1,0,'C',1);
            $pdf->Cell(1);
            $pdf->Cell(33, 5, "Creditable By",1,0,'C',1);
            $pdf->Cell(1);
            $pdf->Cell(0, 5, "Amount Creditable (". $currency. ")",1,0,'C',1);
            $pdf->Ln(6);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(60, 5, pad($invoice, 11, '0'),1,0,'C');
            $pdf->Cell(1);
            $pdf->Cell(33, 5, $issued_on,1,0,'C');
            $pdf->Cell(1);
            $pdf->Cell(33, 5, $expire_on,1,0,'C');
            $pdf->Cell(1);
            $pdf->Cell(0, 5, number_format($credit_amount, 2, '.', ','),1,0,'C');
            $pdf->Ln(6);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(60, 5, "User ID",1,0,'C',1);
            $pdf->Cell(1);
            $pdf->Cell(0, 5, "Employer Name",1,0,'C',1);
            $pdf->Ln(6);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(60, 5, $employer->id(),1,0,'C');
            $pdf->Cell(1);
            $pdf->Cell(0, 5, $employer->get_name(),1,0,'C');
            $pdf->Ln(10);
            
            $table_header = array("No.", "Item", "Amount (". $currency. ")");
            $pdf->FancyTable($table_header);
            
            $pdf->Ln(13);
            $pdf->SetFont('','I');
            $pdf->Cell(0, 0, "This credit note was automatically generated. Signature is not required.", 0, 0, 'C');
            $pdf->Ln(6);
            $pdf->Cell(0, 5, "Refund Notice",'LTR',0,'C');
            $pdf->Ln();
            $pdf->Cell(0, 5, "- Refund will be made payable to ". $employer->get_name(). ". ", 'LR', 0, 'C');
            $pdf->Ln();
            $pdf->Cell(0, 5, "- To facilitate the refund process, please inform us of any discrepancies.", 'LBR', 0, 'C');
            $pdf->Ln(10);
            $pdf->Cell(0, 0, "E. & O. E.", 0, 0, 'C');
            $pdf->Close();
            $pdf->Output($GLOBALS['data_path']. '/credit_notes/'. $filename. '.pdf', 'F');
            
            $attachment = chunk_split(base64_encode(file_get_contents($GLOBALS['data_path']. '/credit_notes/'. $filename. '.pdf')));

            $subject = "Balance Refund Notice of Invoice ". pad($previous_invoice, 11, '0');
            $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
            $headers .= 'Bcc: sales@yellowelevator.com'. "\n";
            $headers .= 'MIME-Version: 1.0'. "\n";
            $headers .= 'Content-Type: multipart/mixed; boundary="yel_mail_sep_'. $filename. '";'. "\n\n";

            $body = '--yel_mail_sep_'. $filename. "\n";
            $body .= 'Content-Type: multipart/alternative; boundary="yel_mail_sep_alt_'. $filename. '"'. "\n";
            $body .= '--yel_mail_sep_alt_'. $filename. "\n";
            $body .= 'Content-Type: text/plain; charset="iso-8859-1"'. "\n";
            $body .= 'Content-Transfer-Encoding: 7bit"'. "\n";
            
            $mail_lines = file('../private/mail/employer_credit_note.txt');
            $message = '';
            foreach ($mail_lines as $line) {
                $message .= $line;
            }

            $message = str_replace('%company%', $employer->get_name(), $message);
            $message = str_replace('%previous_invoice%', pad($previous_invoice, 11, '0'), $message);
            $message = str_replace('%new_invoice%', pad($invoice, 11, '0'), $message);
            $message = str_replace('%job_title%', $job_title, $message);
            
            $body .= $message. "\n";
            $body .= '--yel_mail_sep_alt_'. $filename. "--\n\n";
            $body .= '--yel_mail_sep_'. $filename. "\n";
            $body .= 'Content-Type: application/pdf; name="yel_credit_note_'. pad($invoice, 11, '0'). '.pdf"'. "\n";
            $body .= 'Content-Transfer-Encoding: base64'. "\n";
            $body .= 'Content-Disposition: attachment'. "\n";
            $body .= $attachment. "\n";
            $body .= '--yel_mail_sep_'. $filename. "--\n\n";
            mail($employer->get_email_address(), $subject, $body, $headers);

            unlink($GLOBALS['data_path']. '/credit_notes/'. $filename. '.pdf');
        }
    }
    
    // 2.4 If it is a replacement, update both referrals to disable future replacements.
    if ($is_replacement) {
        $queries = "UPDATE referrals SET 
                    replaced_on = '". now(). "', 
                    replaced_referral = ". $_POST['id']. " 
                    WHERE id = ". $previous_referral. "; 
                    UPDATE referrals SET 
                    guarantee_expire_on = '". $today. "', 
                    replacement_authorized_on = NULL, 
                    replaced_on = '". now(). "', 
                    replaced_referral = ". $_POST['id']. " 
                    WHERE id = ". $_POST['id'];
        if (!$mysqli->transact($queries)) {
            echo 'ko';
            exit();
        }
    }
    
    // 3. Send a notification
    $mail_lines = file('../private/mail/member_reward.txt');
    $message = '';
    foreach ($mail_lines as $line) {
        $message .= $line;
    }

    $message = str_replace('%member_name%', $member->get_name(), $message);
    $message = str_replace('%referee_name%', $referee->get_name(), $message);
    $message = str_replace('%employer%', $employer->get_name(), $message);
    $message = str_replace('%job_title%', $job_title, $message);
    $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
    $message = str_replace('%root%', $GLOBALS['root'], $message);
    $subject = desanitize($referee->get_name()). " was successfully employed!";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($member->id(), $subject, $message, $headers);
    
    echo "ok";
    exit();
}
?>
