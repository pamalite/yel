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
    
    $query = "SELECT DISTINCT members.email_addr AS email_addr, members.phone_num AS phone_num, 
              members.zip, countries.country, 
              CONCAT(members.firstname, ', ', members.lastname) AS member_name, 
              DATE_FORMAT(members.joined_on, '%e %b, %Y') AS formatted_joined_on 
              FROM members
              LEFT JOIN industries AS primary_industries ON primary_industries.id = members.primary_industry 
              LEFT JOIN industries AS secondary_industries ON secondary_industries.id = members.secondary_industry 
              LEFT JOIN countries ON countries.country_code = members.country 
              WHERE (members.added_by IS NULL OR members.added_by = '') AND 
              (members.zip IS NOT NULL AND members.zip <> '') ";
    
    if (!empty($filter)) {
        $query .= "AND (members.primary_industry = ". $filter. " OR members.secondary_industry = ". $filter. ") ";
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
    if (count($result) <= 0 || is_null($result)) {
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
    
    $response = array('members' => array('member' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_profile') {
    $query = "SELECT members.email_addr AS email_addr, members.phone_num AS phone_num, 
              members.firstname, members.lastname, 
              DATE_FORMAT(members.joined_on, '%e %b, %Y') AS formatted_joined_on, 
              primary_industries.industry AS first_industry, 
              secondary_industries.industry AS second_industry, 
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
?>
