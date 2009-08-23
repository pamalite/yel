<?php
require_once dirname(__FILE__). "/../../utilities.php";

class ReferralReward {
    private $mysqli = NULL;
    
    public static function create($data) {
        $mysqli = Database::connect();
        
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        if (!array_key_exists('referral', $data) || !array_key_exists('reward', $data)) {
            return false;
        }
        
        $data = sanitize($data);
        
        if ($data['reward'] <= 0) {
            return false;
        }
        
        $query = "INSERT INTO referral_rewards SET ";                
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
    
    public static function update($data) {
        $mysqli = Database::connect();
        
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        if (!array_key_exists('id', $data)) {
            return false;
        }
        
        $data = sanitize($data);
        $query = "UPDATE referral_rewards SET ";
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
    
    public static function get($_id) {
        $mysqli = Database::connect();
        $query = "SELECT * FROM referral_rewards WHERE id = '". $_id. "' LIMIT 1";
        
        return $mysqli->query($query);
    }
    
    public static function get_all_of_referral($_referral) {
        if (empty($_referral)) {
            return false;
        }
        
        $mysqli = Database::connect();
        $query = "SELECT * FROM referral_rewards WHERE referral = ". $_referral;
        
        return $mysqli->query($query);
    }
    
    public static function get_all() {
        $mysqli = Database::connect();
        $query = "SELECT * FROM referral_rewards";
        
        return $mysqli->query($query);
    }
    
    public static function get_sum_paid_of_referral($_referral) {
        if (empty($_referral)) {
            return false;
        }
        
        $mysqli = Database::connect();
        $query = "SELECT SUM(reward) AS amount FROM referral_rewards 
                  WHERE referral = ". $_referral;
        
        return $mysqli->query($query);
    }
}
?>
