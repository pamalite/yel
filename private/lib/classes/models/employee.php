<?php
require_once dirname(__FILE__). "/../../utilities.php";

class Employee implements Model {
    private $id = 0;
    private $seed_id = 0;
    private $mysqli = NULL;
    
    function __construct($_id = "", $_seed_id = "") {
        if (!is_a($this->mysqli, "MySQLi")) {
            $this->mysqli = Database::connect();
        }
        
        $this->initializeWith($_id, $_seed_id);
    }
    
    // function __destruct() {
    //     $this->mysqli->close();
    // }
    
    private function hasData($_data) {
        if (is_null($_data) || !is_array($_data)) {
            return false;
        }
        
        return true;
    }
    
    private function getIdOnlyFromUserId($_id) {
        return substr($_id, 8);
    }
    
    private function getJoinedDateOnlyFromUserId($_id) {
        $tmp = substr($_id, 0, 8);
        $year = substr($tmp, 0, 4);
        $month = substr($tmp, 4, 2);
        $day = substr($tmp, 6, 2);
        
        return $year. "-". $month. "-". $day;
    }
    
    private function getPasswordHash() {
        $query = "SELECT password FROM employees WHERE id = ". $this->id. "";
        if ($passwords = $this->mysqli->query($query)) {
            return $passwords[0]['password'];
        }
        
        return false;
    }
    
    private function resetSessionWithNewPassword($_password_md5) {
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
    
    public function create($_data) {
        if (!$this->hasData($_data)) {
            return false;
        }
        
        if (!array_key_exists('id', $_data)) {
            return false;
        }
        
        $data = sanitize($_data);
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
    
    public function update($_data) {
        if (!$this->hasData($_data)) {
            return false;
        }
        
        $data = sanitize($_data);
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
                return $this->resetSessionWithNewPassword($data['password']);
            }
            return true;
        }
    
        return false;
    }
    
    public function delete() {
        // Reserved for future use.
    }
    
    public function get() {
        $query = "SELECT * FROM employees WHERE id = ". $this->id. " LIMIT 1";
        return $this->mysqli->query($query);
    }
    
    public function find($_criteria) {
        if (!$this->hasData($_criteria)) {
            return false;
        }
        
        $columns = '*';
        $joins = '';
        $order = '';
        $group = '';
        $limit = '';
        $match = '';
        
        foreach ($_criteria as $key => $clause) {
            switch (strtoupper($key)) {
                case 'COLUMNS':
                    $columns = trim($clause);
                    break;
                case 'JOINS':
                    $conditions = explode(',', $clause);
                    $i = 0;
                    foreach ($conditions as $condition) {
                        $joins .= "LEFT JOIN ". trim($condition);

                        if ($i < count($conditions)-1) {
                            $joins .= " ";
                        }
                        $i++;
                    }
                    break;
                case 'ORDER':
                    $order = "ORDER BY ". trim($clause);
                    break;
                case 'GROUP':
                    $group = "GROUP BY ". trim($clause);
                    break;
                case 'LIMIT':
                    $limit = "LIMIT ". trim($clause);
                    break;
                case 'MATCH':
                    $match = "WHERE ". trim($clause);
                    break;
            }
        }
        
        $query = "SELECT ". $columns. " FROM employees ". $joins. 
                  " ". $match. " ". $group. " ". $order. " ". $limit;
        
        return $this->mysqli->query($query);
    }
    
    public function initializeWith($_id = "", $_seed_id = "") {
        $this->id = 0;
        $this->seed_id = 0;
        
        if (!empty($_id)) {
            if (strlen($_id) < 8) {
                $this->id = $_id;
            } else {
                $this->id = sanitize($this->getIdOnlyFromUserId($_id));
            }
        }

        if (!empty($_seed_id)) {
            $this->seed_id = sanitize($_seed_id);
        } 
    }
    
    public function reset() {
        $this->initializeWith();
    }
    
    public function getSeedId() {
        return $this->seed_id;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getEmailAddress() {
        $query = "SELECT email_addr FROM employees WHERE id = ". $this->id. " LIMIT 1";
        if ($email_addr = $this->mysqli->query($query)) {
            return $email_addr[0]['email_addr'];
        }
        
        return false;
    }
    
    public function isRegistered($_sha1) {
        if ($this->seed_id == 0) {
            return false;
        } else {
            if ($_sha1 != sanitize($_sha1)) return false; // A hacking attempt occured.
        }
        
        $query = "SELECT seed FROM seeds WHERE id = ". $this->seed_id;
        if ($result = $this->mysqli->query($query)) {
            $seed = $result[0]['seed'];
            $sha1 = sha1($this->id. $this->getPasswordHash(). $seed);
            if ($sha1 == $_sha1) {
                return true;
            }
        }
        
        return false;
    }
    
    public function isLoggedIn($_sha1) {
        if ($_sha1 != sanitize($_sha1)) return false; // A hacking attempt occured.
        
        $query = "SELECT sha1 FROM employee_sessions WHERE employee = ". $this->id;
        
        if ($result = $this->mysqli->query($query)) {
            return ($result[0]['sha1'] == $_sha1);
        }
        
        return false;
    }
    
    public function setSessionWith($_sha1) {
        if (empty($_sha1)) {
            return false;
        } else {
            if ($_sha1 != sanitize($_sha1)) return false; // A hacking attempt occured?
        }
        
        $query = "SELECT COUNT(employee) AS exist FROM employee_sessions 
                  WHERE employee = ". $this->id. " LIMIT 1";
                  
        if ($sessions = $this->mysqli->query($query)) {
          if ($sessions[0]['exist'] == "1") {
              $query = "UPDATE employee_sessions SET 
                        sha1 = '". $_sha1. "', 
                        last_login = NOW() 
                        WHERE employee = ". $this->id;
          } else {
              $query = "INSERT INTO employee_sessions SET 
                        employee = ". $this->id. ", 
                        sha1 = '". $_sha1. "', 
                        last_login = NOW()"; 
          }
          
          return $this->mysqli->execute($query);
        }
        
        return false;
    }
    
    public function getName() {
        $query = "SELECT CONCAT(firstname, ' ', lastname) AS name 
                  FROM employees 
                  WHERE id = ". $this->id. " LIMIT 1";
        if ($name = $this->mysqli->query($query)) {
            return stripslashes($name[0]['name']);
        }
        
        return false;
    }
    
    public function getUserId() {
        $query = "SELECT joined_on FROM employees WHERE id = ". $this->id. " LIMIT 1";
        if ($joined_on = $this->mysqli->query($query)) {
            $date_stamp = str_replace('-', '', $joined_on[0]['joined_on']);
            return $date_stamp. $this->id;
        }
        
        return false;
    }
    
    public function getBranch() {
        $query = "SELECT branches.* 
                  FROM branches 
                  LEFT JOIN employees ON branches.id = employees.branch 
                  WHERE employees.id = ". $this->id. " LIMIT 1";
        return $this->mysqli->query($query);
    }
    
    public function getBusinessGroups() {
        $query = "SELECT business_groups.id, business_groups.group, business_groups.security_clearance 
                  FROM business_groups 
                  LEFT JOIN employees_groups ON business_groups.id = employees_groups.business_group 
                  WHERE employees_groups.employee = ". $this->id;
        return $this->mysqli->query($query);
    }
    
    public function getClearances() {
        $business_groups = $this->getBusinessGroups();
        $i = 0;
        $security_clearance_ids = '';
        foreach ($business_groups as $business_group) {
            $security_clearance_ids .= $business_group['security_clearance'];
            
            if ($i < count($business_groups) - 1){
                $security_clearance_ids .= ', ';
            }
            
            $i++;
        }
        
        $query = "SELECT * FROM security_clearances WHERE id IN (". $security_clearance_ids. ")";
        $result = $this->mysqli->query($query);
        if ($result !== false) {
            $clearances = array();
            foreach ($result as $row) {
                foreach ($row as $column=>$value) {
                    if ($column != 'id' && $value != '0') {
                        $clearances[] = $column;
                    }
                }
            }
            
            return $clearances;
        } 
        
        return false;
    }    
}
?>
