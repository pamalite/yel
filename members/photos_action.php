<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['action'])) {
    $member = new Member($_GET['id']);
    $photos = $member->get_approved_photos();
    
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

if ($_POST['action'] == 'delete') {
    $member = new Member($_POST['member']);
    if (!$member->delete_photo($_POST['id'])) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'upload') {
    $member = new Member($_POST['member']);
    $photos = $member->get_photos();
    if (count($photos) > 0 && $photos != false) {
        $member->delete_photo($photos[0]['id']);
    }
    
    $data = array();
    $data['FILE'] = array();
    $data['FILE']['type'] = $_FILES['my_file']['type'];
    $data['FILE']['size'] = $_FILES['my_file']['size'];
    $data['FILE']['name'] = str_replace(array('\'', '"', '\\'), '', basename($_FILES['my_file']['name']));
    $data['FILE']['tmp_name'] = $_FILES['my_file']['tmp_name'];

    if (!$member->create_photo($data)) {
        ?><script type="text/javascript">top.stop_upload(<?php echo "0"; ?>);</script><?php
        exit();
    }
    
    ?><script type="text/javascript">top.stop_upload(<?php echo "1"; ?>);</script><?php
    exit();
}

if ($_POST['action'] == 'get_hide_banner') {
    $query = "SELECT pref_value FROM member_banners 
              WHERE member = '". $_POST['id']. "' AND pref_key = 'hide_photos_banner' LIMIT 1";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if (is_null($result)) {
        echo '0';
    } else {
        echo $result[0]['pref_value']; 
    }
    
    exit();
}

if ($_POST['action'] == 'set_hide_banner') {
    $query = "SELECT id FROM member_banners 
              WHERE member = '". $_POST['id']. "' AND pref_key = 'hide_photos_banner' LIMIT 1";
    $mysqli = Database::connect();
    $result = $mysqli->query($query);
    if ($result[0]['id'] > 0) {
        $query = "UPDATE member_banners SET pref_value = '". $_POST['hide']. "' WHERE id = ". $result[0]['id'];
    } else {
        $query = "INSERT INTO member_banners SET 
                  id = 0,
                  pref_key = 'hide_photos_banner', 
                  pref_value = '". $_POST['hide']. "',
                  member = '". $_POST['id']. "'";
    }
    
    $mysqli->execute($query);
    
    exit();
}

?>
