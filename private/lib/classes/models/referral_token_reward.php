<?php
require_once dirname(__FILE__). "/../../utilities.php";

class ReferralTokenReward {
    public static function create($_data) {
        if (is_null($_data) || !is_array($_data)) {
            return false;
        }
        
        if (!array_key_exists('referral', $_data) || !array_key_exists('token', $_data)) {
            return false;
        }
        
        $data = sanitize($_data);
        if ($data['token'] <= 0) {
            return false;
        }
        
        $mysqli = Database::connect();
        
        $query = "INSERT INTO referral_token_rewards SET ";                
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "ID") {
                if (is_string($value)) {
                    if (strtoupper($value) == "NULL") {
                        $query .= "`". $key. "` = NULL";
                    } else {
                        $query .= "`". $key. "` = '". $value. "'";
                    }
                } else if (is_null($value) || empty($value)) {
                    $query .= "`". $key. "` = ''";
                } else {
                    $query .= "`". $key. "` = ". $value;
                }

                if ($i < count($data) - 1) {
                    $query .= ", ";
                }
            }
            
            $i++;
        }
        
        if (($id = $mysqli->execute($query, true)) > 0) {
            return $id;
        }
        
        return false;
    }
    
    public static function update($_data) {
        if (is_null($_data) || !is_array($_data)) {
            return false;
        }
        
        if (!array_key_exists('id', $_data)) {
            return false;
        }
        
        $mysqli = Database::connect();
        $data = sanitize($_data);
        $query = "UPDATE referral_token_rewards SET ";
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "ID") {
                if (is_string($value)) {
                    if (strtoupper($value) == "NULL") {
                        $query .= "`". $key. "` = NULL";
                    } else {
                        $query .= "`". $key. "` = '". $value. "'";
                    }
                } else if (is_null($value) || empty($value)) {
                    $query .= "`". $key. "` = ''";
                } else {
                    $query .= "`". $key. "` = ". $value;
                }

                if ($i < count($data) - 1) {
                    $query .= ", ";
                } else {
                    $query .= " ";
                }
            }
            
            $i++;
        }
    
        $query .= "WHERE id = '". $data['id']. "'";
    
         return $mysqli->execute($query);
    }
    
    public static function getId($_id) {
        $mysqli = Database::connect();
        $query = "SELECT * FROM referral_token_rewards WHERE id = '". $_id. "' LIMIT 1";
        
        return $mysqli->query($query);
    }
    
    public static function getAllForReferral($_referral) {
        if (empty($_referral)) {
            return false;
        }
        
        $mysqli = Database::connect();
        $query = "SELECT * FROM referral_token_rewards WHERE referral = ". $_referral;
        
        return $mysqli->query($query);
    }
    
    public static function getAll() {
        $mysqli = Database::connect();
        $query = "SELECT * FROM referral_token_rewards";
        
        return $mysqli->query($query);
    }
    
    public static function getSumPaidForReferral($_referral) {
        if (empty($_referral)) {
            return false;
        }
        
        $mysqli = Database::connect();
        $query = "SELECT SUM(reward) AS amount FROM referral_token_rewards 
                  WHERE referral = ". $_referral;
        
        return $mysqli->query($query);
    }
}
?>
