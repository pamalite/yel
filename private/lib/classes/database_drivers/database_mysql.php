<?php
require_once dirname(__FILE__). "/../xmldom.php";

class DatabaseMySQL {
    private $mysqli;
    private $database;
    private $mysql_error;
    
    function __construct($_host = "", $_username = "", $_password = "", $_database = "") {
        $this->database    = $_database;
        $this->mysql_error = array();
        
        // Select the default database. If not, do not select at all. 
        if (!empty($_database)) {
            $this->mysqli = new MySQLi($_host, $_username, $_password, $_database);
        } else {
            $this->mysqli = new MySQLi($_host, $_username, $_password);
        }
        
        if (mysqli_connect_errno()) {
            $this->mysql_error['error'] = mysqli_connect_error();
            $this->mysql_error['errno'] = mysqli_connect_errno();
        }
    }
    
    function __destruct() {
        $this->close();
    }
    
    /**
     * Close the MySQL connection.
     */
    public function close() {
        if (is_a($this->mysqli, "MySQLi")) {
            $this->mysqli->close();
        }
    }
    
    /**
     * Returns the MySQL error stored in the $mysql_error array.
     * @return mysql_error[]  Returns mysql_error['error'] and mysql_error['errno'].
     */
    public function error() {
        return $this->mysql_error;
    }
    
    /**
     * Returns the MySQL error stored in the $mysql_error array in XML string.
     * @return String  Returns mysql_error in XML format.
     * FORMAT:
     * <errors><errno>mysql_error['errno']</errno><error>mysql_error['error']</error></errors>
     */
    public function error_in_xml() {
        $data = array(
            'errors' => array(
                'errno' => $this->mysql_error['errno'],
                'error' => $this->mysql_error['error']
            )
        );
        $xml_dom = new XMLDOM();
        return $xml_dom->get_xml_from_array($data);
    }
    
    /**
     * Ping the MySQL server to see whether is it alive.
     * @return boolean    Returns TRUE, if the server is alive, otherwise FALSE.
     */
    public function ping() {
        return $this->mysqli->ping();
    }
    
    /**
     * Perform a normal retrieval query with a single SQL query.
     * @param  sql         The SQL query. 
     * @param  _database   (Optional) The database to use when performing the query. 
     * @return mixed       The associative array or FALSE if the query had failed.
     */
    public function query($sql, $_database = "") {
        $db = (empty($_database)) ? $this->database : $_database;
        
        if (($this->mysqli->select_db($db))) {
            if (($result = $this->mysqli->query($sql))) {
                $results = array();
                $i = 0;
                while ($row = $result->fetch_assoc()) {
                    $results[$i] = $row;
                    $i++;
                }
                
                $result->close();
                return $results;
            }
        }
        
        $this->mysql_error['error'] = $this->mysqli->error;
        $this->mysql_error['errno'] = $this->mysqli->errno;
        return false;
    }
    
    /**
     * Perform a normal retrieval query from multiple SQL queries, separated by semicolons (;).
     * @param sqls         The SQL queries, separated by semicolons (;).
     * @param _database    (Optional) The database to use when performing the queries.
     * @return mixed       The arrays of associative array or FALSE if the query had failed.
     */
    public function query_all($sqls, $_database = "") {
        $db = (empty($_database)) ? $this->database : $_database;
        
        $records = array();
        $i = 0;
        if (($this->mysqli->select_db($db))) {
            
            if ($this->mysqli->multi_query($sqls)) {
                do {
                    if ($result = $this->mysqli->store_result()) {
                        $rows = array();
                        $j = 0;
                        while ($row = $result->fetch_assoc()) {
                            $rows[$j] = $row;
                            $j++;
                        }
                        
                        $records[$i] = $rows;
                        $i++;
                        $result->close();
                    }
                } while ($this->mysqli->next_result());
                
                return $records;
            }
        }
        
        $this->mysql_error['error'] = $this->mysqli->error;
        $this->mysql_error['errno'] = $this->mysqli->errno;
        return false;
    }
    
    /**
     * Perform a normal non-retrieval query with a single SQL query. 
     * @param  sql         The SQL query. 
     * @param  _last_id    (Optional) Specify whether this query should return LAST_INSERT_ID. 
     * @param  _database   (Optional) The database to use when performing the query. 
     * @return mixed       Returns TRUE, if the query was successful, or an integer if _last_id
     *                     was specified, otherwise FALSE.
     */
    public function execute($sql, $_last_id = false, $_database = "") {
        $db = (empty($_database)) ? $this->database : $_database;
        
        if (($this->mysqli->select_db($db))) {
            if ($this->mysqli->query($sql)) {
                $last_insert_id = $this->mysqli->insert_id;
                
                return ($_last_id) ? $last_insert_id : true;
            }
        }
        
        $this->mysql_error['error'] = $this->mysqli->error;
        $this->mysql_error['errno'] = $this->mysqli->errno;
        return false;
    }
    
    /** 
     * Perform a normal locked non-retrieval query with multiple SQL queries, separated by semicolons (;). 
     * This method will make all queries as a single transaction by enclosing them with autocommit(FALSE)
     * and then autocommit(TRUE). It will call commit() on a successful execution, rollback() otherwise.
     * @param  sqls        The SQL queries. 
     * @param  _last_id    (Optional) Specify whether this query should return LAST_INSERT_ID. 
     * @param  _database   (Optional) The database to use when performing the query. 
     * @return mixed       Returns TRUE, if the query was successful, or an integer if _last_id
     *                     was specified, otherwise FALSE.
     */
    public function transact($sqls, $_last_id = false, $_database = "") {
        $db = (empty($_database)) ? $this->database : $_database;
        
        $i = 0;
        if (($this->mysqli->select_db($db))) {
            $this->mysqli->autocommit(FALSE);
            
            if ($this->mysqli->multi_query($sqls)) {
                do {
                    if ($result = $this->mysqli->store_result()) {
                        $result->close();
                    }
                } while ($this->mysqli->next_result());
                
                $last_insert_id = $this->mysqli->insert_id;
                $this->mysqli->commit();
                $this->mysqli->autocommit(TRUE);
                
                return ($_last_id) ? $last_insert_id : true;
            }
        }
        
        $this->mysqli->rollback();
        $this->mysqli->autocommit(TRUE);
        $this->mysql_error['error'] = $this->mysqli->error;
        $this->mysql_error['errno'] = $this->mysqli->errno;
        return false;
    }
    
    /**
     * Perform a stored procedure call. 
     * @param  _procedure  The name of the procedure. 
     * @param  _parameters (Optional) The parameters to be passed. 
     * @param  _database   (Optional) The database to use when performing the query. 
     * @return mixed       Returns an associative array of the result otherwise FALSE.
     * NOTE:
     * - This method only allow ONE resultset to be returned from the procedure. 
     */
    public function call($_procedure, $_parameters = "", $_database = "") {
        $db = (empty($_database)) ? $this->database : $_database;
        
        $sql = "CALL ". $_procedure. "(";
        $i = 0;
        if (!empty($_parameters) || !is_null($_parameters)) {
            foreach($_parameters as $param) {
                if (empty($param)) {
                    $sql .= "NULL";
                } else if (is_string($param)) {
                    $sql .= "'". $param. "'"; 
                } else {
                    $sql .= $param;
                }

                if ($i < count($_parameters) - 1) {
                    $sql .= ", ";
                }
                $i++;
            }
        }
        $sql .= ")";
        
        if (($this->mysqli->select_db($db))) {
            if ($this->mysqli->multi_query($sql)) {
                $result = false;
                $has_result = false;
                $results = array();
                do {
                    if (($result = $this->mysqli->store_result())) {
                        if ($result->num_rows > 0) {
                            $i = 0;
                            while ($row = $result->fetch_assoc()) {
                                $results[$i] = $row;
                                $i++;
                            }
                            $result->close();
                            $has_result = true;
                        }
                    }
                } while ($this->mysqli->next_result());
                
                if ($has_result) return $results;
                
                return $result;
            }
        }
        
        $this->mysql_error['error'] = $this->mysqli->error;
        $this->mysql_error['errno'] = $this->mysqli->errno;
        return false;
    }

}

?>