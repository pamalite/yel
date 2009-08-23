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
    $order_by = 'employers.joined_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT employers.id, employers.name, employers.active, 
              DATE_FORMAT(employers.joined_on, '%e %b, %Y') AS formatted_joined_on, 
              DATE_FORMAT(employer_sessions.first_login, '%e %b, %Y') AS formatted_first_login 
              FROM employers 
              LEFT JOIN employer_sessions ON employer_sessions.employer = employers.id 
              WHERE employers.registered_by = ". $_POST['employee']. " 
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
        $result[$i]['employer'] = htmlspecialchars_decode($row['employer']);
    }
    
    $response = array('employers' => array('employer' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'deactivate') {
    if (!isset($_POST['payload'])) {
        echo "ko";
        exit();
    }
    
    $xml_dom->load_from_xml($_POST['payload']);
    $employers = $xml_dom->get('id');
    $query = "UPDATE employers SET active = 'N' WHERE id IN (";
    $i = 0;
    foreach ($employers as $employer) {
        $query .= "'". $employer->nodeValue. "'";
        
        if ($i < $employers->length-1) {
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

if ($_POST['action'] == 'activate') {
    $query = "UPDATE employers SET active = 'Y' 
              WHERE id = '". $_POST['id'] . "'";
    
    $mysqli = Database::connect();
    if (!$mysqli->execute($query)) {
        echo "ko";
        exit();
    }
    
    echo "ok";
    exit();
}

if ($_POST['action'] == 'get_employer') {
    $employer = new Employer($_POST['id']);
    $result = $employer->get();
    if (!$result) {
        echo "ko";
        exit();
    }
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('employer' => $result));
    exit();
}

if ($_POST['action'] == 'reset_password') {
    $new_password = generate_random_string_of(6);
    $data = array();
    $data['password'] = md5($new_password);
    $employer = new Employer($_POST['id']);
    if (!$employer->update($data, true)) {
        echo "ko";
        exit();
    }
    
    $query = "SELECT email_addr FROM employers WHERE id = '". $_POST['id']. "' LIMIT 1";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    $lines = file(dirname(__FILE__). '/../private/mail/employer_password_reset_admin.txt');
    $message = '';
    foreach($lines as $line) {
        $message .= $line;
    }
    
    $message = str_replace('%user_id%', $_POST['id'], $message);
    $message = str_replace('%temporary_password%', $new_password, $message);
    $subject = "Employer Password Reset";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($result[0]['email_addr'], $subject, $message, $headers);
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'save_profile') {
    $mode = 'update';
    if ($_POST['id'] == '0') {
        $mode = 'create';
    }
    
    $query = "SELECT branch FROM employees WHERE id = ". $_POST['employee']. " LIMIT 1";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    $branch = 0;
    if ($result !== false) {
        $branch = $result[0]['branch'];
    }
    
    $data = array();
    $data['license_num'] = $_POST['license_num'];
    $data['name'] = $_POST['name'];
    $data['phone_num'] = $_POST['phone_num'];
    $data['email_addr'] = $_POST['email_addr'];
    $data['contact_person'] = $_POST['contact_person'];
    $data['address'] = $_POST['address'];
    $data['state'] = $_POST['state'];
    $data['zip'] = $_POST['zip'];
    $data['country'] = $_POST['country'];
    $data['working_months'] = $_POST['working_months'];
    //$data['bonus_months'] = $_POST['bonus_months'];
    $data['payment_terms_days'] = $_POST['payment_terms_days'];
    $data['branch'] = $branch;
    
    $data['website_url'] = $_POST['website_url'];
    if (substr($_POST['website_url'], 0, 4) != 'http') {
        $data['website_url'] = 'http://'. $_POST['website_url'];
    }
    
    if ($mode == 'update') {
        $employer = new Employer($_POST['id']);
        if (!$employer->update($data)) {
            echo 'ko';
            exit();
        }
    } else {
        $employer = new Employer($_POST['user_id']);
        $data['password'] = md5($_POST['password']);
        $data['registered_by'] = $_POST['employee'];
        $data['registered_through'] = 'M';
        $data['joined_on'] = now();
        if (!$employer->create($data)) {
            echo 'ko';
            exit();
        }
        
        $lines = file(dirname(__FILE__). '/../private/mail/employer_welcome.txt');
        $message = '';
        foreach($lines as $line) {
            $message .= $line;
        }

        $message = str_replace('%company%', $_POST['name'], $message);
        $message = str_replace('%user_id%', $_POST['user_id'], $message);
        $message = str_replace('%temporary_password%', $_POST['password'], $message);
        $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
        $message = str_replace('%root%', $GLOBALS['root'], $message);
        $subject = "Welcome To Yellow Elevator!";
        $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
        mail($_POST['email_addr'], $subject, $message, $headers);
        
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'copy_fees_and_extras') {
    $from_employer = new Employer($_POST['employer']);
    $to_employer = new Employer($_POST['id']);
    
    $fees = $from_employer->get_fees();
    $extras = $from_employer->get_extras();
    
    if (!$to_employer->create_fees($fees)) {
        echo 'ko';
        exit();
    }
    
    if (!$to_employer->create_extras($extras)) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_fees') {
    $employer = new Employer($_POST['id']);
    $result = $employer->get_fees();
    
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }
    
    if (!$result) {
        echo 'ko';
        exit();
    }
    
    foreach ($result as $i=>$row) {
        $result[$i]['salary_start'] = number_format($row['salary_start'], 2, '.' , ', ');
        $result[$i]['salary_end'] = number_format($row['salary_end'], 2, '.' , ', ');
    }
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('fees' => array('fee' => $result)));
    exit();
}

if ($_POST['action'] == 'delete_fees') {
    if (!isset($_POST['payload'])) {
        echo "ko";
        exit();
    }
    
    $xml_dom->load_from_xml($_POST['payload']);
    $fees = $xml_dom->get('id');
    $query = "DELETE FROM employer_fees WHERE id IN (";
    $i = 0;
    foreach ($fees as $fee) {
        $query .= "'". $fee->nodeValue. "'";
        
        if ($i < $fees->length-1) {
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

if ($_POST['action'] == 'get_fee') {
    $query = "SELECT * FROM employer_fees WHERE id = ". $_POST['id']. " LIMIT 1";
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
    echo $xml_dom->get_xml_from_array(array('fee' => $result));
    exit();
}

if ($_POST['action'] == 'save_service_fee') {
    if (isset($_POST['salary_range_check'])) {
        $query = "SELECT COUNT(*) AS overlapped FROM employer_fees 
                  WHERE employer = '". $_POST['employer']. "' AND 
                  id <> ". $_POST['id']. " AND 
                  ((salary_start = ". $_POST['salary_start']. " OR salary_end = ". $_POST['salary_start']. ") OR
                  (salary_start = ". $_POST['salary_end']. " OR salary_end = ". $_POST['salary_end']. ") OR 
                  (salary_start < ". $_POST['salary_start']. " AND (salary_end > ". $_POST['salary_start']. " OR salary_end = 0)) OR
                  (salary_start < ". $_POST['salary_end']. " AND (salary_end > ". $_POST['salary_end']. " OR salary_end = 0)))";
        $mysqli = Database::connect();
        $result = $mysqli->query($query);

        if ($result[0]['overlapped'] != 0) {
            echo '-1';
            exit();
        }
    }
    
    $data = array();
    $data['id'] = $_POST['id'];
    $data['guarantee_months'] = $_POST['guarantee_months'];
    $data['discount'] = $_POST['discount'];
    $data['service_fee'] = $_POST['service_fee'];
    $data['reward_percentage'] = $_POST['reward_percentage'];
    $data['premier_fee'] = '0.00';
    
    if (isset($_POST['salary_range_check'])) {
        $data['salary_start'] = $_POST['salary_start'];
        $data['salary_end'] = $_POST['salary_end'];
    }
    
    $employer = new Employer($_POST['employer']);
    if ($_POST['id'] == '0') {
        if (!$employer->create_fee($data)) {
            echo 'ko';
            exit();
        }
    } else {
        if (!$employer->update_fee($data)) {
            echo 'ko';
            exit();
        }
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_charges') {
    $employer = new Employer($_POST['id']);
    $result = $employer->get_extras();
    
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }
    
    if (!$result) {
        echo 'ko';
        exit();
    }
    
    foreach ($result as $i=>$row) {
        $result[$i]['charges'] = number_format($row['charges'], 2, '.' , ', ');
    }
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('extras' => array('extra' => $result)));
    exit();
}

if ($_POST['action'] == 'delete_charges') {
    if (!isset($_POST['payload'])) {
        echo "ko";
        exit();
    }
    
    $xml_dom->load_from_xml($_POST['payload']);
    $extras = $xml_dom->get('id');
    $query = "DELETE FROM employer_extras WHERE id IN (";
    $i = 0;
    foreach ($extras as $extra) {
        $query .= "'". $extra->nodeValue. "'";
        
        if ($i < $extras->length-1) {
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

if ($_POST['action'] == 'get_charge') {
    $query = "SELECT * FROM employer_extras WHERE id = ". $_POST['id']. " LIMIT 1";
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
    echo $xml_dom->get_xml_from_array(array('extra' => $result));
    exit();
}

if ($_POST['action'] == 'save_extra_charge') {
    $data = array();
    $data['id'] = $_POST['id'];
    $data['label'] = $_POST['label'];
    $data['charges'] = number_format($_POST['charges'], 2);
    
    $employer = new Employer($_POST['employer']);
    if ($_POST['id'] == '0') {
        if (!$employer->create_extra($data)) {
            echo 'ko';
            exit();
        }
    } else {
        if (!$employer->update_extra($data)) {
            echo 'ko';
            exit();
        }
    }
    
    echo 'ok';
    exit();
}

?>
