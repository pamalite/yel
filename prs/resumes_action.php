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
    $query = "SELECT DISTINCT members.email_addr AS email_addr, members.phone_num AS phone_num, 
              CONCAT(members.firstname, ', ', members.lastname) AS member_name, 
              DATE_FORMAT(members.joined_on, '%e %b, %Y') AS formatted_joined_on 
              FROM members
              LEFT JOIN industries AS primary_industries ON primary_industries.id = members.primary_industry 
              LEFT JOIN industries AS secondary_industries ON secondary_industries.id = members.secondary_industry 
              WHERE (members.added_by IS NULL OR members.added_by = '') ";
    if (!empty($filter)) {
        $query .= "AND (members.primary_industry = ". $filter. " OR members.secondary_industry = ". $filter. ") ";
    }
    $query .= "ORDER BY ". $_POST['order_by'];
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
        $result[$i]['member_name'] = htmlspecialchars_decode(html_entity_decode(stripslashes(desanitize($row['member_name']))));
    }
    
    $response = array('members' => array('member' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_profile') {
    $query = "SELECT members.email_addr AS email_addr, members.phone_num AS phone_num, 
              members.firstname, members.lastname, 
              DATE_FORMAT(members.joined_on, '%e %b, %Y') AS formatted_joined_on, 
              primary_industries.industry AS primary_industry, 
              secondary_industries.industry AS secondary_industry, 
              countries.country, members.zip 
              FROM members 
              LEFT JOIN countries ON countries.country_code = members.country 
              LEFT JOIN industries AS primary_industries ON primary_industries.id = members.primary_industry 
              LEFT JOIN industries AS secondary_industries ON secondary_industries.id = members.secondary_industry 
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
               WHERE primary_industry IS NOT NULL 
               UNION 
               SELECT DISTINCT secondary_industry 
               FROM members 
               WHERE secondary_industry IS NOT NULL) AS primary_secondary_industry 
              LEFT JOIN industries ON industries.id = primary_secondary_industry.industry 
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
