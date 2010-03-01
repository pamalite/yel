<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

log_activity('Initializing Job Expiry Reminder Generator...', 'yellowel_job_expiry_reminders_generator.log');

$mysqli = Database::connect();

log_activity('Getting all active employers...', 'yellowel_job_expiry_reminders_generator.log');
$query = "SELECT employers.id, employers.name AS employer, employers.email_addr, 
          employers.contact_person, branches.country AS branch 
          FROM employers 
          LEFT JOIN employees ON employees.id = employers.registered_by 
          LEFT JOIN branches ON branches.id = employees.branch 
          WHERE employers.active = 'Y'";
$employers = $mysqli->query($query);

log_activity('Preparing reminders...', 'yellowel_job_expiry_reminders_generator.log');
foreach ($employers as $employer) {
    $branch = 'my';
    if (!is_null($employer['branch']) && !empty($employer['branch'])) {
        $branch = strtolower($employer['branch']);
    }
    
    $sales_email_addr = 'sales.'. $branch. '@yellowelevator.com';
    
    log_activity('Getting expired jobs for employer: '. $employer['employer'], 'yellowel_job_expiry_reminders_generator.log');
    $query = "SELECT title, 
              DATE_FORMAT(expire_on, '%e %b, %Y') AS formatted_expire_on 
              FROM jobs 
              WHERE DATEDIFF(expire_on, NOW()) < 0 AND employer = '". $employer['id']. "' AND 
              closed = 'N'";
    $expired_jobs = $mysqli->query($query);
    
    if (!is_null($expired_jobs) && !empty($expired_jobs)) {
        $jobs = '';
        foreach ($expired_jobs as $expired_job) {
            $jobs .= '- '. htmlspecialchars_decode($expired_job['title']). ' expired on '. $expired_job['formatted_expire_on']. "\n";
        }
        
        $lines = file(dirname(__FILE__). '/../../private/mail/employer_job_expired_reminder.txt');
        $message = '';
        foreach($lines as $line) {
            $message .= $line;
        }
        
        $message = str_replace('%employer%', htmlspecialchars_decode($employer['employer']), $message);
        $message = str_replace('%jobs%', $jobs, $message);
        $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
        $message = str_replace('%root%', $GLOBALS['root'], $message);
        $subject = 'Expired Job Ads';
        $headers = 'To: '.  $employer['email_addr']. '<'. $employer['email_addr']. ">\n";
        $headers .= 'Cc: '.  $sales_email_addr. ">\n";
        $headers .= 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
        log_activity('Sending e-mail to: '. $employer['email_addr'], 'yellowel_job_expiry_reminders_generator.log');
        mail($employer['email_addr'], $subject, $message, $headers);
        
        // $handle = fopen('/tmp/email_to_'. $employer['email_addr']. '.txt', 'w');
        // fwrite($handle, 'Subject: '. $subject. "\n\n");
        // fwrite($handle, $message);
        // fclose($handle);
    }
    
    log_activity('Getting expiring jobs for employer: '. $employer['employer'], 'yellowel_job_expiry_reminders_generator.log');
    $query = "SELECT title, 
              DATE_FORMAT(expire_on, '%e %b, %Y') AS formatted_expire_on 
              FROM jobs 
              WHERE DATEDIFF(expire_on, NOW()) = 7 AND employer = '". $employer['id']. "' AND 
              closed = 'N'";
    $expiring_jobs = $mysqli->query($query);
    
    if (!is_null($expiring_jobs) && !empty($expiring_jobs)) {
        $jobs = '';
        foreach ($expiring_jobs as $expiring_job) {
            $jobs .= '- '. htmlspecialchars_decode($expiring_job['title']). ' expires on '. $expiring_job['formatted_expire_on']. "\n";
        }
        
        $lines = file(dirname(__FILE__). '/../../private/mail/employer_extend_job_reminder.txt');
        $message = '';
        foreach($lines as $line) {
            $message .= $line;
        }
        
        $message = str_replace('%employer%', htmlspecialchars_decode($employer['employer']), $message);
        $message = str_replace('%jobs%', $jobs, $message);
        $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
        $message = str_replace('%root%', $GLOBALS['root'], $message);
        $subject = 'Expired Job Ads';
        $headers = 'To: '.  $employer['email_addr']. '<'. $employer['email_addr']. ">\n";
        $headers .= 'Cc: '.  $sales_email_addr. ">\n";
        $headers .= 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
        log_activity('Sending e-mail to: '. $employer['email_addr'], 'yellowel_job_expiry_reminders_generator.log');
        mail($employer['email_addr'], $subject, $message, $headers);

        // $handle = fopen('/tmp/email_to_'. $employer['email_addr']. '.txt', 'w');
        // fwrite($handle, 'Subject: '. $subject. "\n\n");
        // fwrite($handle, $message);
        // fclose($handle);
    }
}

log_activity('Task completed. Goodbye!', 'yellowel_job_expiry_reminders_generator.log');
?>
