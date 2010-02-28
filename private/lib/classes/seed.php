<?php
require_once dirname(__FILE__). "/../utilities.php";

class Seed {
    public static function generateSeed() {
        $mysqli = Database::connect();
        list($usec, $sec) = explode(" ", microtime());
        $seed = number_format(($usec + $sec), 8, ".", "");
        $query = "INSERT INTO seeds SET seed = '". $seed. "'";

        if ($mysqli->execute($query)) {
            $query = "SELECT id, seed FROM seeds ORDER BY id DESC LIMIT 1";
            if ($result = $mysqli->query($query)) {
                $response = array(
                    'login' => array(
                        'id' => $result[0]['id'],
                        'seed' => $result[0]['seed']
                    )
                );
                return $response;
            }        
        }

        return false;
    }
}
?>
