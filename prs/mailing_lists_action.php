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
    $order_by = 'candidates_mailing_lists.created_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT candidates_mailing_lists.id, candidates_mailing_lists.label, 
              COUNT(candidate_email_manifests.email_addr) AS number_of_candidates, 
              CONCAT(employees.firstname, ', ', employees.lastname) AS employee_name, 
              DATE_FORMAT(candidates_mailing_lists.created_on, '%e %b, %Y') AS formatted_created_on 
              FROM candidates_mailing_lists 
              LEFT JOIN employees ON employees.id = candidates_mailing_lists.created_by  
              LEFT JOIN candidate_email_manifests ON candidate_email_manifests.mailing_list = candidates_mailing_lists.id 
              GROUP BY candidates_mailing_lists.id
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
        $result[$i]['employee_name'] = htmlspecialchars_decode(html_entity_decode(stripslashes(desanitize($row['employee_name']))));
    }
    
    $response = array('mailing_lists' => array('mailing_list' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'add_mailing_list') {
    $mysqli = Database::connect();
    $query = "INSERT INTO candidates_mailing_lists SET 
              label = '". sanitize($_POST['label']). "', 
              created_on = NOW(), 
              created_by = '". $_POST['id']. "'";
    if ($mysqli->execute($query)) {
        echo '0';
    } else {
        echo 'ko';
    }
    
    exit();
}

if ($_POST['action'] == 'remove_mailing_list') {
    $mysqli = Database::connect();
    $query = "DELETE FROM candidate_email_manifests WHERE mailing_list = ". $_POST['id'];
    if ($mysqli->execute($query)) {
        $query = "DELETE FROM candidates_mailing_lists WHERE id = ". $_POST['id'];
        if ($mysqli->execute($query)) {
            echo '0';
        } else {
            echo 'ko';
        }
    } else {
        echo 'ko';
    }
    
    exit();
}

if ($_POST['action'] == 'rename_mailing_list') {
    $mysqli = Database::connect();
    $query = "UPDATE candidates_mailing_lists SET 
              label = '". sanitize($_POST['label']). "' 
              WHERE id = ". $_POST['id'];
    if ($mysqli->execute($query)) {
        echo '0';
    } else {
        echo 'ko';
    }
    
    exit();
}

if ($_POST['action'] == 'get_candidates') {
    $mysqli = Database::connect();
    $query = "SELECT members.email_addr, members.phone_num, members.added_by, 
              CONCAT(members.firstname, ', ', members.lastname) AS candidate_name, 
              DATE_FORMAT(members.joined_on, '%e %b, %Y') AS formatted_joined_on 
              FROM candidate_email_manifests 
              LEFT JOIN members ON members.email_addr = candidate_email_manifests.email_addr 
              WHERE candidate_email_manifests.mailing_list = ". $_POST['id'];
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
        $result[$i]['candidate_name'] = htmlspecialchars_decode(html_entity_decode(stripslashes(desanitize($row['candidate_name']))));
    }
    
    $response = array('candidates' => array('candidate' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'remove_candidate') {
    $mysqli = Database::connect();
    $query = "DELETE FROM candidate_email_manifests 
              WHERE mailing_list = ". $_POST['id']. " AND 
              email_addr = '". $_POST['candidate']. "'";
    if ($mysqli->execute($query)) {
        echo '0';
    } else {
        echo 'ko';
    }
    
    exit();
}
?>
