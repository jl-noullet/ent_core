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
$formexp[1]->add( 'Q1A1', "Q1.A.1 Où les variables locales aa, bb, cc, dd, ee de la fonction sub01 sont-elles stockées ?", 'T', 1 );
$formexp[1]->add( 'Q1A2', "Q1.A.2 Comment l'espace de stockage pour ces variables est-il réservé ? Libéré après usage ?", 'T', 2 );
$formexp[1]->add( 'Q1A3', "Q1.A.3 En quoi le stockage des variables locales a-t-il été amélioré ?", 'T', 2 );
$formexp[1]->add( 'Q1A4', "Q1.A.4 Comment l'appel à la fonction expose() a-t-il été optimisé ?", 'T', 2 );
$formexp[1]->add( 'Q1X1', "Q1.X.1 Où les variables locales aa, bb, cc, dd, ee de la fonction sub01 sont-elles stockées ?", 'T', 1 );
$formexp[1]->add( 'Q1X2', "Q1.X.2 Comment l'espace de stockage pour ces variables est-il réservé ? Libéré après usage ?", 'T', 2 );
$formexp[1]->add( 'Q1X3', "Q1.X.3 Qu'est-ce qui a changé dans le stockage des variables locales aa, bb, cc, dd, ee de la fonction sub01 ?", 'T', 2 );
$formexp[1]->add( 'Q1X4', "Q1.X.4 Comment les opérations arithmétiques portant sur des constantes on-t-elles été optimisées ?", 'T', 2 );
$formexp[1]->add( 'Q1Y1', "Q1.Y.1 Où les variables locales aa, bb, cc, dd, ee de la fonction sub01 sont-elles stockées ?", 'T', 1 );
$formexp[1]->add( 'Q1Y2', "Q1.Y.2 Comment l'espace de stockage pour ces variables est-il réservé ? Libéré après usage ?", 'T', 2 );

$formexp[2] = new form;
$formexp[2]->nom = 'exp2';
$formexp[2]->add( 'indix', 'index', 'R' );	// numero de binome
$formexp[2]->add( 'Q2A1', "Q2.A.1 Comment la fonction seed() retourne-t-elle sa valeur ?", 'T', 1 );
$formexp[2]->add( 'Q2A2', "Q2.A.2 Comment les arguments aa, bb, cc, dd, ee sont-ils passés à la fonction sub01() ?", 'T', 1 );
// $formexp[2]->add( 'Q213', "Q2.A.3 Comment l'espace de stockage pour ces arguments est-il réservé ? Libéré après usage ?", 'T', 2 );
$formexp[2]->add( 'Q2A4', "Q2.A.4 En quoi la préparation des arguments pour sub01() a-t-elle été améliorée ?", 'T', 2 );
$formexp[2]->add( 'Q2X1', "Q2.X.1 Comment la fonction seed() retourne-t-elle sa valeur ?", 'T', 1 );
$formexp[2]->add( 'Q2X2', "Q2.X.2 Comment les arguments aa, bb, cc, dd, ee sont-ils passés à la fonction sub01() ?", 'T', 1 );
// $formexp[2]->add( 'Q223', "Q2.X.3 Comment l'espace de stockage pour ces arguments est-il réservé ? Libéré après usage ?", 'T', 2 );
$formexp[2]->add( 'Q2X4', "Q2.X.4 Comment le traitement des arguments aa, bb, cc, dd, ee à l'intérieur de la fonction sub01() a-t-il été optimisé?", 'T', 2 );
$formexp[2]->add( 'Q2X5', "Q2.X.5 En observant le contenu de la fonction sub01(), donner un résumé des règles d'utilisation des registres", 'T', 2 );
$formexp[2]->add( 'Q2X6', "Q2.X.6 Comment l'appel à sub01() a-t-il été optimisé ?", 'T', 2 );
$formexp[2]->add( 'Q2X7', "Q2.X.7 Quel est l'impact sur les opérations arithmétiques ?", 'T', 2 );
$formexp[2]->add( 'Q2Y1', "Q2.Y.1 Comment les arguments aa, bb, cc, dd, ee sont-ils passés à la fonction sub01() ?", 'T', 1 );
$formexp[2]->add( 'Q2Y2', "Q2.Y.2 En observant le contenu de la fonction main(), donner un résumé des règles d'utilisation des registres", 'T', 2 );

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
//$menu1->add( "$self?op=exp3_edit", 'Formulaire exp 3' );
//$menu1->add( "$self?op=exp4_edit", 'Formulaire exp 4' );
//$menu1->add( "$self?op=exp5_edit", 'Formulaire exp 5' );
$menu1->add( "$self?logout=1", 'Logout' );

$label = array();
// boutons
$label['mod']   = 'Sauver';
$label['add']   = 'Sauver';
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
$label['header1'] = 'TP Archi ARM/X86 -- 3';

?>
