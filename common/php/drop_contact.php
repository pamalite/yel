<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

if (isset($_POST['company']) && isset($_POST['phone']) &&
    isset($_POST['email']) && isset($_POST['contact'])) {
    $subject = "Potential Employer: ". $_POST['company'];
    $message = "Company: ". $_POST['company']. "\n";
    $message .= "Contact Person: ". $_POST['contact']. "\n";
    $message .= "Email: ". $_POST['email']. "\n";
    $message .= "Phone: ". $_POST['phone']. "\n";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    
    $ok = mail('sui.cheng.wong@yellowelevator.com', $subject, $message, $headers);
    
    echo ($ok) ? "ok" : "ko";
} else {
    echo "ko";
}
?>