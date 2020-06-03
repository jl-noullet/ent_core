<?php

require_once('ink/longage.php');
require_once('ink/db.php');
require_once('ink/def.php');
require_once('ink/boodle.php');
require_once('ink/head.php');

$self = $_SERVER['PHP_SELF'];
if	( preg_match( '/(mic|imacs|pro)[.]php$/', $self, $apo ) == 0 )
	{
	echo "<p>[ {$_SERVER['PHP_SELF']} ]</p>";
	mostra_fatal('access denied');
	}
// echo "<p>[ $apo[1] ]</p>";


/* zone de login rudimentaire pour dev. 
*/
session_start();
if  	( !isset( $_SESSION['usuario'] ) )
	{	// traiter login
	if	( ( isset( $_GET['login'] ) ) && ( isset( $_GET['potironmagique'] ) ) )
		{
		$_SESSION['usuario'] = $_GET['login'];
		}
	}
if  	( !isset( $_SESSION['usuario'] ) )
	mostra_fatal('access denied');
//*/

/* zone CAS
*
$phpcas_path = './LECAS';		// relative path to dir containing CAS.php & the CAS directory
$cas_host = 'cas.insa-toulouse.fr';	// Full Hostname of your CAS Server
$cas_context = '/cas';			// Context of the CAS Server
$cas_port = 443;			// Port of your CAS server. Normal https is 443
require_once $phpcas_path . '/CAS.php';	// Load the CAS lib
// phpCAS::setDebug();			// Enable debugging (fichier phpCAS.log)
// Initialize phpCAS
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
// For quick testing you can disable SSL validation of the CAS server.
phpCAS::setNoCasServerValidation();

if	( isset($_REQUEST['logout']) )
	phpCAS::logout();

// force CAS authentication
phpCAS::forceAuthentication();	// cette fonction bloque tant que l'user n'est pas logué
$lenom = phpCAS::getUser();
// echo '<p>Le Nom : ' . $lenom . '</p>';
// quelques verifications de parano
if	( session_status() != PHP_SESSION_ACTIVE )
	mostra_fatal('erreur improbable, pas de session apres phpCAS');
$_SESSION['usuario'] = $lenom;
//*/

$boodle = new boodle;
$boodle->init( $apo[1] );

echo '<div id="main">';
echo '<h2><button type="button" id="openbtn" onclick="openNav()">&#9776;</button>&nbsp; ', $label['header1'] . $apo[1], '</h2>';

$form_bi->itsa['eleve1']->topt = $boodle->liste_eleves;
$form_bi->itsa['eleve2']->topt = $boodle->liste_eleves;
$form_bi->itsa['eleve3']->topt = $boodle->liste_eleves;

$boodle->db->connect();

$curbin = $boodle->find_login( $_SESSION['usuario'] );
if	( $curbin < 0 )
	{
	}
else	{
	$_SESSION['lebin'] = $curbin;
	}

// premiere connexion : numero de binome pas encore attribue
if	( !isset($_SESSION['lebin']) )
	{
	// traiter les commandes par GET qui sont dans le menu0
	if	( isset($_GET['op']) )
		{
		if	( $_GET['op'] == 'binome_add' )
			{
			$boodle->form_add_binome();
			}
		else if	( $_GET['op'] == 'binome_edit' )
			{
			$boodle->form_edit_binome( $_GET['ind'], false );
			}
		else if	( $_GET['op'] == 'binome_list' )
			{
			$boodle->list_binomes();
			}
		}
	else if	( isset($_GET['logout']) )
		{
		session_unset();		// normalement intercepte par CAS avant d'arriver ici
		}
	// POSTS relatifs a l'objet binome, resultant des get ci-dessus
	else if	( isset( $_POST['binome_add'] ) )
		{
		$form_bi->post2form_full( TRUE );
		$curbin = $form_bi->form2db_insert_full( $boodle->db, $boodle->table_binomes, TRUE );
		echo "<p class=\"resu\">{$label['added']}</p>";
		if	( !isset($_SESSION['lebin']) )
			{
			$boodle->add_login( $_SESSION['usuario'], $curbin );
			$_SESSION['lebin'] = $curbin;
			}
		}
	else if	( isset( $_POST['binome_mod'] ) )
		{
		$form_bi->post2form_full( FALSE );
		$form_bi->form2db_update_full( $boodle->db, $boodle->table_binomes );
		echo "<p class=\"resu\">{$label['moded']}</p>";
		if	( !isset($_SESSION['lebin']) )
			{
			$curbin = $form_bi->itsa['indix']->val;
			$boodle->add_login( $_SESSION['usuario'], $curbin );
			$_SESSION['lebin'] = $curbin;
			}
		}
	else if	( isset( $_POST['binome_abt'] ) )
		{
		echo "<p class=\"resu\">{$label['aborted']}</p>";
		}
	// ni GET ni POST, un petit greeting alors
	else	{
		echo "<p class=\"resu\">Bonjour {$_SESSION['usuario']}<br>",
			'Vous devez appartenir à un binôme pour continuer</p>';
		}
	}
// numero de binome deja attribue - on re-teste car cela a pu changer
if	( isset($_SESSION['lebin']) )
	{
	// traiter les commandes par GET qui sont dans le menu1
	if	( isset($_GET['op']) )
		{
		if	( $_GET['op'] == 'exp1_edit' )	$boodle->exp_edit( 1, $_SESSION['lebin'] );
		else if	( $_GET['op'] == 'exp2_edit' )	$boodle->exp_edit( 2, $_SESSION['lebin'] );
		else if	( $_GET['op'] == 'exp3_edit' )	$boodle->exp_edit( 3, $_SESSION['lebin'] );
		else if	( $_GET['op'] == 'exp4_edit' )	$boodle->exp_edit( 4, $_SESSION['lebin'] );
		else if	( $_GET['op'] == 'exp5_edit' )	$boodle->exp_edit( 5, $_SESSION['lebin'] );
		}
	else if	( isset($_GET['logout']) )
		session_unset();		// normalement intercepte par CAS avant d'arriver ici
	// traiter les retours de formulaire POST
	else if	( isset( $_POST['exp1_add'] ) )	$boodle->exp_insert(1);
	else if	( isset( $_POST['exp2_add'] ) )	$boodle->exp_insert(2);
	else if	( isset( $_POST['exp3_add'] ) )	$boodle->exp_insert(3);
	else if	( isset( $_POST['exp4_add'] ) )	$boodle->exp_insert(4);
	else if	( isset( $_POST['exp5_add'] ) )	$boodle->exp_insert(5);
	else if	( isset( $_POST['exp1_mod'] ) )	$boodle->exp_mod(1);
	else if	( isset( $_POST['exp2_mod'] ) )	$boodle->exp_mod(2);
	else if	( isset( $_POST['exp3_mod'] ) )	$boodle->exp_mod(3);
	else if	( isset( $_POST['exp4_mod'] ) )	$boodle->exp_mod(4);
	else if	( isset( $_POST['exp5_mod'] ) )	$boodle->exp_mod(5);
	// ni GET ni POST, un petit greeting alors
	else	{
		echo "<p class=\"resu\">Bonjour {$_SESSION['usuario']} de <br>";
		$boodle->list_1binome( $curbin ); echo '</p>';
		}
	}

// sidebar
echo "</div>\n";
echo '<div id="sidebar"><button type="button" id="closebtn" onclick="closeNav()">&lt;&lt;</button>';
// on re-teste $_SESSION['lebin'] car il vient peut etre d'etre mis a jour,
// pour afficher le menu qui convient 
if	( isset($_SESSION['lebin']) )
	{
	echo '<p><i>', $_SESSION['usuario'], '</i><br>';
	$boodle->list_1binome( $_SESSION['lebin'], '<br>' );
	echo "</p>\n";
	$menu = $menu1;
	}
else	{
	echo '<p><i>', $_SESSION['usuario'], '</i></p>';
	$menu = $menu0;
	}
$menu->display();
echo '</div>';
?>
</body></html>