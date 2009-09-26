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
        $this->top("Yellow Elevator&nbsp;&nbsp;<span style=\"color: #FC8503;\">Take a Tour</span>");
        ?>
        <div class="content">
            <img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/out_of_order.jpg" /><br/><br/>
            Sorry, the touring elevator will be back soon.
        </div>
        <?php
    }
}
?>