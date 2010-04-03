<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

// if (!isset($_GET['ie_suck'])) {
//     if ($GLOBALS['protocol'] == 'https') {
//         if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
//             redirect_to('https://'. $GLOBALS['root']. '/employers/resume.php?id='. $_GET['id']);
//             exit();
//         }
//     }
// }

if (!isset($_SESSION['yel']['employer']) || 
    empty($_SESSION['yel']['employer']['id']) || 
    empty($_SESSION['yel']['employer']['sid']) || 
    empty($_SESSION['yel']['employer']['hash'])) {
    echo "An illegal attempt to view resume has been detected.";
    exit();
}

$resume = new Resume('', $_GET['id']);
$resume_file = $resume->getFileInfo();
$member = new Member($resume_file['member']);
$has_photo = $member->hasPhoto();

$file = $GLOBALS['resume_dir']. "/". $_GET['id']. ".". $resume_file['file_hash'];

if ($has_photo) {
    ?>
        <html>
        </body>
        <div style="text-align: center;">
            <?php 
            if (file_exists($file)) { 
            ?>
            <a href="http://<?php echo $GLOBALS['root']. '/employers/resume_download.php?id='. $_GET['id'] ?>">
                Click here to download the resume.
            </a>
            <?php 
            } else { 
                echo 'Sorry, the resume file seemed to be missing. Please contact us at <a href="mailto: support@yellowelevator.com">support@yellowelevator.com</a>.';
            } 
            ?>
        </div>
        <br/>
        <div style="text-align: center;">
            <img src="candidate_photo.php?id=<?php echo $member->getId() ?>" style="border: none;" />
        </div>
        </body>
        </html>
    <?php
} else {
    if (file_exists($file)) {
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Expires: -1');
        header('Content-Description: File Transfer');
        header('Content-Length: ' . $resume_file['file_size']);
        header('Content-Disposition: attachment; filename="' . $resume_file['file_name'].'"');
        header('Content-type: '. $resume_file['file_type']);
        ob_clean();
        flush();
        readfile($file);
    } else {
        ?>
        <html>
        <body>
        <?php
        echo 'Sorry, the resume file seemed to be missing. Please contact us at <a href="mailto: support@yellowelevator.com">support@yellowelevator.com</a>.';
        ?>
        </body>
        </html>
        <?php
    }
}
?>