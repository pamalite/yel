<?php
require_once dirname(__FILE__). "/../../utilities.php";

class ClaimRewardsGuidePage extends Page {
    
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
            How to Claim My Rewards?
        </div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 1: Check Referral Progress</div><br/>
            <div style="text-align: center;">After you have submitted your friend's/acquaintance's resume to the employer as a referral, you will need to follow up with your friend/acquaintance every once in a while to find out if there is any progress or not. You can also check the progress under the <a class="no_link" onClick="window.opener.location.replace('../../members/my_referrals.php');">My Referrals</a> section but that will only let you know if the employer has viewed your referral or not.</div>
        </div>
        <div class="flow_arrow"><img style="height: 25px;" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/flow_arrow.jpg" /></div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 2: Confirm Employment</div><br/>
            <div style="text-align: center;"> If your friend/acquaintance has been successfully hired for the job, please remind your friend/ acquaintance to go to the <a class="no_link" onClick="window.opener.location.replace('../../members/confirm_hires.php');">Jobs Applied</a> in his member account, locate the job that they have just been successfully hired for and click the "I'm Employed" button on the far right. This will get you your referrer's reward and get your friend/acquaintance their bonus.</div>
        </div>
        <div class="flow_arrow"><img style="height: 25px;" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/flow_arrow.jpg" /></div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 3: Claim reward!</div><br/>
            <div style="text-align: center;">You're done. You should receive your reward shortly after the employer settles everything else with us. Congratulations on your successful referral and thank you for choosing Yellow Elevator. Please refer more friends for more rewards!</div>
        </div>
        <?php
    }
}
?>