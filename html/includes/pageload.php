<?php
include_once '../config/config.php';
include_once ("common_funcs.php");

?>
<html> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2"> 
<link rel=stylesheet href=meta2.css type=text/css> 
<title>Searching for messages from <?php echo $host?> - please be patient</title> 
<style> 
.brd{border-width: 0px 0px 0px 0px;border-style: none;} 
</style> 
<script>


var colors=new Array("","#274957","#2c4d5b","#305260","#355764","#3a5b69","#3E606E","#436472","#486977","#4C6E7B","#517380","#567785","#5A7C89","#5F818E","#648593","#688A97","#6D8E9C","#7293A0","#7798A5","#7B9CA9","#BCEEF4");

nsaz=50; 
iqq=1; 
var borne=1; 
var ns=document.layers?1:0; 
var bx=document; 
var nbx=document;

function zx(){ 
	if(ns) { 
		qx="<center><font color=white face=verdana size=1>"+(iqq*2)+"%</font></center><table border=1 width="+(iqq*4)+" bgcolor=#6C8D9B><tr><td></td></tr></table><br><br>g"; 
		bx.write(""+qx); 
		bx.close(); 
	} 
	else { 
		temp=eval("document.all.t"+iqq); 
		temp.style.background=colors[iqq]; 
		//bx.innerHTML="<table border=0 width="+(iqq*4)+" bgcolor=#6C8D9B height=8><tr><td>"+(document.all?'':' ')+"</td></tr></table>" 
		nbx.innerHTML="<center><font face=verdana color=white size=1 >"+(iqq*5)+"% </font></center>" 
	} 
	iqq++; 
	if(iqq<21) { 
		setTimeout("zx()",nsaz); 
	} 
	if(iqq>Math.round(borne/2.5)){ 
		if(nsaz<1000) nsaz+=30; 
		else nsaz+=500; 
	} 
	else{ 
		if(nsaz>250) nsaz=-150; 
	}

	if(iqq==20){ 
		if(!ns) { 
			//nbx.innerHTML=""; 
			//bx.innerHTML="<center><font color=white size=1 >loaded 100%</font></center>"; 
		} 
		parent.changeIt(); 
	} 
}

	function make(){ 
		if(ns) 
			bx=eval("document.xz_.document.xz__.document"); 
		else bx=eval("document.getElementById('xz')"); 
		if(ns) 
			nbx=eval("document.nxz_.document.nxz__.document"); 
		else nbx=eval("document.getElementById('nxz')"); 
		zx(); 
	} 
</script> 
<style> 
#xz{} 
</style> 
</head>

<body bgcolor="#224452" background="up.gif" scroll="no" onload="make();">

<table border="0" width=100% height=100%> 
<tr> 
<td valign=middle align=center> 
<font color=white face=verdana size=1>Searching for <?php echo $host?></font>
<table width="200" border="0" cellpadding="2"> 
<tr> 
<td align=left style="border-width: 1px 1px 1px 1px;border-style:solid;border-color:#80a1ae" class=brd>

<font size=2 face=verdana> 
<table summary="" border="0" width=100% height=12> 
<tr> 
<td id=t1></td> 
<td id=t2></td> 
<td id=t3></td> 
<td id=t4></td> 
<td id=t5></td> 
<td id=t6></td> 
<td id=t7></td> 
<td id=t8></td> 
<td id=t9></td> 
<td id=t10></td> 
<td id=t11></td> 
<td id=t12></td> 
<td id=t13></td> 
<td id=t14></td> 
<td id=t15></td> 
<td id=t16></td> 
<td id=t17></td> 
<td id=t18></td> 
<td id=t19></td> 
<td id=t20></td> 
</tr> 
</table>

<script> 
if(document.layers) document.write("<ILAYER id=xz_ width=200 height=25><LAYER id=xz__ width=200 height=25> </layer></ilayer>"); 
else document.write('<div id=xz></div>'); 
</script> 
</font> 
</td> 
</tr> 
<tr><td> 
<script> 
if(document.layers) document.write("<ILAYER id=nxz_ width=200 height=25><LAYER id=nxz__ width=200 height=25> </layer></ilayer>"); 
else document.write('<div id=nxz></div>'); 
</script> 
</td></tr> 
</table>

</td> 
</tr> 
</table>

</body> 
</html> 
