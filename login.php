<?PHP
if(isset($_POST['login']))
{
	$sql_logincheck = "select * from budget_users where user_login = '".$_POST['login']."' and user_pass = '".$_POST['pass']."';";
	$logincheck_query = mysql_query($sql_logincheck);
	if(mysql_num_rows($logincheck_query) == 1)
	{
		$row = mysql_fetch_array($logincheck_query);
		$_SESSION['userid'] = $row['id'];
		$_SESSION['user_name'] = $row['user_name'];
		$_SESSION['user_email'] = $row['user_email'];
		$_SESSION['accountid'] = "all";
		?><HTML>
<HEAD>
	<TITLE>Luke's Budgeting Widget</TITLE>
	<LINK REL=StyleSheet HREF="style.css" TYPE="text/css"></LINK>
	<META http-equiv="refresh" content="0"/>
</HEAD>
<BODY><?PHP
		$thank_you = "<H2>Thank you for logging in.</H2>\n";
		$thank_you .= "<H3>Loading your data...</H3>\n";
		$thank_you = outputTable($thank_you);
		echo $thank_you;
		?></BODY>
</HTML>
		<?PHP
	}
}
if(!isset($_SESSION['userid']))
{
	?><HTML>
<HEAD>
	<TITLE>Luke's Budgeting Widget</TITLE>
	<LINK REL=StyleSheet HREF="style.css" TYPE="text/css"></LINK>
</HEAD>
<BODY><?PHP
	//	$_SESSION['userid'] = 1;
	//	$_SESSION['user_name'] = "Brittany & Luke Rebarchik";
	//	$_SESSION['accountid'] = "all";
	$loginform	=	"<TABLE>\n<TR>\n<FORM method=\"POST\">\n";
	$loginform	.=	"\t<TD>Login:</TD>\n";
	$loginform	.=	"\t<TD><INPUT type=\"text\" name=\"login\" class=\"loginform\"/></TD>\n";
	$loginform	.=	"</TR>\n";
	$loginform	.=	"<TR>\n";
	$loginform	.=	"\t<TD>Password:</TD>\n";
	$loginform	.=	"\t<TD><INPUT type=\"password\" name=\"pass\" class=\"loginform\"/></TD>\n";
	$loginform	.=	"</TR>\n";
	$loginform	.=	"<TR>\n";
	$loginform	.=	"\t<TD colspan=2 align=\"center\"><input type=\"submit\" value=\" log in \" class=\"loginform\"/></TD>\n";
	$loginform	.=	"</FORM>\n</TR>\n";
	$loginform	.=	"</TABLE>\n";
	$loginform = outputTable($loginform);
	echo $loginform;
	?></BODY>
</HTML>
	<?PHP
}
?>