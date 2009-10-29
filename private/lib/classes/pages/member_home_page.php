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
        echo 'var id = "'. $this->member->id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function generate_resumes_list() {
        $query = "SELECT id, name FROM resumes 
                  WHERE member = '". $this->member->id(). "' AND 
                  private = 'N' AND 
                  deleted = 'N'";
        $mysqli = Database::connect();
        $result = $mysqli->query($query);
        if (!$result) {
            echo 'Sorry, you need to create at least a public viewable resume to proceed.';
            echo '<input type="hidden" name="resume" value="0" />';
            exit();
        }
        
        echo '<select class="field" id="resume" name="resume">'. "\n";
        echo '<option value="0" selected>Please select a resume</option>'. "\n";
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        
        foreach ($result as $resume) {
            echo '<option value="'. $resume['id']. '">'. $resume['name']. '</option>'. "\n";
        }
        
        echo '</select>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_search($this->member->get_name(). "&nbsp;&nbsp;<span style=\"color: #FC8503;\">Home</span>");
        $this->menu('member', 'home');
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div class="banner" id="div_banner">
            <a class="no_link" onClick="toggle_banner();"><span id="hide_show_label">Hide</span> Guides</a>
            <br/><br/><br/>
            <table class="guides">
                <tr>
                    <td colspan="3" style="text-align: center; padding-bottom: 5px;">
                        <a class="no_link guides" onClick="show_guide_page('setting_up_account.php');">
                            <span style="font-weight: bold;">Setting Up My Account <img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/icons/triangle.jpg" style="vertical-align: baseline;" /></span>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: right; padding-bottom: 5px; padding-right: 8px;">
                        <a class="no_link guides" onClick="show_guide_page('job_referral.php');">
                            <span style="font-weight: bold;">How to Make a Job Referral? <img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/icons/triangle.jpg" style="vertical-align: baseline;" /></span>
                        </a>
                    </td>
                    <td rowspan="2" class="spacer"></td>
                    <td style="padding-bottom: 5px; padding-left: 5px;">
                        <a class="no_link guides" onClick="show_guide_page('apply_job.php');">
                            <span style="font-weight: bold;">How to Apply for a Job Position? <img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/icons/triangle.jpg" style="vertical-align: baseline;" /></span>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: right; padding-bottom: 5px; padding-right: 8px;">
                        <a class="guides" href="my_referrals.php">
                            <span style="font-weight: bold;">How to Track My Job Referrals? <img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/icons/triangle.jpg" style="vertical-align: baseline;" /></span>
                        </a>
                    </td>
                    <td style="padding-bottom: 5px; padding-left: 5px;">
                        <a class="guides" href="confirm_hires.php">
                            <span style="font-weight: bold;">How to Track My Job Applications? <img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/icons/triangle.jpg" style="vertical-align: baseline;" /></span>
                        </a>
                    </td>
                </tr>
            </table>
        </div>
        <div id="div_tabs">
            <ul>
                <li id="li_jobs">Jobs Referred To Me<span id="referred_count"></span></li>
                <li id="li_approvals">New Contacts Requests<span id="approval_count"></span></li>
                <!--li id="li_rewards">My Rewards<span id="rewards_count"></span></li-->
                <!--li id="li_acknowledgements">Contacts' Responses<span id="responses_count"></span></li-->
            </ul>
        </div>
        
        <div id="div_jobs">
            <table class="header">
                <tr>
                    <td class="industry"><span class="sort" id="sort_jobs_industry">Industry</span></td>
                    <td class="title"><span class="sort" id="sort_jobs_employer">Employer</span></td>
                    <td class="title"><span class="sort" id="sort_jobs_title">Job</span></td>
                    <td class="title"><span class="sort" id="sort_jobs_referrer">Referrer</span></td>
                    <td class="date"><span class="sort" id="sort_jobs_referred_on">Referred On</span></td>
                    <td class="acknowledge">&nbsp;</td>
                </tr>
            </table>
            <div id="div_referred_jobs_list">
            </div>
        </div>
        
        <div id="div_approvals">
            <table class="header">
                <tr>
                    <td class="title">Member E-mail</td>
                    <td class="title"><span class="sort" id="sort_approvals_member">Member</span></td>
                    <td class="date"><span class="sort" id="sort_approvals_referred_on">Requested On</span></td>
                    <td class="approve">&nbsp;</td>
                </tr>
            </table>
            <div id="div_approvals_list">
            </div>
        </div>
        
        <div id="div_rewards">
            <table class="header">
                <tr>
                    <td class="industry"><span class="sort" id="sort_rewards_industry">Industry</span></td>
                    <td class="title"><span class="sort" id="sort_rewards_title">Job</span></td>
                    <td class="title"><span class="sort" id="sort_rewards_candidate">Candidate</span></td>
                    <td class="date"><span class="sort" id="sort_rewards_employed_on">Employed On</span></td>
                    <td class="date"><span class="sort" id="sort_rewards_work_commence_on">Work Commence On</span></td>
                    <td class="reward_title"><span class="sort" id="sort_rewards_reward">Reward</span></td>
                    <td class="reward_title"><span class="sort" id="sort_rewards_paid">Paid</span></td>
                </tr>
            </table>
            <div id="div_rewards_list">
            </div>
        </div>
        
        <div id="div_acknowledgements">
            <table class="header">
                <tr>
                    <td class="employer"><span class="sort" id="sort_acknowledgements_employer">Employer</span></td>
                    <td class="title"><span class="sort" id="sort_acknowledgements_title">Job</span></td>
                    <td class="title"><span class="sort" id="sort_acknowledgements_candidate">Contact</span></td>
                    <td class="date"><span class="sort" id="sort_acknowledgements_referred_on">Referred On</span></td>
                    <td class="date"><span class="sort" id="sort_acknowledgements_acknowledged_on">Resume Submitted On</span></td>
                    <td class="potential_title"><span class="sort" id="sort_acknowledgements_reward">Potential Reward</span></td>
                </tr>
            </table>
            <div id="div_acknowledgements_list">
            </div>
        </div>
        
        <div class="job_count" id="job_count">
            Counting jobs available...
        </div>
        <div class="rewards" id="total_potential_rewards">
            Loading potential rewards...
        </div>
        <!--div class="companies">
            <a href="http://www.honeywell.com/" target="_new"><img class="companies" src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/honeywell.jpg" /></a>&nbsp;
            <a href="http://www.bbraun.com/" target="_new"><img class="companies" src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/bbraun.jpg" /></a>&nbsp;
            <a href="http://www.mattel.com/" target="_new"><img class="companies" src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/mattel.jpg" /></a>&nbsp;
            <a href="http://www.fairchildsemi.com/" target="_new"><img class="companies" src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/fairchild.jpg" /></a>&nbsp;
            <a href="http://www.intel.com/" target="_new"><img class="companies" src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/intel.jpg" /></a>&nbsp;
            <a href="http://www.agilent.com/" target="_new"><img class="companies" src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/logos/agilent.jpg" /></a>
        </div-->
        
        <div id="div_blanket"></div>
        <div id="div_job_info">
            <table id="job_info" class="job_info">
                <tr>
                    <td colspan="2" class="title"><span id="job.title">Loading</span></td>
                </tr>
                <tr>
                    <td colspan="2" class="title_reward">Potential Reward of <span id="job.currency_1"></span>&nbsp;<span id="job.potential_reward">Loading</span></td>
                </tr>
                <tr>
                    <td class="label">Industry:</td>
                    <td class="field"><span id="job.industry">Loading</span></td>
                </tr>
                <tr>
                    <td class="label">Employer:</td>
                    <td class="field"><span id="job.employer">Loading</span></td>
                </tr>
                <tr>
                    <td class="label">Country:</td>
                    <td class="field"><span id="job.country">Loading</span></td>
                </tr>
                <tr>
                    <td class="label">State/Province/Area:</td>
                    <td class="field"><span id="job.state">Loading</span></td>
                </tr>
                <tr>
                    <td class="label">Salary:</td>
                    <td class="field"><span id="job.currency"></span>&nbsp;<span id="job.salary">Loading</span>&nbsp;<span id="job.salary_end">Loading</span>&nbsp;[<span id="job.salary_negotiable">Loading</span>]</td>
                </tr>
                <tr>
                    <td class="label">Description:</td>
                    <td class="field"><div class="job_description"><span id="job.description">Loading</span></div></td>
                </tr>
                <tr>
                    <td class="label">&nbsp;</td>
                    <td class="field">&nbsp;</td>
                </tr>
                <tr>
                    <td class="label">Created On:</td>
                    <td class="field"><span id="job.created_on">Loading</span></td>
                </tr>
                <tr>
                    <td class="label">Expires On:</td>
                    <td class="field"><span id="job.expire_on">Loading</span></td>
                </tr>
                <tr>
                    <td id="buttons" class="buttons" colspan="2">
                        <input type="button" class="button" value="Close" onClick="close_job_info();" />
                    </td>
                </tr>
            </table>
        </div>
        
        <div id="div_acknowledge_form">
            <form onSubmit="retun false;">
                <p>
                    Please select a resume for&nbsp;<span id="job_title" style="font-weight: bold;"></span>&nbsp;position:
                </p>
                <p><?php $this->generate_resumes_list(); ?></p>
                <p class="button"><input type="button" value="Cancel" onClick="close_acknowledge_form();" />&nbsp;<input type="button" value="OK" onClick="acknowledge();" /></p>
            </form>
        </div>
        <?php
    }
}
?>