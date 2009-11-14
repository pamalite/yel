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
                
            </form>
        </div>
        
        <?php
    }
}
?>