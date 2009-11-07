<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

function replace_characters($_description) {
    // Strip newline characters.
    $_description = str_replace(chr(10), " ", $_description);
    $_description = str_replace(chr(13), " ", $_description);
    // Replace single quotes.
    $_description = str_replace(chr(145), chr(39), $_description);
    $_description = str_replace(chr(146), chr(39), $_description);
    // Return the result.
    return $_description;
}

session_start();

if (!isset($_POST['id'])) {
    echo "ko";
    exit();
    //redirect_to('login.php');
}

$xml_dom = new XMLDOM();

if (!isset($_POST['action'])) {
    $order_by = 'num_expired desc';
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT employers.id, employers.name, 
              (SELECT COUNT(jobs.id) FROM jobs 
               WHERE (jobs.expire_on < CURDATE()) AND 
               jobs.employer = employers.id 
              ) AS num_expired 
              FROM employers 
              GROUP BY employers.id 
              ORDER BY ". $order_by;
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }
    
    foreach ($result as $i=>$row) {
        $result[$i]['name'] = htmlspecialchars_decode(desanitize($row['name']));
    }
    
    $xml_dom = new XMLDOM();
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('employers' => array('employer' => $result)));
    exit();
}

if ($_POST['action'] == 'get_jobs') {
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
    
    $response = array(
        'jobs' => array('job' => $jobs)
    );
    
    $xml_dom = new XMLDOM();
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_job') {
    $criteria = array(
        'columns' => 'jobs.*, countries.country AS country_name, industries.industry AS full_industry, 
                      DATE_FORMAT(jobs.created_on, \'%e %b, %Y %k:%i:%s\') AS formatted_created_on, 
                      DATE_FORMAT(jobs.expire_on, \'%e %b, %Y %k:%i:%s\') AS formatted_expire_on, 
                      DATEDIFF(NOW(), jobs.expire_on) AS expired',
        'joins' => 'industries ON industries.id = jobs.industry, 
                    countries ON countries.country_code = jobs.country', 
        'match' => 'jobs.id = \''. $_POST['id']. '\''
    );

    $jobs = Job::find($criteria);
    $job = array();

    foreach ($jobs[0] as $key => $value) {
        $job[$key] = $value;
        
        if ($key == 'description') {
            $job[$key] = htmlspecialchars_decode(html_entity_decode(desanitize($value)));
            $job[$key] = replace_characters($job[$key]);
        } else if ($key == 'title') {
            $job[$key] = html_entity_decode(desanitize($value));
        }
    }

    $response =  array('job' => $job);

    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'publish') {
    $id = $_POST['job'];
    $job = '';
    
    if ($id <= 0) {
        $job = new Job();
    } else {
        $job = new Job($id);
    }
    
    $data = array();
    $data['employer'] = $_POST['employer'];
    $data['industry'] = $_POST['industry'];
    $data['country'] = $_POST['country'];
    $data['state'] = $_POST['state'];
    $data['currency'] = $_POST['currency'];
    $data['salary'] = $_POST['salary'];
    $data['salary_end'] = $_POST['salary_end'];
    $data['salary_negotiable'] = $_POST['salary_negotiable'];
    $data['created_on'] = now();
    $data['expire_on'] = sql_date_add($data['created_on'], 30, 'day');
    $data['title'] = $_POST['title'];
    //$data['description'] = str_replace(array("\r\n", "\r", "\n"), '<br/>', $_POST['description']);
    $data['description'] = $_POST['description'];
    $data['acceptable_resume_type'] = $_POST['resume_type'];
    $data['closed'] = 'N';
    
    $salary_end = $_POST['salary_end'];
    if ($salary_end <= 0) {
        $salary_end = $_POST['salary'];
        $data['salary_end'] = 'NULL';
    }
    $data['potential_reward'] = Job::calculate_potential_reward_from($salary_end, $_POST['employer']);
    
    // Check whether employer's account is ready.
    if ($data['potential_reward'] <= 0) {
        echo '-1';
        exit();
    }
    
    $new_id = 0;
    if ($id <= 0) {
        if (($new_id = $job->create($data)) === false) {
            echo "ko";
            exit();
        }
    } else {
        if ($job->update($data) == false) {
            echo "ko";
            exit();
        }
    }
    
    $tmp = explode('/', $GLOBALS['root']);
    $is_test_site = false;
    foreach ($tmp as $t) {
        if ($t == 'yel') {
            $is_test_site = true;
            break;
        }
    }
    
    // Tweet about this job, if it is new
    if ($new_id > 0 && !$is_test_site) {
        $query = "SELECT name FROM employers WHERE id = '". $_POST['employer']. "' LIMIT 1";
        $mysqli = Database::connect();
        $result = $mysqli->query($query);
        $employer = $result[0]['name'];
        $url = $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/job/'. $new_id;
        $status = $data['title']. ' ('. desanitize($employer). ') - '. $url;
        $twitter_username = 'yellowelevator';
        $twitter_password = 'yellow123456';
        $tweetUrl = 'http://www.twitter.com/statuses/update.xml';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $tweetUrl);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "status=". $status);
        curl_setopt($curl, CURLOPT_USERPWD, $twitter_username. ':'. $twitter_password);

        $result = curl_exec($curl);
        
        // Don't bother to check because if Twitter fails, it doesn't matter.
        //$resultArray = curl_getinfo($curl);
        //if ($resultArray['http_code'] != 200) {
        //    echo 'ko';
        //}

        curl_close($curl);
    }
    
    echo "ok";
    exit();
}

if ($_POST['action'] == 'close') {
    if (!isset($_POST['payload'])) {
        echo "ko";
        exit();
    }
    
    $xml_dom->load_from_xml($_POST['payload']);
    $jobs = $xml_dom->get('id');
    $query = "UPDATE jobs SET closed = 'Y' WHERE id IN (";
    $i = 0;
    foreach ($jobs as $job) {
        $query .= $job->nodeValue;
        
        if ($i < $jobs->length-1) {
            $query .= ", ";
        }
        
        $i++;
    }
    $query .= ")";
    
    $mysqli = Database::connect();
    
    if (!$mysqli->execute($query)) {
        echo "ko";
        exit();
    }
    
    echo "ok";
    exit();
}

if ($_POST['action'] == 'extend') {
    $mysqli = Database::connect();
    $query = "INSERT INTO job_extensions 
              SELECT 0, id, created_on, expire_on, for_replacement, invoiced FROM jobs WHERE id = ". $_POST['id'];
    if (!$mysqli->execute($query)) {
        echo "ko";
        exit();
    }
    
    $query = "SELECT expire_on 
              FROM jobs 
              WHERE id = ". $_POST['id']. " LIMIT 1";
    $result = $mysqli->query($query);
    $is_expired = (sql_date_diff($result[0]['expire_on'], now()) <= 0) ? true : false;
    $expire_on = $result[0]['expire_on'];
    if ($is_expired) {
        $expire_on = now();
    }
    
    $data = array();
    $data['created_on'] = $expire_on;
    $data['expire_on'] = sql_date_add($data['created_on'], 30, 'day');
    $data['closed'] = 'N';
    $job = new Job($_POST['id']);
    if ($job->update($data) == false) {
        echo "ko";
        exit();
    }
    
    echo "ok";
    exit();
}

if ($_POST['action'] == 'get_currency') {
    $employer = new Employer($_POST['id']);
    echo Currency::symbol_from_country_code($employer->get_country_code());
    exit();
}
?>
