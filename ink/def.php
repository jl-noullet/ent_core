<?php

$db1 = new database;
$db1->server = 'localhost';
$db1->base = 'sourceconst';
$db1->user = 'root';
$db1->pass = '';

$groupes = array( 'C', 'D', 'E' );
$form_bi = new form;
$form_bi->nom = 'binome';	// le nom de la form est a usage interne, ne pas traduire
$form_bi->add( 'indix', 'index', 'R' );
$form_bi->add( 'eleve1', 'Eleve 1', 'S', array(), true );
$form_bi->add( 'eleve2', 'Eleve 2', 'S', array(), true );
$form_bi->add( 'eleve3', 'Eleve 3', 'S', array(), true );
$form_bi->add( 'groupe', 'GROUPE', 'S', $groupes, true );

$self = $_SERVER['PHP_SELF'];
$menu0 = new menu;
$menu0->add( "$self?op=binome_add", 'Créer votre binôme' );
$menu0->add( "$self?op=binome_list", 'Rejoindre un binôme existant' );
$menu0->add( "$self?op=logout", 'Logout' );


$menu1 = new menu;
$menu1->add( "$self?op=init", 'Initialiser la base de données' );
$menu1->add( "$self?op=binome_add", 'Ajouter un binome' );
$menu1->add( "$self?op=binome_list_k", 'Editer liste des binomes' );
$menu1->add( "$self?op=logout", 'Logout' );

$label = array();
// boutons
$label['mod']   = ' Ok ';
$label['add']   = 'Ajouter';
$label['kill']  = 'Supprimer';
$label['find']  = 'Chercher';
$label['abort'] = 'Retour';
$label['edit']  = 'Modifier';
$label['select'] = 'Choisir';

// messages de completion
$label['added'] = 'ajout effectué';
$label['moded'] = 'modification effectuée';
$label['aborted'] = 'opération abandonnée';
// titres
$label['title'] = 'BOODLE prototype';
$label['header1'] = 'TP ARCHI ARM/X86';


?>
