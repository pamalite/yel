<?php
require_once dirname(__FILE__). "/../../utilities.php";

class ManageJobPostGuidePage extends Page {
    
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
            How to Create &amp; Publish a Job Ad?
        </div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 1: Go to <a class="no_link" onClick="window.opener.location.replace('../../employers/jobs.php');">Job Ads</a></div><br/>
        </div>
        <div class="flow_arrow"><img style="height: 25px;" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/flow_arrow.jpg" /></div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 2: Click on "Create New Job Ad"</div><br/>
            <div style="text-align: center;">If you like to re-use or edit the contents from an existing job ad, click on "New From This Job"</div><br/>
        </div>
        <div class="flow_arrow"><img style="height: 25px;" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/flow_arrow.jpg" /></div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 3: Fill in information of the job ad</div><br/>
            <div style="text-align: center;">For 'Specialization', select the specialization of your job position instead of the industry of your company.</div><br/>
        </div>
        <div class="flow_arrow"><img style="height: 25px;" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/flow_arrow.jpg" /></div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 4: Click on "Publish"</div><br/>
            <div style="text-align: center;">If you have not completed your job ad, you may click on "Save" and complete it later. Once you have posted your job ad, you will not be able to make any amendments anymore.</div><br/>
        </div>
        <div class="flow_arrow"><img style="height: 25px;" src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/flow_arrow.jpg" /></div>
        <div class="step">
            <div style="text-align: center; font-weight: bold;">Step 5: Close Job Ad (After the opened position is filled.)</div><br/>
            <div style="text-align: center;">To close a job ad, select the job ad/s you wish to close and click on "Close Selected Jobs".<br/><br/>
            Closing your job ad removes your job ad from the website. As such, if you have hired a candidate or decided not to hire a candidate for the job position, please close your job ad.</div>
            <br/>
            <div class="notice">
                <span style="font-weight: bolder;">Important Notice:</span> Closing job ads does not affect the resumes you receive. The resumes you receive will stay in the "Referrals" section despite the job ad is closed. 
            </div><br/>
        </div>
        <?php
    }
}
?>