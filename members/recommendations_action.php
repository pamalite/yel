<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id'])) {
    echo "ko";
    exit();
    //redirect_to('login.php');
}

$xml_dom = new XMLDOM();

if (!isset($_POST['action'])) {
    redirect_to('job_applications.php');
}

if ($_POST['action'] == 'get_recommendations') {
    $order_by = 'referred_on desc';

    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $member = new Member($_POST['id']);
    $result = $member->getReferrals($order_by);
    foreach ($result as $i=>$row) {
        $result[$i]['candidate_name'] = htmlspecialchars_decode(stripslashes($row['candidate_name']));
        $result[$i]['job'] = htmlspecialchars_decode(stripslashes($row['job']));
        
        if (!is_null($row['alternate_employer']) && !empty($row['alternate_employer'])) {
            $result[$i]['employer'] = $row['alternate_employer'];
        }
        
        $result[$i]['employer'] = htmlspecialchars_decode(stripslashes($row['employer']));
    }
    
    $response = array(
        'recommendations' => array('recommendation' => $result)
    );
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    
    exit();
}

if ($_POST['action'] == 'delete_buffered') {
    $member = new Member();
    if ($member->deleteReferral($_POST['id']) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

?>