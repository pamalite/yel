<?php
require_once dirname(__FILE__)."/../private/lib/utilities.php";
require_once dirname(__FILE__)."/../private/lib/classes/pages/employee_member_page.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/employees/member.php');
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

$member_id = '';
$is_new = false;
if (isset($_GET['member_email_addr'])) {
    $member_id = $_GET['member_email_addr'];
} else {
    redirect_to('members.php');
} 

if (empty($member_id)) {
    $is_new = true;
}

$section = 'profile';
if (isset($_POST['page'])) {
    $section = $_POST['page'];
} else if (isset($_GET['page'])) {
    $section = $_GET['page'];
}

$page = new EmployeeMemberPage($_SESSION['yel']['employee'], $member_id);

if (isset($_GET['error'])) {
    $page->set_error($_GET['error']);
}

$page->new_member($is_new);
$page->set_page($section);
$page->header(array('root_dir' => '../', 
                    'title' => 'Member'));
$page->insert_employee_member_css();
$page->insert_employee_member_scripts();
$page->insert_inline_scripts();
$page->show();
$page->footer();
?>
