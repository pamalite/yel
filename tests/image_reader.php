<?php
$metadata = getimagesize(dirname(__FILE__). "/../common/images/dot_separator.png");
foreach ($metadata as $key => $value) {
    echo $key. " = ". $value. "<br>";
}
?>