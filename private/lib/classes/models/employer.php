<?php
require_once dirname(__FILE__). "/../../utilities.php";

class Employer {
    private $id = 0;
    private $seed_id = 0;
    private $mysqli = NULL;
    
    function __construct($_id = "", $_seed_id = "") {
        $this->set($_id, $_seed_id);
    }
    
    private function get_password() {
        $query = "SELECT password FROM employers WHERE id = '". $this->id. "'";
        
        if ($passwords = $this->mysqli->query($query)) {
            return $passwords[0]['password'];
        }
        
        return false;
    }
    
    public static function simple_authenticate(&$_mysqli, $_id, $_password_md5) {
        $query = "SELECT COUNT(*) AS exist FROM employers 
                  WHERE id = '". $_id. "' AND password = '". $_password_md5. "' LIMIT 1";

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
            $this->id = sanitize($_id);
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
    
    public function is_active() {
        $query = "SELECT active FROM employers WHERE id = '". $this->id. "' LIMIT 1";
        if ($result = $this->mysqli->query($query)) {
            if ($result[0]['active'] == 'Y') {
                return true;
            }
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
        
        $query = "SELECT sha1 FROM employer_sessions WHERE employer = '". $this->id. "'";
        
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
            //return false;
        }
        
        $data = sanitize($data);
        $query = "INSERT INTO employers SET ";                
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
        
        if ($i == 0) {
            $query .= "`id` = '". $this->id. "'";
        } else {
            $query .= ", `id` = '". $this->id. "'";
        }
        //return $query;
        if ($this->mysqli->execute($query)) {
            return true;
        }
        
        return false;
    }
    
    public function update($data, $by_admin = false) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        $data = sanitize($data);
        $password_updated = false;
        $query = "UPDATE employers SET ";
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
    
        $query .= "WHERE id = '". $this->id. "'";
        
        if ($this->mysqli->execute($query)) {
            if ($password_updated && !$by_admin) {
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
        
        $query = "SELECT COUNT(employer) AS exist FROM employer_sessions 
                  WHERE employer = '". $this->id. "' LIMIT 1";
        
        if ($sessions = $this->mysqli->query($query)) {
            if ($sessions[0]['exist'] == "1") {
                $query = "SELECT seed FROM seeds WHERE id = ". $this->seed_id;
                if ($seed = $this->mysqli->query($query)) {
                    $sha1 = sha1($this->id. $_password_md5. $seed[0]['seed']);
                    $query = "UPDATE employer_sessions SET sha1 = '". $sha1. "'
                              WHERE employer = '". $this->id. "'";
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
        
        $query = "SELECT COUNT(employer) AS exist FROM employer_sessions 
                  WHERE employer = '". $this->id. "' LIMIT 1";
                  
        if ($sessions = $this->mysqli->query($query)) {
          if ($sessions[0]['exist'] == "1") {
              $query = "UPDATE employer_sessions SET 
                        sha1 = '". $sha1. "', 
                        last_login = NOW() 
                        WHERE employer = '". $this->id. "'";
          } else {
              $query = "INSERT INTO employer_sessions SET 
                        employer = '". $this->id. "', 
                        sha1 = '". $sha1. "', 
                        first_login = NOW(), 
                        last_login = NOW()"; 
          }
          
          return $this->mysqli->execute($query);
        }
        
        return false;
    }
    
    public function get() {
        $query = "SELECT * FROM employers WHERE id = '". $this->id. "' LIMIT 1";
        
        return $this->mysqli->query($query);
    }
    
    public static function get_all() {
        $mysqli = Database::connect();
        $query = "SELECT * FROM employers";
        
        return $mysqli->query($query);
    }
    
    public static function get_all_with_limit($limit, $offset = 0) {
        if (empty($limit) || $limit <= 0) {
            return false;
        }
        
        $mysqli = Database::connect();
        $query = "SELECT * FROM employers ";
        
        if ($offset > 0) {
            $query .= "LIMIT ". $offset. ", ". $limit;
        } else {
            $query .= "LIMIT ". $limit;
        }
        
        return $mysqli->query($query);
    }
    
    public function get_name() {
        $query = "SELECT name FROM employers WHERE id = '". $this->id. "' LIMIT 1";
        if ($name = $this->mysqli->query($query)) {
            return $name[0]['name'];
        }
        
        return false;
    }
    
    public function get_email_address() {
        $query = "SELECT email_addr FROM employers WHERE id = '". $this->id. "' LIMIT 1";
        if ($email_addr = $this->mysqli->query($query)) {
            return $email_addr[0]['email_addr'];
        }
        
        return false;
    }
    
    public function get_country() {
        $query = "SELECT countries.country FROM employers 
                  LEFT JOIN countries ON employers.country = countries.country_code 
                  WHERE employers.id = '". $this->id. "' LIMIT 1";
        if ($name = $this->mysqli->query($query)) {
            return $name[0]['country'];
        }
        
        return false;
    }
    
    public function get_country_code() {
        $query = "SELECT country FROM employers 
                  WHERE id = '". $this->id. "' LIMIT 1";
        if ($name = $this->mysqli->query($query)) {
            return $name[0]['country'];
        }
        
        return false;
    }
    
    public function get_branch() {
        /*$query = "SELECT branches.*, countries.country AS country_name 
                  FROM branches 
                  LEFT JOIN employers ON branches.id = employers.branch 
                  LEFT JOIN countries ON countries.country_code = branches.country 
                  WHERE employers.id = '". $this->id. "' LIMIT 1";
        */
        $query = "SELECT branches.*, countries.country AS country_name 
                  FROM branches 
                  LEFT JOIN employees ON branches.id = employees.branch 
                  LEFT JOIN employers ON employees.id = employers.registered_by 
                  LEFT JOIN countries ON countries.country_code = branches.country 
                  WHERE employers.id = '". $this->id. "' LIMIT 1";
        return $this->mysqli->query($query);
    }
    
    public function get_subscriptions_details() {
        $query = "SELECT DATEDIFF(subscription_expire_on, NOW()) AS expired, 
                  DATE_FORMAT(subscription_expire_on, '%e %b, %Y') AS formatted_expire_on, 
                  subscription_expire_on, subscription_suspended 
                  FROM employers 
                  WHERE id = '". $this->id. "' LIMIT 1";
        return $this->mysqli->query($query);
    }
    
    public function extend_subscription($_period) {
        if ($_period > 0) {
            $result = $this->get_subscriptions_details();
            $query = '';
            
            if ($result[0]['expired'] < 0 || 
                is_null($result[0]['expired']) ||
                empty($result[0]['expired'])) {
                // extend from today
                $query = "UPDATE employers SET 
                          subscription_expire_on = DATE_ADD(NOW(), INTERVAL ". $_period. " MONTH) 
                          WHERE id = '". $this->id. "'";
            } else {
                // extend from the expiry
                $query = "UPDATE employers SET 
                          subscription_expire_on = DATE_ADD(subscription_expire_on, INTERVAL ". $_period. " MONTH) 
                          WHERE id = '". $this->id. "'";
            }
            
            return $this->mysqli->execute($query);
        }
        
        return false;
    }
    
    public function unsuspend_subscription() {
        $query = "UPDATE employers SET subscription_suspended = FALSE 
                  WHERE id = '". $this->id. "'";
        return $this->mysqli->execute($query);
    }
    
    public function suspend_subscription() {
        $query = "UPDATE employers SET subscription_suspended = TRUE 
                  WHERE id = '". $this->id. "'";
        return $this->mysqli->execute($query);
    }
    
    public function has_free_job_postings() {
        $query = "SELECT free_postings_left FROM employers 
                  WHERE id = '". $this->id. "'";
        $result = $this->mysqli->query($query);
        if ($result[0]['free_postings_left'] <= 0) {
            return false;
        }
        
        return $result[0]['free_postings_left'];
    }
    
    public function used_free_job_posting() {
        $query = "UPDATE employers SET free_postings_left = free_postings_left - 1 
                  WHERE id = '". $this->id. "'";
        return $this->mysqli->execute($query);
    }
    
    public function has_paid_job_postings() {
        $query = "SELECT paid_postings_left FROM employers 
                  WHERE id = '". $this->id. "'";
        $result = $this->mysqli->query($query);
        if ($result[0]['paid_postings_left'] <= 0) {
            return false;
        }
        
        return $result[0]['paid_postings_left'];
    }
    
    public function used_paid_job_posting() {
        $query = "UPDATE employers SET paid_postings_left = paid_postings_left - 1 
                  WHERE id = '". $this->id. "'";
        return $this->mysqli->execute($query);
    }
    
    public function add_paid_job_posting($_postings) {
        $query = "UPDATE employers SET paid_postings_left = paid_postings_left + $_postings 
                  WHERE id = '". $this->id. "'";
        return $this->mysqli->execute($query);
    }
    
    public function create_fee($data) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        $data = sanitize($data);
        $query .= "INSERT INTO employer_fees SET employer = '". $this->id. "', ";
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "EMPLOYER" && strtoupper($key) != "ID") {
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
        
        return $this->mysqli->execute($query);
    }
    
    public function create_fees($data) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        $data = sanitize($data);
        $query = "";
        $line = 0;
        foreach ($data as $record) {
            $query .= "INSERT INTO employer_fees SET employer = '". $this->id. "', ";
            $i = 0;
            foreach ($record as $key => $value) {
                if (strtoupper($key) != "EMPLOYER" && strtoupper($key) != "ID") {
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
                    
                    if ($i < count($record) - 1) {
                        $query .= ", ";
                    }
                }
                
                $i++;
            }
            
            if ($line < count($data) - 1) {
                $query .= "; ";
            }
            
            $line++;
        }
        //echo $query;
        return $this->mysqli->transact($query);
    }
    
    public function create_extras($data) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        $data = sanitize($data);
        $query = "";
        $line = 0;
        foreach ($data as $record) {
            $query .= "INSERT INTO employer_extras SET employer = '". $this->id. "', ";
            $i = 0;
            foreach ($record as $key => $value) {
                if (strtoupper($key) != "EMPLOYER" && strtoupper($key) != "ID") {
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
                    
                    if ($i < count($record) - 1) {
                        $query .= ", ";
                    }
                }

                $i++;
            }
            
            if ($line < count($data) - 1) {
                $query .= "; ";
            }
            
            $line++;
        }
        
        return $this->mysqli->transact($query);
    }
    
    public function create_extra($data) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        $data = sanitize($data);
        $query .= "INSERT INTO employer_extras SET employer = '". $this->id. "', ";
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "EMPLOYER" && strtoupper($key) != "ID") {
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
        
        return $this->mysqli->execute($query);
    }
    
    public function update_fee($data) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        if (!array_key_exists('id', $data)) {
            return false;
        }
        
        $data = sanitize($data);
        $query = "UPDATE employer_fees SET ";
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "EMPLOYER" && strtoupper($key) != "ID") {
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
        
        $query .= " WHERE id = '". $data['id']. "'";
        
        return $this->mysqli->execute($query);
    }
    
    public function update_extra($data) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        if (!array_key_exists('id', $data)) {
            return false;
        }
        
        $data = sanitize($data);
        $query = "UPDATE employer_extras SET ";
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "EMPLOYER" && strtoupper($key) != "ID") {
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
        
        $query .= " WHERE id = '". $data['id']. "'";
        
        return $this->mysqli->execute($query);
    }
    
    public function get_fees() {
        $query = "SELECT * FROM employer_fees WHERE employer = '". $this->id. "' ORDER BY salary_start ASC";
        
        return $this->mysqli->query($query);
    }
    
    public function get_extras() {
        $query = "SELECT * FROM employer_extras WHERE employer = '". $this->id. "'";
        
        return $this->mysqli->query($query);
    }
    
    public function get_payment_terms_days() {
        $query = "SELECT payment_terms_days FROM employers WHERE id = '". $this->id. "' LIMIT 1";
        $result = $this->mysqli->query($query);
        
        return $result[0]['payment_terms_days'];
    }
    
    public function delete() {
        // TODO: Check all dependencies before deleting the entry
    }
    
    public function delete_fees() {
        $query = "DELETE FROM employer_fees WHERE employer = '". $this->id. "'";
        
        return $this->mysqli->execute($query);
    }
    
    public function delete_extras() {
        $query = "DELETE FROM employer_extras WHERE employer = '". $this->id. "'";
        
        return $this->mysqli->execute($query);
    }
    
    public function delete_fee($_id) {
        if (empty($id)) {
            return false;
        }
        
        $query = "DELETE FROM employer_fees WHERE id = ". $_id;
        
        return $this->mysqli->execute($query);
    }
    
    public function delete_extra() {
        if (empty($id)) {
            return false;
        }
        
        $query = "DELETE FROM employer_extras WHERE id = ". $_id;
        
        return $this->mysqli->execute($query);
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
        
        $query = "SELECT ". $columns. " FROM employers ". $joins. 
                  " ". $match. " ". $group. " ". $order. " ". $limit;
        
        $mysqli = Database::connect();
        return $mysqli->query($query);
    }
}
?>