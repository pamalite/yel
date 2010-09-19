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
            'having' => "(paid_reward < referrals.total_reward OR paid_reward IS NULL)"
        );
        
        if ($_is_paid) {
            $criteria['columns'] .= ", referral_rewards.gift, DATE_FORMAT(MAX(referral_rewards.paid_on), '%e %b, %Y') AS formatted_paid_on";
            $criteria['having'] = "(paid_reward >= referrals.total_reward OR referral_rewards.gift IS NOT NULL)";
        } else {
            $criteria['match'] .= "AND (referral_rewards.gift IS NULL OR referral_rewards.gift = '')";
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
            $paid_rewards[$i]['gift'] = htmlspecialchars_decode(stripslashes($row['gift']));
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
        
        <div id="new_rewards">
        <?php
        if (is_null($new_rewards) || count($new_rewards) <= 0 || $new_rewards === false) {
        ?>
            <div class="empty_results">No rewards being offered at this moment.</div>
        <?php
        } else {
        ?>
            <div id="div_new_rewards">
            <?php
                $new_rewards_table = new HTMLTable('new_rewards_table', 'new_rewards');

                $new_rewards_table->set(0, 0, "<a class=\"sortable\" onClick=\"sort_by('new_rewards', 'referrals.employed_on');\">Employed On</a>", '', 'header');
                $new_rewards_table->set(0, 1, "<a class=\"sortable\" onClick=\"sort_by('new_rewards', 'jobs.title');\">Job</a>", '', 'header');
                $new_rewards_table->set(0, 2, "<a class=\"sortable\" onClick=\"sort_by('new_rewards', 'members.lastname');\">Referrer</a>", '', 'header');
                $new_rewards_table->set(0, 3, "Receipt", '', 'header');
                $new_rewards_table->set(0, 4, "Reward", '', 'header');
                $new_rewards_table->set(0, 5, 'Actions', '', 'header action');
                
                foreach ($new_rewards as $i=>$new_reward) {
                    $new_rewards_table->set($i+1, 0, $new_reward['formatted_employed_on'], '', 'cell');
                    
                    $job =  htmlspecialchars_decode(stripslashes($new_reward['title'])). '</span>'. "\n";
                    $job .= '<div class="small_contact"><span class="contact_label">Employer:</span> '. $new_reward['employer']. '</div>'. "\n";
                    $new_rewards_table->set($i+1, 1, $job, '', 'cell');
                    
                    $referrer_short_details = '';
                    if (substr($new_reward['member_id'], 0, 5) == 'team.' && 
                        substr($new_reward['member_id'], 7) == '@yellowelevator.com') {
                        $referrer_short_details = 'Yellow Elevator';
                    } else {
                        $referrer_short_details =  htmlspecialchars_decode(stripslashes($new_reward['member'])). "\n";
                        $referrer_short_details .= '<div class="small_contact"><span class="contact_label">Tel.:</span> '. $new_reward['phone_num']. '</div>'. "\n";
                        $referrer_short_details .= '<div class="small_contact"><span class="contact_label">Email: </span><a href="mailto:'. $new_reward['member_id']. '">'. $new_reward['member_id']. '</a></div>'. "\n";
                    }
                    $new_rewards_table->set($i+1, 2, $referrer_short_details, '', 'cell');
                    
                    $new_rewards_table->set($i+1, 3, '<a class="no_link" onClick="show_invoice_page('. $new_reward['invoice']. '">'. $new_reward['padded_invoice']. '</a>&nbsp;<a href="invoice_pdf.php?id='. $new_reward['invoice']. '"><img src="../common/images/icons/pdf.gif" /></a>', '', 'cell');
                    $new_rewards_table->set($i+1, 4, $new_reward['currency']. '$ '. $new_reward['total_reward'], '', 'cell');
                    
                    $actions = '<input type="button" value="Award" onClick="show_award_popup('. $new_reward['referral']. ');" />';
                    $new_rewards_table->set($i+1, 5, $actions, '', 'cell action');
                }

                echo $new_rewards_table->get_html();
            ?>
            </div>
        <?php
        }
        ?>
        </div>
        
        <div id="paid_rewards">
        <?php
        if (is_null($paid_rewards) || count($paid_rewards) <= 0 || $paid_rewards === false) {
        ?>
            <div class="empty_results">No rewards awarded at this moment.</div>
        <?php
        } else {
        ?>
            <div id="div_paid_rewards">
            <?php
                $paid_rewards_table = new HTMLTable('paid_rewards_table', 'paid_rewards');

                $paid_rewards_table->set(0, 0, "<a class=\"sortable\" onClick=\"sort_by('paid_rewards', 'referral_rewards.paid_on');\">Awarded On</a>", '', 'header');
                $paid_rewards_table->set(0, 1, "<a class=\"sortable\" onClick=\"sort_by('paid_rewards', 'jobs.title');\">Job</a>", '', 'header');
                $paid_rewards_table->set(0, 2, "<a class=\"sortable\" onClick=\"sort_by('paid_rewards', 'members.lastname');\">Referrer</a>", '', 'header');
                $paid_rewards_table->set(0, 3, "Receipt", '', 'header');
                $paid_rewards_table->set(0, 4, "Total Reward", '', 'header');
                $paid_rewards_table->set(0, 5, 'Given Reward', '', 'header');
                
                foreach ($paid_rewards as $i=>$paid_reward) {
                    $paid_rewards_table->set($i+1, 0, $paid_reward['formatted_paid_on'], '', 'cell');
                    
                    $job =  htmlspecialchars_decode(stripslashes($paid_reward['title'])). '</span>'. "\n";
                    $job .= '<div class="small_contact"><span class="contact_label">Employer:</span> '. $paid_reward['employer']. '</div>'. "\n";
                    $paid_rewards_table->set($i+1, 1, $job, '', 'cell');
                    
                    $referrer_short_details = '';
                    if (substr($paid_reward['member_id'], 0, 5) == 'team.' && 
                        substr($paid_reward['member_id'], 7) == '@yellowelevator.com') {
                        $referrer_short_details = 'Yellow Elevator';
                    } else {
                        $referrer_short_details =  htmlspecialchars_decode(stripslashes($paid_reward['member'])). "\n";
                        $referrer_short_details .= '<div class="small_contact"><span class="contact_label">Tel.:</span> '. $paid_reward['phone_num']. '</div>'. "\n";
                        $referrer_short_details .= '<div class="small_contact"><span class="contact_label">Email: </span><a href="mailto:'. $paid_reward['member_id']. '">'. $paid_reward['member_id']. '</a></div>'. "\n";
                    }
                    $paid_rewards_table->set($i+1, 2, $referrer_short_details, '', 'cell');
                    
                    $paid_rewards_table->set($i+1, 3, '<a class="no_link" onClick="show_invoice_page('. $paid_reward['invoice']. '">'. $paid_reward['padded_invoice']. '</a>&nbsp;<a href="invoice_pdf.php?id='. $paid_reward['invoice']. '"><img src="../common/images/icons/pdf.gif" /></a>', '', 'cell');
                    $paid_rewards_table->set($i+1, 4, $paid_reward['currency']. '$ '. $paid_reward['total_reward'], '', 'cell');
                    
                    $rewarded = $paid_reward['currency']. '$ '. $paid_reward['paid_reward'];
                    if ($paid_reward['paid_reward'] <= 0 && !is_null($paid_reward['gift'])) {
                        $rewarded = $paid_reward['gift'];
                    }
                    $paid_rewards_table->set($i+1, 5, $rewarded, '', 'cell');
                }

                echo $paid_rewards_table->get_html();
            ?>
            </div>
        <?php
        }
        ?>
        </div>
        
        <!-- popup windows goes here -->
        <div id="award_window" class="popup_window">
            <div class="popup_window_title">Award</div>
            <form onSubmit="return false;">
                <input type="hidden" id="referral_id" value="" />
                <div class="award_form">
                    <table class="award_form">
                        <tr>
                            <td class="label">Referrer:</td>
                            <td class="field"><span id="lbl_referrer"></span></td>
                        </tr>
                        <tr>
                            <td class="label">Total Reward:</td>
                            <td class="field">
                                <span id="lbl_reward"></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Award as:</td>
                            <td class="field">
                                <div class="award_field">
                                    <input type="radio" name="award_as" id="award_as_money" checked />Monetary Incentive<br/>
                                    Payment Mode:
                                    <select id="payment_mode" name="payment_mode">
                                        <option value="IBT" selected>Bank Transfer</option>
                                        <option value="CSH">Cash</option>
                                        <option value="CHQ">Cheque</option>
                                        <option value="CDB">Bank on-behalf</option>
                                    </select><br/>
                                    Bank Account:
                                    <span id="banks_list"></span><br/>
                                    Receipt #:
                                    <input type="text" id="receipt" value="" />
                                </div>
                                <br/>
                                <div class="award_field">
                                    <input type="radio" name="award_as" id="award_as_gift" />Gift: 
                                    <input type="text" id="gift" value="" />
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Cancel" onClick="close_award_popup(false);" />
                <input type="button" value="Confirm" onClick="close_award_popup(true);" />
            </div>
        </div>
        
        <?php
    }
}
?>