<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";
require_once dirname(__FILE__). "/subscription_invoice.php";

session_start();

if (!isset($_POST['id'])) {
    echo "ko";
    exit();
    //redirect_to('login.php');
}

$xml_dom = new XMLDOM();

if ($_POST['action'] == 'get_subscriptions_details') {
    $employer = new Employer($_POST['id']);
    $result = $employer->get_subscriptions_details();
    $result[0]['has_free_postings'] = ($employer->has_free_job_postings() === false) ? '0' : $employer->has_free_job_postings();
    $result[0]['has_paid_postings'] = ($employer->has_paid_job_postings() === false) ? '0' : $employer->has_paid_job_postings();
    $response = array('subscription' => $result[0]);
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'buy_subscriptions') {
    $mysqli = Database::connect();
    $employer = new Employer($_POST['id']);
    
    // 1. generate invoice in the system
    $data = array();
    $data['issued_on'] = today();
    $data['type'] = 'J';
    $data['employer'] = $_POST['id'];
    $data['payable_by'] = sql_date_add($data['issued_on'], $employer->get_payment_terms_days(), 'day');
    
    $invoice = Invoice::create($data);
    if ($invoice === false) {
        echo 'ko';
        exit();
    }
    
    $desc = $_POST['period']. ' month(s) of subscription';
    $item_added = Invoice::add_item($invoice, $_POST['amount'], '1', $desc);
    
    $items = array();
    $items[0]['itemdesc'] = $desc;
    $items[0]['amount'] = number_format($_POST['price'], '2', '.', ', ');
    $items[1]['itemdesc'] = 'Administration Fee';
    $items[1]['amount'] = number_format($_POST['admin_fee'], '2', '.', ', ');
    
    // 2. generate the PDF version to be attached to sales.xx and the employer
    $branch_raw = $employer->get_branch();
    $sales = 'sales.'. strtolower($branch_raw[0]['country']). '@yellowelevator.com';
    $branch_raw[0]['address'] = str_replace(array("\r\n", "\r"), "\n", $branch_raw[0]['address']);
    $branch_raw['address_lines'] = explode("\n", $branch_raw[0]['address']);
    $currency = $_POST['currency'];
    
    $pdf = new SubscriptionInvoice();
    $pdf->AliasNbPages();
    $pdf->SetAuthor('Yellow Elevator. This invoice was automatically generated. Signature is not required.');
    $pdf->SetTitle($GLOBALS['COMPANYNAME']. ' - Invoice '. pad($invoice, 11, '0'));
    $pdf->SetCurrency($currency);
    $pdf->SetBranch($branch_raw);
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFillColor(54, 54, 54);
    $pdf->Cell(60, 5, "Invoice Number",1,0,'C',1);
    $pdf->Cell(1);
    $pdf->Cell(33, 5, "Issuance Date",1,0,'C',1);
    $pdf->Cell(1);
    $pdf->Cell(33, 5, "Payable By",1,0,'C',1);
    $pdf->Cell(1);
    $pdf->Cell(0, 5, "Amount Payable (". $currency. ")",1,0,'C',1);
    $pdf->Ln(6);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(60, 5, pad($invoice, 11, '0'),1,0,'C');
    $pdf->Cell(1);
    $pdf->Cell(33, 5, $data['issued_on'],1,0,'C');
    $pdf->Cell(1);
    $pdf->Cell(33, 5, $data['payable_by'],1,0,'C');
    $pdf->Cell(1);
    $pdf->Cell(0, 5, number_format($_POST['amount'], '2', '.', ', '),1,0,'C');
    $pdf->Ln(6);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(60, 5, "User ID",1,0,'C',1);
    $pdf->Cell(1);
    $pdf->Cell(0, 5, "Employer Name",1,0,'C',1);
    $pdf->Ln(6);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(60, 5, $employer->id(),1,0,'C');
    $pdf->Cell(1);
    $pdf->Cell(0, 5, $employer->get_name(),1,0,'C');
    $pdf->Ln(10);
    
    $table_header = array("No.", "Item", "Amount (". $currency. ")");
    $pdf->FancyTable($table_header, $items, number_format($_POST['amount'], '2', '.', ', '));
    
    $pdf->Ln(13);
    $pdf->SetFont('','I');
    $pdf->Cell(0, 0, "This invoice was automatically generated. Signature is not required.", 0, 0, 'C');
    $pdf->Ln(6);
    $pdf->Cell(0, 5, "Payment Notice",'LTR',0,'C');
    $pdf->Ln();
    $pdf->Cell(0, 5, "- Payment shall be made payable to ". $branch_raw[0]['branch']. ".", 'LR', 0, 'C');
    $pdf->Ln();
    $pdf->Cell(0, 5, "- To facilitate the processing of the payment, please write down the invoice number(s) on your cheque(s)/payment slip(s)", 'LBR', 0, 'C');
    $pdf->Ln(10);
    $pdf->Cell(0, 0, "E. & O. E.", 0, 0, 'C');
    $pdf->Close();
    $pdf->Output($GLOBALS['data_path']. '/subscription_invoices/'. $invoice. '.pdf', 'F');
    
    $attachment = chunk_split(base64_encode(file_get_contents($GLOBALS['data_path']. '/subscription_invoices/'. $invoice. '.pdf')));
    
    $subject = "Subscription Invoice ". pad($invoice, 11, '0');
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    $headers .= 'Bcc: '. $sales. "\n";
    $headers .= 'MIME-Version: 1.0'. "\n";
    $headers .= 'Content-Type: multipart/mixed; boundary="yel_mail_sep_'. $invoice. '";'. "\n\n";
    
    $body = '--yel_mail_sep_'. $invoice. "\n";
    $body .= 'Content-Type: multipart/alternative; boundary="yel_mail_sep_alt_'. $invoice. '"'. "\n";
    $body .= '--yel_mail_sep_alt_'. $invoice. "\n";
    $body .= 'Content-Type: text/plain; charset="iso-8859-1"'. "\n";
    $body .= 'Content-Transfer-Encoding: 7bit"'. "\n";
    
    $mail_lines = file('../private/mail/employer_subscription_invoice.txt');
    $message = '';
    foreach ($mail_lines as $line) {
        $message .= $line;
    }
    
    $message = str_replace('%employer%', $employer->get_name(), $message);
    $message = str_replace('%period%', $_POST['period'], $message);
    $message = str_replace('%currency%', $currency, $message);
    $message = str_replace('%amount%', number_format($_POST['amount'], 2, '.', ', '), $message);
    
    $body .= $message. "\n";
    $body .= '--yel_mail_sep_alt_'. $invoice. "--\n\n";
    $body .= '--yel_mail_sep_'. $invoice. "\n";
    $body .= 'Content-Type: application/pdf; name="yel_credit_note_'. pad($invoice, 11, '0'). '.pdf"'. "\n";
    $body .= 'Content-Transfer-Encoding: base64'. "\n";
    $body .= 'Content-Disposition: attachment'. "\n";
    $body .= $attachment. "\n";
    $body .= '--yel_mail_sep_'. $invoice. "--\n\n";
    mail($employer->get_email_address(), $subject, $body, $headers);
    
    // $handle = fopen('/tmp/email_to_'. $employer->get_email_address(). '.txt', 'w');
    // fwrite($handle, 'Subject: '. $subject. "\n\n");
    // fwrite($handle, $body);
    // fclose($handle);
    
    unlink($GLOBALS['data_path']. '/subscription_invoices/'. $invoice. '.pdf');
    
    // 3. extend the subscription
    if ($employer->extend_subscription($_POST['period']) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

?>
