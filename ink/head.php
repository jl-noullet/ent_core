<?php
header( 'Content-Type: text/html; charset=utf-8' );
echo "<!DOCTYPE html>\n";
if	( isset($_SESSION['lang']) )
	echo "<html lang=\"{$_SESSION['lang']}\">"
?>
<head><meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $label['title']; ?></title>
<style>
#sidebar { height: 100%; width: 18%; position: fixed; z-index: 1; top: 0; left: 0; padding-top: 50px;
	background-color: #8BF; overflow-x: hidden; transition: 0.15s; }
#sidebar a { padding: 8px 8px 8px 20px; text-decoration: none;
	color: #000; display: block; transition: 0.15s; }
#closebtn { position: absolute; top: 0; right: 0; font-size: 20px; margin-left: 50px; padding: 0px 10px;
	background-color: #111; color: #f00; border: none; cursor: pointer; }
#openbtn { font-size: 20px; padding: 10px 15px; background-color: #111; color: #0f0;
	border: none; cursor: pointer; display: none; }
#main { margin-left: 18%; transition: margin-left 0.15s; }
a.current { font-weight: bold; }
table { border: 0; border-collapse:collapse; }
table td { padding: 5px 6px 5px 8px; }
td img { padding-left: 6px; padding-right: 6px; margin-left: 4px; margin-right: 4px; }
tr:nth-child(even) { background-color: #f2f2f2; }
tr.errtr { border-style: solid; border-color: red; border-width: 2px }
ul { margin: 2px 15px 2px 0px; }
li { margin: 10px 0px 10px 0px; }
pre { margin: 0px; }
.ag { text-align: left }
.ar { text-align: right }
.roin, .textin, .linkin { width: 400px }
.areain { width: 400px }
.boutest { width: 40px; cursor: pointer }
.boutup { cursor: pointer } 
.boutmod  { cursor: pointer; padding: 6px 18px; border: solid 2px; border-color: #DA4; background: #FDA; font-weight: bold; } 
.boutadd  { cursor: pointer; padding: 6px 18px; border: solid 2px; border-color: #DA4; background: #FDA; font-weight: bold; } 
.boutfind { cursor: pointer; padding: 6px 18px; border: solid 2px; border-color: #DA4; background: #FDA; font-weight: bold; } 
.boutkill { cursor: pointer; padding: 6px 18px; border: solid 2px; border-color: #D00; background: #F88; font-weight: bold; } 
.boutabt  { cursor: pointer; padding: 6px 18px; border: solid 2px; border-color: #4D4; background: #AFA; font-weight: bold; } 
.lerreur { background-color: #c00; color: #fff; font-size: 150%; text-align: center }
.resu { background-color: #4f7; color: #040; font-size: 120%; text-align: center }
.oblig { color: #f00 }
p.desc { margin: 2px 2px 2px 0px; padding: 3px 2px 3px 2px; background-color: #ddffff; }
p.val  { margin: 2px 2px 2px 0px; padding: 5px 2px 5px 16px; }
</style>
<style id="restyle"></style>
</head><body>

