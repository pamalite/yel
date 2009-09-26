<?php
require_once dirname(__FILE__). "/../../utilities.php";

class EmployeeReferralsPage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_referrals_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employee_referrals.css">'. "\n";
    }
    
    public function insert_employee_referrals_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employee_referrals.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_employee($this->employee->get_name(). " - Referrals");
        $this->menu_employee($this->clearances, 'referrals');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_acknowledged">
            <table class="header">
                <tr>
                    <td class="employer"><span class="sort" id="sort_employer">Employer</span></td>
                    <td class="title"><span class="sort" id="sort_title">Job</span></td>
                    <td class="referrer"><span class="sort" id="sort_referrer">Referrer</span></td>
                    <td class="candidate"><span class="sort" id="sort_candidate">Candidate</span></td>
                    <td class="date"><span class="sort" id="sort_referred_on">Referred On</span></td>
                    <td class="date"><span class="sort" id="sort_acknowledged_on">Candidate Responded On</span></td>
                    <td class="date"><span class="sort" id="sort_member_confirmed_on">Referrer Submitted On</span></td>
                    <td class="date"><span class="sort" id="sort_agreed_terms_on">Employer Viewed Resume On</span></td>
                    <td class="date"><span class="sort" id="sort_employed_on">Employed On</span></td>
                    <td class="date"><span class="sort" id="sort_commence_on">Work Commence On</span></td>
                    <td class="date"><span class="sort" id="sort_confirmed_on">Candidate Confirmed Employment</span></td>
                    <td class="date"><span class="sort" id="sort_coe_received_on">Offer Letter Received</span></td>
                </tr>
            </table>
            <div id="div_acknowledged_referrals_list">
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