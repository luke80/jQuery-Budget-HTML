<?PHP
header('Content-type: text/xml');

if(substr($_SERVER['HTTP_REFERER'], 0, 35) == "http://www.rworld.net/~luke/budget/")
{
	mysql_connect("localhost", "luke", "***");
	mysql_select_db("luke");
	$update_sql = "update ".$_GET['table']." set ".$_GET['column']." = ".((!is_numeric($_GET['value']))?"'".$_GET['value']."'":$_GET['value'])." where id = ".$_GET['id'].";";
	$select_sql = "select ".$_GET['column']." from ".$_GET['table']." where id = ".$_GET['id'].";";
	$errors = "";
	$select_old_query = mysql_query($select_sql);
	$errors .= mysql_error()."\n";
	$query = mysql_query($update_sql);
	$errors .= mysql_error()."\n";
	$select_new_query = mysql_query($select_sql);
	$errors .= mysql_error()."\n";
	$row = mysql_fetch_array($select_old_query);
	$old_value = $row[$_GET['column']];
	$row = mysql_fetch_array($select_new_query);
	$new_value = $row[$_GET['column']];
}
//	<referrer>< ?PHP echo $_SERVER['HTTP_REFERER']; ? ></referrer>
?><page>
	<oldvalue><?PHP echo $old_value; ?></oldvalue>
	<newvalue><?PHP echo $new_value; ?></newvalue>
	<errors><?PHP echo $errors; ?></errors>
</page>