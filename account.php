<?PHP
require_once('lib/budget.lib.php');
?><HTML>
<HEAD>
	<TITLE>Luke's Budgeting Widget</TITLE>
	<LINK REL=StyleSheet HREF="style.css" TYPE="text/css"></LINK>
	<SCRIPT type="text/javascript" src="lib/event.lib.js"></SCRIPT>
	<SCRIPT type="text/javascript" src="lib/debug.lib.js"></SCRIPT>
	<SCRIPT type="text/javascript" src="lib/ajax.XML.lib.js"></SCRIPT>
	<SCRIPT type="text/javascript" src="lib/menu.lib.js"></SCRIPT>
	<SCRIPT type="text/javascript" src="lib/mousefollow.lib.js"></SCRIPT>
<SCRIPT>
// <!--
var debugVar = new Debug();							// Debug object that displays information in a div tag by id (defualts to "degubDivId")
var onloadFuncs = new OnloadEventRegistrar();			// Creates an object used to easily register functions to run onload
var eventRegistrar = new EventRegistrar();			// Creates an object used to easily register functions to run on any specified event by element (parameters are element as DOMElement, event as string, function as object)
onloadFuncs.registerFunction(on_page_load);			// This has to be after the function definition and any code that changes it... kind of a no-brainer.
var dropDowns = new Array();
var delay = 600;
var dropDownContents;
function on_page_load(e)
{
	var dropDownMenuDiv = document.getElementById("dropDownMenu");
	dropDownContents = dropDownMenuDiv.cloneNode(true);
	deleteElement(dropDownMenuDiv);
	debugVar.assignElement("debugDiv");
	//debugVar.dumpToDebug("page loaded...");
	document.onmousemove = follow;
}
function changeValue(e)
{
	var e = (window.event)?window.event:arguments[0];
	var target = e.target;				// DOM standard event model
	if(!target)	target = e.srcElement;	// IE event model
	createDropDown(e, '', 'dropdown'+target.id, 'x');
	activeId = target.id;
	document.ddform.ddinput.value = target.firstChild.data;
	//alert(target.id);
}
function updateValue(table, column, value, id)
{
	var url = '/~luke/budget/ajax.xml.php?table=' + encodeURI(table) + '&column=' + encodeURI(column) + '&value=' + encodeURI(value) + '&id=' + encodeURI(id);
	var xmldoc = XML.load(url);
	var xmlnodes = xmldoc.getElementsByTagName("newvalue");
	if(xmlnodes.item(0).firstChild.data)
		var newvalue = xmlnodes.item(0).firstChild.data;
	else
	{
		debugVar.dumpToDebug(xmlnodes.item(0).tagName + "\n");
		var newvalue = xmlnodes.item(0).firstChild.data;
	}
	var referrernode = xmldoc.getElementsByTagName("referrer");
	var referrer = referrernode.item(0).firstChild.data;
	//debugVar.dumpToDebug(newvalue + '\n' + typeof xmlnodes + '\n' + xmlnodes.length + '\n' + url + '\n' + referrer);
	return newvalue;
}
var activeId = 0;
function changeCategory(e)
{
	var e = (window.event)?window.event:arguments[0];
	var target = e.target;				// DOM standard event model
	if(!target)	target = e.srcElement;	// IE event model
	updateElement = document.getElementById(activeId);
	var newValue = updateValue('budget_accounts', document.ddform.ddinput.getAttribute("column"), document.ddform.ddinput.value, document.ddform.ddinput.getAttribute("accountid"));
	var newTextNode = document.createTextNode(newValue)
	updateElement.replaceChild(newTextNode, updateElement.firstChild);
	destroyNow = true;
	destroyDropDown();
}
function stopRKey(e)
{
	var e = (window.event)?window.event:arguments[0];
	var target = e.target;				// DOM standard event model
	if(!target)	target = e.srcElement;	// IE event model
	if((e.keyCode == 13) && (target.type=="text"))
	{
		changeCategory(e);
		return false;
	}
}
document.onkeypress = stopRKey;
// -->
</SCRIPT>
</HEAD>
<BODY>
<DIV id="mousefollowdiv" style="position: absolute; border: 1px dotted blue; background-color: lightblue;"><DIV id="debugDiv"></DIV>&nbsp;</DIV>
<DIV class="body_div" align="center">
<?PHP
echo topMenu(false, "Account Settings");
$account_balances = "";
$balance_query = mysql_query("select sum(budget_transactions.amount) as balance, budget_accounts.account_name from budget_transactions, budget_accounts where budget_transactions.accountid = budget_accounts.id group by accountid;");
$account_balances = "";
$balance = 0;
while(false !== ($balance_row = mysql_fetch_array($balance_query)))
{
	$account_balances .= "<H3>".$balance_row['account_name']." ".money("%(n", $balance_row['balance'])."</H3>\n";
	$balance += $balance_row['balance'];
}
echo outputTable("<H2>Total Balance is ".money("%(n", $balance)."</H2>\n".$account_balances);
echo "<br>\n";
$account_admin = "<H3>Account Information</H3>\n";
$account_admin .= "<TABLE class=\"whitebox\">\n";
$account_admin .= "<THEAD>\n";
$account_admin .= "<TR>\n";
$account_admin .= "\t<TD><div>Account Name</div></TD>\n";
$account_admin .= "\t<TD><div>Account Number</div></TD>\n";
$account_admin .= "</TR>\n";
$account_admin .= "</THEAD>\n";
$account_admin .= "<TBODY>\n";
$account_query = mysql_query("select * from budget_accounts where userid = ".$_SESSION['userid'].";");
if(mysql_error() != "")
	echo outputTable(mysql_error());
while(false !== ($row=mysql_fetch_array($account_query)))
{
	$account_admin .= "<TR>\n";
	$account_admin .= "\t<TD><div id=\"accname".$row['id']."\" onclick=\"changeValue(event);\">".$row['account_name']."</div></TD>\n";
	$account_admin .= "\t<TD><div id=\"accnum".$row['id']."\" onclick=\"changeValue(event);\">".$row['account_number']."</div></TD>\n";
	$account_admin .= "</TR>\n";
}
$account_admin .= "</TBODY>\n";
$account_admin .= "</TABLE>\n";
echo outputTable($account_admin);
?>
</DIV>
<DIV id="dropDownMenu"><div id="closeX" style="float: right; text-align: right; font-size: 6pt; color: white; cursor: pointer;" onclick="destroyNow = true;destroyDropDown();">X</div><FORM name="ddform" style="margin:0px;padding:0px;"><INPUT type="text" name="ddinput" onblur="changeCategory(event);"></FORM></DIV>
</BODY>
</HTML>