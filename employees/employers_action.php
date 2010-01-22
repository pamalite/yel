<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";
require_once dirname(__FILE__). "/paid_postings_invoice.php";
require_once dirname(__FILE__). "/../private/config/subscriptions_rate.inc";

session_start();

if (!isset($_POST['id'])) {
    echo "ko";
    exit();
    //redirect_to('login.php');
}

$xml_dom = new XMLDOM();

if (!isset($_POST['action'])) {
    $order_by = 'employers.joined_on desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT employers.id, employers.name, employers.active, 
              CONCAT(employees.firstname, ', ', employees.lastname) AS employee, 
              DATE_FORMAT(employers.joined_on, '%e %b, %Y') AS formatted_joined_on, 
              DATE_FORMAT(employer_sessions.first_login, '%e %b, %Y') AS formatted_first_login 
              FROM employers 
              LEFT JOIN employer_sessions ON employer_sessions.employer = employers.id 
              LEFT JOIN employees ON employees.id = employers.registered_by 
              WHERE employees.branch = ". $_SESSION['yel']['employee']['branch']['id']. "
              ORDER BY ". $_POST['order_by'];
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }
    
    if (!$result) {
        echo 'ko';
        exit();
    }
    
    foreach($result as $i=>$row) {
        $result[$i]['employer'] = htmlspecialchars_decode($row['employer']);
    }
    
    $response = array('employers' => array('employer' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'deactivate') {
    if (!isset($_POST['payload'])) {
        echo "ko";
        exit();
    }
    
    $xml_dom->load_from_xml($_POST['payload']);
    $employers = $xml_dom->get('id');
    $query = "UPDATE employers SET active = 'N' WHERE id IN (";
    $i = 0;
    foreach ($employers as $employer) {
        $query .= "'". $employer->nodeValue. "'";
        
        if ($i < $employers->length-1) {
            $query .= ", ";
        }
        
        $i++;
    }
    $query .= ")";

    $mysqli = Database::connect();
    if (!$mysqli->execute($query)) {
        echo "ko";
        exit();
    }
    
    echo "ok";
    exit();
}

if ($_POST['action'] == 'activate') {
    $query = "UPDATE employers SET active = 'Y' 
              WHERE id = '". $_POST['id'] . "'";
    
    $mysqli = Database::connect();
    if (!$mysqli->execute($query)) {
        echo "ko";
        exit();
    }
    
    echo "ok";
    exit();
}

if ($_POST['action'] == 'get_employer') {
    $employer = new Employer($_POST['id']);
    $result = $employer->get();
    if (!$result) {
        echo "ko";
        exit();
    }
    
    $subscription_expire_on = explode('-', $result[0]['subscription_expire_on']);
    $year = $subscription_expire_on[0];
    
    $day = $subscription_expire_on[2];
    if (substr($day, 0, 1) == '0') {
        $day = substr($day, 1, 1);
    }
    
    $month = '???';
    switch ($subscription_expire_on[1]) {
        case '01':
            $month = 'Jan';
            break;
        case '02':
            $month = 'Feb';
            break;
        case '03':
            $month = 'Mar';
            break;
        case '04':
            $month = 'Apr';
            break;
        case '05':
            $month = 'May';
            break;
        case '06':
            $month = 'Jun';
            break;
        case '07':
            $month = 'Jal';
            break;
        case '08':
            $month = 'Aug';
            break;
        case '09':
            $month = 'Sep';
            break;
        case '10':
            $month = 'Oct';
            break;
        case '11':
            $month = 'Nov';
            break;
        case '12':
            $month = 'Dec';
            break;
    }
    
    $result[0]['formatted_subscription_expire_on'] = $day. ' '. $month. ', '. $year;
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('employer' => $result));
    exit();
}

if ($_POST['action'] == 'reset_password') {
    $new_password = generate_random_string_of(6);
    $data = array();
    $data['password'] = md5($new_password);
    $employer = new Employer($_POST['id']);
    if (!$employer->update($data, true)) {
        echo "ko";
        exit();
    }
    
    $query = "SELECT email_addr FROM employers WHERE id = '". $_POST['id']. "' LIMIT 1";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    $lines = file(dirname(__FILE__). '/../private/mail/employer_password_reset_admin.txt');
    $message = '';
    foreach($lines as $line) {
        $message .= $line;
    }
    
    $message = str_replace('%user_id%', $_POST['id'], $message);
    $message = str_replace('%temporary_password%', $new_password, $message);
    $subject = "Employer Password Reset";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($result[0]['email_addr'], $subject, $message, $headers);
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'save_profile') {
    $today = now();
    
    $mode = 'update';
    if ($_POST['id'] == '0') {
        $mode = 'create';
    }
    
    $query = "SELECT branch FROM employees WHERE id = ". $_POST['employee']. " LIMIT 1";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    $branch = 0;
    if ($result !== false) {
        $branch = $result[0]['branch'];
    }
    
    $data = array();
    $data['license_num'] = $_POST['license_num'];
    $data['name'] = $_POST['name'];
    $data['phone_num'] = $_POST['phone_num'];
    $data['email_addr'] = $_POST['email_addr'];
    $data['contact_person'] = $_POST['contact_person'];
    $data['address'] = $_POST['address'];
    $data['state'] = $_POST['state'];
    $data['zip'] = $_POST['zip'];
    $data['country'] = $_POST['country'];
    $data['working_months'] = $_POST['working_months'];
    //$data['bonus_months'] = $_POST['bonus_months'];
    $data['payment_terms_days'] = $_POST['payment_terms_days'];
    $data['branch'] = $branch;
    
    $data['website_url'] = $_POST['website_url'];
    if (substr($_POST['website_url'], 0, 4) != 'http') {
        $data['website_url'] = 'http://'. $_POST['website_url'];
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
        $data['password'] = md5($_POST['password']);
        $data['registered_by'] = $_POST['employee'];
        $data['registered_through'] = 'M';
        $data['joined_on'] = $today;
        $data['free_postings_left'] = $_POST['free_postings'];
        
        $subscription_expire_on = $data['joined_on'];
        if ($_POST['subscription_period'] > 0) {
            $subscription_expire_on = sql_date_add($data['joined_on'], $_POST['subscription_period'], 'month');
        }
        $data['subscription_expire_on'] = $subscription_expire_on;
        
        if (!$employer->create($data)) {
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
        $message = str_replace('%temporary_password%', $_POST['password'], $message);
        $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
        $message = str_replace('%root%', $GLOBALS['root'], $message);
        $subject = "Welcome To Yellow Elevator!";
        $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
        mail($_POST['email_addr'], $subject, $message, $headers);
    }
    
    if ($_POST['paid_postings'] > 0 && !empty($_POST['paid_postings'])) {
        if ($employer->add_paid_job_posting($_POST['paid_postings']) === false) {
            echo 'ko';
            exit();
        }
        
        // 0. get the job postings pricing and currency
        $branch = $employer->get_branch();
        $sales = 'sales.'. strtolower($branch[0]['country']). '@yellowelevator.com';
        $branch[0]['address'] = str_replace(array("\r\n", "\r"), "\n", $branch[0]['address']);
        $branch['address_lines'] = explode("\n", $branch[0]['address']);
        $posting_rates = $GLOBALS['postings_rates'];
        $currency = Currency::symbol_from_country_code($branch[0]['country']);
        $price = $posting_rates[$currency];
        
        // 1. generate invoice in the system
        $data = array();
        $data['issued_on'] = $today;
        $data['type'] = 'P';
        $data['employer'] = $_POST['id'];
        $data['payable_by'] = sql_date_add($today, $employer->get_payment_terms_days(), 'day');
        
        $invoice = Invoice::create($data);
        if ($invoice === false) {
            echo 'ko';
            exit();
        }
        
        $amount = $price * $_POST['paid_postings'];
        $desc = $_POST['paid_postings']. ' Job Posting(s) @ '. $currency. ' $'. $price;
        $item_added = Invoice::add_item($invoice, $amount, '1', $desc);
        
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
        
        $issued_on = substr($data['issued_on'], 0, 10);
        $pdf->Cell(33, 5, $issued_on,1,0,'C');
        $pdf->Cell(1);
        
        $payable_by = substr($data['payable_by'], 0, 10);
        $pdf->Cell(33, 5, $payable_by,1,0,'C');
        $pdf->Cell(1);
        $pdf->Cell(0, 5, number_format($amount, '2', '.', ', '),1,0,'C');
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

        $message = str_replace('%employer%', $employer->get_name(), $message);
        $message = str_replace('%postings%', $_POST['paid_postings'], $message);
        $message = str_replace('%price%', $price, $message);
        $message = str_replace('%currency%', $currency, $message);
        $message = str_replace('%amount%', number_format($amount, 2, '.', ', '), $message);

        $issued_date = explode('-', $data['issued_on']);
        $issued_timestamp = $issued_date[0]. $issued_date[1]. $issued_date[2];
        $message = str_replace('%purchased_on%', date('j M, Y', $issued_timestamp), $message);

        $body .= $message. "\n";
        $body .= '--yel_mail_sep_alt_'. $invoice. "--\n\n";
        $body .= '--yel_mail_sep_'. $invoice. "\n";
        $body .= 'Content-Type: application/pdf; name="yel_invoice_'. pad($invoice, 11, '0'). '.pdf"'. "\n";
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
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'copy_fees_and_extras') {
    $from_employer = new Employer($_POST['employer']);
    $to_employer = new Employer($_POST['id']);
    
    $fees = $from_employer->get_fees();
    $extras = $from_employer->get_extras();
    
    if (!$to_employer->create_fees($fees)) {
        echo 'ko';
        exit();
    }
    
    if (!$to_employer->create_extras($extras)) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_fees') {
    $employer = new Employer($_POST['id']);
    $result = $employer->get_fees();
    
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

if ($_POST['action'] == 'delete_fees') {
    if (!isset($_POST['payload'])) {
        echo "ko";
        exit();
    }
    
    $xml_dom->load_from_xml($_POST['payload']);
    $fees = $xml_dom->get('id');
    $query = "DELETE FROM employer_fees WHERE id IN (";
    $i = 0;
    foreach ($fees as $fee) {
        $query .= "'". $fee->nodeValue. "'";
        
        if ($i < $fees->length-1) {
            $query .= ", ";
        }
        
        $i++;
    }
    $query .= ")";

    $mysqli = Database::connect();
    if (!$mysqli->execute($query)) {
        echo "ko";
        exit();
    }
    
    echo "ok";
    exit();
}

if ($_POST['action'] == 'get_fee') {
    $query = "SELECT * FROM employer_fees WHERE id = ". $_POST['id']. " LIMIT 1";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }
    
    if (!$result) {
        echo 'ko';
        exit();
    }
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('fee' => $result));
    exit();
}

if ($_POST['action'] == 'save_service_fee') {
    if (isset($_POST['salary_range_check'])) {
        $query = "SELECT COUNT(*) AS overlapped FROM employer_fees 
                  WHERE employer = '". $_POST['employer']. "' AND 
                  id <> ". $_POST['id']. " AND 
                  ((salary_start = ". $_POST['salary_start']. " OR salary_end = ". $_POST['salary_start']. ") OR
                  (salary_start = ". $_POST['salary_end']. " OR salary_end = ". $_POST['salary_end']. ") OR 
                  (salary_start < ". $_POST['salary_start']. " AND (salary_end > ". $_POST['salary_start']. " OR salary_end = 0)) OR
                  (salary_start < ". $_POST['salary_end']. " AND (salary_end > ". $_POST['salary_end']. " OR salary_end = 0)))";
        $mysqli = Database::connect();
        $result = $mysqli->query($query);

        if ($result[0]['overlapped'] != 0) {
            echo '-1';
            exit();
        }
    }
    
    $data = array();
    $data['id'] = $_POST['id'];
    $data['guarantee_months'] = $_POST['guarantee_months'];
    $data['discount'] = $_POST['discount'];
    $data['service_fee'] = $_POST['service_fee'];
    $data['reward_percentage'] = $_POST['reward_percentage'];
    $data['premier_fee'] = '0.00';
    
    if (isset($_POST['salary_range_check'])) {
        $data['salary_start'] = $_POST['salary_start'];
        $data['salary_end'] = $_POST['salary_end'];
    }
    
    $employer = new Employer($_POST['employer']);
    if ($_POST['id'] == '0') {
        if (!$employer->create_fee($data)) {
            echo 'ko';
            exit();
        }
    } else {
        if (!$employer->update_fee($data)) {
            echo 'ko';
            exit();
        }
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_charges') {
    $employer = new Employer($_POST['id']);
    $result = $employer->get_extras();
    
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }
    
    if (!$result) {
        echo 'ko';
        exit();
    }
    
    foreach ($result as $i=>$row) {
        $result[$i]['charges'] = number_format($row['charges'], 2, '.' , ', ');
    }
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('extras' => array('extra' => $result)));
    exit();
}

if ($_POST['action'] == 'delete_charges') {
    if (!isset($_POST['payload'])) {
        echo "ko";
        exit();
    }
    
    $xml_dom->load_from_xml($_POST['payload']);
    $extras = $xml_dom->get('id');
    $query = "DELETE FROM employer_extras WHERE id IN (";
    $i = 0;
    foreach ($extras as $extra) {
        $query .= "'". $extra->nodeValue. "'";
        
        if ($i < $extras->length-1) {
            $query .= ", ";
        }
        
        $i++;
    }
    $query .= ")";

    $mysqli = Database::connect();
    if (!$mysqli->execute($query)) {
        echo "ko";
        exit();
    }
    
    echo "ok";
    exit();
}

if ($_POST['action'] == 'get_charge') {
    $query = "SELECT * FROM employer_extras WHERE id = ". $_POST['id']. " LIMIT 1";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    
    if (count($result) <= 0 || is_null($result)) {
        echo '0';
        exit();
    }
    
    if (!$result) {
        echo 'ko';
        exit();
    }
    
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array(array('extra' => $result));
    exit();
}

if ($_POST['action'] == 'save_extra_charge') {
    $data = array();
    $data['id'] = $_POST['id'];
    $data['label'] = $_POST['label'];
    $data['charges'] = number_format($_POST['charges'], 2);
    
    $employer = new Employer($_POST['employer']);
    if ($_POST['id'] == '0') {
        if (!$employer->create_extra($data)) {
            echo 'ko';
            exit();
        }
    } else {
        if (!$employer->update_extra($data)) {
            echo 'ko';
            exit();
        }
    }
    
    echo 'ok';
    exit();
}

?>
