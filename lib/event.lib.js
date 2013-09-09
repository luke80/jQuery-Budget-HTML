function OnloadEventRegistrar()
{
	this.pageLoaded = false;
	this.functionsRegistered = 0;
	this.registerFunction = function(functionObject)
	{
		if (window.addEventListener)
		{
			window.addEventListener('load',functionObject,false);
		}
		else if (document.addEventListener)
		{
			document.addEventListener('load',functionObject,false);
		}
		else if (window.attachEvent)
		{
			window.attachEvent('onload',functionObject);
		}
		else if (document.attachEvent)
		{
			document.attachEvent('onload',functionObject);
		}
		else
		{//Older browsers only
			if (typeof window.onload=='function')
			{
				var oldload=window.onload;
				window.onload=function()
				{
					oldload();
					functionObject();
				}
			} else
				window.onload=functionObject;
		}
		this.functionsRegistered++;
	};
	this.getPageLoaded = function()
	{
		return this.pageLoaded;
	};
	this.setPageLoaded = function()
	{
		if(typeof arguments[0] == "undefined" || typeof arguments[0] == "object")
			this.pageLoaded = true;
		else if(typeof arguments[0] != "object")
			this.pageLoaded = arguments[0];
		//debugVar.dumpToDebug("page loaded set to " + this.pageLoaded); // + " " + this.getPageLoaded()
		//return this.pageLoaded;
	};
}

function EventRegistrar()
{
	//this.pageLoaded = false;
	this.functionsRegistered = 0;
	this.registerFunction = function(element, eventType, functionObject)
	{
		var returnValue = false;
		//element.style.border = "1px solid blue";
		eventType = eventType.replace(RegExp("^on", "i"), "");
		//alert(eventType);
		if(typeof element != "undefined")
		{
			try
			{
				if (element.addEventListener)
				{
					returnValue = element.addEventListener(eventType,functionObject,false);
				}
				else if (element.attachEvent)
				{
					element.attachEvent('on'+eventType,functionObject);
				}
				else
				{//Older browsers only
					if (typeof eval("element.on"+eventType)=='function')
					{
						var oldload=eval("element.on"+eventType);
						eval("element.on"+eventType+"=function()\n{\n\toldload();\n\tfunctionObject();\n}");
					} else
						eval("element.on"+eventType+"=functionObject");
				}
			}catch(e)
			{
				try
				{
					if (element.attachEvent)
					{
						element.attachEvent('on'+eventType,functionObject);
					}
					else
					{//Older browsers only
						if (typeof eval("element.on"+eventType)=='function')
						{
							var oldload=eval("element.on"+eventType);
							eval("element.on"+eventType+"=function()\n{\n\toldload();\n\tfunctionObject();\n}");
						} else
							eval("element.on"+eventType+"=functionObject");
					}
				}catch(e)
				{
					alert(e.message + '\n' + typeof element);
					if (typeof eval("element.on"+eventType)=='function')
					{
						var oldload=eval("element.on"+eventType);
						eval("element.on"+eventType+"=function()\n{\n\toldload();\n\tfunctionObject();\n}");
					} else
						eval("element.on"+eventType+"=functionObject");
				}
			}
			this.functionsRegistered++;
		}
	};
}

function formEvent()
{
	var e = (window.event)?window.event:arguments[0];
	var target = e.target;				// DOM standard event model
	if(!target)	target = e.srcElement;	// IE event model
	if(target.type.indexOf("text") > -1)
		var code = (window.event)?window.event.keyCode:(e.which)?e.which:"unknown";
	else
		var code = 65;
	//alert(target.tagName.toLowerCase());
	if(target.tagName.toLowerCase() == "select")
	{
		if(target[target.selectedIndex].text == "ADD ITEM")
		{
			if(target.multiple)
			{
				var multiples = "<input type=hidden name=selecteditems value='";
				for(i=0;i<target.length;i++)
				{
					if(target.options[i].selected)
						if(target.options[i].value!="")
						  multiples+=target.options[i].value + "|";
						else
						  multiples+=target.options[i].text + "|";
						multiples+="'>";
				}
			}else
                multiples = "";
			var contents="<title>New " + target.getAttribute("onChangeValue") + "</title>";
			contents+="<form action='addselectvalue.php' method=post>";
			contents+="<input type=hidden name=restype value='1'>";
			contents+=multiples;
			contents+="<input type=hidden name=fieldname value='" + target.id + "'>";
			contents+="<b>New " + target.getAttribute("onChangeValue") + "</b>: <input name=newvalue><br>";
			contents+="<input type=submit value=' Add '></form>";
            win.setSize(250, 100);
			win.populate(contents);
			win.showPopup('APOS' + target.id);
		}
	}
	if(isPrintable(code))
	{
		var character = String.fromCharCode(code);
		var enteredValue = (typeof target.value == "string")?target.value:character;
		var entryPos = parseInt(searchStrings.indexOf("�" + enteredValue + "�")) + 1;
		if(entryPos <= 0 && document.inputForm.restype.selectedIndex != 0)
		{
			var resultIndex = searchResults.length;
			searchResults[resultIndex] = new Array();
			searchResults[resultIndex][0] = enteredValue;
			searchResults[resultIndex][1] = getMatchingTables(enteredValue, restypeDOMElements[document.inputForm.restype[document.inputForm.restype.selectedIndex].value]);
			if(searchResults[resultIndex][1] != false)
			{
				emptyElement("alertDiv2");
				//debugVar.dumpToDebug(enteredValue + " " + searchResults[resultIndex][1].childNodes.length);
				displayMatches(searchResults[resultIndex][1]);
			}
			//var uri = "/lib/resource/datagetter.xml.php";
			//if(typeof target == "object")
			//	uri += "?"+target.name+"="+encodeURIComponent(enteredValue);
			//displayDataToElement(uri, "alertDiv2");
			searchStrings += enteredValue + "�";
			//entryPos = parseInt(searchStrings.indexOf("�" + enteredValue + "�")) + 1;
		}else
		{
			for(var i=0;i<searchResults.length;i++)
			{
				if(searchResults[i][0] == enteredValue)
				{
					emptyElement("alertDiv2");
					displayMatches(searchResults[i][1]);
				}
			}
		}
	}
}
function displayMatches(element)
{
	var outputDiv = document.getElementById("alertDiv2");
	var childNodes = element.childNodes;
	var parentNode = outputDiv.parentNode;
	if(typeof parentNode != "object")
		parentNode = document.getElementById("alertDiv2Border");
	if(childNodes.length < 5 && childNodes.length > 0)
	{
		//changerObject.startFade();
		var heading = document.createElement("h2");
		//emptyElement(outputDiv);
		heading.appendChild(document.createTextNode("Are you entering one of the following:"));
		outputDiv.appendChild(heading);
		var appendMatch = element.cloneNode(true);
		outputDiv.appendChild(appendMatch);
		parentNode.style.visibility = "visible";
		eventRegistrar.registerFunction(appendMatch, "mouseover", highlightBorder);	//function() { document.getElementById("divHolder"+i).style.border="1px solid white"; }
		eventRegistrar.registerFunction(appendMatch, "mouseout", highlightBorder);	//function() { document.getElementById("divHolder"+i).style.border="0px solid transparent"; }
		eventRegistrar.registerFunction(appendMatch, "click", copyToForm);	//function() { document.getElementById("divHolder"+i).style.border="1px solid white"; }
		//outputDiv.appendChild(appendMatch);
		//clone=outputDiv.cloneNode(true);
		//parentNode.replaceChild(clone, outputDiv);
	}else
	{
		//changerObject.stopFade();
		parentNode.style.visibility = "hidden";
	}
}
function copyToForm()
{
	var e = (window.event)?window.event:arguments[0];
	var target = e.target;				// DOM standard event model
	if(!target)	target = e.srcElement;	// IE event model
	var parentNode = target.parentNode;
	var loopCount = 0;
	var maxLoops = 25;
	if(parentNode)
	{
		while(parentNode.id.indexOf("divHolder") == -1)
		{
			if(loopCount > maxLoops)
				break;
			parentNode = parentNode.parentNode;
			loopCount++;
		}
		if(parentNode.id.indexOf("divHolder") > -1)
		{
			resetForm();
			var rows = parentNode.getElementsByTagName("tr");
			for(var rowNum=0;rowNum<rows.length;rowNum++)
			{
				var cells = rows[rowNum].getElementsByTagName("td");
				var inputName = cells[0].getAttribute("dbcolname");
				var inputValue = cells[1].innerHTML;
				//alert(cells[0].getAttribute("dbcolname") + " = " + cells[1].innerHTML + "\n" + rows[rowNum].parentNode.innerHTML);
				if(!eval("document.inputForm."+inputName))
				{
					for(var i=0;i<document.inputForm.elements.length;i++)
					{
						if(document.inputForm.elements.name.indexOf(inputName) > -1)
							var formElement = document.inputForm.elements[i];
					}
				}else
					var formElement = eval("document.inputForm."+inputName);
				
				if(formElement != null)
				{
					if(inputName == "author")
					{
						formElement.value = inputValue;
						var authorSelect = document.inputForm.AEC_author;
						for(var i=0;i<authorSelect.length;i++)
						{
							if(cells[0].innerHTML.indexOf(authorSelect.options[i].text) > -1)
							{
								authorSelect.options[i].selected = true;
							}
						}
					} else if(formElement.type.indexOf("text") > -1)
					{
						formElement.value = inputValue;
					}else if(formElement.type.indexOf("select") != -1)
					{
						//alert(formElement.type + "\n" + inputName + "\n" + formElement.type.indexOf("select"));
						if(inputValue.indexOf("div") > -1)
						{
							var divArray = cells[1].getElementsByTagName("div");
							for(var divNum=0;divNum<divArray.length;divNum++)
							{
								//alert(divArray[divNum].innerHTML);
								for(var i=0;i<formElement.length;i++)
								{
									if(divArray[divNum].innerHTML.indexOf(formElement.options[i].value) > -1)
										formElement.options[i].selected = true;
								}
							}
						}else
						{
							for(var i=0;i<formElement.length;i++)
							{
								if(inputValue.indexOf(formElement.options[i].value) > -1)
									formElement.options[i].selected = true;
							}
						}
					} else if(formElement.type.indexOf("radio") > -1)
					{
						for(var i=0;i<formElement.length;i++)
						{
							if(inputValue.indexOf(formElement[i].value) > -1)
								formElement[i].checked = true;
						}
					} else if(formElement.type.indexOf("checkbox") > -1)
					{
						if(inputValue.indexOf(formElement.value) > -1)
							formElement.checked = true;
					} else
						debugVar.dumpToDebug("ERROR: unhandled form type - " + formElement.type);
				}else
					debugVar.dumpToDebug("ERROR: " + inputName + " form element not found.");
			}
		}
	}
}
function resetForm()
{
	for(var j=0;j<document.inputForm.elements.length;j++)
	{
		var formElement = document.inputForm.elements[j];
		if(formElement.type.indexOf("text") > -1)
		{
			formElement.value = "";
		}else if(formElement.type.indexOf("select") != -1)
		{
			for(var i=0;i<formElement.length;i++)
			{
				formElement.options[i].selected = false;
			}
		} else if(formElement.type.indexOf("radio") > -1)
		{
			for(var i=0;i<formElement.length;i++)
			{
				formElement[i].checked = false;
			}
		} else if(formElement.type.indexOf("checkbox") > -1)
		{
				formElement.checked = false;
		} else
			debugVar.dumpToDebug("ERROR: unhandled form type - " + formElement.type);
		
	}
}
function highlightBorder()
{
	var e = (window.event)?window.event:arguments[0];
	var target = e.target;				// DOM standard event model
	if(!target)	target = e.srcElement;	// IE event model
	var parentNode = target.parentNode;
	var loopCount = 0;
	var maxLoops = 25;
	var parentFound = true;
	if(parentNode)
	{
		if(parentNode.id)
		{
			while(parentNode.id.indexOf("divHolder") == -1)
			{
				if(loopCount > maxLoops)
				{
					parentFound = false;
					break;
				}
				parentNode = parentNode.parentNode;
				loopCount++;
			}
			if(parentNode.id.indexOf("divHolder") > -1 && parentFound)
			{
				if(e.type.indexOf("over") > -1)
					parentNode.style.border = "1px solid white";
				else
					parentNode.style.border = "0px solid transparent";
			}
		}
	}
}

function grabByRestypes()
{
	var domArray = new Array();
	for(var i=1;i<=12;i++)
	{
		var uri = "/lib/resource/datagetter.xml.php?restype="+i;
		domArray[i] = requestXML(uri, "div");
	}
	return domArray;
}
function getMatchingTables(searchString, domTree)
{
	var escapeTheseCharacters = Array("\\", "^", "$", "[", "]", "{", "}", "<", ">", "?", ".", "+", "*", "|");
	for(var i=0;i<escapeTheseCharacters.length;i++)
	{
		searchString.replace(escapeTheseCharacters[i], "\\"+escapeTheseCharacters[i]);
	}
	var regularExpression = new RegExp(searchString, "i");
	if(typeof domTree == "object")
	{
		var matches = document.createElement(domTree.tagName);
		var childNodes = domTree.childNodes;
		if(childNodes != null)
		{
			for(var i=0;i<childNodes.length;i++) // >
			{
				if(childNodes[i] != null)
				{
					if(regularExpression.test(childNodes[i].innerHTML) == true)
					{
						clone=childNodes[i].cloneNode(true);
						var divHolder = document.createElement("div");
						divHolder.id = "divHolder"+i;
						divHolder.appendChild(clone);
						var divClickHere = document.createElement("div");
						divClickHere.className = "XMLContentClickHere";
						divClickHere.appendChild(document.createTextNode("Click Here to Edit This Entry"));
						divHolder.appendChild(divClickHere);
						matches.appendChild(divHolder);
						//eventRegistrar.registerFunction(divHolder, "mouseout", "highlightBorder(\"divHolder"+i+"\", \"0px solid transparent;\")");
						//eventRegistrar.registerFunction(divClickHere, "click", copyToForm);
					}
				}
			}
		}
		return matches;
	}else
		return false;
	/*
	var parentElement = domTree.parentNode;
	//alert(domTree.parentNode);
	if(parentElement != null)
	{
		clone=domTree.cloneNode(true);
		parentElement.replaceChild(clone, domTree);
	}
	*/
}

function onloadinit()
{
	if(onloadFuncs.getPageLoaded() === false)
	{
		document.getElementById("loadingDiv").style.visibility = "hidden";
		changerObject = new ColorFade('changerObject', 'alertDiv2Border');
		changerObject.toggleBackgroundColorFade('alertDiv2Border', '#006', '#fff', 'transparent');
		debugVar.assignElement("debugDivId");
		restypeDOMElements = grabByRestypes();
	}else
	{
	}
	for(var i = 0; i < document.forms.length; i++)
	{
		for(var j = 0; j < document.forms[i].elements.length; j++)
		{
			if(document.forms[i].elements[j].type == "text" || document.forms[i].elements[j].type == "textarea")
				eventRegistrar.registerFunction(document.forms[i].elements[j], "keyup", formEvent);
			else if(document.forms[i].elements[j].type.indexOf("select") > -1 || document.forms[i].elements[j].type.indexOf("radio") > -1 || document.forms[i].elements[j].type.indexOf("checkbox") > -1)
				eventRegistrar.registerFunction(document.forms[i].elements[j], "change", formEvent);
			else if(document.forms[i].elements[j].name != "")
				debugVar.dumpToDebug(document.forms[i].elements[j].name + " not added to the event list.");
		}
	}
	//debugVar.dumpToDebug(eventRegistrar.functionsRegistered + " form elements have an event registered. " + onloadFuncs.getPageLoaded());
	onloadFuncs.setPageLoaded(true);
}
