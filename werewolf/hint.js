/**********************************************************************************   
PopupDescriptions 
*   Copyright (C) 2001 Thomas Brattli
*   This script was released at DHTMLCentral.com
*   Visit for more great scripts!
*   This may be used and changed freely as long as this msg is intact!
*   We will also appreciate any links you could give us.
*
*   Made by Thomas Brattli
*
*   Script date: 09/04/2001 (keep this date to check versions) 
*********************************************************************************/
function lib_bwcheck(){ //Browsercheck (needed)
	this.ver=navigator.appVersion
	this.uagent=navigator.userAgent
	this.dom=document.getElementById?1:0
	this.opera5=(navigator.userAgent.indexOf("Opera")>-1 && document.getElementById)?1:0
	this.ie5=(this.ver.indexOf("MSIE 5")>-1 && this.dom && !this.opera5)?1:0; 
	this.ie6=(this.ver.indexOf("MSIE 6")>-1 && this.dom && !this.opera5)?1:0;
	this.ie4=(document.all && !this.dom && !this.opera5)?1:0;
	this.ie=this.ie4||this.ie5||this.ie6
	this.mac=this.uagent.indexOf("Mac")>-1
	this.ns6=(this.dom && parseInt(this.ver) >= 5) ?1:0; 
	this.ns4=(document.layers && !this.dom)?1:0;
	this.bw=(this.ie6 || this.ie5 || this.ie4 || this.ns4 || this.ns6 || this.opera5)
	return this
}

var bw=lib_bwcheck()

/*******************************************************************************
	Variables to set:
*******************************************************************************/

fromX = 50 //How much from the actual mouse X should the description box appear?
fromY = -20 //How much from the actual mouse Y should the description box appear?

//To set the font size, font type, border color or remove the border or whatever,
//change the clDescription class in the stylesheet.

//Makes crossbrowser object.
function makeObj(obj){								
  	this.evnt=bw.dom? document.getElementById(obj):bw.ie4?document.all[obj]:bw.ns4?document.layers[obj]:0;
	if(!this.evnt) return false
	this.css=bw.dom||bw.ie4?this.evnt.style:bw.ns4?this.evnt:0;	
   	this.wref=bw.dom||bw.ie4?this.evnt:bw.ns4?this.css.document:0;		
	this.writeIt=b_writeIt;																
	return this
}

// A unit of measure that will be added when setting the position of a layer.
var px = bw.ns4||window.opera?"":"px";

function b_writeIt(text){
	if (bw.ns4){this.wref.write(text);this.wref.close()}
	else this.wref.innerHTML = text
}

//Capturing mousemove
var descx = 0
var descy = 0
function popmousemove(e){descx=bw.ns4||bw.ns6?e.pageX:event.x; descy=bw.ns4||bw.ns6?e.pageY:event.y}

var oDesc;
//Shows the messages
function show_hint(message){
	if(oDesc) {
		oDesc.writeIt('<div class="clDescription">'+message+'</div>')
		if(bw.ie5||bw.ie6) descy = descy+document.body.scrollTop
		oDesc.css.left = (descx+fromX)+px
		oDesc.css.top = (descy+fromY)+px
		oDesc.css.visibility = "visible"
    }
}
//Hides it
function hide_hint(){
	if(oDesc) oDesc.css.visibility = "hidden"
}

function setHint(){
  	if(bw.ns4)document.captureEvents(Event.MOUSEMOVE)
    document.onmousemove = popmousemove;
	oDesc = new makeObj('divDescription')
}
