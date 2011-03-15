<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). "/../htmltable.php";

class EmployeePaymentsPage extends Page {
    private $employee = NULL;
    
    function __construct($_session) {
        parent::__construct();
        
        $this->employee = new Employee($_session['id'], $_session['sid']);
    }
    
    public function insert_inline_css() {
        // TODO: Any inline CSS for home page goes here.
    }
    
    public function insert_employee_payments_css() {
        $this->insert_css('employee_payments.css');
    }
    
    public function insert_employee_payments_scripts() {
        $this->insert_scripts(array('flextable.js', 'employee_payments.js'));
    }
    
    public function insert_inline_scripts() {
        $script = 'var id = "'. $this->employee->getId(). '";'. "\n";
        $script .= 'var user_id = "'. $this->employee->getUserId(). '";'. "\n";
        
        $this->header = str_replace('<!-- %inline_javascript% -->', $script, $this->header);
    }
    
    private function get_payments($_is_invoice = true) {
        $criteria = array(
            "columns" => "invoices.id, invoices.type, invoices.payable_by, 
                          employers.name AS employer, employers.contact_person, employers.email_addr, 
                          employers.fax_num, employers.phone_num, 'N/A' AS placement, 
                          SUM(invoice_items.amount) AS amount_payable, currencies.symbol AS currency, 
                          DATE_FORMAT(invoices.issued_on, '%e %b, %Y') AS formatted_issued_on, 
                          DATE_FORMAT(invoices.payable_by, '%e %b, %Y') AS formatted_payable_by,
                          DATE_FORMAT(invoices.paid_on, '%e %b, %Y') AS formatted_paid_on", 
            "joins" => "employers ON employers.id = invoices.employer, 
                        branches ON branches.id = employers.branch, 
                        invoice_items ON invoice_items.invoice = invoices.id, 
                        currencies ON currencies.country_code = branches.country", 
            "group" => "invoices.id"
        );
        
        if ($_is_invoice) {
            $criteria['match'] = "invoices.paid_on IS NULL AND invoices.is_copy = FALSE";
            $criteria['order'] = "invoices.issued_on";
        } else {
            $criteria['columns'] .= ", invoices.paid_through, invoices.paid_id";
            $criteria['match'] = "invoices.paid_on IS NOT NULL AND invoices.is_copy = FALSE";
            $criteria['order'] = "invoices.paid_on DESC";
        }
        
        $invoices = Invoice::find($criteria);
        
        if (empty($invoices) || is_null($invoices) || $invoices === false) {
            return array();
        }
        
        $invoice_ids = array();
        foreach ($invoices as $row) {
            $invoice_ids[] = $row['id'];
        }
        
        $criteria = array(
            'columns' => "DISTINCT invoices.id, invoice_items.item, referrals.job, jobs.title, 
                          CONCAT(members.lastname, ', ', members.firstname) AS candidate", 
            'joins' => "invoice_items ON invoice_items.invoice = invoices.id, 
                        referrals ON referrals.id = invoice_items.item,
                        jobs ON jobs.id = referrals.job, 
                        members ON members.email_addr = referrals.referee",
            'match' => "invoices.is_copy = FALSE AND invoices.type = 'R' AND 
                        invoices.id IN (". implode(',', $invoice_ids). ")"
        );
        
        $result = Invoice::find($criteria);
        
        if (empty($result) || is_null($result) || $iresult === false) {
            return $invoices;
        }
        
        foreach ($invoices as $i => $invoice) {
            foreach ($result as $row) {
                if ($row['id'] == $invoice['id']) {
                    if ((is_null($row['job']) || empty($row['job'])) || 
                        (is_null($row['candidate']) || empty($row['candidate'])) || 
                        (is_null($row['title']) || empty($row['title']))) {
                        $invoices[$i]['placement'] = '(Data is missing)';
                        break;
                    } 
                    
                    $invoices[$i]['placement'] = htmlspecialchars_decode(stripslashes($row['title'])). ' - ';
                    $invoices[$i]['placement'] .= htmlspecialchars_decode(stripslashes($row['candidate']));
                    break;
                }
            }
        }
        
        return $invoices;
    }
    
    private function get_employers($_for_invoice = true) {
        $criteria = array(
            'columns' => "DISTINCT employers.id, employers.name AS employer", 
            'joins' => "employers ON employers.id = invoices.employer"
        );
        
        if ($_for_invoice) {
            $criteria['match'] = "invoices.paid_on IS NULL";
        } else {
            $criteria['match'] = "invoices.paid_on IS NOT NULL";
        }
        
        return Invoice::find($criteria);
    }
    
    public function show() {
        $this->begin();
        $branch = $this->employee->getBranch();
        $this->top('Payments - '. $branch[0]['country']);
        $this->menu_employee('payments');
        
        $employers = $this->get_employers();
        $receipt_employers = $this->get_employers(false);
        
        $today = now();
        $invoices = $this->get_payments();
        foreach($invoices as $i=>$row) {
            $invoices[$i]['padded_id'] = pad($row['id'], 11, '0');
            $invoices[$i]['amount_payable'] = number_format($row['amount_payable'], 2, '.', ', ');
            $delta = sql_date_diff($today, $row['payable_by']);
            if ($delta > 0) {
                $invoices[$i]['expired'] = 'expired';
            } else if ($delta == 0) {
                $invoices[$i]['expired'] = 'nearly';
            } else {
                $invoices[$i]['expired'] = 'no';
            }
        }
        
        $receipts = $this->get_payments(false);
        foreach($receipts as $i=>$row) {
            $receipts[$i]['padded_id'] = pad($row['id'], 11, '0');
            $receipts[$i]['amount_payable'] = number_format($row['amount_payable'], 2, '.', ', ');
        }
        
        ?>
        <!-- submenu -->
        <div class="menu">
            <ul class="menu">
                <li id="item_invoices" style="background-color: #CCCCCC;"><a class="menu" onClick="show_invoices();">Invoices</a></li>
                <li id="item_receipts"><a class="menu" onClick="show_receipts();">Receipts</a></li>
            </ul>
        </div>
        <!-- end submenu -->
        
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="invoices">
        <?php
        if (is_null($invoices) || count($invoices) <= 0 || $invoices === false) {
        ?>
            <div class="empty_results">No invoices issued at this moment.</div>
        <?php
        } else {
        ?>
            <div class="buttons_bar">
                Employer Filter: 
                <select id="invoices_filter" onChange="filter_invoices();">
                    <option value="" selected>All</option>
                    <option value="" disabled>&nbsp;</option>
        <?php
            foreach ($employers as $employer) {
                ?>
                    <option value="<?php echo $employer['id']; ?>"><?php echo htmlspecialchars_decode(stripslashes($employer['employer'])); ?></option>
                <?php
            }
        ?>
                </select>
            </div>
            <div id="div_invoices">
            <?php
                $invoices_table = new HTMLTable('invoices_table', 'invoices');

                $invoices_table->set(0, 0, '&nbsp;', '', 'header expiry_status');
                $invoices_table->set(0, 1, "<a class=\"sortable\" onClick=\"sort_by('invoices', 'invoices.issued_on');\">Issued On</a>", '', 'header');
                $invoices_table->set(0, 2, "<a class=\"sortable\" onClick=\"sort_by('invoices', 'employers.name');\">Employer</a>", '', 'header');
                $invoices_table->set(0, 3, "Placement", '', 'header');
                $invoices_table->set(0, 4, "<a class=\"sortable\" onClick=\"sort_by('invoices', 'invoices.id');\">Invoice</a>", '', 'header');
                $invoices_table->set(0, 5, 'Payable By', '', 'header');
                $invoices_table->set(0, 6, 'Amount Payable', '', 'header');
                $invoices_table->set(0, 7, 'Actions', '', 'header action');

                foreach ($invoices as $i=>$invoice) {
                    $status = '';
                    if ($invoice['expired'] == 'expired') {
                        $status = '<img class="warning" src="../common/images/icons/expired.png" />';
                    } elseif ($invoice['expired'] == 'nearly') {
                        $status = '<img class="warning" src="../common/images/icons/just_expired.png" />';
                    }
                    $invoices_table->set($i+1, 0, $status, '', 'cell expiry_status');
                    
                    $invoices_table->set($i+1, 1, $invoice['formatted_issued_on'], '', 'cell');
                    
                    $employer_contacts = htmlspecialchars_decode(stripslashes($invoice['employer']));
                    $employer_contacts .= '<div class="contacts">';
                    $employer_contacts .= '<span class="contact_label">Tel.:</span> '. $invoice['phone_num']. '<br/>';
                    $employer_contacts .= '<span class="contact_label">Fax.:</span> '. $invoice['fax_num']. '<br/>';
                    $employer_contacts .= '<span class="contact_label">E-mail:</span> <a href="mailto:'. $invoice['email_addr']. '">'. $invoice['email_addr']. '</a><br/>';
                    $employer_contacts .= '<span class="contact_label">Contact:</span> '. $invoice['contact_person']. '<br/></div>';
                    $invoices_table->set($i+1, 2, $employer_contacts, '', 'cell');
                    
                    $invoices_table->set($i+1, 3, $invoice['placement'], '', 'cell');
                    
                    $invoices_table->set($i+1, 4, '<a class="no_link" onClick="show_invoice_page('. $invoice['id']. ');">'. $invoice['padded_id']. '</a>&nbsp;<a href="invoice_pdf.php?id='. $invoice['id']. '"><img src="../common/images/icons/pdf.gif" /></a>', '', 'cell');                    
                    $invoices_table->set($i+1, 5, $invoice['formatted_payable_by'], '', 'cell');
                    
                    $amount = $invoice['currency']. '$&nbsp;'. $invoice['amount_payable'];
                    $invoices_table->set($i+1, 6, $amount, '', 'cell');
                    
                    $actions = '<input type="button" value="Paid" onClick="show_payment_popup('. $invoice['id']. ', \''. $invoice['padded_id']. '\');" /><input type="button" value="Resend" onClick="show_resend_popup('. $invoice['id']. ', \''. $invoice['padded_id']. '\');" />';
                    $invoices_table->set($i+1, 7, $actions, '', 'cell action');
                }

                echo $invoices_table->get_html();
            ?>
            </div>
        <?php
        }
        ?>
        </div>
                
        <div id="receipts">
        <?php
        if (is_null($receipts) || count($receipts) <= 0 || $receipts === false) {
        ?>
            <div class="empty_results">No receipts issued at this moment.</div>
        <?php
        } else {
        ?>
            <div class="buttons_bar">
                Employer Filter: 
                <select id="receipts_filter" onChange="filter_receipts();">
                    <option value="" selected>All</option>
                    <option value="" disabled>&nbsp;</option>
        <?php
            foreach ($receipt_employers as $employer) {
                ?>
                    <option value="<?php echo $employer['id']; ?>"><?php echo htmlspecialchars_decode(stripslashes($employer['employer'])); ?></option>
                <?php
            }
        ?>
                </select>
            </div>
            <div id="div_receipts">
            <?php
                $receipts_table = new HTMLTable('receipts_table', 'receipts');

                $receipts_table->set(0, 0, "<a class=\"sortable\" onClick=\"sort_by('receipts', 'receipts.issued_on');\">Issued On</a>", '', 'header');
                $receipts_table->set(0, 1, "<a class=\"sortable\" onClick=\"sort_by('receipts', 'employers.name');\">Employer</a>", '', 'header');
                $receipts_table->set(0, 2, 'Placement', '', 'header');
                $receipts_table->set(0, 3, "<a class=\"sortable\" onClick=\"sort_by('receipts', 'invoices.id');\">Receipt</a>", '', 'header');
                $receipts_table->set(0, 4, 'Paid On', '', 'header');
                $receipts_table->set(0, 5, 'Amount Paid', '', 'header');
                $receipts_table->set(0, 6, 'Payment', '', 'header payment');

                foreach ($receipts as $i=>$receipt) {
                    $receipts_table->set($i+1, 0, $receipt['formatted_issued_on'], '', 'cell');
                    $receipts_table->set($i+1, 1, htmlspecialchars_decode(stripslashes($receipt['employer'])), '', 'cell');
                    $receipts_table->set($i+1, 2, $receipt['placement'], '', 'cell');
                    
                    $receipts_table->set($i+1, 3, '<a class="no_link" onClick="show_receipt_page('. $receipt['id']. ');">'. $receipt['padded_id']. '</a>&nbsp;<a href="invoice_pdf.php?id='. $receipt['id']. '"><img src="../common/images/icons/pdf.gif" /></a>', '', 'cell');
                    
                    $receipts_table->set($i+1, 4, $receipt['formatted_paid_on'], '', 'cell');
                    
                    $amount = $receipt['currency']. '$&nbsp;'. $receipt['amount_payable'];
                    $receipts_table->set($i+1, 5, $amount, '', 'cell');
                    
                    $payment = 'By Cash';
                    if ($receipt['paid_through'] != 'CSH') {
                        $payment = 'Bank Receipt #:<br/>'. $receipt['paid_id'];
                    }
                    $receipts_table->set($i+1, 6, $payment, '', 'cell payment');
                }

                echo $receipts_table->get_html();
            ?>
            </div>
        <?php
        }
        ?>
        </div>
        
        <!-- popup windows goes here -->
        <div id="paid_window" class="popup_window">
            <div class="popup_window_title">Confirm Payment</div>
            <form onSubmit="return false;">
                <input type="hidden" id="invoice_id" value="" />
                <div class="paid_form">
                    <table class="paid_form">
                        <tr>
                            <td class="label">Invoice:</td>
                            <td class="field"><span id="lbl_invoice"></span></td>
                        </tr>
                        <tr>
                            <td class="label">Paid On:</td>
                            <td class="field">
                            <?php
                                echo generate_dropdown('day', 'day', 1, 31, '', 2, 'Day');
                                echo generate_month_dropdown('month', 'month', 'Month');
                                
                                $today = explode('-', today());
                                $year = $today[0];
                                
                                echo generate_dropdown('year', 'year', $year-1, $year, '', 4, 'Year');
                            ?>
                            </td>
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
                            <td class="label">Bank Receipt #:</td>
                            <td class="field"><input type="text" class="field" id="payment_number" name="payment_number" /></td>
                        </tr>
                    </table>
                </div>
            </form>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Cancel" onClick="close_payment_popup(false);" />
                <input type="button" value="Confirm" onClick="close_payment_popup(true);" />
            </div>
        </div>
        
        <div id="resend_window" class="popup_window">
            <div class="popup_window_title">Resend Invoice <span id="lbl_resend_invoice"></span></div>
            <div class="employer_details">
                <span style="font-weight: bold;">Employer: </span>
                <span id="employer_name"></span>
                <br/>
                <span style="font-weight: bold;">Contact Person: </span>
                <span id="contact_person"></span>
            </div>
            <form onSubmit="return false;">
                <input type="hidden" id="resend_invoice_id" value="" />
                <div class="resend_form">
                    <span style="font-weight: bold;">Recipients: </span>(separated by commas)<br/>
                    <textarea id="recipients" class="recipients"></textarea>
                </div>
            </form>
            <div class="popup_window_buttons_bar">
                <input type="button" value="Cancel" onClick="close_resend_popup(false);" />
                <input type="button" value="Resend" onClick="close_resend_popup(true);" />
            </div>
        </div>
        <?php
    }
}
?>