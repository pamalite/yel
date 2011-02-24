<?php
require_once dirname(__FILE__). "/../config/common.inc";
require_once dirname(__FILE__). "/../config/stopwords.inc";
require_once "geoip.inc";
require_once "interfaces.php";
require_once "models.php";
require_once "classes.php";

date_default_timezone_set('Asia/Kuala_Lumpur');

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

function format_date($_datestamp) {
    $dates = explode('-', $_datestamp);
    return date('j M, Y', mktime(0, 0, 0, $dates[1], $dates[2], $dates[0]));
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

function generate_dropdown($_id, $_css_class, $_min, $_max, 
                           $_selected = '', $_maxlength = 0, 
                           $_default_option = '') {
    if ($_min == $_max || $_max < $_min) {
        return '';
    }
    
    $html = '<select id="'. $_id. '" class="'. $_css_class. '">'. "\n";
    
    if (!empty($_default_option)) {
        if (empty($_selected)) {
            $html .= '<option value="" selected>'. $_default_option. '</option>'. "\n";
        } else {
            $html .= '<option value="">'. $_default_option. '</option>'. "\n";
        }
        
        $html .= '<option value="" disabled>&nbsp;</option>'. "\n";
    }
    
    for($i = $_min; $i <= $_max; $i++) {
        $value = $i;
        if ($_maxlength > 0) {
            $zeros_needed = $_maxlength - strlen($value);
            for ($zero = 1; $zero <= $zeros_needed; $zero++) {
                $value = '0'. $value;
            }
        }
        
        if ($value == $_selected) {
            $html .= '<option value="'. $value. '" selected>'. $value. '</option>'. "\n";
        } else {
            $html .= '<option value="'. $value. '">'. $value. '</option>'. "\n";
        }
    }
    $html .= '</select>';
    
    return $html;
}

function generate_month_dropdown($_id, $_css_class, $_selected = '') {
    $html = '<select id="'. $_id. '" class="'. $_css_class. '">'. "\n";
    if (empty($_selected)) {
        $html .= '<option value="" selected>Month</option>'. "\n";
    } else {
        $html .= '<option value="">Month</option>'. "\n";
    }
    
    $html .= '<option value="" disabled>&nbsp;</option>'. "\n";
    
    for($i = 1; $i <= 12; $i++) {
        $value = $i;
        if ($value < 10) {
            $value = '0'. $value;
        }
        
        $month = '';
        switch ($value) {
            case '01':
                $month = 'January';
                break;
            case '02':
                $month = 'February';
                break;
            case '03':
                $month = 'March';
                break;
            case '04':
                $month = 'April';
                break;
            case '05':
                $month = 'May';
                break;
            case '06':
                $month = 'June';
                break;
            case '07':
                $month = 'July';
                break;
            case '08':
                $month = 'August';
                break;
            case '09':
                $month = 'September';
                break;
            case '10':
                $month = 'October';
                break;
            case '11':
                $month = 'November';
                break;
            case '12':
                $month = 'December';
                break;
        }
        
        if ($value == $_selected) {
            $html .= '<option value="'. $value. '" selected>'. $month. '</option>'. "\n";
        } else {
            $html .= '<option value="'. $value. '">'. $month. '</option>'. "\n";
        }
    }
    $html .= '</select>';
    
    return $html;
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

function format_job_description($_description) {
    $formatted_description = '';
    
    // clean up
    $formatted_description = stripslashes($_description);
    
    // convert square tags to html tags
    $formatted_description = str_replace(array('[/b]', '[/i]', '[/u]', '[/hl]'), '</span>', $formatted_description);
    $formatted_description = str_replace('[b]', '<span class="bold">', $formatted_description);
    $formatted_description = str_replace('[i]', '<span class="italic">', $formatted_description);
    $formatted_description = str_replace('[u]', '<span class="underline">', $formatted_description);
    $formatted_description = str_replace('[hl]', '<span class="highlight">', $formatted_description);
    $formatted_description = str_replace('[list]', '<ul>', $formatted_description);
    $formatted_description = str_replace('[/list]', '</ul>', $formatted_description);
    $formatted_description = str_replace('[nlist]', '<ol>', $formatted_description);
    $formatted_description = str_replace('[/nlist]', '</ol>', $formatted_description);
    $formatted_description = str_replace('[item]', '<li>', $formatted_description);
    $formatted_description = str_replace('[/item]', '</li>', $formatted_description);
    
    // clean up extra newlines
    $formatted_description = str_replace('</li><br/>', '</li>', $formatted_description);
    $formatted_description = str_replace('<ul><br/>', '<ul>', $formatted_description);
    $formatted_description = str_replace('<ol><br/>', '<ol>', $formatted_description);
    $formatted_description = str_replace('</ul><br/>', '</ul>', $formatted_description);
    $formatted_description = str_replace('</ol><br/>', '</ol>', $formatted_description);
    
    return $formatted_description;
}

function sql_nullify($_str) {
    $_str = trim($_str);
    if (empty($_str) || is_null($_str) || 
        $_str == '0' || $_str == 0) {
        return 'NULL';
    }
    
    return $_str;
}
?>
