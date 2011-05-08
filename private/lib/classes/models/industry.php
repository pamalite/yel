<?php
require_once dirname(__FILE__). "/../../utilities.php";

class Industry {
    public static function create($_data) {
        if (is_null($_data) || !is_array($_data)) {
            return false;
        }
        
        $mysqli = Database::connect();
        
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
    
    public static function update($_data) {
        if (is_null($_data) || !is_array($_data)) {
            return false;
        }
        
        if (!array_key_exists('id', $_data)) {
            return false;
        }
        
        $mysqli = Database::connect();
        
        $data = sanitize($_data);
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
    
    public static function find($_criteria) {
        if (is_null($_criteria) || !is_array($_criteria)) {
            return false;
        }
        
        $mysqli = Database::connect();
        
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
        
        $query = "SELECT ". $columns. " FROM industries ". $joins. 
                  " ". $match. " ". $group. " ". $order. " ". $limit;
        return $mysqli->query($query);
    }
    
    public static function get($_id) {
        $mysqli = Database::connect();
        $query = "SELECT * FROM industries WHERE id = '". $_id. "' LIMIT 1";
        
        return $mysqli->query($query);
    }
    
    public static function getMain($_with_job_count = false) {
        $mysqli = Database::connect();
        
        $query = "";
        if ($_with_job_count) {
            $query = "SELECT industries.id, industries.industry, COUNT(jobs.id) AS job_count 
                      FROM industries 
                      LEFT JOIN jobs ON jobs.industry = industries.id AND 
                      jobs.closed = 'N' AND jobs.expire_on >= NOW() 
                      WHERE industries.parent_id IS NULL 
                      GROUP BY industries.id 
                      ORDER BY industries.industry";
        } else {
            $query = "SELECT industries.id, industries.industry 
                      FROM industries  
                      WHERE industries.parent_id IS NULL 
                      ORDER BY industries.industry";
        }
        
        return $mysqli->query($query);
    }
    
    public static function getSubIndustriesOf($_id, $_with_job_count = false) {
        $mysqli = Database::connect();
        
        $query = "";
        if ($_with_job_count) {
            $query = "SELECT industries.id, industries.industry, COUNT(jobs.id) AS job_count 
                      FROM industries 
                      LEFT JOIN jobs ON jobs.industry = industries.id AND 
                      jobs.closed = 'N' AND jobs.expire_on >= NOW() 
                      WHERE industries.parent_id = ". $_id. " 
                      GROUP BY industries.id 
                      ORDER BY industries.industry";
        } else {
            $query = "SELECT industries.id, industries.industry 
                      FROM industries  
                      WHERE industries.parent_id  = ". $_id. " 
                      ORDER BY industries.industry";
        }
        
        return $mysqli->query($query);
    }
    
    public static function getAccumulatedMain() {
        $main_industries = self::getMain(true);
        
        foreach ($main_industries as $i=>$main_industry) {
            $sub_industries = self::getSubIndustriesOf($main_industry['id'], true);
            $sub_total = 0;
            foreach ($sub_industries as $sub_industry) {
                $sub_total += $sub_industry['job_count'];
            }
            $main_industries[$i]['job_count'] += $sub_total;
        }
        
        return $main_industries;
    }
    
    public static function getIndustriesFromJobs($_with_job_count = false) {
        $mysqli = Database::connect();
        
        $query = "";
        if ($_with_job_count) {
            $query = "SELECT industries.id, industries.industry, COUNT(jobs.id) AS job_count 
                      FROM jobs 
                      LEFT JOIN industries ON industries.id = jobs.industry 
                      WHERE jobs.deleted = FALSE AND jobs.expire_on >= CURDATE() AND jobs.closed = 'N'  
                      GROUP BY industries.id 
                      ORDER BY industries.industry";
        } else {
            $query = "SELECT DISTINCT industries.id, industries.industry 
                      FROM jobs 
                      LEFT JOIN industries ON industries.id = jobs.industry 
                      WHERE jobs.deleted = FALSE AND jobs.expire_on >= CURDATE() AND jobs.closed = 'N'
                      ORDER BY industries.industry";
        }
        
        return $mysqli->query($query);
    }
}
?>
