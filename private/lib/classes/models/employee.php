<?php
require_once dirname(__FILE__). "/../../utilities.php";

class Employee {
    private $id = 0;
    private $seed_id = 0;
    private $mysqli = NULL;
    
    function __construct($_id = "", $_seed_id = "") {
        $this->set($_id, $_seed_id);
    }
    
    public static function extract($_id) {
        $id = substr($_id, 8);
        $tmp = substr($_id, 0, 8);
        $year = substr($tmp, 0, 4);
        $month = substr($tmp, 4, 2);
        $day = substr($tmp, 6, 2);
        
        return array('id' => $id, 'joined_on' => ($year. "-". $month. "-". $day));
    }
    
    private function extract_id($_id) {
        return substr($_id, 8);
    }
    
    private function extract_joined_date($_id) {
        $tmp = substr($_id, 0, 8);
        $year = substr($tmp, 0, 4);
        $month = substr($tmp, 4, 2);
        $day = substr($tmp, 6, 2);
        
        return $year. "-". $month. "-". $day;
    }
    
    private function get_password() {
        $query = "SELECT password FROM employees WHERE id = ". $this->id. "";
        if ($passwords = $this->mysqli->query($query)) {
            return $passwords[0]['password'];
        }
        
        return false;
    }
    
    public static function simple_authenticate(&$_mysqli, $_id, $_password_md5) {
        $query = "SELECT COUNT(*) AS exist FROM employees 
                  WHERE id = ". $this->extract_id($_id). " 
                  AND joined_on = '". $this->extract_joined_date($_id). "' 
                  AND password = '". $_password_md5. "' LIMIT 1";

        if ($result = $_mysqli->query($query)) {
            if ($result[0]['exist'] == "1") {
                return true;
            } 
        }
        
        return false;
    }
    
    public function set($_id = "", $_seed_id = "") {
        if (is_a($this->mysqli, "MySQLi")) {
            $this->mysqli->close();
        }
        
        $this->mysqli = Database::connect();
        $this->id = 0;
        $this->seed_id = 0;
        
        if (!empty($_id)) {
            $this->id = sanitize($this->extract_id($_id));
        }

        if (!empty($_seed_id)) {
            $this->seed_id = sanitize($_seed_id);
        } 
    }
    
    public function reset() {
        $this->set();
    }
    
    public function seed_id() {
        return $this->seed_id;
    }
    
    public function id() {
        return $this->id;
    }
    
    public function email_address() {
        $query = "SELECT email_addr FROM employees WHERE id = ". $this->id. " LIMIT 1";
        if ($email_addr = $this->mysqli->query($query)) {
            return $email_addr[0]['email_addr'];
        }
        
        return false;
    }
    
    public function is_registered($_sha1) {
        if ($this->seed_id == 0) {
            return false;
        } else {
            if ($_sha1 != sanitize($_sha1)) return false; // A hacking attempt occured.
        }
        
        $query = "SELECT seed FROM seeds WHERE id = ". $this->seed_id;
        if ($result = $this->mysqli->query($query)) {
            $seed = $result[0]['seed'];
            $sha1 = sha1($this->id. $this->get_password(). $seed);
            if ($sha1 == $_sha1) {
                return true;
            }
        }
        
        return false;
    }
    
    public function is_logged_in($_sha1) {
        if ($_sha1 != sanitize($_sha1)) return false; // A hacking attempt occured.
        
        $query = "SELECT sha1 FROM employee_sessions WHERE employee = ". $this->id;
        
        if ($result = $this->mysqli->query($query)) {
            return ($result[0]['sha1'] == $_sha1);
        }
        
        return false;
    }
    
    public function create($data) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        if (!array_key_exists('id', $data)) {
            return false;
        }
        
        $data = sanitize($data);
        $query = "INSERT INTO employees SET ";
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "ID") { 
                if (strtoupper($key) == "PASSWORD") {
                    if (strlen($value) != 32) {
                        return false;
                    }
                }

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
        
        if (($id = $this->mysqli->execute($query, true)) > 0) {
            return $id;
        }
        
        return false;
    }
    
    public function update($data) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        $data = sanitize($data);
        $password_updated = false;
        $query = "UPDATE employees SET ";
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "ID") {
                if (strtoupper($key) == "PASSWORD") {
                    $password_updated = true;
                    if (strlen($value) != 32) {
                        return false;
                    }
                }
            
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
    
        $query .= "WHERE id = ". $this->id;
        
        if ($this->mysqli->execute($query)) {
            if ($password_updated) {
                return $this->session_reset($data['password']);
            }
            return true;
        }
    
        return false;
    }
    
    private function session_reset($_password_md5) {
        if ($_password_md5 != sanitize($_password_md5)) {
            return false; // A hacking attempt occured?
        }
        
        $query = "SELECT COUNT(employee) AS exist FROM employee_sessions 
                  WHERE employee = ". $this->id. " LIMIT 1";
        
        if ($sessions = $this->mysqli->query($query)) {
            if ($sessions[0]['exist'] == "1") {
                $query = "SELECT seed FROM seeds WHERE id = ". $this->seed_id;
                if ($seed = $this->mysqli->query($query)) {
                    $sha1 = sha1($this->id. $_password_md5. $seed[0]['seed']);
                    $query = "UPDATE employee_sessions SET sha1 = '". $sha1. "'
                              WHERE employee = ". $this->id;
                    return $this->mysqli->execute($query);
                }
            }
        }
        
        return false;
    }
    
    public function session_set($sha1) {
        if (empty($sha1)) {
            return false;
        } else {
            if ($sha1 != sanitize($sha1)) return false; // A hacking attempt occured?
        }
        
        $query = "SELECT COUNT(employee) AS exist FROM employee_sessions 
                  WHERE employee = ". $this->id. " LIMIT 1";
                  
        if ($sessions = $this->mysqli->query($query)) {
          if ($sessions[0]['exist'] == "1") {
              $query = "UPDATE employee_sessions SET 
                        sha1 = '". $sha1. "', 
                        last_login = NOW() 
                        WHERE employee = ". $this->id;
          } else {
              $query = "INSERT INTO employee_sessions SET 
                        employee = ". $this->id. ", 
                        sha1 = '". $sha1. "', 
                        last_login = NOW()"; 
          }
          
          return $this->mysqli->execute($query);
        }
        
        return false;
    }
    
    public function get() {
        $query = "SELECT * FROM employees WHERE id = ". $this->id. " LIMIT 1";
        
        return $this->mysqli->query($query);
    }
    
    public function get_name() {
        $query = "SELECT CONCAT(firstname, ' ', lastname) AS name FROM employees WHERE id = ". $this->id. " LIMIT 1";
        if ($name = $this->mysqli->query($query)) {
            return stripslashes($name[0]['name']);
        }
        
        return false;
    }
    
    public function get_user_id() {
        $query = "SELECT joined_on FROM employees WHERE id = ". $this->id. " LIMIT 1";
        if ($joined_on = $this->mysqli->query($query)) {
            $date_stamp = str_replace('-', '', $joined_on[0]['joined_on']);
            return $date_stamp. $this->id;
        }
        
        return false;
    }
    
    public static function get_all() {
        $mysqli = Database::connect();
        $query = "SELECT * FROM employees";
        
        return $mysqli->query($query);
    }
    
    public static function get_all_with_limit($limit, $offset = 0) {
        if (empty($limit) || $limit <= 0) {
            return false;
        }
        
        $mysqli = Database::connect();
        $query = "SELECT * FROM employees ";
        
        if ($offset > 0) {
            $query .= "LIMIT ". $offset. ", ". $limit;
        } else {
            $query .= "LIMIT ". $limit;
        }
        
        return $mysqli->query($query);
    }
    
    public function get_branch() {
        $query = "SELECT branches.id, branches.branch, countries.country, 
                  branches.country AS country_code 
                  FROM branches 
                  LEFT JOIN employees ON branches.id = employees.branch 
                  LEFT JOIN countries ON countries.country_code = employees.country 
                  WHERE employees.id = ". $this->id. " LIMIT 1";
        return $this->mysqli->query($query);
    }
    
    public function get_business_groups() {
        $query = "SELECT business_groups.id, business_groups.group, business_groups.security_clearance 
                  FROM business_groups 
                  LEFT JOIN employees_groups ON business_groups.id = employees_groups.business_group 
                  WHERE employees_groups.employee = ". $this->id;
        return $this->mysqli->query($query);
    }
    
    public static function has_clearance_for($_action, $_clearances) {
        foreach ($_clearances as $row) {
            foreach ($row as $key=>$value) {
                if ($key == $_action && $value == '1') {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    public static function has_clearances_for($_action, $_clearances) {
        $_action = trim($_action);
        
        foreach ($_clearances as $row) {
            foreach ($row as $key=>$value) {
                if (strpos($key, $_action) !== false && $value == '1') {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    public static function find($criteria, $db = "") {
        if (is_null($criteria) || !is_array($criteria)) {
            return false;
        }
        
        $columns = "*";
        $joins = "";
        $order = "";
        $group = "";
        $limit = "";
        $match = "";
        
        foreach ($criteria as $key => $clause) {
            switch (strtoupper($key)) {
                case "COLUMNS":
                    $columns = trim($clause);
                    break;
                case "JOINS":
                    $conditions = explode(",", $clause);
                    $i = 0;
                    foreach ($conditions as $condition) {
                        $joins .= "LEFT JOIN ". trim($condition);

                        if ($i < count($conditions)-1) {
                            $joins .= " ";
                        }
                        $i++;
                    }
                    break;
                case "ORDER":
                    $order = "ORDER BY ". trim($clause);
                    break;
                case "GROUP":
                    $group = "GROUP BY ". trim($clause);
                    break;
                case "LIMIT":
                    $limit = "LIMIT ". trim($clause);
                    break;
                case "MATCH":
                    $match = "WHERE ". trim($clause);
                    break;
            }
        }
        
        
        $query = "SELECT ". $columns. " FROM employees ". $joins. 
                  " ". $match. " ". $group. " ". $order. " ". $limit;
        
        $mysqli = Database::connect();
        return $mysqli->query($query);
    }
}
?>