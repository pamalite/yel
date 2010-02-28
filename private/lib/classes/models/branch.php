<?php 
require_once dirname(__FILE__). "/../../utilities.php";

class Branch {
    public static function create($_data) {
        if (is_null($_data) || !is_array($_data)) {
            return false;
        }
        
        $mysqli = Database::connect();
        $data = sanitize($_data);
        $query = "INSERT INTO branches SET ";                
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
            $this->id = $id;
            return true;
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
        $query = "UPDATE branches SET ";
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
        
        $query = "SELECT ". $columns. " FROM branches ". $joins. 
                  " ". $match. " ". $group. " ". $order. " ". $limit;
        return $mysqli->query($query);
    }
    
    public static function delete() {
        // Reserved for future use.
    }
    
    public static function getAll($_offset = 0, $_limit = 0, $_order_by = '') {
        $criteria = array();
        $criteria['columns'] = '*';
        
        if ($_offset <= 0 && $_limit > 0) {
            $criteria['limit'] = $_limit;
        } else if ($_offset > 0 && $_limit > 0) {
            $criteria['limit'] = $_offset. ', '. $_limit;
        }
        
        if (!empty($_order_by)) {
            $criteria['order'] = $_order_by;
        }
        
        return self::find($criteria);
    }
    
    public static function get($_id) {
        if (!is_null($_id)) {
            $criteria = array(
                'match' => "id = '". $_id. "'", 
                'limit' => '1'
            );
        
            $result = self::find($criteria);
            if (!is_null($result) && count($result) > 0) {
                return $result[0];
            }
        }
        
        return false;
    }
    
    public static function getCurrency($_id) {
        if (!is_null($_id)) {
            $criteria = array(
                'match' => "id = '". $_id. "'", 
                'columns' => 'currency', 
                'limit' => '1'
            );
        
            $result = self::find($criteria);
            if (!is_null($result) && count($result) > 0) {
                return $result[0];
            }
        }
        
        return false;
    }
}
?>
