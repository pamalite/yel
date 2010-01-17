<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";
require_once dirname(__FILE__). "/../private/lib/classes/fpdf.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/employees/invoice_pdf.php?id='. $_GET['id']);
        exit();
    }
}

if (!isset($_SESSION['yel']['employee']) || 
    empty($_SESSION['yel']['employee']['uid']) || 
    empty($_SESSION['yel']['employee']['id']) || 
    empty($_SESSION['yel']['employee']['sid']) || 
    empty($_SESSION['yel']['employee']['hash'])) {
    echo "An illegal attempt to view invoice has been detected.";
    exit();
}

$clearances = $_SESSION['yel']['employee']['security_clearances'];
if (!Employee::has_clearance_for('invoices_view', $clearances)) {
    echo 'No permisison to view invoice.';
    exit();
}

$invoice = Invoice::get($_GET['id']);
$items = Invoice::get_items_of($_GET['id']);

if (!$invoice) {
    echo "Invoice not found.";
    exit();
}

$employer = new Employer($invoice[0]['employer']);
$query = "SELECT currencies.symbol 
          FROM currencies 
          LEFT JOIN employers ON currencies.country_code = employers.country 
          WHERE employers.id = '". $employer->id(). "' LIMIT 1";
$mysqli = Database::connect();
$result = $mysqli->query($query);
$currency = '???';
if (count($result) > 0 && !is_null($result)) {
    $currency = $result[0]['symbol'];
}

$amount_payable = 0.00;
foreach($items as $i=>$item) {
    $amount_payable += $item['amount'];
    $items[$i]['amount'] = number_format($item['amount'], 2, '.', ', ');
}
$amount_payable = number_format($amount_payable, 2, '.', ', ');
$invoice_or_receipt = (is_null($invoice[0]['paid_on']) || empty($invoice[0]['paid_on'])) ? 'Invoice' : 'Receipt';
$branch = $employer->get_branch();
$branch[0]['address'] = str_replace(array("\r\n", "\r"), "\n", $branch[0]['address']);
$branch['address_lines'] = explode("\n", $branch[0]['address']);

class InvoicePdf extends FPDF   {
    private $invoiceType;
    private $invoice_or_receipt;
    private $currency;
    private $branch;
    
    function SetInvoiceType($type, $_invoice_or_receipt) {
        $this->invoiceType = $type;
        $this->invoice_or_receipt = $_invoice_or_receipt;
    }
    
    function SetCurrency($_currency) {
        $this->currency = $_currency;
    }
    
    function SetBranch($_branch) {
        $this->branch = $_branch;
    }
    
    //Page header
    function Header()   {
        //Logo
        $this->Image($GLOBALS['protocol']. '://'. $GLOBALS['root']. '/common/images/logos/top_letterhead_pdf.png', 10, 8);
        //Arial bold 15
        $this->SetFont('Arial', 'B', 15);
        //Move to the right
        $this->Cell(80);
        //Title
        switch ($this->invoiceType) {
            case 'J':
                $this->Cell(30, 20, "Subscription ". $this->invoice_or_receipt, 0, 0, 'C');
                break;
            case 'R':
                $this->Cell(30, 20, "Service Fee ". $this->invoice_or_receipt, 0, 0, 'C');
                break;
            default:
                $this->Cell(30, 20, "Miscellaneous ". $this->invoice_or_receipt, 0, 0,'C');
                break;
        }
        
        $this->Cell(30);
        $this->SetFont('Arial', '', 5);
        $this->Cell(5, 3, "Phone:", 0, 0);
        $this->Cell(11);
        $this->Cell(5, 3, $this->branch[0]['phone'], 0, 2);
        $this->Cell(-16);
        
        $this->Cell(5, 3, "Fax:", 0, 0);
        $this->Cell(11);
        $this->Cell(5, 3, $this->branch[0]['fax'], 0, 2);
        $this->Cell(-16);
        
        $this->Cell(5, 3, "E-mail:", 0, 0);
        $this->Cell(11);
        $this->Cell(5, 3, "sales@yellowelevator.com", 0, 2);
        $this->Cell(-16);
        
        $this->Cell(5, 3, "Mailing Address:", 0, 0);
        $this->Cell(11);
        foreach ($this->branch['address_lines'] as $i=>$line) {
            if ($i == count($this->branch['address_lines']) - 1) {
                $line = $line. ", ";
            }
            $this->Cell(5, 3, $line, 0, 2);
        }
        $this->Cell(5, 3, $this->branch[0]['zip']. " ". $this->branch[0]['state']. ", ". $this->branch[0]['country_name']. ". ", 0, 2);
        
        $this->SetFont('Arial','',12);
        //Line break
        $this->Ln(5);
    }

    function Footer() {
        //Page footer
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(128);
        $this->Cell(0,10,'Page '.$this->PageNo(). ' of {nb}',0,0,'C');
    }

    function FancyTable($header, $data, $_amount_payable) {
        //Colors, line width and bold font
        $this->SetFillColor(54, 54, 54);
        $this->SetTextColor(255);
        $this->SetDrawColor(255, 255, 255);
        $this->SetLineWidth(0.3);
        //$this->SetFont('','B');
        //Header
        $w = array(10, 135, 45);
        for($i=0; $i < count($header); $i++) {
            $this->Cell($w[$i], 5, $header[$i], 1, 0, 'C', 1);
        }
        
        $this->Ln();
        //Color and font restoration
        $this->SetFillColor(204, 204, 204);
        $this->SetTextColor(0);
        $this->SetFont('');
        //Data
        $fill=0;
        $count = 1;
        foreach($data as $row) {
            $this->Cell($w[0], 5, $count, 'L', 0, 'L', $fill);
            $this->Cell($w[1], 5, stripslashes(htmlspecialchars_decode($row['itemdesc'])), 'L', 0, 'L', $fill);
            $this->Cell($w[2], 5, $row['amount'], 'L', 0,'R', $fill);
            $this->Ln();
            $fill = !$fill;
            $count++;
        }
        $this->Cell(array_sum($w),0,'','T');
        $this->Ln();
        $this->SetFillColor(54, 54, 54);
        $this->SetDrawColor(54, 54, 54);
        $this->SetTextColor(255);
        $this->Cell($w[0]+$w[1], 5, "Total Amount Payable (". $this->currency. ") ", 1, 0, 'R', 1);
        $this->SetTextColor(0);
        $this->SetFont('', 'B');
        $this->SetDrawColor(0, 0, 0);
        $this->Cell($w[2], 5, $_amount_payable, 1, 0, 'R', 0);
        $this->SetFont('Arial', '', 10);
    }
}

$pdf = new InvoicePdf();
$pdf->AliasNbPages();
$pdf->SetAuthor('Yellow Elevator. This invoice was automatically generated. Signature is not required.');
$pdf->SetTitle($GLOBALS['COMPANYNAME']. ' - Invoice '. pad($_GET['id'], 11, '0'));
$pdf->SetInvoiceType($invoice[0]['type'], $invoice_or_receipt);
$pdf->SetCurrency($currency);
$pdf->SetBranch($branch);
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFillColor(54, 54, 54);
$pdf->Cell(60, 5, "Invoice Number",1,0,'C',1);
$pdf->Cell(1);
$pdf->Cell(33, 5, "Issuance Date",1,0,'C',1);
$pdf->Cell(1);

if (is_null($invoice[0]['paid_on']) || empty($invoice[0]['paid_on'])) {
    $pdf->Cell(33, 5, "Payable By",1,0,'C',1);
} else {
    $pdf->Cell(33, 5, "Paid On",1,0,'C',1);
}

$pdf->Cell(1);
$pdf->Cell(0, 5, "Amount Payable (". $currency. ")",1,0,'C',1);
$pdf->Ln(6);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(60, 5, pad($_GET['id'], 11, '0'),1,0,'C');
$pdf->Cell(1);
$pdf->Cell(33, 5, $invoice[0]['issued_on'],1,0,'C');
$pdf->Cell(1);

if (is_null($invoice[0]['paid_on']) || empty($invoice[0]['paid_on']))
{
    $pdf->Cell(33, 5, $invoice[0]['payable_by'],1,0,'C');
} else {
    $pdf->Cell(33, 5, $invoice[0]['paid_on'],1,0,'C');
}

$pdf->Cell(1);
$pdf->Cell(0, 5, $amount_payable,1,0,'C');
$pdf->Ln(6);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(60, 5, "User ID",1,0,'C',1);
$pdf->Cell(1);
$pdf->Cell(0, 5, "Employer Name",1,0,'C',1);
$pdf->Ln(6);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(60, 5, $invoice[0]['employer'],1,0,'C');
$pdf->Cell(1);
$pdf->Cell(0, 5, $employer->get_name(),1,0,'C');
$pdf->Ln(10);

$table_header = array("No.", "Item", "Amount (". $currency. ")");
$pdf->FancyTable($table_header, $items, $amount_payable);

$pdf->Ln(13);
$pdf->SetFont('','I');
$pdf->Cell(0, 0, "This invoice was automatically generated. Signature is not required.", 0, 0, 'C');
$pdf->Ln(6);
$pdf->Cell(0, 5, "Payment Notice",'LTR',0,'C');
$pdf->Ln();
$pdf->Cell(0, 5, "- Payment shall be made payable to Yellow Elevator Sdn. Bhd.", 'LR', 0, 'C');
$pdf->Ln();
$pdf->Cell(0, 5, "- To facilitate the processing of the payment, please write down the invoice number(s) on your cheque(s)/payment slip(s)", 'LBR', 0, 'C');
$pdf->Ln(10);
$pdf->Cell(0, 0, "E. & O. E.", 0, 0, 'C');

$pdf->Close();
$pdf->Output('yel_'. strtolower($invoice_or_receipt). '_'. $_GET['id']. '.pdf', 'D');
?>
