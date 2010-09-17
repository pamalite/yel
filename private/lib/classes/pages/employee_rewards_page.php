<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). "/../htmltable.php";

class EmployeeRewardsPage extends Page {
    private $employee = NULL;
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_rewards_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employee_rewards.css">'. "\n";
    }
    
    public function insert_employee_rewards_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/flextable.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employee_rewards.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->getId(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->getUserId(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function get_rewards($_is_paid = false) {
        $criteria = array(
            'columns' => "invoices.id AS invoice, referrals.id AS referral, referrals.total_reward,
                          referrals.job AS job_id, currencies.symbol AS currency, jobs.title, 
                          referrals.member AS member_id, referrals.employed_on, 
                          employers.name AS employer, members.phone_num, 
                          CONCAT(members.lastname, ', ', members.firstname) AS member, 
                          DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on, 
                          (SUM(referral_rewards.reward) / 3) AS paid_reward", 
            'joins' => "invoice_items ON invoice_items.item = referrals.id, 
                        invoices ON invoices.id = invoice_items.invoice, 
                        referral_rewards ON referral_rewards.referral = referrals.id, 
                        jobs ON jobs.id = referrals.job, 
                        members ON members.email_addr = referrals.member, 
                        employers ON employers.id = jobs.employer, 
                        currencies ON currencies.country_code = employers.country",
            'match' => "invoices.type = 'R' AND 
                        (invoices.paid_on IS NOT NULL AND invoices.paid_on <> '0000-00-00 00:00:00') AND 
                        (referrals.employed_on IS NOT NULL AND referrals.employed_on <> '0000-00-00 00:00:00') AND 
                        (referrals.employer_removed_on IS NULL OR referrals.employer_removed_on = '0000-00-00 00:00:00') AND 
                        (referrals.referee_rejected_on IS NULL OR referrals.referee_rejected_on = '0000-00-00 00:00:00') AND 
                        (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') AND 
                        (referrals.guarantee_expire_on <= CURDATE() OR referrals.guarantee_expire_on IS NULL)                        ", 
            'group' => "referrals.id", 
            'order' => "referrals.employed_on",
            'having' => "(paid_reward < referrals.total_reward OR paid_reward IN NULL)"
        );
        
        if ($_is_paid) {
            $criteria['columns'] .= ", referral_rewards.reward_equivalent";
            $criteria['having'] = "(paid_reward >= referrals.total_reward OR referral_rewards.reward_equivalent IS NOT NULL)";
        } else {
            $criteria['match'] .= "AND (referral_rewards.reward_equivalent IS NULL OR referral_rewards.reward_equivalent = '')";
        }
        
        $referral = new Referral();
        return $referral->find($criteria);
    }
    
    public function show() {
        $this->begin();
        $this->top('Rewards');
        $this->menu_employee('rewards');
        
        $new_rewards = $this->get_rewards();
        foreach ($new_rewards as $i=>$row) {
            $new_rewards[$i]['member'] = htmlspecialchars_decode(stripslashes($row['member']));
            $new_rewards[$i]['employer'] = htmlspecialchars_decode(stripslashes($row['employer']));
            $new_rewards[$i]['title'] = htmlspecialchars_decode(stripslashes($row['title']));
            $new_rewards[$i]['padded_invoice'] = pad($row['invoice'], 11, '0');
            $new_rewards[$i]['total_reward'] = number_format($row['total_reward'], 2, '.', ', ');
            $new_rewards[$i]['paid_reward'] = number_format($row['paid_reward'], 2, '.', ', ');
        }
        
        $paid_rewards = $this->get_rewards(true);
        foreach ($paid_rewards as $i=>$row) {
            $paid_rewards[$i]['member'] = htmlspecialchars_decode(stripslashes($row['member']));
            $paid_rewards[$i]['employer'] = htmlspecialchars_decode(stripslashes($row['employer']));
            $paid_rewards[$i]['title'] = htmlspecialchars_decode(stripslashes($row['title']));
            $paid_rewards[$i]['padded_invoice'] = pad($row['invoice'], 11, '0');
            $paid_rewards[$i]['total_reward'] = number_format($row['total_reward'], 2, '.', ', ');
            $paid_rewards[$i]['paid_reward'] = number_format($row['paid_reward'], 2, '.', ', ');
            $paid_rewards[$i]['reward_equivalent'] = htmlspecialchars_decode(stripslashes($row['reward_equivalent']));
        }
        
        ?>
        <!-- submenu -->
        <div class="menu">
            <ul class="menu">
                <li id="item_new_rewards" style="background-color: #CCCCCC;"><a class="menu" onClick="show_new_rewards();">New</a></li>
                <li id="item_paid_rewards"><a class="menu" onClick="show_paid_rewards();">Paid</a></li>
            </ul>
        </div>
        <!-- end submenu -->
        
        <!-- div class="banner">
            An administration fee of <?php //echo $currency ?> 2.00 will be charged to the referrers for every transfer of rewards into their bank accounts. <br/><br/>Always remember to ensure that the <?php // echo $currency ?> 2.00 administration fee is taken into considration when making an online bank transaction.
        </div -->
        
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_new_rewards">
            <table class="header">
                <tr>
                    <td class="invoice"><span class="sort" id="sort_invoice">Receipt</span></td>
                    <td class="employer"><span class="sort" id="sort_employer">Employer</span></td>
                    <td class="title"><span class="sort" id="sort_title">Job</span></td>
                    <td class="member"><span class="sort" id="sort_member">Referrer</span></td>
                    <td class="date"><span class="sort" id="sort_employed_on">Employed On</span></td>
                    <td class="reward_title"><span class="sort" id="sort_reward">Total Reward</span></td>
                    <td class="action">&nbsp;</td>
                </tr>
            </table>
            <div id="div_new_rewards_list">
            </div>
        </div>
        
        <div id="div_partially_paid_rewards">
            <table class="header">
                <tr>
                    <td class="invoice"><span class="sort" id="sort_partially_paid_invoice">Receipt</span></td>
                    <td class="employer"><span class="sort" id="sort_partially_paid_employer">Employer</span></td>
                    <td class="title"><span class="sort" id="sort_partially_paid_title">Job</span></td>
                    <td class="member"><span class="sort" id="sort_partially_paid_member">Referrer</span></td>
                    <td class="date"><span class="sort" id="sort_partially_paid_employed_on">Employed On</span></td>
                    <td class="date"><span class="sort" id="sort_partially_paid_last_paid_on">Last Paid On</span></td>
                    <td class="reward_title"><span class="sort" id="sort_partially_paid_paid">Amount Paid</span></td>
                    <td class="reward_title"><span class="sort" id="sort_partially_paid_reward">Total Reward</span></td>
                    <td class="action">&nbsp;</td>
                </tr>
            </table>
            <div id="div_partially_paid_rewards_list">
            </div>
        </div>
        
        <div id="div_fully_paid_rewards">
            <table class="header">
                <tr>
                    <td class="invoice"><span class="sort" id="sort_fully_paid_invoice">Receipt</span></td>
                    <td class="employer"><span class="sort" id="sort_fully_paid_employer">Employer</span></td>
                    <td class="title"><span class="sort" id="sort_fully_paid_title">Job</span></td>
                    <td class="member"><span class="sort" id="sort_fully_paid_member">Referrer</span></td>
                    <td class="date"><span class="sort" id="sort_fully_paid_employed_on">Employed On</span></td>
                    <td class="date"><span class="sort" id="sort_fully_paid_fully_paid_on">Fully Paid On</span></td>
                    <td class="reward_title"><span class="sort" id="sort_fully_paid_paid">Amount Paid</span></td>
                    <td class="reward_title"><span class="sort" id="sort_fully_paid_reward">Total Reward</span></td>
                    <td class="action">&nbsp;</td>
                </tr>
            </table>
            <div id="div_fully_paid_rewards_list">
            </div>
        </div>
        
        <div id="div_payments">
            <div class="payment_info">
                Payments history for member <span id="member_name" style="font-weight: bold;"></span><br/>referring the job <span id="job_title" style="font-weight: bold;"></span> (<span id="job_employer" style="font-weight: bold;"></span>)<br/>with the total reward of <span id="total_reward" style="font-weight: bold;"></span> to be paid.
            </div>
            <div id="payments_button" class="button"></div>
            <table class="header">
                <tr>
                    <td class="date"><span class="sort" id="sort_payments_paid_on">Paid On</span></td>
                    <td class="mode"><span class="sort" id="sort_payments_payment_mode">Payment Mode</span></td>
                    <td class="bank"><span class="sort" id="sort_payments_bank">Bank Account</span></td>
                    <td class="cheque"><span class="sort" id="sort_payments_cheque">Cheque</span></td>
                    <td class="receipt"><span class="sort" id="sort_payments_receipt">Receipt</span></td>
                    <td class="reward_title">Amount Paid (<span id="payment_info.currency"></span>)</td>
                </tr>
            </table>
            <div id="div_payments_list">
            </div>
            <table class="total_amount">
                <tr>
                    <td class="date">&nbsp;</td>
                    <td class="mode">&nbsp;</td>
                    <td class="bank">&nbsp;</td>
                    <td class="cheque">&nbsp;</td>
                    <td class="receipt">&nbsp;</td>
                    <td class="reward_title">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="5" class="label">Total Paid (<span id="payment_info.total_paid.currency"></span>)</td>
                    <td class="reward"><span id="total_amount">0.00</span></td>
                </tr>
            </table>
            <div id="payments_button_1" class="button"></div>
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
        
        <div id="div_payment_plan">
            <div style="padding-left: 5px; padding-right: 5px;">
                <p class="instructions">Payment plan of <span id="plan_reward" style="font-weight: bold;"></span> beginning from <span id="plan_employed_on" style="font-weight: bold;"></span> for every 30 days in 180 days (or 6 months).</p>
                <table class="header">
                    <tr>
                        <td class="days">Days</td>
                        <td class="date">Due On</td>
                        <td class="amount_title">Amount (<span id="payment_plan.currency"></span>)</td>
                    </tr>
                </table>
                <div id="payment_plan_list">
                </div>
            </div>
            <p class="button"><input class="button" type="button" value="Close" onClick="close_payment_plan();" /></p>
        </div>
        <?php
    }
}
?>