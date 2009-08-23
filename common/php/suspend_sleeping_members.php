<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

log_activity('Initializing Members Suspender...', 'yellowel_member_suspender.log');

$today = today();
$mysqli = Database::connect();

log_activity('Updating the members which last login 3 months ago.', 'yellowel_member_suspender.log');
$query = "UPDATE members, member_sessions 
          SET members.active = 'S' 
          WHERE DATE_ADD(member_sessions.last_login, INTERVAL 3 MONTH) <= '". $today. "' AND 
          members.email_addr = member_sessions.member AND 
          members.active = 'Y'";
if ($mysqli->execute($query) === false) {
    $errors = $mysqli->error();
    log_activity('Error on updating: '. $errors['errno']. ': '. $errors['error'], 'yellowel_member_suspender.log');
    log_activity('Unable to complete task!', 'yellowel_member_suspender.log');
    exit();
}

log_activity('Task completed. Goodbye!', 'yellowel_member_suspender.log');
?>
