<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

$xml_dom = new XMLDOM();

if (!isset($_POST['action'])) {
    $resume_search = new ResumeSearch();

    $criteria = array();
    $criteria['order_by'] = 'relevance desc';
    $criteria['industry'] = 0;
    $criteria['country_code'] = '';
    $criteria['limit'] = $GLOBALS['default_results_per_page'];
    $criteria['offset'] = 0;
    $criteria['keywords'] = $_POST['keywords'];
    
    $_SESSION['yel']['prs']['resume_search']['criteria'] = array();
    $_SESSION['yel']['prs']['resume_search']['criteria']['order_by'] = 'relevance desc';
    $_SESSION['yel']['prs']['resume_search']['criteria']['industry'] = 0;
    $_SESSION['yel']['prs']['resume_search']['criteria']['country_code'] = '';
    $_SESSION['yel']['prs']['resume_search']['criteria']['limit'] = $GLOBALS['default_results_per_page'];
    $_SESSION['yel']['prs']['resume_search']['criteria']['offset'] = 0;
    $_SESSION['yel']['prs']['resume_search']['criteria']['keywords'] = $_POST['keywords'];
    
    if (isset($_POST['order_by'])) {
        $criteria['order_by'] = $_POST['order_by'];
        $_SESSION['yel']['prs']['resume_search']['criteria']['order_by'] = $_POST['order_by'];
    }

    if (isset($_POST['industry'])) {
        $criteria['industry'] = $_POST['industry'];
        $_SESSION['yel']['prs']['resume_search']['criteria']['industry'] = $_POST['industry'];
    }
    
    if (isset($_POST['country_code'])) {
        $criteria['country_code'] = $_POST['country_code'];
        $_SESSION['yel']['prs']['resume_search']['criteria']['country_code'] = $_POST['country_code'];
    }

    if (isset($_POST['limit'])) {
        $criteria['limit'] = $_POST['limit'];
        $_SESSION['yel']['prs']['resume_search']['criteria']['limit'] = $_POST['limit'];
    }

    if (isset($_POST['offset'])) {
        $criteria['offset'] = $_POST['offset'];
        $_SESSION['yel']['prs']['resume_search']['criteria']['offset'] = $_POST['offset'];
    }
    
    $result = $resume_search->search_using($criteria);
    if ($result == 0) {
        echo "0";
        exit();
    }

    if (!$result) {
        echo "ko";
        exit();
    }
    
    $total_results = $resume_search->total_results();
    $current_page = '1';
    if ($criteria['offset'] > 0) {
        $current_page = ceil($criteria['offset'] / $criteria['limit']) + 1;
    }
    
    $result[0]['changed_country_code'] = 0;
    if ($resume_search->country_code_changed()) {
        $result[0]['changed_country_code'] = 1;
    } 
    
    foreach($result as $i=>$row) {
        $result[$i]['member'] = htmlspecialchars_decode(html_entity_decode(stripslashes(desanitize($row['member']))));
        $result[$i]['total_results'] = $total_results;
        $result[$i]['current_page'] = $current_page;
        
        if (is_null($result[$i]['added_by']) || empty($result[$i]['added_by'])) {
            $result[$i]['added_by'] = '-1';
        }
        
        if (is_null($result[$i]['zip']) || empty($result[$i]['zip'])) {
            $result[$i]['zip'] = '0';
        }
        
        if (is_null($result[$i]['prime_industry']) || empty($result[$i]['prime_industry'])) {
            $result[$i]['prime_industry'] = 'N/A';
        }
        
        if (is_null($result[$i]['second_industry']) || empty($result[$i]['second_industry'])) {
            $result[$i]['second_industry'] = 'N/A';
        }
    }

    $response = array('results' => array('result' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_available_industries') {
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
    
    $industries = array();
    foreach ($result as $i=>$row) {
        $industries[$i]['id'] = $row['id'];
        $industries[$i]['industry_name'] = $row['industry'];
    }
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('industries' => array('industry' => $industries)));
    exit();
}

if ($_POST['action'] == 'get_available_countries') {
    $mysqli = Database::connect();
    $query = "SELECT DISTINCT countries.country_code AS id, countries.country 
              FROM members 
              LEFT JOIN countries ON countries.country_code = members.country 
              ORDER BY countries.country";
    $result = $mysqli->query($query);
    
    $countries = array();
    foreach ($result as $i=>$row) {
        $countries[$i]['country_code'] = $row['id'];
        $countries[$i]['country_name'] = $row['country'];
    }
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('countries' => array('country' => $countries)));
    exit();
}
?>
