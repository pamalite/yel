<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

log_activity('Initializing Job Invoice Generator...', 'yellowel_job_invoice_generator.log');

$today = today();
$mysqli = Database::connect();

// 1. Get the id and joined_on of each employer which is more than or 1 year
log_activity('Getting the employers which joined more than a year ago.', 'yellowel_job_invoice_generator.log');
$query = "SELECT id, joined_on, payment_terms_days, DATE_ADD(joined_on, INTERVAL 1 YEAR) AS invoice_start_date 
          FROM employers 
          -- WHERE DATE_ADD(joined_on, INTERVAL 1 YEAR) <= '". $today. "'";
$employers = $mysqli->query($query);

if ($employers === false) {
    $errors = $mysqli->error();
    log_activity('Error on querying: '. $errors['errno']. ': '. $errors['error'], 'yellowel_job_invoice_generator.log');
    log_activity('Unable to complete task!', 'yellowel_job_invoice_generator.log');
    exit();
}

// 2. For each employer:- 
// 2.1 Get the today's date and the invoice_start_date, which is to filter out jobs created
//     within the free period.
// 2.2 Get jobs which are 7 days old
// 2.3 Generate invoices
log_activity('Entering main loop...', 'yellowel_job_invoice_generator.log');
foreach ($employers as $employer) {
    $id = $employer['id'];
    $joined_on = $employer['joined_on'];
    $invoice_start_date = $employer['invoice_start_date'];
    
    $query = "SELECT id, title 
              FROM jobs 
              WHERE employer = '". $id. "' AND
              invoiced = 'N' AND 
              closed <> 'S' AND 
              DATEDIFF('". $today. "', created_on) >= 7 AND 
              created_on >= '". $invoice_start_date. "'";
    $jobs = $mysqli->query($query);
    if (count($jobs) > 0 && !is_null($jobs)) {
        log_activity('Got some jobs to be invoiced for employer: '. $id, 'yellowel_job_invoice_generator.log');
        
        // check extensions
        $query = "SELECT job, COUNT(*) AS number_of_extensions 
                  FROM job_extensions 
                  WHERE invoiced = 'N' AND for_replacement = 'N' 
                  GROUP BY job";
        $extension_results = $mysqli->query($query);
        
        $extensions = array();
        if (count($extension_results) > 0 && !empty($extension_results)) {
            foreach ($extension_results as $row) {
                $extensions[$row['job']] = $row['number_of_extensions'];
            }
        }
        
        $data = array();
        $data['issued_on'] = $today;
        $data['type'] = 'J';
        $data['employer'] = $id;
        $data['payable_by'] = sql_date_add($data['issued_on'], $employer['payment_terms_days'], 'day');
        
        $invoice = Invoice::create($data);
        if (!$invoice) {
            log_activity('Cannot create blank invoice. All the jobs for employer: '. $id. ' skipped.', 'yellowel_job_invoice_generator.log');
            continue;
        }
        log_activity('Created a blank invoice with ID: '. $invoice, 'yellowel_job_invoice_generator.log');
        
        log_activity('Entering inner main loop...', 'yellowel_job_invoice_generator.log');
        $sub_query = '';
        foreach ($jobs as $i=>$job) {
            $desc = '['. $job['id']. '] '. $job['title'];
            $total_fee = 10;
            
            if (count($extensions) > 0 && !empty($extensions)) {
                if (array_key_exists($job['id'], $extensions)) {
                    $total_fee += 10 * $extensions[$job['id']];
                }
            }
            
            $item_added = Invoice::add_item($invoice, $total_fee, $job['id'], $desc);
            if (!$item_added) {
                log_activity('Cannot create invoice item of job: '. $job['id']. '. The rest of the jobs for employer: '. $id. ' skipped.', 'yellowel_job_invoice_generator.log');
                continue;
            }
            
            $sub_query .= $job['id'];
            if ($i < (count($jobs)-1)) {
                $sub_query .= ", ";
            }
        }
        
        if (!empty($sub_query)) {
            log_activity('All invoice items created successfully for employer: '. $id, 'yellowel_job_invoice_generator.log');
            
            $sub_query = '('. $sub_query. ')';
            $query = "UPDATE jobs SET invoiced = 'Y' WHERE id IN ". $sub_query;
            if (!$mysqli->execute($query)) {
                log_activity('Cannot mark jobs for employer: '. $id. ' as invoiced. Jobs: '. $sub_query, 'yellowel_job_invoice_generator.log');
                log_activity('Error on executing: '. $errors['errno']. ': '. $error['error'], 'yellowel_job_invoice_generator.log');
                continue;
            } 
            
            $query = "UPDATE job_extensions SET invoiced = 'Y' WHERE job IN ". $sub_query;
            if (!$mysqli->execute($query)) {
                log_activity('Cannot mark job extensions for employer: '. $id. ' as invoiced. Jobs: '. $sub_query, 'yellowel_job_invoice_generator.log');
                log_activity('Error on executing: '. $errors['errno']. ': '. $error['error'], 'yellowel_job_invoice_generator.log');
                continue;
            }
            log_activity('All jobs invoiced for employer: '. $id, 'yellowel_job_invoice_generator.log');
        }
    } else {
        log_activity('No jobs to be invoiced for employer: '. $id, 'yellowel_job_invoice_generator.log');
    }
}

if (count($employers) <= 0 || is_null($employers)) {
    log_activity('Nothing to do.', 'yellowel_job_invoice_generator.log');
}

log_activity('Task completed. Goodbye!', 'yellowel_job_invoice_generator.log');
?>
