<?php
require_once dirname(__FILE__). "/../../utilities.php";

class Currency {
    private static function currencyExists($_symbol) {
        $_symbol = strtoupper(trim(sanitize($_symbol)));
        
        $query = "SELECT COUNT(*) AS exist FROM currencies WHERE 
                  symbol = '". $_symbol. "' LIMIT 1";
        $mysqli = Database::connect();
        if ($result = $mysqli->query($query)) {
            if (count($result[0]['exist']) == "1") {
                return true;
            }
        }
        return false;
    }
    
    public static function getSymbolFromCountryCode($_country_code) {
        $_country_code = strtoupper(trim(sanitize($_country_code)));
        $query = "SELECT symbol FROM currencies WHERE country_code = '". $_country_code. "' LIMIT 1";

        $mysqli = Database::connect();
        if ($result = $mysqli->query($query)) {
            return trim(desanitize($result[0]['symbol']));
        } 
        
        return "MYR";
    }
    
    public static function getSymbolFromCountry($_country) {
        return self::getSymbolFromCountryCode(Country::getCountryCodeFrom($_country));
    }
    
    public static function create($_symbol, $_country_code, $_rate) {
        $_country_code = strtoupper(trim(sanitize($_country_code)));
        $_symbol = trim(sanitize($_symbol));
        
        if (!empty($_country_code) && !empty($_symbol) && !empty($_rate)) {
            if (self::currenyExists($_symbol)) {
                return false;
            }
            
            $query = "INSERT INTO currencies SET 
                      country_code = '". $_country_code. "', 
                      symbol = '". $_symbol. "', 
                      rate = ". $_rate;
            $mysqli = Database::connect();
            return $mysqli->execute($query);
        }
        
        return false;
    }
    
    public static function update($_symbol, $_country_code, $_rate) {
        $_country_code = strtoupper(trim(sanitize($_country_code)));
        $_symbol = trim(sanitize($_symbol));
        
        if (!empty($_country_code) && !empty($_symbol) && !empty($_rate)) {
            $query = "UPDATE currenciies SET 
                      country_code = '". $_country_code. "', 
                      rate = '". $_rate. "' 
                      WHERE symbol = '". $_symbol. "'";
            $mysqli = Database::connect();
            return $mysqli->execute($query);
        }
        
        return false;
    }
    
    public static function delete($_symbol) {
        $_symbol = trim(sanitize($_symbol));
        if (!empty($_symbol)) {
            if (self::currenyExists($_symbol)) {
                return false;
            }
            
            $query = "DELETE FROM currencies WHERE symbol = '". $_symbol. "'";
            $mysqli = Database::connect();
            return $mysqli->execute($query);
        }
        
        return false;
    }
    
    public static function getAll() {
        $mysqli = Database::connect();
        $query = "SELECT currencies.symbol, currencies.currency, countries.country 
                  FROM currencies 
                  LEFT JOIN countries ON countries.country_code = currencies.country_code";
        
        return $mysqli->query($query);
    }
}
?>