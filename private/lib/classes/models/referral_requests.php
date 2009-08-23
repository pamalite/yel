<?php
require_once dirname(__FILE__). "/../../utilities.php";

class ReferralRequests {
    private $mysqli = NULL;
    
    public static function create($data) {
        $mysqli = Database::connect();
        
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        if (!array_key_exists('member', $data) || 
            !array_key_exists('referrer', $data) || 
            !array_key_exists('job', $data)) {
            return false;
        }
        
        $data = sanitize($data);
        $query = "INSERT INTO referral_requests SET ";                
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
            !array_key_exists('referrer', $data) || 
            !array_key_exists('job', $data)) {
            return false;
        }
        
        if (is_null($data['referrer']) || !is_array($data['referrer'])) {
            return false;
        }
        
        $data = sanitize($data);
        $query = '';
        $j = 0;
        foreach ($data['referrer'] as $referrer) {
            $query .= "INSERT INTO referral_requests SET ";
            $i = 0;
            foreach ($data as $key => $value) {
                if (strtoupper($key) != "ID") {
                    if (strtoupper($key) == "REFERRER") {
                        $value = $referrer['id'];
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
            
            if ($j < count($data['referrer']) - 1) {
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
        $query = "UPDATE referral_requests SET ";
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
        $query = "SELECT * FROM referral_requests WHERE id = '". $_id. "' LIMIT 1";
        
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
                case 'REFERRER':
                    $condition .= "referrer = '". $criteria['referrer']. "'";
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
        $query = "SELECT * FROM referral_requests WHERE ". $condition;
        
        return $mysqli->query($query);
    }
    
    public static function get_all() {
        $mysqli = Database::connect();
        $query = "SELECT * FROM referral_requests";
        
        return $mysqli->query($query);
    }
    
    public static function close_similar_requests_with_id($_id) {
        if (empty($_id) || $_id <= 0) {
            return false;
        }
        
        $mysqli = Database::connect();
        $query = "SELECT id FROM referral_requests WHERE 
                  id <> ". $_id. " AND 
                  job = (SELECT job FROM referral_requests WHERE id = ". $_id. " LIMIT 1) AND 
                  member = (SELECT member FROM referral_requests WHERE id = ". $_id. " LIMIT 1) AND 
                  (referrer_acknowledged_on IS NULL OR referrer_acknowledged_on = '0000-00-00 00:00:00') AND 
                  (acknowledged_by_others_on IS NULL OR acknowledged_by_others_on = '0000-00-00 00:00:00')";
        $result = $mysqli->query($query);
        $id_string = '';
        foreach ($result as $i => $id) {
            $id_string .= $id['id'];
            if ($i < count($result)-1) {
                $id_string .= ',';
            }
        }
        
        if (!empty($id_string)) {
            $query = "UPDATE referral_requests SET
                      acknowledged_by_others_on = '". now(). "' 
                      WHERE id IN (". $id_string. ") AND 
                      (referrer_acknowledged_on IS NULL OR referrer_acknowledged_on = '0000-00-00 00:00:00') AND 
                      (acknowledged_by_others_on IS NULL OR acknowledged_by_others_on = '0000-00-00 00:00:00')";
            return $mysqli->execute($query);
        }
        
        return true;
    }
    
    public static function already_requested($_member, $_referrer, $_job) {
        if (empty($_member) || empty($_referrer) || empty($_job) || $_job <= 0) {
            return false;
        }
        
        $mysqli = Database::connect();
        $query = "SELECT COUNT(*) AS requested FROM referral_requests WHERE 
                  member = '". $_member. "' AND 
                  referrer = '". $_referrer. "' AND 
                  job = ". $_job;
        $result = $mysqli->query($query);
        if ($result[0]['referred'] != '0') {
            return true;
        }
        return false;
    }
}
?>
