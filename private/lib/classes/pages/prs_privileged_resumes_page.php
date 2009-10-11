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
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/prs_privileged_resumes.css">'. "\n";
    }
    
    public function insert_prs_privileged_resumes_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/prs_privileged_resumes.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
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
                        <td class="added_on_date" colspan="2">Added On: <span id="profile.joined_on">Loading...</span></td>
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
                                            <td class="label"><label for="recommender_industries">Specializations:</label></td>
                                            <td class="field"><?php echo $this->generateIndustries(); ?></td>
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
             <iframe id="upload_target" name="upload_target" src="#" style="width:0px;height:0px;border:none;"></iframe>
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_recommender_form">
        </div>
        <?php
    }
}
?>