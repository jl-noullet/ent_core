<?php

$groupes = array( 'C', 'D', 'E' );

$form_bi = new form;
$form_bi->nom = 'binome';	// le nom de la form est a usage interne, ne pas traduire
$form_bi->add( 'indix', 'index', 'R' );
$form_bi->add( 'eleve1', 'Eleve 1', 'S', array(), true );
$form_bi->add( 'eleve2', 'Eleve 2', 'S', array(), true );
$form_bi->add( 'eleve3', 'Eleve 3', 'S', array(), true );
$form_bi->add( 'groupe', 'GROUPE', 'S', $groupes, true );

$formexp = array();

$formexp[1] = new form;
$formexp[1]->nom = 'exp1';
$formexp[1]->add( 'indix', 'index', 'R' );	// numero de binome
$formexp[1]->add( 'Q111', "Q1.1.1 Où les variables locales aa, bb, cc, dd, ee de la fonction sub01 sont-elles stockées ?", 'T', 1 );
$formexp[1]->add( 'Q112', "Q1.1.2 Comment l'espace de stockage pour ces variables est-il réservé ? Libéré après usage ?", 'T', 2 );
$formexp[1]->add( 'Q113', "Q1.1.3 En quoi le stockage des variables locales a-t-il été amélioré ?", 'T', 2 );
$formexp[1]->add( 'Q114', "Q1.1.4 Comment l'appel à la fonction expose() a-t-il été optimisé ?", 'T', 2 );
$formexp[1]->add( 'Q121', "Q1.2.1 Où les variables locales aa, bb, cc, dd, ee de la fonction sub01 sont-elles stockées ?", 'T', 1 );
$formexp[1]->add( 'Q122', "Q1.2.2 Comment l'espace de stockage pour ces variables est-il réservé ? Libéré après usage ?", 'T', 2 );
$formexp[1]->add( 'Q123', "Q1.2.3 Qu'est-ce qui a changé dans le stockage des variables locales aa, bb, cc, dd, ee de la fonction sub01 ?", 'T', 2 );
$formexp[1]->add( 'Q124', "Q1.2.4 Comment les opérations arithmétiques portant sur des constantes on-t-elles été optimisées ?", 'T', 2 );
$formexp[1]->add( 'Q131', "Q1.3.1 Où les variables locales aa, bb, cc, dd, ee de la fonction sub01 sont-elles stockées ?", 'T', 1 );
$formexp[1]->add( 'Q132', "Q1.3.2 Comment l'espace de stockage pour ces variables est-il réservé ? Libéré après usage ?", 'T', 2 );

$formexp[2] = new form;
$formexp[2]->nom = 'exp2';
$formexp[2]->add( 'indix', 'index', 'R' );	// numero de binome
$formexp[2]->add( 'Q211', "Q2.1.1 Comment la fonction seed() retourne-t-elle sa valeur ?", 'T', 1 );
$formexp[2]->add( 'Q212', "Q2.1.2 Comment les arguments aa, bb, cc, dd, ee sont-ils passés à la fonction sub01() ?", 'T', 1 );
$formexp[2]->add( 'Q213', "Q2.1.3 Comment l'espace de stockage pour ces arguments est-il réservé ? Libéré après usage ?", 'T', 2 );
$formexp[2]->add( 'Q214', "Q2.1.4 En quoi la préparation des arguments pour sub01() a-t-elle été améliorée ?", 'T', 2 );
$formexp[2]->add( 'Q221', "Q2.2.1 Comment la fonction seed() retourne-t-elle sa valeur ?", 'T', 1 );
$formexp[2]->add( 'Q222', "Q2.2.2 Comment les arguments aa, bb, cc, dd, ee sont-ils passés à la fonction sub01() ?", 'T', 1 );
$formexp[2]->add( 'Q223', "Q2.2.3 Comment l'espace de stockage pour ces arguments est-il réservé ? Libéré après usage ?", 'T', 2 );
$formexp[2]->add( 'Q224', "Q2.2.4 Comment le traitement des arguments aa, bb, cc, dd, ee à l'intérieur de la fonction sub01() a-t-il été optimisé?", 'T', 2 );
$formexp[2]->add( 'Q225', "Q2.2.5 En observant le contenu de la fonction sub01(), donner un résumé des règles d'utilisation des registres", 'T', 2 );
$formexp[2]->add( 'Q226', "Q2.2.6 Comment l'appel à sub01() a-t-il été optimisé ?", 'T', 2 );
$formexp[2]->add( 'Q227', "Q2.2.7 Quel est l'impact sur les opérations arithmétiques ?", 'T', 2 );
$formexp[2]->add( 'Q231', "Q2.3.1 Comment les arguments aa, bb, cc, dd, ee sont-ils passés à la fonction sub01() ?", 'T', 1 );
$formexp[2]->add( 'Q232', "Q2.3.2 En observant le contenu de la fonction main(), donner un résumé des règles d'utilisation des registres", 'T', 2 );

$formexp[3] = new form;
$formexp[3]->nom = 'exp3';
$formexp[3]->add( 'indix', 'index', 'R' );	// numero de binome
$formexp[3]->add( 'Q3', "Q Comment ?", 'T', 1 );

$formexp[4] = new form;
$formexp[4]->nom = 'exp4';
$formexp[4]->add( 'indix', 'index', 'R' );	// numero de binome
$formexp[4]->add( 'Q4', "Q Comment ?", 'T', 1 );

$formexp[5] = new form;
$formexp[5]->nom = 'exp5';
$formexp[5]->add( 'indix', 'index', 'R' );	// numero de binome
$formexp[5]->add( 'Q5', "Q Comment ?", 'T', 1 );

$self = $_SERVER['PHP_SELF'];
$menu0 = new menu;
$menu0->add( "$self?op=binome_add", 'Créer votre binôme' );
$menu0->add( "$self?op=binome_list", 'Rejoindre un binôme existant' );
$menu0->add( "$self?logout=1", 'Logout' );


$menu1 = new menu;
$menu1->add( "$self?op=exp1_edit", 'Formulaire exp 1' );
$menu1->add( "$self?op=exp2_edit", 'Formulaire exp 2' );
$menu1->add( "$self?op=exp3_edit", 'Formulaire exp 3' );
$menu1->add( "$self?op=exp4_edit", 'Formulaire exp 4' );
$menu1->add( "$self?op=exp5_edit", 'Formulaire exp 5' );
$menu1->add( "$self?logout=1", 'Logout' );

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
