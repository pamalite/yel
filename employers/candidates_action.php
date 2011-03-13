<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";
require_once dirname(__FILE__). "/../private/lib/classes/member_search.php";

session_start();

date_default_timezone_set('Asia/Kuala_Lumpur');

if (!isset($_POST['id'])) {
    redirect_to('candidates.php');
}

if (!isset($_POST['action'])) {
    redirect_to('candidates.php');
}

$xml_dom = new XMLDOM();

if ($_POST['action'] == 'get_members') {
    $member_search = new MemberSearch();
    $order_by = "members.updated_on DESC";
    $page = 1;
    
    $criteria = array();
    if (!empty($_POST['position'])) {
        $criteria['position'] = $_POST['position'];
    }
    
    if (!empty($_POST['employer'])) {
        $criteria['employer'] = $_POST['employer'];
    }
    
    if (!empty($_POST['total_work_years']) && $_POST['total_work_years'] >= 1) {
        $criteria['total_work_years'] = $_POST['total_work_years'];
    }
    
    if (!empty($_POST['specialization']) && $_POST['specialization'] >= 1) {
        $criteria['specialization'] = $_POST['specialization'];
    }
    
    if (!empty($_POST['emp_desc']) && $_POST['emp_desc'] >= 1) {
        $criteria['emp_desc'] = $_POST['emp_desc'];
    }
    
    if (!empty($_POST['seeking'])) {
        $criteria['seeking_keywords'] = $_POST['seeking'];
    }
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $criteria['order_by'] = $order_by;
    $criteria['limit'] = 0;
    $limit = $GLOBALS['default_results_per_page'];
    
    if (isset($_POST['page'])) {
        $page = $_POST['page'];
    }
    
    $offset = 0;
    if ($page > 1) {
        $offset = ($page-1) * $limit;
        $offset = ($offset < 0) ? 0 : $offset;
    }
    $criteria['offset'] = 0;
    
    $result = $member_search->search_using($criteria);
    // echo $result;
    // exit();
    if (is_null($result) || count($result) <= 0) {
        echo '0';
        exit();
    }

    if ($result === false) {
        echo 'ko';
        exit();
    }
    
    // get the job_profiles
    $members_str = '';
    foreach ($result as $i=>$row) {
        $members_str .= "'". $row['email_addr']. "'";
        
        if ($i < count($result)-1) {
            $members_str .= ", ";
        }
    }
    
    $sub_criteria = array(
        'columns' => "member_job_profiles.id, member_job_profiles.member, 
                      member_job_profiles.position_title, member_job_profiles.employer, 
                      date_format(member_job_profiles.work_from, '%b, %Y') AS formatted_work_from, 
                      date_format(member_job_profiles.work_to, '%b, %Y') AS formatted_work_to", 
        'joins' => "member_job_profiles ON member_job_profiles.member = members.email_addr", 
        'match' => "member_job_profiles.member IS NOT NULL AND 
                    members.email_addr IN (". $members_str. ")", 
        'order' => "member_job_profiles.member, member_job_profiles.work_from DESC"
    );
    
    $member = new Member();
    $sub_result = $member->find($sub_criteria);
    
    // remove all the contacts
    // - basically reconstruct the results
    $results = array();
    foreach ($result as $i=>$row) {
        $id = '0';
        $job_profiles = array();
        foreach ($sub_result as $j=>$sub_row) {
            if ($sub_row['member'] == $row['email_addr']) {
                $job_profile = array();
                $job_profile['position_title'] = htmlspecialchars(stripslashes($sub_row['position_title']));
                $job_profile['employer'] = htmlspecialchars(stripslashes($sub_row['employer']));
                $job_profile['from'] = $sub_row['formatted_work_from'];
                $job_profile['to'] = $sub_row['formatted_work_to'];
                
                $job_profiles[$j] = $job_profile;
                
                if ($id == '0') {
                    $id = $sub_row['id'];
                }
            }
        }
        
        // remove candidates that do not have job profiles
        // - we use the job profiles ID to reverse search for email address
        if ($id != '0') {
            $results[$i]['first_job_profile_id'] = $id;
            $results[$i]['updated_on'] = $row['formatted_updated_on'];
            $results[$i]['seeking'] = htmlspecialchars(stripslashes($row['seeking']));
            $results[$i]['seeking'] = str_replace(array("\n", "\r", "\r\n"), '<br/>', $results[$i]['seeking']);
            $results[$i]['job_profiles'] = array('job_profile' => $job_profiles);
        }
    }
    
    // manually paginate
    $output = array();
    for ($i=$offset; $i < ($offset + $limit); $i++) {
        if (isset($results[$i])) {
            $output[] = $results[$i];
        }
    }
        
    $response = array(
        'members' => array(
            // 'total_pages' => ceil($member_search->total_results() / $criteria['limit']),
            'total_pages' => ceil(count($results) / $limit),
            'member' => $output
        )
    );
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}
?>