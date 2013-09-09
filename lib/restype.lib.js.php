function registerRestypeSelect()
{
	eventRegistrar.registerFunction(document.inputForm.restype, "change", buildFormElements);
}

function buildFormElements()
{
	emptyElement("inputFormTable");
	emptyElement("alertDiv2");
	document.getElementById("restypeDesc").style.visibility = "hidden";
	document.getElementById("alertDiv2Border").style.visibility = "hidden";
	displayProperFields(document.inputForm.restype[document.inputForm.restype.selectedIndex].value);
	
	searchResults = new Array();
	searchStrings = new String("¶");
	
	if(document.inputForm.restype.selectedIndex != 0)
		document.getElementById("alertDiv2").appendChild(restypeDOMElements[document.inputForm.restype[document.inputForm.restype.selectedIndex].value]);
	resizeBody('mainContent', 'pagebody');
	resizeBody('mainContent', 'alertDiv2');
	onloadinit();
}
function eraseFormElements()
{
	//emptyElement(document.getElementById("inputFormTable"));
}
function displayProperFields(restype)
{
	var table = document.getElementById("inputFormTable"); //.getElementsByTagName("tbody");
	var parentTable = table.parentNode;
	document.getElementById("submitButton").style.visibility = "visible";
	restype = parseInt(restype);
	switch(restype)
	{
<?PHP
	require_once($_SERVER['DOCUMENT_ROOT']."/resource/resourcedefs.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/lib/connect.resourcecenter.php");
	//print_r($GLOBALS);
	$formatin=array("<tr valign=\"top\"><td valign=\"top\"><h2 class=\"inputTitle\">","</h2></td><td>","</td></tr>\n");
	foreach($GLOBALS['restypes'] as $key => $value)
	{
		echo "\t\tcase ".$key.":\n";
		echo "\t\t\tvar innerHTMLVar =  \"";
		foreach($GLOBALS['resfield'][$key] as $dbfield => $formData)
		{ 
			for($i=0;$i<count($formData);$i++)
			{
				if($i === 0)
				{
					echo str_replace("\n", "", str_replace("\r", "", str_replace("\"", "\\\"", entryfield($dbfield,$formData,"", $formatin))));
				}
			}
		}
		echo "\";\n";
			echo "\t\t\t//tbody = document.createElement(\"tbody\");
			//tbody.id = \"inputFormTable\";
			parseHTMLToDOMObject(innerHTMLVar, table);
			//parentTable.appendChild(tbody, table);\n";
		//echo "\t\t\ttable.appendChild(parseHTMLToDOMObject(innerHTMLVar));\n";
		echo "\t\tbreak;\n";
	}
?>	default:
		document.getElementById("restypeDesc").style.visibility = "visible";
		document.getElementById("submitButton").style.visibility = "hidden";
		//eraseFormElements();
	}
}
