<?php
require_once dirname(__FILE__). "/../../utilities.php";

class Invoice {
    private $mysqli = NULL;
    
    public static function create($data) {
        $mysqli = Database::connect();
        
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        if (!array_key_exists('employer', $data) || 
            !array_key_exists('issued_on', $data) || 
            !array_key_exists('payable_by', $data)) {
            return false;
        }
        
        $data = sanitize($data);
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
    
    public static function update($data) {
        $mysqli = Database::connect();
        
        if (is_null($data) || !is_array($data)) {
            return false;
        }
        
        if (!array_key_exists('id', $data)) {
            return false;
        }
        
        $data = sanitize($data);
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
    
    public static function get_items_of($_id) {
        if (empty($_id)) {
            return false;
        }
        
        $mysqli = Database::connect();
        $query = "SELECT * FROM invoice_items WHERE invoice = ". $_id;
        
        return $mysqli->query($query);
    }
    
    public static function get_all() {
        $mysqli = Database::connect();
        $query = "SELECT * FROM invoices";
        
        return $mysqli->query($query);
    }
    
    public static function get_all_for_employer($_employer_id, $paid_invoices = false, $order_by = 'issued_on') {
        $null = "IS NULL";
        if ($paid_invoices) {
            $null = "IS NOT NULL";
        }
        
        $mysqli = Database::connect();
        $query = "SELECT * FROM invoices 
                  WHERE employer = '". $_employer_id. "' AND paid_on ". $null. "
                  ORDER BY ". $order_by;
        
        return $mysqli->query($query);
    }
    
    public static function add_item($_invoice, $amount = 0, $item = 0, $itemdesc = '') {
        if (empty($_invoice) || $_invoice <= 0) {
            return false;
        }
        
        $query = "INSERT INTO invoice_items SET 
                  invoice = ". $_invoice. ", 
                  item = ". $item. ", 
                  itemdesc = '". $itemdesc. "', 
                  amount = ". $amount;
        $mysqli = Database::connect();
        return $mysqli->execute($query);
    }
    
    public static function accompany_credit_note_with($_previous_invoice, $_free_invoice, 
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
