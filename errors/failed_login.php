<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

class FailedLogin extends Page {
    public function show() {
        $this->begin();
        ?>
        It seems like your login is bad. Please <a href="<?php echo $GLOBALS['protocol']; ?>://<?php echo $GLOBALS['root']; ?>/<?php echo $_GET['dir']; ?>/index.php">try again</a>.
        <?php
    }
}

$failed = new FailedLogin();
$failed->header(array('root_dir' => '../',
                      'insert_styles' => true, 
                      'insert_scripts' => true, 
                      'title' => 'Yellow Elevator - Bad Login!'));
$failed->show();
$failed->footer();
?>
