<?php
require_once dirname(__FILE__). "/../../utilities.php";

class MemberSavedJobsPage extends Page {
    private $member = NULL;
    
    function __construct($_session) {
        $this->member = new Member($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_member_saved_jobs_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/list_box.css">'. "\n";
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/member_saved_jobs.css">'. "\n";
    }
    
    public function insert_member_saved_jobs_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/list_box.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/member_saved_jobs.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->member->id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function generate_networks_list($_for_request_form = false) {
        $networks = $this->member->get_networks();
        
        if (!$_for_request_form) {
            echo '<select id="network_filter" name="network_filter" onChange="set_filter(false);">'. "\n";
        } else {
            echo '<select id="network_filter_request" name="network_filter_request" onChange="set_filter(true);">'. "\n";
        }
        
        echo '<option value="0" selected>all my networks</option>'. "\n";
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        
        foreach ($networks as $network) {
            echo '<option value="'. $network['id']. '">'. $network['industry']. '</option>'. "\n";
        }
        
        echo '</select>'. "\n";
    }
    
    private function generate_resumes_list() {
        if (!is_null($this->member)) {
            $query = "SELECT id, name FROM resumes 
                      WHERE member = '". $this->member->id(). "' AND 
                      private = 'N' AND 
                      deleted = 'N'";
            $mysqli = Database::connect();
            $result = $mysqli->query($query);
            if (!$result) {
                echo 'Sorry, you need to create at least a public viewable resume to proceed.';
                echo '<input type="hidden" name="resume" value="0" />';
                return;
            }
        
            echo '<select class="field" id="resume" name="resume">'. "\n";
            echo '<option value="0" selected>Please select a resume</option>'. "\n";
            echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        
            foreach ($result as $resume) {
                echo '<option value="'. $resume['id']. '">'. $resume['name']. '</option>'. "\n";
            }
        
            echo '</select>'. "\n";
        }
    }
    
    public function show() {
        $this->begin();
        $this->top_search($this->member->get_name(). "&nbsp;&nbsp;<span style=\"color: #FC8503;\">Saved Jobs</span>");
        $this->menu('member', 'saved_jobs');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div id="div_saved_jobs">
            <table class="buttons">
                <tr>
                    <td class="left"><input class="button" type="button" id="remove_jobs" name="remove_jobs" value="Remove Selected Jobs" /></td>
                    <td class="right">&nbsp;</td>
                </tr>
            </table>
            <table class="header">
                <tr>
                    <td class="checkbox"><input type="checkbox" id="close_all" /></td>
                    <td class="industry"><span class="sort" id="sort_industry">Industry</span></td>
                    <td class="employer"><span class="sort" id="sort_employer">Employer</span></td>
                    <td class="title"><span class="sort" id="sort_title">Title</span></td>
                    <td class="date"><span class="sort" id="sort_saved_on">Saved On</span></td>
                    <td class="date"><span class="sort" id="sort_expire_on">Expire On</span></td>
                    <td class="potential_reward_title"><span class="sort" id="sort_potential_reward">Potential Reward</span></td>
                    <td class="actions">&nbsp;</td>
                </tr>
            </table>
            <div id="div_list">
            </div>
            <table class="buttons">
                <tr>
                    <td class="left"><input class="button" type="button" id="remove_jobs_1" name="remove_jobs_1" value="Remove Selected Jobs" /></td>
                    <td class="right">&nbsp;</td>
                </tr>
            </table>
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_refer_form">
            <form onSubmit="retun false;">
                <table class="refer_form">
                    <tr>
                    <td colspan="3"><p>You are about to refer the job position,&nbsp;<span id="job_title" style="font-weight: bold;"></span>&nbsp;to your contacts. Please select...</p></td>
                    </tr>
                    <tr>
                        <td class="left">
                            <table class="candidate_form">
                                <tr>
                                    <td class="radio"><input type="radio" id="from_list" name="candidate_from" value="list" checked /></td>
                                    <td>
                                        <label for="from_list">from your Contacts</label><br/>
                                        <span class="filter">[ Show candidates from <?php (!is_null($this->member)) ? $this->generate_networks_list() : ''; ?> ]</span><br/>
                                        <div class="candidates" id="candidates" name="candidates"></div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td class="separator"></td>
                        <td class="right">
                            <table class="candidate_form">
                                <tr>
                                    <td class="radio"><input type="radio" id="from_email" name="candidate_from" value="email" /></td>
                                    <td>
                                        <label for="from_email">or enter their e-mail addresses, separated by spaces</label><br/>
                                        <p><textarea class="mini_field" id="email_addr" name="email_addr"></textarea></p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <!--td class="separator"></td>
                        <td class="right">
                            <p>1. How long have you known and how do you know <span id="candidate_name" style="font-weight: bold;">this contact</span>? (<span id="word_count_q1">0</span>/50 words)</p>
                            <p><textarea class="mini_field" id="testimony_answer_1"></textarea></p>
                            <p>2. What makes <span id="candidate_name" style="font-weight: bold;">this contact</span> suitable for <span id="job_title" style="font-weight: bold;">the job</span>?  (<span id="word_count_q2">0</span>/50 words)</p>
                            <p><textarea class="mini_field" id="testimony_answer_2"></textarea></p>
                            <p>3. Briefly, what are the areas of improvements for <span id="candidate_name" style="font-weight: bold;">this contact</span>?  (<span id="word_count_q3">0</span>/50 words)</p>
                            <p><textarea class="mini_field" id="testimony_answer_3"></textarea></p>
                        </td-->
                    </tr>
                </table>
                <!--div style="padding: 2px 2px 2px 2px; text-align: center; font-style: italic;">
                    Your testimonial for this contact can only be viewed by the employer.
                </div-->
                <p class="button"><input type="button" value="Cancel" onClick="close_refer_form();" />&nbsp;<input type="button" value="Refer Now" onClick="refer();" /></p>
            </form>
        </div>
        
        <div id="div_acknowledge_form">
            <form onSubmit="retun false;">
                <p>
                    You are about to make a request for a referral to the <span id="acknowledge_form_job_title" style="font-weight: bold;"><?php echo $job['title']; ?></span> position.
                </p>
                <p>
                    Please select the resume you wish to submit, as well as, make a request to your desired referrer.
                </p>
                <p><?php $this->generate_resumes_list(); ?></p>
                <p>
                    You may make this request to your desired referrer.
                    <table class="request_form">
                        <tr>
                            <td class="radio"><input type="radio" id="referrer_contacts" name="referrer_option" value="contacts" checked /></td>
                            <td class="option">
                                <label for="referrer_contacts">by selecting a referrer from your Contacts</label><br/>
                                <span class="filter">[ Show contacts from <?php (!is_null($this->member)) ? $this->generate_networks_list(true) : ''; ?> ]</span><br/>
                                <div class="referrers" id="referrers" name="referrers"></div>
                            </td>
                            <td class="separator">&nbsp;</td>
                            <td class="radio"><input type="radio" id="referrer_others" name="referrer_option" value="others" /></td>
                            <td class="option">
                                <label for="referrer_others">by e-mail (enter their e-mail addresses, separated by spaces)</label><br/><br/>
                                <textarea id="referrer_emails" name="referrer_emails"></textarea>
                            </td>
                            <td class="separator">&nbsp;</td>
                            <td class="radio"><input type="radio" id="referrer_yel" name="referrer_option" value="yel" /></td>
                            <td class="option">
                                <label for="referrer_yel">by submitting your resume to Yellow Elevator so that we can review your resume and refer you if we find that you are suitable for the job position.</label>
                            </td>
                        </tr>
                    </table>
                </p>
                <p class="button"><input type="button" value="Cancel" onClick="close_refer_me();" />&nbsp;<input type="button" value="Submit Request" onClick="refer_me();" /></p>
            </form>
        </div>
        
        <?php
    }
}
?>