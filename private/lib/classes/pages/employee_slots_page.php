<?php
require_once dirname(__FILE__). "/../../utilities.php";

class EmployeeSlotsPage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_slots_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employee_slots.css">'. "\n";
    }
    
    public function insert_employee_slots_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employee_slots.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_employee($this->employee->get_name(). " - Slots");
        $this->menu_employee($this->clearances, 'slots');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_slots">
            <table class="header">
                <tr>
                    <td class="date"><span class="sort" id="sort_purchased_on">Purchased On</span></td>
                    <td class="employer"><span class="sort" id="sort_employer">Employer</span></td>
                    <td class="trans_id">Transaction ID</td>
                    <td class="currency">Currency</td>
                    <td class="price_per_slot_title">Price</td>
                    <td class="number_of_slots_title">Quantity</td>
                    <td class="amount_title">Amount</td>
                    <td class="action">&nbsp;</td>
                </tr>
            </table>
            <div id="div_slots_list">
            </div>
        </div>
        <?php
    }
}
?>