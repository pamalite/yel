<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id'])) {
    echo "ko";
    exit();
    //redirect_to('login.php');
}

$xml_dom = new XMLDOM();
$order_by = 'referred_on desc';
$filter_by = '0';

if (isset($_POST['order_by'])) {
    $order_by = $_POST['order_by'];
}

if (isset($_POST['filter_by'])) {
    $filter_by = $_POST['filter_by'];
}

if (!isset($_POST['action'])) {
    $member = new Member($_POST['id'], $_SESSION['yel']['member']['sid']);
    $result = $member->get_referees($order_by, $filter_by);
    foreach ($result as $key=>$row) {
        $result[$key]['networks'] = '';
        $result[$key]['network_ids'] = '';
    }

    $networks = $member->get_networks();
    $mysqli = Database::connect();
    foreach ($networks as $network) {
        $query = "SELECT referee FROM member_networks_referees WHERE network = ". $network['id'];
        $referees = $mysqli->query($query);
        foreach ($referees as $referee) {
            foreach ($result as $key=>$row) {
                if ($row['id'] == $referee['referee']) {
                    $result[$key]['networks'] .= $network['industry']. ';';
                    $result[$key]['network_ids'] .= $network['id']. ';';
                }
            }
        }
    } 

    $response = array(
        'candidates' => array('candidate' => $result)
    );

    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'find') {
    $result_order_by = 'joined_on desc';
    $match = '';
    $suffix = " AND email_addr NOT IN (SELECT referee FROM member_referees WHERE member = '". $_POST['id']. "') 
                AND email_addr <> '". $_POST['id']. "'";
    
    if (isset($_POST['result_order_by'])) {
        $result_order_by = $_POST['result_order_by'];
    }
    
    if (isset($_POST['using']) && isset($_POST['criteria'])) {
        $_POST['criteria'] = stripslashes($_POST['criteria']);
        switch($_POST['using']) {
            case 'email_addr':
                $match = 'email_addr = \''. $_POST['criteria']. '\'';
                break;
            case 'lastname':
                $match = 'lastname LIKE \'%'. $_POST['criteria']. '%\'';
                break;
            default:
                $match = 'firstname LIKE \'%'. $_POST['criteria']. '%\'';
                break;
        }
        $match .= $suffix;
    } else {
        echo 'ko';
        exit();
    }
    $criteria = array(
        'columns' => "email_addr, CONCAT(lastname, ', ', firstname) AS name, DATE_FORMAT(joined_on, '%e %b, %Y') AS joined_date",
        'order' => $result_order_by,
        'match' => $match. " AND email_addr NOT LIKE 'team.%@yellowelevator.com'"
    );
    
    $result = Member::find($criteria);
    
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    } 
    
    if ($result === false) {
        echo 'ko';
        exit();
    }
    
    // Randomly mask the username part of an email address.
    // This is to comply of company's privacy policy. 
    foreach ($result as $i=>$row) {
        $position_of_alias = 0;
        $email = $row['email_addr'];
        for ($j = 0; $j < strlen($email); $j++) {
            if (substr($email, $j, 1) == '@') {
                $position_of_alias = $j;
                break;
            }
        }
        
        $username = substr($email, 0, $position_of_alias);
        $length = strlen($username);
        $min_length = round($length / 3);
        $mask_length = mt_rand($min_length, $length);
        $offset = mt_rand(1, ($length-1));

        $masked_username = substr_replace($username, ' ... ', $offset, $mask_length);
        $result[$i]['masked_email_addr'] = $masked_username. substr($email, $position_of_alias);
    }
    
    $response = array('candidates' => array('candidate' => $result));
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_hide_banner') {
    $query = "SELECT pref_value FROM member_banners 
              WHERE member = '". $_POST['id']. "' AND pref_key = 'hide_contacts_banner' LIMIT 1";
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
              WHERE member = '". $_POST['id']. "' AND pref_key = 'hide_contacts_banner' LIMIT 1";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if ($result[0]['id'] > 0) {
        $query = "UPDATE member_banners SET pref_value = '". $_POST['hide']. "' WHERE id = ". $result[0]['id'];
    } else {
        $query = "INSERT INTO member_banners SET 
                  id = 0,
                  pref_key = 'hide_contacts_banner', 
                  pref_value = '". $_POST['hide']. "',
                  member = '". $_POST['id']. "'";
    }
    
    $mysqli->execute($query);
    
    exit();
}
?>
