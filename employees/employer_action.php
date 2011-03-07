<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";
require_once dirname(__FILE__). "/paid_postings_invoice.php";
require_once dirname(__FILE__). "/../private/config/subscriptions_rate.inc";
require_once dirname(__FILE__). "/subscription_invoice.php";

session_start();

if (!isset($_POST['id'])) {
    redirect_to('employer.php');
}

if (!isset($_POST['action'])) {
    redirect_to('employer.php');
}

$xml_dom = new XMLDOM();

function send_invoice(&$_employer, $_paid_postings, $_subscription_period) {
    if (!is_a($_employer, 'Employer')) {
        return false;
    }
    
    $today = date('Y-m-d');
    $branch = $_employer->getAssociatedBranch();
    $sales = 'sales.'. strtolower($branch[0]['country']). '@yellowelevator.com';
    $branch[0]['address'] = str_replace(array("\r\n", "\r"), "\n", $branch[0]['address']);
    $branch['address_lines'] = explode("\n", $branch[0]['address']);
    $currency = Currency::getSymbolFromCountryCode($branch[0]['country']);
    
    if ($_paid_postings > 0) {
        // 0. get the job postings pricing and currency
        $posting_rates = $GLOBALS['postings_rates'];
        $price = $posting_rates[$currency];
        
        // 1. generate invoice in the system
        $data = array();
        $data['issued_on'] = $today;
        $data['type'] = 'P';
        $data['employer'] = $_POST['id'];
        $data['payable_by'] = sql_date_add($today, $_employer->getPaymentTermsInDays(), 'day');
        
        $invoice = Invoice::create($data);
        if ($invoice === false) {
            echo 'ko';
            exit();
        }
        
        $amount = $price * $_paid_postings;
        $desc = $_paid_postings. ' Job Posting(s) @ '. $currency. ' $'. $price;
        $item_added = Invoice::addItem($invoice, $amount, '1', $desc);
        
        $items = array();
        $items[0]['itemdesc'] = $desc;
        $items[0]['amount'] = number_format($amount, '2', '.', ', ');
        
        // 2. generate the invoice as PDF file
        $pdf = new PaidPostingsInvoice();
        $pdf->AliasNbPages();
        $pdf->SetAuthor('Yellow Elevator. This invoice was automatically generated. Signature is not required.');
        $pdf->SetTitle($GLOBALS['COMPANYNAME']. ' - Invoice '. pad($invoice, 11, '0'));
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
        $pdf->Cell(33, 5, "Payable By",1,0,'C',1);
        $pdf->Cell(1);
        $pdf->Cell(0, 5, "Amount Payable (". $currency. ")",1,0,'C',1);
        $pdf->Ln(6);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(60, 5, pad($invoice, 11, '0'),1,0,'C');
        $pdf->Cell(1);
        
        $pdf->Cell(33, 5, sql_date_format($data['issued_on']),1,0,'C');
        $pdf->Cell(1);
        
        $pdf->Cell(33, 5, sql_date_format($data['payable_by']),1,0,'C');
        $pdf->Cell(1);
        $pdf->Cell(0, 5, number_format($amount, '2', '.', ', '),1,0,'C');
        $pdf->Ln(6);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(60, 5, "User ID",1,0,'C',1);
        $pdf->Cell(1);
        $pdf->Cell(0, 5, "Employer Name",1,0,'C',1);
        $pdf->Ln(6);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(60, 5, $_employer->getId(),1,0,'C');
        $pdf->Cell(1);
        $pdf->Cell(0, 5, $_employer->getName(),1,0,'C');
        $pdf->Ln(10);
    
        $table_header = array("No.", "Item", "Amount (". $currency. ")");
        $pdf->FancyTable($table_header, $items, number_format($amount, '2', '.', ', '));
    
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
        $pdf->Output($GLOBALS['data_path']. '/subscription_invoices/'. $invoice. '.pdf', 'F');
        
        // 3. sends it as an email
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
    
        $mail_lines = file('../private/mail/employer_posting_invoice.txt');
        $message = '';
        foreach ($mail_lines as $line) {
           $message .= $line;
        }
    
        $message = str_replace('%employer%', $_employer->getName(), $message);
        $message = str_replace('%postings%', $_POST['paid_postings'], $message);
        $message = str_replace('%price%', $price, $message);
        $message = str_replace('%currency%', $currency, $message);
        $message = str_replace('%amount%', number_format($amount, 2, '.', ', '), $message);
    
        $issued_date = explode('-', $data['issued_on']);
        $issued_timestamp = $issued_date[0]. $issued_date[1]. $issued_date[2];
        $message = str_replace('%purchased_on%', date('j M', $issued_timestamp). ', '. $issued_date[0], $message);
    
        $body .= $message. "\n";
        $body .= '--yel_mail_sep_alt_'. $invoice. "--\n\n";
        $body .= '--yel_mail_sep_'. $invoice. "\n";
        $body .= 'Content-Type: application/pdf; name="yel_invoice_'. pad($invoice, 11, '0'). '.pdf"'. "\n";
        $body .= 'Content-Transfer-Encoding: base64'. "\n";
        $body .= 'Content-Disposition: attachment'. "\n";
        $body .= $attachment. "\n";
        $body .= '--yel_mail_sep_'. $invoice. "--\n\n";
        mail($_employer->getEmailAddress(), $subject, $body, $headers);
    
        // $handle = fopen('/tmp/email_to_'. $_employer->getEmailAddress(). '.txt', 'w');
        // fwrite($handle, 'Subject: '. $subject. "\n\n");
        // fwrite($handle, $body);
        // fclose($handle);
    
        unlink($GLOBALS['data_path']. '/subscription_invoices/'. $invoice. '.pdf');
    }
    
    if ($_subscription_period > 0) {
        $single_subscription_prices = array(
            'MYR' => 1000,
            'SGD' => 1000
        );
        
        // 0. get the job postings pricing and currency
        $subscriptions_rates = $GLOBALS['subscriptions_rates'];
        $amount = $subscriptions_rates[$currency][$_subscription_period];
        if ($_subscription_period == 1) {
            $amount = $single_subscription_prices[$currency];
        }
        
        $admin_fee = 0.05 * $amount;
        $total = $admin_fee + $amount;
        
        // 1. generate invoice in the system
        $data = array();
        $data['issued_on'] = $today;
        $data['type'] = 'J';
        $data['employer'] = $_employer->getId();
        $data['payable_by'] = sql_date_add($today, $_employer->getPaymentTermsInDays(), 'day');
        
        $invoice = Invoice::create($data);
        if ($invoice === false) {
            echo 'ko';
            exit();
        }
        
        $desc = $_subscription_period. ' month(s) of subscription';
        $item_added = Invoice::addItem($invoice, $total, '1', $desc);
    
        $items = array();
        $items[0]['itemdesc'] = $desc;
        $items[0]['amount'] = number_format($amount, '2', '.', ', ');
        $items[1]['itemdesc'] = 'Administration Fee';
        $items[1]['amount'] = number_format($admin_fee, '2', '.', ', ');
        
        // 2. generate the PDF version to be attached to sales.xx and the employer
        $pdf = new SubscriptionInvoice();
        $pdf->AliasNbPages();
        $pdf->SetAuthor('Yellow Elevator. This invoice was automatically generated. Signature is not required.');
        $pdf->SetTitle($GLOBALS['COMPANYNAME']. ' - Invoice '. pad($invoice, 11, '0'));
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
        $pdf->Cell(33, 5, "Payable By",1,0,'C',1);
        $pdf->Cell(1);
        $pdf->Cell(0, 5, "Amount Payable (". $currency. ")",1,0,'C',1);
        $pdf->Ln(6);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(60, 5, pad($invoice, 11, '0'),1,0,'C');
        $pdf->Cell(1);
        
        $pdf->Cell(33, 5, sql_date_format($data['issued_on']),1,0,'C');
        $pdf->Cell(1);
        
        $pdf->Cell(33, 5, sql_date_format($data['payable_by']),1,0,'C');
        $pdf->Cell(1);
        $pdf->Cell(0, 5, number_format($total, '2', '.', ', '),1,0,'C');
        $pdf->Ln(6);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(60, 5, "User ID",1,0,'C',1);
        $pdf->Cell(1);
        $pdf->Cell(0, 5, "Employer Name",1,0,'C',1);
        $pdf->Ln(6);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(60, 5, $_employer->getId(),1,0,'C');
        $pdf->Cell(1);
        $pdf->Cell(0, 5, $_employer->getName(),1,0,'C');
        $pdf->Ln(10);
    
        $table_header = array("No.", "Item", "Amount (". $currency. ")");
        $pdf->FancyTable($table_header, $items, number_format($total, '2', '.', ', '));
    
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
        $pdf->Output($GLOBALS['data_path']. '/subscription_invoices/'. $invoice. '.pdf', 'F');
        
        // 3. attach it to email
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
    
        $message = str_replace('%employer%', $_employer->getName(), $message);
        $message = str_replace('%period%', $_subscription_period, $message);
        $message = str_replace('%currency%', $currency, $message);
        $message = str_replace('%amount%', number_format($total, 2, '.', ', '), $message);
    
        $issued_date = explode('-', $data['issued_on']);
        $issued_timestamp = $issued_date[0]. $issued_date[1]. $issued_date[2];
        $message = str_replace('%purchased_on%', date('j M', $issued_timestamp). ', '. $issued_date[0], $message);
    
        $body .= $message. "\n";
        $body .= '--yel_mail_sep_alt_'. $invoice. "--\n\n";
        $body .= '--yel_mail_sep_'. $invoice. "\n";
        $body .= 'Content-Type: application/pdf; name="yel_invoice_'. pad($invoice, 11, '0'). '.pdf"'. "\n";
        $body .= 'Content-Transfer-Encoding: base64'. "\n";
        $body .= 'Content-Disposition: attachment'. "\n";
        $body .= $attachment. "\n";
        $body .= '--yel_mail_sep_'. $invoice. "--\n\n";
        mail($_employer->getEmailAddress(), $subject, $body, $headers);
    
        /*$handle = fopen('/tmp/email_to_'. $_employer->getEmailAddress(). '.txt', 'w');
        fwrite($handle, 'Subject: '. $subject. "\n\n");
        fwrite($handle, $body);
        fclose($handle);*/
    
        unlink($GLOBALS['data_path']. '/subscription_invoices/'. $invoice. '.pdf');
    }
    
    return true;
}

if ($_POST['action'] == 'reset_password') {
    $new_password = generate_random_string_of(6);
    $data = array();
    $data['password'] = md5($new_password);
    $employer = new Employer($_POST['id']);
    $employer->setAdmin(true);
    if ($employer->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    $lines = file(dirname(__FILE__). '/../private/mail/employer_password_reset_admin.txt');
    $message = '';
    foreach($lines as $line) {
        $message .= $line;
    }
    
    $message = str_replace('%user_id%', $_POST['id'], $message);
    $message = str_replace('%temporary_password%', $new_password, $message);
    $subject = "Employer Password Reset";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($employer->getEmailAddress(), $subject, $message, $headers);
    
    // $handle = fopen('/tmp/email_to_'. $employer->getEmailAddress(). '.txt', 'w');
    // fwrite($handle, 'Subject: '. $subject. "\n\n");
    // fwrite($handle, $message);
    // fclose($handle);
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'save_profile') {
    $today = now();
    
    $mode = 'update';
    if ($_POST['id'] == '0') {
        $mode = 'create';
    }
    
    $employee = new Employee($_POST['employee']);
    $branch = $employee->getBranch();
    
    $data = array();
    $data['license_num'] = $_POST['license_num'];
    $data['is_ye_connect_enabled'] = $_POST['is_ye_connect_enabled'];
    $data['name'] = $_POST['name'];
    $data['phone_num'] = $_POST['phone_num'];
    $data['fax_num'] = $_POST['fax_num'];
    $data['email_addr'] = $_POST['email_addr'];
    $data['contact_person'] = $_POST['contact_person'];
    $data['hr_contacts'] = $_POST['hr_contacts'];
    $data['address'] = $_POST['address'];
    $data['state'] = $_POST['state'];
    $data['zip'] = $_POST['zip'];
    $data['country'] = $_POST['country'];
    // $data['working_months'] = $_POST['working_months'];
    // $data['payment_terms_days'] = $_POST['payment_terms_days'];
    $data['branch'] = $branch[0]['id'];
    
    $data['website_url'] = $_POST['website_url'];
    if (!empty($data['website_url'])) {
        if (substr($_POST['website_url'], 0, 4) != 'http') {
            $data['website_url'] = 'http://'. $_POST['website_url'];
        }
    }
    
    $employer = NULL;
    if ($mode == 'update') {
        $employer = new Employer($_POST['id']);
        if (!$employer->update($data)) {
            echo 'ko';
            exit();
        }
    } else {
        $employer = new Employer($_POST['user_id']);
        
        $new_password = generate_random_string_of(6);
        $hash = md5($new_password);
        $data['password'] = $hash;
        $data['registered_by'] = $employee->getId();
        $data['registered_through'] = 'M';
        $data['joined_on'] = $today;
        // $data['free_postings_left'] = $_POST['free_postings'];
        
        // $subscription_expire_on = $data['joined_on'];
        // if ($_POST['subscription_period'] > 0) {
        //     $subscription_expire_on = sql_date_add($data['joined_on'], $_POST['subscription_period'], 'month');
        // }
        
        if ($employer->create($data) === false) {
            echo 'ko';
            exit();
        }
        
        $lines = file(dirname(__FILE__). '/../private/mail/employer_welcome.txt');
        $message = '';
        foreach($lines as $line) {
            $message .= $line;
        }

        $message = str_replace('%company%', $_POST['name'], $message);
        $message = str_replace('%user_id%', $_POST['user_id'], $message);
        $message = str_replace('%temporary_password%', $new_password, $message);
        $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
        $message = str_replace('%root%', $GLOBALS['root'], $message);
        $subject = "Welcome To Yellow Elevator!";
        $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
        mail($_POST['email_addr'], $subject, $message, $headers);
        
        // $handle = fopen('/tmp/email_to_'. $_POST['email_addr']. '.txt', 'w');
        // fwrite($handle, 'Subject: '. $subject. "\n\n");
        // fwrite($handle, $message);
        // fclose($handle);
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'copy_fees') {
    $from_employer = new Employer($_POST['employer']);
    $to_employer = new Employer($_POST['id']);
    
    $fees = $from_employer->get_fees();
    
    if ($to_employer->create_fees($fees) === false) {
        echo 'ko';
        exit();
    }
    
    $criteria = array(
        'columns' => "working_months, payment_terms_days", 
        'match' => "id = '". $from_employer->getId(). "'", 
        'limit' => "1"
    );
    $result = $from_employer->find($criteria);
    $data = array();
    $data['working_months'] = $result[0]['working_months'];
    $data['payment_terms_days'] = $result[0]['payment_terms_days'];
    
    $to_employer->update($data);
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'delete_fee') {
    $employer = new Employer();
    if ($employer->deleteFee($_POST['id']) === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_fees') {
    $employer = new Employer($_POST['id']);
    $result = $employer->getFees();
    
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }
    
    if (!$result) {
        echo 'ko';
        exit();
    }
    
    foreach ($result as $i=>$row) {
        $result[$i]['salary_start'] = number_format($row['salary_start'], 2, '.' , ', ');
        $result[$i]['salary_end'] = number_format($row['salary_end'], 2, '.' , ', ');
    }
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('fees' => array('fee' => $result)));
    exit();
}

if ($_POST['action'] == 'save_fee') {
    $employer = new Employer($_POST['employer_id']);
    
    if ($_POST['id'] == '0') {
        $criteria = array(
            'columns' => 'COUNT(*) AS overlapped',
            'joins' => 'employer_fees ON employer_fees.employer = employers.id', 
            'match' => "employers.id = '". $_POST['employer_id']. "' AND 
                       ((salary_start = ". $_POST['salary_start']. " OR salary_end = ". $_POST['salary_start']. ") OR
                        (salary_start = ". $_POST['salary_end']. " OR salary_end = ". $_POST['salary_end']. ") OR 
                        (salary_start < ". $_POST['salary_start']. " AND (salary_end > ". $_POST['salary_start']. " OR salary_end = 0)) OR
                        (salary_start < ". $_POST['salary_end']. " AND (salary_end > ". $_POST['salary_end']. " OR salary_end = 0)))"
        );
        
        $result = $employer->find($criteria);
        if ($result[0]['overlapped'] != 0) {
            echo '-1';
            exit();
        }
    }
    
    $data = array();
    $data['guarantee_months'] = $_POST['guarantee_months'];
    $data['service_fee'] = $_POST['service_fee'];
    $data['reward_percentage'] = $_POST['reward_percentage'];
    $data['premier_fee'] = '0.00';
    
    if ($_POST['id'] == '0') {
        $data['salary_start'] = $_POST['salary_start'];
        $data['salary_end'] = $_POST['salary_end'];
        
        if (!$employer->createFee($data)) {
            echo 'ko';
            exit();
        }
    } else {
        $data['id'] = $_POST['id'];
        
        if (!$employer->updateFee($data)) {
            echo 'ko';
            exit();
        }
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'save_subscriptions') {
    $employer = new Employer($_POST['id']);
    
    if ($_POST['free_postings'] != 0) {
        if ($employer->addFreeJobPosting($_POST['free_postings']) === false) {
            echo '-1';
            exit();
        }
    }
    
    if ($_POST['paid_postings'] != 0) {
        if ($employer->addPaidJobPosting($_POST['paid_postings']) === false) {
            echo '-2';
            exit();
        }
    }
    
    if ($_POST['subscription_period'] > 0) {
        if ($employer->extendSubscription($_POST['subscription_period']) === false) {
            echo '-3';
            exit();
        }
    }
    
    if (send_invoice($employer, $_POST['paid_postings'], $_POST['subscription_period']) === false) {
        echo '-4';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_jobs') {
    $employer = new Employer($_POST['id']);
    
    $result = $employer->getJobs($_POST['order']);
    
    if (is_null($result) || empty($result)) {
        echo '0';
        exit();
    }
    
    if ($result === false) {
        echo 'ko';
        exit();
    }
    
    foreach ($result as $i=>$row) {
        $result[$i]['title'] = htmlspecialchars_decode(stripslashes($row['title']));
    }
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('jobs' => array('job' => $result)));
    exit();
}

if ($_POST['action'] == 'delete_job') {
    $job = new Job($_POST['id']);
    if ($job->delete() === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_job') {
    $job = new Job($_POST['id']);
    $result = $job->get();
    
    $result[0]['title'] = htmlspecialchars_decode(stripslashes($result[0]['title']));
    $result[0]['description'] = htmlspecialchars_decode(stripslashes($result[0]['description']));
    $result[0]['description'] = str_replace("<br/>", "\r", $result[0]['description']);
    
    $criteria = array(
        'columns' => "job_index.state",
        'joins' => "job_index ON job_index.job = jobs.id",
        'match' => "jobs.id = ". $_POST['id'],
        'limit' => "1"
    );
    $tmp = $job->find($criteria);
    $result[0]['state'] = $tmp[0]['state'];
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('job' => $result));
    exit();
}

if ($_POST['action'] == 'save_job') {
    $employer = new Employer($_POST['employer']);
    
    // check whether employer can use free job posting?
    if ($employer->hasFreeJobPostings() === false) {
        // check whether employer has paid job postings?
        if ($employer->hasPaidJobPostings() === false) {
            // check whether subscription has expired
            $result = $employer->getSubscriptionsDetails();
            if ($result[0]['expired'] < 0 || $result[0]['subscription_suspended'] != '0') {
                echo '-2';
                exit();
            }
        } else {
            $employer->usedPaidJobPosting();
        }
    } else {
        $employer->usedFreeJobPosting();
    }
    
    $id = $_POST['id'];
    $job = '';
    
    if ($id <= 0) {
        $job = new Job();
    } else {
        $job = new Job($id);
    }
    
    $data = array();
    $data['employer'] = $_POST['employer'];
    $data['industry'] = $_POST['industry'];
    $data['country'] = $_POST['country'];
    $data['state'] = $_POST['state'];
    $data['salary'] = $_POST['salary'];
    $data['salary_end'] = $_POST['salary_end'];
    $data['salary_negotiable'] = $_POST['salary_negotiable'];
    $data['title'] = $_POST['title'];
    $data['description'] = str_replace(array("\r\n", "\r", "\n"), '<br/>', $_POST['description']);
    $data['acceptable_resume_type'] = 'A';
    $data['closed'] = 'N';
    
    $new_id = 0;
    if ($id <= 0) {
        $data['created_on'] = now();
        $data['expire_on'] = sql_date_add($data['created_on'], 30, 'day');
        if (($new_id = $job->create($data)) === false) {
            echo 'ko';
            exit();
        }
    } else {
        if ($job->update($data) == false) {
            echo 'ko';
            exit();
        }
    }
    
    $data = array();
    $salary_end = $_POST['salary_end'];
    if ($salary_end <= 0) {
        $salary_end = $_POST['salary'];
        $data['salary_end'] = 'NULL';
    }
    $data['potential_reward'] = $job->getPotentialReward();
    
    // Check whether employer's account is ready.
    if ($data['potential_reward'] <= 0) {
        echo '-1';
        exit();
    }
    
    $job->update($data);
    
    $tmp = explode('/', $GLOBALS['root']);
    $is_test_site = false;
    foreach ($tmp as $t) {
        if ($t == 'yel') {
            $is_test_site = true;
            break;
        }
    }
    
    // Tweet about this job, if it is new
    if ($new_id > 0 && !$is_test_site) {
        $employer = htmlspecialchars_decode(stripslashes($employer->getName()));
        $url = $GLOBALS['protocol']. '://'. $GLOBALS['root']. '/job/'. $new_id;
        $status = $data['title']. ' ('. $employer. ') - '. $url;
        $twitter_username = 'yellowelevator';
        $twitter_password = 'yellow123456';
        $tweetUrl = 'http://www.twitter.com/statuses/update.xml';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $tweetUrl);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "status=". $status);
        curl_setopt($curl, CURLOPT_USERPWD, $twitter_username. ':'. $twitter_password);

        $result = curl_exec($curl);
        
        // Don't bother to check because if Twitter fails, it doesn't matter.
        //$resultArray = curl_getinfo($curl);
        //if ($resultArray['http_code'] != 200) {
        //    echo 'ko';
        //}

        curl_close($curl);
    }
    
    echo 'ok';
    exit();
    
}

if ($_POST['action'] == 'extend_job') {
    $employer = new Employer($_POST['employer']);
    
    // check whether employer can use free job posting?
    if ($employer->hasFreeJobPostings() === false) {
        // check whether employer has paid job postings?
        if ($employer->hasPaidJobPostings() === false) {
            // check whether subscription has expired
            $result = $employer->getSubscriptionsDetails();
            if ($result[0]['expired'] < 0 || $result[0]['subscription_suspended'] != '0') {
                echo '-2';
                exit();
            }
        } else {
            $employer->usedPaidJobPosting();
        }
    } else {
        $employer->usedFreeJobPosting();
    }
    
    $job = new Job($_POST['id']);
    if ($job->extend() === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'preview_job_desc') {
    $_POST['desc'] = str_replace(array("\r\n", "\r", "\n"), '<br/>', $_POST['desc']);
    echo format_job_description($_POST['desc']);
    exit();
}

if ($_POST['action'] == 'enable_ye_connect') {
    $data = array();
    $data['is_ye_connect_enabled'] = $_POST['enabled'];
    
    $employer = new Employer($_POST['id']);
    if (!$employer->update($data)) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}
?>