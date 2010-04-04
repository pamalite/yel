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

if ($_POST['action'] == 'get_applications') {
    $order_by = 'referrals.referred_on desc';

    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $member = new Member($_POST['id']);
    $referral = new Referral();
    
    $criteria = array(
        'columns' => "referrals.id, referrals.job AS job_id, jobs.alternate_employer, 
                      employers.name AS employer, jobs.title AS job, 
                      resumes.file_name AS `resume`, referrals.`resume` AS resume_id, 
                      referrals.employer_agreed_terms_on, referrals.employed_on, 
                      DATE_FORMAT(referrals.referred_on, '%e %b, %Y') AS formatted_referred_on, 
                      DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on, 
                      DATE_FORMAT(referrals.referee_confirmed_hired_on, '%e %b, %Y') AS formatted_confirmed_on", 
        'joins' => "resumes ON resumes.id = referrals.`resume`, 
                    jobs ON jobs.id = referrals.job, 
                    employers ON employers.id = jobs.employer", 
        'match' => "referrals.referee = '". $member->getId(). "'",
        'order' => $order_by
    );
    
    $result = $referral->find($criteria);
    foreach ($result as $i=>$row) {
        if (!is_null($row['alternate_employer']) && !empty($row['alternate_employer'])) {
            $result[$i]['employer'] = $row['alternate_employer'];
        }
        
        $result[$i]['status'] = '<span style="font-weight: bold; color: #000000;">New</span>';
        if (!is_null($row['employer_agreed_terms_on']) && 
            $row['employer_agreed_terms_on'] != '0000-00-00 00:00:00') {
            $result[$i]['status'] = '<span style="font-weight: bold; color: #0000FF;">Viewed</span>';
        }
        
        if (!is_null($row['employed_on']) && 
            $row['employed_on'] != '0000-00-00 00:00:00') {
            $result[$i]['status'] = '<span style="font-weight: bold; color: #00FF00;">Employed</span>';
        }
    }
    
    $response = array(
        'applications' => array('application' => $result)
    );
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    
    exit();
}

if ($_POST['action'] == 'confirm_employment') {
    $referral = new Referral($_POST['id']);
    
    $data = array();
    $data['referee_confirmed_hired_on'] = now();
    
    if ($referral->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

?>