<?php
require_once dirname(__FILE__). "/../../utilities.php";

class ReferralBuffer implements Model {
    private $mysqli = NULL;
    private $id = 0;
    
    public function __construct($_id = "") {
        if (!is_a($this->mysqli, "MySQLi")) {
            $this->mysqli = Database::connect();
        }
        
        $this->id = 0;
        if (!empty($_id)) {
            $this->id = sanitize($_id);
        }
    }
    
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
        
        if (!array_key_exists('referrer_email', $_data) || 
            !array_key_exists('candidate_email', $_data) || 
            !array_key_exists('job', $_data)) {
            return false;
        }
        
        $data = sanitize($_data);
        $query = "INSERT INTO referral_buffers SET ";                
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
        
        if (($this->id = $this->mysqli->execute($query, true)) > 0) {
            return $this->id;
        }
        
        return false;
    }
    
    public function update($_data) {
        if (!$this->hasData($_data)) {
            return false;
        }
        
        if ($this->id <= 0) {
            return false;
        }
        
        $data = sanitize($_data);
        $query = "UPDATE referral_buffers SET ";
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
        $query .= "WHERE id = ". $this->id;
    
        return $this->mysqli->execute($query);
    }
    
    public function delete() {
        $query = "SELECT resume_file_hash, existing_resume_id AS `resume` 
                  FROM referral_buffers 
                  WHERE id = ". $this->id. " LIMIT 1";
        $result = $this->mysqli->query($query);
        
        if (!is_null($result[0]['resume_file_hash'])) {
            $file = $GLOBALS['buffered_resume_dir']. '/'. $this->id. '.'. $result[0]['resume_file_hash'];
            @unlink($file);
        } elseif (!is_null($result[0]['resume'])) {
            $query = "UPDATE resumes SET deleted = 'Y' WHERE id = ". $result[0]['resume'];
            $this->mysqli->execute($query);
            
            $query = "SELECT COUNT(id) AS is_used FROM referrals WHERE `resume` = ". $result[0]['resume'];
            $result = $this->mysqli->query($query);
            if ($result[0]['is_used'] <= 0) {
                $query = "DELETE FROM resumes WHERE id = ". $result[0]['resume'];
                $this->mysqli->execute($query);
            }
        }
        
        $query = "DELETE FROM referral_buffers WHERE id = ". $this->id;
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
        
        $query = "SELECT ". $columns. " FROM referral_buffers ". $joins. 
                  " ". $match. " ". $group. " ". $order. " ". $limit;
        return $this->mysqli->query($query);
    }
    
    public function get() {
        $query = "SELECT * FROM referral_buffers WHERE id = ". $this->id. " LIMIT 1";
        return $this->mysqli->query($query);
    }
    
    public function getId() {
        return $this->id;
    }    
}
?>
