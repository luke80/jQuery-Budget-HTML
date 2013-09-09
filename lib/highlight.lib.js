//<!--

function highlight_background(e, class_name)
{
	if(!e)
		var e = (window.event)?window.event:arguments[0];
	var target = e.target;				// DOM standard event model
	if(!target)	target = e.srcElement;	// IE event model
	if(document.getElementById(arguments[2]))
	{
		target = document.getElementById(arguments[2]);
		//alert(target.innerHTML);
	}
	if(target.className != class_name)
	{
		target.oldClassName = target.className;
		target.className = class_name;
	}else
		target.className = target.oldClassName;
}

//-->