<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id'])) {
    redirect_to('status.php');
}

if (!isset($_POST['action'])) {
    redirect_to('status.php');
}

$xml_dom = new XMLDOM();

if ($_POST['action'] == 'get_applications') {
    $period = explode(';', $_POST['period']);
    $match = "referrals.referred_on BETWEEN '". $period[0]. "' AND '". $period[1]. "' ";
    
    if (!empty($_POST['filter'])) {
        switch ($_POST['filter']) {
            case 'employed':
                $match = "referrals.employed_on BETWEEN '". $period[0]. "' AND '". $period[1]. "' AND 
                          (referrals.employed_on IS NOT NULL AND referrals.employed_on <> '0000-00-00') AND 
                          (referrals.employer_rejected_on IS NULL OR referrals.employer_rejected_on = '0000-00-00') AND 
                          (referrals.employer_removed_on IS NULL OR referrals.employer_removed_on = '0000-00-00')";
                break;
            case 'rejected':
                $match = "referrals.employer_rejected_on BETWEEN '". $period[0]. "' AND '". $period[1]. "' AND 
                          (referrals.employer_rejected_on IS NOT NULL AND referrals.employer_rejected_on <> '0000-00-00')";
                break;
            case 'removed':
                $match = "referrals.employer_deleted_on BETWEEN '". $period[0]. "' AND '". $period[1]. "' AND 
                          (referrals.employer_removed_on IS NOT NULL AND referrals.employer_removed_on <> '0000-00-00')";
                break;
            case 'viewed':
                $match = "referrals.employer_agreed_terms_on BETWEEN '". $period[0]. "' AND '". $period[1]. "' AND 
                          (referrals.employer_agreed_terms_on IS NOT NULL AND referrals.employer_agreed_terms_on <> '0000-00-00') AND 
                           (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00') AND 
                           (referrals.employer_rejected_on IS NULL OR referrals.employer_rejected_on = '0000-00-00') AND 
                           (referrals.employer_removed_on IS NULL OR referrals.employer_removed_on = '0000-00-00')";
                break;
            case 'not_viewed':
                $match .= "AND (referrals.employer_agreed_terms_on IS NULL OR referrals.employer_agreed_terms_on = '0000-00-00') AND 
                           (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00') AND 
                           (referrals.employer_rejected_on IS NULL OR referrals.employer_rejected_on = '0000-00-00') AND 
                           (referrals.employer_removed_on IS NULL OR referrals.employer_removed_on = '0000-00-00')";
                break;
        }
    }
    
    $limit = "0, 20";
    if (!empty($_POST['page']) || $_POST['page'] > 0) {
        $offset = ($_POST['page'] + 20) - 1;
        $limit = $offset. ", 20";
    }
    
    $criteria = array(
        'columns' => "referrals.id, referrals.member AS referrer, 
                      jobs.title AS job, jobs.id AS job_id, 
                      employers.name AS employer, employers.id AS employer_id, 
                      referrals.resume AS resume_id, resumes.file_name, 
                      CONCAT(members.lastname, ', ', members.firstname) AS referrer_name, 
                      DATE_FORMAT(referrals.referred_on, '%e %b, %Y') AS formatted_referred_on, 
                      DATE_FORMAT(referrals.employer_agreed_terms_on, '%e %b, %Y') AS formatted_employer_agreed_terms_on, 
                      DATE_FORMAT(referrals.employer_rejected_on, '%e %b, %Y') AS formatted_employer_rejected_on, 
                      DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on, 
                      DATE_FORMAT(referrals.employer_removed_on, '%e %b, %Y') AS formatted_employer_removed_on, 
                      IF(referrals.testimony IS NULL OR referrals.testimony = '', '0', '1') AS has_testimony, 
                      IF(referrals.employer_remarks IS NULL OR referrals.employer_remarks = '', '0', '1') AS has_employer_remarks", 
        'joins' => "members ON members.email_addr = referrals.member, 
                    jobs ON jobs.id = referrals.job, 
                    employers ON employers.id = jobs.employer, 
                    resumes ON resumes.id = referrals.resume", 
        'match' => $match,
        'order' => $_POST['order_by']
    );
    
    $referral = new Referral();
    $result = $referral->find($criteria);
    
    if (empty($result) || count($result) <= 0) {
        echo '0';
        exit();
    }
    
    $total_pages = ceil(count($result) / 20);
    $criteria['limit'] = $limit;
    $result = $referral->find($criteria);
    foreach($result as $i=>$row) {
        foreach($row as $col=>$value) {
            $result[$i][$col] = htmlspecialchars_decode(stripslashes($value));
        }
    }
    
    header('Content-type: text/xml');
    $response = array(
        'pagination' => array(
            'total_pages' => $total_pages,
            'current_page' => $_POST['page']
        ),
        'application' => $result
    );
    echo $xml_dom->get_xml_from_array(array('applications' => $response));
    exit();
}

if ($_POST['action'] == 'get_testimony') {
    $criteria = array(
        'columns' => "testimony", 
        'match' => "id = ". $_POST['id'], 
        'limit' => "1"
    );
    
    $referral = new Referral();
    $result = $referral->find($criteria);
    $testimony = htmlspecialchars_decode(str_replace("\n", '<br/>', $result[0]['testimony']));
    
    echo $testimony;
    exit();
}

if ($_POST['action'] == 'get_job_desc') {
    $criteria = array(
        'columns' => "description", 
        'match' => "id = ". $_POST['id'], 
        'limit' => "1"
    );
    
    $job = new Job();
    $result = $job->find($criteria);
    $job_desc = htmlspecialchars_decode(str_replace("\n", '<br/>', $result[0]['description']));
    
    echo $job_desc;
    exit();
}

if ($_POST['action'] == 'get_employer_remarks') {
    $criteria = array(
        'columns' => "employer_remarks", 
        'match' => "id = ". $_POST['id'], 
        'limit' => "1"
    );
    
    $referral = new Referral();
    $result = $referral->find($criteria);
    $remarks = str_replace("\n", '<br/>', stripslashes($result[0]['employer_remarks']));
    
    echo $remarks;
    exit();
}
?>