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
if ($member->hasPhoto()) {
    $photo = $member->getPhotoFileInfo();
    
    $extension = '';
    switch ($photo['photo_type']) {
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
    header('Content-type: '. $photo['photo_type']);
    header('Content-Disposition: attachment; filename="'. $member->getFullName(). '.'. $extension. '"');

    readfile($GLOBALS['photo_dir']. "/". $photo['id']. ".". $photo['photo_hash']);
} else {
    echo "No photo was uploaded by this candidate.";
}
?>