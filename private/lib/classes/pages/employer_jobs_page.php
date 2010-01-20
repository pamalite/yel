<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). "/../../../config/postings_rate.inc";

class EmployerJobsPage extends Page {
    private $employer = NULL;
    
    function __construct($_session) {
        $this->employer = new Employer($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employer_jobs_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employer_jobs.css">'. "\n";
    }
    
    public function insert_employer_jobs_scripts() {
        $this->insert_scripts();
        
        //echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/ggEdit.js"></script>'. "\n";
        echo '<script src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/freerte/js/richtext.js" type="text/javascript" language="javascript"></script>';
        echo '<script src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/freerte/js/config.js" type="text/javascript" language="javascript"></script>';
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employer_jobs.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employer->id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function generateCountries($selected = '') {
        $countries = Country::get_all_with_display();
        
        echo '<select class="field" id="country" name="country">'. "\n";
        
        if ($selected == '') {
            echo '<option value="0" selected>Please select a country</option>'. "\n";
            echo '<option value="0" disabled>&nbsp;</option>'. "\n";
        }
        
        foreach ($countries as $country) {
            if ($country['country_code'] != $selected) {
                echo '<option value="'. $country['country_code']. '">'. $country['country']. '</option>'. "\n";
            } else {
                echo '<option value="'. $country['country_code']. '" selected>'. $country['country']. '</option>'. "\n";
            }
        }
        
        echo '</select>'. "\n";
    }
    
    private function generate_all_industry_list() {
        $industries = Industry::get_main();
        
        echo '<select class="field" id="industry" name="industry">'. "\n";
        
        if ($selected == '') {
            echo '<option value="0" selected>Please select an industry</option>'. "\n";
        }
        
        foreach ($industries as $industry) {
            echo '<option class="main_industry" value="'. $industry['id']. '">'. $industry['industry']. '</option>'. "\n";
            
            $sub_industries = Industry::get_sub_industries_of($industry['id']);
            foreach ($sub_industries as $sub_industry) {
                echo '<option value="'. $sub_industry['id']. '">&nbsp;&nbsp;&nbsp;'. $sub_industry['industry']. '</option>'. "\n";
            }
            
        }
        
        echo '</select>'. "\n";
    }
    
    private function generate_currency_list() {
        $currencies = Currency::get_all();
        
        echo '<select class="field" id="currency" name="currency">'. "\n";
        
        if ($selected == '') {
            echo '<option value="0" selected>Please select a currency</option>'. "\n";
        }
        
        foreach ($currencies as $currency) {
            echo '<option value="'. $currency['symbol']. '">'. $currency['currency']. ' ('. $currency['symbol']. ')</option>'. "\n";
        }
        
        echo '</select>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->support();
        $this->top($this->employer->get_name(). "&nbsp;&nbsp;<span style=\"color: #FC8503;\">Job Ads</span>");
        $this->menu('employer', 'jobs');
        
        $currency = Currency::symbol_from_country_code($this->employer->get_country_code());
        
        $query = "SELECT currencies.symbol 
                  FROM currencies 
                  LEFT JOIN branches ON currencies.country_code = branches.country 
                  LEFT JOIN employers ON branches.id = employers.branch 
                  WHERE employers.id = '". $this->employer->id(). "' LIMIT 1";
        $mysqli = Database::connect();
        $result = $mysqli->query($query);
        $payment_currency = 'MYR';
        if (count($result) > 0 && !is_null($result)) {
            $payment_currency = $result[0]['symbol'];
        }
        
        $posting_rates = $GLOBALS['posting_rates'];
        $posting_rate = $posting_rates[$payment_currency];
        if (!array_key_exists($payment_currency, $posting_rates)) {
            $payment_currency = 'MYR';
            $posting_rate = $posting_rates['MYR'];
        }
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <!--div id="div_slots_info">
            <table class="slots_info">
                <tr>
                    <td class="info">
                        <div class="slots_details">
                            Job Postings available: <span id="num_slots" style="font-weight: bold;">0</span>
                            <br/>
                            Expiring on: <span id="slots_expiry" style="font-weight: bold;"></span>
                        </div>
                    </td>
                    <td class="buy">
                        <input type="image" id="buy_postings_button" onClick="show_buy_slots_form();" src="../common/images/button_buy_now_disabled.gif" disabled />
                        <div style="font-size: 7pt; margin-top: -3px; text-align: center;">
                            <a class="no_link" onClick="show_purchase_histories();">View Purchase History</a>
                        </div>
                    </td>
                </tr>
            </table>
        </div-->
        <div id="div_tabs">
            <ul>
                <li id="li_open"><span id="open_back_arrow">&lt;&lt;&nbsp;</span>Currently Open</li>
                <li id="li_closed"><span id="closed_back_arrow">&lt;&lt;&nbsp;</span>Already Closed</li>
            </ul>
        </div>
        <div id="div_open">
            <p class="note">[&bull;] indicates that you have made 'Confirm Employed' submission/s in the Referrals section.</p>
            <table class="buttons">
                <tr>
                    <td class="left"><input class="button" type="button" id="close_jobs" name="close_jobs" value="Close Selected Jobs" /></td>
                    <td class="right"><input class="button" type="button" id="add_new_job" name="add_new_job" value="Create New Job Ad" /></td>
                </tr>
            </table>
            <table class="header">
                <tr>
                    <td class="checkbox"><input type="checkbox" id="close_all" /></td>
                    <!--td class="id">&nbsp;</td-->
                    <td class="industry"><span class="sort" id="sort_industry">Specialization</span></td>
                    <td class="title"><span class="sort" id="sort_title">Title</span></td>
                    <td class="date"><span class="sort" id="sort_created_on">Created On</span></td>
                    <td class="date"><span class="sort" id="sort_expire_on">Expire On</span></td>
                    <td class="new_from">&nbsp;</td>
                </tr>
            </table>
            <div id="div_list">
            </div>
            <table class="buttons">
                <tr>
                    <td class="left"><input class="button" type="button" id="close_jobs_1" name="close_jobs_1" value="Close Selected Jobs" /></td>
                    <td class="right"><input class="button" type="button" id="add_new_job_1" name="add_new_job_1" value="Create New Job Ad" /></td>
                </tr>
            </table>
            <p class="note">[&bull;] indicates that you have made 'Confirm Employed' submission/s in the Referrals section.</p>
        </div>
        
        <div id="div_closed">
            <table class="header">
                <tr>
                    <!--td class="id">&nbsp;</td-->
                    <td class="industry"><span class="sort" id="sort_industry_closed">Specialization</span></td>
                    <td class="title"><span class="sort" id="sort_title_closed">Title</span></td>
                    <td class="date"><span class="sort" id="sort_created_on_closed">Created On</span></td>
                    <td class="date"><span class="sort" id="sort_expire_on_closed">Expire On</span></td>
                    <td class="new_from">&nbsp;</td>
                </tr>
            </table>
            <div id="div_closed_list">
            </div>
        </div>
        
        <div id="div_job_form">
            <div id="div_tabs_1">
                <ul>
                    <li id="li_back">&lt;&lt; Back to Job Ads</li>
                </ul>
            </div>
            <form method="post"onSubmit="return false;">
                <input type="hidden" id="job_id" value="0" />
                <table id="job_form" class="job_form">
                    <tr>
                        <td colspan="2" class="title"><span id="form_title">Add a New Job</span></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="title">Title:</label></td>
                        <td class="field"><input class="field" type="text" id="title" name="title" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="industry">Specialization:</label></td>
                        <td class="field"><?php $this->generate_all_industry_list(); ?></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="country">Country:</label></td>
                        <td class="field"><?php $this->generateCountries(); ?></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="state">State/Province/Area:</label></td>
                        <td class="field"><input type="text" class="field" id="state" name="state" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="salary">Monthly Salary:</label></td>
                        <td class="field">
                            <input type="hidden" id="currency" name="currency" value="<?php echo $currency; ?>" />
                            <?php echo $currency; ?>$ <input class="salary" type="text" id="salary" name="salary" />&nbsp;-&nbsp;<input class="salary" type="text" id="salary_end" name="salary_end" /><br>
                            <input type="checkbox" id="salary_negotiable" name="salary_negotiable" /> <label for="salary_negotiable">Negotiable</label><br/>
                            <p class="small_notes">This account allows you to create job ads with salary in <?php echo $currency; ?> only. If you wish to create job ads with salary in other currencies, please log into the relevant accounts.</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Send Alert:</td>
                        <td class="field">
                            To: <span id="contact">Loading</span><br/>
                            Cc: <input type="text" class="carbon_copy" id="contact_carbon_copy" name="contact_carbon_copy" />
                        </td>
                    </tr>
                    <tr>
                        <td class="label"><label for="description">Description:</label></td>
                        <!--td class="field"><textarea id="description" name="description"></textarea></td-->
                        <!--td class="field">
                            <div id="description" class="description_field">
                            </div><br/><br/>
                        </td-->
                        <td class="field">
                            <div id="description">
                                <script>initRTE('', root + '/common/freerte/examples/example.css');</script>
                            </div><br/><br/>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Accept Resumes of:</td>
                        <td class="field">
                            <select class="field" id="acceptable_resume_type">
                                <option value="A" selected>Any Kind</option>
                                <option value="O">Online Submission Only</option>
                                <option value="F">File Upload Only</option>
                            </select>
                            <p class="small_notes">Only resumes of online submission will go through our matching system. So, to use our matching system, please choose either <b>Any Kind</b> or <b>Online Submission Only</b>.</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="buttons_left"><input class="button" type="button" id="cancel_job" name="cancel_job" value="Cancel" /></td>
                        <td class="buttons_right"><input class="button" type="button" id="save_job" name="save_job" value="Save" />&nbsp;<input class="button" type="button" id="publish_job" name="publish_job" value="Publish" /></td>
                    </tr>    
                </table>
            </form>
        </div>
        
        <div id="div_job_info">
            <table id="job_info" class="job_info">
                <tr>
                    <td colspan="2" class="title"><span id="job.title">Loading</span></td>
                </tr>
                <tr>
                    <td class="label">Specialization:</td>
                    <td class="field"><span id="job.industry">Loading</span></td>
                </tr>
                <tr>
                    <td class="label">Country:</td>
                    <td class="field"><span id="job.country">Loading</span></td>
                </tr>
                <tr>
                    <td class="label">State/Province/Area:</td>
                    <td class="field"><span id="job.state">Loading</span></td>
                </tr>
                <tr>
                    <td class="label">Monthly Salary:</td>
                    <td class="field"><?php echo $currency; ?>$ <span id="job.salary">Loading</span>&nbsp;<span id="job.salary_end">Loading</span>&nbsp;[<span id="job.salary_negotiable">Loading</span>]</td>
                </tr>
                <tr>
                    <td class="label">Send Alert:</td>
                    <td class="field">
                        <table style="width: 100%; border: none; margin:auto;">
                            <tr>
                                <td style="width: 25px;">To:</td>
                                <td><span id="job.contact">Loading</span></td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top;">Cc:</td>
                                <td><span id="job.contact_carbon_copy">Loading</span></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="label">Description:</td>
                    <td class="field"><span id="job.description">Loading</span></td>
                </tr>
                <tr>
                    <td class="label">Created On:</td>
                    <td class="field"><span id="job.created_on">Loading</span></td>
                </tr>
                <tr>
                    <td class="label">Expires On:</td>
                    <td class="field">
                        <span id="job.expire_on">Loading</span>&nbsp;<span id="job.extend"></span><br/><br/>
                        <div id="job_extend_note" style="padding: 3px 3px 3px 3px;">NOTE: By clicking the "Extend" or "Re-open" button, you are automatically in agreement with our Terms and Agreement to be billed this job as a new post.</div><br/>
                    </td>
                </tr>
                <tr>
                    <td class="label">Accept Resumes of:</td>
                    <td class="field"><span id="job.acceptable_resume_type">Loading</span></td>
                </tr>
                <tr>
                    <td id="job_buttons" colspan="2" style="text-align: center;"></td>
                </tr>
            </table>
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_buy_slots_form">
            <form onSubmit="return false;">
                <input type="hidden" id="payment_currency" name="payment_currency" value="<?php echo $payment_currency; ?>" />
                <table class="buy_slots_form">
                    <tr>
                        <td class="label">Price:</td>
                        <td><?php echo $payment_currency; ?>$&nbsp;<span id="price_per_slot"><?php echo $posting_rate; ?></span></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="qty">Number of postings:</label></td>
                        <td><input type="text" class="field" id="qty" name="qty" value="3" onKeyUp="calculate_fee();" />&nbsp;<span style="font-size: 9pt; color: #888888;">discount: <span id="discount">0%</span></span></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;" class="label">Amount:</td>
                        <td style="border-top: 1px solid #666666; border-bottom: 1px double #666666; font-weight: bold;">
                            <?php echo $payment_currency; ?>$&nbsp;<span id="total_amount"><?php echo ($posting_rate * 3) ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Payment Method:</td>
                        <td>
                            <table style="border: none; margin: auto; width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="width: 10px;">
                                        <input type="radio" name="payment_method" id="payment_method_credit_card" value="credit_card" onClick="remove_admin_fee();" <?php echo ($payment_currency != 'MYR') ? 'checked' : 'disabled'; ?> />
                                    </td>
                                    <td>
                                        <label for="payment_method_credit_card">Credit Card/PayPal <span style="font-size: 7pt; color: #666666;">(via PayPal portal)</span></label>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="vertical-align: top;">
                                        <input type="radio" name="payment_method" id="payment_method_cheque" value="cheque" onClick="add_admin_fee();" <?php echo ($payment_currency == 'MYR') ? 'checked' : ''; ?> />
                                    </td>
                                    <td>
                                        <label for="payment_method_cheque">Cheque/Money Order/Bank Transfer <span style="font-size: 7pt; color: #666666;">(+5% admin fee)</span></label>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <p class="button"><input type="button" value="Cancel" onClick="close_buy_slots_form();" />&nbsp;<input type="button" value="Buy Now" onClick="buy_slots();" /></p>
            </form>
        </div>
        
        <div id="div_purchase_histories">
            <div id="purchase_histories">
                <table class="header">
                    <tr>
                        <td class="date">Date of Purchase</td>
                        <td class="number_of_slots_title">Number of Postings</td>
                        <td class="price_per_slot_title">Price (<?php echo $payment_currency; ?>)</td>
                        <td class="amount_title">Amount (<?php echo $payment_currency; ?>)</td>
                    </tr>
                </table>
                <div id="div_purchases_list">
                </div>
            </div>
            <p class="button"><input type="button" value="Close" onClick="close_purchase_histories();" /></p>
        </div>
        
        <div id="div_paypal_progress">
            <div style="text-align: center;">
                Please wait while you are redirected to PayPal's payment portal...<br/><br/>
                <img src="<?php echo $GLOBALS['protocol'] ?>://<?php echo $GLOBALS['root']; ?>/common/images/progress/circle_big.gif" />
            </div>
        </div>
        <?php
    }
}
?>