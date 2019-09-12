<?php
$self = $_SERVER['PHP_SELF'];
$menu1->add( "$self?op=init", 'initialize the database' );
$menu1->add( "$self?op=add500", 'add 500 random students' );
$menu1->add( "$self?op=add1", 'add 1 student' );
$menu1->add( "$self?op=classes", 'list classes (access to students by class)' );
$menu1->add( "$self?op=add1c", 'add 1 class' );
$menu1->add( "$self?op=eleve", 'find a student' );
$menu1->add( "$self?op=lang", 'change interface language' );

// N.B. le premier item est particulier :
//	son nom DOIT etre 'indix'
//	il n'est pas editable (R ou H)
//	il est int et PRIMARY KEY
$form_s->add( 'indix', 'Id #', 'R', 1 );
$form_s->add( 'nom', 'Name', 'T', 1 );
$form_s->add( 'prenom', 'First Name', 'T', 1 );
$form_s->add( 'classe', "Class", 'S', array() );
$form_s->add( 'date_n', 'Birth Date', 'D', 1 );

$form_c->add( 'indix', 'Index', 'R', 1 );
$form_c->add( 'nom', 'Name', 'T', 1 );

// titres
$label['title'] = 'Digital Workplace Prototype';	// Virtual Learning Environment ?
$label['header1'] = 'Experimental Digital Workplace';
// boutons
$label['save'] = ' Ok ';
$label['kill'] = 'Remove';
$label['abort'] = 'Return';
$label['find'] = 'Find';
$label['edit'] = 'Edit';
$label['add1'] = 'Add a student';
// labels
$label['classe'] = 'Class';
$label['effectif'] = 'Size';
$label['lastname'] = 'Last Name';
$label['orfirstname'] = 'or First Name';
// messages de completion
$label['added'] = 'successful addition';
$label['moded'] = 'change done';
$label['aborted'] = 'action canceled';
// $label[''] = '';
// nom des mois
$monthname = array( 0 => 'jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec' );
?>
