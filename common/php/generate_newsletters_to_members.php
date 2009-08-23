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

log_activity('Getting the latest jobs...', 'yellowel_member_newsletter_generator.log');
$query = "SELECT employers.name AS employer, jobs.title, industries.industry, countries.country,
          jobs.potential_reward, currencies.symbol 
          FROM jobs 
          LEFT JOIN employers ON employers.id = jobs.employer 
          LEFT JOIN industries ON industries.id = jobs.industry 
          LEFT JOIN countries ON countries.country_code = jobs.country 
          LEFT JOIN currencies ON currencies.country_code = employers.country 
          WHERE closed = 'N' 
          ORDER BY created_on DESC 
          LIMIT 20";
$jobs = $mysqli->query($query);

if (count($jobs) <= 0 || is_null($jobs)) {
    log_activity('Nothing to do.', 'yellowel_member_newsletter_generator.log');
} else {
    if ($jobs === false) {
        $errors = $mysqli->error();
        log_activity('Error on querying: '. $errors['errno']. ': '. $errors['error'], 'yellowel_member_newsletter_generator.log');
        log_activity('Unable to complete task!', 'yellowel_member_newsletter_generator.log');
        exit();
    }

    log_activity('Preparing newsletter...', 'yellowel_member_newsletter_generator.log');
    
    $title_col_width = $employer_col_width = $industry_col_width = 0;
    foreach ($jobs as $job) {
        $title = $job['title'];
        $employer = $job['employer'];
        $industry = $job['industry'];
        
        if (strlen($title) > $title_col_width) {
            $title_col_width = strlen($title);
        }
        
        if (strlen($employer) > $employer_col_width) {
            $employer_col_width = strlen($employer);
        }
        
        if (strlen($indsutry) > $industry_col_width) {
            $industry_col_width = strlen($industry);
        }
    }
    
    $title_col_width +=  5;
    $employer_col_width += 5;
    $industry_col_width += 5;
    
    $lines = file(dirname(__FILE__). '/../../private/mail/member_newsletter.txt');
    $message = '';
    foreach($lines as $line) {
        $message .= $line;
    }

    $subject = 'Week '. date('W'). ' newsletter from Yellow Elevator';
    $job_list = '';
    foreach ($jobs as $job) {
        $title = $job['title'];
        $employer = $job['employer'];
        $industry = $job['industry'];
        $country = $job['country'];
        $currency = $job['symbol'];
        $potential_reward = $job['potential_reward'];
        
        $job_list .= '<tr><td>'. $employer. '</td><td>'. $title. '</td><td>'. $industry. '</td><td>'. $country. '</td><td>'. $currency. ' '. $potential_reward. '</td></tr>'. "\n";
        //$job_list .= $employer. pads(strlen($employer), $employer_col_width). $title. pads(strlen($title), $title_col_width). $industry. pads(strlen($industry), $industry_col_width). $country. "\n";
    }
    $message = str_replace('%job_list%', $job_list, $message);
    $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
    $message = str_replace('%root%', $GLOBALS['root'], $message);

    log_activity('Getting the members who are active and wants newsletter...', 'yellowel_member_newsletter_generator.log');
    $query = "SELECT email_addr, firstname, lastname 
              FROM members 
              WHERE active = 'Y' AND like_newsletter = 'Y'";
    $members = $mysqli->query($query);
    foreach ($members as $member) {
        $member_fullname = htmlspecialchars_decode($member['firstname']. ' '. $member['lastname']);
        $new_message = $message;
        $new_message = str_replace('%member%', $member_fullname, $new_message);
        
        $headers  = 'MIME-Version: 1.0' . "\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\n";
        $headers .= 'To: '.  $member_fullname. '<'. $member['email_addr']. ">\n";
        $headers .= 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
        
        log_activity('Sending e-mail to: '. $member['email_addr'], 'yellowel_member_newsletter_generator.log');
        mail($member['email_addr'], $subject, $new_message, $headers);
    }
}

log_activity('Task completed. Goodbye!', 'yellowel_member_newsletter_generator.log');
?>
