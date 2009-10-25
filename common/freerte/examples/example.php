<!--
/**********************************************************************
	Version: FreeRichTextEditor.com Version 1.00.
	License: http://creativecommons.org/licenses/by/2.5/
	Description: Example of how to preload content into freeRTE using PHP.
	Author: Copyright (C) 2006  Steven Ewing
**********************************************************************/
-->
<?php
function freeRTE_Preload($content) {
	// Strip newline characters.
	$content = str_replace(chr(10), " ", $content);
	$content = str_replace(chr(13), " ", $content);
	// Replace single quotes.
	$content = str_replace(chr(145), chr(39), $content);
	$content = str_replace(chr(146), chr(39), $content);
	// Return the result.
	return $content;
}
// Send the preloaded content to the function.
$content = freeRTE_Preload("<i>This is some <b><br>preloaded</b> content</i>")

?>
<script type="text/javascript">
function showValue() {
    alert(trim(document.getElementById(rteFormName).value));
}
</script>

<form method="get">
<!-- Include the Free Rich Text Editor Runtime -->
<script src="../js/richtext.js" type="text/javascript" language="javascript"></script>
<!-- Include the Free Rich Text Editor Variables Page -->
<script src="../js/config.js" type="text/javascript" language="javascript"></script>
<!-- Initialise the editor -->
<table>
    <tr>
        <td>test</td>
        <td>test</td>
    </tr>
    <tr>
        <td>test</td>
        <td>
            <script>
            initRTE('<?= $content ?>', 'example.css');
            </script>
        </td>
    </tr>
</table>
<input type="button" onClick="showValue();" value="submit">
</form>