<?php
require_once dirname(__FILE__). "/../../utilities.php";

class PrsUploadedResumesPage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_prs_uploaded_resumes_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/prs_uploaded_resumes.css">'. "\n";
    }
    
    public function insert_prs_uploaded_resumes_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/prs_uploaded_resumes.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function generateRecommenderIndustries() {
        $mysqli = Database::connect();
        $query = "SELECT id, industry, parent_id FROM industries";
        $result = $mysqli->query($query);
        
        echo '<select class="mini_field" style="height: 200px;" id="recommender_industries" name="recommender_industries" multiple>'. "\n";
        
        foreach ($result as $row) {
            if (empty($row['parent_id']) || is_null($row['parent_id'])) {
                echo '<option value="'. $row['id']. '" style="font-weight: bold;">'. $row['industry']. '</option>'. "\n";
            } else {
                echo '<option value="'. $row['id']. '">&nbsp;&nbsp;&nbsp;'. $row['industry']. '</option>'. "\n";
            }
        }
        
        echo '</select>'. "\n";
    }
    
    private function generateMemberIndustries($_id) {
        $mysqli = Database::connect();
        $query = "SELECT id, industry, parent_id FROM industries";
        $result = $mysqli->query($query);
        
        echo '<select class="mini_field" id="'. $_id. '" name="'. $_id. '">'. "\n";
        echo '<option value="0" selected>Select a specialization</option>'. "\n";
        echo '<option value="0" disabled>&nbsp;</option>'. "\n";
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
        $this->top_prs($this->employee->get_name(). " - Uploaded Resumes");
        $this->menu_prs($this->clearances, 'resumes_uploaded');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_candidates">
            <table class="header">
                <tr>
                    <td class="date"><span class="sort" id="sort_added_on">Uploaded On</span></td>
                    <td class="candidate"><span class="sort" id="sort_candidate">Candidate</span></td>
                    <td class="candidate"><span class="sort" id="sort_referrer">Recommended By</span></td>
                    <td class="resume">Resume</td>
                    <td class="job"><span class="sort" id="sort_job">Job</span></td>
                    <td class="actions">&nbsp;</td>
                </tr>
            </table>
            <div id="div_candidates_list">
            </div>
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_add_to_privileged_form">
            <form onSubmit="return false;">
                <table class="add_to_privileged_form">
                    <tr>
                        <td class="left">
                            <div style="font-size: 12pt; font-weight: bold; padding-bottom: 15px;">Recommender</div>
                            <table class="candidate_form">
                                <tr>
                                    <td class="label"><label for="recommender_region">Region:</label></td>
                                    <td class="field">
                                        <input type="text" class="mini_field" id="recommender_region" name="recommender_region" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="recommender_remark">Remark:</label></td>
                                    <td class="field">
                                        <input type="text" class="mini_field" id="recommender_remark" name="recommender_remark" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="recommender_industries">Specializations:</label></td>
                                    <td class="field">
                                        <?php $this->generateRecommenderIndustries(); ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td class="separator"></td>
                        <td class="right">
                            <div style="font-size: 12pt; font-weight: bold; padding-bottom: 15px;">Candidate</div>
                            <table class="candidate_form">
                                <tr>
                                    <td class="label"><label for="candidate_remark">Remark:</label></td>
                                    <td class="field">
                                        <input type="text" class="mini_field" id="candidate_remark" name="candidate_remark" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="candidate_primary_industry">Primary Industry:</label></td>
                                    <td class="field">
                                        <?php $this->generateMemberIndustries('candidate_primary_industry'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="candidate_secondary_industry">Secondary Industry:</label></td>
                                    <td class="field">
                                        <?php $this->generateMemberIndustries('candidate_secondary_industry'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><label for="candidate_tertiary_industry">Tertiary Industry:</label></td>
                                    <td class="field">
                                        <?php $this->generateMemberIndustries('candidate_tertiary_industry'); ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <p class="button"><input type="button" value="Cancel" onClick="close_add_to_privileged_form();" />&nbsp;<input type="button" value="OK" onClick="add_to_privileged();" /></p>
            </form>
        </div>
        
        <?php
    }
}
?>