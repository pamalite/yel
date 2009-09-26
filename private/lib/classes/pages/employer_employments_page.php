<?php
require_once dirname(__FILE__). "/../../utilities.php";

class EmployerEmploymentsPage extends Page {
    private $employer = NULL;
    
    function __construct($_session) {
        $this->employer = new Employer($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employer_employments_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employer_employments.css">'. "\n";
    }
    
    public function insert_employer_employments_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employer_employments.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employer->id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->support();
        $this->top($this->employer->get_name(). "&nbsp;&nbsp;<span style=\"color: #FC8503;\">Employment Records</span>");
        $this->menu('employer', 'employments');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div id="div_employed_candidates">
            <table class="header">
                <tr>
                    <td class="industry"><span class="sort" id="sort_industry">Industry</span></td>
                    <td class="title"><span class="sort" id="sort_title">Job</span></td>
                    <td class="title"><span class="sort" id="sort_candidate">Candidate</span></td>
                    <td class="date"><span class="sort" id="sort_employed_on">Employed On</span></td>
                    <td class="date"><span class="sort" id="sort_commence_on">Commence On</a></td>
                </tr>
            </table>
            <div id="div_list">
            </div>
        </div>
        
        <?php
    }
}
?>