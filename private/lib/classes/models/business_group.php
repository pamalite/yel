<?php
require_once dirname(__FILE__). "/../../utilities.php";

class BusinessGroup {
    public static function create($_data) {
        $mysqli = Database::connect();
        
        if (is_null($_data) || !is_array($_data)) {
            return false;
        }
        
        $data = sanitize($_data);
        $query = "INSERT INTO business_groups SET ";                
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
        $mysqli = Database::connect();
        
        if (is_null($_data) || !is_array($_data)) {
            return false;
        }
        
        if (!array_key_exists('id', $_data)) {
            return false;
        }
        
        $data = sanitize($_data);
        $query = "UPDATE business_groups SET ";
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
    
    public static function getSecurityClearance($_id) {
        $mysqli = Database::connect();
        $query = "SELECT security_clearance FROM business_groups WHERE id = ". $_id. " LIMIT 1";
        $result = $mysqli->query($query);
        if (count($result) > 0 && !is_null($result)) {
            $id = $result[0]['security_clearance'];
            return SecurityClearance::get($id);
        }
        
        return false;
    }
    
    public static function get($_id) {
        $mysqli = Database::connect();
        $query = "SELECT * FROM business_groups WHERE id = '". $_id. "' LIMIT 1";
        
        return $mysqli->query($query);
    }
    
    public static function getAll() {
        $mysqli = Database::connect();
        $query = "SELECT * FROM business_groups";
        
        return $mysqli->query($query);
    }
    
    public static function addEmployeeToGroup($_employee, $_group) {
        if (empty($_employee) || empty($_group) || $_employee <= 0 || $_group <= 0) {
            return false;
        }
        
        $query = "INSERT INTO employees_groups SET 
                  employee = ". $_employee. ", 
                  business_group = ". $_group;
                  
        $mysqli = Database::connect();
        return $mysqli->execute($query);
    }
    
    public static function removeEmployeeFromGroup($_employee, $_group) {
        if (empty($_employee) || empty($_group) || $_employee <= 0 || $_group <= 0) {
            return false;
        }
        
        $query = "DELETE FROM employees_groups WHERE  employee = ". $_employee. " AND business_group = ". $_group;
        $mysqli = Database::connect();
        return $mysqli->execute($query);
    }
}
?>
