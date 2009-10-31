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
        <?php
    }
}
?>