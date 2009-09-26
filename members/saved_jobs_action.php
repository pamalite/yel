<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id'])) {
    echo "ko";
    exit();
    //redirect_to('login.php');
}

$xml_dom = new XMLDOM();
$order_by = 'member_saved_jobs.saved_on desc';

if (isset($_POST['order_by'])) {
    $order_by = $_POST['order_by'];
}

if (!isset($_POST['action'])) {
    $member = new Member($_POST['id'], $_SESSION['yel']['member']['sid']);
    $result = $member->get_saved_jobs($order_by);
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }
    
    if (!$result) {
        echo 'ko';
        exit();
    }
    
    foreach ($result as $key=>$row) {
        $result[$key]['description'] = htmlspecialchars_decode($row['description']);
        $result[$key]['potential_reward'] = number_format($row['potential_reward'], 2, '.', ', ');
    }
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('saved_jobs' => array('saved_job' => $result)));
    exit();
}

if ($_POST['action'] == 'remove_from_saved_jobs') {
    $member = new Member($_POST['id'], $_SESSION['yel']['member']['sid']);
    $xml_dom->load_from_xml($_POST['payload']);
    $jobs = $xml_dom->get('id');
    foreach ($jobs as $id) {
        if (!$member->remove_from_saved_jobs($id->nodeValue)) {
            echo "ko";
            exit();
        }
    }
    
    echo "ok";
    exit();
}

if ($_POST['action'] == 'get_job_title') {
    $job = new Job($_POST['id']);
    $result = $job->get();
    
    echo htmlspecialchars_decode($result[0]['title']);
    exit();
}
?>
