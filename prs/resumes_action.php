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
    $order_by = 'members.joined_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $filter = '';
    if (isset($_POST['filter_by'])) {
        if (!empty($_POST['filter_by']) && $_POST['filter_by'] > 0) {
            $filter = $_POST['filter_by'];
        }
    }
    
    $filter_country = '';
    if (isset($_POST['filter_country_by'])) {
        if (!empty($_POST['filter_country_by'])) {
            $filter_country = $_POST['filter_country_by'];
        }
    }
    
    $filter_zip = '';
    if (isset($_POST['filter_zip_by'])) {
        if (!empty($_POST['filter_zip_by'])) {
            $filter_zip = $_POST['filter_zip_by'];
        }
    }
    
    $query = "SELECT DISTINCT members.email_addr AS email_addr, members.phone_num AS phone_num, members.remarks, 
              members.zip, countries.country, members.active, 
              CONCAT(members.firstname, ', ', members.lastname) AS member_name, 
              DATE_FORMAT(members.joined_on, '%e %b, %Y') AS formatted_joined_on, 
              (SELECT COUNT(id) FROM resumes WHERE member = members.email_addr) AS has_resumes 
              FROM members
              LEFT JOIN industries AS primary_industries ON primary_industries.id = members.primary_industry 
              LEFT JOIN industries AS secondary_industries ON secondary_industries.id = members.secondary_industry 
              LEFT JOIN industries AS tertiary_industries ON tertiary_industries.id = members.tertiary_industry 
              LEFT JOIN countries ON countries.country_code = members.country 
              WHERE (members.added_by IS NULL OR members.added_by = '') AND 
              (members.zip IS NOT NULL AND members.zip <> '') ";
    
    if (!empty($filter)) {
        $query .= "AND (members.primary_industry = ". $filter. " OR members.secondary_industry = ". $filter. " OR members.tertiary_industry = ". $filter. ") ";
    }
    
    if (!empty($filter_country)) {
        $query .= "AND members.country = '". $filter_country. "' ";
    }
    
    if (!empty($filter_zip)) {
        $query .= "AND members.zip = '". $filter_zip. "' ";
    }
    
    $query .= "ORDER BY ". $_POST['order_by'];
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if (count($result) <= 0 || is_null($result) || empty($result)) {
        echo '0';
        exit();
    }
    
    if (!$result) {
        echo 'ko';
        exit();
    }
    
    $new_result = array();
    foreach($result as $i=>$row) {
        $result[$i]['member_name'] = htmlspecialchars_decode(html_entity_decode(stripslashes(desanitize($row['member_name']))));
        
        if (stripos($result[$i]['member_name'], 'yellow') === false && 
            stripos($result[$i]['member_name'], 'elevator') === false) {
            $new_result[] = $result[$i];
        }
    }
    $result = $new_result;
    if (count($result) <= 0 || is_null($result) || empty($result)) {
        echo '0';
        exit();
    }
    
    $response = array('members' => array('member' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_profile') {
    $query = "SELECT members.email_addr AS email_addr, members.phone_num AS phone_num, members.remarks, 
              members.firstname, members.lastname, 
              DATE_FORMAT(members.joined_on, '%e %b, %Y') AS formatted_joined_on, 
              primary_industries.industry AS first_industry, 
              secondary_industries.industry AS second_industry, 
              tertiary_industries.industry AS tertiary_industry, 
              countries.country, members.zip 
              FROM members 
              LEFT JOIN countries ON countries.country_code = members.country 
              LEFT JOIN industries AS primary_industries ON primary_industries.id = members.primary_industry 
              LEFT JOIN industries AS secondary_industries ON secondary_industries.id = members.secondary_industry 
              LEFT JOIN industries AS tertiary_industries ON tertiary_industries.id = members.tertiary_industry 
              WHERE members.email_addr = '". $_POST['id']. "'";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    
    $profile = array();
    foreach ($result[0] as $key => $value) {
        $profile[$key] = $value;
        
        if (stripos($key, 'firstname') !== false || stripos($key, 'lastname') !== false) {
            $profile[$key] = htmlspecialchars_decode(html_entity_decode(stripslashes(desanitize($value))));
        }
    }

    $response =  array('profile' => $profile);

    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_resumes') {
    $order_by = 'modified_on desc';

    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }

    $criteria = array(
        'columns' => 'id, name, private, DATE_FORMAT(modified_on, \'%e %b, %Y\') AS modified_date, file_hash, file_name',
        'order' => $order_by,
        'match' => 'member = \''. $_POST['id']. '\' AND deleted = \'N\''
    );

    $resumes = Resume::find($criteria);
    $response = array(
        'resumes' => array('resume' => $resumes)
    );

    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_filters') {
    $mysqli = Database::connect();
    $query = "SELECT DISTINCT industries.id, industries.industry FROM 
              (SELECT DISTINCT primary_industry AS industry 
               FROM members 
               WHERE primary_industry IS NOT NULL AND 
               (added_by IS NULL OR added_by = '') 
               UNION 
               SELECT DISTINCT secondary_industry 
               FROM members 
               WHERE secondary_industry IS NOT NULL AND 
               (added_by IS NULL OR added_by = '')) AS member_industry 
              LEFT JOIN industries ON industries.id = member_industry.industry 
              ORDER BY industries.industry";
    $result = $mysqli->query($query);
    
    $filters = array();
    foreach ($result as $i=>$row) {
        $filters[$i]['id'] = $row['id'];
        $filters[$i]['industry'] = $row['industry'];
    }
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('filters' => array('filter' => $filters)));
    exit();
}

if ($_POST['action'] == 'get_jobs') {
    $mysqli = Database::connect();
    $query = "SELECT jobs.id, jobs.title, jobs.description, industries.industry, jobs.currency, jobs.salary, 
              employers.name AS employer
              FROM jobs
              LEFT JOIN industries ON industries.id = jobs.industry 
              LEFT JOIN employers ON employers.id = jobs.employer 
              WHERE jobs.closed = 'N' AND jobs.expire_on >= NOW()";
    if ($_POST['filter_by'] != '0') {
        $query .= " AND (jobs.industry = ". $_POST['filter_by'] ." OR industries.parent_id = ". $_POST['filter_by']. ")";
    }
    
    $result = $mysqli->query($query);
    if (is_null($result) || empty($result)) {
        echo '0';
        exit();
    }
    
    foreach ($result as $i=>$row) {
        $result[$i]['title'] = desanitize($row['title']);
        $result[$i]['employer'] = desanitize($row['employer']);
        $result[$i]['description'] = htmlspecialchars_decode(html_entity_decode(desanitize($row['description'])));
        $result[$i]['salary'] = number_format($row['salary'], 2, '.', ', ');
    }
    
    $response = array('jobs' => array('job' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'make_referral') {
    $job_id = sanitize($_POST['job']);
    $testimony = $_POST['testimony'];
    
    $employee = new Employee($_POST['id']);
    $branch = $employee->get_branch();
    $member = 'team.'. strtolower($branch[0]['country_code']). '@yellowelevator.com';
    $testimony = sanitize('Yellow Elevator Contact:<br/>'. $employee->get_name(). ' ['. $employee->email_address(). ']<br/><br/>'. $testimony);
    
    $mysqli = Database::connect();
    $job = array();
    $query = "SELECT jobs.title, employers.name, employers.email_addr, employers.like_instant_notification,  
              jobs.contact_carbon_copy 
              FROM jobs 
              LEFT JOIN employers ON employers.id = jobs.employer 
              WHERE jobs.id = ". $job_id. " LIMIT 1";
    $result = $mysqli->query($query);
    $job['id'] = $job_id;
    $job['job'] = 'Unknown Job';
    $job['employer'] = 'Unknown Employer';
    $job['employer_email_addr'] = '';
    $job['employer_notify_now'] = false;
    if (count($result) > 0 && !is_null($result)) {
        $job['job'] = $result[0]['title'];
        $job['employer'] = $result[0]['name'];
        $job['employer_email_addr'] = $result[0]['email_addr'];
        $job['employer_notify_now'] = ($result[0]['like_instant_notification'] == '1') ? true : false;
        if (!empty($result[0]['contact_carbon_copy']) && !is_null($result[0]['contact_carbon_copy'])) {
            $job['contact_carbon_copy'] = $result[0]['contact_carbon_copy'];
        }
    }
    
    // check whether are both the member and referee friend
    $query = "SELECT COUNT(*) AS is_friend 
              FROM member_referees 
              WHERE (member = '". $member. "' AND 
              referee = '". $_POST['referee']. "') OR 
              (referee = '". $member. "' AND 
              member = '". $_POST['referee']. "')";
    $result = $mysqli->query($query);
    if ($result[0]['is_friend'] <= 0) {
        $query = "INSERT INTO member_referees SET 
                  `member` = '". $_POST['referee']. "', 
                  `referee` = '". $member. "', 
                  `referred_on` = NOW(), 
                  `approved` = 'Y'; 
                  INSERT INTO member_referees SET 
                  `referee` = '". $_POST['referee']. "', 
                  `member` = '". $member. "', 
                  `referred_on` = NOW(), 
                  `approved` = 'Y'";
        if (!$mysqli->transact($query)) {
            echo '-1';
            exit();
        }
    } else if ($result[0]['is_friend'] == 1) {
        // candidate may want to do their own referral instead
        echo '-2';
        exit();
    }
    
    $today = now();
    $data = array();
    $data['member'] = $member;
    $data['job'] = $job_id;
    $data['referred_on'] = $today;
    $data['member_confirmed_on'] = $today;
    $data['member_rejected_on'] = 'NULL';
    $data['testimony'] = $testimony;
    $data['referee'] = $_POST['referee'];
    $data['resume'] = $_POST['resume'];
    $data['member_read_resume_on'] = $today;
    $data['referee_acknowledged_on'] = $today;
    
    
    if (!Referral::create($data)) {
        echo 'ko';
        exit();
    }
    
    if ($job['employer_notify_now']) {
        $lines = file(dirname(__FILE__). '/../private/mail/employer_new_referral.txt');
        $message = '';
        foreach($lines as $line) {
            $message .= $line;
        }

        $message = str_replace('%company%', desanitize($job['employer']), $message);
        $message = str_replace('%job%', desanitize($job['job']), $message);
        $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
        $message = str_replace('%root%', $GLOBALS['root'], $message);
        $subject = "New application for ". desanitize($job['job']). " position";
        $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
        if (array_key_exists('contact_carbon_copy', $job)) {
            $headers .= 'Cc: '. $job['contact_carbon_copy']. "\n";
        }
        mail($job['employer_email_addr'], $subject, $message, $headers);

        // $handle = fopen('/tmp/email_to_'. $job['employer_email_addr']. '.txt', 'w');
        // fwrite($handle, 'Subject: '. $subject. "\n\n");
        // fwrite($handle, $message);
        // fclose($handle);
    }
    
    $position = '- '. $job['job']. ' at '. $job['employer'];
    $lines = file(dirname(__FILE__). '/../private/mail/member_referred_from_prs.txt');
    $message = '';
    foreach($lines as $line) {
        $message .= $line;
    }
    
    $message = str_replace('%member_name%', 'Yellow Elevator', $message);
    $message = str_replace('%member_email_addr%', $member, $message);
    $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
    $message = str_replace('%root%', $GLOBALS['root'], $message);
    $message = str_replace('%positions%', $position, $message);
    $subject = "Yellow Elevator has screened and submitted your resume for the ". htmlspecialchars_decode($job['job']). " position";
    $headers = 'From: Yellow Elevator <'. $member. '>' . "\n";
    mail($_POST['referee'], $subject, $message, $headers);
    
    // $handle = fopen('/tmp/ref_email_to_'. $_POST['referee']. '.txt', 'w');
    // fwrite($handle, 'Subject: '. $subject. "\n\n");
    // fwrite($handle, $message);
    // fclose($handle);
    
    echo '0';
    exit();
}

if ($_POST['action'] == 'save_remark') {
    $member = new Member($_POST['id']);
    $data = array();
    $data['remarks'] = sanitize($_POST['remark']);
    if ($member->update($data)) {
        echo 'ok';
    } else {
        echo 'ko';
    }
    
    exit();
}
?>
