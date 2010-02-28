<?php
require_once dirname(__FILE__). "/../config/common.inc";
require_once dirname(__FILE__). "/../config/stopwords.inc";
require_once "geoip.inc";
require_once "interfaces.php";
require_once "models.php";
require_once "classes.php";

function sanitize($_in = '') {
    $out = '';
    
    if (is_array($_in)) {
        foreach ($_in as $key => $data) {
            $_in[$key] = sanitize($data);
        }
        $out = $_in;
    } else {
        $out = addslashes(htmlspecialchars($_in));
    }
    
    return $out;
}

function desanitize($_in = '', $_is_htmlize = false) {
    $out = '';
    
    if (is_array($_in)) {
        foreach ($_in as $key => $data) {
            $_in[$key] = desanitize($data, $_is_htmlize);
        }
        $out = $_in;
    } else {
        $out = stripslashes($_in);
        
        if ($_is_htmlize) {
            $out = htmlspecialchars_decode($out);
        }
    }
    
    return $out;
}

function pad($_string, $_maxlength, $_pad_character) {
    $output = '';
    $delta = $_maxlength - strlen($_string);
    if ($delta <= 0) {
        return $_string;
    }
    
    for($i=0; $i < $delta; $i++) {
        $output .= $_pad_character;
    }
    
    return $output. $_string;
}

function generate_random_string_of($_length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $out = NULL;
    
    for ($i=0; $i < $_length; $i++) {
        $index = mt_rand(0, (strlen($characters)-1));
        $out .= substr($characters, $index, 1);
    }
    
    return $out;
}

function now() {
    return date('Y-m-d H:i:s');
}

function today() {
    return date('Y-m-d');
}

function timestamp() {
    return date('YmdHis');
}

function sql_date_add($_date, $_interval, $_unit) {
    $query = "SELECT DATE_ADD('". $_date. "', INTERVAL ". $_interval. " ". strtoupper($_unit). ") AS new_date";    
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    return $result[0]['new_date'];
}

function sql_date_diff($_date_1, $_date_2) {
    $query = "SELECT DATEDIFF('". $_date_1. "', '". $_date_2. "') AS delta";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    return $result[0]['delta'];
}

function sql_date_format($_date) {
    $query = "SELECT DATE_FORMAT('". $_date. "', '%e %b, %Y') AS formatted_date";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    return $result[0]['formatted_date'];
}

function redirect_to($_location) {
    header('Location: '. $_location);
    exit();
}

function initialize_session() {
    $_SESSION['yel'] = array();
    $_SESSION['yel']['country_code'] = "";
    
    $gi = geoip_open($GLOBALS['maxmind_geoip_data_file'], GEOIP_STANDARD);
    $_SESSION['yel']['country_code'] = geoip_country_code_by_addr($gi, $_SERVER['REMOTE_ADDR']);
    geoip_close($gi);
    
    if (empty($_SESSION['yel']['country_code']) || 
        is_null($_SESSION['yel']['country_code']) || 
        !Country::countryInUsed($_SESSION['yel']['country_code'])) {
        $_SESSION['yel']['country_code'] = $GLOBALS['default_country_code'];
    }
}

function set_session($_session_variables) {
    if (!isset($_SESSION['yel'])) {
        initialize_session();
    }
    
    foreach ($session_variables as $key => $value) {
        $_SESSION['yel'][$key] = $value;
    }
}

function log_activity($_message, $_file_name) {
    $log_file = '/var/log/'. $_file_name;
    
    $activity = timestamp(). ' '. $_message."\n";
    
    if ($handle = fopen($log_file, 'a')) {
        if (fwrite($handle, $activity) === FALSE) {
            $errmsg = $timestamp ." Cannot write to ". $logFile."\n";
            fwrite(STDERR, $activity);
            exit;
        }
    } else {
        $errmsg = $timestamp . " " . $log_file . " is not writable\n";
        fwrite(STDERR, $errmsg);
        exit;
    }
}

?>
