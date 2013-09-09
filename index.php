<?PHP
require_once('lib/budget.lib.php');
if(!function_exists("money_format"))
	require_once('lib/money_format.lib.php');
?><HTML>
<HEAD>
	<TITLE>Luke's Budgeting Widget</TITLE>
	<LINK REL=StyleSheet HREF="style.css" TYPE="text/css"></LINK>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<SCRIPT type="text/javascript" src="lib/event.lib.js"></SCRIPT>
	<SCRIPT type="text/javascript" src="lib/debug.lib.js"></SCRIPT>
	<SCRIPT type="text/javascript" src="lib/ajax.XML.lib.js"></SCRIPT>
	<SCRIPT type="text/javascript" src="lib/menu.lib.js"></SCRIPT>
	<SCRIPT type="text/javascript" src="lib/mousefollow.lib.js"></SCRIPT>
<SCRIPT>
// <!--
var debugVar = new Debug();							// Debug object that displays information in a div tag by id (defualts to "degubDivId")
//var onloadFuncs = new OnloadEventRegistrar();			// Creates an object used to easily register functions to run onload
//var eventRegistrar = new EventRegistrar();			// Creates an object used to easily register functions to run on any specified event by element (parameters are element as DOMElement, event as string, function as object)
$(document).ready(function() {
	var dropDownMenuDiv = $("#dropDownMenu");
	dropDownContents = dropDownMenuDiv.clone(true);
	dropDownMenuDiv.remove();
	debugVar.assignElement("debugDiv");
	//debugVar.dumpToDebug("page loaded...");
	$(document).on('mousemove', follow);
});
//onloadFuncs.registerFunction(on_page_load);			// This has to be after the function definition and any code that changes it... kind of a no-brainer.
var dropDowns = new Array();
var delay = 600;
var dropDownContents;
function mouseoverCategory(e)
{
	var e = (window.event)?window.event:arguments[0];
	var target = e.target;				// DOM standard event model
	if(!target)	target = e.srcElement;	// IE event model
	createDropDown(e, '', 'dropdown'+target.id);
	activeId = target.getAttribute('transId');
	//alert(target.id);
}
function updateValue(table, column, value, id)
{
	var url = '/~luke/budget/ajax.xml.php?table=' + encodeURI(table) + '&column=' + encodeURI(column) + '&value=' + encodeURI(value) + '&id=' + encodeURI(id);
	var xmldoc = XML.load(url);
	var xmlnodes = xmldoc.getElementsByTagName("newvalue");
	var newvalue = xmlnodes.item(0).firstChild.data;
	var referrernode = xmldoc.getElementsByTagName("referrer");
	var referrer = referrernode.item(0).firstChild.data;
	//debugVar.dumpToDebug(newvalue + '\n' + typeof xmlnodes + '\n' + xmlnodes.length + '\n' + url + '\n' + referrer);
	return newvalue;
}
var activeId = 0;
function changeCategory()
{
	var e = (window.event)?window.event:arguments[0];
	var target = e.target;				// DOM standard event model
	if(!target)	target = e.srcElement;	// IE event model
	var category = target.firstChild.data.replace('\'', '\\\'');
	updateElement = document.getElementById('transcategory'+activeId);
	var newCategory = updateValue('budget_transactions', 'budget_category', category, activeId);
	var catTextNode = document.createTextNode(newCategory)
	updateElement.replaceChild(catTextNode, updateElement.firstChild);
	destroyNow = true;
	destroyDropDown();
}

// -->
</SCRIPT>
</HEAD>
<BODY>
<DIV id="mousefollowdiv" style="position: absolute; border: 1px dotted blue; background-color: lightblue;"><DIV id="debugDiv"></DIV>&nbsp;</DIV>
<DIV class="body_div" align="center">
<?PHP
echo topMenu(true);
if(isset($_FILES['transactioncsv']))
{
	$uploaddir = '/home/luke/www/budget/uploads/';
	$uploadfile = $uploaddir . basename($_FILES['transactioncsv']['name']);
	if (move_uploaded_file($_FILES['transactioncsv']['tmp_name'], $uploadfile))
		echo outputTable("File is valid, and was successfully uploaded.\n");
	else
		echo outputTable("Invalid file.  Not uploaded.\n");
	$file_handle = fopen($uploadfile, "r");
	$file_contents = preg_replace('/[\\\'\"]/', '', fread($file_handle, filesize($uploadfile)));
	fclose($file_handle);
	$file_array = explode("\n", $file_contents);
	$file_array_keys = explode(",", $file_array[0]);
	array_shift($file_array);
	for($i=0;$i<count($file_array);$i++)
	{
		$file_array[$i] = explode(",", $file_array[$i]);
		for($j=0;$j<count($file_array_keys);$j++)
		{
			$file_array[$i][trim($file_array_keys[$j])] = $file_array[$i][$j];
			unset($file_array[$i][$j]);
		}
	}
	//print_r($file_array);
	foreach($file_array as $transaction)
	{
		$date = strtotime($transaction['Date']);
		if($date != strtotime(""))
		{
			$amount = preg_replace('/^0+/', '', $transaction['Amount']);
			$trans_type = preg_replace('/^0+/', '', $transaction['Transaction']);
			$trans_name = preg_replace('/^0+/', '', $transaction['Name']);
			$trans_memo = preg_replace('/^0+/', '', preg_replace('/Download from usbank\.com\./', '', $transaction['Memo']));
			$budget_category = getCategory($transaction);
			//print_r($transaction);
			//echo $budget_category."\n";
			$accountid = $_POST['accountid'];
			$sql = "insert into budget_transactions(date, amount, transaction_type, transaction_name, transaction_memo, budget_category, accountid) values(".$date.", ".$amount.", '".$trans_type."', '".$trans_name."', '".$trans_memo."', '".$budget_category."', ".$accountid.");";
			//echo $sql."\n\n";
			mysql_query($sql);
		}
	}
}
//print_r($_FILES);
$category_balance = array();
$category_query = mysql_query("select budget_transactions.budget_category, sum(budget_transactions.amount) as balance from budget_transactions, budget_accounts where budget_transactions.accountid = budget_accounts.id and budget_accounts.userid = ".$_SESSION['userid'].(($_SESSION['accountid'] != "all")?" and budget_transactions.accountid = ".$_SESSION['accountid']:"")." group by budget_transactions.budget_category order by balance asc;");
if(mysql_error() != "")
	echo outputTable(mysql_error());
$monthly_category_balance = array();
$monthly_category_query = mysql_query("select budget_category, sum(amount) as balance, CONCAT( YEAR(FROM_UNIXTIME(date)), '-', MONTH(FROM_UNIXTIME(date)), '-1') as month_year, count(id) as transcation_count from budget_transactions group by budget_category, month_year order by month_year asc;");
$all_months = array();
$all_months_query = mysql_query("select CONCAT(YEAR(FROM_UNIXTIME(date)), '-', MONTH(FROM_UNIXTIME(date)), '-1') as month_year from budget_transactions group by month_year order by month_year asc;");
echo mysql_error();
while(false !== $row=mysql_fetch_array($all_months_query))
{
	$all_months[strtotime($row['month_year'])] = $row['month_year'];
}
//print_r($all_months);
setlocale(LC_MONETARY, 'en_US');
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
$category_table = "<H2>Monthly Spending by Category</H2><TABLE class=\"outputTable\">
<THEAD>
<TR>
	<TH>Category</TH>
	<TH>Total Spent</TH>
";
$last_month = "";
$monthly_category_totals = array();
$monthly_totals = array();
while(false !== $row=mysql_fetch_array($monthly_category_query))
{
	if(!is_array($monthly_category_totals[$row['budget_category']]))
		$monthly_category_totals[$row['budget_category']] = array();
	if(!isset($monthly_totals[$row['month_year']]))
		$monthly_totals[$row['month_year']] = 0;
	$monthly_category_totals[$row['budget_category']][$row['month_year']] = $row['balance'];
	$monthly_totals[$row['month_year']] += $row['balance'];
	if($row['month_year'] != $last_month)
		$category_table .= "\t<TH>".date("F", strtotime($row['month_year']))."</TH>\n";
	$last_month = $row['month_year'];
}
$category_table .= "</TR>
</THEAD>
<TBODY>";
while(false !== $row=mysql_fetch_array($category_query))
{
	$category_balance[$row['budget_category']] = $row['balance'];
	$category_table .= "<TR>\n\t<TD>".$row['budget_category']."</TD>\n\t<TD>".money("%(n", $row['balance'])."</TD>\n";
	mysql_data_seek($monthly_category_query, 0);
	$last_month = "";
	foreach($all_months as $timestamp => $month_year)
	{
		$category_table .= "\t<TD>".money("%(n", $monthly_category_totals[$row['budget_category']][$month_year])."</TD>\n";
	}
	$category_table .= "</TR>\n";
}	
$category_table .= "<TR>
	<TD>Net Change</TD>
	<TD>".money("%(n", $balance)."</TD>\n";
foreach($monthly_totals as $month_year => $total)
{
	$category_table .= "\t<TD>".money("%(n", $total)."</TD>\n";
}	
$category_table .= "
</TR>\n";
$category_table .= "</TBODY>
</TABLE>";
echo outputTable($category_table);
$transaction_table = "<TABLE class=\"outputTable\">
<THEAD>
<TR>
	<TH>Date</TH>
	<TH>Amount</TH>
	<TH>Balance</TH>
	<TH>Category</TH>
	<TH>Name</TH>
	<TH>Account</TH>
	<TH>Default Category</TH>
</TR>
</THEAD>
<TBODY>";
$transactions_query = mysql_query("select budget_transactions.*, budget_accounts.account_name from budget_transactions, budget_accounts where budget_accounts.id = budget_transactions.accountid and budget_accounts.userid = ".$_SESSION['userid']." order by date asc, amount desc;");
//echo $balance;
$balance = 0;
$transaction_table_rows = "";
while(false !== $row=mysql_fetch_array($transactions_query))
{
	$balance += $row['amount'];
	$transaction_table_rows	=	"<TR>
	<TD>".date("l F j", $row['date'])."</TD>
	<TD>".money("%(n", $row['amount'])."</TD>
	<TD>".money("%(n", ($balance))."</TD>
	<TD><div id=\"transcategory".$row['id']."\" transId=\"".$row['id']."\" onclick=\"mouseoverCategory(event);\" onmouseout=\"setTimeout('destroyDropDown(null)', delay);\">".((intval($row['budget_category']) > 0)?translateCategoryId($row['budget_category']):"--")."</a></TD>
	<TD>".$row['transaction_name']."</TD>
	<TD>".$row['account_name']."</TD>
	<TD>".getCategory(array($row['transaction_name']))."</TD>
</TR>\n".$transaction_table_rows;
}
$transaction_table .= $transaction_table_rows."</TBODY>
</TABLE>
";
echo outputTable($transaction_table);
$onclick = " onclick=\"changeCategory(event);\"";
$category_menu = "<div id=\"dropDownMenu\" class=\"menudiv\">"."<div ".$onclick.">".implode("</div>\n<div ".$onclick.">", array_keys($monthly_category_totals))."</div>"."</div>";
echo $category_menu;
?>
</DIV>
</BODY>
</HTML>
