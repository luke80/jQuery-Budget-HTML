<?PHP
function formatSearchResultRow($row_string, $highlightStrings, $shorten=true, $returnAlways=false)
{
	$valueOut = $row_string;
	
	$anchorRegExp = '/\<a\s([^h]*)href=[\'\"]?(https?:\/\/|[\s]?)([^\'\"]+)[\'\"]?[^\>]\>(.+)\<\/a\>/iU';
	$linkData = array();
	preg_match_all($anchorRegExp, $valueOut, $linkData);
	/*
	if(trim($linkData[0][0]) != "")
	{
		echo "<pre>";
		print_r($linkData);
		echo "</pre>\n";
	}
	//$valueOut = preg_replace('/(\>|^)([^\<]*)(https?:\/\/|[\s\'\"]|^)(([\S]+\.(com|org|net|gov|edu|us|info|uk|mil))([\/\\][^\s\>\<\'\"]*)?)[$\s\'\"]/i', "$2<span style=\"background-color: yellow;\">$4</span>", $valueOut);
	//$valueOut = preg_replace('/(\>|^|:::)([^\<]*)(https?:\/\/|[\'\"]?|^)(([\d\w\.-]+\.(com|org|net|gov|edu|us|info|uk|mil))[\/]?[\S]*)($|\s|[\'\"])/iU', "$1$2<a style=\"background-color: yellow;\" href=\"http://$4\" target=\"_newWindow\">$5</a>", $valueOut);
	*/
	$valueOut = preg_replace($anchorRegExp, "<a href=\"http://$3\" target=\"_newWindow".date("Hisu").rand(10,99)."\">$4</a>", $valueOut);
	$valueOut = preg_replace('/(\>|^|:::)([^\<]*)(https?:\/\/|[\s]?|^)(([\d\w\.-]+\.(com|org|net|gov|edu|us|info|uk|mil))[\/]?[^\s\<]*)($|[^\<]*\<\/?[^a][^\s][^\>]\>)/iU', "$1$2<a href=\"http://$4\" target=\"_newWindow".date("Hisu").rand(10,99)."\">$5</a>$7", $valueOut);
	$valueOut = preg_replace('/:::/', "", $valueOut);
	
	foreach($highlightStrings as $match)
	{
		$reg_exp = '/((^|\>)[^\<]*)('.preg_replace('/[\^\(\)\<\>\[\{\]\}\\\|\?\.\+\&\$]/i', ".", $match).'(s|\'s)?)/iU';
		$max_loop = 75;
		$loop_count = 0;
		while(preg_match($reg_exp, $valueOut) === 1 && $loop_count <= $max_loop)
		{
			$valueOut = preg_replace($reg_exp, "$1<span class=searchResultMatch>$3</span>", $valueOut);		//	"(<i>".$resfield[$row['restype']][$label][0]."</i>)".
			$loop_count++;
		}
		//if(preg_match('/('.$reg_exp_format.')/i', $valueOut) && ($valueOut == $row_string))
		//	$valueOut .= preg_replace('/((^|\>)[^\<]*)('.$reg_exp_format.'(s|\'s)?)/i', "$1<span class=searchResultMatch>$3</span>", $valueOut);		//	"(<i>".$resfield[$row['restype']][$label][0]."</i>)".
		//else if(preg_match('/('.$reg_exp_format.')/i', $value) && $valueOut != $row_string)
		//	$valueOut = preg_replace('/((^|\>)[^\<]*)('.$reg_exp_format.'(s|\'s)?)/i', "$1<span class=searchResultMatch>$3</span>", $valueOut);
	}
	if($shorten)
	{
		$valueOut = preg_replace('/(^|\>)([^\<]{40,55}\b\s?)([^\<]{25,})(\s?\b[^\<]{10,20}\<|$)/', "$1<span>$2</span>...$4", $valueOut);
		$valueOut = preg_replace('/(^|\>)([^\<]{25,}\b\s?)([^\<]{20,35})(\s?\b[^\<]{10,20}\<|$)/', "$1...<span>$3</span>$4", $valueOut);
	}
//	$valueOut = preg_replace('/\>/', '&gt;', $valueOut);
//	$valueOut = preg_replace('/\</', '&lt;', $valueOut);
	if($valueOut != $row_string || $returnAlways)
		return $valueOut;
	else
		return "";
}
?>