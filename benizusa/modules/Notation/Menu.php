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
$module_name = 'Notation';

/*
$menu['Loginpro']['admin'] = array( // Admin menu.
	'title' => 'Loginpro',
	'default' => 'Loginpro/ListeEleves.php', // Program loaded by default when menu opened.
	'Loginpro/ListeEleves.php' => 'Liste Elèves PDF' );
*/


if ( $RosarioModules['Notation'] ) // Verify target module is activated.
{
	$menu['Notation']['admin'] = array(
	'title' => 'Notation',
	'default' => 'Notation/Saisie.php',
	'Notation/Saisie.php' => 'Saisie des Notes' );
	$menu['Notation']['teacher'] = array(
	'title' => 'Notation',
	'default' => 'Notation/Saisie.php',
	'Notation/Saisie.php' => 'Saisie des Notes' );
}