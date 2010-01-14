<?php
require_once dirname(__FILE__). "/../../utilities.php";

class Member {
    private $id = 0;
    private $seed_id = 0;
    private $mysqli = NULL;
    
    function __construct($_id = "", $_seed_id = "") {
        $this->set($_id, $_seed_id);
    }
    
    private function get_password() {
        $query = "SELECT password FROM members WHERE email_addr = '". $this->id. "'";
        
        if ($passwords = $this->mysqli->query($query)) {
            return $passwords[0]['password'];
        }
        
        return false;
    }
    
    public static function simple_authenticate(&$_mysqli, $_id, $_password_md5) {
        $query = "SELECT COUNT(*) AS exist FROM members 
                  WHERE email_addr = '". $_id. "' AND password = '". $_password_md5. "' LIMIT 1";

        if ($result = $_mysqli->query($query)) {
            if ($result[0]['exist'] == "1") {
                return true;
            } 
        }
        
        return false;
    }
    
    public static function get_member_name_from_id(&$_mysqli, $_id) {
        $query = "SELECT CONCAT(firstname, ' ', lastname) AS name FROM members 
                  WHERE email_addr = '". $_id. "' LIMIT 1";

        if ($result = $_mysqli->query($query)) {
            return $result[0]['name'];
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
    
    public function is_active() {
        $query = "SELECT active FROM members WHERE email_addr = '". $this->id. "' LIMIT 1";
        if ($result = $this->mysqli->query($query)) {
            if ($result[0]['active'] == 'Y') {
                return true;
            }
        }
        
        return false;
    }
    
    public function is_logged_in($_sha1) {
        if ($_sha1 != sanitize($_sha1)) return false; // A hacking attempt occured.
        
        $query = "SELECT sha1 FROM member_sessions WHERE member = '". $this->id. "'";
        
        if ($result = $this->mysqli->query($query)) {
            return ($result[0]['sha1'] == $_sha1);
        }
        
        return false;
    }
    
    public function get_name() {
        $query = "SELECT CONCAT(firstname, ' ', lastname) AS name FROM members WHERE email_addr = '". $this->id. "' LIMIT 1";
        if ($name = $this->mysqli->query($query)) {
            return $name[0]['name'];
        }
        
        return false;
    }
    
    public function get_country() {
        $query = "SELECT countries.country FROM members 
                  LEFT JOIN countries ON members.country = countries.country_code 
                  WHERE members.email_addr = '". $this->id. "' LIMIT 1";
        if ($name = $this->mysqli->query($query)) {
            return $name[0]['country'];
        }
        
        return false;
    }
    
    public function get_country_code() {
        $query = "SELECT country FROM members WHERE members.email_addr = '". $this->id. "' LIMIT 1";
        if ($code = $this->mysqli->query($query)) {
            return $code[0]['country'];
        }
        
        return false;
    }
    
    public function set_active($_active = true) {
        $_active = ($_active == true) ? 'Y' : 'N';
        $query = "UPDATE members SET active = '". $_active. "' WHERE member = '". $this->id. "' ";
        return $this->mysqli->query($query);
    }
    
    public function get() {
        $query = "SELECT * FROM members WHERE email_addr = '". $this->id. "' LIMIT 1";
        
        return $this->mysqli->query($query);
    }
    
    public static function get_all() {
        $mysqli = Database::connect();
        $query = "SELECT * FROM members";
        
        return $mysqli->query($query);
    }
    
    public static function get_all_with_limit($limit, $offset = 0) {
         if (empty($limit) || $limit <= 0) {
                return false;
            }

            $mysqli = Database::connect();
            $query = "SELECT * FROM members ";

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
        $query = "INSERT INTO members SET ";
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "EMAIL_ADDR") {
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
            $query .= "`email_addr` = '". $this->id. "'";
        } else {
            $query .= ", `email_addr` = '". $this->id. "'";
        }
        
        if ($this->mysqli->execute($query)) {
            return true;
        }
        
        return false;
    }
    
    public function update($data, $_new_member = false) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        $data = sanitize($data);
        $password_updated = false;
        $query = "UPDATE members SET ";
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "EMAIL_ADDR") {
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
    
        $query .= "WHERE `email_addr` = '". $this->id. "'";
        if ($this->mysqli->execute($query)) {
            if ($password_updated && !$_new_member) {
                return $this->session_reset($data['password']);
            }
            return true;
        }
    
        return false;
    }
    
    public function delete() {
        // TODO: Check all dependencies before deleting the entry
    }
    
    public function get_approved_photos() {
        $query = "SELECT * FROM member_photos WHERE member = '". $this->id. "' AND approved = 'Y'";
        return $this->mysqli->query($query);
    }
    
    public function get_photos() {
        $query = "SELECT * FROM member_photos WHERE member = '". $this->id. "'";
        return $this->mysqli->query($query);
    }
    
    public function create_photo($_file_data) {
        $type = $_file_data['FILE']['type'];
        $size = $_file_data['FILE']['size'];
        $temp = $_file_data['FILE']['tmp_name'];
        $image_resolution = getimagesize($temp);
        $max_resolution = $GLOBALS['max_photo_resolution'];
        
        if ($image_resolution[0] > $max_resolution['width'] ||
            $image_resolution[1] > $max_resolution['height']) {
            return false;
        }
        
        if ($size <= $GLOBALS['photo_size_limit'] && $size > 0) {
            $allowed_type = false;
            
            foreach ($GLOBALS['allowable_photo_types'] as $mime_type) {
                if ($type == $mime_type) {
                    $query = "INSERT INTO member_photos SET 
                              member = '". $this->id. "',
                              photo_hash = 'new', photo_type = 'new'";
                    if (($id = $this->mysqli->execute($query, true)) > 0) {
                        $hash = generate_random_string_of(6);
                        $new_name = $id. ".". $hash;
                        if (move_uploaded_file($temp, $GLOBALS['photo_dir']. "/". $new_name)) {
                            $query = "UPDATE member_photos SET 
                                      photo_hash = '". $hash. "',
                                      photo_type = '". $type. "', 
                                      approved = 'N' 
                                      WHERE id = ". $id;
                            return $this->mysqli->execute($query);
                        }
                    }
                }
            }
        }
        
        return false;
    }
    
    public function delete_photo($_id) {
        if (empty($_id)) {
            return false;
        }
        
        $query = "SELECT photo_hash FROM member_photos WHERE id = ". $_id;
        if ($result = $this->mysqli->query($query)) {
            $photo = $GLOBALS['photo_dir']. "/". $_id. ".". $result[0]['photo_hash'];
            if (unlink($photo)) {
                $query = "DELETE FROM member_photos WHERE id = ". $_id;
                return $this->mysqli->execute($query);
            }
        }
        
        return true;
    }
    
    public static function approve_photo($_id) {
        if (empty($_id)) {
            return false;
        }
        
        $query = "UPDATE member_photos SET approved = 'Y' WHERE id = ". $_id;
        $mysqli = Database::connect();
        return $mysqli->execute($query);
    }
    
    private function session_reset($_password_md5) {
        if ($_password_md5 != sanitize($_password_md5)) {
            return false; // A hacking attempt occured?
        }
        
        $query = "SELECT COUNT(member) AS exist FROM member_sessions 
                  WHERE member = '". $this->id. "' LIMIT 1";
        
        if ($sessions = $this->mysqli->query($query)) {
            if ($sessions[0]['exist'] == "1") {
                $query = "SELECT seed FROM seeds WHERE id = ". $this->seed_id;
                if ($seed = $this->mysqli->query($query)) {
                    $sha1 = sha1($this->id. $_password_md5. $seed[0]['seed']);
                    $query = "UPDATE member_sessions SET sha1 = '". $sha1. "'
                              WHERE member = '". $this->id. "'";
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
        
        $query = "SELECT COUNT(member) AS exist FROM member_sessions 
                  WHERE member = '". $this->id. "' LIMIT 1";
                  
        if ($sessions = $this->mysqli->query($query)) {
          if ($sessions[0]['exist'] == "1") {
              $query = "UPDATE member_sessions SET 
                        sha1 = '". $sha1. "', 
                        last_login = NOW() 
                        WHERE member = '". $this->id. "'";
          } else {
              $query = "INSERT INTO member_sessions SET 
                        member = '". $this->id. "', 
                        sha1 = '". $sha1. "', 
                        last_login = NOW()"; 
          }
          
          return $this->mysqli->execute($query);
        }
        
        return false;
    }
    
    public function get_banks() {
        $query = "SELECT * FROM member_banks WHERE member = '". $this->id. "'";
        return $this->mysqli->query($query);
    }
    
    public function create_bank($_bank, $_account) {
        if (empty($_bank) || empty($_account)) {
            return false;
        }
        
        $query = "INSERT INTO member_banks SET 
                  member = '". $this->id. "', 
                  bank = '". $_bank. "',
                  account =  '". $_account. "' ";
        return $this->mysqli->execute($query);
    }
    
    public function update_bank($_id, $_bank, $_account) {
        if (empty($_id) || empty($_bank) || empty($_account)) {
            return false;
        }
        
        $query = "UPDATE member_banks SET 
                  bank = '". $_bank. "',
                  account =  '". $_account. "' 
                  WHERE id = '". $_id. "' ";
        return $this->mysqli->execute($query);
    }
    
    public function delete_bank($_id) {
        if (empty($_id)) {
            return false;
        }
        
        $query = "DELETE FROM member_banks WHERE id = '". $_id. "' ";
        return $this->mysqli->execute($query);
    }
    
    public function get_referees($_order_by = "member_referees.referred_on DESC", $_filter_by = '0') {
        $query = "";
        
        if ($_filter_by <= 0) {
            $query = "SELECT member_referees.*, CONCAT(members.lastname, ', ', members.firstname) AS referee_name 
                      FROM member_referees 
                      LEFT JOIN members ON members.email_addr = member_referees.referee 
                      WHERE member_referees.member = '". $this->id. "' AND 
                      member_referees.referee NOT LIKE 'team.%yellowelevator.com' AND 
                      member_referees.approved = 'Y' ORDER BY ". $_order_by;
        } else {
            $query = "SELECT member_referees.*, CONCAT(members.lastname, ', ', members.firstname) AS referee_name 
                      FROM member_referees 
                      LEFT JOIN member_networks_referees ON member_referees.id = member_networks_referees.referee 
                      LEFT JOIN members ON members.email_addr = member_referees.referee 
                      WHERE member_referees.member = '". $this->id. "' AND 
                      member_referees.referee NOT LIKE 'team.%yellowelevator.com' AND 
                      member_referees.approved = 'Y' AND member_networks_referees.network = " . $_filter_by . " ORDER BY ". $_order_by;
        }
        return $this->mysqli->query($query);
    }
    
    public function create_referee($_referee) {
        if (empty($_referee)) {
            return false;
        }
        
        if (strtoupper($_referee) == strtoupper($this->id)) {
            return false;
        }
        
        $query = "INSERT INTO member_referees SET 
                  member = '". $this->id. "', 
                  referee = '". $_referee. "',
                  referred_on =  NOW() ";
        
        return $this->mysqli->execute($query);
    }
    
    public function delete_referee($_referee) {
        if (empty($_referee)) {
            return false;
        }
        
        $query = "DELETE FROM member_networks_referees WHERE referee = ". $_referee. ";  
                  DELETE FROM member_referees WHERE id = ". $_referee;
        return $this->mysqli->transact($query);
    }
    
    public function hide_referee($_referee, $_hide = false) {
        if (empty($_referee)) {
            return false;
        }
        
        $_hide = ($_hide == true) ? 'Y' : 'N';
        
        $query = "UPDATE member_referees SET hidden = '". $_hide. "' WHERE 
                  member = '". $this->id. "' AND referee = '". $_referee. "'";
        return $this->mysqli->execute($query);
    }
    
    public function get_unapproved_references() {
        $query = "SELECT * FROM member_referees WHERE referee = '". $this->id. "' AND approved = 'N'";
        return $this->mysqli->query($query);
    }
    
    public function approve_reference($_id) {
        if (empty($_id)) {
            return false;
        }
        
        $query = "UPDATE member_referees SET approved = 'Y' WHERE id = ". $_id;
        return $this->mysqli->execute($query);
    }
    
    public function get_networks() {
        $query = "SELECT member_networks.id, industries.industry FROM member_networks 
                  LEFT JOIN industries ON industries.id = member_networks.industry 
                  WHERE member_networks.member = '". $this->id. "' ORDER BY industries.industry";
        return $this->mysqli->query($query);
    }
    
    public function create_network($_industry) {
        if (empty($_industry)) {
            return false;
        }
        
        $query = "SELECT COUNT(id) AS is_exists FROM member_networks WHERE 
                  member = '". $this->id. "' AND industry = '". $_industry. "' ";
        $result = $this->mysqli->query($query);
        if ($result[0]['is_exists'] > 0) {
            return true;
        }
        
        $query = "INSERT INTO member_networks SET 
                  member = '". $this->id. "', 
                  industry = '". $_industry. "' ";
        
        return $this->mysqli->execute($query, true);
    }
    
    public function delete_network($_network) {
        if (empty($_network)) {
            return false;
        }
        
        $query = "DELETE FROM member_networks_referees WHERE network = ". $_network. ";
                  DELETE FROM member_networks WHERE id = ". $_network;
        return $this->mysqli->transact($query);
    }
    
    public function get_referees_from_network($_network) {
        if (empty($_network)) {
            return false;
        }
        
        $query = "SELECT members.email_addr, CONCAT(members.lastname, ', ', members.firstname) AS referee_name 
                  FROM member_networks_referees 
                  LEFT JOIN member_referees ON member_referees.id = member_networks_referees.referee 
                  LEFT JOIN members ON members.email_addr = member_referees.referee 
                  WHERE member_networks_referees.network = ". $_network;
        return $this->mysqli->query($query);
    }
    
    public function add_referee_into_network($_referee, $_network) {
        if (empty($_referee) || empty($_network)) {
            return false;
        }
        
        $query = "INSERT INTO member_networks_referees SET 
                  network = ". $_network. ", 
                  referee = ". $_referee;
        return $this->mysqli->execute($query);
    }
    
    public function delete_referee_from_network($_referee, $_network) {
        if (empty($_referee) || empty($_network)) {
            return false;
        }
        
        $query = "DELETE FROM member_networks_referees WHERE referee = ". $_referee. " AND network = ". $_network;
        return $this->mysqli->execute($query);
    }
    
    public function get_referee_id_from_member_id($_member_id) {
        if (empty($_member_id)) {
            return false;
        }
        
        $query = "SELECT id FROM member_referees WHERE member = '". $this->id. "' AND referee = '". $_member_id. "'";
        if ($result = $this->mysqli->query($query)) {
            return $result[0]['id'];
        }
        
        return false;
    }
    
    public function get_saved_jobs($_order_by = 'member_saved_jobs.saved_on DESC') {
        $query = "SELECT jobs.id, jobs.title, jobs.description, currencies.symbol AS currency, 
                  industries.industry, employers.name AS employer, jobs.potential_reward, 
                  DATE_FORMAT(member_saved_jobs.saved_on, '%e %b, %Y') AS formatted_saved_on, 
                  DATE_FORMAT(jobs.created_on, '%e %b, %Y') AS formatted_created_on, 
                  DATE_FORMAT(jobs.expire_on, '%e %b, %Y') AS formatted_expire_on 
                  FROM member_saved_jobs 
                  LEFT JOIN jobs ON jobs.id = member_saved_jobs.job 
                  LEFT JOIN industries ON industries.id = jobs.industry 
                  LEFT JOIN employers ON employers.id = jobs.employer 
                  LEFT JOIN currencies ON currencies.country_code = employers.country 
                  WHERE member_saved_jobs.member = '". $this->id. "' AND 
                  jobs.closed = 'N' 
                  ORDER BY ". $_order_by;
        return $this->mysqli->query($query);
    }
    
    public function get_saved_jobs_with_filter($_filter_by = '0') {
        $query = "SELECT jobs.id, jobs.title, jobs.description, currencies.symbol AS currency, 
                  industries.industry, employers.name AS employer, jobs.potential_reward, 
                  DATE_FORMAT(member_saved_jobs.saved_on, '%e %b, %Y') AS formatted_saved_on, 
                  DATE_FORMAT(jobs.created_on, '%e %b, %Y') AS formatted_created_on, 
                  DATE_FORMAT(jobs.expire_on, '%e %b, %Y') AS formatted_expire_on
                  FROM member_saved_jobs 
                  LEFT JOIN jobs ON jobs.id = member_saved_jobs.job 
                  LEFT JOIN industries ON industries.id = jobs.industry 
                  LEFT JOIN employers ON employers.id = jobs.employer 
                  LEFT JOIN currencies ON currencies.country_code = employers.country 
                  WHERE member_saved_jobs.member = '". $this->id. "' AND 
                  jobs.closed = 'N' ";
        if ($_filter_by == '0') {
            $query .= "ORDER BY jobs.title";
        } else {
            $query .= "AND jobs.industry = ". $_filter_by. " ORDER BY jobs.title";
        }
        
        return $this->mysqli->query($query);
    }
    
    public function add_to_saved_jobs($_job_id) {
        if (empty($_job_id)) {
            return false;
        }
        
        $query = "INSERT INTO member_saved_jobs SET 
                  member = '". $this->id. "', 
                  job = ". $_job_id. ", 
                  saved_on = NOW()";
        return $this->mysqli->execute($query);
    }
    
    public function remove_from_saved_jobs($_job_id) {
        if (empty($_job_id)) {
            return false;
        }
        
        $query = "DELETE FROM member_saved_jobs WHERE member = '". $this->id. "' AND 
                  job = ". $_job_id;
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
        
        $query = "SELECT ". $columns. " FROM members ". $joins. 
                  " ". $match. " ". $group. " ". $order. " ". $limit;
                  
        $mysqli = Database::connect();
        return $mysqli->query($query);
    }
}
?>