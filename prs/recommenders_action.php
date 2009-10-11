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
    $order_by = 'added_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT email_addr, phone_num, 
              CONCAT(firstname, ', ', lastname) AS recommender_name, 
              DATE_FORMAT(added_on, '%e %b, %Y') AS formatted_added_on 
              FROM recommenders 
              WHERE added_by = ". $_POST['id']. " 
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
        $result[$i]['recommender_name'] = htmlspecialchars_decode($row['recommender_name']);
        $industries = array();
        $query = "SELECT industries.industry 
                  FROM recommender_industries 
                  LEFT JOIN industries ON industries.id = recommender_industries.industry 
                  WHERE recommender_industries.recommender = '". $row['email_addr']. "'";
        $industries = $mysqli->query($query);
        if (!empty($industries) && !is_null($industries)) {
            $result[$i]['industries'] = array('industry' => $industries);
        }
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
            $profile[$key] = htmlspecialchars_decode(html_entity_decode(desanitize($value)));
        }
    }
    
    $industries = array();
    $query = "SELECT industry FROM recommender_industries 
              WHERE recommender = '". $_POST['id']. "'";
    $industries = $mysqli->query($query);
    if (!empty($industries) && !is_null($industries)) {
        $profile['industries'] = array('industry' => $industries);
    }
    
    $response =  array('profile' => $profile);

    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'update_profile') {
    $recommender = new Recommender($_POST['email_addr']);
    
    $data = array();
    $data['firstname'] = $_POST['firstname'];
    $data['lastname'] = $_POST['lastname'];
    $data['phone_num'] = $_POST['phone_num'];
    
    if (!$recommender->update($data)) {
        echo '-1'; // failed to update new recommender
        exit();
    }
    
    // update the industries
    $industries = explode(',', $_POST['industries']);
    $query = "DELETE FROM recommender_industries WHERE recommender = '". $_POST['id']. "'";
    $mysqli->execute($query);
    if (!$recommender->add_to_industries($industries)) {
        echo '-2'; // failed to update industries
        exit();
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
?>
