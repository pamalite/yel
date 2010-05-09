<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). "/../../../config/subscriptions_rate.inc";
require_once dirname(__FILE__). "/../htmltable.php";

class EmployeeEmployerPage extends Page {
    private $employee = NULL;
    private $employer = NULL;
    private $is_new = false;
    private $current_page = 'profile';
    
    function __construct($_session, $_employer_id = '') {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->employer = new Employer($_employer_id);
    }
    
    public function new_employer($_is_new) {
        $this->is_new = $_is_new;
    }
    
    public function set_page($_page) {
        $this->current_page = $_page;
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_employer_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employee_employer.css">'. "\n";
    }
    
    public function insert_employee_employer_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/flextable.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employee_employer.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->getId(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->getUserId(). '";'. "\n";
        echo 'var current_page = "'. $this->current_page. '";'. "\n";
        
        if ($this->is_new) {
            echo 'var employer_id = "0";'. "\n";
            
            if (!is_null($this->employer)) {
                echo 'var from_employer = "'. $this->employer->getId(). '"'. "\n";
            } else {
                echo 'var from_employer = ""'. "\n";
            }
        } else {
            echo 'var employer_id = "'. $this->employer->getId(). '";'. "\n";
        }
        
        echo '</script>'. "\n";
    }
    
    private function generate_countries($_selected, $_name = 'country') {
        $criteria = array(
            'columns' => "country_code, country", 
            'order' => "country"
        );
        $countries = Country::find($criteria);
        
        echo '<select class="field" id="'. $_name. '" name="'. $_name. '">'. "\n";
        
        foreach ($countries as $country) {
            if ($country['country_code'] != $_selected) {
                echo '<option value="'. $country['country_code']. '">'. $country['country']. '</option>'. "\n";
            } else {
                echo '<option value="'. $country['country_code']. '" selected>'. $country['country']. '</option>'. "\n";
            }
        }
        
        echo '</select>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top('Employer');
        $this->menu_employee('employers');
        
        $subscriptions_rates = $GLOBALS['subscriptions_rates'];
        $branch = $this->employee->getBranch();
        $available_subscriptions = $subscriptions_rates[Currency::getSymbolFromCountryCode($branch[0]['country_code'])];
        
        $raw_data = array();
        $profile = array();
        $fees = array();
        if (!$this->is_new) {
            // get profile
            $raw_data = $this->employer->get();
            $profile = $raw_data[0];
            
            // get fees
            $raw_data = $this->employer->getFees();
            $fees = $raw_data;
        } else {
            $profile = array(
                'license_num' => '',
                'working_months' => '12',
                'payment_terms_days' => '30',
                'email_addr' => '',
                'contact_person' => '',
                'name' => '',
                'website_url' => '',
                'phone_num' => '',
                'address' => '',
                'state' => '',
                'zip' => '',
                'country' => $branch[0]['country_code']
            );
        }
        
        ?>
        <!-- submenu -->
        <div class="menu">
            <?php $style = 'background-color: #CCCCCC;'; ?>
            <ul class="menu">
                
                <li id="item_profile" style="<?php echo ($this->current_page == 'profile') ? $style : ''; ?>"><a class="menu" onClick="show_profile();">Profile</a></li>
                <li id="item_fees" style="<?php echo ($this->current_page == 'fees') ? $style : ''; ?>"><a class="menu" onClick="show_fees();">Fees</a></li>
                <li id="item_subscriptions" style="<?php echo ($this->current_page == 'subscriptions') ? $style : ''; ?>"><a class="menu" onClick="show_subscriptions();">Subscriptions</a></li>
                <li id="item_jobs" style="<?php echo  ($this->current_page == 'jobs') ? $style : ''; ?>"><a class="menu" onClick="show_jobs();">Jobs</a></li>
            </ul>
        </div>
        <!-- end submenu -->
        
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="employer_profile">
            <form id="profile" method="post" onSubmit="return false;">
                <table class="profile_form">
                    <tr>
                        <td class="buttons_bar" colspan="2"><input type="button" onClick="save_profile();" value="Save &amp; Update Profile" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="business_license">Company/Business Registration No.:</label></td>
                        <td class="field"><input class="field" type="text" id="business_license" name="business_license" value="<?php echo $profile['license_num'] ?>" /></td>
                    </tr>
                    <tr>
                        <td class="title" colspan="2">Sign In Details</td>
                    </tr>
                    <tr>
                        <td class="label">User ID:</td>
                        <td class="field">
                            <?php
                            if ($this->is_new) {
                            ?>
                            <input class="field" type="text" id="user_id" value=""  maxlength="10" />
                            <?php
                            } else {
                                echo $profile['id'];
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="password">Password:</label></td>
                        <td class="field">
                            <?php
                            if ($this->is_new) {
                            ?>
                            <input type="button" value="Reset Password" onClick="reset_password();" disabled />
                            <?php
                            } else {
                            ?>
                            <input type="button" value="Reset Password" onClick="reset_password();" />
                            <?php
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="title" colspan="2">Contact Details</td>
                    </tr>
                    <tr>
                        <td class="label"><label for="email">E-mail Address:</label></td>
                        <td class="field"><input class="field" type="text" id="email" name="email" value="<?php echo $profile['email_addr'] ?>"   /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="name">Business Name:</label></td>
                        <td class="field"><input class="field" type="text" id="name" name="name" value="<?php echo htmlspecialchars_decode(stripslashes($profile['name'])) ?>"  /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="contact_person">Contact Person:</label></td>
                        <td class="field"><input class="field" type="text" id="contact_person" name="contact_person" value="<?php echo htmlspecialchars_decode(stripslashes($profile['contact_person'])) ?>"  /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="phone_num">Telephone Number:</label></td>
                        <td class="field"><input class="field" type="text" id="phone_num" name="phone_num" value="<?php echo $profile['phone_num'] ?>"  /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="address">Mailing Address:</label></td>
                        <td class="field"><textarea id="address" name="address" ><?php echo htmlspecialchars_decode(stripslashes($profile['address'])) ?></textarea></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="state">State/Province/Area:</label></td>
                        <td class="field"><input class="field" type="text" id="state" name="state" value="<?php echo $profile['state'] ?>"   /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="zip">Zip/Postal Code:</label></td>
                        <td class="field"><input class="field" type="text" id="zip" name="zip" value="<?php echo $profile['zip'] ?>"  /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="country">Country:</label></td>
                        <td class="field">
                            <?php echo $this->generate_countries($profile['country']); ?>
                        </</td>
                    </tr>
                    <tr>
                        <td class="label"><label for="website_url">Web-site URL:</label></td>
                        <td class="field"><input class="field" type="text" id="website_url" name="website_url" value="<?php echo htmlspecialchars_decode(stripslashes($profile['website_url'])) ?>"  /></td>
                    </tr>
                    <tr>
                        <td class="buttons_bar" colspan="2"><input type="button" onClick="save_profile();" value="Save &amp; Update Profile" /></td>
                    </tr>
                </table>
            </form>
        </div>
        
        <div id="employer_fees">
            <div class="payment_terms">
                <table class="payment_terms_table">
                    <tr>
                        <td class="label"><label for="working_months">Working Months:</label></td>
                        <td class="field"><input class="field_number" type="text" id="working_months" name="working_months" value="<?php echo $profile['working_months']; ?>" maxlength="2" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="payment_terms_days">Payment Terms:</label></td>
                        <td class="field">
                            <?php
                            $is_selected = array(
                                '30' => '',
                                '60' => '',
                                '90' => ''
                            );
                            $is_selected[$profile['payment_terms_days']] = 'selected';
                            ?>
                            <select id="payment_terms_days" name="payment_terms_days"  >
                                <option value="30" <?php echo $is_selected['30']; ?>>30 days</option>
                                <option value="60" <?php echo $is_selected['60']; ?>>60 days</option>
                                <option value="90" <?php echo $is_selected['90']; ?>>90 days</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="buttons_bar">
                <input class="button" type="button" value="Add" onClick="add_new_fee();" />
            </div>
            <div id="fees" class="fees">
            <?php
                if (is_null($fees) || empty($fees) || $fees === false) {
            ?>
                <div class="empty_results">There is no fee structure set for this employer yet.</div>
            <?php
                } else {
                    $fees_table = new HTMLTable('fees_table', 'fees_table');

                    $fees_table->set(0, 0, "Annual Salary From", '', 'header');
                    $fees_table->set(0, 1, "Annual Salary Until", '', 'header');
                    $fees_table->set(0, 2, "Guaranteed Period (in months)", '', 'header');
                    $fees_table->set(0, 3, "Service Fee (%)", '', 'header');
                    $fees_table->set(0, 4, "Reward (%)", '', 'header');
                    $fees_table->set(0, 5, "&nbsp;", '', 'header action');

                    foreach ($fees as $i=>$fee) {
                        $fees_table->set($i+1, 0, number_format($fee['salary_start'], 2, '.', ','), '', 'cell');
                        $fees_table->set($i+1, 1, number_format($fee['salary_end'], 2, '.', ','), '', 'cell');
                        $fees_table->set($i+1, 2, $fee['guarantee_months'], '', 'cell center');
                        $fees_table->set($i+1, 3, $fee['service_fee'], '', 'cell center');
                        $fees_table->set($i+1, 4, $fee['reward_percentage'], '', 'cell center');

                        $actions = '<input type="button" value="Delete" onClick="delete_fee('. $fee['id']. ');" />';
                        $actions .= '<input type="button" value="Update" onClick="show_fee_window('. $fee['id']. ', \''. number_format($fee['salary_start'], 2, '.', ','). '\', \''. number_format($fee['salary_end'], 2, '.', ','). '\', \''. $fee['guarantee_months']. '\', \''. $fee['service_fee']. '\', \''. $fee['reward_percentage']. '\');" />';
                        $fees_table->set($i+1, 5, $actions, '', 'cell action');
                    }

                    echo $fees_table->get_html();
                }
            ?>
            </div>
            <div class="buttons_bar">
                <input class="button" type="button" value="Add" onClick="add_new_fee();" />
            </div>
        </div>
        
        <div id="employer_subscriptions">
            <table class="subscription_form">
                <tr>
                    <td class="label"><label for="subscription_period">Subscription:</label></td>
                    <td class="field">
                        <?php
                            $expiry = new DateTime($profile['subscription_expire_on']);
                            $today = new DateTime(date('Y-m-d'));
                            $expiry_date = $expiry->format('j M, Y');
                            
                            $expired = '';
                            if ($today->diff($expiry)->format('%d') > 0) {
                                $expired = 'color: #ff0000;';
                            }
                        ?>
                        <div>Expires On: <span id="expiry" style="<?php echo $expired ?>"><?php echo $expiry_date; ?></span></div>
                        <div>Purchase: 
                            <select id="subscription_period" name="subscription_period"  >
                                <option value="0" selected>None</option>
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
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="label"><label for="free_postings">Free Job Postings:</label></td>
                    <td class="field">
                        <span id="free_postings_label"><?php echo $profile['free_postings_left'] ?></span>
                        &nbsp;
                        Add: 
                        <input class="field_number" type="text" id="free_postings" name="free_postings" value="0" maxlength="2" /> 
                    </td>
                </tr>
                <tr>
                    <td class="label"><label for="paid_postings">Paid Job Postings:</label></td>
                    <td class="field">
                        <span id="paid_postings_label"><?php echo $profile['paid_postings_left'] ?></span>
                        &nbsp;
                        Add: 
                        <input class="field_number" type="text" id="paid_postings" name="paid_postings" value="0" maxlength="2"  />
                    </td>
                </tr>
            </table>
            <div class="buttons_bar">
                <input class="button" type="button" value="Save" onClick="save_subscriptions();" />
            </div>
        </div>
        
        <div id="employer_jobs">
        </div>
        
        <!-- popup windows goes here -->
        <div id="fee_window" class="popup_window">
            <div class="popup_window_title">Service Fee</div>
            <div class="popup_fee">
                <div class="note">NOTE: Enter 0 to represent &infin; for Annual Salary Until.</div>
                <form id="service_fee_form" method="post" onSubmit="return false;">
                    <input type="hidden" id="id" name="id" value="0" />
                    <table class="service_fee_form">
                        <tr>
                            <td class="label"><label for="salary_start">Annual Salary Start:</label></td>
                            <td class="field"><input class="field" type="text" id="salary_start" name="salary_start" value="1.00" /></td>
                        </tr>
                        <tr>
                            <td class="label"><label for="salary_end">Annual Salary Until:</label></td>
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
                            <td class="label"><label for="reward_percentage">Reward (%):</label></td>
                            <td class="field"><input class="field" type="text" id="reward_percentage" name="reward_percentage" value="25.00" /></td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Save &amp; Close" onClick="close_fee_window(true);" />
                <input type="button" value="Close" onClick="close_fee_window(false);" />
            </div>
        </div>
        
        <?php
    }
}
?>