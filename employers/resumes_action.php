<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";
require_once dirname(__FILE__). "/credit_note.php";

session_start();

if (!isset($_POST['id'])) {
    echo "ko";
    exit();
    //redirect_to('login.php');
}

$xml_dom = new XMLDOM();

if (!isset($_POST['action'])) {
    redirect_to('resumes.php');
}

if ($_POST['action'] == 'get_referred_jobs') {
    $order_by = 'num_referrals desc';

    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $referral = new Referral();
    $employer = new Employer($_POST['id']);
    
    $criteria = array(
        'columns' => "industries.industry, jobs.id, jobs.title, COUNT(referrals.id) AS num_referrals, 
                      DATE_FORMAT(jobs.expire_on, '%e %b, %Y') AS formatted_expire_on, 
                      jobs.description", 
        'joins' => 'jobs ON jobs.id = referrals.job, 
                    industries ON industries.id = jobs.industry', 
        'match' => "jobs.employer = '". $employer->getId(). "' AND 
                    need_approval = 'N' AND 
                    (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
                    (referrals.member_confirmed_on IS NOT NULL AND referrals.member_confirmed_on <> '0000-00-00 00:00:00') AND 
                    referrals.employer_removed_on IS NULL AND 
                    (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00')", 
        'group' => 'referrals.job',
        'order' => $order_by
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
        $result[$i]['new_referrals_count'] = '0';
    }
    
    $criteria = array(
        'columns' => 'jobs.id, COUNT(referrals.id) AS num_new_referrals', 
        'joins' => 'jobs ON jobs.id = referrals.job, 
                    resumes ON resumes.id = referrals.resume', 
        'match' => "jobs.employer = '". $employer->getId(). "' AND 
                    (resumes.deleted = 'N' AND resumes.private = 'N') AND 
                    (referrals.employer_agreed_terms_on IS NULL OR referrals.employer_agreed_terms_on = '0000-00-00 00:00:00') AND 
                    (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
                    (referrals.member_confirmed_on IS NOT NULL AND referrals.member_confirmed_on <> '0000-00-00 00:00:00') AND 
                    (referrals.employed_on IS NULL OR referrals.employed_on = '0000-00-00 00:00:00') AND 
                    referrals.employer_removed_on IS NULL AND 
                    (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00')", 
        'group' => 'referrals.job'
    );
    
    $new_referrals = $referral->find($criteria);
    if (count($new_referrals) > 0 && !is_null($new_referrals)) {
        foreach ($new_referrals as $new_referral) {
            foreach ($result as $i=>$row) {
                if ($row['id'] == $new_referral['id']) {
                    $result[$i]['new_referrals_count'] = $new_referral['num_new_referrals'];
                    break;
                }
            }
        }
    }
    
    $response = array('referrals' => array('referral' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'get_resumes') {
    $order_by = 'referrals.referred_on desc';
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $filter_by = '';
    if (isset($_POST['filter_by'])) {
        if (!empty($_POST['filter_by'])) {
            $filter_by = "AND ";
            switch ($_POST['filter_by']) {
                case 'no_star':
                    $filter_by .= "referrals.rating <= 0";
                    break;
                case '1_star':
                    $filter_by .= "referrals.rating = 1";
                    break;
                case '2_star':
                    $filter_by .= "referrals.rating = 2";
                    break;
                case '3_star':
                    $filter_by .= "referrals.rating = 3";
                    break;
                case '4_star':
                    $filter_by .= "referrals.rating = 4";
                    break;
                case '5_star':
                    $filter_by .= "referrals.rating = 5";
                    break;
                case 'hired':
                    $filter_by .= "(referrals.employed_on IS NOT NULL AND referrals.employed_on <> '0000-00-00 00:00:00')";
                    break;
            }
        }
    }
    
    $criteria = array(
        "columns" => "CONCAT(members.lastname,', ', members.firstname) AS referrer, 
                      CONCAT(referees.lastname,', ', referees.firstname) AS candidate,
                      DATE_FORMAT(referrals.member_confirmed_on, '%e %b, %Y') AS formatted_referred_on, 
                      DATE_FORMAT(referrals.referee_acknowledged_on, '%e %b, %Y') AS formatted_acknowledged_on, 
                      DATE_FORMAT(referrals.employed_on, '%e %b, %Y') AS formatted_employed_on, 
                      DATE_FORMAT(referrals.employer_rejected_on, '%e %b, %Y') AS formatted_employer_rejected_on, 
                      referrals.resume, referrals.id, referrals.testimony, referrals.shortlisted_on,
                      referrals.employer_agreed_terms_on, members.email_addr AS referrer_email_addr, 
                      referees.email_addr AS candidate_email_addr, members.phone_num AS referrer_phone_num, 
                      referees.phone_num AS candidate_phone_num, 
                      referrals.employer_remarks", 
        "joins" => "members ON members.email_addr = referrals.member, 
                    members AS referees ON referees.email_addr = referrals.referee, 
                    resumes ON resumes.id = referrals.resume", 
        "match" => "referrals.job = ". $_POST['id']. " AND 
                    referrals.need_approval = 'N' AND 
                    (resumes.deleted = 'N' AND resumes.private = 'N') AND 
                    (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
                    (referrals.member_confirmed_on IS NOT NULL AND referrals.member_confirmed_on <> '0000-00-00 00:00:00') AND 
                    referrals.employer_removed_on IS NULL AND 
                    (referrals.replacement_authorized_on IS NULL OR referrals.replacement_authorized_on = '0000-00-00 00:00:00') ". $filter_by, 
        "order" => $order_by
    );
    
    $referral = new Referral();
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
        $result[$i]['testimony'] = htmlspecialchars_decode(desanitize($row['testimony']));
        $result[$i]['employer_remarks'] = stripslashes(desanitize($row['employer_remarks']));
        if (is_null($row['employer_agreed_terms_on']) || 
            $row['employer_agreed_terms_on'] == '0000-00-00 00:00:00') {
            $result[$i]['employer_agreed_terms_on'] = '-1';
        }
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

if ($_POST['action'] == 'agreed_terms') {
    $data = array();
    $data['employer_agreed_terms_on'] = now();
    
    $referral = new Referral($_POST['id']);
    if ($referral->update($data) === true) {
        $criteria = array(
            'columns' => "referrals.referee AS email, jobs.title AS job_title, 
                          employers.name AS employer_name, resumes.name AS resume_name, 
                          members.phone_num, 
                          CONCAT(members.lastname,', ', members.firstname) AS referee", 
            'joins' => "members ON members.email_addr = referrals.referee, 
                        jobs ON jobs.id = referrals.job, 
                        employers ON employers.id = jobs.employer, 
                        resumes ON resumes.id = referrals.resume", 
            'match' => "referrals.id = ". $_POST['id'], 
            'limit' => "1"
        );
        
        $result = $referral->find($criteria);
        $referee_email = $result[0]['email'];
        $referee_name = $result[0]['referee'];
        $employer = $result[0]['employer_name'];
        $job = $result[0]['job_title'];
        $resume = $result[0]['resume_name'];
        
        $lines = file(dirname(__FILE__). '/../private/mail/employer_viewed_resume.txt');
        $message = '';
        foreach($lines as $line) {
           $message .= $line;
        }
        
        $message = str_replace('%referee%', htmlspecialchars_decode(desanitize($referee_name)), $message);
        $message = str_replace('%employer%', htmlspecialchars_decode(desanitize($employer)), $message);
        $message = str_replace('%job%', htmlspecialchars_decode(desanitize($job)), $message);
        $message = str_replace('%resume%', htmlspecialchars_decode(desanitize($resume)), $message);
        $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
        $message = str_replace('%root%', $GLOBALS['root'], $message);
        $subject = desanitize($employer). " has viewed your resume";
        $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
        //mail($referee_email, $subject, $message, $headers);
        
        $handle = fopen('/tmp/email_to_'. $referee_email. '.txt', 'w');
        fwrite($handle, 'Subject: '. $subject. "\n\n");
        fwrite($handle, $message);
        fclose($handle);
        
        echo 'ok';
        exit();
    }
    
    echo 'ko';
    exit();
}

if ($_POST['action'] == 'save_remarks') {
    if (!empty($_POST['remarks'])) {
        $referral = new Referral($_POST['id']);
        $data = array('employer_remarks' => sanitize($_POST['remarks']));
        
        if ($referral->update($data) === false) {
            echo 'ko';
        } else {
            echo 'ok';
        }
    }
    exit();
}

if ($_POST['action'] == 'notify_candidate') {
    $job = new Job();
    $criteria = array(
        'columns' => 'employers.name AS employer', 
        'joins' => 'employers ON employers.id = jobs.employer',
        'match' => 'jobs.id = '. $_POST['job_id'], 
        'limit' => '1'
    );
    $result = $job->find($criteria);
    $employer = $result[0]['employer'];
    
    $news = 'Congratulations! Your application has been shortlisted. We will contact you for further arrangements.';
    if ($_POST['is_good'] == '0') {
        $news = 'We are sorry that your application did not meet our requirements, and your resume has been kept for future use.';
    }
    
    $lines = file(dirname(__FILE__). '/../private/mail/employer_notify_candidate.txt');
    $message = '';
    foreach($lines as $line) {
       $message .= $line;
    }
    
    $message = str_replace('%candidate%', htmlspecialchars_decode(desanitize($_POST['candidate_name'])), $message);
    $message = str_replace('%employer%', desanitize($employer), $message);
    $message = str_replace('%job%', desanitize($_POST['job']), $message);
    $message = str_replace('%news%', $news, $message);
    $message = str_replace('%message%', desanitize($_POST['message']), $message);
    $subject = "A message from ". desanitize($employer);
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    //mail($_POST['candidate_email_addr'], $subject, $message, $headers);
    
    $handle = fopen('/tmp/email_to_'. $_POST['candidate_email_addr']. '.txt', 'w');
    fwrite($handle, 'Subject: '. $subject. "\n\n");
    fwrite($handle, $message);
    fclose($handle);
    
    exit();
}

if ($_POST['action'] == 'confirm_employed') {
    $work_commence_on = $_POST['work_commence_on'];
    $is_replacement = false;
    $is_free_replacement = false;
    $previous_referral = '0';
    $previous_invoice = '0';
    
    // 1. Update the referral to employed
    $referral = new Referral($_POST['id']);
    $employer = new Employer($_POST['employer']);
    $candidate = new Member($_POST['candidate_email_addr']);
    
    // get the referrer
    $criteria = array(
        'columns' => 'member', 
        'match' => 'id = '. $referral->getId(),
        'limit' => '1'
    );
    $result = $referral->find($criteria);
    $member = new Member($result[0]['member']);
    
    $job = array(
        'id' => $_POST['job_id'],
        'title' => $_POST['job']
    );
    
    $salary = $_POST['salary'];
    $irc_id = ($member->isIRC()) ? $member->getId() : NULL;
    $total_reward = $referral->calculateRewardFrom($salary, $irc_id);
    $total_token_reward = $total_reward * 0.30;
    $total_reward_to_referrer = $total_reward - $total_token_reward;
    
    $data = array();
    $data['employed_on'] = now();
    $data['work_commence_on'] = $work_commence_on;
    $data['salary_per_annum'] = $salary;
    $data['total_reward'] = $total_reward_to_referrer;
    $data['total_token_reward'] = $total_token_reward;
    $data['guarantee_expire_on'] = $referral->getGuaranteeExpiryDateWith($salary, $work_commence_on);
    
    // 1.1 Check whether the reward is 0.00 or NULL. If it is, then the employer account is not ready. 
    if ($data['total_reward'] <= 0.00 || 
        $data['guarantee_expire_on'] == '0000-00-00 00:00:00' || 
        is_null($data['guarantee_expire_on'])) {
        echo '-1';
        exit();
    }
    
    if ($referral->update($data) === false) {
        echo 'ko';
        exit();
    }
    
    // 2. Generate an Reference Invoice
    // 2.1 Check whether this job is a replacement for a previous failed referral. 
    $criteria = array(
        'columns' => 'id',
        'match' => "job = ". $job['id']. " AND 
                    (replacement_authorized_on IS NOT NULL AND replacement_authorized_on <> '0000-00-00 00:00:00') AND 
                    (replaced_on IS NULL OR replaced_on = '0000-00-00 00:00:00') AND 
                    replaced_referral IS NULL", 
        'limit' => '1'
    );
    $result = $referral->find($criteria);
    
    if (count($result) > 0 && !is_null($result)) {
        $is_replacement = true;
        $previous_referral = $result[0]['id'];
    }
    
    // 2.2 Get all the fees, discounts and extras and calculate accordingly.
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
    
    // 2.2.1 If this is a replacement, re-calculate accordingly by taking the previously invoiced amount.
    $credit_amount = 0;
    if ($is_replacement) {
        // 2.2.1a If it is a replacement, get the previously invoiced amount.
        $criteria = array(
            'columns' => 'invoices.id, SUM(invoice_items.amount) AS amount_payable', 
            'joins' => 'invoice_items ON invoice_items.invoice = invoices.id', 
            'match' => "invoices.type ='R' AND 
                        invoice_items.item = ". $previous_referral, 
            'group' => 'invoices.id'
        );
        $result = Invoice::find($query);
        $amount_payable = $result[0]['amount_payable'];
        $previous_invoice = $result[0]['id'];
        
        // 2.2.1b Get the difference.
        $amount_difference = $new_total_fee - $amount_payable;
        
        // 2.2.1c If the difference in fees is more than zero, then use the amount difference. 
        if (round($amount_difference, 2) <= 0) {
            $subtotal = $discount = $extra_charges = 0.00;
            $is_free_replacement = true;
            $credit_amount = abs($amount_difference);
        } else {
            $discount = $extra_charges = 0.00;
            $subtotal = $amount_difference;
        }
    }
    
    // 2.3 Generate the invoice
    $issued_on = date('j M, Y');
    $data = array();
    $data['issued_on'] = $issued_on;
    $data['type'] = 'R';
    $data['employer'] = $employer->getId();
    $data['payable_by'] = sql_date_add($data['issued_on'], $payment_terms_days, 'day');
    
    if ($is_free_replacement) {
        $data['paid_on'] = $data['issued_on'];
        $data['paid_through'] = 'CSH';
        $data['paid_id'] = 'FREE_REPLACEMENT';
    }
    
    $invoice = Invoice::create($data);
    if (!$invoice) {
        echo 'ko';
        exit();
    }
    
    $referral_desc = 'Reference fee for ['. $job['id']. '] '. $job['title'];
    if ($is_free_replacement) {
        $referral_desc = 'Free replacement for Invoice: '. pad($previous_invoice, 11, '0');
    } 
    
    if ($is_replacement && !$is_free_replacement) {
        $referral_desc = 'Replacement fee for Invoice: '. pad($previous_invoice, 11, '0');
    }
    
    $item_added = Invoice::addItem($invoice, $subtotal, $referral->getId(), $referral_desc);
    if (!$item_added) {
        echo "ko";
        exit();
    }
    
    if (!$is_free_replacement) {
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
    } else {
        if ($credit_amount > 0) {
            $credit_note_desc = 'Refund of balance for Invoice: '. pad($previous_invoice, 11, '0');
            $filename = generate_random_string_of(8). '.'. generate_random_string_of(8);
            $expire_on = sql_date_add($issued_on, 30, 'day');
            
            Invoice::accompanyCreditNoteWith($previous_invoice, $invoice, $issued_on, $credit_amount);
            
            $branch = $employer->getBranch();
            $sales = 'sales.'. strtolower($branch[0]['country']). '@yellowelevator.com';
            $branch[0]['address'] = str_replace(array("\r\n", "\r"), "\n", $branch[0]['address']);
            $branch['address_lines'] = explode("\n", $branch[0]['address']);
            $currency = Currency::getSymbolFromCountryCode($branch[0]['country']);
            
            $pdf = new CreditNote();
            $pdf->AliasNbPages();
            $pdf->SetAuthor('Yellow Elevator. This credit note was automatically generated. Signature is not required.');
            $pdf->SetTitle($GLOBALS['COMPANYNAME']. ' - Credit Note '. pad($invoice, 11, '0'));
            $pdf->SetRefundAmount($credit_amount);
            $pdf->SetDescription($credit_note_desc);
            $pdf->SetCurrency($currency);
            $pdf->SetBranch($branch);
            $pdf->AddPage();
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFillColor(54, 54, 54);
            $pdf->Cell(60, 5, "Credit Note Number",1,0,'C',1);
            $pdf->Cell(1);
            $pdf->Cell(33, 5, "Issuance Date",1,0,'C',1);
            $pdf->Cell(1);
            $pdf->Cell(33, 5, "Creditable By",1,0,'C',1);
            $pdf->Cell(1);
            $pdf->Cell(0, 5, "Amount Creditable (". $currency. ")",1,0,'C',1);
            $pdf->Ln(6);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(60, 5, pad($invoice, 11, '0'),1,0,'C');
            $pdf->Cell(1);
            $pdf->Cell(33, 5, $issued_on,1,0,'C');
            $pdf->Cell(1);
            $pdf->Cell(33, 5, $expire_on,1,0,'C');
            $pdf->Cell(1);
            $pdf->Cell(0, 5, number_format($credit_amount, 2, '.', ','),1,0,'C');
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
            $pdf->FancyTable($table_header);
            
            $pdf->Ln(13);
            $pdf->SetFont('','I');
            $pdf->Cell(0, 0, "This credit note was automatically generated. Signature is not required.", 0, 0, 'C');
            $pdf->Ln(6);
            $pdf->Cell(0, 5, "Refund Notice",'LTR',0,'C');
            $pdf->Ln();
            $pdf->Cell(0, 5, "- Refund will be made payable to ". $employer->getName(). ". ", 'LR', 0, 'C');
            $pdf->Ln();
            $pdf->Cell(0, 5, "- To facilitate the refund process, please inform us of any discrepancies.", 'LBR', 0, 'C');
            $pdf->Ln(10);
            $pdf->Cell(0, 0, "E. & O. E.", 0, 0, 'C');
            $pdf->Close();
            $pdf->Output($GLOBALS['data_path']. '/credit_notes/'. $filename. '.pdf', 'F');
            
            $attachment = chunk_split(base64_encode(file_get_contents($GLOBALS['data_path']. '/credit_notes/'. $filename. '.pdf')));

            $subject = "Balance Refund Notice of Invoice ". pad($previous_invoice, 11, '0');
            $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
            $headers .= 'Bcc: '. $sales. "\n";
            $headers .= 'MIME-Version: 1.0'. "\n";
            $headers .= 'Content-Type: multipart/mixed; boundary="yel_mail_sep_'. $filename. '";'. "\n\n";

            $body = '--yel_mail_sep_'. $filename. "\n";
            $body .= 'Content-Type: multipart/alternative; boundary="yel_mail_sep_alt_'. $filename. '"'. "\n";
            $body .= '--yel_mail_sep_alt_'. $filename. "\n";
            $body .= 'Content-Type: text/plain; charset="iso-8859-1"'. "\n";
            $body .= 'Content-Transfer-Encoding: 7bit"'. "\n";
            
            $mail_lines = file('../private/mail/employer_credit_note.txt');
            $message = '';
            foreach ($mail_lines as $line) {
                $message .= $line;
            }

            $message = str_replace('%company%', $employer->getName(), $message);
            $message = str_replace('%previous_invoice%', pad($previous_invoice, 11, '0'), $message);
            $message = str_replace('%new_invoice%', pad($invoice, 11, '0'), $message);
            $message = str_replace('%job_title%', $job_title, $message);
            
            $body .= $message. "\n";
            $body .= '--yel_mail_sep_alt_'. $filename. "--\n\n";
            $body .= '--yel_mail_sep_'. $filename. "\n";
            $body .= 'Content-Type: application/pdf; name="yel_credit_note_'. pad($invoice, 11, '0'). '.pdf"'. "\n";
            $body .= 'Content-Transfer-Encoding: base64'. "\n";
            $body .= 'Content-Disposition: attachment'. "\n";
            $body .= $attachment. "\n";
            $body .= '--yel_mail_sep_'. $filename. "--\n\n";
            mail($employer->getEmailAddress(), $subject, $body, $headers);

            unlink($GLOBALS['data_path']. '/credit_notes/'. $filename. '.pdf');
        }
    }
    
    // 2.4 If it is a replacement, update both referrals to disable future replacements.
    if ($is_replacement) {
        $mysqli = Database::connect();
        $queries = "UPDATE referrals SET 
                    replaced_on = '". now(). "', 
                    replaced_referral = ". $referral->getId(). " 
                    WHERE id = ". $previous_referral. "; 
                    UPDATE referrals SET 
                    guarantee_expire_on = '". $work_commence_on. "', 
                    replacement_authorized_on = NULL, 
                    replaced_on = '". now(). "', 
                    replaced_referral = ". $referral->getId(). " 
                    WHERE id = ". $referral->getId();
        if (!$mysqli->transact($queries)) {
            echo 'ko';
            exit();
        }
    }
    
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
    $subject = desanitize($candidate->getFullName()). " was successfully employed!";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    // mail($member->getId(), $subject, $message, $headers);
    
    echo 'ok';
    exit();
}

?>