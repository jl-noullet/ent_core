<?php
session_start();

require_once('ink/longage.php');
require_once('ink/def.php');
require_once('ink/boodle.php');
require_once('ink/head.php');

// zone de login rudimentaire pour dev.

if  	( !isset( $_SESSION['usuario'] ) )
	{	// traiter login
	if	( isset( $_GET['login'] ) )
		{
		$_SESSION['usuario'] = $_GET['login'];
		}
	}

if  	( !isset( $_SESSION['usuario'] ) )
	mostra_fatal('access denied');

/*
$imacs = new boodle;
$imacs->db = $db1;
$imacs->table_logins  = 'BOO_IMACS_logins';
$imacs->table_binomes = 'BOO_IMACS_binomes';
require_once('ink/liste_3imacs.php');	// va creer un array $liste_3imacs
$imacs->liste_eleves = $liste_3imacs;
$boodle = $imacs;
*/

$mic = new boodle;
$mic->db = $db1;
$mic->table_logins  = 'BOO_MIC_logins';
$mic->table_binomes = 'BOO_MIC_binomes';
require_once('ink/liste_3mic.php');	// va creer un array $liste_3mic
$mic->liste_eleves = $liste_3mic;
$boodle = $mic;


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
	if	( $_GET['op'] == 'init' )
		{
		$boodle->create_tables();
		echo "<p class=\"resu\">{$label['moded']}</p>";
		}
	else if	( $_GET['op'] == 'binome_add' )
		{
		$boodle->form_add_binome();
		}
	else if	( $_GET['op'] == 'binome_edit' )
		{
		$boodle->form_edit_binome( $_GET['ind'], false );
		}
	else if	( $_GET['op'] == 'binome_kill' )
		{
		$boodle->form_edit_binome( $_GET['ind'], true );
		}
	else if	( $_GET['op'] == 'binome_list' )
		{
		$boodle->list_binomes();
		}
	else if	( $_GET['op'] == 'binome_list_k' )
		{
		$boodle->list_binomes( true );
		}
	else if	( $_GET['op'] == 'logout' )
		{
		session_unset();
		echo "<p class=\"resu\">Bye Bye</p>";
		}
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
	else	$boodle->list_binomes( true );
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
	else	$boodle->list_binomes( true );
	}
else if	( isset( $_POST['binome_kill'] ) )
	{
	if	( isset( $_POST['indix'] ) )
		$boodle->kill_binome( $_POST['indix'] );
	echo "<p class=\"resu\">{$label['moded']}</p>";
	$boodle->list_binomes( true );
	}
else if	( isset( $_POST['binome_abt'] ) )
	{
	echo "<p class=\"resu\">{$label['aborted']}</p>";
	}

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
