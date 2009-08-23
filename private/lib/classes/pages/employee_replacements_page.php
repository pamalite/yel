<?php
require_once dirname(__FILE__). "/../../utilities.php";

class EmployeeReplacementsPage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_replacements_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employee_replacements.css">'. "\n";
    }
    
    public function insert_employee_replacements_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employee_replacements.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_employee($this->employee->get_name(). " - Replacements");
        $this->menu_employee($this->clearances, 'replacements');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_replacements">
            <table class="header">
                <tr>
                    <td class="invoice">Receipt</td>
                    <td class="employer">Employer</td>
                    <td class="title">Job</td>
                    <td class="member">Referrer</td>
                    <td class="member">Candidate</td>
                    <td class="date">Employed On</td>
                    <td class="action">&nbsp;</td>
                </tr>
            </table>
            <div id="div_replacements_list">
            </div>
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_contact">
            <p class="name" id="name"></p>
            <table class="contact">
                <tr>
                    <td class="label">Telephone:</td>
                    <td class="field"><span id="telephone"></span></td>
                </tr>
                <tr>
                    <td class="label">E-mail:</td>
                    <td class="field"><span id="email_addr"></span></td>
                </tr>
            </table>
            <p class="buttons"><input type="button" value="Close" onClick="close_contact();" /></p>
        </div>
        <?php
    }
}
?>