<?php
require_once dirname(__FILE__). "/../../utilities.php";

class HeadhunterReferral implements Model {
    private $id = 0;
    private $mysqli = NULL;
    
    function __construct($_id = '') {
        $this->initializeWith($_id);
    }
    
    private function initializeWith($_id = '') {
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
        
        $data = sanitize($_data);
        $query = "INSERT INTO headhunter_referrals SET ";
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "ID") {
                if (is_string($value)) {
                    if (strtoupper($value) == "NULL") {
                        $query .= $key. " = NULL";
                    } else {
                        $query .= $key. " = '". $value. "'";
                    }
                } else if (is_null($value) || empty($value)) {
                    $query .= $key. " = ''";
                } else {
                    $query .= $key. " = ". $value;
                }

                if ($i < count($data) - 1) {
                    $query .= ", ";
                }
            }
            $i++;
        }
        
        $id = $this->mysqli->execute($query, true);
        if ($id > 0 && $id != false) {
            $this->id = $id;
            return true;
        }
        
        return false;
    }
    
    public function update($_data) {
        if (!$this->hasData($_data)) {
            return false;
        }
        
        $data = sanitize($_data);
        $query = "UPDATE resumes SET ";
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "ID") {
                if (is_string($value)) {
                    if (strtoupper($value) == "NULL") {
                        $query .= $key. " = NULL";
                    } else {
                        $query .= $key. " = '". $value. "'";
                    }
                } else if (is_null($value) || empty($value)) {
                    $query .= $key. " = ''";
                } else {
                    $query .= $key. " = ". $value;
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
        if ($this->id == 0) {
            return false;
        }
        
        $query = "SELECT resume_file_hash FROM headhunter_referrals WHERE id = ". $this->id. " LIMIT 1";
        $result = $this->mysqli->query($query);
        $file = $this->id. '.'. $result[0]['resume_file_hash'];
        if (unlink($file) === false) {
            return false;
        }
        
        $query = "DELETE FROM headhunter_referrals WHERE id = ". $this->id;
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
        
        $mysqli = Database::connect();
        $query = "SELECT ". $columns. " FROM headhunter_referrals ". $joins. 
                 " ". $match. " ". $group. " ". $order. " ". $limit;
        return $mysqli->query($query);
        
    }
    
    public function get() {
        $query = "SELECT * FROM headhunter_referrals WHERE id = '". $this->id. "' LIMIT 1";
        
        return $this->mysqli->query($query);
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getFileInfo() {
        $resume = array();
        $query = "SELECT resume_file_name, resume_file_hash, resume_file_size, resume_file_type
                  FROM headhunter_referrals 
                  WHERE id = ". $this->id. " LIMIT 1";
        $result = $this->mysqli->query($query);
        if (!is_null($result) && !empty($result) && $result !== false) {
            $resume['file_name'] = $result[0]['resume_file_name'];
            $resume['file_hash'] = $result[0]['resume_file_hash'];
            $resume['file_size'] = $result[0]['resume_file_size'];
            $resume['file_type'] = $result[0]['resume_file_type'];
        }
        
        return $resume;
    }
    
    public function getMessage() {
        $query = "SELECT employer_message FROM headhunter_referrals WHERE id = ". $this->id. " LIMIT 1";
        $result = $this->mysqli->query($query);
        return $result[0]['employer_message'];
    }
    
    public function uploadFile($_file_data) {
        if (!is_array($_file_data)) {
            return false;
        }
        
        if ($this->id == 0) {
            return false;
        }
        
        $type = $_file_data['FILE']['type'];
        $size = $_file_data['FILE']['size'];
        $name = $_file_data['FILE']['name'];
        $temp = $_file_data['FILE']['tmp_name'];
        
        if ($size <= $GLOBALS['resume_size_limit'] && $size > 0) {
            $allowed_type = false;
            
            foreach ($GLOBALS['allowable_resume_types'] as $mime_type) {
                if ($type == $mime_type) {
                    $allowed_type = true;
                    $hash = generate_random_string_of(6);
                    $new_name = $this->id. ".". $hash;
                    if (move_uploaded_file($temp, $GLOBALS['headhunter_resume_dir']. "/". $new_name)) {
                        $query = "UPDATE headhunter_referrals SET 
                                  resume_file_name = '". basename($name)."', 
                                  resume_file_hash = '". $hash."', 
                                  resume_file_size = '". $size."',
                                  resume_file_type = '". $type."'";
                        $query .= " WHERE id = ". $this->id;
                        return $this->mysqli->execute($query);
                    }
                }
            }
        }
        
        return false;
    }
    
    public function calculateRewardFrom($_salary) {
        if (empty($_salary) || $_salary <= 0) {
            return false;
        }
        
        if ($this->id <= 0) {
            return false;
        }
        
        $query = "SELECT jobs.employer 
                  FROM headhunter_referrals 
                  INNER JOIN jobs ON jobs.id = headhunter_referrals.job
                  WHERE headhunter_referrals.id = ". $this->id;
        $result = $this->mysqli->query($query);
        if (is_null($result[0]['employer']) || empty($result[0]['employer'])) {
            return false;
        }
        $employer = $result[0]['employer'];
        
        $query = "SELECT service_fee, discount, reward_percentage 
                  FROM employer_fees 
                  WHERE employer = '". $employer. "' AND 
                  salary_start <= ". $_salary. " AND 
                  (salary_end >= ". $_salary. " OR (salary_end = 0 OR salary_end = NULL)) LIMIT 1";
        $fees = $this->mysqli->query($query);
        
        $total_fees = $discount = $reward_percentage = 0.00;
        if (!$fees || empty($fees)) {
            return false;
        } else {
            $total_fees = ($fees[0]['service_fee'] / 100.00);
            $discount = (1.00 - ($fees[0]['discount'] / 100.00));
        }
        
        $reward_percentage = ($fees[0]['reward_percentage'] / 100.00);
        $query = "SELECT members.headhunter_reward_percentage 
                  FROM headhunter_referrals 
                  LEFT JOIN members ON members.email_addr = headhunter_referrals.member 
                  WHERE headhunter_referrals.id = ". $this->id;
        $result = $this->mysqli->query($query);
        if ($result[0]['headhunter_reward_percentage'] > 0) {
            $reward_percentage = $result[0]['headhunter_reward_percentage'] / 100.00;
        }
        
        return ((($_salary * $total_fees) * $discount) * $reward_percentage);
    }
}
?>