<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";
//require_once dirname(__FILE__). "/../private/lib/classes/fpdf.php";
require_once dirname(__FILE__). "/general_invoice.php";

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

if (isset($_SESSION['yel']['employee']['dev'])) {
    if ($_SESSION['yel']['employee']['dev'] === true) {
        $is_dev = false;
        $root_items = explode('/', $GLOBALS['root']);
        foreach ($root_items as $value) {
            if ($value == 'yel') {
                $is_dev = true;
                break;
            }
        }

        if (!$is_dev) {
            ?>
            <script type="text/javascript">alert('Please logout from your existing connection before proceeding.');</script>
            <?php
            exit();
        }
    }
}

$invoice = Invoice::get($_GET['id']);
$items = Invoice::getItems($_GET['id']);

if (!$invoice) {
    echo "Invoice not found.";
    exit();
}

$employer = new Employer($invoice[0]['employer']);
$branch = $employer->getAssociatedBranch();
$branch[0]['address'] = str_replace(array("\r\n", "\r"), "\n", $branch[0]['address']);
$branch['address_lines'] = explode("\n", $branch[0]['address']);
$currency = Currency::getSymbolFromCountryCode($branch[0]['country']);
$amount_payable = 0.00;
foreach($items as $i=>$item) {
    $amount_payable += $item['amount'];
    $items[$i]['amount'] = number_format($item['amount'], 2, '.', ', ');
}
$amount_payable = number_format($amount_payable, 2, '.', ', ');
$invoice_or_receipt = (is_null($invoice[0]['paid_on']) || empty($invoice[0]['paid_on'])) ? 'Invoice' : 'Receipt';

$pdf = new GeneralInvoice();
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
$pdf->Cell(33, 5, sql_date_format($invoice[0]['issued_on']),1,0,'C');
$pdf->Cell(1);

if (is_null($invoice[0]['paid_on']) || empty($invoice[0]['paid_on']))
{
    $pdf->Cell(33, 5, sql_date_format($invoice[0]['payable_by']),1,0,'C');
} else {
    $pdf->Cell(33, 5, sql_date_format($invoice[0]['paid_on']),1,0,'C');
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
$pdf->Cell(0, 5, $employer->getName(),1,0,'C');
$pdf->Ln(10);

$table_header = array("No.", "Item", "Amount (". $currency. ")");
$pdf->FancyTable($table_header, $items, $amount_payable);

$pdf->Ln(13);
$pdf->SetFont('','I');
$pdf->Cell(0, 0, "This invoice was automatically generated. Signature is not required.", 0, 0, 'C');
$pdf->Ln(6);
$pdf->Cell(0, 5, "Payment Notice",'LTR',0,'C');
$pdf->Ln();
$pdf->Cell(0, 5, "- Payment shall be made payable to ". $branch[0]['branch']. ".", 'LR', 0, 'C');
$pdf->Ln();
$pdf->Cell(0, 5, "- To facilitate the processing of the payment, please write down the invoice number(s) on your cheque(s)/payment slip(s)", 'LBR', 0, 'C');
$pdf->Ln(10);
$pdf->Cell(0, 0, "E. & O. E.", 0, 0, 'C');

$pdf->Close();
$pdf->Output('yel_'. strtolower($invoice_or_receipt). '_'. $_GET['id']. '.pdf', 'D');
?>
