<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id'])) {
    echo "ko";
    exit();
}

$xml_dom = new XMLDOM();

if (!isset($_POST['action'])) {
    redirect_to('jobs.php');
}

if ($_POST['action'] == 'get_jobs') {
    $order_by = "created_on DESC";
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $job = new Job();
    
    $criteria = array(
        'columns' => "id, title, 
                      DATE_FORMAT(expire_on, '%e %b, %Y') AS formatted_expire_on, 
                      DATE_FORMAT(created_on, '%e %b, %Y') AS formatted_created_on", 
        'match' => "employer = '". $_POST['id']. "' AND deleted = FALSE",
        'order' => $order_by
    );
    
    $result = $job->find($criteria);
    if ($result === false) {
        echo 'ko';
        exit();
    }
    
    if (is_null($result) || empty($result)) {
        echo '0';
        exit();
    }
    
    $response = array('jobs' => array('job' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_job_desc') {
    $job = new Job();
    
    $criteria = array(
        'columns' => "jobs.title, jobs.state, jobs.salary, jobs.salary_end, jobs.salary_negotiable, 
                      industries.industry, jobs.description, jobs.contact_carbon_copy, 
                      jobs.alternate_employer, 
                      DATE_FORMAT(expire_on, '%e %b, %Y') AS formatted_expire_on, 
                      DATE_FORMAT(created_on, '%e %b, %Y') AS formatted_created_on", 
        'joins' => "industries ON industries.id = jobs.industry", 
        'match' => "jobs.id = ". $_POST['id']
    );
    
    $result = $job->find($criteria);
    foreach ($result[0] as $key=>$value) {
        if ($key == 'description') {
            $result[0][$key] = htmlspecialchars_decode(desanitize($value));
        }
        
        if ($key == 'salary' || $key == 'salary_end') {
            $result[0][$key] = number_format($value, 2, '.', ',');
            
            if (is_null($value) || empty($value) || $value <= 0) {
                $result[0][$key] = null;
            }
        }
    }
    
    $response = array('job' => $result[0]);
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}
?>