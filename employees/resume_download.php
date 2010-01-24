<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        if (isset($GET['id'])) {
            redirect_to('https://'. $GLOBALS['root']. '/employees/resume.php?id='. $_GET['id']);
        } else {
            redirect_to('https://'. $GLOBALS['root']. '/employees/resume.php?job_id='. $_GET['job_id']. '&candidate_email='. $_GET['candidate_email']. '&referrer_email='. $_GET['referrer_email']);
        }
        exit();
    }
}

if (!isset($_SESSION['yel']['employee']) || 
    empty($_SESSION['yel']['employee']['id']) || 
    empty($_SESSION['yel']['employee']['sid']) || 
    empty($_SESSION['yel']['employee']['hash'])) {
    echo "An illegal attempt to view resume has been detected.";
    exit();
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

$resume = new Resume(0, $_GET['id']);
$cover = $resume->get();

if (!is_null($cover[0]['file_name'])) {
    $file = $resume->get_file();
    
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Expires: -1');
    header('Content-Description: File Transfer');
    header('Content-Length: ' . $file['size']);
    header('Content-Disposition: attachment; filename="' . $file['name'].'"');
    header('Content-type: '. $file['type']);
    ob_clean();
    flush();
    readfile($GLOBALS['resume_dir']. "/". $_GET['id']. ".". $file['hash']);
} else {
    redirect_to('https://'. $GLOBALS['root']. '/employees/resume.php?id='. $_GET['id']);
}

exit();
?>
