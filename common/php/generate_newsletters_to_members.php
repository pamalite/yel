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
$query = "SELECT email_addr, firstname, lastname, filter_jobs, primary_industry, secondary_industry 
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
    
    foreach ($members as $member) {
        $query = '';
        if ($member['filter_jobs'] == 'Y') {
            $query = "SELECT jobs.id, employers.name AS employer, jobs.title, industries.industry, countries.country,
                      jobs.potential_reward, currencies.symbol 
                      FROM jobs 
                      LEFT JOIN employers ON employers.id = jobs.employer 
                      LEFT JOIN industries ON industries.id = jobs.industry 
                      LEFT JOIN countries ON countries.country_code = jobs.country 
                      LEFT JOIN currencies ON currencies.country_code = employers.country 
                      WHERE closed = 'N' AND expire_on >= NOW() AND 
                      jobs.industry = ". $member['primary_industry']. " OR 
                      jobs.industry = ". $member['secondary_industry']. " 
                      ORDER BY created_on DESC 
                      LIMIT 20";
        } else {
            $query = "SELECT jobs.id, employers.name AS employer, jobs.title, industries.industry, countries.country,
                      jobs.potential_reward, currencies.symbol 
                      FROM jobs 
                      LEFT JOIN employers ON employers.id = jobs.employer 
                      LEFT JOIN industries ON industries.id = jobs.industry 
                      LEFT JOIN countries ON countries.country_code = jobs.country 
                      LEFT JOIN currencies ON currencies.country_code = employers.country 
                      WHERE closed = 'N' 
                      ORDER BY created_on DESC 
                      LIMIT 20";
        }
        
        $jobs = $mysqli->query($query);
        if (count($jobs) <= 0 || is_null($jobs)) {
            log_activity('No jobs found for '. $member['email_addr']. '.', 'yellowel_member_newsletter_generator.log');
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
                $job_id = $job['id'];
                $title = $job['title'];
                $employer = $job['employer'];
                $industry = $job['industry'];
                $country = $job['country'];
                $currency = $job['symbol'];
                $potential_reward = $job['potential_reward'];

                $job_list .= '<tr><td>'. $employer. '</td><td><a href="http://yellowelevator.com/job/'. $job_id. '">'. $title. '</a></td><td>'. $industry. '</td><td>'. $country. '</td><td>'. $currency. ' '. $potential_reward. '</td></tr>'. "\n";
                //$job_list .= $employer. pads(strlen($employer), $employer_col_width). $title. pads(strlen($title), $title_col_width). $industry. pads(strlen($industry), $industry_col_width). $country. "\n";
            }
            $message = str_replace('%job_list%', $job_list, $message);
            $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
            $message = str_replace('%root%', $GLOBALS['root'], $message);
            
            $member_fullname = htmlspecialchars_decode($member['firstname']. ' '. $member['lastname']);
            $new_message = $message;
            $new_message = str_replace('%member%', $member_fullname, $new_message);

            $headers  = 'MIME-Version: 1.0' . "\n";
            $headers .= 'Content-type: text/html; charset=utf-8' . "\n";
            $headers .= 'To: '.  $member_fullname. '<'. $member['email_addr']. ">\n";
            $headers .= 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
            $subject = 'Week '. date('W'). ' newsletter from Yellow Elevator';
            
            log_activity('Sending e-mail to: '. $member['email_addr'], 'yellowel_member_newsletter_generator.log');
            mail($member['email_addr'], $subject, $new_message, $headers);
            /*$handle = fopen('/tmp/email_to_'. $member['email_addr']. '.txt', 'w');
            fwrite($handle, 'Subject: '. $subject. "\n\n");
            fwrite($handle, $new_message);
            fclose($handle);*/
        }
    }
}

log_activity('Task completed. Goodbye!', 'yellowel_member_newsletter_generator.log');
?>
