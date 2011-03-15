<?php
require_once "../private/lib/classes/facebook.php";

?><p style="font-weight: bold;">Search jobs </p><p><?php
$fb = new Facebook();
$result = $fb->get_jobs('', 0, '', 'acme124');

if ($result === false) {
    echo $fb->last_error();
    exit();
}

echo '<pre>';
print_r($result);
echo '</pre>';

?></p><br/><br/><?php

if ($fb->notify_ye_consultants(1)) {
    echo 'ok';
} else {
    echo 'ko';
}

if ($fb->notify_ye_consultants(2)) {
    echo 'ok';
} else {
    echo 'ko';
}

?></p><?php
?>
