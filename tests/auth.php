<?php
//if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
    if (!isset($_SERVER['PHP_AUTH_USER'])) {
        header('WWW-Authenticate: Basic realm="My Realm"');
        header('HTTP/1.0 401 Unauthorized');
        exit();
    } else {
        $username = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];
        $type = $_SERVER['PHP_AUTH_TYPE'];

        echo $username. "<br />";
        echo $password. "<br />";
        echo $type. "<br />";
    }
//} else {
//    echo "Not secure.";
//}
?>