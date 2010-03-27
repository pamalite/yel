<?php
require_once dirname(__FILE__). "/../../utilities.php";
require_once dirname(__FILE__). '/../htmltable.php';

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
        
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/flextable.js"></script>'. "\n";
        echo '<script type="text/javascript" src="'. $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/scripts/employer_invoices.js"></script>'. "\n";
    }
    
    public function insert_inline_scripts() {
        echo '<script type="text/javascript">'. "\n";
        echo 'var id = "'. $this->employer->getId(). '";'. "\n";
        echo '</script>'. "\n";
    }
    
    private function get_invoices($_is_paid = false) {
        $order_by = 'issued_on asc';
        
        $paid_on_clause = "paid_on IS NULL";
        if ($_is_paid) {
            $paid_on_clause = "paid_on IS NOT NULL";
        }
        
        $criteria = array(
            'columns' => "id, type, DATEDIFF(payable_by, now()) AS expired, 
                          DATE_FORMAT(issued_on, '%e %b, %Y') AS formatted_issued_on, 
                          DATE_FORMAT(payable_by, '%e %b, %Y') AS formatted_payable_by,
                          DATE_FORMAT(paid_on, '%e %b, %Y') AS formatted_paid_on",
            'match' => "employer = '". $this->employer->getId(). "' AND ". $paid_on_clause, 
            'order' => $order_by
        );
        
        return Invoice::find($criteria);
    }
    
    
    public function show() {
        $this->begin();
        $this->support($this->employer->getId());
        $this->top('Invoices &amp; Receipts');
        $this->menu('employer', 'invoices');
        
        $invoices = $this->get_invoices();
        $receipts = $this->get_invoices(true);
        
        ?>
        <div id="div_status" class="status">
            <span id="span_status" class="status"></span>
        </div>
        
        <div id="div_tabs">
            <ul>
                <li id="li_invoices">Invoices</li>
                <li id="li_receipts">Receipts</li>
            </ul>
        </div>
        
        <div id="div_invoices">
        <?php
            if (empty($invoices)) {
        ?>
            <div class="empty_results">No invoices issued at this moment.</div>
        <?php
            } else {
                $invoices_table = new HTMLTable('invoices_table', 'payments');
                
                $invoices_table->set(0, 0, "&nbsp;", '', 'header cell_indicator');
                $invoices_table->set(0, 1, "<a class=\"sortable\" onClick=\"sort_by('invoices', 'issued_on');\">Issued On</a>", '', 'header');
                $invoices_table->set(0, 2, "<a class=\"sortable\" onClick=\"sort_by('invoices', 'payable_by');\">Payable By</a>", '', 'header');
                $invoices_table->set(0, 3, "<a class=\"sortable\" onClick=\"sort_by('invoices', 'type');\">Type</a>", '', 'header');
                $invoices_table->set(0, 4, "<a class=\"sortable\" onClick=\"sort_by('invoices', 'id');\">Invoice</a>", '', 'header');
                
                foreach ($invoices as $i=>$invoice) {
                    if ($invoice['expired'] <= 0) {
                        $invoices_table->set($i+1, 0, '<img src="../common/images/icons/expired.png" />', '', 'cell cell_indicator');
                    } else {
                        $invoices_table->set($i+1, 0, '&nbsp;', '', 'cell cell_indicator');
                    }
                    
                    $invoices_table->set($i+1, 1, $invoice['formatted_issued_on'], '', 'cell');
                    $invoices_table->set($i+1, 2, $invoice['formatted_payable_by'], '', 'cell');
                    
                    $type = 'Others';
                    switch ($invoice['type']) {
                        case 'R':
                            $type = 'Service Fee';
                            break;
                        case 'J':
                            $type = 'Subscription';
                            break;
                        case 'P':
                            $type = 'Job Posting';
                            break;
                    }
                    $invoices_table->set($i+1, 3, $type, '', 'cell');
                    $invoices_table->set($i+1, 4, '<a class="no_link" onClick="show_invoice_page('. $invoice['id']. ');">'. pad($invoice['id'], 11, '0'). '</a>', '', 'cell');
                }
                
                echo $invoices_table->get_html();
            }
        ?>
        </div>
        
        <div id="div_receipts">
        <?php
            if (empty($receipts)) {
        ?>
            <div class="empty_results">No receipts issued at this moment.</div>
        <?php
            } else {
                $receipts_table = new HTMLTable('receipts_table', 'payments');

                $receipts_table->set(0, 0, "<a class=\"sortable\" onClick=\"sort_by('receipts', 'issued_on');\">Issued On</a>", '', 'header');
                $receipts_table->set(0, 1, "<a class=\"sortable\" onClick=\"sort_by('receipts', 'paid_on');\">Paid On</a>", '', 'header');
                $receipts_table->set(0, 2, "<a class=\"sortable\" onClick=\"sort_by('receipts', 'type');\">Type</a>", '', 'header');
                $receipts_table->set(0, 3, "<a class=\"sortable\" onClick=\"sort_by('receipts', 'id');\">Receipts</a>", '', 'header');

                foreach ($receipts as $i=>$receipt) {
                    $receipts_table->set($i+1, 0, $receipt['formatted_issued_on'], '', 'cell');
                    $receipts_table->set($i+1, 1, $receipt['formatted_paid_on'], '', 'cell');

                    $type = 'Others';
                    switch ($receipt['type']) {
                        case 'R':
                            $type = 'Service Fee';
                            break;
                        case 'J':
                            $type = 'Subscription';
                            break;
                        case 'P':
                            $type = 'Job Posting';
                            break;
                    }
                    $receipts_table->set($i+1, 2, $type, '', 'cell');
                    $receipts_table->set($i+1, 3, '<a class="no_link" onClick="show_invoice_page('. $invoice['id']. ');">'. pad($receipt['id'], 11, '0'). '</a>', '', 'cell');
                }

                echo $receipts_table->get_html();
            }
        ?>
        </div>
        
        <?php
    }
}
?>