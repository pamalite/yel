<?php
require_once dirname(__FILE__). "/../../utilities.php";

class MemberHomePage extends Page {
    private $member = NULL;
    
    function __construct($_session) {
        $this->member = new Member($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_member_home_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/member_home_page.css">'. "\n";
    }
    
    public function insert_member_home_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/member_home_page.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->member->getId(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_search('Home');
        $this->menu('member', 'home');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_completeness">
            <div style="padding-bottom: 3px; font-size: 9pt; text-align: center; width: 100%;">
                Your account details are <span id="progress_percent" style="font-weight: bold;"></span> complete.
            </div>
            <div id="progress">
                <div id="progress_bar"></div>
            </div>
            <div class="progress_details">
                <span id="details"></span>
            </div>
        </div><br/>
        
        
        <?php
    }
}
?>