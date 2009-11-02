<?php
require_once dirname(__FILE__). "/../../utilities.php";

class PrsReferralsPage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_prs_referrals_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/prs_referrals.css">'. "\n";
    }
    
    public function insert_prs_referrals_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/prs_referrals.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo '</script>'. "\n";
    }
        
    public function show() {
        $this->begin();
        $this->top_prs($this->employee->get_name(). " - Referrals");
        $this->menu_prs($this->clearances, 'referrals');
        
        $yyyy = date('Y');
        $mm = date('m');
        $dd = date('d');
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div id="div_tabs">
            <ul>
                <li id="li_buffer">Buffered</li>
                <li id="li_in_process">In Process</li>
                <li id="li_employed">Employed</li>
                <li id="li_rejected">Rejected</li>
            </ul>
        </div>
        
        <div id="div_buffers">
            <table class="header">
                <tr>
                    <td class="date"><span class="sort" id="sort_referred_on">Referred On</span></td>
                    <td class="employer"><span class="sort" id="sort_employer">Employer</span></td>
                    <td class="title"><span class="sort" id="sort_title">Job</span></td>
                    <td class="title"><span class="sort" id="sort_industry">Industry</span></td>
                    <td class="title"><span class="sort" id="sort_candidate">Candidate</span></td>
                </tr>
            </table>
            <div id="div_buffer_list">
            </div>
        </div>
        
        <div id="div_in_process">
            <table class="header">
                <tr>
                    <td class="date"><span class="sort" id="sort_in_process_referred_on">Referred On</span></td>
                    <td class="employer"><span class="sort" id="sort_in_process_employer">Employer</span></td>
                    <td class="title"><span class="sort" id="sort_in_process_title">Job</span></td>
                    <td class="title"><span class="sort" id="sort_in_process_industry">Industry</span></td>
                    <td class="title"><span class="sort" id="sort_in_process_candidate">Candidate</span></td>
                    <td class="date"><span class="sort" id="sort_in_process_employer_view_resume_on">Employer Viewed On</span></td>
                </tr>
            </table>
            <div id="div_in_process_list">
            </div>
        </div>
        
        <div id="div_employeds">
            <table class="header">
                <tr>
                    <td class="date"><span class="sort" id="sort_employed_employed_on">Employed On</span></td>
                    <td class="employer"><span class="sort" id="sort_employed_employer">Employer</span></td>
                    <td class="title"><span class="sort" id="sort_employed_title">Job</span></td>
                    <td class="title"><span class="sort" id="sort_employed_candidate">Candidate</span></td>
                    <td class="title"><span class="sort" id="sort_employed_recommender">Recommender</span></td>
                    <td class="title"><span class="sort" id="sort_employed_invoice">Invoice/Receipt</span></td>
                </tr>
            </table>
            <div id="div_employed_list">
            </div>
        </div>
        
        <div id="div_rejecteds">
            <table class="header">
                <tr>
                    <td class="date"><span class="sort" id="sort_rejected_referred_on">Referred On</span></td>
                    <td class="employer"><span class="sort" id="sort_rejected_employer">Employer</span></td>
                    <td class="title"><span class="sort" id="sort_rejected_title">Job</span></td>
                    <td class="title"><span class="sort" id="sort_rejected_industry">Industry</span></td>
                    <td class="title"><span class="sort" id="sort_rejected_candidate">Candidate</span></td>
                </tr>
            </table>
            <div id="div_rejected_list">
            </div>
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_testimony">
            <div id="testimony" class="testimony"></div>
            <div class="buttons">
                <input type="button" onClick="close_testimony();" value="Close" />
            </div>
        </div>
        
        <div id="div_token_form">
            <p class="label">
                Please enter the token and date presented to <span id="recommender_name" style="font-weight: bold;"></span>
            </p>
            <form onSubmit="retun false;">
                <input type="hidden" id="recommender" name="recommender" value="" />
                <input type="hidden" id="referral" name="referral" value="" />
                <table class="token_form">
                    <tr>
                        <td class="label"><label for="token">Token:</label></td>
                        <td class="field"><input class="field" type="text" id="token" name="token" value="" /></td>
                    </tr>
                    <tr>
                        <td class="label"><label for="presented_on_day">Presented On:</label></td>
                        <td class="field">
                            <input class="date" type="text" id="presented_on_day" name="presented_on_day" value="<?php echo $dd; ?>" maxlength="2" />
                            &nbsp;
                            <select id="presented_on_month" name="presented_on_month">
                                <option value="01" <?php echo ($mm == '01') ? 'selected' : ''; ?>>January</option>
                                <option value="02" <?php echo ($mm == '02') ? 'selected' : ''; ?>>February</option>
                                <option value="03" <?php echo ($mm == '03') ? 'selected' : ''; ?>>March</option>
                                <option value="04" <?php echo ($mm == '04') ? 'selected' : ''; ?>>April</option>
                                <option value="05" <?php echo ($mm == '05') ? 'selected' : ''; ?>>May</option>
                                <option value="06" <?php echo ($mm == '06') ? 'selected' : ''; ?>>June</option>
                                <option value="07" <?php echo ($mm == '07') ? 'selected' : ''; ?>>July</option>
                                <option value="08" <?php echo ($mm == '08') ? 'selected' : ''; ?>>August</option>
                                <option value="09" <?php echo ($mm == '09') ? 'selected' : ''; ?>>September</option>
                                <option value="10" <?php echo ($mm == '10') ? 'selected' : ''; ?>>October</option>
                                <option value="11" <?php echo ($mm == '11') ? 'selected' : ''; ?>>November</option>
                                <option value="12" <?php echo ($mm == '12') ? 'selected' : ''; ?>>December</option>
                            </select>
                            &nbsp;
                            <select id="presented_on_year" name="presented_on_year">
                                <option value="<?php echo ($yyyy-1); ?>"><?php echo ($yyyy-1); ?></option>
                                <option value="<?php echo $yyyy; ?>" selected><?php echo $yyyy; ?></option>
                            </select>
                            
                        </td>
                    </tr>
                </table>
                <p class="button"><input type="button" value="Cancel" onClick="close_token_form();" />&nbsp;<input type="button" value="Submit" onClick="present_token();" /></p>
            </form>
        </div>
        
        <?php
    }
}
?>