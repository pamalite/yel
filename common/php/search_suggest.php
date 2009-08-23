<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$_POST['keywords'] = sanitize($_POST['keywords']);
if (!empty($_POST['keywords'])) {
    $criteria = array();
    $criteria['order_by'] = 'relevance desc';
    $criteria['industry'] = 0;
    $criteria['employer'] = '';
    $criteria['country_code'] = '';
    $criteria['limit'] = $GLOBALS['default_results_per_page'];
    $criteria['offset'] = 0;
    $criteria['keywords'] = $_POST['keywords'];

    if (isset($_POST['industry'])) {
        $criteria['industry'] = $_POST['industry'];
    }

    if (isset($_POST['employer'])) {
        $criteria['employer'] = $_POST['employer'];
    }

    $job_search = new JobSearch();
    $result = $job_search->search_using($criteria);
    if (count($result) <= 0 || is_null($result) || $result === false) {
        echo '';
        exit();
    }

    $output = array();
    foreach($result as $i=>$row) {
        $output[] = '"'. trim(htmlspecialchars_decode($row['title'])). '"';
    }

    echo '['. implode(',', $output). ']';
    exit();
} 

echo '[]';
exit();
?>
