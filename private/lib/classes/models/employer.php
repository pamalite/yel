<?php
require_once dirname(__FILE__). "/../../utilities.php";

class Employer implements Model {
    private $id = 0;
    private $seed_id = 0;
    private $mysqli = NULL;
    private $by_admin = false;
    
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
    
    private function getPasswordHash() {
        $query = "SELECT password FROM employers WHERE id = '". $this->id. "'";

        if ($passwords = $this->mysqli->query($query)) {
           return $passwords[0]['password'];
        }

        return false;
    }
    
    private function resetSessionWithNewPassword($_password_md5) {
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
    
    public function create($_data) {
        if (!$this->hasData($_data)) {
            return false;
        }
        
        $data = sanitize($_data);
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
        
        if ($this->mysqli->execute($query)) {
            return true;
        }
        
        return false;
    }
    
    public function update($_data) {
        if (!$this->hasData($_data)) {
            return false;
        }
        
        $data = sanitize($_data);
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
            if ($password_updated && !$this->by_admin) {
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
        $query = "SELECT *, 
                  date_format(subscription_expire_on, '%e %b, %Y') AS formatted_subscription_expire_on, 
                  datediff(now(), subscription_expire_on) AS is_expired 
                  FROM employers WHERE id = '". $this->id. "' LIMIT 1";
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
        
        $query = "SELECT ". $columns. " FROM employers ". $joins. 
                  " ". $match. " ". $group. " ". $order. " ". $limit;
        
        return $this->mysqli->query($query);
    }
    
    public function initializeWith($_id = "", $_seed_id = "") {
        $this->id = 0;
        $this->seed_id = 0;
        
        if (!empty($_id)) {
            $this->id = sanitize($_id);
        }

        if (!empty($_seed_id)) {
            $this->seed_id = sanitize($_seed_id);
        } 
    }
    
    public function setAdmin($_by_admin) {
        $this->by_admin = $_by_admin;
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
    
    public function isActive() {
        $query = "SELECT active FROM employers WHERE id = '". $this->id. "' LIMIT 1";
        if ($result = $this->mysqli->query($query)) {
            if ($result[0]['active'] == 'Y') {
                return true;
            }
        }
        
        return false;
    }
    
    public function isNew() {
        $query = "SELECT is_new FROM employers WHERE id = '". $this->id. "' LIMIT 1";
        if ($result = $this->mysqli->query($query)) {
            if ($result[0]['is_new'] == '1') {
                return true;
            }
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
        
        $query = "SELECT sha1 FROM employer_sessions WHERE employer = '". $this->id. "'";
        
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
        
        $query = "SELECT COUNT(employer) AS exist FROM employer_sessions 
                  WHERE employer = '". $this->id. "' LIMIT 1";
                  
        if ($sessions = $this->mysqli->query($query)) {
          if ($sessions[0]['exist'] == "1") {
              $query = "UPDATE employer_sessions SET 
                        sha1 = '". $_sha1. "', 
                        last_login = NOW() 
                        WHERE employer = '". $this->id. "'";
          } else {
              $query = "INSERT INTO employer_sessions SET 
                        employer = '". $this->id. "', 
                        sha1 = '". $_sha1. "', 
                        first_login = NOW(), 
                        last_login = NOW()"; 
          }
          
          return $this->mysqli->execute($query);
        }
        
        return false;
    }
    
    public function getName() {
        $query = "SELECT name FROM employers WHERE id = '". $this->id. "' LIMIT 1";
        if ($name = $this->mysqli->query($query)) {
            return $name[0]['name'];
        }
        
        return false;
    }
    
    public function getEmailAddress() {
        $query = "SELECT email_addr FROM employers WHERE id = '". $this->id. "' LIMIT 1";
        if ($email_addr = $this->mysqli->query($query)) {
            return $email_addr[0]['email_addr'];
        }
        
        return false;
    }
    
    public function getCountryCode() {
        $query = "SELECT country FROM employers 
                  WHERE id = '". $this->id. "' LIMIT 1";
        if ($name = $this->mysqli->query($query)) {
            return $name[0]['country'];
        }
        
        return false;
    }
    
    public function getAssociatedBranch() {
        $query = "SELECT branches.*, countries.country AS mailing_country_name 
                  FROM branches 
                  LEFT JOIN employees ON branches.id = employees.branch 
                  LEFT JOIN employers ON employees.id = employers.registered_by 
                  LEFT JOIN countries ON countries.country_code = branches.mailing_country 
                  WHERE employers.id = '". $this->id. "' LIMIT 1";
        return $this->mysqli->query($query);
    }
    
    public function getSubscriptionsDetails() {
        $query = "SELECT DATEDIFF(subscription_expire_on, NOW()) AS expired, 
                  DATE_FORMAT(subscription_expire_on, '%e %b, %Y') AS formatted_expire_on, 
                  subscription_expire_on, subscription_suspended 
                  FROM employers 
                  WHERE id = '". $this->id. "' LIMIT 1";
        return $this->mysqli->query($query);
    }
    
    public function extendSubscription($_period) {
        if ($_period > 0) {
            $result = $this->getSubscriptionsDetails();
            $query = "";
            
            if (is_null($result[0]['expired']) || empty($result[0]['expired']) || 
                $result[0]['expired'] < 0) {
                // extend from today
                $query = "UPDATE employers SET 
                          subscription_expire_on = DATE_ADD(NOW(), INTERVAL ". $_period. " MONTH), 
                          subscription_suspended = FALSE 
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
    
    public function unsuspendSubscription() {
        $query = "UPDATE employers SET subscription_suspended = FALSE 
                  WHERE id = '". $this->id. "'";
        return $this->mysqli->execute($query);
    }
    
    public function suspendSubscription() {
        $query = "UPDATE employers SET subscription_suspended = TRUE 
                  WHERE id = '". $this->id. "'";
        return $this->mysqli->execute($query);
    }
    
    public function hasFreeJobPostings() {
        $query = "SELECT free_postings_left FROM employers 
                  WHERE id = '". $this->id. "'";
        $result = $this->mysqli->query($query);
        if ($result[0]['free_postings_left'] <= 0) {
            return false;
        }
        
        return $result[0]['free_postings_left'];
    }
    
    public function usedFreeJobPosting() {
        $query = "UPDATE employers SET free_postings_left = free_postings_left - 1 
                  WHERE id = '". $this->id. "'";
        return $this->mysqli->execute($query);
    }
    
    public function addFreeJobPosting($_postings) {
        $query = "UPDATE employers SET free_postings_left = free_postings_left + (". $_postings. ") 
                  WHERE id = '". $this->id. "'";
        return $this->mysqli->execute($query);
    }
    
    public function hasPaidJobPostings() {
        $query = "SELECT paid_postings_left FROM employers 
                  WHERE id = '". $this->id. "'";
        $result = $this->mysqli->query($query);
        if ($result[0]['paid_postings_left'] <= 0) {
            return false;
        }
        
        return $result[0]['paid_postings_left'];
    }
    
    public function usedPaidJobPosting() {
        $query = "UPDATE employers SET paid_postings_left = paid_postings_left - 1 
                  WHERE id = '". $this->id. "'";
        return $this->mysqli->execute($query);
    }
    
    public function addPaidJobPosting($_postings) {
        $query = "UPDATE employers SET paid_postings_left = paid_postings_left + (". $_postings. ") 
                  WHERE id = '". $this->id. "'";
        return $this->mysqli->execute($query);
    }
    
    public function createFee($_data) {
        if (!$this->hasData($_data)) {
            return false;
        }
        
        $data = sanitize($_data);
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
    
    public function createFees($_data) {
        if (!$this->hasData($_data)) {
            return false;
        }
        
        $data = sanitize($_data);
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
        
        return $this->mysqli->transact($query);
    }
    
    public function updateFee($_data) {
        if (!$this->hasData($_data)) {
            return false;
        }
        
        if (!array_key_exists('id', $_data)) {
            return false;
        }
        
        $data = sanitize($_data);
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
                
                if ($i < count($data) - 2) {
                    $query .= ", ";
                }
                
            }

            $i++;
        }
        
        $query .= " WHERE id = '". $data['id']. "'";
        
        return $this->mysqli->execute($query);
    }
    
    public function getFees() {
        $query = "SELECT * FROM employer_fees WHERE employer = '". $this->id. "' ORDER BY salary_start ASC";
        return $this->mysqli->query($query);
    }
    
    public function getPaymentTermsInDays() {
        $query = "SELECT payment_terms_days FROM employers WHERE id = '". $this->id. "' LIMIT 1";
        $result = $this->mysqli->query($query);
        
        return $result[0]['payment_terms_days'];
    }
    
    public function deleteFees() {
        $query = "DELETE FROM employer_fees WHERE employer = '". $this->id. "'";
        
        return $this->mysqli->execute($query);
    }
    
    public function deleteFee($_id) {
        if (empty($_id)) {
            return false;
        }
        
        $query = "DELETE FROM employer_fees WHERE id = ". $_id;
        
        return $this->mysqli->execute($query);
    }
    
    public function getJobs($_order = '', $_limit = 0, $_offset = 0) {
        $order = "ORDER BY ";
        if (empty($_order)) {
            $order .= "created_on DESC";
        } else {
            $order .= $_order;
        }
        
        $limit = "";
        if ($_limit > 0) {
            if ($_offset > 0) {
                $limit = "LIMIT ". $_offset. ", ". $_limit;
            } else {
                $limit = "LIMIT ". $_limit;
            }
        }
        
        $query = "SELECT jobs.id, jobs.title, 
                  IFNULL(MIN(job_extensions.previously_created_on), jobs.created_on) AS created_on, 
                  IFNULL(DATE_FORMAT(MIN(job_extensions.previously_created_on), '%e %b, %Y'), DATE_FORMAT(jobs.created_on, '%e %b, %Y')) AS formatted_created_on, 
                  DATEDIFF(jobs.expire_on, NOW()) AS expired, 
                  DATE_FORMAT(jobs.expire_on, '%e %b, %Y') AS formatted_expire_on 
                  FROM jobs  
                  LEFT JOIN job_extensions ON job_extensions.job = jobs.id 
                  WHERE jobs.employer = '". $this->id. "' AND 
                  jobs.deleted = FALSE 
                  GROUP BY jobs.id 
                  ". $order. " ". $limit;
        return $this->mysqli->query($query);
    }
}
?>
