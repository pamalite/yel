<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/employee_home_page.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/employees/home.php');
        exit();
    }
}

if (!isset($_SESSION['yel']['employee']) || 
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

$section = 'applications';
if (isset($_POST['page'])) {
    $section = $_POST['page'];
} else if (isset($_GET['page'])) {
    $section = $_GET['page'];
}

$error_code = '';
if (isset($_GET['error'])) {
    $error_code = $_GET['error'];
}

$home = new EmployeeHomePage($_SESSION['yel']['employee']);
$home->header(array('root_dir' => '../', 
                     'title' => 'Employee Home'));
$home->insert_employee_home_css();
$home->insert_employee_home_scripts();
$home->insert_inline_scripts();
$home->show();
$home->footer();
?>
