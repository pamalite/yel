<?php
require_once dirname(__FILE__). "/../../config/database.inc";
require_once "database_drivers/database_mysql.php";

class Database {
    private static $instance = NULL;
    
    public static function connect($driver = 'MYSQL') {
        if (!isset(self::$instance)) {
            switch($driver) {
                default: 
                    self::$instance = new DatabaseMySQL($GLOBALS['DB_HOST'], 
                                                        $GLOBALS['DB_USERNAME'],
                                                        $GLOBALS['DB_PASSWORD'],
                                                        $GLOBALS['DB_NAME']);
                    self::$instance->set_charset($GLOBAL['DB_CHARSET']);
                    break;
            }
        }
        
        return self::$instance;
    }
}

?>