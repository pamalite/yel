<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";
?>

<html>
<head>
<style>
img {
    border: none;
}
</style>
<script type="text/javascript">
function show_signup() {
    if (window.opener == null) {
        window.open('<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/members/sign_up.php');
    } else {
        window.opener.location.replace('<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/members/sign_up.php');
    }
    
    window.close();
}
</script>
</head>
<body style="margin: 0px 0px 0px 0px;">
<img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/tour/member/2member-pg02.jpg" usemap="#buttons"/>

<map name="buttons">
<area shape="rect" coords="29, 9, 99, 31" href="index.php" />
<area shape="rect" coords="42, 356, 301, 389" href="javascript: show_signup();" />
<area shape="rect" coords="335, 356, 469, 389" href="pg03.php" />
</map>
</body>
</html>
