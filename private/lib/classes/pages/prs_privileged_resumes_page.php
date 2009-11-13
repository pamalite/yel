<?php
require_once dirname(__FILE__). "/../../utilities.php";

class PrsPrivilegedResumesPage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_prs_privileged_resumes_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/list_box.css">'. "\n";
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/prs_privileged_resumes.css">'. "\n";
    }
    
    public function insert_prs_privileged_resumes_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/list_box.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/prs_privileged_resumes.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts($_candidate_id = '') {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo 'var candidate_id = "'. $_candidate_id. '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function generateCountries($selected = '') {
        $countries = Country::get_all();
        
        echo '<select class="field" id="country" name="country">'. "\n";
        
        if ($selected == '') {
            echo '<option value="0" selected>Please select a country</option>'. "\n";
        }
        
        foreach ($countries as $country) {
            if ($country['country_code'] != $selected) {
                echo '<option value="'. $country['country_code']. '">'. $country['country']. '</option>'. "\n";
            } else {
                echo '<option value="'. $country['country_code']. '" selected>'. $country['country']. '</option>'. "\n";
            }
        }
        
        echo '</select>'. "\n";
    }
    
    private function generateRecommenders($selected = '') {
        $mysqli = Database::connect();
        $query = "SELECT email_addr, CONCAT(firstname, ', ', lastname) AS recommender FROM recommenders";
        $result = $mysqli->query($query);
        
        echo '<select class="field" id="recommender" name="recommender">'. "\n";
        
        if ($selected == '') {
            echo '<option value="0" selected>Please select a recommender</option>'. "\n";
        }
        
        foreach ($result as $row) {
            if ($row['email_addr'] != $selected) {
                echo '<option value="'. $row['email_addr']. '">'. $row['recommender']. '</option>'. "\n";
            } else {
                echo '<option value="'. $row['email_addr']. '" selected>'. $row['recommender']. '</option>'. "\n";
            }
        }
        
        echo '</select>'. "\n";
    }
    
    private function generateIndustries() {
        $mysqli = Database::connect();
        $query = "SELECT id, industry, parent_id FROM industries";
        $result = $mysqli->query($query);
        
        echo '<select class="field" style="height: 200px;" id="recommender_industries" name="recommender_industries" multiple>'. "\n";
        
        foreach ($result as $row) {
            if (empty($row['parent_id']) || is_null($row['parent_id'])) {
                echo '<option value="'. $row['id']. '" style="font-weight: bold;">'. $row['industry']. '</option>'. "\n";
            } else {
                echo '<option value="'. $row['id']. '">&nbsp;&nbsp;&nbsp;'. $row['industry']. '</option>'. "\n";
            }
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
        $this->top_prs($this->employee->get_name(). " - Privileged Candidates");
        $this->menu_prs($this->clearances, 'resumes_privileged');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_candidates">
            <table class="buttons">
                <tr>
                    <td class="right"><input class="button" type="button" id="add_new_candidate" name="add_new_candidate" value="Add New Candidate" /></td>
                </tr>
            </table>
            <table class="header">
                <tr>
                    <td class="date"><span class="sort" id="sort_joined_on">Added On</span></td>
                    <td class="date"><span class="sort" id="sort_added_by">Added By</span></td>
                    <td class="candidate"><span class="sort" id="sort_candidate">Candidate</span></td>
                    <td class="candidate"><span class="sort" id="sort_recommender">Recommended By</span></td>
                    <td class="actions">&nbsp;</td>
                </tr>
            </table>
            <div id="div_candidates_list">
            </div>
            <table class="buttons">
                <tr>
                    <td class="right"><input class="button" type="button" id="add_new_candidate_1" name="add_new_candidate_1" value="Add New Candidate" /></td>
                </tr>
            </table>
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
                        <td class="added_on_date" colspan="2">
                            Added On: <span id="profile.joined_on">Loading...</span>
                            <span id="profile.checked_profile" style="color: #FF0000;"></span>
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
                        <td class="title" colspan="2">Recommender</td>
                    </tr>
                    <tr>
                        <td class="label">Firstnames:</td>
                        <td class="field"><span id="profile.recommender.firstname">Loading...</span></td>
                    </tr>
                    <tr>
                        <td class="label">Lastnames:</td>
                        <td class="field"><span id="profile.recommender.lastname">Loading...</span></td>
                    </tr>
                    <tr>
                        <td class="label">E-mail Address:</td>
                        <td class="field"><span id="profile.recommender.email_addr">Loading...</span></td>
                    </tr>
                    <tr>
                        <td class="label">Telephone:</td>
                        <td class="field"><span id="profile.recommender.phone_num">Loading...</span></td>
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
                <table class="buttons">
                    <tr>
                        <td class="right">
                            <input class="button" type="button" id="upload_new_resume" name="upload_new_resume" value="Upload Resume" />
                        </td>
                    </tr>
                </table>
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
                <table class="buttons">
                    <tr>
                        <td class="right">
                            <input class="button" type="button" id="upload_new_resume_1" name="upload_new_resume_1" value="Upload Resume" />
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div id="div_new_member_form">
            <div id="div_tabs">
                <ul>
                    <li class="back" id="li_back_1">&lt;&lt; Back to Privileged Candidates</li>
                </ul>
            </div>
            
            <table class="profile">
                <tr>
                    <td class="title" colspan="2">Contact Details</td>
                </tr>
                <tr>
                    <td class="label"><label for="member_firstname">Firstnames:</label></td>
                    <td class="field"><input type="text" class="field" id="member_firstname" name="member_firstname" /></td>
                </tr>
                <tr>
                    <td class="label"><label for="member_lastname">Lastnames:</label></td>
                    <td class="field"><input type="text" class="field" id="member_lastname" name="member_lastname" /></td>
                </tr>
                <tr>
                    <td class="label"><label for="member_email_addr">E-mail Address:</label></td>
                    <td class="field"><input type="text" class="field" id="member_email_addr" name="member_email_addr" /></td>
                </tr>
                <tr>
                    <td class="label"><label for="member_phone_num">Telephone:</label></td>
                    <td class="field"><input type="text" class="field" id="member_phone_num" name="member_phone_num" /></td>
                </tr>
                <tr>
                    <td class="title" colspan="2">Residence</td>
                </tr>
                <tr>
                    <td class="label"><label for="country">Country:</label></td>
                    <td class="field"><?php echo $this->generateCountries(); ?></td>
                </tr>
                <tr>
                    <td class="label"><label for="zip">Postal/Zip Code:</label></td>
                    <td class="field"><input type="text" class="field" id="zip" name="zip" /></td>
                </tr>
                <tr>
                    <td class="title" colspan="2">Remarks</td>
                </tr>
                <tr>
                    <td class="field" colspan="2" style="text-align: center;"><input type="text" class="field" name="member_remarks" id="member_remarks" value="" /></td>
                </tr>
                <tr>
                    <td class="title" colspan="2">Recommender</td>
                </tr>
                <tr>
                    <td class="label">Recommender:</td>
                    <td class="field">
                        <table class="recommender">
                            <tr>
                                <td class="option">
                                    <input type="radio" id="recommender_from_list" name="recommender_from" checked />
                                </td>
                                <td><?php echo $this->generateRecommenders(); ?></td>
                            </tr>
                            <tr>
                                <td class="option">
                                    <input type="radio" id="recommender_from_new" name="recommender_from" />
                                </td>
                                <td>
                                    <table class="new_recommender_form">
                                        <tr>
                                            <td class="label"><label for="recommender_firstname">Firstnames:</label></td>
                                            <td class="field"><input type="text" class="field" id="recommender_firstname" name="recommender_firstname" /></td>
                                        </tr>
                                        <tr>
                                            <td class="label"><label for="recommender_Lastname">Lastnames:</label></td>
                                            <td class="field"><input type="text" class="field" id="recommender_lastname" name="recommender_lastname" /></td>
                                        </tr>
                                        <tr>
                                            <td class="label"><label for="recommender_email_addr">E-mail Address:</label></td>
                                            <td class="field"><input type="text" class="field" id="recommender_email_addr" name="recommender_email_addr" /></td>
                                        </tr>
                                        <tr>
                                            <td class="label"><label for="recommender_phone_num">Telephone:</label></td>
                                            <td class="field"><input type="text" class="field" id="recommender_phone_num" name="recommender_phone_num" /></td>
                                        </tr>
                                        <tr>
                                            <td class="label"><label for="recommender_region">Region:</label></td>
                                            <td class="field"><input type="text" class="field" id="recommender_region" name="recommender_region" /></td>
                                        </tr>
                                        
                                        <tr>
                                            <td class="label"><label for="recommender_industries">Specializations:</label></td>
                                            <td class="field"><?php echo $this->generateIndustries(); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="label"><label for="recommender_remarks">Remarks:</label></td>
                                            <td class="field"><input type="text" class="field" id="recommender_remarks" name="recommender_remarks" /></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        
                    </td>
                </tr>
                <tr>
                    <td  class="buttons_bar" colspan="2"><input type="button" id="save" value="Save &amp; Add Candidate" /></td>
                </tr>
            </table>
        </div>
        
        <div id="div_upload_resume_form">
            <div id="div_tabs">
                <ul>
                    <li class="back" id="li_back_2">&lt;&lt; Back to <span id="candidate_name"></span>'s Resumes</li>
                </ul>
            </div>
            
            <form action="resumes_privileged_action.php" method="post" enctype="multipart/form-data" target="upload_target">
                <input type="hidden" id="resume_id" name="id" value="0" />
                <input type="hidden" name="resume_member_email_addr" id="resume_member_email_addr" value="" />
                <input type="hidden" name="action" value="upload_resume" />
                <p id="upload_progress" style="text-align: center;">
                    <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/progress/circle_big.gif" />
                </p>
                <p id="upload_form">
                    <table class="upload_form">
                        <tr>
                            <td class="label"><label for="my_file">Resume File:</label></td>
                            <td class="field"><input class="field" name="my_file" type="file" /><br/><span class="upload_note">1. Only HTML (*.html, *.htm), Text (*.txt), Portable Document Format (*.pdf), Rich Text Format (*.rtf) or MS Word document (*.doc) with the file size of less than 2MB are allowed. <br />2. Only ONE resume can be uploaded to the system for every resume. <br />3. You can update your resume by clicking "<span style="font-weight: bold;">Update File</span>" button in the previous page next to the file name.</span></td>
                        </tr>
                        <tr>
                            <td class="buttons_left">&nbsp;</td>
                            <td class="buttons_right"><input class="button" type="submit" id="upload_resume" name="upload_resume" value="Upload" onClick="start_upload();" /></td>
                        </tr>
                    </table>
                </p>
             </form>
             <iframe id="upload_target" name="upload_target" src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/blank.php" style="width:0px;height:0px;border:none;"></iframe>
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