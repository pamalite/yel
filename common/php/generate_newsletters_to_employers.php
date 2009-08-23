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

function front_pads_for($str, $length, $max_length) {
    $pads = '';
    $required = $max_length - $length;
    for ($i=0; $i < $required; $i++) {
        $pads .= ' ';
    }
    
    $str = $pads. $str;
    return $str;
}

log_activity('Initializing Employer Newsletters Generator...', 'yellowel_employer_newsletter_generator.log');

$mysqli = Database::connect();

log_activity('Getting the all unread referrals...', 'yellowel_employer_newsletter_generator.log');
$query = "SELECT employers.name AS employer, employers.email_addr, employers.contact_person, 
          employers.id AS employer_id, jobs.title, industries.industry, 
          COUNT(referrals.job) AS num_of_referrals 
          FROM referrals 
          LEFT JOIN jobs ON jobs.id = referrals.job 
          LEFT JOIN employers ON employers.id = jobs.employer 
          LEFT JOIN industries ON industries.id = jobs.industry 
          WHERE employers.like_newsletter = true AND 
          (referrals.referee_acknowledged_on IS NOT NULL AND referrals.referee_acknowledged_on <> '0000-00-00 00:00:00') AND 
          (referrals.employer_agreed_terms_on IS NULL OR referrals.employer_agreed_terms_on = '0000-00-00 00:00:00') 
          GROUP BY referrals.job 
          ORDER BY referrals.referee_acknowledged_on DESC";
$referrals = $mysqli->query($query);

log_activity('Grouping the referrals to their respective employers...', 'yellowel_employer_newsletter_generator.log');
$sorted_referrals = array();
foreach ($referrals as $referral) {
    $sorted_referrals[$referral['employer_id']][] = $referral;
}

log_activity('Preparing newsletters...', 'yellowel_employer_newsletter_generator.log');
foreach ($sorted_referrals as $refs) {
    if (count($refs) > 0 && !is_null($refs)) {
        $title_col_width = $num_of_referrals_col_width = $industry_col_width = 0;
        
        foreach ($refs as $ref) {
            $title = $ref['title'];
            $num_of_referrals = $ref['num_of_referrals'];
            $industry = $ref['industry'];

            if (strlen($title) > $title_col_width) {
                $title_col_width = strlen($title);
            }

            if (strlen($num_of_referrals) > $num_of_referrals_col_width) {
                $num_of_referrals_col_width = strlen($num_of_referrals);
            }

            if (strlen($industry) > $industry_col_width) {
                $industry_col_width = strlen($industry);
            }
        }

        $title_col_width +=  5;
        $industry_col_width += 5;
        
        $lines = file(dirname(__FILE__). '/../../private/mail/employers_newsletter.txt');
        $message = '';
        foreach($lines as $line) {
            $message .= $line;
        }

        $subject = 'Week '. date('W'). ' referrals from Yellow Elevator';
        $referrals_list = '';
        foreach ($refs as $ref) {
            $title = htmlspecialchars_decode($ref['title']);
            $industry = $ref['industry'];
            $num_of_referrals = $ref['num_of_referrals'];
            
            $referrals_list .= '<tr><td>'. $title. '</td><td>'. $industry. '</td><td align="right">'. $num_of_referrals. '</td></tr>'. "\n";
            //$referrals_list .= $title. pads(strlen($title), $title_col_width). $industry. pads(strlen($industry), $industry_col_width). front_pads_for($num_of_referrals, strlen($num_of_referrals), $num_of_referrals_col_width). "\n";
        }
        $message = str_replace('%referrals%', $referrals_list, $message);
        
        $employer_name = htmlspecialchars_decode($refs[0]['employer']);
        $contact_person = htmlspecialchars_decode($refs[0]['contact_person']);
        $message = str_replace('%employer%', $employer_name, $message);
        $message = str_replace('%contact_person%', $contact_person, $message);
        $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
        $message = str_replace('%root%', $GLOBALS['root'], $message);
        $headers  = 'MIME-Version: 1.0' . "\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\n";
        $headers .= 'To: '.  $contact_person. '<'. $refs[0]['email_addr']. ">\n";
        $headers .= 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
        
        log_activity('Sending e-mail to: '. $refs[0]['email_addr'], 'yellowel_employer_newsletter_generator.log');
        mail($refs[0]['email_addr'], $subject, $message, $headers);
    }
}

log_activity('Task completed. Goodbye!', 'yellowel_employer_newsletter_generator.log');
?>
