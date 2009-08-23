<?php
require_once dirname(__FILE__). "/../../utilities.php";

class MemberReferPage extends Page {
    private $member = NULL;
    
    function __construct($_session) {
        $this->member = new Member($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_member_refer_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/list_box.css">'. "\n";
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/member_refer.css">'. "\n";
    }
    
    public function insert_member_refer_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/list_box.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/member_refer.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->member->id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function generate_networks_list() {
        $networks = $this->member->get_networks();
        
        echo '<select id="network_filter" name="network_filter" onChange="set_filter();">'. "\n";
        echo '<option value="0" selected>all my networks</option>'. "\n";
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        
        foreach ($networks as $network) {
            echo '<option value="'. $network['id']. '">'. $network['industry']. '</option>'. "\n";
        }
        
        echo '</select>'. "\n";
    }
    
    private function generate_all_industry_list() {
        $industries = Industry::get_main();
        
        echo '<select id="job_filter" name="job_filter" onChange="set_job_filter();">'. "\n";
        echo '<option value="0" selected>all industries</option>'. "\n";
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        
        foreach ($industries as $industry) {
            echo '<option class="main_industry" value="'. $industry['id']. '">'. $industry['industry']. '</option>'. "\n";
            
            $sub_industries = Industry::get_sub_industries_of($industry['id']);
            foreach ($sub_industries as $sub_industry) {
                echo '<option value="'. $sub_industry['id']. '">&nbsp;&nbsp;&nbsp;'. $sub_industry['industry']. '</option>'. "\n";
            }
            
        }
        
        echo '</select>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_search($this->member->get_name(). " - Refer");
        $this->menu('member', 'refer');
        
        ?>
        <div class="banner">
            To refer a job to a contact, first select a name under Contacts. <br/>You may also enter the email address of a new contact in the fieldbox below.<br/><br/>
            Then, select a job under Saved Jobs. When a job is selected, its description will be displayed under Job Description.<br/><br/>
            Finally, click on the Refer button at the bottom right corner.
        </div>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <table class="refer">
            <tr>
                <td style="background-color: #CCCCCC;"><div style="font-weight: bold;">Contacts</div></td>
                <td style="background-color: #CCCCCC;"><div style="font-weight: bold;">Saved Jobs</div></td>
                <td style="background-color: #CCCCCC;"><div style="font-weight: bold;">Job Description</div></td>
            </tr>
            <tr>
                <td>
                    <span class="filter">[ Show contacts from <?php $this->generate_networks_list(); ?> ]</span><br/>
                    <div class="candidates" id="candidates"></div>
                </td>
                <td rowspan="2">
                    <span class="filter">[ Show jobs from <?php $this->generate_all_industry_list(); ?> ]</span><br/>
                    <div class="saved_jobs" id="saved_jobs"></div>
                </td>
                <td rowspan="2" class="job_description">
                    <div class="job_description" id="job_description"><div style="text-align: center; padding-top: 50%;">Please select a job to see its job description.</div></div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="new_candidate">
                        <label for="email_addr">or, enter email address of a new contact here</label><br />
                        <input type="text" class="field" id="email_addr" name="email_addr" />
                    </div>
                </td>
            </tr>
        </table>
        <table class="buttons">
            <tr>
                <td class="left">&nbsp;</td>
                <td class="right">
                    <input class="button" type="button" id="refer" name="refer" value="Refer" />
                </td>
            </tr>
        </table>
        
        <div id="div_blanket"></div>
        <div id="div_testimony_form">
            <form onSubmit="retun false;">
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