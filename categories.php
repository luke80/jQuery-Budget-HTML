<?PHP
require_once('lib/budget.lib.php');
?><HTML>
<HEAD>
	<TITLE>Luke's Budgeting Widget</TITLE>
    <LINK REL=StyleSheet HREF="style.css" TYPE="text/css"></LINK>
</HEAD>
<BODY>
<DIV id="mousefollowdiv" style="position: absolute; border: 1px dotted blue; background-color: lightblue;"><DIV id="debugDiv"></DIV>&nbsp;</DIV>
<DIV class="body_div" align="center">
<?PHP
echo topMenu(false, "Categories");
?>
</DIV>
</BODY>
</HTML>