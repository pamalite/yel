<?php
require_once dirname(__FILE__). "/../../utilities.php";

class EmployeeAdminEmployersPage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_admin_employers_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employee_admin_employers.css">'. "\n";
    }
    
    public function insert_employee_admin_employers_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employee_admin_employers.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_employee($this->employee->get_name(). " - Employers");
        $this->menu_employee($this->clearances, 'admin_employers');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_employers">
            <table class="header">
                <tr>
                    <td class="user_id"><span class="sort" id="sort_user_id">User ID</span></td>
                    <td class="employer"><span class="sort" id="sort_employer">Employer</span></td>
                    <td class="date"><span class="sort" id="sort_days_left">Days To Expiry</span></td>
                    <td class="date"><span class="sort" id="sort_joined_on">Joined On</span></td>
                    <td class="actions">&nbsp;</td>
                </tr>
            </table>
            <div id="div_employers_list">
            </div>
        </div>
        <?php
    }
}
?>