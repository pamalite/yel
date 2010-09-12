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

if (isset($_GET['hash'])) {
    // get from ReferralBuffer
    $criteria = array(
        "columns" => "resume_file_name, resume_file_type, resume_file_size", 
        "match" => "id = ". $_GET['id'], 
        "limit" => "1"
    );
    $buffer = new ReferralBuffer();
    $result = $buffer->find($criteria);
    
    if (is_null($result) || count($result) <= 0 || $result === false) {
        echo 'No record of resume.';
        exit();
    }
    
    if (!file_exists($GLOBALS['buffered_resume_dir']. '/'. $_GET['id']. '.'. $_GET['hash'])) {
        echo 'Resume file not found.';
        exit();
    }
    
    header('Content-type: '. $result[0]['resume_file_type']);
    header('Content-Disposition: attachment; filename="'. $result[0]['resume_file_name'].'"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-length: '. $result[0]['resume_file_size']);
    ob_clean();
    flush();
    readfile($GLOBALS['buffered_resume_dir']. '/'. $_GET['id']. '.'. $_GET['hash']);
    exit();
} else {
    // get from Resume
    redirect_to('resume_download.php?id='. $_GET['id']);
}

?>