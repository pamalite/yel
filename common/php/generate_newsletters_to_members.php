<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

function pads($length, $max_length) {
    $pads = '';
    $required = $max_length - $length;
    for ($i=0; $i < $required; $i++) {
        $pads .= ' ';
    }
    
    return $pads;
}

log_activity('Initializing Member Newsletters Generator...', 'yellowel_member_newsletter_generator.log');

$mysqli = Database::connect();

log_activity('Getting the members who are active and wants newsletter...', 'yellowel_member_newsletter_generator.log');
$query = "SELECT email_addr, firstname, lastname, filter_jobs, 
          primary_industry, secondary_industry, tertiary_industry 
          FROM members 
          WHERE active = 'Y' AND like_newsletter = 'Y'";
$members = $mysqli->query($query);

if (is_null($members) || count($members) <= 0) {
    log_activity('Nothing to do.', 'yellowel_member_newsletter_generator.log');
} else {
    if ($members === false) {
        $errors = $mysqli->error();
        log_activity('Error on querying: '. $errors['errno']. ': '. $errors['error'], 'yellowel_member_newsletter_generator.log');
        log_activity('Unable to complete task!', 'yellowel_member_newsletter_generator.log');
        exit();
    }
    
    // 1. Count the latest jobs
    $query = "SELECT COUNT(jobs.id) AS job_count 
              FROM jobs
              INNER JOIN employers ON employers.id = jobs.employer 
              INNER JOIN branches ON branches.id = employers.branch 
              WHERE jobs.created_on BETWEEN date_add(CURDATE(), INTERVAL -1 WEEK) AND CURDATE()";
    $result = $mysqli->query($query);
    $new_jobs_count = '(No new jobs this week.)';
    if ($result[0]['job_count'] > 0) {
        $new_jobs_count = $result[0]['job_count'];
    }
    
    // 2. List the new employers
    $query = "SELECT employers.id, employers.name 
              FROM employers 
              INNER JOIN branches ON branches.id = employers.branch 
              WHERE employers.joined_on BETWEEN DATE_ADD(CURDATE(), INTERVAL -1 WEEK) AND CURDATE() 
              LIMIT 3";
    $result = $mysqli->query($query);
    $new_employers_list = '(No new employers this week.)';
    if (!is_null($result[0]['name']) && !empty($result[0]['name'])) {
        $new_employers_list = '<ul>'. "\n";
        foreach ($result as $employer) {
            $new_employers_list .= '<li><a href="%protocol%://%root%/search.php?industry=0&employer='. $employer['id']. '&keywords=">'. htmlspecialchars_decode(desanitize($employer['name'])). '</a></li>'. "\n";
        }
        $new_employers_list .= '</ul>'. "\n";
    }
    
    // 3. List the top 5 most lucrative
    $query = "SELECT jobs.id, jobs.title, employers.name AS employer, 
              branches.currency, jobs.salary, jobs.salary_end, jobs.potential_reward
              FROM jobs 
              INNER JOIN employers ON employers.id = jobs.employer 
              INNER JOIN branches ON branches.id = employers.branch 
              WHERE jobs.closed = 'N' AND jobs.expire_on > CURDATE() 
              ORDER BY jobs.potential_reward DESC
              LIMIT 5";
    $result = $mysqli->query($query);
    $top_five_lucrative_jobs = '';
    if (!is_null($result) && !empty($result)) {
        $i = 1;
        foreach ($result as $row) {
            if ($i % 2 != 0) {
                $top_five_lucrative_jobs .= '<tr bgcolor="#eeeeee">'. "\n";
            } else {
                $top_five_lucrative_jobs .= '<tr>'. "\n";
            }
            $top_five_lucrative_jobs .= '<td><font color="#0000ff" face="Tahoma" size="2"><a href="%protocol%://%root%/job/'. $row['id']. '">'. $row['title']. '</a></font></td>'. "\n";
            $top_five_lucrative_jobs .= '<td><font color="#666666" face="Tahoma" size="2">'. $row['employer']. '</font></td>'. "\n";
            
            if (empty($row['salary_end']) || is_null($row['salary_end'])) {
                $top_five_lucrative_jobs .= '<td><font color="#666666" face="Tahoma" size="2">from '. $row['currency']. ' '. number_format($row['salary'], 2, '.', ','). '</font></td>'. "\n";
            } else {
                $top_five_lucrative_jobs .= '<td><font color="#666666" face="Tahoma" size="2">'. $row['currency']. ' '. number_format($row['salary'], 2, '.', ','). ' - '. number_format($row['salary_end'], 2, '.', ','). '</font></td>'. "\n";
            }
            
            $top_five_lucrative_jobs .= '<td><font color="#666666" face="Tahoma" size="2">'. $row['currency']. ' '. number_format($row['potential_reward'], 2, '.', ','). '</font></td>'. "\n";
            
            $top_five_lucrative_jobs .= '</tr>'. "\n";
            $i++;
        }
    }
    
    // 4. List top 10 jobs, by filter if set.
    foreach ($members as $member) {
        $query = '';
        if ($member['filter_jobs'] == 'Y') {
            $primary_industry = (is_null($member['primary_industry']) || empty($member['primary_industry'])) ? 'NULL' : $member['primary_industry'];
            $secondary_industry = (is_null($member['secondary_industry']) || empty($member['secondary_industry'])) ? 'NULL' : $member['secondary_industry'];
            $tertiary_industry = (is_null($member['tertiary_industry']) || empty($member['tertiary_industry'])) ? 'NULL' : $member['tertiary_industry'];
            
            $query = "SELECT jobs.id, employers.name AS employer, 
                      jobs.title, jobs.potential_reward, branches.currency, 
                      jobs.salary, jobs.salary_end 
                      FROM jobs 
                      INNER JOIN employers ON employers.id = jobs.employer 
                      INNER JOIN branches ON branches.id = employers.branch AND branches.country = 'MY'
                      INNER JOIN industries ON industries.id = jobs.industry 
                      WHERE jobs.closed = 'N' AND jobs.expire_on > CURDATE() AND
                      (jobs.industry = ". $primary_industry. " OR 
                      jobs.industry = ". $secondary_industry. " OR 
                      jobs.industry = ". $tertiary_industry. ")
                      ORDER BY jobs.potential_reward DESC 
                      LIMIT 10";
        } else {
            $query = "SELECT jobs.id, employers.name AS employer, 
                      jobs.title, jobs.potential_reward, branches.currency, 
                      jobs.salary, jobs.salary_end 
                      FROM jobs 
                      INNER JOIN employers ON employers.id = jobs.employer 
                      INNER JOIN branches ON branches.id = employers.branch AND branches.country = 'MY'
                      WHERE jobs.closed = 'N' AND jobs.expire_on > CURDATE() 
                      ORDER BY jobs.potential_reward DESC 
                      LIMIT 10";
        }
        
        $jobs = $mysqli->query($query);
        if ($jobs === false) {
            $errors = $mysqli->error();
            log_activity('Error on querying: '. $errors['errno']. ': '. $errors['error'], 'yellowel_member_newsletter_generator.log');
            log_activity('Unable to complete task!', 'yellowel_member_newsletter_generator.log');
            exit();
        }
        
        $top_10_jobs = '';
        if (count($jobs) <= 0 || is_null($jobs)) {
            log_activity('No jobs found for '. $member['email_addr']. '.', 'yellowel_member_newsletter_generator.log');
            $top_10_jobs = '<tr><td colspan="4">(No jobs found at the moment.)</td></td>'. "\n";
        } else {
            log_activity('Preparing newsletter...', 'yellowel_member_newsletter_generator.log');
            
            $i = 1;
            foreach ($jobs as $job) {
                if ($i % 2 != 0) {
                    $top_10_jobs .= '<tr bgcolor="#eeeeee">'. "\n";
                } else {
                    $top_10_jobs .= '<tr>'. "\n";
                }
                
                $top_10_jobs .= '<td><font color="#0000ff" face="Tahoma" size="2"><a href="%protocol%://%root%/job/'. $job['id']. '">'. $job['title']. '</a></font></td>'. "\n";
                $top_10_jobs .= '<td><font color="#666666" face="Tahoma" size="2">'. $job['employer']. '</font></td>'. "\n";
                
                if (is_null($jobs['salary_end']) || empty($jobs['salary_end'])) {
                    $top_10_jobs .= '<td><font color="#666666" face="Tahoma" size="2">from '. $job['currency']. ' '. number_format($job['salary'], 2, '.', ','). '</font></td>'. "\n";
                } else {
                    $top_10_jobs .= '<td><font color="#666666" face="Tahoma" size="2">'. $job['currency']. ' '. number_format($job['salary'], 2, '.', ','). ' - '. number_format($job['salary_end'], 2, '.', ','). '</font></td>'. "\n";
                }
                
                $top_10_jobs .= '<td><font color="#666666" face="Tahoma" size="2">'. $job['currency']. ' '. number_format($job['potential_reward'], 2, '.', ','). '</font></td>'. "\n";
                
                $top_10_jobs .= '</tr>'. "\n";
                $i++;
            }
        }
        
        // 5. Send the newsletter
        $lines = file(dirname(__FILE__). '/../../private/mail/member_newsletter.txt');
        $message = '';
        foreach($lines as $line) {
            $message .= $line;
        }
        
        $message = str_replace('%printed_on%', date('l j M Y'), $message);
        $message = str_replace('%number_of_new_jobs%', $new_jobs_count, $message);
        $message = str_replace('%new_employers_list%', $new_employers_list, $message);
        $message = str_replace('%top_5_lucrative_jobs%', $top_five_lucrative_jobs, $message);
        $message = str_replace('%top_10_new_jobs%', $top_10_jobs, $message);
        $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
        $message = str_replace('%root%', $GLOBALS['root'], $message);
        
        $headers  = 'MIME-Version: 1.0' . "\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\n";
        $headers .= 'To: '.  $member_fullname. '<'. $member['email_addr']. ">\n";
        $headers .= 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
        $subject = 'Week '. date('W'). ' newsletter from Yellow Elevator';
        
        log_activity('Sending e-mail to: '. $member['email_addr'], 'yellowel_member_newsletter_generator.log');
        mail($member['email_addr'], $subject, $new_message, $headers);
        
        // $handle = fopen('/tmp/email_to_'. $member['email_addr']. '.txt', 'w');
        // fwrite($handle, $message);
        // fclose($handle);
    }
}

log_activity('Task completed. Goodbye!', 'yellowel_member_newsletter_generator.log');
?>
