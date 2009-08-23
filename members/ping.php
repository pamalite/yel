<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

/*
    Return OK if the id and password provided match. 
    Return 401 if the id and password provided do not match.
    Return 401 & insecure if this page is being called from non-SSL.
*/
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
    if (isset($_SERVER['PHP_AUTH_USER'])) {
        $id = $_SERVER['PHP_AUTH_USER'];
        $password = md5($_SERVER['PHP_AUTH_PW']);

        $mysqli = Database::connect();
        if (Member::simple_authenticate($mysqli, $id, $password)) {
            header('HTTP/1.0 200 OK');
            exit();
        }
    }
    
    header('WWW-Authenticate: Basic realm="Yellow Elevator"');
    header('HTTP/1.0 401 Unauthorized');
    exit();
} else {
    header('HTTP/1.0 401 Unauthorized');
    echo 'insecure';
    exit();
}

?>