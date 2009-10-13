<?php
require_once dirname(__FILE__). "/../../utilities.php";

class Recommender {
    private $id = 0;
    private $mysqli = NULL;
    
    function __construct($_id = "") {
        $this->set($_id);
    }
    
    public function set($_id = "") {
        if (is_a($this->mysqli, "MySQLi")) {
            $this->mysqli->close();
        }
        
        $this->mysqli = Database::connect();
        $this->id = 0;
        
        if (!empty($_id)) {
            $this->id = sanitize($_id);
        }
    }
    
    public function reset() {
        $this->set();
    }
    
    public function id() {
        return $this->id;
    }
    
    public function get_name() {
        $query = "SELECT CONCAT(firstname, ' ', lastname) AS name 
                  FROM recommenders WHERE email_addr = '". $this->id. "' LIMIT 1";
        if ($name = $this->mysqli->query($query)) {
            return $name[0]['name'];
        }
        
        return false;
    }
    
    public function get_industries() {
        $query = "SELECT industries.industry 
                  FROM recommender_industries 
                  LEFT JOIN industries ON industries.id = recommender_industries.industry 
                  WHERE recommender_industries.recommender = '". $this->id. "'";
        if ($name = $this->mysqli->query($query)) {
            return $name[0]['industry'];
        }
        
        return false;
    }
    
    public function get() {
        $query = "SELECT * FROM recommenders WHERE email_addr = '". $this->id. "' LIMIT 1";
        
        return $this->mysqli->query($query);
    }
    
    public static function get_all() {
        $mysqli = Database::connect();
        $query = "SELECT * FROM recommenders";
        
        return $mysqli->query($query);
    }
    
    public static function get_all_with_limit($limit, $offset = 0) {
         if (empty($limit) || $limit <= 0) {
                return false;
            }

            $mysqli = Database::connect();
            $query = "SELECT * FROM recommenders ";

            if ($offset > 0) {
                $query .= "LIMIT ". $offset. ", ". $limit;
            } else {
                $query .= "LIMIT ". $limit;
            }

            return $mysqli->query($query);
    }
    
    public function create($data) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        $data = sanitize($data);
        $query = "INSERT INTO recommenders SET ";
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "EMAIL_ADDR") {
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
        
        if ($i == 0) {
            $query .= "email_addr = '". $this->id. "'";
        } else {
            $query .= ", email_addr = '". $this->id. "'";
        }
        
        if ($this->mysqli->execute($query)) {
            return true;
        }
        
        return false;
    }
    
    public function update($data) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        $data = sanitize($data);
        $password_updated = false;
        $query = "UPDATE recommenders SET ";
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "EMAIL_ADDR") {
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
    
        $query .= "WHERE email_addr = '". $this->id. "'";
        
        return $this->mysqli->execute($query);
    }
    
    public function delete() {
        // TODO: Check all dependencies before deleting the entry
    }
    
    public function add_to_industry($_industry_id) {
        if (empty($_industry_id) || is_null($_industry_id)) {
            return false;
        }
        
        $query = "INSERT INTO recommender_industries SET 
                  recommender = '". $this->id(). "', 
                  industry = ". $_industry_id;
        
        return $this->mysqli->execute($query);
    }
    
    public function add_to_industries($_industry_ids) {
        if (empty($_industry_ids) || is_null($_industry_ids) || !is_array($_industry_ids)) {
            return false;
        }
        
        $all_success = true;
        foreach ($_industry_ids as $industry_id) {
            if (!$this->add_to_industry(trim($industry_id))) {
                $all_success = false;
            }
        }
        
        return $all_success;
    }
    
    public function get_recommended_candidates($_added_by, $_order = '') {
        if (empty($_added_by) || is_null($_added_by)) {
            return false;
        }
        
        if (empty($_order)) {
            $_order = 'joined_on DESC';
        }
        $query = "SELECT email_addr, CONCAT(firstname, ', ', lastname) AS member, phone_num, 
                  DATE_FORMAT(joined_on, '%e %b, %Y') AS formatted_joined_on 
                  FROM members 
                  WHERE recommender = '". $this->id(). "' AND 
                  added_by = '". $_added_by. "' 
                  ORDER BY ". $_order;
                  
        return $this->mysqli->query($query);
    }
    
    public static function find($criteria, $db = "") {
        if (is_null($criteria) || !is_array($criteria)) {
            return false;
        }
        
        $columns = "*";
        if (array_key_exists('columns', $criteria)) {
            $columns = trim($criteria['columns']);
        }
        
        $joins = "";
        if (array_key_exists('joins', $criteria)) {
            $conditions = explode(",", $criteria['joins']);
            $i = 0;
            foreach ($conditions as $condition) {
                $joins .= "LEFT JOIN ". trim($condition);
                
                if ($i < count($conditions)-1) {
                    $joins .= " ";
                }
                $i++;
            }
        }
        
        $order = "";
        if (array_key_exists('order', $criteria)) {
            $order = "ORDER BY ". trim($criteria['order']);
        }
        
        $group = "";
        if (array_key_exists('GROUP', $criteria)) {
            $order = "GROUP BY ". trim($criteria['group']);
        }
        
        $limit = "";
        if (array_key_exists('limit', $criteria)) {
            $limit = "LIMIT ". trim($criteria['limit']);
        }
        
        $match = "";
        if (array_key_exists('match', $criteria)) {
            $match = "WHERE ". trim($criteria['match']);
        }
        
        $query = "SELECT ". $columns. " FROM recommenders ". $joins. 
                  " ". $match. " ". $group. " ". $order. " ". $limit;
                  
        $mysqli = Database::connect();
        return $mysqli->query($query);
    }
}
?>