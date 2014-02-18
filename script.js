function $(x){return document.getElementById(x);}
var list,input;
var re1 = /([a-zA-Z0-9\-\.]+\.(com|net|biz|edu|gov|xxx|name|org|info|[a-zA-Z]{2}))[^a-zA-Z0-9\.]/gi;
var re2 = /(js|css|dtd)$/ig;
function inchange(){
	localStorage['ignore']=$('txtIgnore').value;
	
	list.options.length=0;
	var x,a=input.value,b,i;
	if (a.indexOf("<body")>0) {
		$("opt1HTML").selected=true;
	}
	a=a.replace(/\r/g,"");
	b=a.split("\n");
	var opt1 = $('opt1').value;
	var opt2 = $('opt2').value;
	var opt2l = $('txtIgnore').value.split(';');
	var exists = ",";
	masterloop:
	for (var ii=0;ii<b.length;ii++){
		i = b[ii].trim();
		if (i.length==0 && opt1=="comment2") {
			x = new Option("");
			x.className = "listComment";
			list.options.add(x);
			continue;
		}
		if (i.indexOf('#')==0) {
			if (opt1=="ignore") continue;
			if (opt1=="comment"||opt1=="comment2") {
				x = new Option(i);
				x.className = "listComment";
				list.options.add(x);
				continue;
			}
		}
		for (var j=0;j<opt2l.length;j++) {
			if (i.indexOf(opt2l[j])>=0) {
				if (opt2=='none')	continue masterloop;
				if (opt2=='keep') {
					list.options.add(new Option(i));
					continue masterloop;
				}
			}
		}
		i=i+" "; //SHIT CLEAN
		re1.lastIndex=0;
		while((x=re1.exec(i))!=null) {
			if (re2.test(x[1])) continue;
			if (x[1].indexOf('.')==0) continue;
			if (exists.indexOf(","+x[1]+",")>=0) continue;
			list.options.add(new Option(x[1]));
			exists+=x[1]+",";
		}
	}
}
function pageloaded(){
	list = $("domainlist");
	input = $("input");
	$('txtIgnore').value=localStorage['ignore']||$('txtIgnore').value;
	try{
		list.addEventListener("keydown",function(e){
			if ((e.keyCode||e.which)==46) t_del();
			},false);
	}catch(e){}
}
function t_del(){
	for (var i=0;i<list.options.length;i++) {
		if (list.options[i].selected) {
			list.remove(i);
			i--;
		}
	}
}
function t_loc(){
	var h=$('txtIP1').value;
	if (h.length) h+="\t";
	for (var ii=0;ii<list.options.length;ii++) {
		var i=list.options[ii];
		if (i.selected) {
			if (i.className == "listComment") continue;
			re1.lastIndex=0;
			var d = re1.exec(" "+i.textContent+" ");
			if (d==null) continue;
			i.textContent = h+d[1];
		}
	}
}
function t_sel(){
	var p = $('txtPattern1').value;
	var pt = $('txtPattern1t').value;
	if (pt=="wildcard") {
		p=p.replace(/\./g,"\.");
		p=p.replace(/\(/g,"\(");
		p=p.replace(/\)/g,"\)");
		p=p.replace(/\?/g,".");
		p=p.replace(/\*/g,".*");
		p="^"+p+"$";
	}
	var r=/./g;
	var rr = $('optR').checked;
	var rc = $('optC').checked;
	r.compile(p);
	for (var i=0;i<list.options.length;i++) {
		var s = r.test(list.options[i].textContent);
		if (rr) s=!s;
		if (rc) s=s&&(list.options[i].className != "listComment");
		list.options[i].selected = s;
	}
	list.focus();
}
function d_change(){
	$('txtDNS').value = $('optDNS').value;	
}
function GetXmlHttpObject(){
var x=null;
try {x=new XMLHttpRequest();} catch (e) {
try {x=new ActiveXObject("Msxml2.XMLHTTP");} catch (e)	{x=new ActiveXObject("Microsoft.XMLHTTP");}}
return x;
}
var ajax1;
function start_cb(){
	if (ajax1.readyState==4){ //已经加载了
		$('go').disabled=false;
		$('go2').disabled=false;
		var oo=[],xml=ajax1.responseXML;
		for (var i=0;i<list.options.length;i++) {
			oo.push(list.options[i].textContent);
		}
		xml = xml.childNodes[0];
		var xmla = xml.getElementsByTagName('item');
		for (var i=0;i<xmla.length;i++){
			var id=Number(xmla[i].getAttribute('id'));
			oo[id] = xmla[i].getAttribute('data') + "\t" + oo[id];
		}
		
		var errcount=0;
		xmla = xml.getElementsByTagName('error');
		for (var i=0;i<xmla.length;i++){
			var id=Number(xmla[i].getAttribute('id'));
			oo[id] = "# ERROR #" + xmla[i].getAttribute('err') + " @ " + oo[id];
			errcount++;
		}
		
		if (errcount) alert("解析发生" + errcount + "个错误。");
		$('output').value=oo.join("\n");
	}
}
function start(){
	ajax1=GetXmlHttpObject();
	if (ajax1==null){
	alert ("你的浏览器不支持AJAX!");
	return;
	}
	var url="?r="+Math.random();
	
	var mm="",mm2="";
	for (var ii=0;ii<list.options.length;ii++) {
		var i=list.options[ii];
		if (i.className == "listComment") continue;
		re1.lastIndex=0;
		var d = re1.exec(" "+i.textContent+" ");
		if (d==null) continue;
		if (d[1]!=i.textContent) continue;
		mm+=","+ii+","+d[1];
		mm2+=","+d[1];
	}
	
	var pl="";
	pl+="srv="+encodeURIComponent($('txtDNS').value);
	pl+="&port="+encodeURIComponent($('txtPort').value);
	if ($('optTCP').checked) pl+="&tcp=true";
	if ($('optAAAA').checked) pl+="&aaaa=true";
	
	$('args').value = pl + "&directout=true&m="+encodeURIComponent(mm2.substring(1));
	
	pl+="&m="+encodeURIComponent(mm.substring(1));
	
	$('go').disabled=true;
	$('go2').disabled=true;
	$('output_hidden').style.display='';
	$('output').value="正在载入，请等待……";
	
	ajax1.onreadystatechange=start_cb;
	ajax1.open("POST",url,true);
	ajax1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	ajax1.responseType = 'xml';
	ajax1.send(pl);
}


function adv_show(){
	$('advanced_skill').style.display='';$('args').selectionStart=0;$('args').selectionEnd=$('args').value.length;$('args').focus();
}
function start2_cb(){
	if (ajax1.readyState==4){ //已经加载了
		$('go').disabled=false;
		$('go2').disabled=false;
		$('output').value=ajax1.responseText;
	}
}
function start2(){
	ajax1=GetXmlHttpObject();
	if (ajax1==null){
	alert ("你的浏览器不支持AJAX!");
	return;
	}
	var url="?r="+Math.random();
	$('go').disabled=true;
	$('go2').disabled=true;
	$('output').value="正在载入，请等待……";
	var pl=$('args').value;
	ajax1.onreadystatechange=start2_cb;
	ajax1.open("POST",url,true);
	ajax1.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	ajax1.send(pl);
	
	var element=$('go');
	var offsetTop=element.offsetTop;
	while (element = element.offsetParent) { offsetTop += element.offsetTop; }
	
	window.scrollTo(0,offsetTop);
}
