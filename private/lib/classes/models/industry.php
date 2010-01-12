<?php
require_once dirname(__FILE__). "/../../utilities.php";

class Industry {
    private $mysqli = NULL;
    
    public static function create($data) {
        $mysqli = Database::connect();
        
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        $data = sanitize($data);
        $query = "INSERT INTO industries SET ";                
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
    
    public static function update($data) {
        $mysqli = Database::connect();
        
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        if (!array_key_exists('id', $data)) {
            return false;
        }
        
        $data = sanitize($data);
        $query = "UPDATE industries SET ";
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
        $query = "SELECT * FROM industries WHERE id = '". $_id. "' LIMIT 1";
        
        return $mysqli->query($query);
    }
    
    public static function get_all() {
        $mysqli = Database::connect();
        $query = "SELECT * FROM industries";
        
        return $mysqli->query($query);
    }
    
    public static function get_main() {
        $mysqli = Database::connect();
        $query = "SELECT id, industry, description FROM industries WHERE parent_id IS NULL ORDER BY industry";
        
        return $mysqli->query($query);
    }
    
    public static function get_sub_industries_of($_id) {
        $mysqli = Database::connect();
        $query = "SELECT id, industry, description FROM industries WHERE parent_id = ". $_id. " ORDER BY industry";
        
        return $mysqli->query($query);
    }
    
    public static function get_main_with_job_count($_member_session = '', $_default_country) {
        /*$country_code = '';
        if (!empty($_member_session)) {
            $member = new Member($_member_session['id']);
            $country_code = $member->get_country_code();
        } else {
            $country_code = $_default_country;
        }*/
        
        $mysqli = Database::connect();
        $query = "SELECT industries.id, industries.industry, industries.description, 
                  COUNT(jobs.id) AS job_count
                  FROM industries 
                  LEFT JOIN jobs ON jobs.industry = industries.id AND 
                  jobs.closed = 'N' AND 
                  jobs.expire_on >= NOW() 
                  WHERE parent_id IS NULL 
                  GROUP BY industries.id 
                  ORDER BY industry";
        /*$query = "SELECT industries.id, industries.industry, industries.description, 
                  COUNT(jobs.id) AS job_count
                  FROM industries 
                  LEFT JOIN jobs ON jobs.industry = industries.id AND 
                  jobs.closed = 'N' AND 
                  jobs.expire_on >= NOW() AND 
                  jobs.country = '". $country_code. "' 
                  WHERE parent_id IS NULL 
                  GROUP BY industries.id 
                  ORDER BY industry";*/
        return $mysqli->query($query);
    }
    
    public static function get_sub_industries_with_job_count_of($_id, $_member_session = '', $_default_country) {
        /*$country_code = '';
        if (!empty($_member_session)) {
            $member = new Member($_member_session['id']);
            $country_code = $member->get_country_code();
        } else {
            $country_code = $_default_country;
        }*/
        
        $mysqli = Database::connect();
        $query = "SELECT industries.id, industries.industry, industries.description, 
                  COUNT(jobs.id) AS job_count
                  FROM industries 
                  LEFT JOIN jobs ON jobs.industry = industries.id AND 
                  jobs.closed = 'N' AND 
                  jobs.expire_on >= NOW() 
                  WHERE parent_id = ". $_id. " 
                  GROUP BY industries.id 
                  ORDER BY industry";
        /*$query = "SELECT industries.id, industries.industry, industries.description, 
                  COUNT(jobs.id) AS job_count
                  FROM industries 
                  LEFT JOIN jobs ON jobs.industry = industries.id AND 
                  jobs.closed = 'N' AND 
                  jobs.expire_on >= NOW() AND 
                  jobs.country = '". $country_code. "' 
                  WHERE parent_id = ". $_id. " 
                  GROUP BY industries.id 
                  ORDER BY industry";
        */
        return $mysqli->query($query);
    }
    
    public static function get_industry_from_id($_id) {
        $mysqli = Database::connect();
        $query = "SELECT industry FROM industries WHERE id = ". $_id. " LIMIT 1";
        $result = $mysqli->query($query);
        return $result[0]['industry'];
    }
}
?>
