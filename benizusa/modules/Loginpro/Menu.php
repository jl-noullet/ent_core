<?php
/**
 * Menu.php file
 * Required
 * - Menu entries for this module
 * - Menu entries to other modules
 */

/**
 * Use dgettext() function instead of _() for Module specific strings translation
 * see locale/README file for more information.
 */
$module_name = 'Loginpro';

/*
$menu['Loginpro']['admin'] = array( // Admin menu.
	'title' => 'Loginpro',
	'default' => 'Loginpro/ListeEleves.php', // Program loaded by default when menu opened.
	'Loginpro/ListeEleves.php' => 'Liste Elèves PDF' );
*/

// Add a Menu entry to the Resources module.
if ( $RosarioModules['Students'] ) // Verify target module is activated.
{
	$menu['Students']['admin'] += array(
		4 => 'Loginpro',
		'Loginpro/ListeEleves.php' => 'Liste Élèves en PDF',
		'Loginpro/Programmes.php' => 'Contenu des Programmes' );
	$menu['Students']['teacher'] += array(
		4 => 'Loginpro',
		'Loginpro/ListeEleves.php' => 'Liste Élèves en PDF',
		'Loginpro/Programmes.php' => 'Contenu des Programmes' );
	$menu['Students']['parent'] += array(
		4 => 'Loginpro',
		'Loginpro/ListeEleves.php' => 'Liste Élèves en PDF' );
/*	$menu['Students']['student'] += array(
		4 => 'Loginpro',
		'Loginpro/ListeEleves.php' => 'Liste Elèves en PDF' );
*/
}
