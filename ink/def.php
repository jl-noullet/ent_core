<?php

$db1 = new database;
$db1->server = 'localhost';
$db1->base = 'sourceconst';
$db1->user = 'root';
$db1->pass = '';

$ecole1 = new school;
$ecole1->table_eleves = 'ENT_S1_eleves';
$ecole1->table_classes = 'ENT_S1_classes';

$menu1 = new menu;
$form1 = new form;
$form1->nom = 'eleve';	// le nom de la form est a usage interne, ne pas traduire

$label = array();
// ensuite viendront les definitions dependant de la langue
?>
