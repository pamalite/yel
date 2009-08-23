<?php
require_once "../../private/lib/utilities.php";

$mysqli = Database::connect();

echo "Simple query test with cross database: <br>";
echo "<pre>";
if ($result = $mysqli->query("SELECT * FROM career_category", "yel")) {
    $records = array();
    $i = 0;
    foreach ($result as $row) {
        $records[$i] = array();
        foreach ($row as $column => $field) {
            $records[$i][$column] = $field;
        }
        $i++;
    }
    print_r($records);
} else {
    $error = $mysqli->error();
    echo $error['errno']. ": ". $error['error'];
}
echo "</pre><br><br>";

echo "Stored Procedure call test with cross database: <br>";
echo "<pre>";
if ($result = $mysqli->call("testProc", array("helloworld"), "yel")) {
    $records = array();
    $i = 0;
    foreach ($result as $row) {
        $records[$i] = array();
        foreach ($row as $column => $field) {
            $records[$i][$column] = $field;
        }
        $i++;
    }
    print_r($records);
} else {
    $error = $mysqli->error();
    echo $error['errno']. ": ". $error['error'];
}
echo "</pre><br><br>";

echo "multi-query test with cross database: <br>";
echo "<pre>";
$query = "SELECT NOW();";
$query .= "SELECT * FROM career_category";
if ($result =$mysqli->query_all($query, "yel")) {
    $record_sets = array();
    foreach ($result as $record) {
        $records = array();
        $i = 0;
        foreach ($record as $row) {
            $records[$i] = array();
            foreach ($row as $column => $field) {
                $records[$i][$column] = $field;
            }
            $i++;
        }
        $record_sets[] = $records;
    }
    print_r($record_sets);
} else {
    $error = $mysqli->error();
    echo $error['errno']. ": ". $error['error'];
}
echo "</pre><br><br>";

echo "transact test with last insert id turned on and cross database: <br>";
echo "<pre>";
$query = "SELECT NOW();";
$query .= "SELECT * FROM career_category";
if (!$insert_id = $mysqli->transact($query, true, "yel")) {
    echo "Successful";
} else {
    echo "returned should be zero as no AUTO_INCREMENT was used";
    $error = $mysqli->error();
    echo $error['errno']. ": ". $error['error'];
}
echo "</pre><br><br>";
?>