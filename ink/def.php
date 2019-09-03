<?php

$db1 = new database;
//$db1->server = 'sourcecojln.mysql.db';
//$db1->base = 'sourcecojln';
//$db1->user = 'sourcecojln';
//$db1->pass = 'JWhxQ1';
$db1->server = 'localhost';
$db1->base = 'sourceconst';
$db1->user = 'root';
$db1->pass = '';

$ecole1 = new school;
$ecole1->table_eleves = 'ENT_S1_eleves';
$ecole1->table_classes = 'ENT_S1_classes';

$menu1 = new menu;
$menu1->add( 'bench1.php?op=init', 'initialiser la base de donnees' );
$menu1->add( 'bench1.php?op=add1', 'ajouter 1 eleve' );
$menu1->add( 'bench1.php?op=add100', 'ajouter 100 eleves' );
$menu1->add( 'bench1.php?op=classes', 'lister les classes' );

$msug = array();
$msug['save'] = ' Ok ';
$msug['kill'] = 'Supprimer';
$msug['abort'] = 'Retour';

$form1 = new form;
// N.B. le premier item est particulier :
//	son nom DOIT etre 'indix'
//	il n'est pas editable (R ou H)
//	il est int et PRIMARY KEY
$form1->nom = 'eleve';
$form1->add( 'indix', 'Matricule', 'R', 1 );
$form1->add( 'nom', 'Nom', 'T', 1 );
$form1->add( 'prenom', 'Prenom', 'T', 1 );
$form1->add( 'classe', "Classe", 'S', array() );
$form1->add( 'date_n', 'Date de Naissance', 'T', 1 );
?>
