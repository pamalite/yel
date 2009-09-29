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
    
    public function show() {
        $this->begin();
        $this->top_prs($this->employee->get_name(). " - Privileged Candidates");
        $this->menu_prs($this->clearances, 'resumes_privileged');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_candidates">
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
        </div>
        
        <div id="div_candidate">
            <div id="div_tabs">
                <ul>
                    <li id="li_profile"><span id="profile_back_arrow">&lt;&lt;&nbsp;</span>Profile</li>
                    <li id="li_resumes"><span id="resumes_back_arrow">&lt;&lt;&nbsp;</span>Resumes</li>
                </ul>
            </div>
            
            
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_recommender_form">
        </div>
        <?php
    }
}
?>