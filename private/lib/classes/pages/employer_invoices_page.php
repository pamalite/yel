<?php
require_once dirname(__FILE__). "/../../utilities.php";

class EmployerInvoicesPage extends Page {
    private $employer = NULL;
    
    function __construct($_session) {
        $this->employer = new Employer($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employer_invoices_css() {
        $this->insert_css();
        
        echo '<link rel="stylesheet" type="text/css" href="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/css/employer_invoices.css">'. "\n";
    }
    
    public function insert_employer_invoices_scripts() {
        $this->insert_scripts();
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employer_invoices.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employer->id(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    public function show() {
        $this->begin();
        $this->support();
        $this->top($this->employer->get_name(). "&nbsp;&nbsp;<span style=\"color: #FC8503;\">Invoices &amp; Receipts</span>");
        $this->menu('employer', 'invoices');
        
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
                    <td class="date"><span class="sort" id="sort_issued_on">Issued On</span></td>
                    <td class="date"><span class="sort" id="sort_payable_by">Payable By</span></td>
                    <td class="type"><span class="sort" id="sort_type">Type</span></td>
                    <td class="invoice"><span class="sort" id="sort_invoice">Invoice</span></td>
                </tr>
            </table>
            <div id="div_new_invoices_list">
            </div>
        </div>
        
        <div id="div_paid_invoices">
            <table class="header">
                <tr>
                    <td class="date"><span class="sort" id="sort_paid_issued_on">Issued On</span></td>
                    <td class="date"><span class="sort" id="sort_paid_paid_on">Paid On</span></td>
                    <td class="type"><span class="sort" id="sort_paid_type">Type</span></td>
                    <td class="invoice"><span class="sort" id="sort_paid_invoice">Invoice</span></td>
                </tr>
            </table>
            <div id="div_paid_invoices_list">
            </div>
        </div>
        
        <?php
    }
}
?>