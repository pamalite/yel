<?php
session_start();

require_once dirname(__FILE__). "/../private/lib/utilities.php";
require_once dirname(__FILE__). "/../private/lib/classes/pages/prs_resume_search_page.php";

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/prs/resumes_privileged.php');
        exit();
    }
}

if (!isset($_SESSION['yel']['employee']) || 
    empty($_SESSION['yel']['employee']['uid']) || 
    empty($_SESSION['yel']['employee']['id']) || 
    empty($_SESSION['yel']['employee']['sid']) || 
    empty($_SESSION['yel']['employee']['hash'])) {
    redirect_to('login.php');
}

if (isset($_SESSION['yel']['employee']['dev'])) {
    if ($_SESSION['yel']['employee']['dev'] === true) {
        $is_dev = false;
        $root_items = explode('/', $GLOBALS['root']);
        foreach ($root_items as $value) {
            if ($value == 'yel') {
                $is_dev = true;
                break;
            }
        }

        if (!$is_dev) {
            ?>
            <script type="text/javascript">alert('Please logout from your existing connection before proceeding.');</script>
            <?php
            exit();
        }
    }
}

// Check whether a new search session had been initiated
$criteria = array();
if (isset($_POST['industry']) && isset($_POST['keywords'])) {
    $criteria = $_POST;
    $_SESSION['yel']['prs']['resume_search']['criteria'] = $_POST;
} else if (isset($_GET['industry']) && isset($_GET['keywords'])) {
    $criteria = $_GET;
    $_SESSION['yel']['prs']['resume_search']['criteria'] = $_GET;
} else {
    $criteria = $_SESSION['yel']['prs']['resume_search']['criteria'];
}

// Generate page
$search = $search = new PrsResumeSearchPage($_SESSION['yel']['employee'], $criteria);
$search->header(array('title' => 'Resumes Search Results'));
$search->insert_resume_search_css();
$search->insert_resume_search_scripts();
$search->insert_inline_scripts();
$search->show();
$search->footer();
?>