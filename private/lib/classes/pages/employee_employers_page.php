<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). "/../../../config/subscriptions_rate.inc";

class EmployeeEmployersPage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_employers_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employee_employers.css">'. "\n";
    }
    
    public function insert_employee_employers_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employee_employers.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_employee($this->employee->get_name(). " - My Employers");
        $this->menu_employee($this->clearances, 'employers');
        
        $subscriptions_rates = $GLOBALS['subscriptions_rates'];
        $branch = $this->employee->get_branch();
        $available_subscriptions = $subscriptions_rates[Currency::symbol_from_country_code($branch[0]['country_code'])];
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_employers">
            <table class="buttons">
                <tr>
                    <td class="left"><input class="button" type="button" id="deactivate_employers" name="deactivate_employers" value="Deactivate Selected Employers" onClick="deactivate_employers();" /></td>
                    <td class="right"><input class="button" type="button" id="add_new_employer" name="add_new_employer" value="Add New Employer" /></td>
                </tr>
            </table>
            <table class="header">
                <tr>
                    <td class="checkbox"><input type="checkbox" id="deactivate_all" /></td>
                    <td class="user_id"><span class="sort" id="sort_user_id">User ID</span></td>
                    <td class="employer"><span class="sort" id="sort_employer">Employer</span></td>
                    <td class="employer"><span class="sort" id="sort_employee">Created By</span></td>
                    <td class="date"><span class="sort" id="sort_joined_on">Joined On</span></td>
                    <td class="date"><span class="sort" id="sort_first_login">First Signed In On</span></td>
                    <td class="actions">&nbsp;</td>
                </tr>
            </table>
            <div id="div_employers_list">
            </div>
            <table class="buttons">
                <tr>
                    <td class="left"><input class="button" type="button" id="deactivate_employers_1" name="deactivate_employers_1" value="Deactivate Selected Employers" onClick="deactivate_employers();" /></td>
                    <td class="right"><input class="button" type="button" id="add_new_employer_1" name="add_new_employer_1" value="Add New Employer" /></td>
                </tr>
            </table>
        </div>
        
        <div id="div_employer">
            <input type="hidden" id="employer_id" name="employer_id" value="" />
            <div id="div_tabs">
                <ul>
                    <li id="li_employers">&lt;&lt;</li>
                    <li id="li_profile">Profile</li>
                    <li id="li_service_fees">Service Fees</li>
                    <li id="li_extra_fees">Extra Charges</li>
                </ul>
            </div>
            
            <div id="div_profile">
                <form id="profile" method="post" onSubmit="return false;">
                    <table class="profile_form">
                        <tr>
                            <td class="buttons_bar" colspan="2"><input type="button" id="save_profile" value="Save &amp; Update Profile" /></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="business_license">Company/Business Registration No.:</label></td>
                            <td class="field"><input class="field" type="text" id="business_license" name="business_license" value="" onChange="profile_is_dirty();" /></td>
                        </tr>
                        <tr>
                            <td class="title" colspan="2">Sign In Details</td>
                        </tr>
                        <tr>
                            <td class="label">User ID:</td>
                            <td class="field"><span id="user_id_placeholder"></span><span id="user_id_warning"></span></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="password">Password:</label></td>
                            <td class="field"><span id="password_placeholder"></span></td>
                        </tr>
                        <tr>
                            <td class="title" colspan="2">Contact Details</td>
                        </tr>
                        <tr>
                            <td class="label"><label for="email">E-mail Address:</label></td>
                            <td class="field"><input class="field" type="text" id="email" name="email" value=""  onChange="profile_is_dirty();" /></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="name">Business Name:</label></td>
                            <td class="field"><input class="field" type="text" id="name" name="name" value="" onChange="profile_is_dirty();" /></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="contact_person">Contact Person:</label></td>
                            <td class="field"><input class="field" type="text" id="contact_person" name="contact_person" value="" onChange="profile_is_dirty();" /></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="phone_num">Telephone Number:</label></td>
                            <td class="field"><input class="field" type="text" id="phone_num" name="phone_num" value="" onChange="profile_is_dirty();" /></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="address">Mailing Address:</label></td>
                            <td class="field"><textarea id="address" name="address" onChange="profile_is_dirty();"></textarea></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="state">State/Province/Area:</label></td>
                            <td class="field"><input class="field" type="text" id="state" name="state" value=""  onChange="profile_is_dirty();" /></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="zip">Zip/Postal Code:</label></td>
                            <td class="field"><input class="field" type="text" id="zip" name="zip" value="" onChange="profile_is_dirty();" /></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="country">Country:</label></td>
                            <td class="field"><span id="country_dropdown_list">Loading...</span></</td>
                        </tr>
                        <tr>
                            <td class="label"><label for="website_url">Web-site URL:</label></td>
                            <td class="field"><input class="field" type="text" id="website_url" name="website_url" value="" onChange="profile_is_dirty();" /></td>
                        </tr>
                        <tr>
                            <td class="title" colspan="2">Payment &amp; Subscription Details</td>
                        </tr>
                        <tr>
                            <td class="label"><label for="working_months">Working Months:</label></td>
                            <td class="field"><input class="field_number" type="text" id="working_months" name="working_months" value="12" maxlength="2" onChange="profile_is_dirty();" /></td>
                        </tr>
                        <!--tr>
                            <td class="label"><label for="bonus_months">Bonus Months:</label></td>
                            <td class="field"><input class="field_number" type="text" id="bonus_months" name="bonus_months" value="1" maxlength="2" onChange="profile_is_dirty();" /></td>
                        </tr-->
                        <tr>
                            <td class="label"><label for="payment_terms_days">Payment Terms:</label></td>
                            <td class="field">
                                <select id="payment_terms_days" name="payment_terms_days"  onChange="profile_is_dirty();">
                                    <option value="30" selected>30 days</option>
                                    <option value="60">60 days</option>
                                    <option value="90">90 days</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="subscription_period">Subscription:</label></td>
                            <td class="field">
                                <span id="subscription_period_label"></span>
                                <select id="subscription_period" name="subscription_period"  onChange="profile_is_dirty();">
                                    <option value="0" selected>No subscription purchased</option>
                                    <option value="0" disabled>&nbsp;</option>
                                    <option value="1">1 month</option>
                                <?php
                                foreach ($available_subscriptions as $month => $price) {
                                ?>
                                    <option value="<?php echo $month; ?>"><?php echo $month; ?> months</option>
                                <?php
                                }
                                ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="free_postings">Free Job Postings:</label></td>
                            <td class="field"><input class="field_number" type="text" id="free_postings" name="free_postings" value="1" maxlength="2" onChange="profile_is_dirty();" /></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="paid_postings">Paid Job Postings:</label></td>
                            <td class="field">
                                <span id="paid_postings_label">0</span>
                                &nbsp;
                                Add: 
                                <input class="field_number" type="text" id="paid_postings" name="paid_postings" value="0" maxlength="2" onChange="profile_is_dirty();" />
                            </td>
                        </tr>
                        <tr>
                            <td class="buttons_bar" colspan="2"><input type="button" id="save_profile_1" value="Save &amp; Update Profile" /></td>
                        </tr>
                    </table>
                </form>
            </div>
            
            <div id="div_service_fees">
                <table class="buttons">
                    <tr>
                        <td class="left"><input class="button" type="button" id="delete_service_fees" name="delete_service_fees" value="Delete Selected Fees" onClick="delete_fees();" /></td>
                        <td class="right"><input class="button" type="button" id="add_new_service_fee" name="add_new_service_fee" value="Add New Fee" /></td>
                    </tr>
                </table>
                <table class="header">
                    <tr>
                        <td class="checkbox"><input type="checkbox" id="delete_all_service_fees" /></td>
                        <td class="salary_start_title">Annual Salary Start</td>
                        <td class="salary_end_title">Annual Salary End</td>
                        <td class="guaranteed_months_title">Guaranteed Period (in months)</td>
                        <td class="service_fee_title">Service Fee (%)</td>
                        <td class="discount_title">Discount (%)</td>
                        <td class="reward_percentage_title">Reward (%)</td>
                        <td class="actions">&nbsp;</td>
                    </tr>
                </table>
                <div id="div_service_fees_list">
                </div>
                <table class="buttons">
                    <tr>
                        <td class="left"><input class="button" type="button" id="delete_service_fees_1" name="delete_service_fees_1" value="Delete Selected Fees" onClick="delete_fees();" /></td>
                        <td class="right"><input class="button" type="button" id="add_new_service_fee_1" name="add_new_service_fee_1" value="Add New Fee" /></td>
                    </tr>
                </table>
            </div>
            
            <div id="div_extra_fees">
                <table class="buttons">
                    <tr>
                        <td class="left"><input class="button" type="button" id="delete_extra_fees" name="delete_extra_fees" value="Delete Selected Charges" onClick="delete_charges();" /></td>
                        <td class="right"><input class="button" type="button" id="add_new_extra_fee" name="add_new_extra_fee" value="Add New Charge" /></td>
                    </tr>
                </table>
                <table class="header">
                    <tr>
                        <td class="checkbox"><input type="checkbox" id="delete_all_extra_fees" /></td>
                        <td class="charge_label">Charge</td>
                        <td class="amount_title">Amount</td>
                        <td class="actions">&nbsp;</td>
                    </tr>
                </table>
                <div id="div_extra_fees_list">
                </div>
                <table class="buttons">
                    <tr>
                        <td class="left"><input class="button" type="button" id="delete_extra_fees_1" name="delete_extra_fees_1" value="Delete Selected Charges" onClick="delete_charges();" /></td>
                        <td class="right"><input class="button" type="button" id="add_new_extra_fee_1" name="add_new_extra_fee_1" value="Add New Charge" /></td>
                    </tr>
                </table>
            </div>
            
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_service_fee_form">
            <p class="instructions">Please enter the following details for this service fee:</p>
            <p class="tiny_note">NOTE: Enter 0 to represent &infin; for Salary End.</p>
            <form id="service_fee_form" method="post" onSubmit="return false;">
                <input type="hidden" id="service_fee_id" name="service_fee_id" value="0" />
                <table class="service_fee_form">
                    <tr>
                        <td class="label"><label for="salary_start">Salary Start:</label></td>
                        <td class="field"><input class="field" type="text" id="salary_start" name="salary_start" value="1.00" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="salary_end">Salary End:</label></td>
                        <td class="field"><input class="field" type="text" id="salary_end" name="salary_end" value="0.00" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="Guaranteed Months">Guaranteed Months:</label></td>
                        <td class="field"><input class="field" type="text" id="guarantee_months" name="guarantee_months" value="1" maxlength="2" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="service_fee">Service Fee (%):</label></td>
                        <td class="field"><input class="field" type="text" id="service_fee" name="service_fee" value="" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="discount">Discount (%):</label></td>
                        <td class="field"><input class="field" type="text" id="discount" name="discount" value="" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="reward_percentage">Reward (%):</label></td>
                        <td class="field"><input class="field" type="text" id="reward_percentage" name="reward_percentage" value="25.00" /></td>
                    </tr>
                </table>
                <p class="button"><input type="button" value="Cancel" onClick="close_service_fee_form();" />&nbsp;<input type="button" value="Save" onClick="save_service_fee();" /></p>
            </form>
        </div>
        
        <div id="div_extra_fee_form">
            <p class="instructions">Please enter the following details for this extra charge:</p>
            <form id="service_fee_form" method="post" onSubmit="return false;">
                <input type="hidden" id="extra_fee_id" name="extra_fee_id" value="0" />
                <table class="extra_fee_form">
                    <tr>
                        <td class="label"><label for="charge_label">Charge:</label></td>
                        <td class="field"><input class="field" type="text" id="charge_label" name="charge_label" value="" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="amount">Amount:</label></td>
                        <td class="field"><input class="field" type="text" id="amount" name="amount" value="1.00" /></td>
                    </tr>
                </table>
                <p class="button"><input type="button" value="Cancel" onClick="close_extra_fee_form();" />&nbsp;<input type="button" value="Save" onClick="save_extra_fee();" /></p>
            </form>
        </div>
        <?php
    }
}
?>