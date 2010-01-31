<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

function highlight_keywords($_keyword_string, $_text, $_use_and_op = false) {
    $punctuations = array(",", ".", ";", "\"", "'", "(", ")", "[", "]", "{", "}", "<", ">", 
                          "|", "?", "!", "`", "~", "/", "\\", "&", "-");
    $_keyword_string = str_replace($punctuations, '', trim($_keyword_string));
    
    $red = 'FF';
    $green = 'FF';
    $blue = '00';
    
    if ($_use_and_op) {
        $non_characters = " ,.?!;\"'\\\/(){}<>\[\]~`\|\&-";
        $_keyword_string = str_replace(' ', '['. $non_characters. ']*', $_keyword_string);
        $color = '#'. $red. $green. $blue;
        $_text = preg_replace('|('. $_keyword_string. ')|Ui', 
                              '<span style="font-weight: bold; background-color: '. $color. ';">&nbsp;$1&nbsp;</span>', 
                              $_text);
    } else {
        
        $keywords = explode(' ', $_keyword_string);
        foreach ($keywords as $word) {
            $red = dechex(mt_rand(mt_rand(60, 100), mt_rand(200, 252)));
            $green = dechex(mt_rand(mt_rand(60, 100), mt_rand(200, 252)));
            $blue = dechex(mt_rand(mt_rand(60, 100), mt_rand(200, 252)));
            
            $bg_color = '#'. $red. $green. $blue;
            $font_color = '#000000';
            if (hexdec($red) <= 102 || 
                hexdec($green) <= 153 || 
                hexdec($blue) <= 153) {
                $font_color = '#FFFFFF';
            }
            
            $_text = preg_replace('|('. $word. ')|Ui', 
                                  '<span style="font-weight: bold; background-color: '. $bg_color. '; color: '. $font_color. ';">&nbsp;$1&nbsp;</span>', 
                                  $_text);
        }
    }
    
    return $_text;
}

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
    $criteria['use_exact'] = (isset($_POST['use_exact'])) ? true : false;
    $_SESSION['yel']['prs']['resume_search']['criteria'] = array();
    $_SESSION['yel']['prs']['resume_search']['criteria']['order_by'] = 'relevance desc';
    $_SESSION['yel']['prs']['resume_search']['criteria']['industry'] = 0;
    $_SESSION['yel']['prs']['resume_search']['criteria']['country_code'] = '';
    $_SESSION['yel']['prs']['resume_search']['criteria']['limit'] = $GLOBALS['default_results_per_page'];
    $_SESSION['yel']['prs']['resume_search']['criteria']['offset'] = 0;
    $_SESSION['yel']['prs']['resume_search']['criteria']['keywords'] = $_POST['keywords'];
    $_SESSION['yel']['prs']['resume_search']['criteria']['use_exact'] = $criteria['use_exact'];
    
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
        if ($criteria['use_exact']) {
            echo "01";
        } else {
            echo "0";
        }
        exit();
    }

    if (!$result) {
        if ($criteria['use_exact']) {
            echo "ko1";
        } else {
            echo "ko";
        }
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
        
        if ($criteria['use_exact']) {
            $result[$i]['use_exact'] = '1';
        } else {
            $result[$i]['use_exact'] = '0';
        }
        
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

if ($_POST['action'] == 'preview_resume') {
    $use_and_op = ($_POST['use_and'] == '1') ? true : false;
    
    $mysqli = Database::connect();
    $query = "SELECT cover_note, qualification, work_summary, 
              skill, technical_skill, file_text
              FROM resume_index 
              WHERE `resume` = ". $_POST['resume_id']. " LIMIT 1";
    $result = $mysqli->query($query);
    
    $preview_text = '';
    $preview_text .= (!empty($result[0]['cover_note']) && !is_null($result[0]['cover_note'])) ? $result[0]['cover_note']. "\n\n" : '';
    $preview_text .= (!empty($result[0]['qualification']) && !is_null($result[0]['qualification'])) ? $result[0]['qualification']. "\n\n" : '';
    $preview_text .= (!empty($result[0]['work_summary']) && !is_null($result[0]['work_summary'])) ? $result[0]['work_summary']. "\n\n" : '';
    $preview_text .= (!empty($result[0]['skill']) && !is_null($result[0]['skill'])) ? $result[0]['skill']. "\n\n" : '';
    $preview_text .= (!empty($result[0]['technical_skill']) && !is_null($result[0]['technical_skill'])) ? $result[0]['technical_skill']."\n\n" : '';
    $preview_text .= (!empty($result[0]['file_text']) && !is_null($result[0]['file_text'])) ? $result[0]['file_text'] : '';
    
    $preview_text = htmlspecialchars_decode(stripslashes($preview_text));
    $preview_text = highlight_keywords($_POST['keywords'], $preview_text, $use_and_op);
    $response = array('resume' => array('preview_text' => $preview_text));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_available_industries') {
    $mysqli = Database::connect();
    $query = "SELECT DISTINCT industries.id, industries.industry FROM 
              (SELECT email_addr, primary_industry AS industry 
               FROM members 
               WHERE primary_industry IS NOT NULL 
               UNION 
               SELECT email_addr, secondary_industry 
               FROM members 
               WHERE secondary_industry IS NOT NULL) AS member_industry 
              LEFT JOIN industries ON industries.id = member_industry.industry 
              LEFT JOIN resumes ON resumes.member = member_industry.email_addr 
              WHERE resumes.private = 'N' 
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
              LEFT JOIN resumes ON resumes.member = members.email_addr 
              WHERE resumes.private = 'N' 
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

if ($_POST['action'] == 'save_to_mailing_list') {    
    $mysqli = Database::connect();
    $mailing_list_id = $_POST['id'];
    
    if ($mailing_list_id == 'new') {
        $query = "INSERT INTO candidates_mailing_lists SET 
                  label = '". $_POST['label']. "', 
                  created_on = now(), 
                  created_by = ". $_POST['employee'];
        $mailing_list_id = $mysqli->execute($query, true);
    }
    
    if ($mailing_list_id > 0) {
        $query = "INSERT INTO candidate_email_manifests SET 
                  mailing_list = ". $mailing_list_id. ", 
                  email_addr = '". $_POST['candidate']. "'";
        if (!$mysqli->execute($query)) {
            echo '-1';
        } else {
            echo '0';
        }
    } else {
        echo 'ko';
    }
    
    exit();
}

if ($_POST['action'] == 'get_mailing_lists') {
    $query = "SELECT id, label FROM candidates_mailing_lists ORDER BY label";
    $mysqli = Database::connect();
    
    $result = $mysqli->query($query);
    if (is_null($result) || empty($result)) {
        echo '0';
        exit();
    }
    
    $lists = array();
    foreach ($result as $i=>$row) {
        $lists[$i]['id'] = $row['id'];
        
        if (is_null($row['label']) || empty($row['label'])) {
            $lists[$i]['label'] = 'Unlabeled';
        } else {
            $lists[$i]['label'] = $row['label'];
        }
    }
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('lists' => array('list' => $lists)));
    exit();
}
?>
