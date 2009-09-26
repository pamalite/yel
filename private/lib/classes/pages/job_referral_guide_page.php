<?php
require_once dirname(__FILE__). "/../../utilities.php";

class JobReferralGuidePage extends Page {
    
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
            How to Make a Job Referral?
        </div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 1: Search Jobs</div><br/>
            <div style="text-align: center;">Do a job search at the search bar located at the top of every page. Click on your desired job position to view the job description/requirements.</div>
        </div>
        <div class="flow_arrow"><img style="height: 25px;" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/flow_arrow.jpg" /></div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 2: Select your contact and REFER</div><br/>
            <div style="text-align: center;">To start the referral process, click on "Refer Now" and select your contact you wish to refer. If your contact is not in your <a class="no_link" onClick="window.opener.location.replace('../../members/candidates.php');">Contacts</a>, enter the email address of your new contact, and click on "Refer Now" again. Inform your contact about your referral and inform him/her to submit his/her resume if interested in the job position.</div>
            <br/>
            <div style="text-align: center;">
                <a class="no_link" onClick="toggle_tip('step2', '150')">Tips and Hints</a>
            </div>
            <br/>
            <div class="tip" id="step2">
                For your convenience, a notification email is delivered to your contact's email account once you click on "Refer Now". However, the notification email may land in your contact's Junk/Spam folder so we advise you to follow up closely with your referrer to check whether he/she is interested in the job position.<br/><br/>
                At this point, the referral process is partially done. You can only complete your referral process once your contact submits his/her resume for your review.
            </div>
        </div>
        <div class="flow_arrow"><img style="height: 25px;" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/flow_arrow.jpg" /></div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 3: Track your contact's resume submission</div><br/>
            <div style="text-align: center;">In order to track whether your contact has submitted his/her resume, please go to <a class="no_link" onClick="window.opener.location.replace('../../members/referral_requests.php');">Referral Requests</a>.</div>
            <br/>
            <div style="text-align: center;">
                <a class="no_link" onClick="toggle_tip('step3', '100')">Tips and Hints</a>
            </div>
            <br/>
            <div class="tip" id="step3">
                For your convenience, a notification email will be sent to your registered email account once your contact has submitted his/her resume. However, the notification email may land in your Junk/Spam folder so we advise you to check <a class="no_link" onClick="window.opener.location.replace('../../members/candidates.php');">Referral Requests</a> constantly and follow up closely with your contact to check on his/her resume submission status.
            </div>
        </div>
        <div class="flow_arrow"><img style="height: 25px;" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/flow_arrow.jpg" /></div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 4: Screen contact's resume, recommend, and complete your referral</div><br/>
            <div style="text-align: center;">Once your contact has submitted his/her resume, go to <a class="no_link" onClick="window.opener.location.replace('../../members/candidates.php');">Referral Requests</a> to view his/her resume. Be sure to screen it first before you recommend the candidate. Once you are done, click on "Submit".</div>
            <br/>
            <div class="notice">
                <span style="font-weight: bolder;">Important Notice:</span> In order to receive your reward when your contact is hired, your contact MUST click on "I'm Employed" in the <a class="no_link" onClick="window.opener.location.replace('../../members/confirm_hires.php');">Jobs Applied</a> section to verify that he/she is hired. Hence, please follow up closely with your contact throughout his/her job application.
            </div>
            <br/>
            <div style="text-align: center;">
                <a class="no_link" onClick="toggle_tip('step4', '15')">Tips and Hints</a>
            </div>
            <br/>
            <div class="tip" id="step4">
                To track the referrals that you have made, go to <a class="no_link" onClick="window.opener.location.replace('../../members/my_referrals.php');">My Referrals</a>. 
            </div>
        </div>
        <?php
    }
}
?>