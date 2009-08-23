<?php
require_once dirname(__FILE__). "/../../utilities.php";

class BusinessGroup {
    private $mysqli = NULL;
    
    public static function create($data) {
        $mysqli = Database::connect();
        
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        $data = sanitize($data);
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
    
    public static function update($data) {
        $mysqli = Database::connect();
        
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        if (!array_key_exists('id', $data)) {
            return false;
        }
        
        $data = sanitize($data);
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
    
    public static function get_security_clearance($_id) {
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
    
    public static function get_all() {
        $mysqli = Database::connect();
        $query = "SELECT * FROM business_groups";
        
        return $mysqli->query($query);
    }
    
    public static function add_employee_to_group($_employee, $_group) {
        if (empty($_employee) || empty($_group) || $_employee <= 0 || $_group <= 0) {
            return false;
        }
        
        $query = "INSERT INTO employees_groups SET 
                  employee = ". $_employee. ", 
                  business_group = ". $_group;
                  
        $mysqli = Database::connect();
        return $mysqli->execute($query);
    }
    
    public static function remove_employee_from_group($_employee, $_group) {
        if (empty($_employee) || empty($_group) || $_employee <= 0 || $_group <= 0) {
            return false;
        }
        
        $query = "DELETE FROM employees_groups WHERE  employee = ". $_employee. " AND business_group = ". $_group;
        $mysqli = Database::connect();
        return $mysqli->execute($query);
    }
}
?>
