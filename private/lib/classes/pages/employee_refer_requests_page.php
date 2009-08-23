<?php
require_once dirname(__FILE__). "/../../utilities.php";

class EmployeeReferRequestsPage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_refer_requests_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employee_refer_requests.css">'. "\n";
    }
    
    public function insert_employee_refer_requests_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employee_refer_requests.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_employee($this->employee->get_name(). " - Refer Requests");
        $this->menu_employee($this->clearances, 'refer_requests');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_requests">
            <table class="header">
                <tr>
                    <td class="member"><span class="sort" id="sort_member">Member</span></td>
                    <td class="job"><span class="sort" id="sort_job">Job</span></td>
                    <td class="date"><span class="sort" id="sort_requested_on">Requested On</span></td>
                    <td class="actions">&nbsp;</td>
                </tr>
            </table>
            <div id="div_requests_list">
            </div>
        </div>
        <?php
    }
}
?>