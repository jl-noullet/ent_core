<?php
header( 'Content-Type: text/html; charset=utf-8' );
echo "<!DOCTYPE html>\n";
if	( isset($_SESSION['lang']) )
	echo "<html lang=\"{$_SESSION['lang']}\">"
?>
<head><meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $label['title']; ?></title>
<link rel="stylesheet" type="text/css" href="boodle.css" />
<style id="restyle"></style>
<script>
// // code pour le menu responsif
var menuwidth = 200;
var inputwidth = 400;

// // menu hamburger
function openNav() {
var smenuwidth = menuwidth.toFixed(0) + "px";
document.getElementById("sidebar").style.width = smenuwidth;
document.getElementById("main").style.marginLeft = smenuwidth;
document.getElementById("openbtn").style.display = "none";
}
function closeNav() {
document.getElementById("sidebar").style.width = "0";
document.getElementById("main").style.marginLeft= "0";
document.getElementById("openbtn").style.display = "inline";
}

function resize_callback() {
var ww, restyle_text; 
ww = document.documentElement.clientWidth;
inputwidth = Math.floor(ww/2);
menuwidth = Math.floor(ww/5);
if	( menuwidth > 180 )
	menuwidth = 180;
restyle_text  = "#sidebar { width: " + menuwidth + "px; } #main { margin-left: " + menuwidth + "px; } ";
restyle_text += ".roin, .textin, .linkin { width: " + inputwidth + "px; } .areain { width: " + inputwidth + "px; }";
// alert( restyle_text );
document.getElementById("restyle").innerHTML = restyle_text;
// une property qui a ete overridee via document.getElementById("pipo").style n'obeira plus au <style> !
openNav();	// alors on insiste!
}
</script>
</head><body onresize="resize_callback()" onload="resize_callback()">
