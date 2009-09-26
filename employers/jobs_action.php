<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id']) || !isset($_POST['closed'])) {
    echo "ko";
    exit();
    //redirect_to('login.php');
}

$xml_dom = new XMLDOM();
$order_by = 'created_on desc';
$closed = 'jobs.closed <> \'Y\'';

if (isset($_POST['order_by'])) {
    $order_by = $_POST['order_by'];
}

if (isset($_POST['closed'])) {
    if ($_POST['closed'] <> 'N') {
        $closed = 'jobs.closed = \'Y\'';
    }
}

$criteria = array(
    'columns' => 'jobs.id, industries.industry AS industry, jobs.title, jobs.closed, 
                  DATE_FORMAT(jobs.created_on, \'%e %b, %Y\') AS created_on, 
                  DATE_FORMAT(jobs.expire_on, \'%e %b, %Y\') AS expire_on',
    'joins' => 'industries ON industries.id = jobs.industry', 
    'order' => $order_by,
    'match' => $closed. ' AND jobs.employer = \''. $_POST['id']. '\''
);

$jobs = Job::find($criteria);

foreach ($jobs as $i=>$job) {
    $jobs[$i]['is_referred'] = 'N';
}

// Check whether the job has already employments
$today = now();
//$today = '0000-00-00 00:00:00'; // use this to temporarily bypass the date
$query = "SELECT DISTINCT jobs.id 
          FROM referrals 
          LEFT JOIN jobs ON jobs.id = referrals.job 
          WHERE jobs.employer = '". $_POST['id']. "' AND 
          (jobs.expire_on >= '". $today. "' OR jobs.closed = 'N') AND 
          (referrals.employed_on IS NOT NULL AND referrals.employed_on <> '0000-00-00 00:00:00') AND 
          referrals.employer_removed_on IS NULL AND 
          (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00')";
$mysqli = Database::connect();
$result = $mysqli->query($query);

if ($result !== false) {
    foreach ($jobs as $i=>$job) {
        foreach ($result as $id) {
            if ($job['id'] == $id['id']) {
                $jobs[$i]['is_referred'] = 'Y';
                break;
            }
        }
    }
}

$response = array(
    'jobs' => array('job' => $jobs)
);

header('Content-type: text/xml');
echo $xml_dom->get_xml_from_array($response);
?>
