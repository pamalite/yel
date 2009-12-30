<?php
require_once dirname(__FILE__). "/../config/common.inc";
require_once "geoip.inc";
require_once "models.php";
require_once "classes.php";

function sanitize($in = "") {
    $out = "";
    
    if (is_array($in)) {
        foreach ($in as $key => $data) {
            $in[$key] = sanitize($data);
        }
        $out = $in;
    } else {
        $out = addslashes(htmlspecialchars($in));
    }
    
    return $out;
}

function desanitize($in = "") {
    $out = "";
    
    if (is_array($in)) {
        foreach ($in as $key => $data) {
            $in[$key] = desanitize($data);
        }
        $out = $in;
    } else {
        $out = stripslashes($in);
    }
    
    return $out;
}

function htmlize($in = "") {
    $out = "";
    
    if (is_array($in)) {
        foreach ($in as $key => $data) {
            $in[$key] = htmlize($data);
        }
        $out = $in;
    } else {
        $out = htmlspecialchars_decode($in);
    }
    
    return $out;
}

function initialize_session () {
    $_SESSION['yel'] = array();
    $_SESSION['yel']['country_code'] = "";
    
    $gi = geoip_open($GLOBALS['maxmind_geoip_data_file'], GEOIP_STANDARD);
    $_SESSION['yel']['country_code'] = geoip_country_code_by_addr($gi, $_SERVER['REMOTE_ADDR']);
    geoip_close($gi);
    
    if (empty($_SESSION['yel']['country_code']) || 
        is_null($_SESSION['yel']['country_code']) || 
        !Country::country_in_used($_SESSION['yel']['country_code'])) {
        $_SESSION['yel']['country_code'] = $GLOBALS['default_country_code'];
    }
}

function set_session ($session_variables) {
    if (!isset($_SESSION['yel'])) {
        initialize_session();
    }
    
    foreach ($session_variables as $key => $value) {
        $_SESSION['yel'][$key] = $value;
    }
}

function redirect_to($location) {
    header('Location: '. $location);
    exit();
}

function pad($string, $maxlength, $pad_character) {
    $output = '';
    $delta = $maxlength - strlen($string);
    if ($delta <= 0) {
        return $string;
    }
    
    for($i=0; $i < $delta; $i++) {
        $output .= $pad_character;
    }
    
    return $output. $string;
}

function generate_random_string_of($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    
    // list($usec, $sec) = explode(" ", microtime());
    // $seed = ((float) $sec + (float) $usec);
    // mt_srand($seed);
    $out = NULL;
    
    for ($i=0; $i < $length; $i++) {
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

function sql_date_add($date, $interval, $unit) {
    $query = "SELECT DATE_ADD('". $date. "', INTERVAL ". $interval. " ". strtoupper($unit). ") AS new_date";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    return $result[0]['new_date'];
}

function sql_date_diff($date_1, $date_2) {
    $query = "SELECT DATEDIFF('". $date_1. "', '". $date_2. "') AS delta";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    return $result[0]['delta'];
}

function sql_date_format($date) {
    $query = "SELECT DATE_FORMAT('". $date. "', '%e %b, %Y') AS formatted_date";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    return $result[0]['formatted_date'];
}

function log_activity($message, $file_name) {
    $log_file = '/var/log/'. $file_name;
    
    $timestamp = date("YmdHis");
    $activity = $timestamp. ' '. $message."\n";
    
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

/**
 * Send HTTP POST Request (from PayPal)
 *
 * @param   string  The API method name
 * @param   string  The POST Message fields in &name=value pair format
 * @return  array   Parsed HTTP Response body
 */
function PPHttpPost($methodName_, $nvpStr_) {
    $environment = $GLOBALS['paypal_environment'];

    // Set up your API credentials, PayPal end point, and API version.
    $API_UserName = urlencode($GLOBALS['paypal_api_username']);
    $API_Password = urlencode($GLOBAL['paypal_api_password']);
    $API_Signature = urlencode($GLOBALS['paypal_api_signature']);
    $API_Endpoint = $GLOBALS['paypal_api_endpoint'];
    $version = urlencode('56.0');
    
    // setting the curl parameters.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);

    // Set the curl parameters.
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);

    // Set the API operation, version, and API signature in the request.
    $nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";
    
    // Set the request as a POST FIELD for curl.
    curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
    
    // Get response from the server.
    $httpResponse = curl_exec($ch);
    
    if(!$httpResponse) {
        return -1;
        // exit('$methodName_ failed: '.curl_error($ch).'('.curl_errno($ch).')');
    }
    
    // Extract the response details.
    $httpResponseAr = explode("&", $httpResponse);
    
    $httpParsedResponseAr = array();
    foreach ($httpResponseAr as $i => $value) {
        $tmpAr = explode("=", $value);
        if(sizeof($tmpAr) > 1) {
            $httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
        }
    }
    
    if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
        return -2;
        // exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
    }
    
    curl_close($ch);
    
    return $httpParsedResponseAr;
}

?>
