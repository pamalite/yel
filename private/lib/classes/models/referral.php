<?php
require_once dirname(__FILE__). "/../../utilities.php";

class Referral implements Model {
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
    
    private function simplify($_data) {
        $_data = htmlspecialchars_decode($_data);
        return str_replace('<br/>', "\n", $_data);
    }
    
    public function create($_data) {
        if (!$this->hasData($_data)) {
            return false;
        }
        
        if (!array_key_exists('member', $_data) || 
            !array_key_exists('referee', $_data) || 
            !array_key_exists('job', $_data)) {
            return false;
        }
        
        $simplified_testimony = '';
        if (array_key_exists('testimony', $_data)) {
            $simplified_testimony = $this->simplify($_data['testimony']);
            $simplified_testimony = addslashes($simplified_testimony);
        }
        
        $data = sanitize($_data);
        $query = "INSERT INTO referrals SET ";                
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
            if (!empty($simplified_testimony)) {
                $query = "INSERT INTO `referral_index` SET 
                          `referral` = ". $this->id. ", 
                          `testimony` = '". $simplified_testimony. "'";
                $this->mysqli->execute($query);
            }
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
        
        $simplified_testimony = '';
        if (array_key_exists('testimony', $_data)) {
            $simplified_testimony = $this->simplify($_data['testimony']);
            $simplified_testimony = addslashes($simplified_testimony);
        }
        
        $data = sanitize($_data);
        $query = "UPDATE referrals SET ";
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
            if (!empty($simplified_testimony)) {
                $query = "UPDATE `referral_index` SET 
                          `testimony` = '". $simplified_testimony. "' 
                          WHERE referral = ". $this->id;
                return $this->mysqli->execute($query);
            } else {
                return true;
            }
        }
        
        return false;
    }
    
    public function delete() {
        // Reserved for future use.
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
        
        $query = "SELECT ". $columns. " FROM referrals ". $joins. 
                  " ". $match. " ". $group. " ". $order. " ". $limit;
        return $this->mysqli->query($query);
    }
    
    public function get() {
        $query = "SELECT * FROM referrals WHERE id = '". $this->id. "' LIMIT 1";
        return $this->mysqli->query($query);
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function calculateRewardFrom($_salary, $_irc_id = NULL) {
        if (empty($_salary) || $_salary <= 0) {
            return false;
        }
        
        if ($this->id <= 0) {
            return false;
        }
        
        $query = "SELECT jobs.employer 
                  FROM referrals 
                  INNER JOIN jobs ON jobs.id = referrals.job
                  WHERE referrals.id = ". $this->id;
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
        if (!is_null($_irc_id)) {
            $query = "SELECT headhunter_reward_percentage 
                      FROM members 
                      WHERE email_addr = '". $_irc_id. "'";
            $result = $this->mysqli->query($query);
            if ($result[0]['headhunter_reward_percentage'] > 0) {
                $reward_percentage = $result[0]['headhunter_reward_percentage'] / 100.00;
            }
        }
        
        return ((($_salary * $total_fees) * $discount) * $reward_percentage);
    }
    
    public function createMultiple($_data) {
        if (!$this->hasData($_data)) {
            return false;
        }
        
        if (!array_key_exists('member', $_data) || 
            !array_key_exists('referee', $_data) || 
            !array_key_exists('job', $_data)) {
            return false;
        }
        
        if (is_null($_data['job']) || !is_array($_data['job'])) {
            return false;
        }
        
        $data = sanitize($_data);
        $query = '';
        $j = 0;
        foreach ($data['job'] as $job) {
            $query .= "INSERT INTO referrals SET ";
            $i = 0;
            foreach ($data as $key => $value) {
                if (strtoupper($key) != "ID") {
                    if (strtoupper($key) == "JOB") {
                        $value = $job['id'];
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
            
            if ($j < count($data['job']) - 1) {
                $query .= "; ";
            }
            
            $j++;
        }
        
        if ($this->mysqli->transact($query)) {
            return true;
        }
        
        return false;
    }
    
    public function getGuaranteeExpiryDateWith($_salary, $_today) {
        if (empty($_salary) || $_salary < 0 || empty($_today)) {
            return false;
        }
        
        if ($this->id <= 0) {
            return false;
        }
        
        $query = "SELECT jobs.employer 
                  FROM referrals 
                  INNER JOIN jobs ON jobs.id = referrals.job 
                  WHERE referrals.id = ". $this->id. " LIMIT 1";
        $result = $this->mysqli->query($query);
        if (is_null($result[0]['employer']) || empty($result[0]['employer'])) {
            return false;
        }
        $employer = $result[0]['employer'];
        
        $query = "SELECT DATE_ADD('". $_today. "', INTERVAL guarantee_months MONTH) AS expiry_date 
                  FROM employer_fees 
                  WHERE employer = '". $employer. "' AND 
                  salary_start <= ". $_salary. " AND 
                  (salary_end >= ". $_salary. " OR (salary_end = 0 OR salary_end IS NULL)) LIMIT 1";
        $result = $this->mysqli->query($query);
        
        return $result[0]['expiry_date'];
    }
    
    public function closeSimilarReferrals() {
        if ($this->id <= 0) {
            return false;
        }
        
        $query = "SELECT id FROM referrals WHERE 
                  id <> ". $this->id. " AND 
                  job = (SELECT job FROM referrals WHERE id = ". $this->id. " LIMIT 1) AND 
                  referee = (SELECT referee FROM referrals WHERE id = ". $this->d. " LIMIT 1) AND 
                  (referee_acknowledged_on IS NULL OR referee_acknowledged_on = '0000-00-00 00:00:00') AND 
                  (referee_acknowledged_others_on IS NULL OR referee_acknowledged_others_on = '0000-00-00 00:00:00')";
        $result = $this->mysqli->query($query);
        $id_string = '';
        foreach ($result as $i => $id) {
            $id_string .= $id['id'];
            if ($i < count($result)-1) {
                $id_string .= ',';
            }
        }
        
        if (!empty($id_string)) {
            $query = "UPDATE referrals SET
                      referee_acknowledged_others_on = '". now(). "' 
                      WHERE id IN (". $id_string. ") AND 
                      (referee_acknowledged_on IS NULL OR referee_acknowledged_on = '0000-00-00 00:00:00') AND 
                      (referee_acknowledged_others_on IS NULL OR referee_acknowledged_others_on = '0000-00-00 00:00:00')";
            return $this->mysqli->execute($query);
        }
        
        return true;
    }
    
    public static function serializeTestimony($_testimony) {
        if (!is_array($_testimony)) {
            return false;
        }
        
        $out = "";
        $i = 0;
        foreach ($_testimony as $testimony) {
            $out .= $testimony;
            
            if ($i < count($_testimony)-1) {
                $out .= "\n";
            }
            
            $i++;
        }
        
        return $out;
    }
    
    public static function isAlreadyReferred($_member, $_candidate, $_job) {
        if (empty($_member) || empty($_candidate) || empty($_job) || $_job <= 0) {
            return NULL;
        }
        
        $mysqli = Database::connect();
        $query = "SELECT COUNT(*) AS referred FROM referrals WHERE 
                  member = '". $_member. "' AND 
                  referee = '". $_candidate. "' AND 
                  job = ". $_job;
        $result = $mysqli->query($query);
        if ($result[0]['referred'] != '0') {
            return true;
        }
        return false;
    }
}
?>
