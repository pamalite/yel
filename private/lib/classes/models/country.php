<?php
require_once dirname(__FILE__). "/../../utilities.php";

class Country {
    
    private static function country_exists($_country_code, $_country) {
        $_country = trim(sanitize($_country));
        $_country_code = strtoupper(trim(sanitize($_country_code)));
        
        $query = "SELECT COUNT(*) AS exist FROM countries WHERE 
                  country_code = '". $_country_code. "' AND
                  country = '". $_country. "' LIMIT 1";
        $mysqli = Database::connect();
        if ($result = $mysqli->query($query)) {
            if (count($result[0]['exist']) == "1") {
                return true;
            }
        }
        return false;
    }
    
    public static function country_in_used($_country_code) {
        $_country_code = strtoupper(trim(sanitize($_country_code)));
        
        $query = "SELECT COUNT(*) AS in_used FROM countries WHERE 
                  country_code = '". $_country_code. "' AND
                  show_in_list = 'Y' LIMIT 1";
        $mysqli = Database::connect();
        if ($result = $mysqli->query($query)) {
            if (count($result[0]['in_used']) == "1") {
                return true;
            }
        }
        return false;
    }
    
    public static function code_from_country($_country) {
        $_country = trim(sanitize($_country));
        $query = "SELECT country_code FROM countries WHERE country = '". $_country. "' LIMIT 1";
        $mysqli = Database::connect();
        if ($result = $mysqli->query($query)) {
            if (count($result) == 1) {
                return strtoupper($result[0]['country_code']);
            }
        }

        return $GLOBALS['default_country_code'];
    }
    
    public static function country_from_code($_country_code) {
        $_country_code = strtoupper(trim(sanitize($_country_code)));
        $query = "SELECT country FROM countries WHERE country_code = '". $_country_code. "' LIMIT 1";

        $mysqli = Database::connect();
        if ($result = $mysqli->query($query)) {
            return trim(desanitize($result[0]['country']));
        } 
        
        return $GLOBALS['default_country'];
    }
    
    public static function branch_code_from_code($_country_code) {
        $_country_code = strtoupper(trim(sanitize($_country_code)));
        $query = "SELECT branch_country_code FROM countries WHERE country_code = '". $_country_code. "' LIMIT 1";
        $mysqli = Database::connect();
        if ($result = $mysqli->query($query)) {
            return trim(desanitize($result[0]['branch_country_code']));
        } 
        
        return false;
    }
    
    public static function create($_country_code, $_country, $_branch_code, $_show_in_list = 'Y') {
        $_country_code = strtoupper(trim(sanitize($_country_code)));
        $_country = trim(sanitize($_country));
        $_branch_code = strtoupper(trim(sanitize($_branch_code)));
        
        if (!empty($_country_code) && !empty($_country) && !empty($_branch_code)) {
            if (self::country_exists($_country_code, $_country)) {
                return false;
            }
            
            $query = "INSERT INTO countries SET 
                      country_code = '". $_country_code. "', 
                      country = '". $_country. "', 
                      branch_country_code = '". $_branch_code. "', 
                      show_in_list = '". $_show_in_list. "'";
            $mysqli = Database::connect();
            return $mysqli->execute($query);
        }
        
        return false;
    }
    
    public static function update($_country_code, $_country, $_branch_code, $_show_in_list = 'Y') {
        $_country_code = strtoupper(trim(sanitize($_country_code)));
        $_country = trim(sanitize($_country));
        $_branch_code = strtoupper(trim(sanitize($_branch_code)));
        
        if (!empty($_country_code) && !empty($_country) && !empty($_branch_code)) {
            $query = "UPDATE countries SET 
                      country = '". $_country. "', 
                      branch_country_code = '". $_branch_code. "', 
                      show_in_list = '". $_show_in_list. "'; 
                      WHERE country_code = '". $_country_code. "'";
            $mysqli = Database::connect();
            return $mysqli->execute($query);
        }
        
        return false;
    }
    
    public static function delete($_country_code) {
        // TODO: Implement this method.
    }
    
    public static function get_all() {
        $mysqli = Database::connect();
        $query = "SELECT country_code, country FROM countries ORDER BY country";
        
        return $mysqli->query($query);
    }
    
    public static function get_all_with_display() {
        $mysqli = Database::connect();
        $query = "SELECT country_code, country FROM countries WHERE show_in_list = 'Y' ORDER BY country";
        
        return $mysqli->query($query);
    }
}
?>