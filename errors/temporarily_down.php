<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

class TemporarilyDown extends Page {
    public function show() {
        $this->begin();
        echo "The elevator is currently under maintenance. We will be back soon!";
    }
}

$temp_down = new TemporarilyDown();
$temp_down->header(array('root_dir' => '../',
                         'insert_styles' => true, 
                         'insert_scripts' => true, 
                         'title' => 'Yellow Elevator - Out of Order!'));
$temp_down->show();
$temp_down->footer();
?>
