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
    
    $query = "SELECT recommenders.email_addr, recommenders.phone_num, 
              CONCAT(recommenders.firstname, ', ', recommenders.lastname) AS recommender_name, 
              DATE_FORMAT(recommenders.added_on, '%e %b, %Y') AS formatted_added_on 
              FROM recommenders ";
    if ($_POST['filter_by'] == '0') {
        $query .= "WHERE recommenders.added_by = '". $_POST['id']. "'";
    } else {
        $query .= "LEFT JOIN recommender_industries ON recommender_industries.recommender = recommenders.email_addr 
                   WHERE recommenders.added_by = '". $_POST['id']. "' AND 
                   recommender_industries.industry = ". $_POST['filter_by'];
    }
    $query .= " ORDER BY ". $_POST['order_by'];
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
    }
    
    $response = array('recommenders' => array('recommender' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_profile') {
    $query = "SELECT email_addr, firstname, lastname, phone_num, 
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
    $data['firstname'] = $_POST['firstname'];
    $data['lastname'] = $_POST['lastname'];
    $data['phone_num'] = $_POST['phone_num'];
    $data['added_by'] = $_POST['id'];
    $data['added_on'] = $joined_on;
    
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
?>
