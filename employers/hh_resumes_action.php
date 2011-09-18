<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";
require_once dirname(__FILE__). "/general_invoice.php";
require_once dirname(__FILE__). "/credit_note.php";

session_start();

if (isset($_GET['id']) && isset($_GET['hash'])) {
    // download resume
    $referral = new HeadhunterReferral($_GET['id']);
    $file_info = $referral->getFileInfo();
    $file = $GLOBALS['headhunter_resume_dir']. "/". $_GET['id']. ".". $_GET['hash'];
    if (file_exists($file)) {
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Expires: -1');
        header('Content-Description: File Transfer');
        header('Content-Length: ' . $file_info['file_size']);
        header('Content-Disposition: attachment; filename="' . $file_info['file_name'].'"');
        header('Content-type: '. $file_info['file_type']);
        ob_clean();
        flush();
        readfile($file);
    } else {
        redirect_to('hh_resumes.php');
    }
    exit();
}


if (!isset($_POST['id'])) {
    echo "ko";
    exit();
    //redirect_to('login.php');
}

$xml_dom = new XMLDOM();

if (!isset($_POST['action'])) {
    redirect_to('hh_resumes.php');
}

if ($_POST['action'] == 'get_referred_jobs') {
    $order_by = 'num_referrals desc';

    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $referral = new HeadhunterReferral();
    $employer = new Employer($_POST['id']);
    
    $criteria = array(
        'columns' => "industries.industry, jobs.id, jobs.title, 
                      COUNT(headhunter_referrals.id) AS num_referrals, 
                      DATE_FORMAT(jobs.expire_on, '%e %b, %Y') AS formatted_expire_on, 
                      jobs.description", 
        'joins' => 'jobs ON jobs.id = headhunter_referrals.job, 
                    industries ON industries.id = jobs.industry', 
        'match' => "jobs.employer = '". $employer->getId(). "' AND 
                    headhunter_referrals.employer_rejected_on IS NULL AND 
                    headhunter_referrals.employer_removed_on IS NULL", 
        'group' => 'headhunter_referrals.job',
        'order' => 'num_referrals DESC'
    );
    
    $result = $referral->find($criteria);
    if ($result === false) {
        echo 'ko';
        exit();
    }
    
    if (is_null($result) || empty($result)) {
        echo '0';
        exit();
    }
    foreach ($result as $i=>$row) {
        $result[$i]['description'] = htmlspecialchars_decode(desanitize($row['description']));
    }
    
    $response = array('referrals' => array('referral' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_resumes') {
    $order_by = 'headhunter_referrals.referred_on desc';
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $filter_by = '';
    if (isset($_POST['filter_by'])) {
        if (!empty($_POST['filter_by'])) {
            $filter_by = "AND ";
            switch ($_POST['filter_by']) {
                case 'no_star':
                    $filter_by .= "headhunter_referrals.rating <= 0";
                    break;
                case '1_star':
                    $filter_by .= "headhunter_referrals.rating = 1";
                    break;
                case '2_star':
                    $filter_by .= "headhunter_referrals.rating = 2";
                    break;
                case '3_star':
                    $filter_by .= "headhunter_referrals.rating = 3";
                    break;
                case '4_star':
                    $filter_by .= "headhunter_referrals.rating = 4";
                    break;
                case '5_star':
                    $filter_by .= "headhunter_referrals.rating = 5";
                    break;
                case 'hired':
                    $filter_by .= "headhunter_referrals.employed_on IS NOT NULL";
                    break;
            }
        }
    }
    
    $criteria = array(
        "columns" => "headhunter_referrals.*,
                      DATE_FORMAT(referred_on, '%e %b, %Y') AS formatted_referred_on, 
                      DATE_FORMAT(employed_on, '%e %b, %Y') AS formatted_employed_on, 
                      DATE_FORMAT(employer_agreed_on, '%e %b, %Y') AS formatted_agreed_on,
                      DATE_FORMAT(interview_scheduled_on, '%e %b, %Y') AS formatted_scheduled_on", 
        "match" => "headhunter_referrals.job = ". $_POST['id']. " AND 
                    employer_removed_on IS NULL AND 
                    employer_rejected_on IS NULL ". $filter_by, 
        "order" => $order_by
    );
    
    $referral = new HeadhunterReferral();
    $result = $referral->find($criteria);
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }
    
    if ($result === false) {
        echo 'ko';
        exit();
    }
    
    foreach ($result as $i=>$row) {
        $cover_note = htmlspecialchars_decode(stripslashes($row['cover_note']));
        $cover_note = str_replace('<br/>', "\r\n", $cover_note);
        $result[$i]['cover_note'] = $cover_note;
    }
    
    $response = array('applications' => array('application' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_job_description') {
    $job = new Job($_POST['id']);
    $result = $job->get();
    $response = array(
        'job' => array(
            'title' => $result[0]['title'],
            'description' => htmlspecialchars_decode(desanitize($result[0]['description']))
        )
    );
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'confirm_employed') {
    $work_commence_on = $_POST['work_commence_on'];
    
    // 1. Update the referral to employed
    $referral = new HeadhunterReferral($_POST['id']);
    $employer = new Employer($_POST['employer']);
    
    $result = $referral->get();
    $member = new Member($result[0]['member']);
    
    $job = array(
        'id' => $_POST['job_id'],
        'title' => $_POST['job']
    );
    
    $salary = $_POST['salary'];
    $total_reward = $referral->calculateRewardFrom($salary);
    $total_token_reward = $total_reward * 0.30;
    $total_reward_to_referrer = $total_reward - $total_token_reward;
    
    $data = array();
    $data['employed_on'] = now();
    $data['salary_per_annum'] = $salary;
    $data['total_reward'] = $total_reward_to_referrer;
    $data['total_token_reward'] = $total_token_reward;
    
    // 1.1 Check whether the reward is 0.00 or NULL. If it is, then the employer account is not ready. 
    if ($data['total_reward'] <= 0.00) {
        echo '-1';
        exit();
    }
    
    if ($referral->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    // 2. Generate an Reference Invoice
    // 2.1 Get all the fees, discounts and extras and calculate accordingly.
    $fees = $employer->getFees();
    $payment_terms_days = $employer->getPaymentTermsInDays();
    
    $subtotal = $discount = $extra_charges = 0.00;
    foreach($fees as $fee) {
        if ($salary >= $fee['salary_start'] && 
            ($salary <= $fee['salary_end'] || $fee['salary_end'] == 0)) {
            $discount = -($salary * ($fee['discount'] / 100.00));
            $subtotal = ($salary * ($fee['service_fee'] / 100.00));
            break;
        }
    }
    $new_total_fee = $subtotal + $discount;
    
    // 2.2 Generate the invoice
    $issued_on = date('j M, Y');
    $data = array();
    $data['issued_on'] = $issued_on;
    $data['type'] = 'R';
    $data['employer'] = $employer->getId();
    $data['payable_by'] = sql_date_add($data['issued_on'], $payment_terms_days, 'day');
    
    $invoice = Invoice::create($data);
    if (!$invoice) {
        echo 'ko';
        exit();
    }
    
    $referral_desc = 'Reference fee for ['. $job['id']. '] '. $job['title'];
    
    $item_added = Invoice::addItem($invoice, $subtotal, $referral->getId(), $referral_desc);
    if (!$item_added) {
        echo "ko";
        exit();
    }
    
    $item_added = Invoice::addItem($invoice, $discount, $referral->getId(), 'Discount');
    if (!$item_added) {
        echo "ko";
        exit();
    }

    $item_added = Invoice::addItem($invoice, $extra_charges, $referral->getId(), 'Extra charges');
    if (!$item_added) {
        echo "ko";
        exit();
    }
    
    // generate and send invoice
    $filename = generate_random_string_of(8). '.'. generate_random_string_of(8);
    $branch = $employer->getAssociatedBranch();
    $sales = 'sales.'. strtolower($branch[0]['country']). '@yellowelevator.com';
    $currency = $branch[0]['currency'];
    
    $items = Invoice::getItems($invoice);
    $amount_payable = 0.00;
    foreach($items as $i=>$item) {
        $amount_payable += $item['amount'];
        $items[$i]['amount'] = number_format($item['amount'], 2, '.', ', ');
    }
    $amount_payable = number_format($amount_payable, 2, '.', ', ');
    $invoice_or_receipt = 'Invoice';
    
    $pdf = new GeneralInvoice();
    $pdf->AliasNbPages();
    $pdf->SetAuthor('Yellow Elevator. This invoice was automatically generated. Signature is not required.');
    $pdf->SetTitle($GLOBALS['COMPANYNAME']. ' - Invoice '. pad($invoice, 11, '0'));
    $pdf->SetInvoiceType('R', $invoice_or_receipt);
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
    $pdf->Cell(33, 5, $issued_on, 1,0,'C');
    $pdf->Cell(1);
    $pdf->Cell(33, 5, format_date($data['payable_by']),1,0,'C');
    $pdf->Cell(1);
    $pdf->Cell(0, 5, $amount_payable,1,0,'C');
    $pdf->Ln(6);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(60, 5, "User ID",1,0,'C',1);
    $pdf->Cell(1);
    $pdf->Cell(0, 5, "Employer Name",1,0,'C',1);
    $pdf->Ln(6);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(60, 5, $employer->getId(),1,0,'C');
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
    $pdf->Output($GLOBALS['data_path']. '/general_invoices/'. $filename. '.pdf', 'F');
    
    $attachment = chunk_split(base64_encode(file_get_contents($GLOBALS['data_path']. '/general_invoices/'. $filename. '.pdf')));

    $subject = "Notice of Invoice ". pad($invoice, 11, '0');
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    $headers .= 'Bcc: '. $sales. "\n";
    $headers .= 'MIME-Version: 1.0'. "\n";
    $headers .= 'Content-Type: multipart/mixed; boundary="yel_mail_sep_'. $filename. '";'. "\n\n";

    $body = '--yel_mail_sep_'. $filename. "\n";
    $body .= 'Content-Type: multipart/alternative; boundary="yel_mail_sep_alt_'. $filename. '"'. "\n";
    $body .= '--yel_mail_sep_alt_'. $filename. "\n";
    $body .= 'Content-Type: text/plain; charset="iso-8859-1"'. "\n";
    $body .= 'Content-Transfer-Encoding: 7bit"'. "\n";
    
    $mail_lines = file('../private/mail/employer_general_invoice.txt');
    $message = '';
    foreach ($mail_lines as $line) {
        $message .= $line;
    }

    $message = str_replace('%company%', $employer->getName(), $message);
    $message = str_replace('%invoice%', pad($invoice, 11, '0'), $message);
    $message = str_replace('%issued_on%', $issued_on, $message);
    $message = str_replace('%payable_by%', format_date($data['payable_by']), $message);
    $message = str_replace('%amount%', $amount_payable, $message);
    $message = str_replace('%currency%', $currency, $message);
    
    $body .= $message. "\n";
    $body .= '--yel_mail_sep_alt_'. $filename. "--\n\n";
    $body .= '--yel_mail_sep_'. $filename. "\n";
    $body .= 'Content-Type: application/pdf; name="yel_invoice_'. pad($invoice, 11, '0'). '.pdf"'. "\n";
    $body .= 'Content-Transfer-Encoding: base64'. "\n";
    $body .= 'Content-Disposition: attachment'. "\n";
    $body .= $attachment. "\n";
    $body .= '--yel_mail_sep_'. $filename. "--\n\n";
    mail($employer->getEmailAddress(), $subject, $body, $headers);
    
    // $handle = fopen('/tmp/email_to_'. $employer->getEmailAddress(). '.txt', 'w');
    // fwrite($handle, 'To: '. $employer->getEmailAddress(). "\n\n");
    // fwrite($handle, 'Header: '. $headers. "\n\n");
    // fwrite($handle, 'Subject: '. $subject. "\n\n");
    // fwrite($handle, $body);
    
    unlink($GLOBALS['data_path']. '/general_invoice/'. $filename. '.pdf');
    
    // 3. Send a notification
    $mail_lines = file('../private/mail/member_reward.txt');
    $message = '';
    foreach ($mail_lines as $line) {
        $message .= $line;
    }

    $message = str_replace('%member_name%', $member->getFullName(), $message);
    $message = str_replace('%referee_name%', $candidate->getFullName(), $message);
    $message = str_replace('%employer%', $employer->getName(), $message);
    $message = str_replace('%job_title%', $job['title'], $message);
    $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
    $message = str_replace('%root%', $GLOBALS['root'], $message);
    $subject = "Your recommendation was successfully employed!";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($member->getId(), $subject, $message, $headers);
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'reject_resume') {
    $data = array();
    $data['employer_rejected_on'] = date('Y-m-d H:i:s');
    
    $referral = new HeadhunterReferrals($_POST['id']);
    $referral->update($data);
    
    exit();
}

if ($_POST['action'] == 'schedule_interview') {
    
}
?>