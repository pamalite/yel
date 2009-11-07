<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";
?>

<html>
<body style="margin: 0px 0px 0px 0px;">
<img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/tour/main.jpg" usemap="#buttons"/>

<map name="buttons">
<area shape="rect" coords="261,179,461,229" href="employer/index.php" />
<area shape="rect" coords="261,229,461,279" href="member/index.php" />
</map>
</body>
</html>
