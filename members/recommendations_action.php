<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (isset($_GET['id']) && isset($_GET['hash'])) {
    // download resume
    $referral = new HeadhunterReferral($_GET['id']);
    $file_info = $referral->getFileInfo();
    $file = $GLOBALS['headhunter_resume_dir']. "/". $_GET['id']. ".". $_GET['hash'];
    if (file_exists($file)) {
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Expires: -1');
        header('Content-Description: File Transfer');
        header('Content-Length: ' . $file_info['file_size']);
        header('Content-Disposition: attachment; filename="' . $file_info['file_name'].'"');
        header('Content-type: '. $file_info['file_type']);
        ob_clean();
        flush();
        readfile($file);
    } else {
        redirect_to('headhunter_recommendations.php');
    }
    exit();
}

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

if ($_POST['action'] == 'get_hh_recommendations') {
    $order_by = 'referred_on desc';

    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $criteria = array(
        'columns' => "headhunter_referrals.*, jobs.alternate_employer, 
                      employers.name AS employer, jobs.title AS job_title,
                      DATE_FORMAT(headhunter_referrals.referred_on, '%e %b, %Y') AS formatted_referred_on, 
                      DATE_FORMAT(headhunter_referrals.employer_agreed_on, '%e %b, %Y') AS formatted_agreed_on,
                      DATE_FORMAT(headhunter_referrals.employer_rejected_on, '%e %b, %Y') AS formatted_rejected_on, 
                      DATE_FORMAT(headhunter_referrals.interview_scheduled_on, '%e %b, %Y') AS formatted_scheduled_on",
        'joins' => "jobs ON jobs.id = headhunter_referrals.job, 
                    employers ON employers.id = jobs.employer", 
        'match' => "headhunter_referrals.member = '". $_POST['id']. "'",
        'order' => $order_by
    );
    
    $referrals = new HeadhunterReferral();
    $result = $referrals->find($criteria);
    foreach ($result as $i=>$row) {
        $result[$i]['job_title'] = htmlspecialchars_decode(stripslashes($row['job_title']));
        
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
?>