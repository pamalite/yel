<?php
require_once dirname(__FILE__). "/../../utilities.php";

class TourPage extends Page {
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_tour_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/tour.css">'. "\n";
    }
    
    public function insert_tour_scripts() {
        $this->insert_scripts();
        
        //echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/tour.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        // TODO: Any inline scripts for welcome page goes here.
    }
    
    public function show() {
        $this->begin();
        $this->top("Yellow Elevator - Take a Tour");
        ?>
        <div class="content">
            <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0">
                <param name="movie" value="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/flash/steps.swf" />
                <param name="quality" value="high" />
                <embed src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root']; ?>/common/images/flash/steps.swf" quality="high"type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
            </object>
        </div>
        <?php
    }
}
?>