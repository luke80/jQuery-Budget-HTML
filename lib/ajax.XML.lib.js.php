/*
	Create a new Document object.  If no arguments are specified,
	the document will be empty.  If a root tag is specified, the document
	will contain that single root tag.  If the root tag has a namespace
	prefix, the second argument must specify the URL that identifies the namespace.
*/
var XML = { };
XML.newDocument = function(rootTagName, namespaceURL)
{
	if(!rootTagName) rootTagName = "";
	if(!namespaceURL) namespaceURL = "";
	
	if(document.implementation && document.implementation.createDocument)
	{
		//This is the W3C standard way to do it
		return document.implementation.createDocument(namespaceURL, rootTagName, null);
	}
	else
	{
		//This is the IE way to do it
		//Create an empty document as an ActiveX object
		//If there is no root element, this is all we need
		var doc = new ActiveXObject("MSXML2.DOMDocument");
		//If there is no root tag, initialize the document
		if(rootTagName)
		{
			//Look for a namespace prefix
			var prefix = "";
			var tagname = rootTagName;
			var p = rootTagName.indexOf(':');
			if(p != -1)
			{
				prefix = rootTagName.substring(0,p);
				tagname = rootTagName.substring(p+1);
			}
			//If there is a namespace, find a namespace prefix
			//If there is no namespace, then ignore prefixes
			if(namespaceURL)
				if(!prefix) prefix = "a0";  //This is for Firefox
			else prefix = "";
			//Create a root element (with optional namespace) as a string
			var text = "<" + (prefix?(prefix+":"):"") + tagname + (namespaceURL?(" xmlns:"+prefix+'="'+namespaceURL+'"'):"") + "/>";
			//Now parse the text into the empty document
			doc.loadXML(text);
		}
		return doc;
	}
};
//	Now to use it to load a document remotely
XML.load = function(url, roottag, namespace)
{
	//alert(typeof window.XMLHttpRequest);
	// For mozilla we need to build a request properly, in safari its an object in firefox its a function
	if(typeof window.XMLHttpRequest == "function" || typeof window.XMLHttpRequest == "object")
	{
		var request = XML.newDocument(roottag, namespace);	//Use the above function to create the new document object
		request = new XMLHttpRequest();
		if (request.overrideMimeType)
			request.overrideMimeType('text/xml');
		request.open("GET", url, false);
		request.send(null);
		//alert(request.readyState);
		if(request.responseXML)
			return request.responseXML;
	} else
	{
		var xmldoc = XML.newDocument(roottag, namespace);		//Use the above function to create the new document object
		xmldoc.async = false;																//Load async or sync determined by function call
		xmldoc.load(url);																		//Load and parse
		return xmldoc;																			//Return
	}
};
// This function doesn't work in mozilla
XML.loadAsync = function(url, callback)
{
	var xmldoc = XML.newDocument();		//Use the above function to create the new document object
	//Use onLoad or onreadystatechange to determine if it is loaded
	if(document.implementation && document.implementation.createDocument)
	{
		xmldoc.onload = function() { callback(xmldoc); }
		
	} else
	{
		xmldoc.onreadystatechange = function()
		{
			if(xmldoc.readyState == 4) callback(xmldoc);
		};
	}
	xmldoc.load(url);				//Now call for request and parse
};

/**		Example 21-7 from the O'Reilly javascript book (5th Edition)
		Building an HTML table from XML data

Extract data from the specified XML document and format it as an HTML table
Append the table to the specified HTML element
If the element is a string, it is taken as an element ID, and the named 
element is looked up

The schema argument is a JavaScript object that specifies what data is to 
be extracted and how it is to be displayed.  The schema object must have a
property named "rowtag" that specifies the tag name of the XML elements that
contain the data for one row of the table.  The schema object must also have
a property named "columns" that refers to an array.  The elements of this
array specify the order and content of the columns of the table.  Each 
array element may be a string or a JavaScript object.  If an element is a 
string, that string is used as the tag name of the XML element that contains 
table data for the column, and also as the column header for the column.
If an element of the columns[] array is an object, it must have one property 
named "tagname" and one named "label."  The tagname property is used to
extract data from the XML document and the label property is used as the
column header text.  If the tagname begins with an @ character, it is an
attribute of the row element rather than a child of the row.
**/
function makeTable(xmldoc, schema, element, decode)
{
	document.getElementById(element).innerHTML = "";
	// First off, create the <table> element
	var table = document.createElement("table");
	
	// Create the header row of <th> elements in a <tr> in a <thread>
	var thead = document.createElement("thead");
	var header = document.createElement("tr");
	for(var i = 0; i < schema.columns.length; i++)
	{
		var c = schema.columns[i];
		var label = (typeof c == "string")?c:c.label;
		var cell = document.createElement("th");
		cell.appendChild(document.createTextNode(label));
		header.appendChild(cell);
	}
	// Put the header into the table
	thead.appendChild(header);
	table.appendChild(thead);
	
	// The remaining rows of the table go in a <tbody>
	var tbody = document.createElement("tbody");
	
	// Now get the elements that contain our data from the xml document
	var xmlrows = xmldoc.getElementsByTagName(schema.rowtag);
	//alert(xmldoc);
	// Loop through these elements. Each one contains a row of the table.
	for(var r=0; r < xmlrows.length; r++)
	{
		// This is the XML element that holds data for the row
		var xmlrow = xmlrows[r];
		// Create an HTML element to display data in the row
		var row = document.createElement("tr");
		
		// Loop through the columns specified by the schema object
		for(var c = 0; c < schema.columns.length; c++)
		{
			var sc = schema.columns[c];
			var tagname = (typeof sc == "string")?sc:sc.tagname;
			var celltext;
			if(tagname.charAt(0) == '@')
			{
				// If the tagname begins with '@' it is an attribute name
				celltext = xmlrow.getAttribute(tagname.substring(1)); //.replace(/&lt;/, "<").replace(/&gt;/, ">");
			} else if(tagname.toLowerCase() == "link")
			{
				var xmlcell = xmlrow.getElementsByTagName(tagname)[0];
				if(xmlcell)
				{
					//alert('' + xmlrow.getElementsByTagName(tagname).item(0) + '\n' + tagname + '\n' + xmlcell);
					try
					{
						celltext = (xmlcell.firstChild.data.length > 1)?"<a href=\"" + xmlcell.firstChild.data + "\">Click Here</a>":"";
					} catch(e)
					{
						celltext = "";
					}
				} else celltext = "";
			} else
			{
				// Find the XML element that holds the data for this column
				var xmlcell = xmlrow.getElementsByTagName(tagname)[0];
				// Assume that element has a text node as its first child
				try
				{
					var celltext = xmlcell.firstChild.data;
				} catch(e)
				{
					celltext = "";
				}
			}
			// Create the HTML element element for this cell
			var cell = document.createElement("td");
			// Put the text data into the HTML cell
			try
			{
				cell.innerHTML = celltext;
				cell.style.verticalAlign = "top";
				cell.style.textAlign = "left";
			} catch (e)
			{
				cell.appendChild(document.createTextNode(celltext));
			}
			// Add the cell to the row
			row.appendChild(cell);
			//alert(sc.label + '\n' + tagname + '\n' + cell.innerHTML + '\n' + celltext);
		}
		// And add the row to the tbody of the table
		tbody.appendChild(row);
	}
	// Put the tbody into the table
	table.appendChild(tbody);
	// Set an HTML attribute on the table element by setting a property
	// Note that in XML we must use setAttribute() instead
	table.frame = "border";
	
	// Now that we've created the HTML table, add it to the specified element
	// If that element is a string, assume it is an element ID
	if(typeof element == "string") element = document.getElementById(element);
	element.appendChild(table);
}
function makeConnectionsTable(xmldoc, schema, element, decode)
{
	document.getElementById(element).innerHTML = "";	// First off, create the <table> element
	var xmlrows = xmldoc.getElementsByTagName(schema.rowtag);	// Loop through these elements. Each one contains a row of the table.
	for(var r=0; r < xmlrows.length; r++)
	{
		var table = document.createElement("table");		// The remaining rows of the table go in a <tbody>
		var tbody = document.createElement("tbody");		// Now get the elements that contain our data from the xml document
		var xmlrow = xmlrows[r];						// This is the XML element that holds data for the row
		var doappend = false;
		for(var c = 0; c < schema.columns.length; c++)	// Loop through the columns specified by the schema object
		{
			var row = document.createElement("tr");		// Create an HTML element to display data in the row
			var sc = schema.columns[c];
			var tagname = (typeof sc == "string")?sc:sc.tagname;
			var celltext;
			var labelcell = document.createElement("td");	// Create the HTML element element for the label cell
			labelcell.className = "XMLcontent";
			row.className = "XMLcontent";
			labelcell.appendChild(document.createTextNode((typeof c == "string")?sc:sc.label));
			row.appendChild(labelcell);
			if(tagname.charAt(0) == '@')				// If the tagname begins with '@' it is an attribute name
			{
				celltext = xmlrow.getAttribute(tagname.substring(1)); //.replace(/&lt;/, "<").replace(/&gt;/, ">");
			} else if(tagname.toLowerCase() == "link")
			{
				var xmlcell = xmlrow.getElementsByTagName(tagname)[0];
				if(xmlcell)
				{
					if(typeof xmlcell.firstChild == "object")
						celltext = (xmlcell.firstChild.data.length > 1)?"<a href=\"" + xmlcell.firstChild.data + "\">Click Here</a>":"";
					else
						celltext = "";
				} else celltext = "";
			} else
			{
				var xmlcell = xmlrow.getElementsByTagName(tagname)[0];	// Find the XML element that holds the data for this column
				if(xmlcell)
				{
					try
					{
					if(typeof xmlcell.firstChild == "object")
						var celltext = xmlcell.firstChild.data;	// Assume that element has a text node as its first child
					else
						var celltext = "";
					} catch(e)
					{
						var celltext = "";
					}
				} else celltext = "";
			}
			var cell = document.createElement("td");	// Create the HTML element element for this cell
			cell.className = "XMLcontent";
			/*
			*/
			table.setAttribute("restype", xmlrow.getAttribute("restype"));
			try											// Put the text data into the HTML cell
			{
				cell.innerHTML = celltext;
				cell.style.verticalAlign = "top";
				cell.style.textAlign = "left";
			} catch (e)
			{
				cell.appendChild(document.createTextNode(celltext));
			}
			if(cell.innerHTML.length > 0 && (cell.innerHTML.charAt(0) != " " || cell.innerHTML.length > 1))
			{
				row.appendChild(cell);						// Add the cell to the row
				tbody.appendChild(row);						// And add the row to the tbody of the table
				doappend = true;
			}
			
		}
		table.appendChild(tbody);						// Put the tbody into the table
		// Set an HTML attribute on the table element by setting a property
		// Note that in XML we must use setAttribute() instead
		// table.style.border = "1px solid black";
		table.className = "XMLcontent";
		//table.style.visibility = "visible";
		//table.style.height = "50px";
		//table.style.overflow = "hidden";
		//table.style.border = "1px solid blue";
		// Now that we've created the HTML table, add it to the specified element
		// If that element is a string, assume it is an element ID
		if(typeof element == "string") element = document.getElementById(element);
		if(doappend)
		{
			container = document.createElement("div");
			container.className = "XMLcontent";
			container.appendChild(table);
			container.id = "table" + r;
			element.appendChild(container);
		}
	}
}
function parseXMLtoDOM(xmldoc, schema, parentNode)
{
	// Get the elements that contain our data from the xml document
	/*
	if(xmldoc.hasChildNodes())
	{
		try
		{
			var test_range = xmldoc.createRange(xmldoc.firstChild);
			//test_range.selectNodeContents(xmldoc.firstChild);
			//var range_contents = test_range.extractContents();
			debugVar.dumpToDebug(xmldoc.firstChild.tagName + "\n" + xmldoc.childNodes.length);
		} catch(e)
		{
			debugVar.dumpToDebug("ERROR: " + "\n" + e.message + "\n");
		}
	}
	*/
	var xmlrows = xmldoc.getElementsByTagName(schema.rowtag);
	// Loop through these elements. Each one contains two cells: title and content.
	for(var r=0; r < xmlrows.length; r++)
	{
		var domTable = document.createElement("table");
		domTable.className = "XMLcontent";
		var domTableBody = document.createElement("tbody");
		// This is the XML element that holds data for the line
		var xmlrow = xmlrows[r];
		// This is a DOM element to recieve the data.
		// Loop through the columns specified by the schema object
		for(var c = 0; c < schema.columns.length; c++)
		{
			var domRow = document.createElement("tr");
			var sc = schema.columns[c];
			tagname = (typeof sc == "string")?sc:sc.tagname;
			debugVar.dumpToDebug("schema column found: " + tagname + "\n");
			celltext = null;			// If the celltext remains null there is no data worth appending.
			var domCellTitle = document.createElement("td");
			domCellTitle.className = "XMLcontent";
			domCellTitle.appendChild(document.createTextNode((typeof c == "string")?sc:sc.label));
			var domCellContent = document.createElement("td");
			domCellContent.className = "XMLcontent";
			if(tagname.charAt(0) == '@')
			{
				// If the tagname begins with '@' it is an attribute name
				celltext = xmlrow.getAttribute(tagname.substring(1));
				if(celltext != null)
				{
					try
					{
						domCellContent.appendChild(document.createTextNode(celltext));
					} catch(e)
					{
						debugVar.dumpToDebug("ERROR: " + "\n" + e.message + "\n celltext not defined for \'" + tagname + "\'\n \(var type: \'" + typeof celltext + "\'\)\n" + celltext + "\n");
					}
				}else
					debugVar.dumpToDebug("NON FATAL ERROR: " + "\n Unable to get attribute for " + xmlrow.tagName + "\n");
			} else if(xmlrow.getElementsByTagName(tagname).length > 0)
			{
				// Find the XML element that holds the data for this column
				//alert(xmlrow.getElementsByTagName(tagname)[0]);
				if(xmlrow.getElementsByTagName(tagname)[0])
					var xmlcell = xmlrow.getElementsByTagName(tagname)[0];
				else if(xmlrow.getElementsByTagName(tagname).item(0))
					var xmlcell = xmlrow.getElementsByTagName(tagname).item(0);
				// Assume that element has a text node as its first child
				try
				{
					if(typeof xmlcell != "undefined")
						celltext = xmlcell.firstChild.data;
					else
						celltext = null;
				} catch(e)
				{
					debugVar.dumpToDebug("ERROR: " + "\n" + e.message + "\n for tagname \'" + tagname + "\'\n \(var type: \'" + typeof xmlcell + "\'\)\n" + ((tagname.charAt(0) == "@")?"This shouldn't be here!\n":tagname.charAt(0)));
				}
			}
			if(celltext !== null)
			{
				domCellContent.align = "left";
				if(celltext.indexOf("|") == -1)
					domCellContent.appendChild(document.createTextNode(celltext));
				else
				{
					var authorRegExp = new RegExp("^[\\\s]*(a)[\\\s]*$", "i");
					var editorRegExp = new RegExp("^[\\\s]*(e)[\\\s]*$", "i");
					var compilerRegExp = new RegExp("^[\\\s]*(c)[\\\s]*$", "i");
					celltexts = celltext.split("|");
					for(var i=0;i<celltexts.length;i++)
					{
						if(authorRegExp.test(celltexts[i]))
						{
							domCellTitle = document.createElement("td");
							domCellTitle.className = "XMLcontent";
							domCellTitle.appendChild(document.createTextNode("Author"));
						} else if(editorRegExp.test(celltexts[i]))
						{
							domCellTitle = document.createElement("td");
							domCellTitle.className = "XMLcontent";
							domCellTitle.appendChild(document.createTextNode("Editor"));
						} else if(compilerRegExp.test(celltexts[i]))
						{
							domCellTitle = document.createElement("td");
							domCellTitle.className = "XMLcontent";
							domCellTitle.appendChild(document.createTextNode("Compiler"));
						} else if(tagname.toLowerCase() == "author")
						{
							//var celltextsDiv = document.createElement("div");
							//celltextsDiv.appendChild(document.createTextNode(celltexts[i]));
							domCellContent.appendChild(document.createTextNode(celltexts[i]));
						}else
						{
							var celltextsDiv = document.createElement("div");
							celltextsDiv.appendChild(document.createTextNode(celltexts[i]));
							domCellContent.appendChild(celltextsDiv);
						}
					}
				}
				/*
				try
				{
					if(xmlcell.getAttribute("dbcolname"))
					{
						domCellTitle.setAttribute("dbcolname", xmlcell.getAttribute("dbcolname"));
					}
				} catch(e)
				{
					debugVar.dumpToDebug("ERROR: " + "\n" + e.message + "\n attribute 'dbcolname' not found \'" + "" + "\n");
				}
				*/
				domRow.appendChild(domCellTitle);
				domRow.appendChild(domCellContent);
				domTableBody.appendChild(domRow);
			}
		}
		domTable.appendChild(domTableBody);
		parentNode.appendChild(domTable);
	}
	return parentNode;
}

function requestXML(url, parentNode)
{
	if(typeof parentNode == "string")
	{
		if(document.getElementById(parentNode))
		{
			parentNode = document.getElementById(parentNode);
		}else if(document.createElement(parentNode).type != "undefined")
		{
			parentNode = document.createElement(parentNode);
		}else
		{
			debugVar.dumpToDebug("Invalid element passed to requestXML.");
		}
		//alert(document.createElement("notanhtmltag").type);
	}
	var schema = {
		rowtag: "character",
		columns: [
			{ tagname: "@charUrl", label: "charUrl" },
			{ tagname: "@class", label: "class" }, 
			{ tagname: "@faction", label: "faction" }, 
			{ tagname: "@gender", label: "gender" }, 
			{ tagname: "@guildName", label: "guildName" }, 
			{ tagname: "@lastModified", label: "lastModified" }, 
			{ tagname: "@level", label: "level" }, 
			{ tagname: "@name", label: "name" }, 
			{ tagname: "@race", label: "race" }, 
			{ tagname: "@realm", label: "realm" }
			],
		rowtag: "talentSpec",
		columns: [
			{ tagname: "@treeOne", label: "treeOne" }, 
			{ tagname: "@treeTwo", label: "treeTwo" }, 
			{ tagname: "@treeThree", label: "treeThree" }
			],
		rowtag: "skill",
		columns: [
			{ tagname: "@key", label: "skillName" }, 
			{ tagname: "@skillMax", label: "currentCeiling" }, 
			{ tagname: "@value", label: "skillPoints" } 
			]
	};
	var xmldoc = XML.load(url);
	return parseXMLtoDOM(xmldoc, schema, parentNode);
}
function displayTable(url)
{
	var schema = {
		rowtag: "book",
		columns: [
					{ tagname: "@category", label: "category" },
					{ tagname: "title", label: "book title" }, 
					{ tagname: "author", label: "author" }, 
					{ tagname: "year", label: "year" }, 
					{ tagname: "price", label: "price" }
				]
	};
	var xmldoc = XML.load(url);
	makeTable(xmldoc, schema, "books");
}
function displayOtherTable(url, htmlelement)
{
	var schema = {
		rowtag: "contact",
		columns: [
					{ tagname: "@name", label: "Name" },
					{ tagname: "email", label: "Address" } 
				]
	};
	var xmldoc = XML.load(url);
	makeTable(xmldoc, schema, htmlelement);
}
function displayRSSTable(url, roottag, htmlelement)
{
	var regexp = /\w+:\/\/([\w.]+)\/\S*/i;
	var urlparsed = url.match(regexp);
	var namespace = (urlparsed)?urlparsed[1]:null;
	//alert(namespace);
	var schema = {
		rowtag: "item",
		columns: [
					{ tagname: "title", label: "Title" },
					{ tagname: "link", label: "Link" }, 
					{ tagname: "description", label: "Abstract" } 
				]
	};
	var xmldoc = XML.load(url, roottag, namespace);
	makeTable(xmldoc, schema, htmlelement, true);
}
<?PHP
/*
?>
function displayDataToElement(url, htmlelement)
{
	var schema = {
		rowtag: "item",
		columns: [
			{ tagname: "nevereverthere", label: "causeyournot" } 
<?PHP
require_once("resource/resourcedefs.php");
$taglist = array();
foreach($resfield as $resindex => $restagarray)
	foreach($restagarray as $dbcolumn => $tagarray)
		if(!isset($taglist[strtolower(trim(preg_replace("/\W/", "", $tagarray[0])))])) $taglist[strtolower(trim(preg_replace("/\W/", "", $tagarray[0])))] = $tagarray[0];
//print_r($taglist);
foreach($taglist as $key => $value)
	if($key == "restype")
		echo "\t\t\t, { tagname: \"restype\", label: \"Resource Type\" } \n";
	else
		echo "\t\t\t, { tagname: \"".$key."\", label: \"".$value."\" } \n";
?>
			]
	};
//			, { tagname: "restype", label: "typedef0" } 
	var xmldoc = XML.load(url);
	makeConnectionsTable(xmldoc, schema, htmlelement, true);
	element = (typeof htmlelement == "string")?document.getElementById(htmlelement):htmlelement;
	//alert(document.getElementById('mainContent').scrollHeight + "\n" + element.scrollHeight);
	//document.getElementById('mainContent').style.height = (element.scrollHeight > document.getElementById('mainContent').scrollHeight)?element.scrollHeight + "px":window.innerHeight + "px";
	resizeBody('mainContent', 'alertDiv2');
}
function returnDOMElement(url)
{
	var schema = {
		rowtag: "item",
		columns: [
			{ tagname: "nevereverthere", label: "causeyournot" } 
<?PHP
require_once("resource/resourcedefs.php");
$taglist = array();
foreach($resfield as $resindex => $restagarray)
	foreach($restagarray as $dbcolumn => $tagarray)
		if(!isset($taglist[strtolower(trim(preg_replace("/\W/", "", $tagarray[0])))])) $taglist[strtolower(trim(preg_replace("/\W/", "", $tagarray[0])))] = $tagarray[0];
//print_r($taglist);
foreach($taglist as $key => $value)
	if($key == "restype")
		echo "\t\t\t, { tagname: \"restype\", label: \"Resource Type\" } \n";
	else
		echo "\t\t\t, { tagname: \"".$key."\", label: \"".$value."\" } \n";
?>
			]
	};
//			, { tagname: "restype", label: "typedef0" } 
	//alert(url);
	var xmldoc = XML.load(url);
		debugVar.dumpToDebug(url);
	alertXML(url);
	return false;
	//return makeDOMElement(xmldoc, schema, "div", true);
}
function makeDOMElement(xmldoc, schema, tagName)
{
	var returnDOMElement = document.createElement(tagName);	// First off, create the <table> element
	var xmlrows = xmldoc.getElementsByTagName(schema.rowtag);	// Loop through these elements. Each one contains a row of the table.
	for(var r=0; r < xmlrows.length; r++)
	{
		var table = document.createElement("table");		// The remaining rows of the table go in a <tbody>
		var tbody = document.createElement("tbody");		// Now get the elements that contain our data from the xml document
		var xmlrow = xmlrows[r];						// This is the XML element that holds data for the row
		var doappend = false;
		for(var c = 0; c < schema.columns.length; c++)	// Loop through the columns specified by the schema object
		{
			var row = document.createElement("tr");		// Create an HTML element to display data in the row
			var sc = schema.columns[c];
			var tagname = (typeof sc == "string")?sc:sc.tagname;
			var celltext;
			var labelcell = document.createElement("td");	// Create the HTML element element for the label cell
			labelcell.className = "XMLcontent";
			row.className = "XMLcontent";
			labelcell.appendChild(document.createTextNode((typeof c == "string")?sc:sc.label));
			row.appendChild(labelcell);
			if(tagname.charAt(0) == '@')				// If the tagname begins with '@' it is an attribute name
			{
				celltext = xmlrow.getAttribute(tagname.substring(1)); //.replace(/&lt;/, "<").replace(/&gt;/, ">");
			} else if(tagname.toLowerCase() == "link")
			{
				var xmlcell = xmlrow.getElementsByTagName(tagname)[0];
				if(xmlcell)
				{
					if(typeof xmlcell.firstChild == "object")
						celltext = (xmlcell.firstChild.data.length > 1)?"<a href=\"" + xmlcell.firstChild.data + "\">Click Here</a>":"";
					else
						celltext = "";
				} else celltext = "";
			} else
			{
				var xmlcell = xmlrow.getElementsByTagName(tagname)[0];	// Find the XML element that holds the data for this column
				if(xmlcell)
				{
					try
					{
					if(typeof xmlcell.firstChild == "object")
						var celltext = xmlcell.firstChild.data;	// Assume that element has a text node as its first child
					else
						var celltext = "";
					} catch(e)
					{
						var celltext = "";
					}
				} else celltext = "";
			}
			var cell = document.createElement("td");	// Create the HTML element element for this cell
			cell.className = "XMLcontent";
			table.setAttribute("restype", xmlrow.getAttribute("restype"));
			try											// Put the text data into the HTML cell
			{
				cell.innerHTML = celltext;
				cell.style.verticalAlign = "top";
				cell.style.textAlign = "left";
			} catch (e)
			{
				cell.appendChild(document.createTextNode(celltext));
			}
			if(cell.innerHTML.length > 0 && (cell.innerHTML.charAt(0) != " " || cell.innerHTML.length > 1))
			{
				row.appendChild(cell);						// Add the cell to the row
				tbody.appendChild(row);						// And add the row to the tbody of the table
				doappend = true;
			}
			
		}
		table.appendChild(tbody);						// Put the tbody into the table
		// Set an HTML attribute on the table element by setting a property
		// Note that in XML we must use setAttribute() instead
		// table.style.border = "1px solid black";
		table.className = "XMLcontent";
		//table.style.visibility = "visible";
		//table.style.height = "50px";
		//table.style.overflow = "hidden";
		//table.style.border = "1px solid blue";
		// Now that we've created the HTML table, add it to the specified element
		// If that element is a string, assume it is an element ID
	}
	returnDOMElement.appendChild(table);
	return returnDOMElement;
}
function displayResourceType()
{
	for(var i = 1; i < divsByType.length; i++)
		for(var j =0; j < divsByType[i].length; j++)
		{
			divsByType[i][j].style.height = 0 + "px";
			divsByType[i][j].style.visibility = "hidden";
			divsByType[i][j].style.overflow = "hidden";
			//alert(divsByType[document.inputForm.restypeSelect[document.inputForm.restypeSelect.selectedIndex].value][j].innerHTML);
		}
	var divsInQuestion = divsByType[document.inputForm.restypeSelect[document.inputForm.restypeSelect.selectedIndex].value];
	//alert(divsInQuestion.length);
	for(var i = 0; i < divsInQuestion.length; i++)
	{
		divsInQuestion[i].style.height = divsInQuestion[i].scrollHeight + "px";
		divsInQuestion[i].style.visibility = "visible";
		divsInQuestion[i].style.overflow = "visible";
		//alert(divsInQuestion[i].innerHTML);
	}
	//document.getElementById('mainContent').style.height = (document.getElementById('alertDiv2').scrollHeight > document.getElementById('mainContent').scrollHeight)?document.getElementById('alertDiv2').scrollHeight + "px":window.innerHeight + "px";
	resizeBody('mainContent', 'alertDiv2');
}

function getHTMLElementMatches(searchString)
{
	var allDivs = document.getElementsByTagName("div");
	var regExp = "/<td>.*"+searchString+".*</td>/i";
	alert(regExp + "\n" + searchString);
	for(var i = 0; i < allDivs.length; i++)
	{
		if(allDivs[i].id.length > 0 && allDivs[i].id.substring(0,5) == "table")
		{
			var theTable = divArray[i].getElementsByTagName("table");
			divsByType[theTable[0].getAttribute("restype")][divsByType[theTable[0].getAttribute("restype")].length] = divArray[i];
		}
		//if(allDivs[i].id )
	}
	return true;
	for(var i = 1; i < divsByType.length; i++)
		for(var j =0; j < divsByType[i].length; j++)
		{
			divsByType[i][j].style.height = 0 + "px";
			divsByType[i][j].style.visibility = "hidden";
			divsByType[i][j].style.overflow = "hidden";
			//alert(divsByType[document.inputForm.restypeSelect[document.inputForm.restypeSelect.selectedIndex].value][j].innerHTML);
		}
	var divsInQuestion = divsByType[document.inputForm.restypeSelect[document.inputForm.restypeSelect.selectedIndex].value];
	//alert(divsInQuestion.length);
	for(var i = 0; i < divsInQuestion.length; i++)
	{
		divsInQuestion[i].style.height = divsInQuestion[i].scrollHeight + "px";
		divsInQuestion[i].style.visibility = "visible";
		divsInQuestion[i].style.overflow = "visible";
		//alert(divsInQuestion[i].innerHTML);
	}
	//document.getElementById('mainContent').style.height = (document.getElementById('alertDiv2').scrollHeight > document.getElementById('mainContent').scrollHeight)?document.getElementById('alertDiv2').scrollHeight + "px":window.innerHeight + "px";
	resizeBody('mainContent', 'alertDiv2');
}

function initxmldivs()
{
	displayDataToElement(arguments[0], arguments[1]);
	<?PHP
	echo "\n";
	foreach($restypes as $index => $typeval)
	{
		//echo "divsByType['".$typeval."'] = new Array();\n";
		echo "\tdivsByType[".$index."] = new Array();\n";
	}
	?>
	var divArray = document.getElementsByTagName("div");
	for(var i = 0; i < divArray.length; i++)
		if(divArray[i].id.length > 0 && divArray[i].id.substring(0,5) == "table")
		{
			var theTable = divArray[i].getElementsByTagName("table");
			divsByType[theTable[0].getAttribute("restype")][divsByType[theTable[0].getAttribute("restype")].length] = divArray[i];
		}
}
<?PHP
*/
?>
