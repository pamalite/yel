<?php
require_once dirname(__FILE__). "/../../utilities.php";

class Job implements Model {
    private $id = 0;
    private $mysqli = NULL;
    
    function __construct($_id = "") {
        if (!is_a($this->mysqli, "MySQLi")) {
            $this->mysqli = Database::connect();
        }
        
        $this->id = 0;
        if (!empty($_id)) {
            $this->id = sanitize($_id);
        }
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
    
    public function create($_data) {
        if (!$this->hasData($_data)) {
            return false;
        }
        
        if (!array_key_exists('employer', $_data)) {
            return false;
        }
        
        $description_no_gloss = sanitize(strip_tags($_data['description']));
        $data = sanitize($_data);
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
            
            // get potential reward
            $potential_reward = $this->getPotentialReward();
            $query = "UPDATE jobs SET 
                      potential_reward = ". $potential_reward. " 
                      WHERE id = ". $this->id;
            $this->mysqli->execute($query);
            
            // index the job
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
    
    public function update($_data) {
        if (!$this->hasData($_data)) {
            return false;
        }
        
        $description_no_gloss = "";
        if (array_key_exists('description', $_data)) {
            $description_no_gloss = sanitize(strip_tags($_data['description']));
        }
        
        $data = sanitize($_data);
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
            // re-calculate potential reward
            $potential_reward = $this->getPotentialReward();
            $query = "UPDATE jobs SET 
                      potential_reward = ". $potential_reward. " 
                      WHERE id = ". $this->id;
            $this->mysqli->execute($query);
            
            // re-index the job
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
        $query = "UPDATE jobs SET deleted = TRUE WHERE id = ". $this->id;
        return $this->mysqli->execute($query);
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
        
        $query = "SELECT ". $columns. " FROM jobs ". $joins. 
                  " ". $match. " ". $group. " ". $order. " ". $limit;
        return $this->mysqli->query($query);
    }
    
    public function get() {
        $query = "SELECT * FROM jobs WHERE id = '". $this->id. "' LIMIT 1";
        return $this->mysqli->query($query);
    }
    
    public function getTitle() {
        $query = "SELECT title FROM jobs WHERE id = '". $this->id. "' LIMIT 1";
        $result = $this->mysqli->query($query);
        if ($result !== false) {
            return $result[0]['title'];
        }
        
        return false;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getExpiryDate() {
        $query = "SELECT expire_on FROM jobs WHERE id = ". $this->id. " LIMIT 1";
        $result = $this->mysqli->query($query);
        if ($result === false || is_null($result) || empty($result)) {
            return false;
        }
        
        return $result[0]['expire_on'];
    }
    
    public function incrementViewCount() {
        $query = "UPDATE jobs SET views_count = (views_count + 1) 
                  WHERE id = ". $this->id;
        return $this->mysqli->execute($query);
    }
    
    public function getPotentialReward() {
        if ($this->id == 0 || is_null($this->id) || empty($this->id)) {
            return false;
        }
        
        $query = "SELECT employer, salary, salary_end 
                  FROM jobs 
                  WHERE id = ". $this->id. " LIMIT 1";
        $result = $this->mysqli->query($query);

        $salary = $result[0]['salary'];
        if (!is_null($result[0]['salary_end']) && 
            !empty($result[0]['salary_end']) && 
            $result[0]['salary_end'] > 0) {
            $salary = $result[0]['salary_end'];
        }
        
        if ($salary <= 0) {
            return false;
        }
        
        $salary = $salary * 12;
        $employer = $result[0]['employer'];
        $query = "SELECT service_fee, premier_fee, reward_percentage, discount 
                  FROM employer_fees 
                  WHERE employer = '". $employer. "' AND 
                  salary_start <= ". $salary. " AND 
                  (salary_end >= ". $salary. " OR (salary_end = 0 OR salary_end IS NULL)) LIMIT 1";
        $fees = $this->mysqli->query($query);
        
        $total_fees = $discount = $reward_percentage = 0;
        if (!$fees || empty($fees)) {
            return false;
        } else {
            $total_fees = ($fees[0]['service_fee'] / 100);
            $discount = (1.00 - ($fees[0]['discount'] / 100));
            $reward_percentage = ($fees[0]['reward_percentage'] / 100);
        }
        
        return ((($salary * $total_fees) * $discount) * $reward_percentage);
    }
    
    public function extend() {
        $query = "INSERT INTO job_extensions 
                  SELECT 0, id, created_on, expire_on, for_replacement, invoiced 
                  FROM jobs 
                  WHERE id = ". $this->id;
        if ($this->mysqli->execute($query) === false) {
            return false;
        }
        
        $query = "SELECT expire_on FROM jobs 
                  WHERE id = ". $this->id. " LIMIT 1";
        $result = $this->mysqli->query($query);
        $is_expired = (sql_date_diff($result[0]['expire_on'], now()) <= 0) ? true : false;
        $expire_on = $result[0]['expire_on'];
        if ($is_expired) {
            $expire_on = now();
        }

        $data = array();
        $data['created_on'] = $expire_on;
        $data['expire_on'] = sql_date_add($data['created_on'], 30, 'day');
        $data['closed'] = 'N';
        if ($this->update($data) == false) {
            return false;
        }
        
        return true;
    }
}
?>