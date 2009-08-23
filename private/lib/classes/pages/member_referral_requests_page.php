<?php
require_once dirname(__FILE__). "/../../utilities.php";

class MemberReferralRequestsPage extends Page {
    private $member = NULL;
    
    function __construct($_session) {
        $this->member = new member($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_member_referral_requests_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/list_box.css">'. "\n";
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/member_referral_requests.css">'. "\n";
    }
    
    public function insert_member_referral_requests_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/list_box.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/member_referral_requests.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->member->id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function mark_all_requests_viewed() {
        $query = "UPDATE referral_requests SET 
                  requests_counted = TRUE
                  WHERE requests_counted = FALSE AND 
                  referrer = '". $this->member->id(). "'";
        $mysql = Database::connect();
        $mysql->execute($query);
    }
    
    public function show() {
        $this->mark_all_requests_viewed();
        
        $this->begin();
        $this->top_search($this->member->get_name(). " - Referral Requests");
        $this->menu('member', 'referral_requests');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div id="div_requests">
            <table class="header">
                <tr>
                    <td class="employer"><span class="sort" id="sort_employer">Employer</span></td>
                    <td class="title"><span class="sort" id="sort_title">Job</span></td>
                    <td class="title"><span class="sort" id="sort_candidate">Contact</span></td>
                    <td class="date"><span class="sort" id="sort_requested_on">Requested On</span></td>
                    <td class="potential_title"><span class="sort" id="sort_reward">Potential Reward</span></td>
                    <td class="actions">&nbsp;</td>
                </tr>
            </table>
            <div id="div_list">
            </div>
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_testimony_form">
            <form onSubmit="retun false;">
                <input type="hidden" id="request" name="request" value="1" />
                <input type="hidden" id="resume" name="resume" value="" />
                <input type="hidden" id="requested_on" name="requested_on" value="" />
                <p>1. How long have you known and how do you know <span id="candidate_name" style="font-weight: bold;"></span>?  (<span id="word_count_q1">0</span>/50 words)</p>
                <p><textarea class="field" id="testimony_answer_1"></textarea></p>
                <p>2. What makes <span id="candidate_name" style="font-weight: bold;"></span> suitable for the job <span id="job_title" style="font-weight: bold;"></span> (<span id="word_count_q2">0</span>/50 words)</p>
                <p><textarea class="field" id="testimony_answer_2"></textarea></p>
                <p>3. Briefly, what are the areas of improvement for <span id="candidate_name" style="font-weight: bold;"></span>? (<span id="word_count_q3">0</span>/50 words)</p>
                <p><textarea class="field" id="testimony_answer_3"></textarea></p>
                <p class="button"><input type="button" value="Cancel" onClick="close_testimony_form();" />&nbsp;<input type="button" value="Refer Now" onClick="refer();" /></p>
            </form>
        </div>
        <?php
    }
}
?>