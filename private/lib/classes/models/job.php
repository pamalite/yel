<?php
require_once dirname(__FILE__). "/../../utilities.php";

class Job {
    private $id = 0;
    private $mysqli = NULL;
    
    function __construct($_id = "") {
        $this->mysqli = Database::connect();
        $this->id = 0;
        
        if (!empty($_id)) {
            $this->id = sanitize($_id);
        }
    }
    
    public function id() {
        return $this->id;
    }
    
    public function get() {
        $query = "SELECT * FROM jobs WHERE id = '". $this->id. "' LIMIT 1";
        
        return $this->mysqli->query($query);
    }
    
    public function add_view_count() {
        $query = "UPDATE jobs SET views_count = (views_count + 1) 
                  WHERE id = ". $this->id;
                  
        return $this->mysqli->execute($query);
    }
    
    public static function get_all() {
        $mysqli = Database::connect();
        $query = "SELECT * FROM jobs";
        
        return $mysqli->query($query);
    }
    
    public static function get_all_with_limit($limit, $offset = 0) {
         if (empty($limit) || $limit <= 0) {
                return false;
            }

            $mysqli = Database::connect();
            $query = "SELECT * FROM jobs ";

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
        
        if (!array_key_exists('employer', $data)) {
            return false;
        }
        
        $description_no_gloss = sanitize(strip_tags($data['description']));
        $data = sanitize($data);
        $query = "INSERT INTO jobs SET ";
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
        
        if (($id = $this->mysqli->execute($query, true)) > 0) {
            $this->id = $id;
            
            $query = "INSERT INTO job_index SET 
                      job = ". $this->id. ", 
                      country = '". $data['country']. "', 
                      currency = '". $data['currency']. "', 
                      title = '". $data['title']. "', 
                      description = '". $description_no_gloss. "'"; 
            
            if (array_key_exists('state', $data)) {
                $query .= ", state = '". $data['state']. "'";
            }
            
            $this->mysqli->execute($query);
            return $this->id;
        }
        
        return false;
    }
    
    public function update($data) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        $description_no_gloss = "";
        if (array_key_exists('description', $data)) {
            $description_no_gloss = sanitize(strip_tags($data['description']));
        }
        
        $data = sanitize($data);
        $query = "UPDATE jobs SET ";
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
    
        $query .= "WHERE id = '". $this->id. "'";
        
        if ($this->mysqli->execute($query)) {
            $need_update = false;
            $query = "UPDATE job_index SET "; 
            
            if (array_key_exists('state', $data)) {
                if ($need_update) {
                    $query .= ", state = '". $data['state']. "'";
                } else {
                    $query .= "state = '". $data['state']. "'";
                    $need_update = true;
                }
            }
            
            if (array_key_exists('country', $data)) {
                if ($need_update) {
                    $query .= ", country = '". $data['country']. "'";
                } else {
                    $query .= "country = '". $data['country']. "'";
                    $need_update = true;
                }
            }
            
            if (array_key_exists('currency', $data)) {
                if ($need_update) {
                    $query .= ", currency = '". $data['currency']. "'";
                } else {
                    $query .= "currency = '". $data['currency']. "'";
                    $need_update = true;
                }
            }
            
            if (array_key_exists('title', $data)) {
                if ($need_update) {
                    $query .= ", title = '". $data['title']. "'";
                } else {
                    $query .= "title = '". $data['title']. "'";
                    $need_update = true;
                }
            }
            
            if (array_key_exists('description', $data)) {
                if ($need_update) {
                    $query .= ", description = '". $description_no_gloss. "'";
                } else {
                    $query .= "description = '". $ddescription_no_gloss. "'";
                    $need_update = true;
                }
            }
            
            $query .= "WHERE job = ". $this->id; 
            
            $this->mysqli->execute($query);
            return true;
        }
        
        return false;
    }
    
    public function delete() {
        // TODO: Check all dependencies before deleting the entry
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
        if (array_key_exists('group', $criteria)) {
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
        
        $query = "SELECT ". $columns. " FROM jobs ". $joins. 
                  " ". $match. " ". $group. " ". $order. " ". $limit;
        $mysqli = Database::connect();
        return $mysqli->query($query);
    }
    
    public static function calculate_potential_reward_from($_salary, $_employer) {
        if (empty($_salary) || $_salary < 0 || empty($_employer)) {
            return false;
        }
        
        $_salary = $_salary * 12;
        
        $mysqli = Database::connect();
        $query = "SELECT service_fee, premier_fee, reward_percentage, discount FROM employer_fees 
                  WHERE employer = '". $_employer. "' AND 
                  salary_start <= ". $_salary. " AND (salary_end >= ". $_salary. " OR (salary_end = 0 OR salary_end IS NULL)) LIMIT 1";
        $fees = $mysqli->query($query);
        $query = "SELECT SUM(charges) AS extras FROM employer_extras WHERE employer = '". $_employer. "'";
        $extras = $mysqli->query($query);
        
        $total_fees = $discount = $reward_percentage = 0;
        if (!$fees || empty($fees)) {
            return false;
        } else {
            //$total_fees = (($fees[0]['service_fee'] + $fees[0]['premier_fee']) / 100);
            $total_fees = ($fees[0]['service_fee'] / 100);
            $discount = (1.00 - ($fees[0]['discount'] / 100));
            $reward_percentage = ($fees[0]['reward_percentage'] / 100);
        }
        
        $charges = 0;
        if ($extras === false) {
            return false;
        } else {
            if (!empty($extras)) {
                $charges = $extras[0]['extras'];
            }
        }
        
        return ((((($_salary * $total_fees) * $discount)) + $charges) * $reward_percentage);
    }
}
?>