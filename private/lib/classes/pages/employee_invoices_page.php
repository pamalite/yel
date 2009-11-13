<?php
require_once dirname(__FILE__). "/../../utilities.php";

class EmployeeInvoicesPage extends Page {
    private $employee = NULL;
    private $clearances = array();
    
    function __construct($_session) {
        $this->employee = new Employee($_session['id'], $_session['sid']);
        $this->clearances = $_session['security_clearances'];
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_invoices_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employee_invoices.css">'. "\n";
    }
    
    public function insert_employee_invoices_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employee_invoices.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employee->id(). '";'. "\n";
        echo 'var user_id = "'. $this->employee->get_user_id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function generate_year_list() {
        $year = date('Y');
        echo '<select class="field_year" id="year" name="year">'. "\n";
        echo '<option value="'. ($year-1). '">'. ($year-1). '</option>'. "\n";
        echo '<option value="'. $year. '" selected>'. $year. '</option>'. "\n";
        echo '</select>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->top_employee($this->employee->get_name(). " - Invoices &amp; Receipts");
        $this->menu_employee($this->clearances, 'invoices');
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        <div id="div_tabs">
            <ul>
                <li id="li_new">Invoices</li>
                <li id="li_paid">Receipts</li>
            </ul>
        </div>
        
        <div id="div_new_invoices">
            <table class="header">
                <tr>
                    <td class="expired">&nbsp;</td>
                    <td class="employer"><span class="sort" id="sort_employer">Employer</span></td>
                    <td class="date"><span class="sort" id="sort_issued_on">Issued On</span></td>
                    <td class="date"><span class="sort" id="sort_payable_by">Payable By</span></td>
                    <td class="type"><span class="sort" id="sort_type">Type</span></td>
                    <td class="invoice"><span class="sort" id="sort_invoice">Invoice</span></td>
                    <td class="amount_title"><span class="sort" id="sort_amount">Amount Payable</span></td>
                    <td class="payment_received">&nbsp;</td>
                    <td class="pdf">&nbsp;</td>
                </tr>
            </table>
            <div id="div_new_invoices_list">
            </div>
        </div>
        
        <div id="div_paid_invoices">
            <table class="header">
                <tr>
                    <td class="employer"><span class="sort" id="sort_paid_employer">Employer</span></td>
                    <td class="date"><span class="sort" id="sort_paid_issued_on">Issued On</span></td>
                    <td class="date"><span class="sort" id="sort_paid_paid_on">Paid On</span></td>
                    <td class="type"><span class="sort" id="sort_paid_type">Type</span></td>
                    <td class="invoice"><span class="sort" id="sort_paid_invoice">Invoice</span></td>
                    <td class="amount_title"><span class="sort" id="sort_paid_amount">Amount Payable</span></td>
                    <td class="pdf">&nbsp;</td>
                </tr>
            </table>
            <div id="div_paid_invoices_list">
            </div>
        </div>
        
        <div id="div_blanket"></div>
        <div id="div_confirm_payment_form">
            <form onSubmit="return false;">
                <input type="hidden" id="invoice_id" name="invoice_id" value="0" />
                <p>
                    Please fill-up the following payment details for invoice <span id="invoice" style="font-weight: bold;"></span>:
                </p>
                <table class="confirm_payment_form">
                    <tr>
                        <td class="label">Employer:</td>
                        <td class="field"><span id="employer"></span></td>
                    </tr>
                    <tr>
                        <td class="label">Amount Payable:</td>
                        <td class="field"><span id="amount_payable"></span></td>
                    </tr>
                    <tr>
                        <td class="label">Paid On:</td>
                        <td class="field"><input type="text" style="width: 25px;" maxlength="2" id="day" name="day" value="dd" />&nbsp;<span id="month_list"></span>&nbsp;<?php $this->generate_year_list(); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Payment Mode:</td>
                        <td class="field">
                            <select class="field" id="payment_mode" name="payment_mode">
                                <option value="CSH">Cash</option>
                                <option value="IBT">Bank Transfer</option>
                                <option value="CHQ">Cheque</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Cheque/Receipt:</td>
                        <td class="field"><input type="text" class="field" id="payment_number" name="payment_number" /></td>
                    </tr>
                </table>
                <p class="button"><input type="button" value="Cancel" onClick="close_confirm_payment_form();" />&nbsp;<input type="button" value="Confirm Payment" onClick="confirm_payment();" /></p>
            </form>
        </div>
        
        <?php
    }
}
?>