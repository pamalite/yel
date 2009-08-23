<?php
require_once dirname(__FILE__). "/../utilities.php";

class Debug {
    public static function show($_object) {
        echo '<div class="debug"><pre>'. "\n";
        
        if (is_array($_object)) {
            print_r($_object);
        } else if (is_object($_object)) {
            var_dump($_object);
        } else {
            echo $_object;
        }
        
        echo '</div></pre>'. "\n";
    }
}
?>