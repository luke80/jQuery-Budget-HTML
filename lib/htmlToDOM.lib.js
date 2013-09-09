function parseHTMLToDOMObject(htmlString)
{
	var defaultHTMLTag = (typeof arguments[1] == "string")?arguments[1]:"span";
	var parentDOMObject = (typeof arguments[1] == "object")?arguments[1]:document.createElement(defaultHTMLTag);
	var defaultHTMLId = (typeof arguments[2] == "string")?arguments[2]:"";
	if(defaultHTMLId != "")
		parentDOMObject.id = defaultHTMLId;
	var returnDOMObject = parentDOMObject;
	var initialLength = htmlString.length;
	returnDOMObject = recurseThroughHTMLString(htmlString, parentDOMObject);
	returnDOMObject.normalize();
	return returnDOMObject;
}
function recurseThroughHTMLString(htmlString, parentElement)
{
	var preTagTextExp = new RegExp("^([^<]+)", "i");
	var preTagText = preTagTextExp.exec(htmlString);
	if(preTagTextExp.test(htmlString))
	{
		var textObject = document.createTextNode(preTagText[0]);
		parentElement.appendChild(textObject);
		var shortenedHTMLString = htmlString.substr(preTagText[0].length);
		recurseThroughHTMLString(shortenedHTMLString, parentElement);
		return parentElement;
	}else if(htmlString.length > 0)
	{
		var beginTagExp = new RegExp("^<([^>\\s\\/]+)\\s?([^>]*)>", "i");
		var beginTag = beginTagExp.exec(htmlString);
		if(beginTagExp.test(htmlString))
			var endTagExp = new RegExp("<(\\/"+beginTag[1]+"+)>", "i");
		else
			var endTagExp = new RegExp("<(\\/"+"\\w"+"+)>", "i");
		var endTag = endTagExp.exec(htmlString);
		if(beginTagExp.test(htmlString))
		{
			var newNode = document.createElement(beginTag[1]);
			if(beginTag[2] != "")
			{
				var splitDefinitions = new RegExp("(\\w+)\\=[\\\"]?([^\\\"]+)[\\\"]?", "g");
				var foundResults = splitDefinitions.exec(beginTag[2]);
				while(foundResults != null)
				{
					newNode = applyDefinitions(newNode, foundResults[1], foundResults[2]);
					foundResults = splitDefinitions.exec(beginTag[2]);
				}//else
				var singleDefinitions = new RegExp("(^|\\s)([abcdefghijklmnopqrstuvwxyz]+)($|\\s)", "ig");
				foundResults = singleDefinitions.exec(beginTag[2]);
				var validSingleProperties = new Array("multiple", "checked", "selected", "inactive", "defer");
				while(foundResults != null)
				{
					if(validSingleProperties.toString().indexOf(foundResults[2].toLowerCase()) > -1)
					{
						newNode = applyDefinitions(newNode, foundResults[1], foundResults[1]);
					}
					foundResults = singleDefinitions.exec(beginTag[2]);
				}//else
			}
			if(!endTagExp.test(htmlString))
			{
				parentElement.appendChild(newNode);
				var shortenedHTMLString = htmlString.substr(beginTag.index+beginTag[0].length);
				recurseThroughHTMLString(shortenedHTMLString, parentElement);
				if(endTagExp.test(shortenedHTMLString))
				{
					var testNest = endTagExp.exec(shortenedHTMLString)
					if(testNest.index == 0)
						return parentElement;
					else
					{
						var shortenedHTMLString = htmlString.substr(endTag.index+endTag[0].length);
						return recurseThroughHTMLString(shortenedHTMLString, parentElement); // parentElement;
					}
				}
				return parentElement;
			}else if(endTagExp.test(htmlString))
			{
				var shortenedHTMLString = htmlString.substr(beginTag.index+beginTag[0].length);
				var newElement = recurseThroughHTMLString(shortenedHTMLString, newNode);
				if(beginTag[1].toLowerCase() == "select" && beginTag[2].toLowerCase().indexOf("multiple") > -1)
				{
					newElement.multiple = "true";
					parentElement.appendChild(newElement);
					try
					{
						newElement.size = newElement.options.length;
						clone=newElement.cloneNode(true);
						parentElement.replaceNode(clone, newElement);
					} catch(e)
					{
						//alert(e.message);
					}
				}else
					parentElement.appendChild(newElement);
				if(endTagExp.test(shortenedHTMLString))
				{
					var testNest = endTagExp.exec(shortenedHTMLString)
					if(testNest.index == 0)
						return parentElement;
					else
					{
						var shortenedHTMLString = htmlString.substr(endTag.index+endTag[0].length);
						return recurseThroughHTMLString(shortenedHTMLString, parentElement); // parentElement;
					}
				}
				return parentElement;
			}
		}else if(!beginTagExp.test(htmlString) && endTagExp.test(htmlString))
		{
			if(endTag.index == 0)
			{
				var shortenedHTMLString = htmlString.substr(endTag[0].length);
				return parentElement;	//	recurseThroughHTMLString(shortenedHTMLString, parentElement, count, initialLength); //
			}else
			{
				var shortenedHTMLString = htmlString.substr(0);
				return recurseThroughHTMLString(shortenedHTMLString, parentElement);
			}
		}else
		{
			return parentElement;
		}
	}// else
	//	alert("done");
	return parentElement;
}
function applyDefinitions(htmlElement, propertyName, propertyValue)
{
	if(htmlElement.getAttribute(propertyName) && propertyName != "style")
	{
		htmlElement.setAttribute(propertyName, propertyValue);
		//eval("htmlElement."+propertyName+" = "+((isNaN(propertyValue))?"\"":"\"")+propertyValue+((isNaN(propertyValue))?"\"":"\""));
	} else if(propertyName == "style")
	{
		htmlElement.style.cssText = propertyValue;
	} else
	{
		if(propertyName == "class")
			htmlElement.className = propertyValue;
		else if(propertyName.match(/((on|key|mouse|load)[^\(]*)\(/i))
		{
			eventRegistrar.registerFunction(htmlElement, propertyName, propertyValue);
		}else if(!propertyName.match(/^\s*$/)  && !propertyValue.match(/^\s*$/))
		{
			//alert(propertyName + " not found in " + htmlElement.tagName + "\n\"" + propertyValue + "\"");
			try
			{
				htmlElement.setAttribute(propertyName, propertyValue);
				//clone=htmlElement.cloneNode(true);
				//htmlElement.replaceNode(clone);
			} catch(e)
			{
				alert(e.message + "\n" + propertyName + " = " + propertyValue + "\n" + "Property NOT set!");
			}
			//alert(propertyName + " not found in " + htmlElement.tagName);
			//alert(htmlElement.getAttributeList());
			//var attributeArray = htmlElement.attributeList;
			//for(var i=0;i<attributeArray.length;i++)
			//	alert(attributeArray[i]);
		}
	}
	htmlElement.normalize();
	return htmlElement;
}