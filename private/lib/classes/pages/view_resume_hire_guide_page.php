<?php
require_once dirname(__FILE__). "/../../utilities.php";

class ViewResumeHireGuidePage extends Page {
    
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
            How to View Resumes &amp; Hire Candidates?
        </div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 1: Go to <a class="no_link" onClick="window.opener.location.replace('../../employers/referrals.php');">Referrals</a></div><br/>
        </div>
        <div class="flow_arrow"><img style="height: 25px;" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/flow_arrow.jpg" /></div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 2: Click on the number under the "Referrals" column.</div><br/>
        </div>
        <div class="flow_arrow"><img style="height: 25px;" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/flow_arrow.jpg" /></div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 3: View and screen resumes</div><br/>
            <div style="text-align: center;">
                On top of screening the resumes, you may also contact the referrers (recommenders) for more information/reference regarding the candidates. The contact information of the referrers is stated next to the contact information of the candidates.
                <br/>
                <br/>
                <div style="text-align: center;">
                    <a class="no_link" onClick="toggle_tip('step3_1', '35')">The Shortlist Feature</a>
                </div>
                <br/>
                <div class="tip" id="step3_1">
                    This feature helps you keep track of shortlisted candidates by transferring them over to the "My Shortlist" section.
                </div>
                <br/>
                <div style="text-align: center;">
                    <a class="no_link" onClick="toggle_tip('step3_2', '100')">The Reject Feature</a>
                </div>
                <br/>
                <div class="tip" id="step3_2">
                    Please click on "Reject" if you have identified the candidate to be not suitable for the job position. 
                    <br/><br/>
                    Clicking on "Reject" will send an automated email to the candidate informing him/her that he/she is not shortlisted for the job position. We strongly encourage you to use this feature to keep the rejected candidates informed on their latest status.
                </div>
                <br/>
                <div style="text-align: center;">
                    <a class="no_link" onClick="toggle_tip('step3_3', '50')">The "Confirm Employed" Feature</a>
                </div>
                <br/>
                <div class="tip" id="step3_3">
                    Clicking on "Confirm Employed" will inform us that you have successfully hired a candidate from Yellow Elevator. You MUST submit the candidate's employment details to us whenever there is a successful employment.
                </div>
            </div><br/>
        </div>
        <div class="flow_arrow"><img style="height: 25px;" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/flow_arrow.jpg" /></div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 4: Hire a candidate</div><br/>
            <div style="text-align: center;">Once you have successfully hired a candidate from Yellow Elevator, click on "Confirm Employed" and submit the employment details to us. We will get in touch with you once we hear from you.</div><br/>
        </div>
        <?php
    }
}
?>