<?php
require_once dirname(__FILE__). "/../../utilities.php";

class MemberMyReferralsPage extends Page {
    private $member = NULL;
    private $rewards_count = 0;
    
    function __construct($_session) {
        $this->member = new member($_session['id'], $_session['sid']);
        $this->rewards_count = $this->get_rewards_count();
    }
    
    private function get_rewards_count() {
        $query = "SELECT COUNT(referrals.id) AS num_rewards 
                  FROM referrals 
                  LEFT JOIN jobs ON jobs.id = referrals.job 
                  LEFT JOIN member_referees ON member_referees.member = referrals.member AND 
                  member_referees.referee = referrals.referee 
                  WHERE referrals.member = '". $this->member->id(). "' AND 
                  referrals.reward_counted = false AND 
                  (referrals.employed_on IS NOT NULL AND referrals.employed_on <> '0000-00-00 00:00:00') AND 
                  (referrals.work_commence_on IS NOT NULL AND referrals.work_commence_on <> '0000-00-00 00:00:00') AND 
                  (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') 
                  AND jobs.closed = 'N' AND jobs.expire_on >= NOW()";
        $mysqli = Database::connect();
        $result = $mysqli->query($query);
        return $result[0]['num_rewards'];
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_member_my_referrals_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/member_my_referrals.css">'. "\n";
    }
    
    public function insert_member_my_referrals_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/member_my_referrals.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->member->id(). '";'. "\n";
        echo 'var rewards_count = '. $this->rewards_count. ';'. "\n";
        echo '</script>'. "\n";
    }
    
    private function mark_all_referrals_viewed() {
        $mysqli = Database::connect();
        
        $query = "UPDATE referrals SET response_counted = TRUE
                  WHERE response_counted = FALSE AND 
                  view_counted = FALSE AND 
                  (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
                  (referrals.work_commence_on IS NULL OR referrals.work_commence_on = '0000-00-00 00:00:00') AND 
                  (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
                  (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
                  (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') AND 
                  (referrals.employer_agreed_terms_on IS NULL OR referrals.employer_agreed_terms_on = '0000-00-00 00:00:00') AND 
                  member = '". $this->member->id(). "'";
        $mysqli->execute($query);
        
        $query = "UPDATE referrals SET view_counted = TRUE 
                  WHERE view_counted = FALSE AND 
                  (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
                  (referrals.work_commence_on IS NULL OR referrals.work_commence_on = '0000-00-00 00:00:00') AND 
                  (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
                  (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
                  (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') AND 
                  (referrals.employer_agreed_terms_on IS NOT NULL AND referrals.employer_agreed_terms_on <> '0000-00-00 00:00:00') AND 
                  member = '". $this->member->id(). "'";
        $mysqli->execute($query);
    }
    
    public function show() {
        $this->mark_all_referrals_viewed();
        
        $this->begin();
        $this->top_search($this->member->get_name(). " - My Referrals");
        $this->menu('member', 'my_referrals');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div id="div_tabs">
            <ul>
                <li id="li_pendings">Referrals</li>
                <li id="li_rewardeds">Rewards<span id="rewards_count"><?php echo ($this->rewards_count > 0) ? ' ('. $this->rewards_count. ')' : '' ?></span></li>
            </ul>
        </div>
        <div id="div_total_rewards">
            You have earned a total of<br/>
            <span id="rewards"></span><br/>
            as referral rewards. Keep it up!
        </div>
        <div id="div_pendings">
            <table class="header">
                <tr>
                    <td class="employer"><span class="sort" id="sort_employer">Employer</span></td>
                    <td class="title"><span class="sort" id="sort_title">Job</span></td>
                    <td class="title"><span class="sort" id="sort_candidate">Contact</span></td>
                    <td class="date"><span class="sort" id="sort_referred_on">Referred On</span></td>
                    <td class="date"><span class="sort" id="sort_acknowledged_on">Resume Submitted On (R)</span></td>
                    <td class="date"><span class="sort" id="sort_employer_view_resume_on">Employer Viewed On (V)</span></td>
                    <td class="potential_title"><span class="sort" id="sort_reward">Potential Reward</span></td>
                </tr>
            </table>
            <div id="div_list">
            </div>
        </div>
        
        <div id="div_rewardeds">
            <table class="header">
                <tr>
                    <td class="employer"><span class="sort" id="sort_rewarded_employer">Employer</span></td>
                    <td class="title"><span class="sort" id="sort_rewarded_title">Job</span></td>
                    <td class="title"><span class="sort" id="sort_rewarded_candidate">Contact</span></td>
                    <td class="date"><span class="sort" id="sort_rewarded_employed_on">Employed On</span></td>
                    <td class="date"><span class="sort" id="sort_rewarded_work_commence_on">Work Commence On</span></td>
                    <td class="reward_title"><span class="sort" id="sort_rewarded_reward">Reward</span></td>
                    <td class="reward_title"><span class="sort" id="sort_rewarded_paid">Paid</span></td>
                </tr>
            </table>
            <div id="div_rewarded_list">
            </div>
        </div>
        <?php
    }
}
?>