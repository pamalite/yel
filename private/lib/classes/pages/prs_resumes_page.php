<?php
require_once dirname(__FILE__). "/../../utilities.php";

class PrsResumesPage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_prs_resumes_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/prs_resumes.css">'. "\n";
    }
    
    public function insert_prs_resumes_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/prs_resumes.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_prs($this->employee->get_name(). " - Other Resumes");
        $this->menu_prs($this->clearances, 'resumes');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_candidates">
            <table class="header">
                <tr>
                    <td class="date"><span class="sort" id="sort_joined_on">Joined On</span></td>
                    <td class="candidate">
                        <span class="sort" id="sort_candidate">Candidate</span>
                        &nbsp;
                        <span style="font-size: 8pt;">[ Show <span id="candidate_filters_dropdown"></span> ]</span>
                    </td>
                    <td class="contact_data">Telephone Number</td>
                    <td class="contact_data">E-mail Address</td>
                    <td class="actions">&nbsp;</td>
                </tr>
            </table>
            <div id="div_candidates_list">
            </div>
        </div>
        
        <div id="div_candidate">
            <div id="div_tabs">
                <ul>
                    <li class="back" id="li_back">&lt;&lt;</li>
                    <li id="li_profile">Profile</li>
                    <li id="li_resumes">Resumes</li>
                </ul>
            </div>
            
            <div id="div_profile">
                <table class="profile">
                    <tr>
                        <td class="added_on_date" colspan="2">Joinedd On: <span id="profile.joined_on">Loading...</span></td>
                    </tr>
                    <tr>
                        <td class="title" colspan="2">Specializations</td>
                    </tr>
                    <tr>
                        <td class="specializations" colspan="2">
                            <div style="text-align: center;">
                                <span id="profile.specializations">Loading...</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="title" colspan="2">Contact Details</td>
                    </tr>
                    <tr>
                        <td class="label">Firstnames:</td>
                        <td class="field"><span id="profile.firstname">Loading...</span></td>
                    </tr>
                    <tr>
                        <td class="label">Lastnames:</td>
                        <td class="field"><span id="profile.lastname">Loading...</span></td>
                    </tr>
                    <tr>
                        <td class="label">E-mail address:</td>
                        <td class="field"><span id="profile.email_addr">Loading...</span></td>
                    </tr>
                    <tr>
                        <td class="label">Telephone:</td>
                        <td class="field"><span id="profile.phone_num">Loading...</span></td>
                    </tr>
                    <tr>
                        <td class="title" colspan="2">Residence</td>
                    </tr>
                    <tr>
                        <td class="label">Country:</td>
                        <td class="field"><span id="profile.country">Loading...</span></td>
                    </tr>
                    <tr>
                        <td class="label">Postal/Zip Code:</td>
                        <td class="field"><span id="profile.zip">Loading...</span></td>
                    </tr>
                </table>
            </div>
            
            <div id="div_resumes">
                 <table class="header">
                    <tr>
                        <td class="private">&nbsp;</td>
                        <td class="date"><span class="sort" id="sort_resumes_modified_on">Modified On</span></td>
                        <td class="resume_label"><span class="sort" id="sort_resumes_label">Resume Label</span></td>
                        <td class="actions">&nbsp;</td>
                    </tr>
                </table>
                <div id="div_resumes_list">
                </div>
            </div>
        </div>
        <?php
    }
}
?>