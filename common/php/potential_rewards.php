<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

header('Content-type: text/xml');
$xml_dom = new XMLDOM ();
$result = Job::find(array(
        'columns' => 'SUM(potential_reward) AS sumReward, employers.country AS country_code, currencies.symbol AS currency', 
        'joins' => 'employers on employers.id = jobs.employer,
                    currencies ON currencies.country_code = employers.country', 
        'match' => 'jobs.closed = \'N\' AND jobs.expire_on >= NOW()', 
        //'match' => 'jobs.closed = \'N\'', 
        'group' => 'employers.country', 
        'order' => 'sumReward'
    )
);

$response = array();
$i = 0;
foreach ($result as $row) {
    if ($row['sumReward'] != '0') {
        $response[$i] = $row;
        $i++;
    }
}

/*$response = Job::find(array(
        'columns' => 'SUM(potential_reward) AS sumReward, jobs.currency, currencies.country_code', 
        'joins' => 'currencies ON currencies.symbol = jobs.currency', 
        'match' => 'jobs.closed = \'N\'', 
        'group' => 'jobs.currency', 
        'order' => 'sumReward'
    )
);*/

// format the rewards
foreach ($response as $i => $row) {
    $response[$i]['sumReward'] = number_format($row['sumReward'], 2, ".", ", ");
}

$xml_array = array('rewards' => array('potential' => $response));
echo $xml_dom->get_xml_from_array($xml_array);

?>