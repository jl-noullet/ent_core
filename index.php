<?php

require_once('ink/longage.php');
require_once('ink/db.php');
require_once('ink/def.php');
require_once('ink/boodle.php');
require_once('ink/head.php');

/* zone de login rudimentaire pour dev. *
session_start();
if  	( !isset( $_SESSION['usuario'] ) )
	{	// traiter login
	if	( isset( $_GET['login'] ) )
		{
		$_SESSION['usuario'] = $_GET['login'];
		}
	}
if  	( !isset( $_SESSION['usuario'] ) )
	mostra_fatal('access denied');

//*/

/* zone CAS */
$phpcas_path = './LECAS';// relative path to dir containing CAS.php & the CAS directory
$cas_host = 'cas.insa-toulouse.fr';// Full Hostname of your CAS Server
$cas_context = '/cas';// Context of the CAS Server
$cas_port = 443;// Port of your CAS server. Normally for a https server it's 443
require_once $phpcas_path . '/CAS.php';// Load the CAS lib
// phpCAS::setDebug();// Enable debugging (fichier phpCAS.log)
// Initialize phpCAS
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
// For quick testing you can disable SSL validation of the CAS server.
phpCAS::setNoCasServerValidation();

if	( isset($_REQUEST['logout']) )
	phpCAS::logout();

// force CAS authentication
phpCAS::forceAuthentication();	// cette fonction bloque tant que l'user n'est pas logué
$lenom = phpCAS::getUser();
echo '<p>Le Nom : ' . $lenom . '</p>';
// quelques verifications de parano
if	( session_status() != PHP_SESSION_ACTIVE )
	mostra_fatal('erreur improbable, pas de session apres phpCAS');
$_SESSION['usuario'] = $lenom;
//*/

$boodle = new boodle;
$boodle->init( 'mic' );

echo '<div id="main">';
echo '<h1><button type="button" id="openbtn" onclick="openNav()">&#9776;</button>&nbsp; ', $label['header1'], '</h1>';

$form_bi->itsa['eleve1']->topt = $boodle->liste_eleves;
$form_bi->itsa['eleve2']->topt = $boodle->liste_eleves;
$form_bi->itsa['eleve3']->topt = $boodle->liste_eleves;

$boodle->db->connect();

if	( isset($_SESSION['lebin']) )	// provisoire, a mettre en footer
	{ echo '<h3>', $boodle->list_1binome( $_SESSION['lebin'] ), "</h3>\n"; }

// traiter les commandes par GET
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
	else if	( $_GET['op'] == 'exp1_edit' )	$boodle->exp_edit( 1, $_SESSION['lebin'] );
	else if	( $_GET['op'] == 'exp2_edit' )	$boodle->exp_edit( 2, $_SESSION['lebin'] );
	else if	( $_GET['op'] == 'exp3_edit' )	$boodle->exp_edit( 3, $_SESSION['lebin'] );
	else if	( $_GET['op'] == 'exp4_edit' )	$boodle->exp_edit( 4, $_SESSION['lebin'] );
	else if	( $_GET['op'] == 'exp5_edit' )	$boodle->exp_edit( 5, $_SESSION['lebin'] );
	}

// traiter les retours de formulaire POST (c'est le else de if	( isset($_GET[...]) ) )
// POSTS relatifs a l'objet binome
else if	( isset( $_POST['binome_add'] ) )
	{
	$form_bi->post2form_full( TRUE );
	$curbin = $form_bi->form2db_insert_full( $boodle->db, $boodle->table_binomes, TRUE );
	echo "<p class=\"resu\">{$label['added']}</p>";
	if	( !isset($_SESSION['lebin']) )
		{
		echo '<p>'; $boodle->list_1binome( $curbin ); echo '</p>';
		$_SESSION['lebin'] = $curbin;
		$boodle->add_login( $_SESSION['usuario'], $curbin );
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
		echo '<p>'; $boodle->list_1binome( $curbin ); echo '</p>';
		$_SESSION['lebin'] = $curbin;
		$boodle->add_login( $_SESSION['usuario'], $curbin );
		}
	}
else if	( isset( $_POST['binome_abt'] ) )
	{
	echo "<p class=\"resu\">{$label['aborted']}</p>";
	}
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

// traiter entree sans op ni POST
else if	( !isset($_SESSION['lebin']) )
	{
	$curbin = $boodle->find_login( $_SESSION['usuario'] );
	if	( $curbin < 0 )
		{
		echo "<p class=\"resu\">Bonjour {$_SESSION['usuario']}<br>",
		     'Vous devez appartenir à un binôme pour continuer</p>';
		}
	else	{
		echo "<p class=\"resu\">Bonjour {$_SESSION['usuario']} de <br>";
		$boodle->list_1binome( $curbin ); echo '</p>';
		$_SESSION['lebin'] = $curbin;
		}
	}

echo "</div>\n";
echo '<div id="sidebar"><button type="button" id="closebtn" onclick="closeNav()">&lt;&lt;</button>';
if	( isset($_SESSION['lebin']) )
	$menu = $menu1;
else	$menu = $menu0;
$menu->display();
echo '</div>';
?>
<script>
function openNav() {
document.getElementById("sidebar").style.width = "25%";
document.getElementById("main").style.marginLeft = "25%";
document.getElementById("openbtn").style.display = "none"; }
function closeNav() {
document.getElementById("sidebar").style.width = "0";
document.getElementById("main").style.marginLeft= "0";
document.getElementById("openbtn").style.display = "inline"; }
</script>
</body></html>
