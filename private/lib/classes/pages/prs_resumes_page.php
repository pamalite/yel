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
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/list_box.css">'. "\n";
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/prs_resumes.css">'. "\n";
    }
    
    public function insert_prs_resumes_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/list_box.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/prs_resumes.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts($_candidate_id = '') {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo 'var candidate_id = "'. $_candidate_id. '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function generateCountryFilters() {
        $mysqli = Database::connect();
        $query = "SELECT DISTINCT countries.country_code AS id, countries.country 
                  FROM members 
                  LEFT JOIN countries ON countries.country_code = members.country 
                  ORDER BY countries.country";
        $result = $mysqli->query($query);
        
        echo '<select id="country_filter" name="country_filter" onChange="refresh_candidates();">'. "\n";
        echo '<option value="">all countries</option>'. "\n";
        echo '<option value="" disabled>&nbsp;</option>'. "\n";
        
        foreach ($result as $row) {
            echo '<option value="'. $row['id']. '">'. $row['country']. '</option>'. "\n";
        }
        
        echo '</select>'. "\n";
    }
    
    private function generateZipFilters() {
        $mysqli = Database::connect();
        $query = "SELECT DISTINCT zip 
                  FROM members 
                  WHERE zip IS NOT NULL AND zip <> '' 
                  ORDER BY zip";
        $result = $mysqli->query($query);
        
        echo '<select id="zip_filter" name="zip_filter" onChange="refresh_candidates();">'. "\n";
        echo '<option value="">all areas</option>'. "\n";
        echo '<option value="" disabled>&nbsp;</option>'. "\n";
        
        foreach ($result as $row) {
            echo '<option value="'. $row['zip']. '">'. $row['zip']. '</option>'. "\n";
        }
        
        echo '</select>'. "\n";
    }
    
    private function generateJobIndustries() {
        $mysqli = Database::connect();
        $query = "SELECT DISTINCT industries.id, industries.industry 
                  FROM industries 
                  LEFT JOIN jobs ON industries.id = jobs.industry 
                  WHERE jobs.closed = 'N' -- AND jobs.expire_on >= NOW() 
                  ORDER BY industries.industry";
        $result = $mysqli->query($query);
        
        echo '<select id="job_industry_filter" name="job_industry_filter" onChange="set_filter();">'. "\n";
        echo '<option value="0" selected>all industries</option>'. "\n";
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        
        foreach ($result as $row) {
            echo '<option value="'. $row['id']. '">'. $row['industry']. '</option>'. "\n";
        }
        
        echo '</select>'. "\n";
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
                    <td class="country">
                        <span class="sort" id="sort_country">Country</span>
                        &nbsp;
                        <span style="font-size: 8pt;">[ From <?php $this->generateCountryFilters(); ?> ]</span>
                    </td>
                    <td class="zip">
                        <span class="sort" id="sort_zip">Postal Code</span>
                        &nbsp;
                        <span style="font-size: 8pt;">[ In <?php $this->generateZipFilters(); ?> ]</span>
                    </td>
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
                        <td class="added_on_date" colspan="2">Joined On: <span id="profile.joined_on">Loading...</span></td>
                    </tr>
                    <tr>
                        <td class="title" colspan="2">Specializations</td>
                    </tr>
                    <tr>
                        <td class="specializations" colspan="2">
                            <div style="text-align: center; padding-top: 5px; padding-bottom: 5px;">
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
                    <tr>
                        <td class="title" colspan="2">Remarks</td>
                    </tr>
                    <tr>
                        <td class="field" colspan="2" style="text-align: center;"><input type="text" class="remarks_field" name="profile.remarks" id="profile.remarks" value="" />&nbsp;<input type="button" value="Save" onClick="save_remark();" /></td>
                    </tr>
                </table>
            </div>
            
            <div id="div_resumes">
                <div style="width: 50%; margin: auto; text-align: center; padding-bottom: 5px;">
                    <span id="candidate_name" style="font-weight: bold;"></span><br/>
                    <span id="candidate_specializations"></span>
                </div>
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
        
        <div id="div_blanket"></div>
        <div id="div_job_select_form">
            <form onSubmit="return false;">
                <input type="hidden" id="job_select_form.resume_id" name="job_select_form.resume_id" value="0" />
                <table class="job_select_form">
                    <tr>
                    <td colspan="3"><p>Please select a job to refer <span id="job_select_form.candidate_name" style="font-weight: bold;"></span>...</p></td>
                    </tr>
                    <tr>
                        <td class="left">
                            <span class="filter">[ Show jobs from <?php $this->generateJobIndustries(); ?> ]</span><br/>
                            <div class="jobs" id="jobs" name="jobs"></div>
                        </td>
                        <td class="separator"></td>
                        <td class="right">
                            <div id="instructions">&lt;- Select a job to see description.</div>
                            <table id="job_details" class="job_details">
                                <tr>
                                    <td colspan="2" class="title"><span id="job_details.title">&nbsp;</span></td>
                                </tr>
                                <tr>
                                    <td class="label">Industry:</td>
                                    <td class="field"><span id="job_details.industry"></span></td>
                                </tr>
                                <tr>
                                    <td class="label">Salary:</td>
                                    <td class="field"><span id="job_details.currency"></span>&nbsp;<span id="job_details.salary"></span></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="title">Description</td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="field">
                                        <div style="width: 100%; height: 230px; overflow: auto;">
                                            <span id="job_details.description"></span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <p class="button"><input type="button" value="Cancel" onClick="close_job_select_form();" />&nbsp;<input type="button" value="Refer Now" onClick="show_testimony_form();" /></p>
            </form>
        </div>
        
        <div id="div_testimony_form">
            <form onSubmit="return false;">
                <p>1. What experience and skill-sets do <span id="testimony.candidate_name" style="font-weight: bold;"></span> have that makes him/her suitable for the <span id="testimony.job_title" style="font-weight: bold;"></span> position? (<span id="word_count_q1">0</span>/200 words)</p>
                <p><textarea class="field" id="testimony_answer_1"></textarea></p>
                <p>2. Does <span id="testimony.candidate_name" style="font-weight: bold;"></span> meet all the requirements of the <span id="testimony.job_title" style="font-weight: bold;"></span> position?</p><div style="text-align: center;"><input type="radio" id="meet_req_yes" name="meet_req" value="yes" checked /><label for="meet_req_yes">Yes</label>&nbsp;&nbsp;&nbsp;<input type="radio" id="meet_req_no" name="meet_req" value="no" /><label for="meet_req_no">No</label></div><p>Briefly describe how they are met if you choose 'Yes'. (<span id="word_count_q2">0</span>/200 words)</p>
                <p><textarea class="field" id="testimony_answer_2"></textarea></p>
                <p>3. Briefly, describe <span id="testimony.candidate_name" style="font-weight: bold;"></span>'s personality and work attitude. (<span id="word_count_q3">0</span>/200 words)</p>
                <p><textarea class="field" id="testimony_answer_3"></textarea></p>
                <p>4. Additional recommendations for <span id="testimony.candidate_name" style="font-weight: bold;"></span> (if any) ? (<span id="word_count_q4">0</span>/200 words)</p>
                <p><textarea class="field" id="testimony_answer_4"></textarea></p>
                <p class="button"><input type="button" value="Cancel" onClick="close_testimony_form();" />&nbsp;<input type="button" value="Refer Now" onClick="refer();" /></p>
            </form>
        </div>
        
        <?php
    }
}
?>