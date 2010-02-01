<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/prs_recommenders_page.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/prs/recommenders.php');
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

$home = new PrsRecommendersPage($_SESSION['yel']['employee']);
$home->header(array('root_dir' => '../', 
                    'title' => 'Recommenders'));
$home->insert_prs_recommenders_css();
$home->insert_prs_recommenders_scripts();
$home->insert_inline_scripts();
$home->show();
$home->footer();
?>
