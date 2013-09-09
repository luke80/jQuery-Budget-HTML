function dumpToDebug(debugdivid, text, replaceText)
{
	var debugInfo = document.createElement("div");
	debugInfo.appendChild(document.createTextNode(text));
	if(!replaceText)
		document.getElementById(debugdivid).appendChild(debugInfo);
	else
		document.getElementById(debugdivid).innerHTML = debugInfo.innerHTML;
}
function resizeBody(bodyID, compareID)
{
	var menuDiv = document.getElementById("menuDiv");
	var menuClone = menuDiv.cloneNode(true);
	var menuParent = menuDiv.parentNode;
	menuParent.replaceChild(menuClone, menuDiv);
	/*
	var parentParent = menuParent.parentNode;
	var parentClone = menuParent.cloneNode(true);
	parentParent.replaceChild(parentClone, menuParent);
	*/
	document.getElementById(bodyID).style.height = ((document.getElementById(compareID).scrollHeight > document.getElementById(bodyID).scrollHeight)?document.getElementById(compareID).scrollHeight + "px":document.getElementById(bodyID).scrollHeight + 'px');
}
function isPrintable(code)
{
	if(
			code == 8	|| code == 46	|| code == 48	|| code == 49	|| code == 50	|| 
			code == 51	|| code == 52	|| code == 53	|| code == 54	|| code == 55	|| 
			code == 56	|| code == 57	|| code == 59	|| code == 61	|| code == 65	|| 
			code == 66	|| code == 67	|| code == 68	|| code == 69	|| code == 70	|| 
			code == 71	|| code == 72	|| code == 73	|| code == 74	|| code == 75	|| 
			code == 76	|| code == 77	|| code == 78	|| code == 79	|| code == 80	|| 
			code == 81	|| code == 82	|| code == 83	|| code == 84	|| code == 85	|| 
			code == 86	|| code == 87	|| code == 88	|| code == 89	|| code == 90	|| 
			code == 109	|| code == 188	|| code == 190	|| code == 191	|| code == 192	|| 
			code == 219	|| code == 221	|| code == 220	|| code == 222
		)
		return true;
	else
		return false;
}

function checkfield(e,texta)
{
	if (!e) var e = window.event;
	if(typeof e == "string")
		var targetElement = document.getElementById(e);
	else
		var targetElement = (window.event) ? e.srcElement : e.target;
	fieldname = targetElement.id;
	field=document.getElementById(fieldname);
	if(!field.multiple && field.length-field.selectedIndex!=1)
	return;
	//alert(field.length);
	//alert(field.selectedIndex);
	win.setSize(200,100);
	win.offsetX = -50;
	win.offsetY = -50;
	var multiples='';
	if(field.multiple)
	{
	multiples="<input type=hidden name=selecteditems value='";
	for(i=0;i<field.length;i++)
	  if(field.options[i].selected)
		if(field.options[i].value!="")
		  multiples+=field.options[i].value + "|";
		else
		  multiples+=field.options[i].text + "|";
	multiples+="'>";
	}
	var contents="<title>New " + texta + "</title>";
	contents+="<form action='addselectvalue.php' method=post>";
	contents+="<input type=hidden name=restype value='1'>";
	contents+=multiples;
	contents+="<input type=hidden name=fieldname value='" + field.id + "'>";
	contents+="<b>New " + texta + "</b>: <input name=newvalue><br>";
	contents+="<input type=submit value=' Add '></form>";
	win.populate(contents);
	win.showPopup('APOS' + field.id);
}
