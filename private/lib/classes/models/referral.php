<?php
require_once dirname(__FILE__). "/../../utilities.php";

class Referral {
    private $mysqli = NULL;
    
    public static function create($data) {
        $mysqli = Database::connect();
        
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        if (!array_key_exists('member', $data) || 
            !array_key_exists('referee', $data) || 
            !array_key_exists('job', $data)) {
            return false;
        }
        
        $data = sanitize($data);
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
        
        if (($id = $mysqli->execute($query, true)) > 0) {
            return $id;
        }
        
        return false;
    }
    
    public static function create_multiple($data) {
        $mysqli = Database::connect();
        
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        if (!array_key_exists('member', $data) || 
            !array_key_exists('referee', $data) || 
            !array_key_exists('job', $data)) {
            return false;
        }
        
        if (is_null($data['job']) || !is_array($data['job'])) {
            return false;
        }
        
        $data = sanitize($data);
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
        
        if ($mysqli->transact($query)) {
            return true;
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
    
        $query .= "WHERE id = '". $data['id']. "'";
    
         return $mysqli->execute($query);
    }
    
    public static function get($_id) {
        $mysqli = Database::connect();
        $query = "SELECT * FROM referrals WHERE id = '". $_id. "' LIMIT 1";
        
        return $mysqli->query($query);
    }
    
    public static function get_by($criteria) {
        if (is_null($criteria) || !is_array($criteria)) {
            return false;
        }
        
        $condition = "";
        $i = 0;
        foreach ($criteria as $key => $value) {
            switch (strtoupper($key)) {
                case 'MEMBER':
                    $condition .= "member = '". $criteria['member']. "'";
                    break;
                case 'REFEREE':
                    $condition .= "referee = '". $criteria['referee']. "'";
                    break;
                case 'JOB':
                    $condition .= "job = ". $criteria['job'];
                    break;
            }
            
            if ($i < count($criteria)-1) {
                $condition .= " AND ";
            }
            
            $i++;
        }
        
        $mysqli = Database::connect();
        $query = "SELECT * FROM referrals WHERE ". $condition;
        
        return $mysqli->query($query);
    }
    
    public static function get_all() {
        $mysqli = Database::connect();
        $query = "SELECT * FROM referrals";
        
        return $mysqli->query($query);
    }
    
    public static function calculate_total_reward_from($_salary, $_employer) {
        if (empty($_salary) || $_salary < 0 || empty($_employer)) {
            return false;
        }
        
        $mysqli = Database::connect();
        $query = "SELECT service_fee, premier_fee, discount, reward_percentage FROM employer_fees 
                  WHERE employer = '". $_employer. "' AND 
                  salary_start <= ". $_salary. " AND (salary_end >= ". $_salary. " OR (salary_end = 0 OR salary_end = NULL)) LIMIT 1";
        $fees = $mysqli->query($query);
        //print_r($fees);
        $query = "SELECT SUM(charges) AS extras FROM employer_extras WHERE employer = '". $_employer. "'";
        $extras = $mysqli->query($query);
        //print_r($extras);
        $total_fees = $discount = $reward_percentage = 0.00;
        if (!$fees || empty($fees)) {
            return false;
        } else {
            //$total_fees = (($fees[0]['service_fee'] + $fees[0]['premier_fee']) / 100.00);
            $total_fees = ($fees[0]['service_fee'] / 100.00);
            $discount = (1.00 - ($fees[0]['discount'] / 100.00));
        }
        
        $charges = 0.00;
        if ($extras == false) {
            return false;
        } else {
            if (!empty($extras)) {
                $charges = $extras[0]['extras'];
            }
        }
        
        $reward_percentage = ($fees[0]['reward_percentage'] / 100.00);
        
        return (((($_salary * $total_fees) * $discount) + $charges) * $reward_percentage);
    }
    
    public static function serialize_testimony($_testimony) {
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
    
    public static function get_guarantee_expiry_date_from($_salary, $_employer, $_today) {
        if (empty($_salary) || $_salary < 0 || empty($_employer) || empty($_today)) {
            return false;
        }
        
        $mysqli = Database::connect();
        $query = "SELECT DATE_ADD('". $_today. "', INTERVAL guarantee_months MONTH) AS expiry_date 
                  FROM employer_fees 
                  WHERE employer = '". $_employer. "' AND 
                  salary_start <= ". $_salary. " AND (salary_end >= ". $_salary. " OR (salary_end = 0 OR salary_end IS NULL)) LIMIT 1";
        $result = $mysqli->query($query);
        
        return $result[0]['expiry_date'];
    }
    
    public static function close_similar_referrals_with_id($_id) {
        if (empty($_id) || $_id <= 0) {
            return false;
        }
        
        $mysqli = Database::connect();
        $query = "SELECT id FROM referrals WHERE 
                  id <> ". $_id. " AND 
                  job = (SELECT job FROM referrals WHERE id = ". $_id. " LIMIT 1) AND 
                  referee = (SELECT referee FROM referrals WHERE id = ". $_id. " LIMIT 1) AND 
                  (referee_acknowledged_on IS NULL OR referee_acknowledged_on = '0000-00-00 00:00:00') AND 
                  (referee_acknowledged_others_on IS NULL OR referee_acknowledged_others_on = '0000-00-00 00:00:00')";
        $result = $mysqli->query($query);
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
            return $mysqli->execute($query);
        }
        
        return true;
    }
    
    public static function already_referred($_member, $_candidate, $_job) {
        if (empty($_member) || empty($_candidate) || empty($_job) || $_job <= 0) {
            return false;
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
