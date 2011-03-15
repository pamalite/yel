<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/employee_employer_page.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/employees/employer.php');
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

$employer_id = '';
$is_new = false;
if (isset($_POST['id'])) {
    $employer_id = $_POST['id'];
} else if (isset($_GET['id'])) {
    $employer_id = $_GET['id'];
} else {
    redirect_to('employers.php');
}

if (empty($employer_id)) {
    if (isset($_POST['from_employer']) || isset($_GET['from_employer'])) {
        $employer_id = (isset($_POST['from_employer'])) ? $_POST['from_employer'] : $_GET['from_employer'];
    }
    
    $is_new = true;
}

$section = 'profile';
if (isset($_POST['page'])) {
    $section = $_POST['page'];
} else if (isset($_GET['page'])) {
    $section = $_GET['page'];
}

$page = new EmployeeEmployerPage($_SESSION['yel']['employee'], $employer_id);
$page->new_employer($is_new);
$page->set_page($section);
$page->header(array('root_dir' => '../', 
                    'title' => 'Employer'));
$page->insert_employee_employer_css();
$page->insert_employee_employer_scripts();
$page->insert_inline_scripts();
$page->show();
$page->footer();
?>
