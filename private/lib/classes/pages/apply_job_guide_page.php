<?php
require_once dirname(__FILE__). "/../../utilities.php";

class ApplyJobGuidePage extends Page {
    
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
            How to apply for a job position?
        </div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 1:  Upload your resume</div><br/>
            <div style="text-align: center;">Upload your resume at <a class="no_link" onClick="window.opener.location.replace('../../members/resumes.php');">Resumes</a>. If you do not have a file format resume, you may create an online resume with us.</div>
        </div>
        <div class="flow_arrow"><img style="height: 25px;" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/flow_arrow.jpg" /></div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 2: Identify your desired job</div><br/>
            <div style="text-align: center;">Do a job search at the search bar located at the top of every page, and identify your desired job position. Then, click on the job position to view the job description/requirements.</div>
            <br/>
            <div style="text-align: center;">
                <a class="no_link" onClick="toggle_tip('step2', '100')">Tips and Hints</a>
            </div>
            <br/>
            <div class="tip" id="step2">
                You will see "Referrer's Potential Reward" and "Candidate's Bonus". The former means the rough estimate of the reward a referrer could get if there is a successful employment. The latter means a rough estimate of the bonus to the candidate who is successfully hired for the job position. The final Reward and Bonus will be determined by the final salary upon the confirmation of employment.
            </div>
        </div>
        <div class="flow_arrow"><img style="height: 25px;" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/flow_arrow.jpg" /></div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 3:  Select your referrer and REQUEST FOR A REFERRAL</div><br/>
            <div style="text-align: center;">To apply for the job position, you need to be referred (recommended) by a referrer (recommender). Click on "Request for a Referral". You are then required to select your resume and your desired referrer.<br/><br/>
            If your desired referrer is not in your Contacts, then enter the email address of your new referrer. When ready, click on "Submit". Your referrer will receive a notification email requesting him/her to refer (recommend) you to the job position.</div>
            <br/>
            <div style="text-align: center;">
                <a class="no_link" onClick="toggle_tip('step3', '130')">Tips and Hints</a>
            </div>
            <br/>
            <div class="tip" id="step3">
                Your referrer will need to screen your resume and write a recommendation prior to submitting your resume to the employer on behalf of you. As such, select your referrers wisely. Select referrers who know you well and able to write a good recommendation for you.<br/><br/>
                (At this point, your job application process is partially done. You can only complete your job application process when your referrer has submitted your resume to the employer.)
                
            </div>
        </div>
        <div class="flow_arrow"><img style="height: 25px;" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/flow_arrow.jpg" /></div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 4: Track your application</div><br/>
            <div style="text-align: center;">To track whether your referrer has screened, recommended, and submitted your resume to the employer, please go to <a class="no_link" onClick="window.opener.location.replace('../../members/confirm_hires.php');">Jobs Applied</a>.</div>
            <br/>
            <div style="text-align: center;">
                <a class="no_link" onClick="toggle_tip('step4', '100')">Tips and Hints</a>
            </div>
            <br/>
            <div class="tip" id="step4">
                For your convenience, a notification email is delivered to your registered email account once your resume is submitted to the employer. However, the notification email may land in your Junk/Spam folder so we advise you to check <a class="no_link" onClick="window.opener.location.replace('../../members/confirm_hires.php');">Jobs Applied</a> constantly and follow up closely with your referrer to check on the status of your job application.
            </div>
        </div>
        <div class="flow_arrow"><img style="height: 25px;" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/flow_arrow.jpg" /></div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 5: Hear from the employer</div><br/>
            <div style="text-align: center;">Once your resume is submitted, the employer will review it and will contact you for an interview if the employer finds you suitable for the job position.</div>
            <br/>
            <div class="notice">
                <span style="font-weight: bolder;">Important Notice:</span> As long as you are successfully hired by the employer regardless of the job position the employer has hired you for, please REMEMBER to click on "I'm Employed". Failure to do so will result in your referrer not receiving his/her reward, and you not receiving your bonus.
            </div>
            <br/>
            <div style="text-align: center;">
                <a class="no_link" onClick="toggle_tip('step5', '70')">Tips and Hints</a>
            </div>
            <br/>
            <div class="tip" id="step5">
                Please be advised that decisions pertaining to short listing or employment of candidates are solely made by the employers. The employers may or may not contact you if they find you not suitable for the job position.
            </div>
        </div>
        <?php
    }
}
?>