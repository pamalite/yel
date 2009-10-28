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
    $order_by = 'members.joined_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $mysqli = Database::connect();
    $query = "SELECT members.email_addr, members.phone_num, members.added_by, 
              CONCAT(members.firstname, ', ', members.lastname) AS candidate_name, 
              DATE_FORMAT(members.joined_on, '%e %b, %Y') AS formatted_joined_on 
              FROM candidate_email_manifests 
              LEFT JOIN members ON members.email_addr = candidate_email_manifests.email_addr 
              WHERE candidate_email_manifests.mailing_list = ". $_POST['id']. " 
              ORDER BY ". $order_by;
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

if ($_POST['action'] == 'send_email_to_list') {
    $message = sanitize($_POST['message']);
    
    $mysqli = Database::connect();
    
    $query = "SELECT email_addr, CONCAT(firstname, ', ', lastname) AS employee 
              FROM employees WHERE id = ". $_POST['employee']. " LIMIT 1";
    $result = $mysqli->query($query);
    $employee['email_addr'] = $result[0]['email_addr'];
    $employee['name'] = $result[0]['employee'];
    
    $query = "SELECT email_addr FROM candidate_email_manifests WHERE mailing_list = ". $_POST['id'];
    $result = $mysqli->query($query);
    
    foreach ($result as $row) {
        $email_addr = $row['email_addr'];
        $subject = "A Message From YellowElevator.com";
        $headers = 'From: '. $employee['name']. ' <'. $employee['email_addr']. '>' . "\n";
        mail($row['email_addr'], $subject, $message, $headers);
                    
        // $handle = fopen('/tmp/email_to_'. $row['email_addr']. '.txt', 'w');
        // fwrite($handle, 'Subject: '. $subject. "\n\n");
        // fwrite($handle, 'Header: '. $headers. "\n\n");
        // fwrite($handle, $message);
        // fclose($handle);
    }
    
    echo '0';
    exit();
}
?>
