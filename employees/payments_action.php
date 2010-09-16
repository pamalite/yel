<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id'])) {
    redirect_to('payments.php');
}

if (!isset($_POST['action'])) {
    redirect_to('payments.php');
}

function get_payments($_is_invoice = true, $_order = "invoices.issued_on", 
                      $_employer_to_filter = '') {
    $criteria = array(
        "columns" => "invoices.id, invoices.type, invoices.payable_by,
                      employers.name AS employer, employers.contact_person, employers.email_addr, 
                      employers.fax_num, employers.phone_num,
                      SUM(invoice_items.amount) AS amount_payable, currencies.symbol AS currency, 
                      DATE_FORMAT(invoices.issued_on, '%e %b, %Y') AS formatted_issued_on, 
                      DATE_FORMAT(invoices.payable_by, '%e %b, %Y') AS formatted_payable_by,
                      DATE_FORMAT(invoices.paid_on, '%e %b, %Y') AS formatted_paid_on", 
        "joins" => "employers ON employers.id = invoices.employer, 
                    branches ON branches.id = employers.branch, 
                    invoice_items ON invoice_items.invoice = invoices.id, 
                    currencies ON currencies.country_code = branches.country", 
        "group" => "invoices.id", 
        "order" => $_order
    );
    
    if ($_is_invoice) {
        $criteria['match'] = "invoices.paid_on IS NULL";
    } else {
        $criteria['columns'] .= ", invoices.paid_through, invoices.paid_id";
        $criteria['match'] = "invoices.paid_on IS NOT NULL";
    }
    
    if (!empty($_employer_to_filter)) {
        $criteria['match'] .= " AND invoices.employer = '". $_employer_to_filter. "'";
    }
    
    return Invoice::find($criteria);
}

$xml_dom = new XMLDOM();

if ($_POST['action'] == 'get_invoices') {
    $result = get_payments(true, $_POST['order_by'], $_POST['filter']);
    
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }
    
    if (!$result) {
        echo 'ko';
        exit();
    }
    
    $today = today();
    foreach($result as $i=>$row) {
        $result[$i]['employer'] = htmlspecialchars_decode(stripslashes($row['employer']));
        $result[$i]['padded_id'] = pad($row['id'], 11, '0');
        $result[$i]['amount_payable'] = number_format($row['amount_payable'], 2, '.', ', ');
        $delta = sql_date_diff($today, $row['payable_by']);
        if ($delta > 0) {
            $result[$i]['expired'] = 'expired';
        } else if ($delta == 0) {
            $result[$i]['expired'] = 'nearly';
        } else {
            $result[$i]['expired'] = 'no';
        }
    }
    
    $response = array('invoices' => array('invoice' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_receipts') {
    $result = get_payments(false, $_POST['order_by'], $_POST['filter']);
    
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }
    
    if (!$result) {
        echo 'ko';
        exit();
    }
    
    $today = today();
    foreach($result as $i=>$row) {
        $result[$i]['employer'] = htmlspecialchars_decode(stripslashes($row['employer']));
        $result[$i]['padded_id'] = pad($row['id'], 11, '0');
        $result[$i]['amount_payable'] = number_format($row['amount_payable'], 2, '.', ', ');
    }
    
    $response = array('receipts' => array('receipt' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'confirm_payment') {
    $data = array();
    $data['id'] = $_POST['id'];
    $data['paid_on'] = $_POST['paid_on'];
    $data['paid_through'] = $_POST['paid_through'];
    $data['paid_id'] = $_POST['paid_id'];
    
    if (!Invoice::update($data)) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_employer_info') {
    $criteria = array(
        'columns' => "employers.id, employers.name, employers.contact_person, employers.email_addr", 
        'joins' => "employers ON employers.id = invoices.employer", 
        'match' => "invoices.id = ". $_POST['id'],
        'limit' => "1"
    );
    
    $result = Invoice::find($criteria);
    if ($result === false || is_null($result) || count($result) <= 0) {
        echo '0';
        exit();
    }
    
    $response = array('employer' => $result);
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'resend') {
    $invoice = Invoice::get($_POST['id']);
    $invoice[0]['items'] = Invoice::getItems($_POST['id']);
    $employer = new Employer($invoice[0]['employer']);
    $branch = $employer->getAssociatedBranch();
    $sales = 'sales.'. strtolower($branch[0]['country']). '@yellowelevator.com';
    $branch[0]['address'] = str_replace(array("\r\n", "\r"), "\n", $branch[0]['address']);
    $branch['address_lines'] = explode("\n", $branch[0]['address']);
    $currency = Currency::getSymbolFromCountryCode($branch[0]['country']);
    $amount_payable = 0.00;
    foreach($invoice[0]['items'] as $i=>$item) {
        $amount_payable += $item['amount'];
        $items[$i]['amount'] = number_format($item['amount'], 2, '.', ', ');
    }
    $amount_payable = number_format($amount_payable, 2, '.', ', ');
    
    // generate pdf
    $pdf = new GeneralInvoice();
    $pdf->AliasNbPages();
    $pdf->SetAuthor('Yellow Elevator. This invoice was automatically generated. Signature is not required.');
    $pdf->SetTitle($GLOBALS['COMPANYNAME']. ' - Invoice '. pad($invoice[0]['id'], 11, '0'));
    $pdf->SetInvoiceType($invoice[0]['type'], 'Invoice');
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
    $pdf->Cell(60, 5, pad($invoice[0]['id'], 11, '0'),1,0,'C');
    $pdf->Cell(1);
    $pdf->Cell(33, 5, sql_date_format($invoice[0]['issued_on']),1,0,'C');
    $pdf->Cell(1);
    $pdf->Cell(33, 5, sql_date_format($invoice[0]['payable_by']),1,0,'C');
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
    $pdf->Output($GLOBALS['data_path']. '/general_invoices/'. $invoice[0]['id']. '.pdf', 'F');
    
    // generate email and attach it
    $attachment = chunk_split(base64_encode(file_get_contents($GLOBALS['data_path']. '/general_invoices/'. $invoice[0]['id']. '.pdf')));
    
    $subject = "YellowElevator: Resent Invoice ". pad($invoice[0]['id'], 11, '0');
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    $headers .= 'Bcc: '. $sales. "\n";
    $headers .= 'MIME-Version: 1.0'. "\n";
    $headers .= 'Content-Type: multipart/mixed; boundary="yel_mail_sep_'. $invoice[0]['id']. '";'. "\n\n";
    
    $body = '--yel_mail_sep_'. $invoice[0]['id']. "\n";
    $body .= 'Content-Type: multipart/alternative; boundary="yel_mail_sep_alt_'. $invoice[0]['id']. '"'. "\n";
    $body .= '--yel_mail_sep_alt_'. $invoice[0]['id']. "\n";
    $body .= 'Content-Type: text/plain; charset="iso-8859-1"'. "\n";
    $body .= 'Content-Transfer-Encoding: 7bit"'. "\n";
    
    $mail_lines = file('../private/mail/employer_general_invoice.txt');
    $message = '';
    foreach ($mail_lines as $line) {
        $message .= $line;
    }

    $message = str_replace('%employer%', $employer->getName(), $message);
    $message = str_replace('%invoice%', pad($invoice[0]['id'], 11, '0'), $message);
    $message = str_replace('%currency%', $currency, $message);
    $message = str_replace('%amount%', $amount_payable, $message);
    $message = str_replace('%issued_on%', sql_date_format($invoice[0]['issued_on']), $message);
    $message = str_replace('%payable_by%', sql_date_format($invoice[0]['payable_by']), $message);
    
    $body .= $message. "\n";
    $body .= '--yel_mail_sep_alt_'. $invoice[0]['id']. "--\n\n";
    $body .= '--yel_mail_sep_'. $invoice[0]['id']. "\n";
    $body .= 'Content-Type: application/pdf; name="yel_invoice_'. pad($invoice[0]['id'], 11, '0'). '.pdf"'. "\n";
    $body .= 'Content-Transfer-Encoding: base64'. "\n";
    $body .= 'Content-Disposition: attachment'. "\n";
    $body .= $attachment. "\n";
    $body .= '--yel_mail_sep_'. $invoice[0]['id']. "--\n\n";
    //mail($_employer->getEmailAddress(), $subject, $body, $headers);

    $handle = fopen('/tmp/email_to_'. $_employer->getEmailAddress(). '.txt', 'w');
    fwrite($handle, 'Subject: '. $subject. "\n\n");
    fwrite($handle, $body);
    fclose($handle);

    @unlink($GLOBALS['data_path']. '/subscription_invoices/'. $invoice[0]['id']. '.pdf');
    
    echo 'ok';
    exit();
}
?>