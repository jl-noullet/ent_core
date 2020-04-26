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

if  	( !isset($_SESSION['usuario']) )
	mostra_fatal('access denied');

require_once('ink/liste_3imacs.php');

$imacs = new boodle;
$imacs->db = $db1;
$imacs->table_binomes = 'BOO_IMACS_binomes';
$imacs->liste_eleves = $liste_3imacs;


echo '<div id="main">';
echo '<h1><button type="button" id="openbtn" onclick="openNav()">&#9776;</button>&nbsp; ', $label['header1'], '</h1>';

$boodle = $imacs;
$form_bi->itsa['eleve1']->topt = $boodle->liste_eleves;
$form_bi->itsa['eleve2']->topt = $boodle->liste_eleves;
$form_bi->itsa['eleve3']->topt = $boodle->liste_eleves;

$boodle->db->connect();
// traiter les commandes par GET
if	( isset($_GET['op']) )
	{
	if	( $_GET['op'] == 'init' )
		{
		$boodle->create_tables();
		echo "<p class=\"resu\">{$label['moded']}</p>";
		}
	else if	( $_GET['op'] == 'addbin' )
		{
		$boodle->form_add_binome();
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
	$form_bi->form2db_insert_full( $boodle->db, $boodle->table_binomes, TRUE );
	echo "<p class=\"resu\">{$label['added']}</p>";
	// $school->show_1_classe($_POST['classe']);
	}
else if	( isset( $_POST['binome_abt'] ) )
	{
	echo "<p class=\"resu\">{$label['aborted']}</p>";
	}

echo "</div>\n";
echo '<div id="sidebar"><button type="button" id="closebtn" onclick="closeNav()">&lt;&lt;</button>';
$menu1->display();
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
