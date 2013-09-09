<?PHP

function format_string_for_query($string)
{
	return preg_replace('/\'/', '\\\'', $string);
}
function request_account_characters($character="",$player_id="")
{
	$output = curl_request("https://www.wowarmory.com/vault/character-select.xml");
	//echo preg_replace('/\</', "&lt;", preg_replace('/\>/', "&gt;", $output));
	//echo $output;
	$import_xml_array = xml2array($output);
	$insertSQL = array_to_insert($import_xml_array['page']);
	return array("insert_query" => $insertSQL, "xml_array" => $import_xml_array['page']);
}
function request_guild($realm, $guild, $character="",$player_id="")
{
	$realm_encoded = urlencode($realm);
	$guild_encoded = urlencode($guild);
	$output = curl_request("https://www.wowarmory.com/vault/guild-bank-contents.xml?r=".$realm_encoded."&n=".$guild_encoded."&loginType=com");
	//echo preg_replace('/\</', "&lt;", preg_replace('/\>/', "&gt;", $output));
	//echo $output;
	$import_xml_array = xml2array($output);
	//print_r($import_xml_array);
	$insertSQL = array_to_insert($import_xml_array['page']);
	return array("insert_query" => $insertSQL, "xml_array" => $import_xml_array['page']);
}
function table_format_guild_bank($xml_array)
{
	$table_out = "<table border=1>
		<tr>
			<td>Bank Slot</td>
			<td>Image and Name</td>
			<td>Quantity</td>
			<td>Type</td>
			<td>Subtype</td>
		</tr>\n";
	foreach($xml_array['guildBank']['items']['item'] as $item_array)
	{
		if(count($item_array) > 0)
		{
			//	http://www.wowarmory.com/wow-icons/_images/43x43/inv_scroll_05.png
			/*
			echo "<!--\n";
			print_r($xml_array['guildBank']);
			print_r($item_array['attr']['bag']);
			echo "-->\n";
			*/
			$image1_filename = $xml_array['guildBank']['bags']['bag'][$item_array['attr']['bag']]['attr']['icon'];
			$image2_filename = $item_array['attr']['icon'];
			if(!file_exists("/home/web/thedissident.us/images/new_icons/".$image1_filename.".png") && strlen($image1_filename) > 0)
				$filecopy = copy("http://www.wowarmory.com/wow-icons/_images/43x43/".$image1_filename.".png", "/home/web/thedissident.us/images/new_icons/".$image1_filename.".png");
			if(!file_exists("/home/web/thedissident.us/images/new_icons/".$image2_filename.".png") && strlen($image2_filename) > 0)
				$filecopy = copy("http://www.wowarmory.com/wow-icons/_images/43x43/".$image2_filename.".png", "/home/web/thedissident.us/images/new_icons/".$image2_filename.".png");
			$table_out .= "	<tr>
		<td><img src=\"/images/new_icons/".$image1_filename.".png\" height=40 width=40 border=0> ".$xml_array['guildBank']['bags']['bag'][$item_array['attr']['bag']]['attr']['name']."</td>
		<td><a href=\"http://www.wowhead.com/?item=".$item_array['attr']['id']."\" target=\"_new\"><img src=\"/images/new_icons/".$item_array['attr']['icon'].".png\" height=40 width=40 border=0> ".$item_array['attr']['name']."</a></td>
		<td>".$item_array['attr']['quantity']."</td>
		<td>".$item_array['attr']['type']."</td>
		<td>".$item_array['attr']['subtypeLoc']."</td>
		</tr>\n";
		}
	}
	$table_out .= "</table>\n";
	return $table_out;
}
function parse_json($file_string, $file_type="month")
{
	if($file_type == "month")
	{
		preg_match('/"events":\[(\{[^\]]+)\]/', $file_string, $events_strings);
		$events = preg_replace('/\{/', '', preg_replace('/\}/', '|', $events_strings[1]));
		$events = explode("|", $events);
		for($j=0;$j<count($events);$j++)
			eval("\$events[\$j] = array(".preg_replace('/\:/', ' => ', preg_replace('/\$/', '\\\$', preg_replace('/^,/', '', $events[$j]))).");");
		return $events;
	}
	if($file_type == "detail")
	{
		/*
		calendar.loadEventDetail({"summary":"Fire it up","calendarType":"player","start":1232154000243,"type"
:"raid","owner":"Myrianna","invites":[{"classId":5,"status":"confirmed","moderator":true,"invitee":"Myrianna"
,"id":1727114},{"classId":9,"status":"available","moderator":true,"invitee":"Fluxell","id":1727124},
{"classId":2,"status":"available","moderator":true,"invitee":"Rayoo","id":1727130},{"classId":8,"status"
:"available","moderator":false,"invitee":"Skyes","id":1727131},{"classId":5,"status":"available","moderator"
:false,"invitee":"Anoranta","id":1727117},{"classId":1,"status":"available","moderator":false,"invitee"
:"Mysticstitch","id":1727128},{"classId":1,"status":"available","moderator":false,"invitee":"Braddock"
,"id":1727118},{"classId":7,"status":"available","moderator":false,"invitee":"Neikki","id":1727129},
{"classId":3,"status":"declined","moderator":false,"invitee":"Dagness","id":1727123},{"classId":6,"status"
:"declined","moderator":false,"invitee":"Mirrada","id":1727127},{"classId":9,"status":"invited","moderator"
:false,"invitee":"Corisona","id":1727121},{"classId":1,"status":"invited","moderator":false,"invitee"
:"Hanzoro","id":1727125},{"classId":8,"status":"invited","moderator":false,"invitee":"Aenlic","id":1727115
},{"classId":11,"status":"invited","moderator":false,"invitee":"Crioknight","id":1727122},{"classId"
:3,"status":"invited","moderator":false,"invitee":"Helganorth","id":1727126},{"classId":4,"status":"invited"
,"moderator":false,"invitee":"Chuckelator","id":1727120},{"classId":3,"status":"invited","moderator"
:false,"invitee":"Amiiy","id":1727116},{"classId":7,"status":"invited","moderator":false,"invitee":"Caloiiaa"
,"id":1727119}],"id":538325,"locked":false,"tz":-21600000,"icon":"LFGIcon-Karazhan","description":"Flash
 backs anyone?","location":"Karazhan","moderator":true});
*/
		//$detail_array = array();
		//exec("\$detail_array = ".preg_replace('/(^\'|\'$)/', '', preg_replace('/\:/', ' => ', preg_replace('/[\}\]]/', ')', preg_replace('/[\{\[]/', 'array(', preg_replace('/^[^(]+/', 'array', $file_string)))))."");
		eval("\$detail_array = ".preg_replace('/\,/', ', ', preg_replace('/(^\'|\'$)/', '', preg_replace('/\:/', ' => ', preg_replace('/[\]]/', ')', preg_replace('/[\}]/', ')', preg_replace('/[\{]/', 'array(', preg_replace('/[\[]/', 'array(', preg_replace('/(^[^\(]+\(|\)\;$)/', '', $file_string)))))))).";");
		//print_r($detail_array);
		return $detail_array;
	}
	//return false;
}
function xml2array($string, $get_attributes = 1, $priority = 'tag')
{
	$contents = "";
	if (!function_exists('xml_parser_create'))
	{
		return array ();
	}
	$parser = xml_parser_create('');
	$contents = $string;
	xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, trim($contents), $xml_values);
	xml_parser_free($parser);
	if (!$xml_values)
		return; //Hmm...
	$xml_array = array ();
	$parents = array ();
	$opened_tags = array ();
	$arr = array ();
	$current = & $xml_array;
	$repeated_tag_index = array ();
	foreach ($xml_values as $data)
	{
		unset ($attributes, $value);
		extract($data);
		$result = array ();
		$attributes_data = array ();
		if (isset ($value))
		{
			if ($priority == 'tag')
				$result = $value;
			else
				$result['value'] = $value;
		}
		if (isset ($attributes) and $get_attributes)
		{
			foreach ($attributes as $attr => $val)
			{
				if ($priority == 'tag')
					$attributes_data[$attr] = $val;
				else
					$result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
			}
		}
		if ($type == "open")
		{
			$parent[$level -1] = & $current;
			if (!is_array($current) or (!in_array($tag, array_keys($current))))
			{
				$current[$tag]['tag_contents'] = $result;
				if ($attributes_data)
					$current[$tag]['attr'] = $attributes_data;
				$repeated_tag_index[$tag . '_' . $level] = 1;
				$current = & $current[$tag];
			}
			else
			{
				if (isset ($current[$tag][0]))
				{
					$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
					$repeated_tag_index[$tag . '_' . $level]++;
				}
				else
				{
					$current[$tag] = array (
						$current[$tag],
						$result
					);
					$repeated_tag_index[$tag . '_' . $level] = 2;
					if (isset ($current[$tag]['tag_contents']))
					{
						$current[$tag]['0']['tag_contents'] = $current[$tag]['tag_contents'];
						unset ($current[$tag]['attr']);
					}
				}
				$last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
				$current = & $current[$tag][$last_item_index];
			}
		}
		elseif ($type == "complete")
		{
			if (!isset ($current[$tag]))
			{
				$current[$tag] = $result;
				$repeated_tag_index[$tag . '_' . $level] = 1;
				if ($priority == 'tag' and $attributes_data)
					$current[$tag]['attr'] = $attributes_data;
			}
			else
			{
				if (isset ($current[$tag][0]) and is_array($current[$tag]))
				{
					$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
					if ($priority == 'tag' and $get_attributes and $attributes_data)
					{
						$current[$tag][$repeated_tag_index[$tag . '_' . $level]]['attr'] = $attributes_data;
					}
					$repeated_tag_index[$tag . '_' . $level]++;
				}
				else
				{
					$current[$tag] = array (
						$current[$tag],
						$result
					);
					$repeated_tag_index[$tag . '_' . $level] = 1;
					if ($priority == 'tag' and $get_attributes)
					{
						if (isset ($current[$tag]['attr']))
						{
							$current[$tag]['0']['attr'] = $current[$tag . '_attr'];
							unset ($current[$tag]['attr']);
						}
						if ($attributes_data)
						{
							$current[$tag][$repeated_tag_index[$tag . '_' . $level]]['attr'] = $attributes_data;
						}
					}
					$repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
				}
			}
		}
		elseif ($type == 'close')
		{
			$current = & $parent[$level -1];
		}
	}
	return ($xml_array);
}
function array_to_insert($data_array, $player_id=0, $guild="", $character="")
{
	$insertSQL = "";
	foreach($data_array as $key1 => $array1)
	{
		if(count($array1) > 0 && is_array($array1))
		{
			foreach($array1 as $key2 => $array2)
			{
				if(count($array2) > 0 && is_array($array2))
				{
					foreach($array2 as $key3 => $array3)
					{
						if(count($array3) > 0 && is_array($array3))
						{
							foreach($array3 as $key4 => $array4)
							{
								if(count($array4) > 0 && is_array($array4))
								{
									foreach($array4 as $key5 => $array5)
									{
										if(is_string($array5))
										{
											$insertSQL .= "insert into table_name(playerid, guild, character, type1, type2, type3, type4, key, value) values(".format_string_for_query($player_id).", '".format_string_for_query($guild)."', '".format_string_for_query($character)."','".format_string_for_query($key1)."', '".format_string_for_query($key2)."', '".format_string_for_query($key3)."', '".format_string_for_query($key4)."', '".format_string_for_query($key5)."', '".format_string_for_query($array5)."')\n";
										}
									}
								}
								if(is_string($array4))
								{
									$insertSQL .= "insert into table_name(playerid, guild, character, type1, type2, type3, key, value) values(".format_string_for_query($player_id).", '".format_string_for_query($guild)."', '".format_string_for_query($character)."','".format_string_for_query($key1)."', '".format_string_for_query($key2)."', '".format_string_for_query($key3)."', '".format_string_for_query($key4)."', '".format_string_for_query($array4)."')\n";
								}
							}
						}
						if(is_string($array3))
						{
							$insertSQL .= "insert into table_name(playerid, guild, character, type1, type2, key, value) values(".format_string_for_query($player_id).", '".format_string_for_query($guild)."', '".format_string_for_query($character)."','".format_string_for_query($key1)."', '".format_string_for_query($key2)."', '".format_string_for_query($key3)."', '".format_string_for_query($array3)."')\n";
						}
					}
				}
				if(is_string($array2))
				{
					$insertSQL .= "insert into table_name(playerid, guild, character, type1, key, value) values(".format_string_for_query($player_id).", '".format_string_for_query($guild)."', '".format_string_for_query($character)."','".format_string_for_query($key1)."', '".format_string_for_query($key2)."', '".format_string_for_query($array2)."')\n";
				}
			}
		}
		if(is_string($array1))
		{
			$insertSQL .= "insert into table_name(playerid, guild, character, key, value) values(".format_string_for_query($player_id).", '".format_string_for_query($guild)."', '".format_string_for_query($character)."','".format_string_for_query($key1)."', '".format_string_for_query($array1)."')\n";
		}
	}
	return $insertSQL;
}
function curl_request($url, $authenticate="", $content_type="text/html")
{
	if($authenticate)
	{
		$cr = curl_init("https://www.blizzard.com/login/login.xml?loginType=com");
		curl_setopt($cr, CURLOPT_RETURNTRANSFER, true);							// Get returned value as string (don"t put to screen)
		curl_setopt($cr, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.6) Gecko/2009011913 Firefox/3.0.6");		// Spoof the user agent
		curl_setopt($cr, CURLOPT_COOKIEJAR, "/home/web/thedissident.us/cookies/cookie_".session_id().".txt");						// Use cookie.txt for STORING cookies
		curl_setopt($cr, CURLOPT_COOKIEFILE, "/home/web/thedissident.us/cookies/cookie_".session_id().".txt");						// Use cookie.txt for STORING cookies
		curl_setopt($cr, CURLOPT_HEADER, false);								// Make sure we don't get the header.
		curl_setopt($cr, CURLINFO_HEADER_OUT, true);							// -- unless we ask for info
		curl_setopt($cr, CURLOPT_FOLLOWLOCATION, true);							// If the page moved (or is authenticate) follow it
		curl_setopt($cr, CURLOPT_FRESH_CONNECT, true);		    			// Use cookie.txt for STORING cookies
		curl_setopt($cr, CURLOPT_POST, true);								// Tell curl that we are posting data
		curl_setopt($cr, CURLOPT_POSTFIELDS, $authenticate);				// Post the data in the array above
		curl_exec($cr);
		curl_close($cr);
	}//else
	//	echo "auth string ".$authenticate;
	
	$cr = curl_init($url);
	curl_setopt($cr, CURLOPT_RETURNTRANSFER, true);							// Get returned value as string (don"t put to screen)
	curl_setopt($cr, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.6) Gecko/2009011913 Firefox/3.0.6");		// Spoof the user agent
	if($authenticate !== "nocookies")
	{
		curl_setopt($cr, CURLOPT_COOKIEJAR, "/home/web/thedissident.us/cookies/cookie_".session_id().".txt");						// Use cookie.txt for STORING cookies
		curl_setopt($cr, CURLOPT_COOKIEFILE, "/home/web/thedissident.us/cookies/cookie_".session_id().".txt");						// Use cookie.txt for STORING cookies
	}
	curl_setopt($cr, CURLOPT_HEADER, false);								// Make sure we don't get the header.
	curl_setopt($cr, CURLINFO_HEADER_OUT, true);							// -- unless we ask for info
	curl_setopt($cr, CURLOPT_FOLLOWLOCATION, true);							// If the page moved (or is authenticate) follow it
	/*
	if($authenticate)
	{
		curl_setopt($cr, CURLOPT_FRESH_CONNECT, true);		    			// Use cookie.txt for STORING cookies
		curl_setopt($cr, CURLOPT_POST, true);								// Tell curl that we are posting data
		curl_setopt($cr, CURLOPT_POSTFIELDS, $authenticate);				// Post the data in the array above
	}
	*/
	if($content_type != "text/html")
		curl_setopt($cr, CURLOPT_HTTPHEADERS,array('Content-Type: text/xml;charset=UTF-8')); 
	$output = curl_exec($cr);
	curl_close($cr);
	return $output;
}
?>