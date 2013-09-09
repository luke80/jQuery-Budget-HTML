/*
//		This script contains an object with number of methods.  Parameters are as follows:  (The first is intended to be the only oned called on the page)
//
//	First a varibale must be defined calling the ColorFade constructor.
//	The following are the methods of this object.
//
//	toggleBackgroundColorFade( %HTMLElementId target%,  %CSSString beginColor%,  %CSSString endColor%,  %CSSString offColor%, %boolean debugInfo% )
//		NO RETURN VALUE meant to start or stop the loop, not compute a value
//	fadeBackgroundColor( %HTMLElementId target%,  %CSSString beginColor%,  %CSSString endColor%,  %CSSString offColor% )
//		NO RETURN VALUE meant to start the loop, not compute a value
//	loopColorChange( %HTMLElementId target%,  %CSSString offColor% )
//		NO RETURN VALUE intended to be called by the previous function only
//	parseColorFromCSS( %CSSString color% )
//		returns an array with three elements one for each color from 0 to 255 ordered red green blue
//	cssFormat( %colorArray from parseColorFromCSS% )
//		returns a string formatted rgb(red, green, blue) for use with CSS inputs
//	
*/
function ColorFade(variableName) //, htmlElementId, beginColor, endColor, stopColor, debugBool
{
	this.interval = 0;
	this.backgroundToggle = false;
	this.beginColor = new Color("white");
	this.endColor = new Color("white");
	this.colorStep = new Color("white");
	this.currentStep = 0;
	this.stepCount = 25;
	this.variableName = variableName;
	this.htmlElement = document.getElementById(arguments[1]);
	if(typeof arguments[2] != "undefined")
		this.beginColor = new Color(arguments[2]);
	if(typeof arguments[3] != "undefined")
		this.endColor = new Color(arguments[3]);
	if(typeof arguments[4] != "undefined")
		this.stopColor = arguments[4];
	if(typeof arguments[5] != "undefined")
		this.debugBool = arguments[5];
}
ColorFade.prototype.toggleBackgroundColorFade = function(elementId) //,beginColor, endColor, stopColor, debugBool
{
	this.htmlElement = document.getElementById(elementId);
	if(typeof arguments[1] != "undefined")
		this.beginColor = new Color(arguments[1]);
	if(typeof arguments[2] != "undefined")
		this.endColor = new Color(arguments[2]);
	if(typeof arguments[3] != "undefined")
		this.stopColor = arguments[3];
	if(typeof arguments[4] != "undefined")
		this.debugBool = arguments[4];
	if(typeof this.debugBool != "undefined" && this.debugBool == true)
	{
		if(document.getElementById("debugDivId") === null)
		{
			this.debugDiv = document.createElement("div");
			this.debugDiv.id = "debugDivId";
			this.debugDiv.innerHTML = "";
			document.getElementsByTagName("body")[0].appendChild(this.debugDiv);
		}
	}
	if(!this.backgroundToggle)
	{
		this.backgroundToggle = true;
		// alert(typeof this.beginColor.rgbCSS);
		this.fadeBackgroundColor(this.htmlElement.id, ((typeof this.beginColor == "object")?this.beginColor.rgbCSS:((typeof arguments[1] != "undefined")?arguments[1]:null)), ((typeof this.endColor == "object")?this.endColor.rgbCSS:((typeof arguments[2] != "undefined")?arguments[2]:null)), arguments[3], arguments[4]);
	} else
		this.backgroundToggle = false;
}
ColorFade.prototype.startFade = function()
{
	this.backgroundToggle = true;
	this.toggleBackgroundColorFade(this.htmlElement.id);
}
ColorFade.prototype.stopFade = function()
{
	this.backgroundToggle = false;
	//this.toggleBackgroundColorFade(this.htmlElement.id);
}
ColorFade.prototype.fadeBackgroundColor = function(elementId)
{
	this.htmlElement = document.getElementById(elementId);
	this.color1 = new Color((typeof arguments[1] == "string")?arguments[1]:htmlElement.style.backgroundColor); //parseColorFromCSS((typeof arguments[1] == "string")?arguments[1]:htmlElement.style.backgroundColor);
	this.fromStyle = new Color((document.getElementById(elementId).currentStyle)?document.getElementById(elementId).currentStyle.backgroundColor:window.getComputedStyle(document.getElementById(elementId), null).backgroundColor); //parseColorFromCSS((document.getElementById(elementId).currentStyle)?document.getElementById(elementId).currentStyle.backgroundColor:window.getComputedStyle(document.getElementById(elementId), null).backgroundColor);
	this.beginColor = this.color1;
	this.endColor = (typeof arguments[2] != "undefined")?new Color(arguments[2]):this.fromStyle; //((arguments[2] != "")?((typeof arguments[2] == "array")?arguments[2]:parseColorFromCSS(arguments[2])):fromStyle);
	this.colorStep = Array((Math.abs(this.beginColor.rgbArray[0] - this.endColor.rgbArray[0]) / this.stepCount), (Math.abs(this.beginColor.rgbArray[1] - this.endColor.rgbArray[1]) / this.stepCount), (Math.abs(this.beginColor.rgbArray[2] - this.endColor.rgbArray[2]) / this.stepCount));
	if(typeof arguments[3] != "undefined")
		this.stopColor = new Color(arguments[3]);
	if(arguments[4] == true)
	{
		dumpToDebug("debugDivId", "beginColor CSS: " + this.beginColor.rgbCSS, false);
		dumpToDebug("debugDivId", "endColor CSS: " + this.endColor.rgbCSS, false);
		dumpToDebug("debugDivId", "step at loop: " + this.colorStep[0]			+	", " + this.colorStep[1]			+ ", " + this.colorStep[2]				+ "", false);
	}
	document.getElementById(elementId).style.backgroundColor = this.color1.rgbCSS;
	this.backgroundToggle = true;
	this.loopColorChange(elementId, arguments[3], arguments[4]);
}
ColorFade.prototype.loopColorChange = function(divId)
{
	newColorString = "rgb(";
	for(var i = 0; i < 3; i++)
	{
		newColorString += ((this.beginColor.rgbArray[i]) > (this.endColor.rgbArray[i]))?((this.beginColor.rgbArray[i]) - Math.ceil(this.currentStep*this.colorStep[i])):((this.beginColor.rgbArray[i]) + Math.ceil(this.currentStep*this.colorStep[i]));
		newColorString += ", ";
	}
	newColorString = newColorString.substring(0, newColorString.length-2);
	newColorString +=	")";
	this.newColor = new Color(newColorString);
	if(arguments[2] == true)
	{
		dumpToDebug("debugDivId", "", true);
		dumpToDebug("debugDivId", "beginColor CSS: "	+ this.beginColor.rgbCSS, false);
		dumpToDebug("debugDivId", "endColor CSS: "		+ this.endColor.rgbCSS, false);
		dumpToDebug("debugDivId", "step at loop: "			+ this.colorStep[0]		+	", " + this.colorStep[1]	+ ", " + this.colorStep[2]	+ " ", false);
		dumpToDebug("debugDivId", "currentColor CSS: "	+ this.newColor.rgbCSS, false);
	}
	this.htmlElement.style.backgroundColor = this.newColor.rgbCSS;
	this.currentStep++;
	if
		(
			( this.beginColor.rgbArray[0] != this.endColor.rgbArray[0] && ((this.beginColor.rgbArray[0] > this.endColor.rgbArray[0] && this.newColor.rgbArray[0] <= this.endColor.rgbArray[0]) || (this.beginColor.rgbArray[0] < this.endColor.rgbArray[0] && this.newColor.rgbArray[0] >= this.endColor.rgbArray[0]))) || 
			( this.beginColor.rgbArray[1] != this.endColor.rgbArray[1] && ((this.beginColor.rgbArray[1] > this.endColor.rgbArray[1] && this.newColor.rgbArray[1] <= this.endColor.rgbArray[1]) || (this.beginColor.rgbArray[1] < this.endColor.rgbArray[1] && this.newColor.rgbArray[1] >= this.endColor.rgbArray[1]))) || 
			( this.beginColor.rgbArray[2] != this.endColor.rgbArray[2] && ((this.beginColor.rgbArray[2] > this.endColor.rgbArray[2] && this.newColor.rgbArray[2] <= this.endColor.rgbArray[2]) || (this.beginColor.rgbArray[2] < this.endColor.rgbArray[2] && this.newColor.rgbArray[2] >= this.endColor.rgbArray[2])))
		)
	{
		var placeKeeper =	new Color(this.endColor.rgbCSS);
		this.endColor = 	new Color(this.beginColor.rgbCSS);
		this.beginColor = 	new Color(placeKeeper.rgbCSS);
		this.currentStep = 0;
	}
	if(this.backgroundToggle)
	{
		setTimeout(this.variableName + ".loopColorChange('" + divId + "'" + ((typeof arguments[1] != "undefined")?", '" + arguments[1] + "'":", null") + ((typeof arguments[2] != "undefined")?", " + arguments[2] + "":", null") + ")", this.interval) //, '" + bgURL + "'
	}  else
	{
		if(typeof arguments[1] == "undefined")
			 this.htmlElement.style.backgroundColor = this.newColor.rgbCSS;
		else
			this.htmlElement.style.backgroundColor = arguments[1];
	}
}

function Color(colorString)
{
	this.rgbArray = new Array();
	this.cssFormat = function()
	{
		this.rgbCSS = "rgb(" + this.rgbArray[0]+", " + this.rgbArray[1] + ", " + this.rgbArray[2]+")";
		return "rgb(" + this.rgbArray[0]+", " + this.rgbArray[1] + ", " + this.rgbArray[2]+")";
	}
	this.cssFormatHex = function()
	{
		this.hexRed = this.rgbArray[0].toString(16);
		this.hexGreen = this.rgbArray[1].toString(16);
		this.hexBlue = this.rgbArray[2].toString(16);
		if(this.hexRed.length == 1)
			this.hexRed = "0"+this.hexRed;
		if(this.hexGreen.length == 1)
			this.hexGreen = "0"+this.hexGreen;
		if(this.hexBlue.length == 1)
			this.hexBlue = "0"+this.hexBlue;
		this.hexCSS = "#"+this.hexRed+""+this.hexGreen+""+this.hexBlue+"";
		return "#"+this.hexRed+""+this.hexGreen+""+this.hexBlue+"";
	}
	this.parseColorFromCSS = function()
	{
		this.matchSixDigitHex		= new RegExp("^#?([0123456789abcdef]{2})([0123456789abcdef]{2})([0123456789abcdef]{2})$", "i");
		this.matchThreeDigitHex		= new RegExp("^#?([0123456789abcdef]{1})([0123456789abcdef]{1})([0123456789abcdef]{1})$", "i");
		this.matchStyleSheetFormat	= new RegExp("^rgb.(-?[0123456789]{1,3}),[ \s](-?[0123456789]{1,3}),[ \s](-?[0123456789]{1,3}).$", "i");
		if(this.matchSixDigitHex.test(this.colorString))
		{
			this.matchArray = this.matchSixDigitHex.exec(this.colorString);
			this.rgbArray[0] = parseInt(this.matchArray[1], 16);  //red
			this.rgbArray[1] = parseInt(this.matchArray[2], 16);  //green
			this.rgbArray[2] = parseInt(this.matchArray[3], 16);  //blue
		} else if(this.matchThreeDigitHex.test(this.colorString))
		{
			this.matchArray = this.matchThreeDigitHex.exec(this.colorString);
			this.rgbArray[0] = parseInt("" + this.matchArray[1] + this.matchArray[1], 16);  //red
			this.rgbArray[1] = parseInt("" + this.matchArray[2] + this.matchArray[2], 16);  //green
			this.rgbArray[2] = parseInt("" + this.matchArray[3] + this.matchArray[3], 16);  //blue
		} else if(this.matchStyleSheetFormat.test(this.colorString))
		{
			this.matchArray = this.matchStyleSheetFormat.exec(this.colorString);
			this.rgbArray[0] = parseInt(this.matchArray[1]);  //red
			this.rgbArray[1] = parseInt(this.matchArray[2]);  //green
			this.rgbArray[2] = parseInt(this.matchArray[3]);  //blue
		} else
		{
			this.colorHex = this.translateCSScolorName(this.colorString);
			if(this.matchSixDigitHex.test(this.colorHex))
			{
				this.matchArray = this.matchSixDigitHex.exec(this.colorHex);
				this.rgbArray[0] = parseInt(this.matchArray[1], 16);  //red
				this.rgbArray[1] = parseInt(this.matchArray[2], 16);  //green
				this.rgbArray[2] = parseInt(this.matchArray[3], 16);  //blue
			}	else // if(colorHex.toLowerCase() == "transparent")
				alert("ERROR: " + this.colorString + " is not a valid color." + "\n" + this.rgbCSS);
		}
		return this.rgbArray;
	}
	this.translateCSScolorName = function(colorName)
	{
		this.colorNames = new Array(
			"AliceBlue",		"AntiqueWhite",			"Aqua",				"Aquamarine",
			"Azure",			"Beige",				"Bisque",			"Black",
			"BlanchedAlmond",	"Blue",					"BlueViolet",		"Brown",
			"BurlyWood",		"CadetBlue",			"Chartreuse",		"Chocolate",
			"Coral",			"CornflowerBlue",		"Cornsilk",			"Crimson",
			"Cyan",				"DarkBlue",				"DarkCyan",			"DarkGoldenRod",
			"DarkGray",			"DarkGrey",				"DarkGreen",		"DarkKhaki",
			"DarkMagenta",		"DarkOliveGreen",		"Darkorange",		"DarkOrchid",
			"DarkRed",			"DarkSalmon",			"DarkSeaGreen",		"DarkSlateBlue",
			"DarkSlateGray",	"DarkSlateGrey",		"DarkTurquoise",	"DarkViolet",
			"DeepPink",			"DeepSkyBlue",			"DimGray",			"DimGrey",
			"DodgerBlue",		"FireBrick",			"FloralWhite",		"ForestGreen",
			"Fuchsia",			"Gainsboro",			"GhostWhite",		"Gold",
			"GoldenRod",		"Gray",					"Grey",				"Green",
			"GreenYellow",		"HoneyDew",				"HotPink",			"IndianRed",
			"Indigo",			"Ivory",				"Khaki",			"Lavender",
			"LavenderBlush",	"LawnGreen",			"LemonChiffon",		"LightBlue",
			"LightCoral",		"LightCyan",			"LightGoldenRodYellow",	"LightGray",
			"LightGrey",		"LightGreen",			"LightPink",		"LightSalmon",
			"LightSeaGreen",	"LightSkyBlue",			"LightSlateGray",	"LightSlateGrey",
			"LightSteelBlue",	"LightYellow",			"Lime",				"LimeGreen",
			"Linen",			"Magenta",				"Maroon",			"MediumAquaMarine",
			"MediumBlue",		"MediumOrchid",			"MediumPurple",		"MediumSeaGreen",
			"MediumSlateBlue",	"MediumSpringGreen",	"MediumTurquoise",	"MediumVioletRed",
			"MidnightBlue",		"MintCream",			"MistyRose",		"Moccasin",
			"NavajoWhite",		"Navy",					"OldLace",			"Olive",
			"OliveDrab",		"Orange",				"OrangeRed",		"Orchid",
			"PaleGoldenRod",	"PaleGreen",			"PaleTurquoise",	"PaleVioletRed",
			"PapayaWhip",		"PeachPuff",			"Peru",				"Pink",
			"Plum",				"PowderBlue",			"Purple",			"Red",
			"RosyBrown",		"RoyalBlue",			"SaddleBrown",		"Salmon",
			"SandyBrown",		"SeaGreen",				"SeaShell",			"Sienna",
			"Silver",			"SkyBlue",				"SlateBlue",		"SlateGray",
			"SlateGrey",		"Snow",					"SpringGreen",		"SteelBlue",
			"Tan",				"Teal",					"Thistle",			"Tomato",
			"Turquoise",		"Violet",				"Wheat",			"White",
			"WhiteSmoke",		"Yellow",				"YellowGreen",		"Transparent"
			);
		this.colorHex = new Array(
			"#F0F8FF",	"#FAEBD7",	"#00FFFF",	"#7FFFD4",
			"#F0FFFF",	"#F5F5DC",	"#FFE4C4",	"#000000",
			"#FFEBCD",	"#0000FF",	"#8A2BE2",	"#A52A2A",
			"#DEB887",	"#5F9EA0",	"#7FFF00",	"#D2691E",
			"#FF7F50",	"#6495ED",	"#FFF8DC",	"#DC143C",
			"#00FFFF",	"#00008B",	"#008B8B",	"#B8860B",
			"#A9A9A9",	"#A9A9A9",	"#006400",	"#BDB76B",
			"#8B008B",	"#556B2F",	"#FF8C00",	"#9932CC",
			"#8B0000",	"#E9967A",	"#8FBC8F",	"#483D8B",
			"#2F4F4F",	"#2F4F4F",	"#00CED1",	"#9400D3",
			"#FF1493",	"#00BFFF",	"#696969",	"#696969",
			"#1E90FF",	"#B22222",	"#FFFAF0",	"#228B22",
			"#FF00FF",	"#DCDCDC",	"#F8F8FF",	"#FFD700",
			"#DAA520",	"#808080",	"#808080",	"#008000",
			"#ADFF2F",	"#F0FFF0",	"#FF69B4",	"#CD5C5C",
			"#4B0082",	"#FFFFF0",	"#F0E68C",	"#E6E6FA",
			"#FFF0F5",	"#7CFC00",	"#FFFACD",	"#ADD8E6",
			"#F08080",	"#E0FFFF",	"#FAFAD2",	"#D3D3D3",
			"#D3D3D3",	"#90EE90",	"#FFB6C1",	"#FFA07A",
			"#20B2AA",	"#87CEFA",	"#778899",	"#778899",
			"#B0C4DE",	"#FFFFE0",	"#00FF00",	"#32CD32",
			"#FAF0E6",	"#FF00FF",	"#800000",	"#66CDAA",
			"#0000CD",	"#BA55D3",	"#9370D8",	"#3CB371",
			"#7B68EE",	"#00FA9A",	"#48D1CC",	"#C71585",
			"#191970",	"#F5FFFA",	"#FFE4E1",	"#FFE4B5",
			"#FFDEAD",	"#000080",	"#FDF5E6",	"#808000",
			"#6B8E23",	"#FFA500",	"#FF4500",	"#DA70D6",
			"#EEE8AA",	"#98FB98",	"#AFEEEE",	"#D87093",
			"#FFEFD5",	"#FFDAB9",	"#CD853F",	"#FFC0CB",
			"#DDA0DD",	"#B0E0E6",	"#800080",	"#FF0000",
			"#BC8F8F",	"#4169E1",	"#8B4513",	"#FA8072",
			"#F4A460",	"#2E8B57",	"#FFF5EE",	"#A0522D",
			"#C0C0C0",	"#87CEEB",	"#6A5ACD",	"#708090",
			"#708090",	"#FFFAFA",	"#00FF7F",	"#4682B4",
			"#D2B48C",	"#008080",	"#D8BFD8",	"#FF6347",
			"#40E0D0",	"#EE82EE",	"#F5DEB3",	"#FFFFFF",
			"#F5F5F5",	"#FFFF00",	"#9ACD32",	"#FFFFFF"
			);
		for(var i = 0; i < this.colorNames.length; i++)
		{
			if(typeof colorName == "string")
			if(this.colorNames[i].toLowerCase() == colorName.toLowerCase())
			{
				this.matchSixDigitHex		= new RegExp("^#?([0123456789abcdef]{2})([0123456789abcdef]{2})([0123456789abcdef]{2})$", "i");
				this.matchArray = this.matchSixDigitHex.exec(this.colorHex[i]);
				this.rgbArray[0] = parseInt(this.matchArray[1], 16);  //red
				this.rgbArray[1] = parseInt(this.matchArray[2], 16);  //green
				this.rgbArray[2] = parseInt(this.matchArray[3], 16);  //blue
				this.hexCSS = this.colorHex[i];
				return this.colorHex[i];
			}
		}
	}
	this.colorString = colorString;
	this.rgbArray = this.parseColorFromCSS();
	this.rgbCSS = this.cssFormat();
	this.cssFormat();
	this.hexCSS = this.cssFormatHex();
}
