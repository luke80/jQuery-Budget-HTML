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
var firstLoad_xsl = true;
if(window.XMLHttpRequest)
	var xslDocRequest = new XMLHttpRequest();
else
	var xslDocRequest =  new ActiveXObject("MSXML2.XSLTemplate");
xslDocRequest.overrideMimeType("application/xml");
var ttipXsltProcessor = new XSLTProcessor();

XML.load = function(url, roottag, namespace)
{
    firstLoad_xsl = false;
	//alert(typeof window.XMLHttpRequest);
	// For mozilla we need to build a request properly, in safari its an object in firefox its a function
	if(firstLoad_xsl && typeof roottag == "boolean" && roottag == true)		// && typeof Sarissa.getDomDocument == "function"
	{
		firstLoad_xsl = false;
		var xslurl = "/layout/item-tooltip.xsl";
		try
		{
			xslDocRequest.async = false;
			xslDocRequest.open("GET", xslurl, false);
			xslDocRequest.send(null);
		}catch	(e)
		{
			debugVar.dumpToDebug("XSL Error: " + e.message);
			//var request = Sarissa.getDomDocument();
			//request = new XMLHttpRequest();
			//if (request.overrideMimeType)
			//	request.overrideMimeType('text/xml');
			debugVar.dumpToDebug('xsl request prepared: ' + xslurl + '\n');
			//needs to be async for all browsers, otherwise unformatted 
			xslDocRequest.async = false;
			xslDocRequest.load(xslurl);
			//request.open("GET", xmlsrl, false);
			//request.send(null);
		}
		//responsetext will appear in Firefox for the first item that's moused over
		xsldoc = xslDocRequest.responseXML;
		ttipXsltProcessor.importStylesheet(xslDocRequest.responseXML);
		ttipXslProcessorIsReady = true;
	
		debugVar.dumpToDebug('xsl request sent: ' + xslDocRequest.readyState + '\n');
	}
	if(typeof window.XMLHttpRequest == "function" || typeof window.XMLHttpRequest == "object")
	{
		//var request = XML.newDocument(roottag, namespace);	//Use the above function to create the new document object
		var request = new XMLHttpRequest();
		request.async = false;
		//if (request.overrideMimeType)
		//	request.overrideMimeType('text/xml');
		//debugVar.dumpToDebug('request prepared: ' + url + '\n' + typeof roottag + '\n');
		var async = false;
		if(typeof roottag == "boolean" && roottag == true)
		{
			//debugVar.dumpToDebug("xsl processor rschange func set.\n")
			/*
			if(request.onreadystatechange)
				request.onreadystatechange = xslProcessorReadyStateHandler;
			else
				request.readystatechange = xslProcessorReadyStateHandler;
			*/
			//eventRegistrar.registerFunction(request, "readystatechange", this.xslProcessorReadyStateHandler);
			//alert(request.readystatechange);
			//request.addEventListener("readystatechange", xslProcessorReadyStateHandler, false);
			//alert(request.readystatechange);
			//eventRegistrar();
			async = false;
		}
		if(url.indexOf("auth=") == -1)
		{
			request.open("GET", url, async);
			request.send(null);
		}else
		{
			var authString = url.replace(/.+(\?|\&)(auth\=[^\&\?]+).*/, "$2"); //url.search(/(\?|\&)auth\=/));
			url = url.replace(/(.+)(\?|\&)(auth\=[^\&\?]+)(.+)/, "$1$2$4");
			request.open("POST", url, async);
			request.setRequestHeader('auth', authString);
			request.send(null);
		}
		/*
		if(!request.getResponseHeader("Date"))
		{
			var cached = request;
			request =  new XMLHttpRequest();
			var ifModifiedSince = cached.getResponseHeader("Last-Modified");
			ifModifiedSince = (ifModifiedSince) ?
				ifModifiedSince : new Date(0); // January 1, 1970
			request.open("GET", url, false);
			
			request.setRequestHeader("If-Modified-Since", ifModifiedSince);
			request.send("");
			if(request.status == 304) {
			request = cached;
			}
		}
		*/
		if(request.readyState == 4)
			xslProcessorReadyStateHandler();
		//debugVar.dumpToDebug('request sent: ' + request.readyState + '\n' + request.responseXML.documentElement.nodeName + '\n' + request.responseXML.documentElement.toString() + '\n');
		if(request.responseXML)
			return request.responseXML;
	} else
	{
		var xmldoc = XML.newDocument(roottag, namespace);		//Use the above function to create the new document object
		xmldoc.async = false;																//Load async or sync determined by function call
		xmldoc.load(url);																		//Load and parse
		return xmldoc;																			//Return
	}
		function xslProcessorReadyStateHandler(e)
		{
			//alert(e.readyState);
			if(typeof roottag == "boolean" && roottag == true && request.readyState == 4)
			{
				deleteElement("activityBar");
				var preBufferedItemContainer = document.createElement("div");
				var untranslatedXML = request.responseXML;
				untranslatedXML.normalize();
				//untranslatedXML.removeChild(untranslatedXML.childNodes.item(0));
				var translatedXML = ttipXsltProcessor.transformToFragment(untranslatedXML, window.document);
				var newItemHtml = translatedXML;
				if(!newItemHtml)
				{
					//Create the objects
					//XML = CreateXMLStringParser("<XMLDataString />");
					//XSL = CreateXMLStringParser("<XSLTransformString />");
					var xmlDoc= new ActiveXObject("Microsoft.XMLDOM");
					xmlDoc.loadXML(untranslatedXML);
					var xslDoc= new ActiveXObject("Microsoft.XMLDOM");
					xslDoc.loadXML(xsldoc);

					//Perform the transform
					TransformResults = XML.transformNode(xsldoc);
					
					debugVar.dumpToDebug("untranslatedXML: " + untranslatedXML.childNodes.length + '\n');
					debugVar.dumpToDebug("translatedXML: " + translatedXML + '\n');
					debugVar.dumpToDebug("newItemHtml.innerHTML: " + newItemHtml + '\n' + request.readyState + ' ' + xsldoc + ' ' +Sarissa.getParseErrorText(newItemHtml));
					//ttipXsltProcessor.outputMethod = "text";
					newItemHtml = Sarissa.getParseErrorText(ttipXsltProcessor.transformToFragment(request.responseXML, window.document));
					debugVar.dumpToDebug("newItemHtml.innerHTML: " + newItemHtml + '\n' + request.responseText);
					newItemHtml = document.createTextNode(newItemHtml);
					debugVar.dumpToDebug("newItemHtml.innerHTML xml: \n" + request.responseText + '\n');
					debugVar.dumpToDebug("newItemHtml.innerHTML xsl: \n" + xsldoc + '\n');
					preBufferedItemContainer.appendChild(newItemHtml);
					//preBufferedItemContainer.innerHTML = request.responseText;
				}else
				{
					debugVar.dumpToDebug("newItemHtml.innerHTML: " + newItemHtml.innerHTML + '\n');
					preBufferedItemContainer.appendChild(newItemHtml);
				}
				//emptyElement('tooltipDiv');
				pasteElement.appendChild(preBufferedItemContainer);
				debugVar.dumpToDebug("tooltip innerHTML: " + preBufferedItemContainer.innerHTML + '\n');
			}
		}
};
XML.loadXsl = function(xslurl)
{
	if(typeof Sarissa.getDomDocument == "function")
	{
		try
		{
			var xslDocRequest = new XMLHttpRequest();
			xslDocRequest.async = false;
			xslDocRequest.open("GET", xslurl, false);
			xslDocRequest.send(null);
		}catch	(e)
		{
			debugVar.dumpToDebug("XSL Error: " + e.message);
			var xslDocRequest = new XMLHttpRequest();
			//var request = Sarissa.getDomDocument();
			//request = new XMLHttpRequest();
			//if (request.overrideMimeType)
			//	request.overrideMimeType('text/xml');
			debugVar.dumpToDebug('xsl request prepared: ' + xslurl + '\n');
			//needs to be async for all browsers, otherwise unformatted 
			xslDocRequest.async = false;
			xslDocRequest.load(xslurl);
			//request.open("GET", xmlsrl, false);
			//request.send(null);
		}
		//responsetext will appear in Firefox for the first item that's moused over
		ttipXslProcessorIsReady = true;
	
		debugVar.dumpToDebug('xsl request sent: ' + xslDocRequest.readyState + '\n');
		if(xslDocRequest)
			return xslDocRequest.responseXML;
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


function CreateXMLStringParser(XMLString)
{//Function to create the XML objects
  try
  {
    var xmlParser = new DOMParser();
    var xmlDoc = xmlParser.parseFromString(XMLString, "text/xml");
  }
  catch(Err)
  {
    try
    {
      var xmlDoc= new ActiveXObject("Microsoft.XMLDOM");
      xmlDoc.async="false";
      xmlDoc.loadXML(XMLString);
    }
    catch(Err)
    {
      window.alert("Browser does not support XML parsing.");
      return false;
    }
  }
 
    return xmlDoc;
}

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
	debugVar.dumpToDebug("schema length: " + schema.rowtags.length + "\n");
	var domTable = document.createElement("table");
	domTable.className = "XMLcontent";
	var domTableBody = document.createElement("tbody");
	var cellsDisplayed = 0;
	var domRow = document.createElement("tr");
	var activeLoadingImageId = "";
	for(var t=0; t < schema.rowtags.length; t++)
	{
		//var xmlrows = xmldoc.getElementsByTagName(eval("schema."+schema.rowtags[t]);
		//debugVar.dumpToDebug(schema.rowtags[t].rowtag + " " + schema.rowtags[t].columns + "\n");
		var rowtag = eval("schema."+schema.rowtags[t].rowtag);
		var columns = eval("schema."+schema.rowtags[t].columns);
		var xmlrows = xmldoc.getElementsByTagName(rowtag);
		
		// Loop through these elements. Each one contains two cells: title and content.
		for(var r=0; r < xmlrows.length; r++)
		{
			// This is the XML element that holds data for the line
			var xmlrow = xmlrows[r];
			// This is a DOM element to recieve the data.
			// Loop through the columns specified by the schema object
			//debugVar.dumpToDebug("schema columns: " + columns.length + "\n");
			for(var c = 0; c < columns.length; c++)
			{
				//var tagname = null;
				var sc = columns[c];
				tagname = (typeof sc == "string")?sc:sc.tagname;
				//debugVar.dumpToDebug("schema column found: " + sc.rowtag + "\n");
				celltext = null;			// If the celltext remains null there is no data worth appending.
				var domCellTitle = document.createElement("td");
				domCellTitle.className = "XMLcontent";
				var titleText = (typeof c == "string")?sc:sc.label;
				if(titleText == "slot")
				{
					titleText = xmlrow.getAttribute("slot") + ' ' + titleText;
					titleText = titleText.replace(/\b0\b/, 'head');
					titleText = titleText.replace(/\-\b1\b/, 'ammo');
					titleText = titleText.replace(/\b1\b/, 'neck');
					titleText = titleText.replace(/\b2\b/, 'shoulders');
					titleText = titleText.replace(/\b3\b/, 'shirt');
					titleText = titleText.replace(/\b4\b/, 'chest');
					titleText = titleText.replace(/\b5\b/, 'waist');
					titleText = titleText.replace(/\b6\b/, 'legs');
					titleText = titleText.replace(/\b7\b/, 'feet');
					titleText = titleText.replace(/\b8\b/, 'wrist');
					titleText = titleText.replace(/\b9\b/, 'hands');
					titleText = titleText.replace(/\b10\b/, 'ring1');
					titleText = titleText.replace(/\b11\b/, 'ring2');
					titleText = titleText.replace(/\b12\b/, 'trinket1');
					titleText = titleText.replace(/\b13\b/, 'trinket2');
					titleText = titleText.replace(/\b14\b/, 'back');
					titleText = titleText.replace(/\b15\b/, 'main hand');
					titleText = titleText.replace(/\b16\b/, 'off hand');
					titleText = titleText.replace(/\b17\b/, 'ranged');
					titleText = titleText.replace(/\b18\b/, 'tabard');
					domCellTitle.appendChild(document.createTextNode(titleText));
				}else if(titleText == "talentSpec")
				{
					titleText = "talent build";
					domCellTitle.appendChild(document.createTextNode(titleText));
				}else if(titleText == "skill")
				{
					titleText = xmlrow.getAttribute("name");
					domCellTitle.appendChild(document.createTextNode(titleText));
				}else
				{
					domCellTitle.appendChild(document.createTextNode(titleText));
				}
				var domCellContent = document.createElement("td");
				domCellContent.className = "XMLcontent";
				if(tagname.charAt(0) == '@')
				{
					// If the tagname begins with '@' it is an attribute name
					if(xmlrow.nodeName == "talentSpec")
					{
						celltext = xmlrow.getAttribute("treeOne") + '/' + xmlrow.getAttribute("treeTwo") + '/' + xmlrow.getAttribute("treeThree");
					}else if(xmlrow.nodeName == "skill")
					{
						celltext = xmlrow.getAttribute("value") + "/" + xmlrow.getAttribute("max");
					}else
						celltext = xmlrow.getAttribute(tagname.substring(1));
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
					if(titleText == "icon image" || titleText.indexOf('slot') > 0)
					{
						var iconImage = document.createElement("img");
						iconImage.src = "/images/new_icons/"+xmlrow.getAttribute("icon")+".png";
						eventRegistrar.registerFunction(iconImage, "mouseover", imageMouseover_tooltip);
						eventRegistrar.registerFunction(iconImage, "mouseout", imageMouseout_tooltip);
						debugVar.dumpToDebug("item attributes: " + xmlrow.getAttribute("id") + ' ' + xmlrow.getAttribute("slot") + ' ' + xmldoc.getElementsByTagName("character").item(0).getAttribute("name") + ' ' + xmldoc.getElementsByTagName("character").item(0).getAttribute("realm") + "\n");
						iconImage.setAttribute("itemId", xmlrow.getAttribute("id"));
						iconImage.setAttribute("realm", xmldoc.getElementsByTagName("character").item(0).getAttribute("realm"));
						iconImage.setAttribute("characterName", xmldoc.getElementsByTagName("character").item(0).getAttribute("name"));
						iconImage.setAttribute("slot", xmlrow.getAttribute("slot"));
						domCellContent.appendChild(iconImage);
						activeLoadingImageId = "loading_"+xmldoc.getElementsByTagName("character").item(0).getAttribute("name")+"_"+xmldoc.getElementsByTagName("character").item(0).getAttribute("realm");
					}
					else
					{
						domCellContent.appendChild(document.createTextNode(celltext));
					}
					domCellTitle.setAttribute("align", "right");
					domRow.appendChild(domCellTitle);
					cellsDisplayed++;
					domRow.appendChild(domCellContent);
					cellsDisplayed++;
					if(cellsDisplayed % 6 == 0)
					{
						domTableBody.appendChild(domRow);
						domRow = document.createElement("tr");
					}
				}
			}
			domTable.appendChild(domTableBody);
			parentNode.appendChild(domTable);
		}
	}
	//refreshNode(parentNode);
	if(activeLoadingImageId != "")
	{
		var activeLoadingImage = document.getElementById(activeLoadingImageId);
		if(activeLoadingImage)
			activeLoadingImage.parentNode.removeChild(activeLoadingImage);
	}
	debugVar.dumpToDebug("table Contents: " + parentNode.innerHTML + '\n');
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
		rowtags: [
			{ rowtag: "rowtag1", columns: "columns1" },
			{ rowtag: "rowtag2", columns: "columns2" },
			{ rowtag: "rowtag3", columns: "columns3" },
			{ rowtag: "rowtag4", columns: "columns4" }
		],
		rowtag1: "character",
		columns1: [
			{ tagname: "@lastModified", label: "lastModified" } 
			],
		rowtag2: "talentSpec",
		columns2: [
			{ tagname: "@treeOne", label: "talent build" }
			],
		rowtag3: "skill",
		columns3: [
			{ tagname: "@name", label: "skill" } 
			],
		rowtag4: "item",
		columns4: [
			{ tagname: "@slot", label: "slot" }
		]
	};
	var xmldoc = XML.load(url);
	return parseXMLtoDOM(xmldoc, schema, parentNode);
}
function requestCharList(url, parentNode)
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
	var xmldoc = XML.load(url);
	try
	{
		var xmlrows = xmldoc.getElementsByTagName("character");
	}catch(e)
	{
		if(typeof xmldoc != "undefined")
			var xmlrows = xmldoc.getElementsByName("character");
		else
			debugVar.dumpToDebug("no xml found at \n" + url + '\n\'' + ((typeof xmldoc == "undefined")?typeof xmldoc:typeof xmldoc).toString() + '\'\n');
		//debugVar.dumpToDebug("xmldoc error: " e.toString() + '\n' + "..." + '\n');
	}//debugVar.dumpToDebug("xmlrows: " + xmlrows.length + '\n');
	var tableCells = new Array("name", "level", "icons", "guild", "realm");
	var characterTable = document.createElement("table");
	var thead = document.createElement("thead");
	var theadRow = document.createElement("tr");
	for(var i=0;i<tableCells.length;i++)
	{
		var headCell = document.createElement("td");
		headCell.style.margin = "1em";
		headCell.appendChild(document.createTextNode(tableCells[i]));
		theadRow.appendChild(headCell);
	}
	thead.appendChild(theadRow);
	var tbody = document.createElement("tbody");
	for(var i=0;i<xmlrows.length;i++)
	{
		var destId = xmlrows[i].getAttribute("name")+xmlrows[i].getAttribute("realm");
		var tbodyRow = document.createElement("tr");
		for(var j=0;j<tableCells.length;j++)
		{
			var bodyCell = document.createElement("td");
			bodyCell.style.margin = "1em";
			if(j != 2)
			{
				bodyCell.appendChild(document.createTextNode(xmlrows[i].getAttribute(tableCells[j])));
			}else
			{
				var raceGenderImage = document.createElement("img");
				raceGenderImage.setAttribute("height", 18);
				raceGenderImage.setAttribute("width", 18);
				raceGenderImage.setAttribute("border", 0);
				raceGenderImage.setAttribute("src", "/images/race-gender_icons/" + xmlrows[i].getAttribute("raceId") + "-" +xmlrows[i].getAttribute("genderId") + ".gif");
				var classImage = document.createElement("img");
				classImage.setAttribute("height", 18);
				classImage.setAttribute("width", 18);
				classImage.setAttribute("border", 0);
				classImage.setAttribute("src", "/images/class_icons/" + xmlrows[i].getAttribute("classId") + ".gif");
				bodyCell.appendChild(raceGenderImage);
				bodyCell.appendChild(classImage);
			}
			tbodyRow.appendChild(bodyCell);
		}
		var requestLink = document.createElement("input");
		requestLink.setAttribute("type", "button");
		//requestLink.setAttribute("itemId", xmlrows[i].getAttribute("id"));
		requestLink.setAttribute("value", " Get Data ");
		requestLink.setAttribute("realm", xmlrows[i].getAttribute("realm"));
		requestLink.setAttribute("characterName", xmlrows[i].getAttribute("name"));
		//requestLink.setAttribute("slot", xmlrows[i].getAttribute("slot"));
		requestLink.setAttribute("targetElement", destId);
		eventRegistrar.registerFunction(requestLink, "click", requestCharDetails);
		if(xmlrows[i].getAttribute("level") > 10)
			tbodyRow.appendChild(requestLink);
		debugVar.dumpToDebug("xmlrow[" + i + "] :" + xmlrows[i].getAttribute("name"));
		tbody.appendChild(tbodyRow);
		var destinationRow = document.createElement("tr");
		var destinationCell = document.createElement("td");
		destinationCell.setAttribute("colspan", tableCells.length);
		var destinationDiv = document.createElement("div");
		destinationDiv.setAttribute("id", destId);
		destinationCell.appendChild(destinationDiv);
		destinationRow.appendChild(destinationCell);
		tbody.appendChild(destinationRow);
	}
	characterTable.appendChild(thead);
	characterTable.appendChild(tbody);
	characterTable.className = "characterTable";
	parentNode.appendChild(characterTable);
	return true;
}
var loadingImageWhite = document.createElement("img");
loadingImageWhite.setAttribute("src", "/images/indicator.white.gif");
loadingImageWhite.setAttribute("height", "16");
loadingImageWhite.setAttribute("width", "16");
function requestCharDetails(e)
{
	var e = (window.event)?window.event:arguments[0];
	var target = e.target;				// DOM standard event model
	if(!target)	target = e.srcElement;	// IE event model
	//var itemNum = target.getAttribute("itemId");
	var activeLoading = loadingImageWhite.cloneNode(true);
	var realm = target.getAttribute("realm");
	var characterName = target.getAttribute("characterName");
	activeLoading.setAttribute("id", "loading_"+characterName+"_"+realm);
	target.parentNode.appendChild(activeLoading);
	//var slot = target.getAttribute("slot");
	var targetElementId = target.getAttribute("targetElement");
	targetElement = document.getElementById(targetElementId);
	debugVar.dumpToDebug("get data target: " + targetElementId + " is " + ((targetElement != null)?" found " + typeof targetElement:" not found."));
	var url = "/stats/lib/datagetter.xml.php?loc="+"http%3A%2F%2Fwww.wowarmory.com%2Fcharacter-sheet.xml%3Fr%3D"+encodeURIComponent(realm).replace(/\%/g, '%2525')+"%26n%3D"+encodeURIComponent(encodeURIComponent(characterName));
	if(targetElement != null)
		requestXML(url, targetElement);
}
function getItemDOMTooltip(url, parentNode)
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
		rowtags: [
			{ rowtag: "rowtag1", columns: "columns1" },
			{ rowtag: "rowtag2", columns: "columns2" },
			{ rowtag: "rowtag3", columns: "columns3" },
			{ rowtag: "rowtag4", columns: "columns4" }
		],
		rowtag1: "itemTooltip",
		columns1: [
			{ tagname: "id", label: "id" },
			{ tagname: "name", label: "name" }, 
			{ tagname: "icon", label: "faction" }, 
			{ tagname: "overallQualityId", label: "quality" }, 
			{ tagname: "bonding", label: "bonded" }, 
			{ tagname: "classId", label: "class" }, 
			{ tagname: "@armorBonus", label: "armor" }, 
			{ tagname: "requiredLevel", label: "level req" }, 
			{ tagname: "@value", label: "item source" } 
		],
		rowtag2: "equipData",
		columns2: [
			{ tagname: "inventoryType", label: "invtype" }, 
			{ tagname: "subclassName", label: "class2" } 
		],
		rowtag3: "armor",
		columns3: [
			{ tagname: "@armorBonus", label: "ab" } 
		]
	};
	var xmldoc = XML.load(url);
	return parseXMLtoDOM(xmldoc, schema, parentNode);
}
var loadingImage = document.createElement("img");
loadingImage.src = "/images/activityanimation.gif";
loadingImage.height = 7;
loadingImage.width = 78;
loadingImage.id = "activityBar";
document.getElementsByTagName("html").item(0).appendChild(loadingImage);
function requestArmoryItemTooltip(e, itemNum, realm, characterName, slot, compare)
{
	var tooltipElement = "tooltipDiv";
	var tooltipElement = document.getElementById(tooltipElement);
	emptyElement(tooltipElement);
	/*
	var imageDiv = document.createElement("div");
	imageDiv.appendChild(loadingImage);
	tooltipElement.appendChild(loadingImage);
	alert(tooltipElement.innerHTML);
	*/
	var url = "/stats/lib/datagetter.xml.php?loc="+"http%3A%2F%2Fwww.wowarmory.com%2Fitem-tooltip.xml%3Fi%3D"+itemNum+"%26r%3D"+encodeURIComponent(realm).replace(/\%/g, '%2525')+"%26n%3D"+encodeURIComponent(encodeURIComponent(characterName))+"%26s%3D"+slot+((compare != null)?"&auth="+compare:"");
	var xmldoc = XML.load(url, true);
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
function emptyElement(element)
{
	if(typeof element == "string")
	{
		if(document.getElementById(element))
		{
			element = document.getElementById(element);
		}else
		{
			debugVar.dumpToDebug("ERROR: Invalid element passed to emptyElement.");
		}
	}else if(element.tagName == "undefined")
	{
		element = document.getElementById(element.id);
		if(element.type == undefined)
			debugVar.dumpToDebug("Invalid element passed to emptyElement. " + element.id + " " + typeof element);
	}
	if(typeof element == "object")
	{
		var childNodes = element.childNodes;
		//for(var i=0;i<childNodes.length;i++) // >
		if(typeof childNodes != "undefined")
		{
			while(childNodes.length > 0)
			{
				element.removeChild(childNodes[0]);
				childNodes = element.childNodes;
			}
			var parentElement = element.parentNode;
			clone=element.cloneNode(true);
			parentElement.replaceChild(clone, element);
		}
	}
	//return element;
}
function deleteElement(element)
{
	if(typeof element == "string")
	{
		if(document.getElementById(element))
		{
			element = document.getElementById(element);
		}else
		{
			debugVar.dumpToDebug("ERROR: Invalid element passed to deleteElement.");
		}
	}else if(element.tagName == "undefined")
	{
		element = document.getElementById(element.id);
		if(element.type == undefined)
			debugVar.dumpToDebug("Invalid element passed to deleteElement. " + element.id + " " + typeof element);
	}
	if(typeof element == "object")
	{
		var childNode = element;
		var parentNode = element.parentNode;
		var grandparentNode = element.parentNode;
		parentNode.removeChild(childNode);
		//clone=parentElement.cloneNode(true);
		//grandparentElement.replaceChild(clone, parentElement);
	}
}
function refreshNode(node)
{
	var parent = node.parentNode;
	clone=node.cloneNode(true);
	parent.replaceChild(clone, node);
}