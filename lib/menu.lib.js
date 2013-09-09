function createDropDown(e, styleClass, ddid)
{
	var e = (window.event)?window.event:arguments[0];
	var target = e.target;				// DOM standard event model
	if(!target)	target = e.srcElement;	// IE event model
	var destroyMethod = ((arguments[3] != null)?arguments[3]:"mouseout");
	// var dropDownDiv = document.createElement("div");
	var dropDownDiv = dropDownContents.cloneNode(true);
	dropDownDiv.id = "dd"+dropDowns.length+Math.round(Math.random()*1000);
	dropDownDiv.style.position = "absolute";
	var xy = getAbsolutePos(target, "below");
	try
	{
		dropDownDiv.style.left = (xy[0]+1)+"px";
		dropDownDiv.style.top = (xy[1]+1)+"px";
	}catch(e)
	{
		alert(e.name+": "+e.message+'\n'+(xy[0])+'\n'+(xy[1]));
	}
	dropDownDiv.style.border = "1px solid black";
	dropDownDiv.style.width = target.offsetWidth+"px";
	dropDownDiv.style.padding = "3px";
	dropDownDiv.style.margin = "0px";
	dropDownDiv.style.color = "white";
	dropDownDiv.style.zindex = "99999";
	dropDownDiv.destruct = destroyMethod;
	try
	{
		dropDownDiv.style.backgroundColor = "gray";
	} catch (e)
	{
		dropDownDiv.style.backgroundColor = "grey";
	}
	//alert(dropDownContents.innerHTML)
	//dropDownDiv.appendChild(dropDownContents);
	//dropDownDiv.appendChild(document.createTextNode("test"));
	if(destroyMethod == "mouseout")
		eventRegistrar.registerFunction(dropDownDiv, "mouseover", delayDestruction);
	document.getElementsByTagName("body")[0].appendChild(dropDownDiv);
	//registerAllChildren(dropDownDiv, "mouseover", delayDestruction);
	dropDowns[dropDowns.length] = dropDownDiv;
}
function registerAllChildren(primarynode, eventType, functionName)
{
	var iterator
	if((iterator = document.createNodeIterator(primarynode, NodeFilter.SHOW_ELEMENT, null, false)) == true)
	{
		alert("traversal available");
		//var iterator = document.createNodeIterator(node, NodeFilter.SHOW_ALL, null, false);
		var loopNode;
		while((loopNode = iterator.nextNode()) != null)
		{
			eventRegistrar.registerFunction(loopNode, eventType, functionName);
			alert(loopNode.tagName + '\n' + loopNode.id);
		}
	}else
		alert(typeof iterator);
	/*
	alert(node.firstChild);
	for(var i=0;i<node.length;i++)
	{
		//eventRegistrar.registerFunction(nodeArray[i], eventType, functionName);
		//if(nodeArray[i].childNodes.length > 0)
		//	registerAllChildren(nodeArray[i].childNodes, functionName);
	}
	alert(nodeArray[i].tagName + '\n' + nodeArray[i].id);
	*/
}
function beginDestroy()
{
	setTimeout('destroyDropDown(null)', delay);
}
function destroyDropDown()
{
	//debugVar.dumpToDebug(destroyNow + ' = destroyNow');
	if(arguments[0])
	{
		var argument = arguments[0];
		if(argument.type.indexOf("mouse") > -1)
			argument = "all";
	}else
		argument = "all";
	if(destroyNow === false)
		argument = "none";
	if(argument.toLowerCase() == "all" || argument.toLowerCase() == "")
	{
		for(var i=0;i<dropDowns.length;i++)
			if(document.getElementById(dropDowns[i].id).parentNode)
				document.getElementById(dropDowns[i].id).parentNode.removeChild(dropDowns[i]);
			else
			{
				alert(document.getElementById(dropDowns[i].id).id+" parent not found...");
			}
		dropDowns = Array();
	} else if(typeof argument == "string" && argument != "none")
		document.getElementById(argument).parentElement.removeChild(dropDowns[0]);
	else if(typeof argument == "object")
		argument.parentElement.removeChild(argument);
}
var destroyNow = true;
function delayDestruction(e)
{
	destroyNow = false;
	var e = (window.event)?window.event:arguments[0];
	var target = e.target;				// DOM standard event model
	if(!target)	target = e.srcElement;	// IE event model
	eventRegistrar.registerFunction(target, "mouseover", highlightElement);
	eventRegistrar.registerFunction(target, "mouseover", delayDestruction);
	eventRegistrar.registerFunction(target, "mouseout", highlightElement);
	eventRegistrar.registerFunction(target, "mouseout", goDestruction);
	eventRegistrar.registerFunction(target, "mouseout", beginDestroy);
}
function goDestruction(e)
{
	var e = (window.event)?window.event:arguments[0];
	var target = e.target;				// DOM standard event model
	if(!target)	target = e.srcElement;	// IE event model
	//debugVar.dumpToDebug(target.parentNode.id + '\n' + target.parentNode.id.substr(0,2));
	if(target.parentNode.id.substr(0,2) != "dd")
		destroyNow = true;
	else
		destroyNow = false;
}
function mouseoutalert(e)
{
	var e = (window.event)?window.event:arguments[0];
	var target = e.target;				// DOM standard event model
	if(!target)	target = e.srcElement;	// IE event model
	alert(target.tagName + '\n' + target.id);
}
function getAbsolutePos(target)
{
	var offsetTopVar	=	((arguments[1].indexOf("below") > -1)	?target.offsetTop+target.offsetHeight	:0);
	var offsetLeftVar	=	((arguments[1].indexOf("right") > -1)	?target.offsetLeft+target.offsetWidth	:0);
	var targetChild		=	target.firstChild;
	var offsetTopVarTry		= targetChild.offsetTop;
	var offsetLeftVarTry	= targetChild.offsetLeft;
	if(!isNaN(offsetTopVarTry) && !isNaN(offsetLeftVarTry))
	{
		offsetTopVar += offsetTopVarTry;
		offsetLeftVar += offsetLeftVarTry;
	}
	var parentElementVar = target.offsetParent;
	while(parentElementVar != null)
	{
		offsetTopVar	=	offsetTopVar	+	parentElementVar.offsetTop;
		offsetLeftVar	=	offsetLeftVar	+	parentElementVar.offsetLeft;
		parentElementVar = parentElementVar.offsetParent;
	}
	return Array(offsetLeftVar, offsetTopVar);
}
function highlightElement(e)
{
	var e = (window.event)?window.event:arguments[0];
	var target = e.target;				// DOM standard event model
	if(!target)	target = e.srcElement;	// IE event model
	//debugVar.dumpToDebug(e.type + ' '+ target.id + ' ' + target.tagName);
	if(e.type.indexOf("over") > -1)
		target.className = "mouseoverCat";
	else
		target.className = "mouseoutCat";
}
