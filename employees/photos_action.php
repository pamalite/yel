<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['action'])) {
    $member = new Member($_GET['id']);
    $photos = $member->get_photos();
    
    if (!$photos) {
        exit();
    }
    
    header('Content-type: '. $photos[0]['photo_type']);
    readfile($GLOBALS['photo_dir']. "/". $photos[0]['id']. ".". $photos[0]['photo_hash']);
    exit();
}

if (!isset($_POST['id'])) {
    echo "ko";
    exit();
    //redirect_to('login.php');
}

$xml_dom = new XMLDOM();

if ($_POST['action'] == 'get_photos') {
    $order_by = 'id desc';
    
    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $query = "SELECT member_photos.id, member_photos.photo_hash, member_photos.member AS email_addr, 
              CONCAT(members.lastname, ', ', members.firstname) AS member 
              FROM member_photos 
              LEFT JOIN members ON members.email_addr = member_photos.member 
              WHERE member_photos.approved = 'N' AND 
              members.country = '". $_SESSION['yel']['employee']['branch']['country_code']. "' 
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
    
    $response = array('photos' => array('photo' => $result));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'approve_photo') {
    if (!Member::approve_photo($_POST['id'])) {
        echo 'ko';
        exit();
    }
    
    $message = 'The photo that you have uploaded is approved and is now viewable by employers.';
    $subject = "Uploaded Photo Approved";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($_POST['member'], $subject, $message, $headers);
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'disapprove_photo') {
    $member = new Member($_POST['member']);
    if (!$member->delete_photo($_POST['id'])) {
        echo 'ko';
        exit();
    }
    
    $message = 'The photo that you have uploaded cannot be approved. Please upload a new photo at the "Photo" section in your member account.';
    $subject = "Uploaded Photo Cannot Be Approved";
    $headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
    mail($_POST['member'], $subject, $message, $headers);
    
    echo 'ok';
    exit();
}

?>
