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

$form1 = new form;
$form1->nom = 'exp1';
$form1->add( 'indix', 'index', 'R' );	// numero de binome
$form1->add( 'Q111', "Q1.1.1 Où les variables locales aa, bb, cc, dd, ee de la fonction sub01 sont-elles stockées ?", 'T', 1 );
$form1->add( 'Q112', "Q1.1.2 Comment l'espace de stockage pour ces variables est-il réservé ? Libéré après usage ?", 'T', 2 );
$form1->add( 'Q113', "Q1.1.3 En quoi le stockage des variables locales a-t-il été amélioré ?", 'T', 2 );
$form1->add( 'Q114', "Q1.1.4 Comment l'appel à la fonction expose() a-t-il été optimisé ?", 'T', 2 );
$form1->add( 'Q121', "Q1.2.1 Où les variables locales aa, bb, cc, dd, ee de la fonction sub01 sont-elles stockées ?", 'T', 1 );
$form1->add( 'Q122', "Q1.2.2 Comment l'espace de stockage pour ces variables est-il réservé ? Libéré après usage ?", 'T', 2 );
$form1->add( 'Q123', "Q1.2.3 Qu'est-ce qui a changé dans le stockage des variables locales aa, bb, cc, dd, ee de la fonction sub01 ?", 'T', 2 );
$form1->add( 'Q124', "Q1.2.4 Comment les opérations arithmétiques portant sur des constantes on-t-elles été optimisées ?", 'T', 2 );
$form1->add( 'Q131', "Q1.3.1 Où les variables locales aa, bb, cc, dd, ee de la fonction sub01 sont-elles stockées ?", 'T', 1 );
$form1->add( 'Q132', "Q1.3.2 Comment l'espace de stockage pour ces variables est-il réservé ? Libéré après usage ?", 'T', 2 );

$self = $_SERVER['PHP_SELF'];
$menu0 = new menu;
$menu0->add( "$self?op=binome_add", 'Créer votre binôme' );
$menu0->add( "$self?op=binome_list", 'Rejoindre un binôme existant' );
$menu0->add( "$self?op=logout", 'Logout' );


$menu1 = new menu;
$menu1->add( "$self?op=exp1_edit", 'Formulaire expérience 1' );
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
