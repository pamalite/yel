<?php
require_once dirname(__FILE__). "/../../utilities.php";

class SecurityClearance {
    private $mysqli = NULL;
    
    public static function create($data) {
        $mysqli = Database::connect();
        
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        $data = sanitize($data);
        $query = "INSERT INTO security_clearances SET ";                
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
        $query = "UPDATE security_clearances SET ";
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
        $query = "SELECT * FROM security_clearances WHERE id = '". $_id. "' LIMIT 1";
        
        return $mysqli->query($query);
    }
    
    public static function get_all() {
        $mysqli = Database::connect();
        $query = "SELECT * FROM security_clearances";
        
        return $mysqli->query($query);
    }
    
    public static function grant_all($_id) {
        if (empty($_id) || $_id <= 0) {
            return false;
        }
        
        $query = "UPDATE security_clearances SET 
                  reports_create = 1,
                  reports_remove = 1,
                  reports_update = 1,
                  reports_view = 1,
                  invoice_create = 1,
                  invoice_remove = 1,
                  invoice_update = 1,
                  invoice_view = 1,
                  rewards_create = 1,
                  rewards_remove = 1,
                  rewards_update = 1,
                  rewards_view = 1,
                  hirers_create = 1,
                  hirers_remove = 1,
                  hirers_update = 1,
                  hirers_view = 1,
                  referrers_create = 1,
                  referrers_remove = 1,
                  referrers_update = 1,
                  referrers_view = 1,
                  prospect_create = 1,
                  prospect_remove = 1,
                  prospect_update = 1,
                  prospect_view = 1
                  WHERE id = ". $_id;
         $mysqli = Database::connect();
         return $mysqli->execute($query);
    }
    
    public static function revoke_all($_id) {
        if (empty($_id) || $_id <= 0) {
            return false;
        }
        
        $query = "UPDATE security_clearances SET 
                  reports_create = 0,
                  reports_remove = 0,
                  reports_update = 0,
                  reports_view = 0,
                  invoice_create = 0,
                  invoice_remove = 0,
                  invoice_update = 0,
                  invoice_view = 0,
                  rewards_create = 0,
                  rewards_remove = 0,
                  rewards_update = 0,
                  rewards_view = 0,
                  hirers_create = 0,
                  hirers_remove = 0,
                  hirers_update = 0,
                  hirers_view = 0,
                  referrers_create = 0,
                  referrers_remove = 0,
                  referrers_update = 0,
                  referrers_view = 0,
                  prospect_create = 0,
                  prospect_remove = 0,
                  prospect_update = 0,
                  prospect_view = 0
                  WHERE id = ". $_id;
         $mysqli = Database::connect();
         return $mysqli->execute($query);
    }
}
?>
