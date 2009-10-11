<?php
require_once dirname(__FILE__). "/../../utilities.php";

class PrsRecommendersPage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_prs_recommenders_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/prs_recommenders.css">'. "\n";
    }
    
    public function insert_prs_recommenders_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/prs_recommenders.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function generateIndustries($_for_profile = false;) {
        $mysqli = Database::connect();
        $query = "SELECT id, industry, parent_id FROM industries";
        $result = $mysqli->query($query);
        
        if ($_for_profile) {
            echo '<select class="field" style="height: 200px;" id="profile_industries" name="profile_industries" multiple>'. "\n";
        } else {
            echo '<select class="field" style="height: 200px;" id="recommender_industries" name="recommender_industries" multiple>'. "\n";
        }
        
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
        $this->top_prs($this->employee->get_name(). " - Recommenders");
        $this->menu_prs($this->clearances, 'recommenders');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_candidates">
            <table class="buttons">
                <tr>
                    <td class="right"><input class="button" type="button" id="add_new_recommender" name="add_new_recommender" value="Add New Recommender" /></td>
                </tr>
            </table>
            <table class="header">
                <tr>
                    <td class="date"><span class="sort" id="sort_added_on">Added On</span></td>
                    <td class="recommender"><span class="sort" id="sort_recommender">Recommender</span></td>
                    <td class="industries">Specializations</td>
                    <td class="actions">&nbsp;</td>
                </tr>
            </table>
            <div id="div_recommenders_list">
            </div>
            <table class="buttons">
                <tr>
                    <td class="right"><input class="button" type="button" id="add_new_recommender_1" name="add_new_recommender_1" value="Add New Recommender" /></td>
                </tr>
            </table>
        </div>
        
        <div id="div_recommender">
            <div id="div_tabs">
                <ul>
                    <li class="back" id="li_back">&lt;&lt;</li>
                    <li id="li_profile">Profile</li>
                    <li id="li_candidates">Candidates</li>
                </ul>
            </div>
            
            <div id="div_profile">
                <table class="profile">
                    <tr>
                        <td class="added_on_date" colspan="2">Added On: <span id="profile.added_on">Loading...</span></td>
                    </tr>
                    <tr>
                        <td class="title" colspan="2">Contact Details</td>
                    </tr>
                    <tr>
                        <td class="label">E-mail Address:</td>
                        <td class="field"><span id="profile_email_addr">Loading...</span></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="profile_firstname">Firstnames:</label></td>
                        <td class="field"><input type="text" class="field" id="profile_firstname" name="profile_firstname" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="profile_lastname">Lastnames:</label></td>
                        <td class="field"><input type="text" class="field" id="profile_lastname" name="profile_lastname" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="profile_phone_num">Telephone:</label></td>
                        <td class="field"><input type="text" class="field" id="profile_phone_num" name="profile_phone_num" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="profile_industries">Specializations:</label></td>
                        <td class="field"><?php echo $this->generateIndustries(true); ?></td>
                    </tr>
                    <tr>
                        <td  class="buttons_bar" colspan="2"><input type="button" id="save" value="Save Profile" /></td>
                    </tr>
                </table>
            </div>
            
            <div id="div_candidates">
                <table class="header">
                    <tr>
                        <td class="date"><span class="sort" id="sort_join_on">Joined On</span></td>
                        <td class="candidate"><span class="sort" id="sort_candidate">Candidate</span></td>
                        <td class="actions">&nbsp;</td>
                    </tr>
                </table>
                <div id="div_candidates_list">
                </div>
            </div>
        </div>
        
        <div id="div_new_candidate_form">
            <div id="div_tabs">
                <ul>
                    <li class="back" id="li_back_1">&lt;&lt; Back to Recommenders</li>
                </ul>
            </div>
            
            <table class="profile">
                <tr>
                    <td class="title" colspan="2">Contact Details</td>
                </tr>
                <tr>
                    <td class="label"><label for="recommender_firstname">Firstnames:</label></td>
                    <td class="field"><input type="text" class="field" id="recommender_firstname" name="recommender_firstname" /></td>
                </tr>
                <tr>
                    <td class="label"><label for="recommender_lastname">Lastnames:</label></td>
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
                <tr>
                    <td  class="buttons_bar" colspan="2"><input type="button" id="add" value="Save &amp; Add Recommender" /></td>
                </tr>
            </table>
        </div>
        
        <?php
    }
}
?>