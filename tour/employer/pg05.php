<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";
?>

<html>
<style>
img {
    border: none;
}
</style>
<script type="text/javascript">
function show_contact_us() {
    if (window.opener == null) {
        alert('Oops! Looks like you have closed the parent page.' + "\n" + 'Please point your browser to yellowelevator.com and click on the \'Contact Us to Sign Up\' button.');
        return false;
    } 
    
    window.opener.show_contact_drop_form()
    window.close();
}
</script>
<body style="margin: 0px 0px 0px 0px;">
<img src="<?php echo $GLOBALS['protocol']. '://'. $GLOBALS['root'] ?>/common/images/tour/employer/2employer-pg05.jpg" usemap="#buttons"/>

<map name="buttons">
<area shape="rect" coords="29, 9, 99, 31" href="pg04.php" />
<area shape="rect" coords="196, 355, 314, 392" href="javascript: show_contact_us();" />
</map>
</body>
</html>
