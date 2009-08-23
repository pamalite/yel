<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/employers/candidate_photo.php?id='. $_GET['id']);
        exit();
    }
}

if (!isset($_SESSION['yel']['employer']) || 
    empty($_SESSION['yel']['employer']['id']) || 
    empty($_SESSION['yel']['employer']['sid']) || 
    empty($_SESSION['yel']['employer']['hash'])) {
    echo "An illegal attempt to view candidate photo has been detected.";
    exit();
}


$member = new Member($_GET['id']);
$photos = $member->get_photos();

if (count($photos) > 0 && $photos != false) {
    $extension = '';
    switch ($photos[0]['photo_type']) {
        case 'image/jpeg':
            $extension = 'jpg';
            break;
        case 'image/gif':
            $extension = 'gif';
            break;
        case 'image/png':
            $extension = 'png';
            break;
        case 'image/tiff':
            $extension = 'tiff';
            break;
        default:
            $extension = 'bmp';
    }
    header('Content-type: '. $photos[0]['photo_type']);
    header('Content-Disposition: attachment; filename="'. $member->get_name(). '.'. $extension. '"');

    readfile($GLOBALS['photo_dir']. "/". $photos[0]['id']. ".". $photos[0]['photo_hash']);
    exit();
} else {
    echo "The candidate did not upload one's photo.";
}
?>
