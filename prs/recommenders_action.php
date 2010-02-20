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
    $order_by = 'recommenders.added_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT recommenders.email_addr, recommenders.phone_num, recommenders.remarks, recommenders.region, 
              CONCAT(recommenders.firstname, ', ', recommenders.lastname) AS recommender_name, 
              DATE_FORMAT(recommenders.added_on, '%e %b, %Y') AS formatted_added_on 
              FROM recommenders 
              LEFT JOIN employees ON employees.id = recommenders.added_by ";
    if ($_POST['filter_by'] == '0') {
        $query .= "WHERE employees.branch = ". $_SESSION['yel']['employee']['branch']['id'];
    } else {
        $query .= "LEFT JOIN recommender_industries ON recommender_industries.recommender = recommenders.email_addr 
                   WHERE employees.branch = ". $_SESSION['yel']['employee']['branch']['id']. " AND 
                   recommender_industries.industry = ". $_POST['filter_by'];
    }
    $query .= " AND recommenders.email_addr NOT LIKE 'team.%@yellowelevator.com' 
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
        $result[$i]['recommender_name'] = htmlspecialchars_decode(html_entity_decode(stripslashes(desanitize($row['recommender_name']))));
        $result[$i]['remarks'] = htmlspecialchars_decode(html_entity_decode(stripslashes(desanitize($row['remarks']))));
    }
    
    $response = array('recommenders' => array('recommender' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_profile') {
    $query = "SELECT email_addr, firstname, lastname, phone_num, remarks, region, 
              DATE_FORMAT(added_on, '%e %b, %Y') AS formatted_added_on 
              FROM recommenders 
              WHERE email_addr = '". $_POST['id']. "'";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    
    $profile = array();
    foreach ($result[0] as $key => $value) {
        $profile[$key] = $value;
        
        if (stripos($key, 'firstname') !== false || stripos($key, 'lastname') !== false) {
            $profile[$key] = htmlspecialchars_decode(html_entity_decode(stripslashes(desanitize($value))));
        }
    }
    
    $industries = array();
    $query = "SELECT industry FROM recommender_industries 
              WHERE recommender = '". $_POST['id']. "'";
    $result = $mysqli->query($query);
    foreach ($result as $row) {
        $industries['industry'][] = array(0 => $row['industry']);
    }
    
    if (!empty($industries) && !is_null($industries)) {
        $profile['industries'] = $industries;
    }
    
    $response =  array('profile' => $profile);

    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'update_profile') {
    $recommender = new Recommender($_POST['id']);
    
    $data = array();
    $data['firstname'] = sanitize($_POST['firstname']);
    $data['lastname'] = sanitize($_POST['lastname']);
    $data['phone_num'] = $_POST['phone_num'];
    $data['remarks'] = sanitize($_POST['remarks']);
    $data['region'] = sanitize($_POST['region']);
    
    if (!$recommender->update($data)) {
        echo '-1'; // failed to update new recommender
        exit();
    }
    
    // update the industries
    $query = "DELETE FROM recommender_industries WHERE recommender = '". $_POST['id']. "'";
    $mysqli = Database::connect();
    $mysqli->execute($query);
    if ($_POST['industries'] != '0') {
        $industries = explode(',', $_POST['industries']);
        if (!$recommender->add_to_industries($industries)) {
            echo '-2'; // failed to update industries
            exit();
        }
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'add_new_recommender') {
    $recommender_industries_adding_error = false;
    
    $mysqli = Database::connect();
    $added_on = today();
    
    // check recommender
    $recommender = new Recommender($_POST['email_addr']);
    
    // create recommender
    $query = "SELECT COUNT(*) AS id_used FROM recommenders WHERE email_addr = '". $_POST['email_addr']. "'";
    $result = $mysqli->query($query);
    if ($result[0]['id_used'] != '0') {
        echo '-1';  // recommender already exists
        exit();        
    }
    
    $data = array();
    $data['firstname'] = sanitize($_POST['firstname']);
    $data['lastname'] = sanitize($_POST['lastname']);
    $data['phone_num'] = $_POST['phone_num'];
    $data['remarks'] = sanitize($_POST['remarks']);
    $data['region'] = sanitize($_POST['region']);
    $data['added_by'] = $_POST['id'];
    $data['added_on'] = $added_on;
    
    if ($recommender->create($data)) {
        $industries = explode(',', $_POST['industries']);
        if (!$recommender->add_to_industries($industries)) {
            $recommender_industries_adding_error = true;
        }
    } else {
        echo '-2'; // failed to create new recommender
        exit();
    }
    
    if ($recommender_industries_adding_error) {
        echo '-3';
    } else {
        echo '0';
    }
    exit();
}

if ($_POST['action'] == 'get_filters') {
    $mysqli = Database::connect();
    $query = "SELECT DISTINCT industries.id, industries.industry 
              FROM recommender_industries 
              LEFT JOIN industries ON industries.id = recommender_industries.industry 
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

if ($_POST['action'] == 'get_candidates') {
    $order_by = '';
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $recommender = new Recommender($_POST['id']);
    $result = $recommender->get_recommended_candidates($order_by);
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }
    
    if (!$result) {
        echo 'ko';
        exit();
    }
    
    foreach($result as $i=>$row) {
        $result[$i]['member'] = htmlspecialchars_decode(html_entity_decode(stripslashes(desanitize($row['member']))));
    }
    
    $response = array('candidates' => array('candidate' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_recommender_industries') {
    $mysqli = Database::connect();
    $query = "SELECT industries.industry 
              FROM recommender_industries 
              LEFT JOIN industries ON industries.id = recommender_industries.industry 
              WHERE recommender_industries.recommender = '". $_POST['id']. "' 
              ORDER BY industries.industry";
    $result = $mysqli->query($query);
    
    if (is_null($result) || empty($result)) {
        echo '0';
        exit();
    }
    
    $industries = array();
    foreach ($result as $row) {
        $industries[] = array($row['industry']);
    }
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('industries' => array('industry' => $industries)));
    exit();
    
}

if ($_POST['action'] == 'send_email_to_list') {
    $message = sanitize($_POST['message']);
    $subject = sanitize($_POST['subject']);
    $recommender_email_addrs = explode(',', $_POST['emails']);
    
    $mysqli = Database::connect();
    $query = "SELECT email_addr, CONCAT(firstname, ' ', lastname) AS employee 
              FROM employees WHERE id = ". $_POST['id']. " LIMIT 1";
    $result = $mysqli->query($query);
    $headers = 'From: '. $result[0]['employee']. ' <'. $result[0]['email_addr']. '>' . "\n";
    
    foreach ($recommender_email_addrs as $recommender_email_addr) {
        $recommender = new Recommender($recommender_email_addr);
        
        $message = str_replace('%recommender%', htmlspecialchars_decode(desanitize($recommender->get_name())), $message);
        $message = str_replace('%recommender_email_address%', $recommender->id(), $message);
        
        mail($recommender->id(), $subject, $message, $headers);
                    
        // $handle = fopen('/tmp/email_to_'. $recommender->id(). '.txt', 'w');
        // fwrite($handle, 'Subject: '. $subject. "\n\n");
        // fwrite($handle, 'Header: '. $headers. "\n\n");
        // fwrite($handle, $message);
        // fclose($handle);
    }
    
    echo '0';
    exit();
}
?>
