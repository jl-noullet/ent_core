<?php
$self = $_SERVER['PHP_SELF'];
$menu1->add( "$self?op=init", 'initialiser la base de données' );
$menu1->add( "$self?op=add500", 'ajouter 500 élèves aléatoires' );
$menu1->add( "$self?op=add1", 'ajouter 1 élève' );
$menu1->add( "$self?op=classes", 'lister les classes (accés aux élèves par classe)' );
$menu1->add( "$self?op=eleve", 'chercher un élève' );
$menu1->add( "$self?op=lang", 'changer la langue de l\'interface' );
$menu1->add( "$self?op=add1c", 'ajouter 1 classe' );

// N.B. le premier item est particulier :
//	son nom DOIT etre 'indix'
//	il n'est pas editable (R ou H)
//	il est int et PRIMARY KEY
$form_s->add( 'indix', 'Matricule', 'R', 1 );
$form_s->add( 'nom', 'Nom', 'T', 1 );
$form_s->add( 'prenom', 'Prénom', 'T', 1 );
$form_s->add( 'classe', "Classe", 'S', array() );
$form_s->add( 'date_n', 'Date de Naissance', 'D', 1 );

$form_c->add( 'indix', 'Index', 'R', 1 );
$form_c->add( 'nom', 'Nom', 'T', 1 );

// titres
$label['title'] = 'ENT prototype';
$label['header1'] = 'ENT Expérimental';
// boutons
$label['save'] = ' Ok ';
$label['kill'] = 'Supprimer';
$label['abort'] = 'Retour';
$label['find'] = 'Chercher';
$label['edit'] = 'Modifier';
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
