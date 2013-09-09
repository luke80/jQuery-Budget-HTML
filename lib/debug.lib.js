function Debug()
{
	this.htmlElement = document.getElementById(arguments[0]) || null;
	this.replaceText = false;
	
	this.dumpToDebug = function(text) 
	{
		document.getElementsByTagName("body").id = "bodyTag";
		this.replaceText = false;
		if(typeof arguments[1] == "boolean")
			this.replaceText = arguments[1];
		var debugInfo = document.createElement("div");
		debugInfo.appendChild(document.createTextNode(text));
		if(!this.replaceText)
		{
			if(this.htmlElement)
				this.htmlElement.appendChild(debugInfo);
			else if(document.getElementsByTagName("body")[0])
				document.getElementsByTagName("body")[0].appendChild(debugInfo);
			//else
                        //  alert("documentNotLoaded");
		}
		else
		{
			if(this.htmlElement)
				this.htmlElement.innerHTML = debugInfo.innerHTML;
			else
			{
				debugInfo.id = "debugInfoId";
				if(document.getElementById("debugInfoId") == null && document.getElementsByTagName("body")[0])
					document.getElementsByTagName("body")[0].appendChild(debugInfo);
				else if(document.getElementsByTagName("body")[0])
					document.getElementsByTagName("body")[0].replaceChild(debugInfo, document.getElementById("debugInfoId"));
			}
		}
	};
	this.assignElement = function(elementId)
	{
		if(document.getElementById(elementId) == null)
			this.createElement(elementId, arguments[1]);
		this.htmlElement = document.getElementById(elementId);
	};
	this.createElement = function()
	{
		var elementId = (typeof arguments[0] == "string")?arguments[0]:"debugDivId";
		this.htmlElement = document.createElement("div");
		this.htmlElement.id = elementId;
		this.htmlElement.className = (typeof arguments[1] == "string")?arguments[1]:"debugClass";
		if(navigator.userAgent.indexOf("MSIE") != -1)
		{
			if(typeof document.getElementsByTagName("body").id == "string") document.documentElement.appendChild(this.htmlElement);
		}else
			document.documentElement.appendChild(this.htmlElement);	
		return this.htmlElement;
	}
}
