<?php
require_once dirname(__FILE__). "/../../utilities.php";

class Member implements Model {
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
        $query = "SELECT password FROM members WHERE email_addr = '". $this->id. "'";
        
        if ($passwords = $this->mysqli->query($query)) {
            return $passwords[0]['password'];
        }
        
        return false;
    }
    
    private function resetSessionWithNewPassword($_password_md5) {
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
    
    private function createBankAccount($_bank, $_account) {
        if (empty($_bank) || empty($_account)) {
            return false;
        }
        
        $query = "INSERT INTO member_banks SET 
                  member = '". $this->id. "', 
                  bank = '". $_bank. "',
                  account =  '". $_account. "' ";
        return $this->mysqli->execute($query);
    }
    
    private function updateBankAccount($_id, $_bank, $_account) {
        if (empty($_id) || empty($_bank) || empty($_account)) {
            return false;
        }
        
        $query = "UPDATE member_banks SET 
                  bank = '". $_bank. "',
                  account =  '". $_account. "' 
                  WHERE id = '". $_id. "' ";
        return $this->mysqli->execute($query);
    }
    
    // No longer in use
    // private function deleteBankAccount($_id) {
    //     if (empty($_id)) {
    //         return false;
    //     }
    //     
    //     $query = "DELETE FROM member_banks WHERE id = '". $_id. "' ";
    //     return $this->mysqli->execute($query);
    // }
    
    public function create($_data) {
        if (!$this->hasData($_data)) {
            return false;
        }
        
        $data = sanitize($_data);
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
    
    public function update($_data) {
        if (!$this->hasData($_data)) {
            return false;
        }
        
        $data = sanitize($_data);
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
        $query = "SELECT * FROM members WHERE email_addr = '". $this->id. "' LIMIT 1";
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
        
        $query = "SELECT ". $columns. " FROM members ". $joins. 
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
    
    public function reset() {
        $this->initializeWith();
    }
    
    public function setAdmin($_by_admin) {
        $this->by_admin = $_by_admin;
    }
    
    public function getSeedId() {
        return $this->seed_id;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getPasswordHint() {
        $question = '';
        
        $query = "SELECT password_reset_questions.question 
                  FROM password_reset_questions 
                  LEFT JOIN members ON password_reset_questions.id = members.forget_password_question 
                  WHERE members.email_addr = '". $this->id. "' LIMIT 1";
        $result = $this->mysqli->query($query);
        
        if (is_null($result) || empty($result) || $result === false) {
            return false;
        }
        
        return $result[0]['question'];
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
    
    public function isActive() {
        $query = "SELECT active FROM members WHERE email_addr = '". $this->id. "' LIMIT 1";
        if ($result = $this->mysqli->query($query)) {
            if ($result[0]['active'] == 'Y') {
                return true;
            }
        }
        
        return false;
    }
    
    public function isLoggedIn($_sha1) {
        if ($_sha1 != sanitize($_sha1)) return false; // A hacking attempt occured.
        
        $query = "SELECT sha1 FROM member_sessions WHERE member = '". $this->id. "'";
        
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
        
        $query = "SELECT COUNT(member) AS exist FROM member_sessions 
                  WHERE member = '". $this->id. "' LIMIT 1";
                  
        if ($sessions = $this->mysqli->query($query)) {
          if ($sessions[0]['exist'] == "1") {
              $query = "UPDATE member_sessions SET 
                        sha1 = '". $_sha1. "', 
                        last_login = NOW() 
                        WHERE member = '". $this->id. "'";
          } else {
              $query = "INSERT INTO member_sessions SET 
                        member = '". $this->id. "', 
                        sha1 = '". $_sha1. "', 
                        last_login = NOW()"; 
          }
          
          return $this->mysqli->execute($query);
        }
        
        return false;
    }
    
    public function getFullName() {
        $query = "SELECT CONCAT(firstname, ' ', lastname) AS name 
                  FROM members WHERE email_addr = '". $this->id. "' LIMIT 1";
        if ($name = $this->mysqli->query($query)) {
            return $name[0]['name'];
        }
        
        return false;
    }
    
    public function getPhone() {
        $query = "SELECT phone_num 
                  FROM members WHERE email_addr = '". $this->id. "' LIMIT 1";
        if ($phone = $this->mysqli->query($query)) {
            return $phone[0]['phone_num'];
        }
        
        return false;
    }
    
    public function getCountry() {
        $query = "SELECT members.country, countries.country AS country_name 
                  FROM members 
                  LEFT JOIN countries ON members.country = countries.country_code 
                  WHERE members.email_addr = '". $this->id. "' LIMIT 1";
        if ($name = $this->mysqli->query($query)) {
            return $name[0]['country'];
        }
        
        return false;
    }
    
    public function setActive($_active = true) {
        $_active = ($_active == true) ? 'Y' : 'N';
        $query = "UPDATE members SET active = '". $_active. "' 
                  WHERE email_addr = '". $this->id. "' ";
        return $this->mysqli->query($query);
    }
    
    public function hasResume() {
        $query = "SELECT COUNT(*) AS has_resume 
                  FROM resumes 
                  WHERE member = '". $this->id. "'";
        $result = $this->mysqli->query($query);
        if ($result[0]['has_resume'] > 0) {
            return true;
        }
        
        return false;
    }
    
    public function getApprovedPhotoURL() {
        $query = "SELECT * FROM member_photos 
                  WHERE member = '". $this->id. "' AND approved = 'Y' 
                  LIMIT 1";
        return $this->mysqli->query($query);
    }
    
    public function hasPhoto() {
        $query = "SELECT COUNT(*) AS has_photo 
                  FROM member_photos 
                  WHERE member = '". $this->id. "'";
        $result = $this->mysqli->query($query);
        if ($result[0]['has_photo'] > 0) {
            return true;
        }
        
        return false;
    }
    
    public function isPhotoApproved() {
        $query = "SELECT approved 
                  FROM member_photos 
                  WHERE member = '". $this->id. "'";
        $result = $this->mysqli->query($query);
        if ($result[0]['approved'] == 'Y') {
            return true;
        }
        
        return false;
    }
    
    public function getPhotoFileInfo() {
        $photo = array();
        $query = "SELECT id, photo_hash, photo_type 
                  FROM member_photos WHERE member = '". $this->id. "' LIMIT 1";
        $result = $this->mysqli->query($query);
        if (!is_null($result) && !empty($result) && $result !== false) {
            $photo['id'] = $result[0]['id'];
            $photo['photo_hash'] = $result[0]['photo_hash'];
            $photo['photo_type'] = $result[0]['photo_type'];
        }
        
        return $photo;
    }
    
    public function savePhoto($_file_data) {
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
                    if ($this->hasPhoto()) {
                        $query = "SELECT id, photo_hash FROM member_photos WHERE member = '". $this->id. "' LIMIT 1";
                        $result = $this->mysqli->query($query);
                        $file_name = $result[0]['id']. ".". $result[0]['photo_hash'];
                        if (move_uploaded_file($temp, $GLOBALS['photo_dir']. "/". $file_name)) {
                            $query = "UPDATE member_photos SET 
                                      photo_hash = '". $result[0]['photo_hash']. "',
                                      photo_type = '". $type. "', 
                                      approved = 'N' 
                                      WHERE id = ". $result[0]['id'];
                            return $this->mysqli->execute($query);
                        }
                    } else {
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
        }
        
        return false;
    }
    
    public function deletePhoto($_id) {
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
    
    public function approvePhoto($_id) {
        if (empty($_id)) {
            return false;
        }
        
        if ($this->by_admin) {
            $query = "UPDATE member_photos SET approved = 'Y' WHERE id = ". $_id;
            $mysqli = Database::connect();
            return $mysqli->execute($query);
        }
        
        return false;
    }
    
    public function getBankAccount() {
        $query = "SELECT * FROM member_banks 
                  WHERE member = '". $this->id. "' LIMIT 1";
        return $this->mysqli->query($query);
    }
    
    public function saveBankAccount($_bank, $_account, $_id = 0) {
        if ($_id == 0) {
            // check whether a bank account is already available
            $result = $this->getBankAccount();
            if (count($result) > 0 && !is_null($result) && !empty($result)) {
                // update
                $_id = $result[0]['id'];
                return $this->updateBankAccount($_id, $_bank, $_account);
            }
                        
            // create
            return $this->createBankAccount($_bank, $_account);
        } else {
            // update
            return $this->updateBankAccount($_id, $_bank, $_account);
        }
    }
    
    public function getSavedJobs($_order_by = '', $_filter_by = '0') {
        $order_by = 'member_saved_jobs.saved_on DESC';
        if (!empty($_order_by) && !is_null($_order_by)) {
            $order_by = $_order_by;
        }
        
        $query = "SELECT jobs.id, jobs.title, jobs.description, branches.currency, 
                  industries.industry, employers.name AS employer, jobs.potential_reward, 
                  DATE_FORMAT(member_saved_jobs.saved_on, '%e %b, %Y') AS formatted_saved_on, 
                  DATE_FORMAT(jobs.created_on, '%e %b, %Y') AS formatted_created_on, 
                  DATE_FORMAT(jobs.expire_on, '%e %b, %Y') AS formatted_expire_on 
                  FROM member_saved_jobs 
                  LEFT JOIN jobs ON jobs.id = member_saved_jobs.job 
                  LEFT JOIN industries ON industries.id = jobs.industry 
                  LEFT JOIN employers ON employers.id = jobs.employer 
                  LEFT JOIN branches ON branches.id = employers.branch 
                  WHERE member_saved_jobs.member = '". $this->id. "' AND 
                  jobs.closed = 'N' ";
        
        if ($_filter_by > 0) {
            $query .= "AND jobs.industry = ". $_filter_by. ' ';
        }
        
        $query .= "ORDER BY ". $_order_by;
        return $this->mysqli->query($query);
    }
    
    public function addToSavedJobs($_job_id) {
        if (empty($_job_id)) {
            return false;
        }
        
        $query = "INSERT INTO member_saved_jobs SET 
                  member = '". $this->id. "', 
                  job = ". $_job_id. ", 
                  saved_on = NOW()";
        return $this->mysqli->execute($query);
    }
    
    public function removeFromSavedJobs($_job_id) {
        if (empty($_job_id)) {
            return false;
        }
        
        $query = "DELETE FROM member_saved_jobs WHERE member = '". $this->id. "' AND 
                  job = ". $_job_id;
        return $this->mysqli->execute($query);
    }
    
    public function isIRC() {
        $query = "SELECT individual_headhunter FROM members WHERE 
                  email_addr = '". $this->id. "' LIMIT 1";
        $result = $this->mysqli->query($query);
        if ($result[0]['individual_headhunter'] == '1') {
            return true;
        }
        
        return false;
    }
    
    public function getIndustries() {
        $query = "SELECT industries.id, industries.industry 
                  FROM member_industries 
                  LEFT JOIN industries ON industries.id = member_industries.industry 
                  WHERE member_industries.member = '". $this->id. "'";
        return $this->mysqli->query($query);
    }
    
    public function saveIndustries($_industries) {
        if (!$this->hasData($_industries)) {
            return false;
        }
        
        $query = "DELETE FROM member_industries WHERE member = '". $this->id. "'";
        if ($this->mysqli->execute($query) === false) {
            return false;
        }
        
        $query = '';
        foreach($_industries as $i=>$industry) {
            $query .= "INSERT INTO member_industries SET 
                       member = '". $this->id. "', 
                       industry = ". $industry;
            if ($i < count($_industries)-1) {
                $query .= ";";
            }
        }
        return $this->mysqli->transact($query);
    }
    
    public function getNotes() {
        $query = "SELECT notes FROM member_index WHERE member = '". $this->id. "'";
        $result = $this->mysqli->query($query);
        return $result[0]['notes'];
    }
    
    public function saveNotes($_notes) {
        $simplified_notes = '';
        $simplified_notes = htmlspecialchars_decode($_notes);
        $simplified_notes = str_replace('<br/>', "\n", $simplified_notes);
        $simplified_notes = addslashes($simplified_notes);
        
        $query = "SELECT COUNT(*) AS is_exists FROM member_index WHERE member = '". $this->id. "'";
        $result = $this->mysqli->query($query);
        if ($result[0]['is_exists'] > 0) {
            $query = "UPDATE member_index SET notes = '". $simplified_notes. "' 
                      WHERE member = '". $this->id. "'";
        } else {
            $query = "INSERT INTO member_index SET 
                      notes = '". $simplified_notes. "', 
                      member = '". $this->id. "'";
        }
        
        return $this->mysqli->execute($query);
    }
    
    public function getReferees() {
        $query = "SELECT members.email_addr, CONCAT(members.firstname, ', ', members.lastname) AS referee 
                  FROM members 
                  LEFT JOIN member_referees ON members.email_addr = member_referees.referee 
                  WHERE member_referees.member = '". $this->id. "' 
                  ORDER BY members.lastname ASC";
        return $this->mysqli->query($query);
    }
    
    public function getReferrers() {
        $query = "SELECT members.email_addr, CONCAT(members.firstname, ', ', members.lastname) AS referrer 
                  FROM members 
                  LEFT JOIN member_referees ON members.email_addr = member_referees.member 
                  WHERE member_referees.referee = '". $this->id. "' 
                  ORDER BY members.lastname ASC";
        return $this->mysqli->query($query);
    }
    
    public function removeReferee($_referee_email) {
        $_referee_email = addslashes(stripslashes($_referee_email));
        $query = "DELETE FROM member_referees WHERE 
                  member = '". $this->id. "' AND referee = '". $_referee_email. "'";
        return $this->mysqli->execute($query);
    }
    
    public function removeReferrer($_referrer_email) {
        $_referrer_email = addslashes(stripslashes($_referrer_email));
        $query = "DELETE FROM member_referees WHERE 
                  member = '". $_referrer_email. "' AND referee = '". $this->id. "'";
        return $this->mysqli->execute($query);
    }
    
    public function addReferrer($_referrer_email) {
        $_referrer_email = addslashes(stripslashes($_referrer_email));
        $query = "INSERT INTO member_referees SET 
                  member = '". $_referrer_email. "', 
                  referee = '". $this->id. "'";
        return $this->mysqli->execute($query);
    }
    
    public function addReferee($_referee_email) {
        $_referee_email = addslashes(stripslashes($_referee_email));
        $query = "INSERT INTO member_referees SET 
                  member = '". $this->id. "', 
                  referee = '". $_referee_email. "'";
        return $this->mysqli->execute($query);
    }
    
    public function addJobApplied($_data) {
        if (!$this->hasData($_data)) {
            return false;
        }
        
        $data = sanitize($_data);
        $query = "INSERT INTO member_jobs SET ";
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
        
        if ($i == 0) {
            $query .= "`member` = '". $this->id. "'";
        } else {
            $query .= ", `member` = '". $this->id. "'";
        }
        
        if ($this->mysqli->execute($query)) {
            return true;
        }
        
        return false;
    }
    
    public function getJobsApplied() {
        $query = "SELECT member_jobs.id, jobs.title AS job, employers.name AS employer, 
                  DATE_FORMAT(member_jobs.applied_on, '%e %b, %Y')  AS formatted_requested_on
                  FROM member_jobs 
                  LEFT JOIN jobs ON jobs.id = member_jobs.job
                  LEFT JOIN employers ON employers.id = jobs.employer
                  WHERE member_jobs.member = '". $this->id. "' 
                  ORDER BY member_jobs.applied_on DESC, jobs.title";
        return $this->mysqli->query($query);
    }
    
    public function getProgressNotes() {
        $query = "SELECT progress_notes FROM members WHERE email_addr = '". $this->id. "' LIMIT 1";
        $result = $this->mysqli->query($query);
        return $result[0]['progress_notes'];
    }
    
    public function saveProgressNotes($_notes) {
        $simplified_notes = '';
        $simplified_notes = htmlspecialchars_decode($_notes);
        $simplified_notes = str_replace('<br/>', "\n", $simplified_notes);
        $simplified_notes = addslashes($simplified_notes);
        
        $query = "UPDATE members SET progress_notes = '". $simplified_notes. "' 
                  WHERE email_addr = '". $this->id. "'";
        return $this->mysqli->execute($query);
    }
}
?>
