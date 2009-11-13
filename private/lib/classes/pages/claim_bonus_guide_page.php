<?php
require_once dirname(__FILE__). "/../../utilities.php";

class ClaimBonusGuidePage extends Page {
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for welcome page goes here.
    }
    
    public function insert_guide_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/guide.css">'. "\n";
    }
    
    public function insert_guide_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/guide.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        // TODO: Any inline scripts for welcome page goes here.
    }
    
    public function show() {
        $this->begin();        
        ?>
        <div class="title">
            How to Claim My Bonuses?
        </div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 1: Confirm your employment</div><br/>
            <div style="text-align: center;">Confirm your employment with the employer if you haven't already. You should have signed and received a copy of the offer letter by now.</div>
        </div>
        <div class="flow_arrow"><img style="height: 25px;" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/flow_arrow.jpg" /></div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 2: Tell us the good news</div><br/>
            <div style="text-align: center;"> Go to the <a class="no_link" onClick="window.opener.location.replace('../../members/confirm_hires.php');">Jobs Applied</a> and locate the job that you have just been successfully hired for and click the "I'm Employed" button on the far right.</div>
        </div>
        <div class="flow_arrow"><img style="height: 25px;" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/flow_arrow.jpg" /></div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 3: Claim bonus!</div><br/>
            <div style="text-align: center;">You're done. You should receive your bonus shortly after your new employer settles everything else with us. Congratulations on your success and thank you for choosing Yellow Elevator. Now refer your friends for more rewards!</div>
        </div>
        <?php
    }
}
?>