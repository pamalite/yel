<?php
require_once dirname(__FILE__). "/../../utilities.php";

class EmployeeHeadhunterTestimoniesPage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_headhunter_testimonies_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employee_headhunter_testimonies.css">'. "\n";
    }
    
    public function insert_employee_headhunter_testimonies_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employee_headhunter_testimonies.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_employee($this->employee->get_name(). " - IRC Testimonies");
        $this->menu_employee($this->clearances, 'headhunter_testimonies');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_testimonies">
            <table class="header">
                <tr>
                    <td class="date"><span class="sort" id="sort_referred_on">Referred On</span></td>
                    <td class="member"><span class="sort" id="sort_member">Referrer</span></td>
                    <td class="referee"><span class="sort" id="sort_referee">Candidate</span></td>
                    <td class="job"><span class="sort" id="sort_job">Job</span></td>
                    <td class="testimony_title">Testimony</td>
                </tr>
            </table>
            <div id="div_testimonies_list">
            </div>
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_testimony_form">
            <form onSubmit="return false;">
                <input type="hidden" id="referral_id" value="referral_id" value="" />
                <div style="width: 99%; margin: 5px 5px 5px 5px; text-align: center;">
                    Testimony from <span style="font-weight: bold;" id="member"></span> to <span style="font-weight: bold;" id="referee"></span> for <span style="font-weight: bold;" id="job"></span>.
                </div>
                <div style="width: 99%; text-align: center;"><textarea id="testimony" name="testimony"></textarea></div>
                <p class="button"><input type="button" value="Cancel" onClick="close_testimony_form();" />&nbsp;<input type="button" value="Approve" onClick="approve_testimony();" /></p>
            </form>
        </div>
        <?php
    }
}
?>