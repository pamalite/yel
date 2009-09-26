<?php
require_once dirname(__FILE__). "/../../utilities.php";

class EmployeeTokenRewardsPage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_token_rewards_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employee_token_rewards.css">'. "\n";
    }
    
    public function insert_employee_token_rewards_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employee_token_rewards.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_employee($this->employee->get_name(). " - Tokens");
        $this->menu_employee($this->clearances, 'tokens');
        
        $query = "SELECT currencies.symbol 
                  FROM employees 
                  LEFT JOIN branches ON branches.id = employees.branch 
                  LEFT JOIN currencies ON currencies.country_code = branches.country 
                  WHERE employees.id = ". $this->employee->id(). " LIMIT 1";
        $mysqli = Database::connect();
        $result = $mysqli->query($query);
        $currency = '??? $';
        if ($result !== false) {
            $currency = $result[0]['symbol'];
        }
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div id="div_tabs">
            <ul>
                <li id="li_new">New</li>
                <li id="li_fully_paid">Paid</li>
            </ul>
        </div>
        
        <div id="div_new_rewards">
            <table class="header">
                <tr>
                    <td class="employer"><span class="sort" id="sort_employer">Employer</span></td>
                    <td class="title"><span class="sort" id="sort_title">Job</span></td>
                    <td class="member"><span class="sort" id="sort_member">Candidate</span></td>
                    <td class="date"><span class="sort" id="sort_employed_on">Employed On</span></td>
                    <td class="date"><span class="sort" id="sort_confirmed_on">Confirm Employed On</span></td>
                    <td class="reward_title"><span class="sort" id="sort_reward">Total Bonus</span></td>
                    <td class="action">&nbsp;</td>
                </tr>
            </table>
            <div id="div_new_rewards_list">
            </div>
        </div>
        
        <div id="div_fully_paid_rewards">
            <table class="header">
                <tr>
                    <td class="employer"><span class="sort" id="sort_fully_paid_employer">Employer</span></td>
                    <td class="title"><span class="sort" id="sort_fully_paid_title">Job</span></td>
                    <td class="member"><span class="sort" id="sort_fully_paid_member">Candidate</span></td>
                    <td class="date"><span class="sort" id="sort_fully_paid_employed_on">Employed On</span></td>
                    <td class="date"><span class="sort" id="sort_fully_paid_confirmed_on">Confirm Employed On</span></td>
                    <td class="date"><span class="sort" id="sort_fully_paid_fully_paid_on">Paid On</span></td>
                    <td class="reward_title"><span class="sort" id="sort_fully_paid_reward">Total Bonus</span></td>
                    <td class="payment_details">Payment Details</td>
                </tr>
            </table>
            <div id="div_fully_paid_rewards_list">
            </div>
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_payment_form">
            <form method="post" onSubmit="return false;">
                <input type="hidden" id="referral_id" name="referral_id" value="0" />
                <p class="instructions">Please enter the following fields to confirm payment of <span id="reward" style="font-weight: bold;"></span> to <span id="member" style="font-weight: bold;"></span>.</p>
                <table id="payment_form" class="payment_form">
                    <tr>
                        <td class="label"><label for="amount">Amount (<span id="payment_form.currency"></span>):</label></td>
                        <td class="field"><input class="field" type="text" id="amount" name="amount" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="payment_mode">Payment mode:</label></td>
                        <td class="field">
                            <select id="payment_mode" name="payment_mode">
                                <option value="IBT" selected>Bank Transfer</option>
                                <option value="CSH">Cash</option>
                                <option value="CHQ">Cheque</option>
                                <option value="CDB">Bank on-behalf</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="accounts_dropdown">Account:</label></td>
                        <td class="field"><span id="accounts_list"></span></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="cheque">Cheque:</label></td>
                        <td class="field"><input class="field" type="text" id="cheque" name="cheque" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="receipt">Receipt:</label></td>
                        <td class="field"><input class="field" type="text" id="receipt" name="receipt" /></td>
                    </tr>
                </table>
                <p class="button"><input class="button" type="button" value="Cancel" onClick="close_payment_form();" />&nbsp;<input class="button" type="button" id="save_bank" name="save_bank" value="Confirm Payment" onClick="confirm_payment();" /></p>
            </form>
        </div>
        
        <?php
    }
}
?>