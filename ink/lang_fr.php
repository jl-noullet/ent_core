<?php
$self = $_SERVER['PHP_SELF'];
$menu1->add( "$self?op=init", 'Initialiser la base de données' );
$menu1->add( "$self?op=add500", 'Ajouter 500 élèves aléatoires' );
$menu1->add( "$self?op=add1", 'Ajouter 1 élève' );
$menu1->add( "$self?op=classes", 'Lister les classes (accés aux élèves par classe)' );
$menu1->add( "$self?op=add1c", 'Ajouter 1 classe' );
$menu1->add( "$self?op=eleve", 'Chercher un élève' );
$menu1->add( "$self?op=lang", 'Changer la langue de l\'interface' );

// N.B. le premier item est particulier :
//	son nom DOIT etre 'indix'
//	il n'est pas editable (R ou H)
//	il est int et PRIMARY KEY

$form_s->add( 'indix', 'Matricule', 'R' );
$form_s->add( 'nom', 'Nom', 'T', 1, true );
$form_s->add( 'prenom', 'Prénom', 'T' );
$form_s->add( 'classe', "Classe", 'S', array(), true );
$form_s->add( 'date_n', 'Date de Naissance', 'D' );

$form_c->add( 'indix', 'Index', 'R' );
$form_c->add( 'nom', 'Nom', 'T', 1, true );

// titres
$label['title'] = 'ENT prototype';
$label['header1'] = 'ENT Expérimental';
// boutons
$label['mod']   = ' Ok ';
$label['add']   = 'Ajouter';
$label['kill']  = 'Supprimer';
$label['find']  = 'Chercher';
$label['abort'] = 'Retour';
$label['edit'] = 'Modifier';
$label['add1'] = 'Ajouter un élève';
// labels
$label['classe'] = 'Classe';
$label['effectif'] = 'Effectif';
$label['lastname'] = 'Nom';
$label['orfirstname'] = 'ou Prénom';
// messages de completion
$label['added'] = 'ajout effectué';
$label['moded'] = 'modification effectuée';
$label['aborted'] = 'opération abandonnée';
// $label[''] = '';
// nom des mois
$monthname = array( 0 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre' );
?>
