<?php
require_once dirname(__FILE__). "/../../utilities.php";

class Currency {
    /*
     NOTE: All currencies are based on per EURO, as the forex feed is from 
           European Central Bank.  
    */
    
    private static function currency_exists($_symbol) {
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
    
    public static function symbol_from_country_code($_country_code) {
        $_country_code = strtoupper(trim(sanitize($_country_code)));
        $query = "SELECT symbol FROM currencies WHERE country_code = '". $_country_code. "' LIMIT 1";

        $mysqli = Database::connect();
        if ($result = $mysqli->query($query)) {
            return trim(desanitize($result[0]['symbol']));
        } 
        
        return "MYR";
    }
    
    public static function symbol_from_country($_country) {
        return self::symbol_from_country_code(Country::code_from_country($_country));
    }
    
    public static function convert_amount_from_to($_from, $_to, $_amount) {
        $_from = strtoupper(trim(sanitize($_from)));
        $_to = strtoupper(trim(sanitize($_to)));
        
        if ($_from == $_to) {
            return $_amount;
        }
        
        $query = "SELECT symbol, rate FROM currencies WHERE symbol IN ('". $_from. "', '". $_to."')";
        $mysqli = Database::connect();
        $rate_from = 1;
        $rate_to = 1;
        if ($rates = $mysqli->query($query)) {
            foreach ($rates as $rate) {
                if ($rate['symbol'] == $_from) {
                    $rate_from = $rate['rate'];
                } else {
                    $rate_to = $rate['rate'];
                }
            }
        }
        return $_amount * ($rate_to / $rate_from);
    }
    
    public static function create($_symbol, $_country_code, $_rate) {
        $_country_code = strtoupper(trim(sanitize($_country_code)));
        $_symbol = trim(sanitize($_symbol));
        
        if (!empty($_country_code) && !empty($_symbol) && !empty($_rate)) {
            if (self::currency_exists($_symbol)) {
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
            if (self::currency_exists($_symbol)) {
                return false;
            }
            
            $query = "DELETE FROM currencies WHERE symbol = '". $_symbol. "'";
            $mysqli = Database::connect();
            return $mysqli->execute($query);
        }
        
        return false;
    }
    
    public static function get_all() {
        $mysqli = Database::connect();
        $query = "SELECT currencies.symbol, currencies.currency, countries.country, currencies.rate 
                  FROM currencies 
                  LEFT JOIN countries ON countries.country_code = currencies.country_code";
        
        return $mysqli->query($query);
    }
    
    public static function update_rates() {
        $mysqli = Database::connect();
        $new_rates = array();
        $XMLContent = file($GLOBALS['forex_feed']);
        foreach ($XMLContent as $line) {
            if (ereg("currency='([[:alpha:]]+)'",$line,$currencyCode)) {
                if (ereg("rate='([[:graph:]]+)'",$line,$rate)) {
                    $new_rates[$currencyCode[1]] = $rate[1];
                }
            }
        }
        
        $query = "SELECT DISTINCT symbol FROM currencies";
        $symbols = $mysqli->query($query);
        $query = '';
        foreach ($symbols as $symbol) {
            $query .= "UPDATE currencies SET 
                      rate = ". $new_rates[$symbol['symbol']]. " 
                      WHERE symbol = '". $symbol['symbol']. "'; ";
        }
        
        if (!$mysqli->transact($query)) {
            return false;
        }
        
        return true;
    }
}
?>