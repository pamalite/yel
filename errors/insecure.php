<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

class Insecure extends Page {
    public function show() {
        $this->begin();
        ?>
        It seems like you are viewing this page from this page from an insecure site. Please <a href="https://<?php echo $GLOBALS['root']; ?>/<?php echo $_GET['dir']; ?>/<?php echo $_GET['page'] ?>">try again</a>.
        <?php
    }
}

$insecure = new Insecure();
$insecure->header(array('root_dir' => '../',
                      'insert_styles' => true, 
                      'insert_scripts' => true, 
                      'title' => 'Yellow Elevator - Insecure Viewing!'));
$insecure->show();
$insecure->footer();
?>
