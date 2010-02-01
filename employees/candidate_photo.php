<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/employees/resume.php?id='. $_GET['id']);
        exit();
    }
}

if (!isset($_SESSION['yel']['employee']) || 
    empty($_SESSION['yel']['employee']['id']) || 
    empty($_SESSION['yel']['employee']['sid']) || 
    empty($_SESSION['yel']['employee']['hash'])) {
    echo "An illegal attempt to view photo has been detected.";
    exit();
}

if (isset($_SESSION['yel']['employee']['dev'])) {
    if ($_SESSION['yel']['employee']['dev'] === true) {
        $is_dev = false;
        $root_items = explode('/', $GLOBALS['root']);
        foreach ($root_items as $value) {
            if ($value == 'yel') {
                $is_dev = true;
                break;
            }
        }

        if (!$is_dev) {
            ?>
            <script type="text/javascript">alert('Please logout from your existing connection before proceeding.');</script>
            <?php
            exit();
        }
    }
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
