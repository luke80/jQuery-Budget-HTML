<?PHP
session_start();
setlocale(LC_MONETARY, 'en_US');
if(!isset($_SESSION['userid']))
{
	include("login.php");
	exit;
}

function money($format, $amount)
{
	$amount_out = money_format($format, $amount);
	$amount_out = (($amount < 0)?"<span class=\"negative_amount\">":"<span class=\"positive_amount\">").$amount_out.(($amount < 0)?"</span>":"</span>");
	return $amount_out;
}
function getCategory($transaction)
{
	$category = false;
	$transaction_string = implode(";", $transaction);
	$category_sql = "select budget_category_defaultrules.*, budget_categories.category_name from budget_category_defaultrules, budget_categories where budget_categories.id = budget_category_defaultrules.categoryid and budget_category_defaultrules.userid = ".$_SESSION['userid'].";";
	$category_query = mysql_query($category_sql);
	$search_categories = array();
	while(false !== ($row=mysql_fetch_array($category_query)))
	{
		$category_name = preg_replace('/\'/', '\\\'', $row['category_name']);
		if(!is_array($search_categories[$category_name]))
			$search_categories[$category_name] = array();
		if(substr($row['rule_def'], 0, 1) == "/")
			$search_categories[$category_name][] = $row['rule_def'];
		else if($row['rule_def'] != "")
			array_unshift($search_categories[$category_name], $row['rule_def']);
	}
	$search_categories['Other'] = array();
	//echo "<pre>";
	//print_r($search_categories);
	//echo "</pre>";
	$search_categories2 = array(
		"Clothing" => array('/TARGET/i', '/WHITEHSE/i', '/NORDSTROM/i', '/FOREVER/i', '/APPAREL/i', '/OLD NAVY/i', '/LDS SPAN/i', '/JOURNEYS/i', '/DRY CLEAN/i', '/AMERICAN EAGLE/i', '/;(PANDA){0} EXPRESS /i', '/KOHLS/i', '/H \& M/i', '/TAILOR/i', '/GAP /i', '/VICTORIAS/i', '/MACEYS /i', '/;\s*LDS/i', '/DOWNEAST/i', '/HAYDENHA/i', '/CHARLOTTE RUSS/i', '/WEAR/i'),
		"Food" => array('/SMITHS /i', '/ALBERTSON/i', '/COSTCO/i'),
		"Travel" => array('/MAVERIK/i', '/GEICO/i', '/CHECKER /i', '/CHEVRON/i', '/AutoZone /i', '/7-ELEVEN/i', '/DMV OFF/i', '/STORRS /i', '/SINCLAIR/i', '/GAS N GO/i', '/WALKER/i', '/AMPCO/i'),
		"Home Improvement" => array('/IKEA/i', '/PIER 1/i', '/KINKOS/i', '/MICHAELS /i', '/FLOWER PATCH/i', '/JOANN STORE/i'),
		"Health" => array('/HEAL/i', '/BEAUTY/i', '/BEDBATH\&BEYOND/i', '/BATH \& BODY/i', '/WALGREEN/i', '/OBSTETRIC/i', '/BEACH /i'),
		"Work Related" => array('/COMCAST/i', '/BEST BUY/i'),
		"School" => array('/Tuition/i'),
		"Entertainment" => array('/TERMINAL /i', '/CINEMA/i', '/BASKIN ROBBINS/i', '/MCDONALDS /i', '/WAL-MART/i', '/BLOCKBUSTER/i', '/BARNESNOBLE/i', '/OUTBACK/i', '/BORDERS/i', '/OSAKA /i', '/SUB ZERO/i', '/CINEMARK/i', '/REGAL/i', '/PANDA EX/i', '/JASONS DELI/i', '/KRISPY KREME/i', '/CARLS JR/i', '/PRETZEL/i', '/IHOP/i', '/SHOOTS/i', '/PAPA JOHN/i', '/GLORIAS LITTLE/i', '/CARMIKE/i', '/SUBWAY/i', '/DELI/i', '/BOMBAY HOUSE/i', '/DRIVE-IN/i', '/GURUS/i', '/ShopKo/i', '/CAFE RIO/i', '/GELATO/i', '/MAGAZINE/i', '/VIDEO/i', '/JOE BANDIDOS/i', '/STORRS/i', '/MOVIE/i', '/RESTAURAN/i', '/PIZZA/i', '/FYE/i', '/ICEBERG/i'),
		"Savings" => array('/BANKING TRANSFER/i'),
		"Rent" => array('%Amount% > -800 && %Amount% < -700', '/CHECK/i'),
		"Car Loan" => array('%Amount% > -300 && %Amount% < -200', '/(CHECK|BAAG)/i'),
		"Luke\'s Job" => array('%Amount% > 700'),
		"Brittany\'s Job" => array('%Amount% > 100 && %Amount% < 700'),
		"Utilities" => array('%Amount% > -100', '/CHECK/i'),
		"Donations" => array(),
		"Gifts" => array(),
		"Other" => array()
	);
	//echo "<pre>";
	//print_r($search_categories2);
	//echo "</pre>";
	foreach($search_categories as $category => $regexp_array)
	{
		foreach($regexp_array as $regexp)
			if(substr($regexp, 0, 1) == "/")
			{
				if(preg_match($regexp, $transaction_string) != 0)
					return $category;
			}else
			{
				$condition = preg_replace('/\%([^\%]+)\%/', "\$transaction['".'${1}'."']", $regexp);
				eval("\$condition = ".$condition.";")."<br>\n";
				//echo $condition."\n";
				if($condition)
				{
					$all_match = true;
					foreach($regexp_array as $regexp2)
						if(substr($regexp2, 0, 1) == "/")
							if(preg_match($regexp2, $transaction_string) == 0)
								$all_match = false;
					if($all_match)
						return $category;
				}
				break;
			}
	}
	return $category;
}
function translateCategoryId($id) {
	$category_sql = "SELECT BC.* FROM budget_categories AS BC WHERE BC.userid = ".$_SESSION['userid']." AND BC.id = ".$id;
	$category_query = mysql_query($category_sql);
	while(false !== ($row=mysql_fetch_array($category_query)))
		$category = $row['category_name'];
	return $category;
}
function outputTable($html_string)
{
	return "<div class=\"whitebox\">
<table class=\"whitebox\" align=\"center\">
	<tr>
		<td class=\"tl\">&nbsp; &nbsp; </td>
		<td class=\"tm\">&nbsp; &nbsp; </td>
		<td class=\"tr\">&nbsp; &nbsp; </td>
	</tr>
	<tr>
		<td class=\"ml\">&nbsp; &nbsp; </td>
		<td class=\"mm\"><div class=\"whitebox_contents\">".$html_string."</div></td>
		<td class=\"mr\">&nbsp; &nbsp; </td>
	</tr>
	<tr>
		<td class=\"bl\"><em>&nbsp;</em></td>
		<td class=\"bm\">&nbsp; &nbsp; </td>
		<td class=\"br\"><em>&nbsp;</em></td>
	</tr>
</table>
</div>
";
}
function topMenu($incuploadmenu = false, $title = "Accounts")
{
	$menuString = "<H2><TABLE class=\"whitebox\"><TR><TD><a href=\"index.php\">View Budget</a></TD><TD><a href=\"account.php\">Edit Account Settings</a></TD><TD><a href=\"categories.php\">Edit Category Settings</a></TD><TD><a href=\"profile.php\">Edit Login/Profile Settings</a></TD></TR></TABLE></H2>\n";
	$titleArea = "<H1>".$_SESSION['user_name']."'s ".$title."</H1>\n";
	$titleArea .= $menuString;
	if($incuploadmenu)
	{
		$uploadMenu = "<FORM method=\"POST\" enctype=\"multipart/form-data\">\n<INPUT type=\"file\" name=\"transactioncsv\" />\n";
		$uploadMenu .= "<SELECT name=\"accountid\">\n";
		$account_query = mysql_query("select * from budget_accounts where userid = ".$_SESSION['userid'].";");
		while(false !== ($row=mysql_fetch_array($account_query)))
		{
			$uploadMenu .= "\t<OPTION value=\"".$row['id']."\">".$row['account_name']." [".$row['account_number']."]"."</OPTION>\n";
		}
		$uploadMenu .= "</SELECT>\n";
		$uploadMenu .= "<INPUT type=\"submit\" value=\" Upload Transactions \" />\n</FORM>";
		$titleArea .= $uploadMenu;
	}
	$titleArea = outputTable($titleArea);
	return $titleArea."\n<br>\n";
}
/*
$header_array = array(
	'Host'				=>	'www4.usbank.com',
	'User-Agent'		=>	'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8',
	'Accept'			=>	'text/html,application/xhtml+xml,application/xml;q=0.9,* / *;q=0.8',
	'Accept-Language'	=>	'en-us,en;q=0.5',
	'Accept-Encoding'	=>	'gzip,deflate',
	'Accept-Charset'	=>	'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
	'Keep-Alive'		=>	'300',
	'Connection'		=>	'keep-alive',
	'Referer'			=>	'https://www4.usbank.com/internetBanking/RequestRouter?requestCmdId=DisplayLoginPage'
);
function curl_request($url, $authenticate="", $content_type="text/html", $referrer="")
{
	$separateCookieSessions = false;
	if($authenticate)
	{
		$headers = array(
			
		);
		$cr = curl_init($url);
		curl_setopt($cr, CURLOPT_RETURNTRANSFER, true);									// Get returned value as string (don"t put to screen)
		curl_setopt($cr, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.6) Gecko/2009011913 Firefox/3.0.6");		// Spoof the user agent
		curl_setopt($cr, CURLOPT_COOKIEJAR, "cookie".(($separateCookieSessions)?"_".session_id():"").".txt");				// Use cookie.txt for STORING cookies
		curl_setopt($cr, CURLOPT_COOKIEFILE, "cookie".(($separateCookieSessions)?"_".session_id():"").".txt");			// Use cookie.txt for STORING cookies
		curl_setopt($cr, CURLOPT_HEADER, false);										// Make sure we don't get the header.
		curl_setopt($cr, CURLINFO_HEADER_OUT, true);									// -- unless we ask for info
		curl_setopt($cr, CURLOPT_FOLLOWLOCATION, true);								// If the page moved (or is authenticate) follow it
		curl_setopt($cr, CURLOPT_FRESH_CONNECT, true);		    						// Use cookie.txt for STORING cookies
		curl_setopt($cr, CURLOPT_POST, true);											// Tell curl that we are posting data
		curl_setopt($cr, CURLOPT_POSTFIELDS, $authenticate);							// Post the data in the array above
		if(count($header_array) > 0)
			curl_setopt($cr, CURLOPT_HTTPHEADER, $header_array);
		if($referrer != "")
			curl_setopt($cr, CURLOPT_REFERER, $referrer);
		// curl_setopt($cr, CURLOPT_FRESH_CONNECT, true);		    					// probably don't freshconnect anymore
		$output = curl_exec($cr);
		curl_close($cr);
		//$header_array = curl_getinfo($cr,CURLINFO_HEADER_OUT);
		//var_dump(curl_getinfo($cr,CURLINFO_HEADER_OUT));
	}else
	{
		//	echo "auth string ".$authenticate;
		
		$cr = curl_init($url);
		curl_setopt($cr, CURLOPT_RETURNTRANSFER, true);									// Get returned value as string (don"t put to screen)
		curl_setopt($cr, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.6) Gecko/2009011913 Firefox/3.0.6");		// Spoof the user agent
		if($authenticate !== "nocookies")
		{
			curl_setopt($cr, CURLOPT_COOKIEJAR, "cookie".(($separateCookieSessions)?"_".session_id():"").".txt");			// Use cookie.txt for STORING cookies
			curl_setopt($cr, CURLOPT_COOKIEFILE, "cookie".(($separateCookieSessions)?"_".session_id():"").".txt");		// Use cookie.txt for STORING cookies
		}
		curl_setopt($cr, CURLOPT_HEADER, false);										// Make sure we don't get the header.
		curl_setopt($cr, CURLINFO_HEADER_OUT, true);									// -- unless we ask for info
		curl_setopt($cr, CURLOPT_FOLLOWLOCATION, true);									// If the page moved (or is authenticate) follow it
		if($content_type != "text/html")
			curl_setopt($cr, CURLOPT_HTTPHEADERS,array('Content-Type: '.$content_type));	//  text/xml;charset=UTF-8')); 
		$output = curl_exec($cr);
		curl_close($cr);
	}
	return $output;
}
/*
It doesn't work to log into usbank.  They have a lot of complicated security to block just this type of thing.
$request_login = curl_request('https://www4.usbank.com/internetBanking/RequestRouter?requestCmdId=DisplayLoginPage');
echo $request_login."<BR>\n\n\n\n";

$LOGIN_ARRAY = array(
	'MACHINEATTR'	=>	'colorDepth=16|width=1600|height=1200|availWidth=1600|availHeight=1166|platform=Win32|javaEnabled=Yes|userAgent=Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9',
	'NONCE'			=> '',
	'USERID	'		=>	'lrebarchik0929',
	'doubleclick'	=>	'2',
	'requestCmdId'	=>	'VALIDATEID'
);
$submit_login = curl_request('https://www4.usbank.com/internetBanking/RequestRouter', $LOGIN_ARRAY, "text/html", 'https://www4.usbank.com/internetBanking/RequestRouter?requestCmdId=DisplayLoginPage');
echo "--".$submit_login."<BR>\n\n\n\n";
preg_match('/\<input type=hidden name="LOGINSESSIONID" value=\'([^\']+)\'\>/i', $submit_login, $matches);
print_r($matches);


exit;
$loginsessid = 
$LOGIN_ARRAY = array(
	'LOGINSESSIONID'	=>	'KUTeM1dPq1-bpcBLfG1qEva',
	'PSWD'	=>	'it5n07you',
	'USEDSINGLEACCESSCODE'	=>	'null',
	'USERID'	=>	'lrebarchik0929',
	'doubleclick'	=>	'2',
	'requestCmdId'	=>	'Logon'
);
$submit_login = curl_request('https://www4.usbank.com/internetBanking/RequestRouter', $LOGIN_ARRAY);


//	'requestCmdId'	=>	'exit'

exit;
*/
?>
