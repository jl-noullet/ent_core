<?php

$db1 = new database;
$db1->server = 'localhost';
$db1->base = 'sourceconst';
$db1->user = 'root';
$db1->pass = '';

$form_s = new form;
$form_s->nom = 'eleve';	// le nom de la form est a usage interne, ne pas traduire
$form_c = new form;
$form_c->nom = 'classe';
// $form_a = new form;
// $form_a->nom = 'activite';
// $form_e = new form;
// $form_e->nom = 'event';

$menu1 = new menu;

$label = array();
// ensuite viendront les definitions dependant de la langue
?>
