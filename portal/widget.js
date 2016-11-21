/*	Project:	EQdkp-Plus
 *	Package:	EQdkp-Plus
 *	Link:		http://eqdkp-plus.eu
 *
 *	Copyright (C) 2006-2016 EQdkp-Plus Developer Team
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU Affero General Public License as published
 *	by the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU Affero General Public License for more details.
 *
 *	You should have received a copy of the GNU Affero General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
 
var EQdkpPortal = new function(){
	
	var url = false;
	var target = false;
	var moduleID = false;
	var query = null;
	//Default Values
	var wide = 0;
	var position = "left";
	var header = 1;
	var random_value = false;
	var context = new Array();
	
	
	this.init = function(intModuleID, strRandomValue, eqdkp_url){
		moduleID = intModuleID;
		
		random_value = strRandomValue || "";
		if (random_value != "") random_value = random_value + "_";
		target = "eqdkp_portal_"+random_value + intModuleID;
		
		url = eqdkp_url || url;
		if(!url) getURL();
		saveContext();
		addResources(target);
		resetValues();
	}
	
	this.setVar = function(varname, value){
		if (varname == "header"){
			header = value;
		}
		if (varname == "wide"){
			wide = value;
		}
		if (varname == "position"){
			position = value;
		}
		if(varname == "url"){
			url = value;
		}
		saveContext();
	}
	
	function getURL(){
		var scripts = document.getElementsByTagName("script");
		for(var i = 0; i< scripts.length; i++){
			if (scripts[i].src != undefined && scripts[i].src != ""){
				var src = scripts[i].src;
				
				if (src.indexOf("/portal/widget.js") !=-1){
					var src = scripts[i].src;
					url = src.substr(0, src.length-16);
				}
			}
		}
	}
	
	function addResources(localtarget){
		var scripts = document.getElementsByTagName("script");
		var loaded = false;
		for(var i = 0; i< scripts.length; i++){
			if (scripts[i].src != undefined && scripts[i].src != ""){
				var src = scripts[i].src;
				
				if (src.indexOf("/jquery/core/core.js") !=-1){
					loaded = true;
				}
			}
		}
		
		if (!loaded){
			var mycontext = context[localtarget];
			if(typeof mycontext != "undefined"){
				url = mycontext[4];
			}
			
			
			var head = document.getElementsByTagName("head")[0];
			//Jquery CSS
			
			var ac = document.createElement("link");
			ac.href = url + "libraries/FontAwesome/font-awesome.min.css";
			ac.type = 'text/css';
			ac.rel = 'stylesheet';
			head.appendChild(ac);
			
			//JQuery core
			var aj = document.createElement("script");
			aj.src = url  + "libraries/jquery/core/core.js";
			aj.type = 'text/javascript';
			aj.onload=function(){scriptLoaded(localtarget)};
			head.appendChild(aj);
		} else {
			getModule(localtarget);
		}
	}

	function scriptLoaded(localtarget){
		jQuery.noConflict();
		getModule(localtarget);
	}

	function getModule(localtarget){
		var xmlHttpObject = false;
		var mycontext = context[localtarget];
		if(typeof mycontext != "undefined"){
			moduleID = mycontext[0];
			position = mycontext[1];
			header = mycontext[2];
			wide = mycontext[3];
			url = mycontext[4];
		}

		if (typeof XMLHttpRequest != 'undefined') {
			xmlHttpObject = new XMLHttpRequest();
		}

		if (!xmlHttpObject) {
			try {
				xmlHttpObject = new ActiveXObject("Msxml2.XMLHTTP");
			} catch(e) {
				try {
					xmlHttpObject = new ActiveXObject("Microsoft.XMLHTTP");
				} catch(e) {
					xmlHttpObject = false;
				}
			}
		}
		
		if (xmlHttpObject){
			query = xmlHttpObject;
			if (query.readyState == 4 || query.readyState == 0) {
				query.open("GET", url+'exchange.php?out=portal&id=' + moduleID+'&header='+header+'&position='+position+'&wide='+wide, true);
				query.onreadystatechange = function(){
					if (this.readyState == 4) {
						result = this.responseText;
						html = jQuery.parseHTML(result,document, true);
						document.getElementById(localtarget).innerHTML = html[1].innerHTML;
						jQuery('head').append(html[0].innerHTML);
					}
				}; 
				query.send(null);
			}
			
		}		
	}
	
	function resetValues(){
		position = "left";
		header = 1;
		width = 0;
		url = false;
		random_value = false;
		target = false;
	}
	
	function saveContext(){
		if(target == false) return;
		context[target] = new Array(moduleID, position, header, wide, url, random_value);
	}
}