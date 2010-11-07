<?php
require_once dirname(__FILE__). "/../../utilities.php";

class Resume implements Model{
    private $id = 0;
    private $member_id = 0;
    private $mysqli = NULL;
    
    function __construct($_member_id = '', $_id = '') {
        $this->initializeWith($_member_id, $_id);
    }
    
    private function initializeWith($_member_id = '', $_id = '') {
        if (!is_a($this->mysqli, "MySQLi")) {
            $this->mysqli = Database::connect();
        }
        
        $this->id = 0;
        if (!empty($_id)) {
            $this->id = sanitize($_id);
        }
        
        $this->member_id = 0;
        if (!empty($_member_id)) {
            $this->member_id = sanitize($_member_id);
        }
    }
    
    private function hasData($_data) {
        if (is_null($_data) || !is_array($_data)) {
            return false;
        }
        
        return true;
    }
    
    public static function getTextFromRTF($_file) {
        $content = file_get_contents($_file);
        $text = '';
        $is_tag = false;
        for ($i=0; $i < strlen($content); $i++) {
            $char = substr($content, $i, 1);
            if ($char == "\\" && !$is_tag) {
                $is_tag = true;
                continue;
            } elseif (($char == ' ' || $char == "\n") && $is_tag) {
                $is_tag = false;
                continue;
            }

            if (!$is_tag) {
                if ($char != '{' && $char != '}') {
                    $text .= $char;
                }
            }
        }

        return $text;
    }
    
    public static function getTextFromMsword($_userDoc) {
        $fileHandle = fopen($_userDoc, "r");
        $line = @fread($fileHandle, filesize($userDoc));
        $lines = explode(chr(0x0D),$line);
        $outtext = "";
        foreach($lines as $thisline)
          {
            $pos = strpos($thisline, chr(0x00));
            if (($pos !== FALSE)||(strlen($thisline)==0))
              {
              } else {
                $outtext .= $thisline." ";
              }
          }
        $outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$outtext);
        return $outtext;
    }
    
    public function create($_data) {
        if (!$this->hasData($_data)) {
            return false;
        }
        
        $data = sanitize($_data);
        $query = "INSERT INTO resumes SET ";
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "ID" && strtoupper($key) != "MEMBER") {
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
        
        if ($i == 0) {
            $query .= "member = '". $this->member_id. "' ";
        } else {
            $query .= ", member = '". $this->member_id. "' ";
        }
        
        if ($id = $this->mysqli->execute($query, true)) {
            if ($id > 0 && $id != false) {
                $this->id = $id;
                $query = "INSERT INTO resume_index SET 
                          resume = ". $this->id. ", 
                          member = '". $this->member_id. "' ";
                return $this->mysqli->execute($query);
            }
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
            if (strtoupper($key) != "ID" && strtoupper($key) != "MEMBER") {
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
        
        $query = "SELECT COUNT(id) AS is_used FROM referrals 
                  WHERE `resume` = ". $this->id;
        $result = $this->mysqli->query($query);
        if ($result[0]['is_used'] > 0) {
            $query = "UPDATE resumes SET deleted = TRUE WHERE id = ". $this->id;
            $this->id = 0;
            return $this->mysqli->execute($query);
        } else {
            $query = "SELECT file_name FROM resumes WHERE id = ". $this->id. " LIMIT 1";
            if ($result = $this->mysqli->query($query)) {
                if (!empty($result[0]['file_name']) && !is_null($result[0]['file_name'])) {
                    if (!$this->deleteFile()) {
                        return false;
                    }
                }
            }

            $query = "DELETE FROM resume_index WHERE resume = ". $this->id. ";
                      DELETE FROM resumes WHERE id = ". $this->id;
            if ($this->mysqli->transact($query)) {
                $this->id = 0;
                return true;
            }
        }
        
        return false;
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
        $query = "SELECT ". $columns. " FROM resumes ". $joins. 
                 " ". $match. " ". $group. " ". $order. " ". $limit;
        return $mysqli->query($query);
        
    }
    
    public function get() {
        $query = "SELECT * FROM resumes WHERE id = '". $this->id. "' LIMIT 1";
        
        return $this->mysqli->query($query);
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getFileInfo() {
        $resume = array();
        $query = "SELECT member, file_name, file_hash, file_size, file_type
                  FROM resumes 
                  WHERE id = ". $this->id. " LIMIT 1";
        $result = $this->mysqli->query($query);
        if (!is_null($result) && !empty($result) && $result !== false) {
            $resume['member'] = $result[0]['member'];
            $resume['file_name'] = $result[0]['file_name'];
            $resume['file_hash'] = $result[0]['file_hash'];
            $resume['file_size'] = $result[0]['file_size'];
            $resume['file_type'] = $result[0]['file_type'];
        }
        
        return $resume;
    }
    
    public function reset() {
        $this->initializeWith();
    }
    
    public function exists() {
        if ($this->id == 0) {
            return false;
        } 
        
        $query = "SELECT COUNT(id) AS exist FROM resumes WHERE id = ". $this->_id;
        if ($result = $this->mysqli->query($query)) {
            if ($result[0]['exist'] == "1") {
                return true;
            }
        }
        
        return false;
    }
    
    public function getFile() {
        $query = "SELECT file_name, file_hash, file_size, file_type 
                  FROM resumes WHERE id = '". $this->id. "' LIMIT 1";
        if ($result = $this->mysqli->query($query)) {
            $file = array();
            $file['name'] = $result[0]['file_name'];
            $file['hash'] = $result[0]['file_hash'];
            $file['size'] = $result[0]['file_size'];
            $file['type'] = $result[0]['file_type'];
            return $file;
        }
        
        return false;
    }
    
    public function uploadFile($_file_data, $_update = false) {
        if (!is_array($_file_data)) {
            return false;
        }
        
        if ($this->id == 0) {
            return false;
        }
        
        if ($_update) {
            $query = "SELECT file_hash FROM resumes WHERE id = ". $this->id. " LIMIT 1";
            $result = $this->mysqli->query($query);
            $file = $GLOBALS['resume_dir']. '/'. $this->id. '.'. $result[0]['file_hash'];
            @unlink($file);
        }
        
        $type = $_file_data['FILE']['type'];
        $size = $_file_data['FILE']['size'];
        $name = $_file_data['FILE']['name'];
        $temp = $_file_data['FILE']['tmp_name'];
        
        if ($size <= $GLOBALS['resume_size_limit'] && $size > 0) {
            $allowed_type = false;
            
            foreach ($GLOBALS['allowable_resume_types'] as $mime_type) {
                if ($type == $mime_type) {
                    $allowed = true;
                    $hash = generate_random_string_of(6);
                    $new_name = $this->id. ".". $hash;
                    if (move_uploaded_file($temp, $GLOBALS['resume_dir']. "/". $new_name)) {
                        $query = "UPDATE resumes SET 
                                  file_name = '". basename($name)."', 
                                  file_hash = '". $hash."', 
                                  file_size = '". $size."',
                                  file_type = '". $type."'";
                        if ($type == 'application/msword') {
                            $query .= ", needs_indexing = TRUE";
                        }
                        $query .= " WHERE id = ". $this->id;
                        if ($this->mysqli->execute($query)) {
                            //return true;
                            $resume_text = '';
                            switch ($type) {
                                case 'text/plain':
                                    $tmp = file_get_contents($GLOBALS['resume_dir']. "/". $new_name);
                                    $resume_text = sanitize($tmp);
                                    break;
                                case 'text/html':
                                    $tmp = file_get_contents($GLOBALS['resume_dir']. "/". $new_name);
                                    $resume_text = sanitize(strip_tags($tmp));
                                    break;
                                case 'application/pdf':
                                    $cmd = "/usr/local/bin/pdftotext ". $GLOBALS['resume_dir']. "/". $new_name. " /tmp/". $new_name;
                                    shell_exec($cmd);
                                    $tmp = file_get_contents('/tmp/'. $new_name);
                                    $resume_text = sanitize($tmp);
                                    
                                    if (!empty($tmp)) {
                                        unlink('/tmp/'. $new_name);
                                    }
                                    break;
                                case 'application/msword':
                                    // $tmp = Resume::getTextFromMsword($GLOBALS['resume_dir']. "/". $new_name);
                                    // if (empty($tmp)) {
                                    //     $tmp = Resume::getTextFromRTF($GLOBALS['resume_dir']. "/". $new_name);
                                    // }
                                    // $resume_text = sanitize($tmp);
                                    // break;
                                    return true;
                            }
                            
                            if (!empty($resume_text)) {
                                $keywords = preg_split("/[\s,]+/", $resume_text);
                                $resume_text = '';
                                foreach ($keywords as $i=>$keyword) {
                                    $resume_text .= $keyword;
                                    
                                    if ($i < count($keywords)-1) {
                                        $resume_text .= ' ';
                                    }
                                }
                                
                                $query = "SELECT COUNT(*) AS is_exists FROM resume_index 
                                          WHERE resume = ". $this->id. " AND member = '". $this->member_id. "'";
                                $result = $this->mysqli->query($query);
                                if ($result[0]['is_exists'] == '0') {
                                    $query = "INSERT INTO resume_index SET 
                                              resume = ". $this->id. ", 
                                              member = '". $this->member_id. "', 
                                              file_text = '". $resume_text. "'";
                                } else {
                                    $query = "UPDATE resume_index SET file_text = '". $resume_text. "' 
                                              WHERE resume = ". $this->id. " AND 
                                              member = '". $this->member_id. "'";
                                }
                                
                                return $this->mysqli->execute($query);
                            }
                        }
                    }
                }
            }
        }
        
        return false;
    }
    
    public function copyFrom($_original_file, $_file_text) {
        $query = "SELECT COUNT(*) AS is_exists FROM resume_index 
                  WHERE resume = ". $this->id. " AND member = '". $this->member_id. "'";
        $result = $this->mysqli->query($query);
        if ($result[0]['is_exists'] == '0') {
            $query = "INSERT INTO resume_index SET 
                      resume = ". $this->id. ", 
                      member = '". $this->member_id. "', 
                      file_text = '". $_file_text. "'";
        } else {
            $query = "UPDATE resume_index SET file_text = '". $_file_text. "' 
                      WHERE resume = ". $this->id. " AND 
                      member = '". $this->member_id. "'";
        }
        
        if ($this->mysqli->execute($query) === false) {
            return false;
        }
        
        $query = "SELECT file_hash FROM resumes WHERE id = ". $this->id;
        $result = $this->mysqli->query($query);
        $new_hash = $result[0]['file_hash'];
        $new_file = $GLOBALS['resume_dir']. '/'. $this->id. '.'. $new_hash;
        
        @copy($_original_file, $new_file);
        
        return true;
    }
    
    public function deleteFile() {
        $query = "SELECT file_hash FROM resumes WHERE id = ". $this->id;
        if ($result = $this->mysqli->query($query)) {
            $file = $GLOBALS['resume_dir']. "/". $this->id. ".". $result[0]['file_hash'];
            if (unlink($file)) {
                $query = "UPDATE resumes SET 
                          file_name = NULL, 
                          file_hash = NULL, 
                          file_size = NULL, 
                          file_type = NULL
                          WHERE id = ". $this->id;
                return $this->mysqli->execute($query);
            }
        }
        
        return false;
    }
}
?>