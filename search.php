<?php
session_start();

require_once "private/lib/utilities.php";
require_once "private/lib/classes/pages/search_page.php";

// 1. Check whether session has been initialized
if (!isset($_SESSION['yel'])) {
    redirect_to('index.php');
}

// 1.5 Check whether a new search session had been initiated
$criteria = array();
if (isset($_POST['industry']) && isset($_POST['keywords']) && isset($_POST['employer'])) {
    $criteria = $_POST;
    $_SESSION['yel']['job_search']['criteria'] = $_POST;
} else if (isset($_GET['industry']) && isset($_GET['keywords']) && isset($_GET['employer'])) {
    $criteria = $_GET;
    $_SESSION['yel']['job_search']['criteria'] = $_GET;
} else if (isset($_GET['special'])) {
    $criteria['industry'] = 0;
    $criteria['employer'] = '';
    $criteria['keywords'] = '';
    $criteria['is_local'] = 1;
    $criteria['country_code'] = $_GET['country'];
    
    switch ($_GET['special']) {
        case 'salary':
            $criteria['special'] = 'salary';
            switch ($_GET['range']) {
                case 0:
                    $criteria['salary'] = 8001;
                    break;
                case 1:
                    $criteria['salary'] = 7001;
                    $criteria['salary_end'] = 8000;
                    break;
                case 2:
                    $criteria['salary'] = 6001;
                    $criteria['salary_end'] = 7000;
                    break;
                case 3:
                    $criteria['salary'] = 5001;
                    $criteria['salary_end'] = 6000;
                    break;
                case 4:
                    $criteria['salary'] = 4001;
                    $criteria['salary_end'] = 5000;
                    break;
                default:
                    $criteria['salary'] = 3001;
                    $criteria['salary_end'] = 4000;
                    break;
            }
            break;
        default:
            $criteria['special'] = $_GET['special'];
            break;
    }
    
    $_SESSION['yel']['job_search']['criteria'] = $criteria;
} else {
    $criteria = $_SESSION['yel']['job_search']['criteria'];
}

// 2. Generate page
$search = '';
if (isset($_SESSION['yel']['member']) && 
    !empty($_SESSION['yel']['member']['id']) && 
    !empty($_SESSION['yel']['member']['sid']) && 
    !empty($_SESSION['yel']['member']['hash'])) {
    $search = new SearchPage($_SESSION['yel']['member'], $criteria);
} else {
    $search = new SearchPage(NULL, $criteria);
}
$search->header(array('title' => 'Searched Jobs'));
$search->insert_search_css();
$search->insert_search_scripts();
$search->insert_inline_scripts();
$search->show();
$search->footer();
?>