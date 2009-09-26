<?php
require_once dirname(__FILE__). "/../../utilities.php";

class SettingUpAccountGuidePage extends Page {
    
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
            Setting Up My Account
        </div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 1:  Upload your resume at <a class="no_link" onClick="window.opener.location.replace('../../members/resumes.php');">Resumes</a></div><br/>
        </div>
        <div class="flow_arrow"><img style="height: 25px;" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/flow_arrow.jpg" /></div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 2: Upload a Photo of Yourself at <a class="no_link" onClick="window.opener.location.replace('../../members/photos.php');">Photos</a></div><br/>
            <br/>
            <div style="text-align: center;">
                <a class="no_link" onClick="toggle_tip('step2', '50')">Tips and Hints</a>
            </div>
            <br/>
            <div class="tip" id="step2">
                Your photo will be attached together with your resume and will be viewed by the employers. However, this is optional. You may not upload a photo if you do not wish to.
            </div>
        </div>
        <div class="flow_arrow"><img style="height: 25px;" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/flow_arrow.jpg" /></div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 3:  Submit your bank account information at <a class="no_link" onClick="window.opener.location.replace('../../members/banks.php');">Bank Accounts</a></div><br/>
            <div style="text-align: center;">To apply for the job position, you need to be referred (recommended) by a referrer (recommender). Click on "Request for a Referral". You are then required to select your resume and your desired referrer.<br/><br/>
            If your desired referrer is not in your Contacts, then enter the email address of your new referrer. When ready, click on "Submit". Your referrer will receive a notification email requesting him/her to refer (recommend) you to the job position.</div>
            <br/>
            <div style="text-align: center;">
                <a class="no_link" onClick="toggle_tip('step3', '130')">Tips and Hints</a>
            </div>
            <br/>
            <div class="tip" id="step3">
                Yellow Elevator pays members (referrers) electronically. You are required to submit your bank account details in "Bank Accounts".<br/><br/>
                If you prefer not to receive payments by cheque, you do not need to submit your bank account details. However, you are required to inform us through email (<a href="mailto:billing@yellowelevator.com">billing@yellowelevator.com</a>) within 30 days starting from the day your contact accepts the employment offer from the employer. Your payment will be forfeited if you fail to either submit your bank account details or send us an email. All fees including but not limited to administration fee, postage, transaction fee, and GST will be charged accordingly.
            </div>
        </div>
        <div class="flow_arrow"><img style="height: 25px;" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/flow_arrow.jpg" /></div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 4: Start building your <a class="no_link" onClick="window.opener.location.replace('../../members/candidates.php');">Contacts</a></div>
        </div>
        <?php
    }
}
?>