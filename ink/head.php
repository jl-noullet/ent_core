<?php
header( 'Content-Type: text/html; charset=utf-8' ); 
?><!DOCTYPE html>
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>ENT prototype</title>
<style type="text/css">
/* table { border: 2px solid black; border-collapse:collapse; }
   table td { border: 1px solid green; padding: 5px 6px 5px 8px; }
 */
table { border: 0; border-collapse:collapse; }
table td { padding: 5px 6px 5px 8px; }
td img { padding-left: 6px; padding-right: 6px; margin-left: 4px; margin-right: 4px; }
tr:nth-child(even) { background-color: #f2f2f2; }
tr.errtr { border-style: solid; border-color: red; border-width: 2px }
ul { margin: 2px 15px 2px 0px; }
li { margin: 10px 0px 10px 0px; }
pre { margin: 0px; }
.roin, .textin, .linkin { width: 300px }
.areain { width: 340px }
.boutest { width: 40px; cursor: pointer }
.boutup { cursor: pointer } 
.boutfini { cursor: pointer; padding: 6px 18px; border: solid 2px; border-color: #DA4; background: #FDA; font-weight: bold; } 
.lemenu { font-size: 120% }
.lerreur { background-color: #c00; color: #fff; font-size: 150%; text-align: center }
.oblig { color: #f00 }
p.desc { margin: 2px 2px 2px 0px; padding: 3px 2px 3px 2px; background-color: #ddffff; }
p.val  { margin: 2px 2px 2px 0px; padding: 5px 2px 5px 16px; }
</style>
<style id="restyle"></style>
</head><body>
<h1>ENT Expérimental</h1>
