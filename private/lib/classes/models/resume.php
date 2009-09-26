<?php
require_once dirname(__FILE__). "/../../utilities.php";

class Resume {
    private $id = 0;
    private $member_id = 0;
    private $mysqli = NULL;
    
    function __construct($_member_id, $_id = "") {
        $this->set($_member_id, $_id);
    }
    
    public function set($_member_id, $_id = "") {
        if (is_a($this->mysqli, "MySQLi")) {
            $this->mysqli->close();
        }
        
        $this->mysqli = Database::connect();
        $this->id = 0;
        $this->member_id = 0;
        
        if (!empty($_id)) {
            $this->id = sanitize($_id);
        } 
        
        if (!empty($_member_id)) {
            $this->member_id = sanitize($_member_id);
        } 
    }
    
    public function reset() {
        $this->set();
    }
    
    public function id() {
        return $this->id;
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
    
    public function is_private() {
        if ($this->_id == 0) {
            return false;
        } 
        
        $query = "SELECT private FROM resumes WHERE id = ". $this->_id;
        if ($result = $this->mysqli->query($query)) {
            if ($result[0]['private'] == "Y") {
                return true;
            }
        }
        
        return false;
    }
    
    public function get() {
        $query = "SELECT * FROM resumes WHERE id = '". $this->id. "' LIMIT 1";
        
        return $this->mysqli->query($query);
    }
    
    public function get_name() {
        $query = "SELECT name FROM resumes WHERE id = '". $this->id. "' LIMIT 1";
        if ($name = $this->mysqli->query($query)) {
            return $name[0]['name'];
        }
        
        return false;
    }
    
    public function get_cover_note() {
        $query = "SELECT name, private, cover_note FROM resumes WHERE id = '". $this->id. "' LIMIT 1";
        if ($cover_note = $this->mysqli->query($query)) {
            return $cover_note[0];
        }
        
        return false;
    }
    
    public function get_file() {
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
    
    public function create($data) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        $data = sanitize($data);
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
                          member = '". $this->member_id. "', 
                          cover_note = '". $data['cover_note']. "' ";
                return $this->mysqli->execute($query);
            }
        }
        
        return false;
    }
    
    public function update($data) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        $data = sanitize($data);
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
        
        if ($this->mysqli->execute($query)) {
            $query = "UPDATE resume_index SET 
                      cover_note = '". $data['cover_note']. "'
                      WHERE resume = ". $this->id. " AND member = '". $this->member_id. "'";
            return $this->mysqli->execute($query);
        }
    
        return false;
    }
    
    public function delete() {
        if ($this->id == 0) {
            return false;
        }
        
        $query = "SELECT file_name FROM resumes WHERE id = ". $this->id. " LIMIT 1";
        if ($result = $this->mysqli->query($query)) {
            if (!empty($result[0]['file_name']) && !is_null($result[0]['file_name'])) {
                if (!$this->delete_file()) {
                    return false;
                }
            }
        }
        
        $query = "DELETE FROM resume_technical_skills WHERE resume = ". $this->id. "; 
                  DELETE FROM resume_skills WHERE resume = ". $this->id. "; 
                  DELETE FROM resume_educations WHERE resume = ". $this->id. "; 
                  DELETE FROM resume_work_experiences WHERE resume = ". $this->id. "; 
                  DELETE FROM resume_index WHERE resume = ". $this->id. ";
                  DELETE FROM resumes WHERE id = ". $this->id. ";
                  DELETE FROM resume_index WHERE resume = ". $this->id. ";";
        if ($this->mysqli->transact($query)) {
            $this->id = 0;
            return true;
        }
        
        return false;
    }
    
    private function get_text_from_rtf($_file) {
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
    
    private function get_text_from_msword($userDoc) {
        $fileHandle = fopen($userDoc, "r");
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
    
    public function upload_file($_file_data, $_update = false) {
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
            unlink($file);
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
                                  file_type = '". $type."' 
                                  WHERE id = ". $this->id;
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
                                    $tmp = $this->get_text_from_msword($GLOBALS['resume_dir']. "/". $new_name);
                                    $resume_text = sanitize($tmp);
                                    break;
                                case 'application/rtf':
                                    $tmp = $this->get_text_from_rtf($GLOBALS['resume_dir']. "/". $new_name);
                                    $resume_text = sanitize($tmp);
                                    break;
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
    
    public function delete_file() {
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
    
    public function get_work_experiences() {
        $query = "SELECT * FROM resume_work_experiences WHERE resume = ". $this->id;
        
        return $this->mysqli->query($query);
    }
    
    public function create_work_experience($data) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        $data = sanitize($data);
        $query = "INSERT INTO resume_work_experiences SET ";                
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "ID" && strtoupper($key) != "RESUME") {
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
            $query .= "resume = '". $this->id. "' ";
        } else {
            $query .= ", resume = '". $this->id. "' ";
        }
        
        if ($this->mysqli->execute($query)) {
            $query = "SELECT work_summary FROM resume_work_experiences WHERE resume = ". $this->id;
            $results = $this->mysqli->query($query);
            $work_summary = "";
            $i = 0;
            foreach ($results as $result) {
                $work_summary .= $result['work_summary'];
                if ($i < count($results)-1) {
                    $work_summary .= " ";
                }
                $i++;
            }
            
            $query = "UPDATE resume_index SET 
                      work_summary = '". $work_summary. "' 
                      WHERE resume = ". $this->id. " AND member = '". $this->member_id. "'";
            return $this->mysqli->execute($query);
        }
        
        return false;
    }
    
    public function update_work_experience($data) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        if (!array_key_exists("id", $data)) {
            return false;
        }
        
        $data = sanitize($data);
        $query = "UPDATE resume_work_experiences SET ";                
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "ID" && strtoupper($key) != "RESUME") {
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
        
        $query .= "WHERE id = ". $data['id'];
        
        if ($this->mysqli->execute($query)) {
            $query = "SELECT work_summary FROM resume_work_experiences WHERE resume = ". $this->id;
            $results = $this->mysqli->query($query);
            $work_summary = "";
            $i = 0;
            foreach ($results as $result) {
                $work_summary .= $result['work_summary'];
                if ($i < count($results)-1) {
                    $work_summary .= " ";
                }
                $i++;
            }
            
            $query = "UPDATE resume_index SET 
                      work_summary = '". $work_summary. "' 
                      WHERE resume = ". $this->id. " AND member = '". $this->member_id. "'";
            return $this->mysqli->execute($query);
        }
        
        return false;
    }
    
    public function delete_work_experience($_id) {
        if (empty($_id)) {
            return false;
        }
        
        $query = "DELETE FROM resume_work_experiences WHERE id = ". $_id;
        if ($this->mysqli->execute($query)) {
            $query = "SELECT work_summary FROM resume_work_experiences WHERE resume = ". $this->id;
            $results = $this->mysqli->query($query);
            $work_summary = "";
            $i = 0;
            foreach ($results as $result) {
                $work_summary .= $result['work_summary'];
                if ($i < count($results)-1) {
                    $work_summary .= " ";
                }
                $i++;
            }
            
            $query = "UPDATE resume_index SET 
                      work_summary = '". $work_summary. "' 
                      WHERE resume = ". $this->id. " AND member = '". $this->member_id. "'";
            return $this->mysqli->execute($query);
        }
        
        return false;
    }
    
    public function get_educations() {
        $query = "SELECT * FROM resume_educations WHERE resume = ". $this->id;
        
        return $this->mysqli->query($query);
    }
    
    public function create_education($data) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        $data = sanitize($data);
        $query = "INSERT INTO resume_educations SET ";                
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "ID" && strtoupper($key) != "RESUME") {
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
            $query .= "resume = '". $this->id. "' ";
        } else {
            $query .= ", resume = '". $this->id. "' ";
        }
        
        if ($this->mysqli->execute($query)) {
            $query = "SELECT qualification, completed_on FROM resume_educations 
                      WHERE resume = ". $this->id. " ORDER BY completed_on DESC";
            $result = $this->mysqli->query($query);
            $qualification = "";
            $i = 0;
            foreach ($result as $row) {
                $qualification .= $row['qualification'];
                if ($i < count($result)-1) {
                    $qualification .= " ";
                }
                $i++;
            }
            
            $query = "UPDATE resume_index SET 
                      qualification = '". $qualification. "' 
                      WHERE resume = ". $this->id. " AND member = '". $this->member_id. "'";
            return $this->mysqli->execute($query);
        }
        
        return false;
    }
    
    public function update_education($data) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        $data = sanitize($data);
        $query = "UPDATE resume_educations SET ";                
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "ID" && strtoupper($key) != "RESUME") {
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
        
        $query .= "WHERE id = ". $data['id'];
        
        if ($this->mysqli->execute($query)) {
            $query = "SELECT qualification, completed_on FROM resume_educations 
                      WHERE resume = ". $this->id. " ORDER BY completed_on DESC";
            $result = $this->mysqli->query($query);
            $qualification = "";
            $i = 0;
            foreach ($result as $row) {
                $qualification .= $row['qualification'];
                if ($i < count($result)-1) {
                    $qualification .= " ";
                }
                $i++;
            }
            
            $query = "UPDATE resume_index SET 
                      qualification = '". $qualification. "' 
                      WHERE resume = ". $this->id. " AND member = '". $this->member_id. "'";
            return $this->mysqli->execute($query);
        }
        
        return false;
    }
    
    public function delete_education($_id) {
        if (empty($_id)) {
            return false;
        }
        
        $query = "DELETE FROM resume_educations WHERE id = ". $_id;
        if ($this->mysqli->execute($query)) {
            $query = "SELECT qualification, completed_on FROM resume_educations 
                      WHERE resume = ". $this->id. " ORDER BY completed_on DESC";
            $result = $this->mysqli->query($query);
            $qualification = "";
            $i = 0;
            foreach ($result as $row) {
                $qualification .= $row['qualification'];
                if ($i < count($result)-1) {
                    $qualification .= " ";
                }
                $i++;
            }
            
            $query = "UPDATE resume_index SET 
                      qualification = '". $qualification. "' 
                      WHERE resume = ". $this->id. " AND member = '". $this->member_id. "'";
            return $this->mysqli->execute($query);
        }
        
        return false;
    }
    
    public function get_skills() {
        $query = "SELECT * FROM resume_skills WHERE resume = ". $this->id;
        
        return $this->mysqli->query($query);
    }
    
    public function create_skill($data) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        $data = sanitize($data);
        $query = "INSERT INTO resume_skills SET ";                
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "ID" && strtoupper($key) != "RESUME") {
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
            $query .= "resume = '". $this->id. "' ";
        } else {
            $query .= ", resume = '". $this->id. "' ";
        }
        
        if ($this->mysqli->execute($query)) {
            $query = "SELECT skill FROM resume_skills 
                      WHERE resume = ". $this->id;
            $result = $this->mysqli->query($query);
            $skill = "";
            $i = 0;
            foreach ($result as $row) {
                $skill .= $row['skill'];
                if ($i < count($result)-1) {
                    $skill .= " ";
                }
                $i++;
            }
            
            $query = "UPDATE resume_index SET 
                      skill = '". $skill. "' 
                      WHERE resume = ". $this->id. " AND member = '". $this->member_id. "'";
            return $this->mysqli->execute($query);
        }
        
        return false;
    }
    
    public function update_skill($data) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        $data = sanitize($data);
        $query = "UPDATE resume_skills SET ";                
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "ID" && strtoupper($key) != "RESUME") {
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
        
        $query .= "WHERE id = ". $data['id'];
        
        if ($this->mysqli->execute($query)) {
            $query = "SELECT skill FROM resume_skills 
                      WHERE resume = ". $this->id;
            $result = $this->mysqli->query($query);
            $skill = "";
            $i = 0;
            foreach ($result as $row) {
                $skill .= $row['skill'];
                if ($i < count($result)-1) {
                    $skill .= " ";
                }
                $i++;
            }
            
            $query = "UPDATE resume_index SET 
                      skill = '". $skill. "' 
                      WHERE resume = ". $this->id. " AND member = '". $this->member_id. "'";
            return $this->mysqli->execute($query);
        }
        
        return false;
    }
    
    public function delete_skill($_id) {
        if (empty($_id)) {
            return false;
        }
        
        $query = "DELETE FROM resume_skills WHERE id = ". $_id;
        if ($this->mysqli->execute($query)) {
            $query = "SELECT skill FROM resume_skills 
                      WHERE resume = ". $this->id;
            $result = $this->mysqli->query($query);
            $skill = "";
            $i = 0;
            foreach ($result as $row) {
                $skill .= $row['skill'];
                if ($i < count($result)-1) {
                    $skill .= " ";
                }
                $i++;
            }
            
            $query = "UPDATE resume_index SET 
                      skill = '". $skill. "' 
                      WHERE resume = ". $this->id. " AND member = '". $this->member_id. "'";
            return $this->mysqli->execute($query);
        }
    }
    
    public function get_technical_skills() {
        $query = "SELECT * FROM resume_technical_skills WHERE resume = ". $this->id;
        
        return $this->mysqli->query($query);
    }

    public function create_technical_skill($data) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        $data = sanitize($data);
        $query = "INSERT INTO resume_technical_skills SET ";                
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "ID" && strtoupper($key) != "RESUME") {
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
            $query .= "resume = '". $this->id. "' ";
        } else {
            $query .= ", resume = '". $this->id. "' ";
        }
        
        if ($this->mysqli->execute($query)) {
            $query = "SELECT level, technical_skill FROM resume_technical_skills 
                      WHERE resume = ". $this->id. " ORDER BY level DESC";
            $result = $this->mysqli->query($query);
            $skill = "";
            $i = 0;
            foreach ($result as $row) {
                $skill .= $row['technical_skill'];
                if ($i < count($result)-1) {
                    $skill .= " ";
                }
                $i++;
            }
            
            $query = "UPDATE resume_index SET 
                      technical_skill = '". $skill. "' 
                      WHERE resume = ". $this->id. " AND member = '". $this->member_id. "'";
            return $this->mysqli->execute($query);
        }
        
        return false;
    }
    
    public function update_technical_skill($data) {
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        $data = sanitize($data);
        $query = "UPDATE resume_technical_skills SET ";                
        $i = 0;
        foreach ($data as $key => $value) {
            if (strtoupper($key) != "ID" && strtoupper($key) != "RESUME") {
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
        
        $query .= "WHERE id = ". $data['id'];
        
        if ($this->mysqli->execute($query)) {
            $query = "SELECT level, technical_skill FROM resume_technical_skills 
                      WHERE resume = ". $this->id. " ORDER BY level DESC";
            $result = $this->mysqli->query($query);
            $skill = "";
            $i = 0;
            foreach ($result as $row) {
                $skill .= $row['technical_skill'];
                if ($i < count($result)-1) {
                    $skill .= " ";
                }
                $i++;
            }
            
            $query = "UPDATE resume_index SET 
                      technical_skill = '". $skill. "' 
                      WHERE resume = ". $this->id. " AND member = '". $this->member_id. "'";
            return $this->mysqli->execute($query);
        }
        
        return false;
    }
    
    public function delete_technical_skill($_id) {
        if (empty($_id)) {
            return false;
        }
        
        $query = "DELETE FROM resume_technical_skills WHERE id = ". $_id;
        if ($this->mysqli->execute($query)) {
            $query = "SELECT level, technical_skill FROM resume_technical_skills 
                      WHERE resume = ". $this->id. " ORDER BY level DESC";
            $result = $this->mysqli->query($query);
            $skill = "";
            $i = 0;
            foreach ($result as $row) {
                $skill .= $row['technical_skill'];
                if ($i < count($result)-1) {
                    $skill .= " ";
                }
                $i++;
            }
            
            $query = "UPDATE resume_index SET 
                      technical_skill = '". $skill. "' 
                      WHERE resume = ". $this->id. " AND member = '". $this->member_id. "'";
            return $this->mysqli->execute($query);
        }
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
        
        $query = "SELECT ". $columns. " FROM resumes ". $joins. 
                  " ". $match. " ". $group. " ". $order. " ". $limit;
        
        $mysqli = Database::connect();
        return $mysqli->query($query);
    }
}
?>