<?php
require_once dirname(__FILE__). "/../../utilities.php";

class Invoice {
    public static function create($_data) {
        $mysqli = Database::connect();
        
        if (is_null($_data) || !is_array($_data)) {
            return false;
        }
        
        if (!array_key_exists('employer', $_data) || 
            !array_key_exists('issued_on', $_data) || 
            !array_key_exists('payable_by', $_data)) {
            return false;
        }
        
        $data = sanitize($_data);
        $query = "INSERT INTO invoices SET ";                
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
        $mysqli = Database::connect();
        
        if (is_null($_data) || !is_array($_data)) {
            return false;
        }
        
        if (!array_key_exists('id', $_data)) {
            return false;
        }
        
        $data = sanitize($_data);
        $query = "UPDATE invoices SET ";
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
        if (empty($_id)) {
            return false;
        }
        
        $mysqli = Database::connect();
        $query = "SELECT * FROM invoices WHERE id = '". $_id. "' LIMIT 1";
        
        return $mysqli->query($query);
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
        
        $query = "SELECT ". $columns. " FROM invoices ". $joins. 
                  " ". $match. " ". $group. " ". $order. " ". $limit;
        return $mysqli->query($query);
    }
    
    public static function getItems($_id) {
        if (empty($_id)) {
            return false;
        }
        
        $mysqli = Database::connect();
        $query = "SELECT * FROM invoice_items WHERE invoice = ". $_id;
        
        return $mysqli->query($query);
    }
    
    public static function getAllFromEmployer($_employer_id, $_paid_invoices = false, 
                                              $_order_by = 'issued_on') {
        $null = "IS NULL";
        if ($paid_invoices) {
            $null = "IS NOT NULL";
        }
        
        $criteria = array(
            'columns' => '*', 
            'match' => "employer = '". $_employer_id. "' AND 
                        paid_on ". $null, 
            'order' => $order_by
        );
        
        return self::find($criteria);
    }
    
    public static function addItem($_invoice, $_amount = 0, $_item = 0, $_itemdesc = '') {
        if (empty($_invoice) || $_invoice <= 0) {
            return false;
        }
        
        $query = "INSERT INTO invoice_items SET 
                  invoice = ". $_invoice. ", 
                  item = ". $_item. ", 
                  itemdesc = '". $_itemdesc. "', 
                  amount = ". $_amount;
        $mysqli = Database::connect();
        return $mysqli->execute($query);
    }
    
    public static function accompanyCreditNoteWith($_previous_invoice, $_free_invoice, 
                                                   $_issued_on, $_credit_amount = 0) {
        if ((empty($_previous_invoice) || $_previous_invoice <= 0) || 
            (empty($_free_invoice) || $_free_invoice <= 0)) {
            return false;
        }
        
        $query = "INSERT INTO credit_notes SET 
                  previous_invoice = ". $_previous_invoice. ", 
                  free_invoice = ". $_free_invoice. ", 
                  credit_amount = ". $_credit_amount. ", 
                  issued_on = '". $_issued_on. "'";
        $mysqli = Database::connect();
        return $mysqli->execute($query);
    }
}
?>
