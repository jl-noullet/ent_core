<?php
session_start();

require_once('ink/longage.php');
require_once('ink/db.php');
require_once('ink/def.php');
require_once('ink/boodle.php');
require_once('ink/boodladm.php');
require_once('ink/head.php');

// zone de login rudimentaire pour dev.

if  	( !isset($_SESSION['cacique']) )
	{	// traiter login
	if	( ( isset( $_REQUEST['id_1136'] ) ) && ( isset( $_REQUEST['id_1137'] ) ) )
		{
		if	( $_REQUEST['id_1137'] == 'risc' )
			{
			if	( $_REQUEST['id_1136'] == 'harward' )
				$_SESSION['cacique'] = $_REQUEST['id_1136'];
			}
		}
	}

if  	( !isset($_SESSION['cacique']) )
	{	// proposer login
	$self = $_SERVER['PHP_SELF'];
	echo "<form method=\"post\" action=\"{$self}\">"
	?>
	<table class="login"><tr><td>login :</td><td><input size="30" name="id_1136" type="text"></td></tr>
	<tr><td>mot de passe :</td><td><input size="30" name="id_1137" type="password"></td></tr>
	</table>
	<input value="Go!" size="20" type="submit">
	</form></body></html>
	<?php
	exit();
	}

$menua = new menu;
$menua->add( "$self?op=init", 'Initialiser la base de données' );
$menua->add( "$self?op=binome_add", 'Ajouter un binome' );
$menua->add( "$self?op=binome_list_k", 'Editer liste des binomes' );
$menua->add( "$self?op=login_list", 'Editer liste des logins' );
$menua->add( "$self?op=logout", 'Logout' );

$boodle = new boodladm;
$boodle->init( 'mic' );

echo '<div id="main">';
echo '<h1><button type="button" id="openbtn" onclick="openNav()">&#9776;</button>&nbsp; ', $label['header1'], '</h1>';

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
	else if	( $_GET['op'] == 'binome_add' )
		{
		$boodle->form_add_binome();
		}
	else if	( $_GET['op'] == 'binome_edit' )
		{
		$boodle->form_edit_binome( $_GET['ind'], FALSE );
		}
	else if	( $_GET['op'] == 'binome_kill' )
		{
		$boodle->form_edit_binome( $_GET['ind'], TRUE );
		}
	else if	( $_GET['op'] == 'binome_list_k' )
		{
		$boodle->list_binomes( true );
		}
	else if	( $_GET['op'] == 'login_list' )
		{
		$boodle->list_logins();
		}
	else if	( $_GET['op'] == 'login_kill' )
		{
		if	( isset($_GET['confirmed']) )
			$boodle->kill_login( $_GET['ind'], TRUE );
		else	$boodle->kill_login( $_GET['ind'], FALSE );
		}
	else if	( $_GET['op'] == 'logout' )
		{
		session_unset();
		echo "<p class=\"resu\">Bye Bye</p></body></html>";
		exit();
		}
	}

// traiter les retours de formulaire POST (c'est le else de if	( isset($_GET[...]) ) )
// POSTS relatifs a l'objet binome
else if	( isset( $_POST['binome_add'] ) )
	{
	$form_bi->post2form_full( TRUE );
	$curbin = $form_bi->form2db_insert_full( $boodle->db, $boodle->table_binomes, TRUE );
	echo "<p class=\"resu\">{$label['added']}</p>";
	$boodle->list_binomes( true );
	}
else if	( isset( $_POST['binome_mod'] ) )
	{
	$form_bi->post2form_full( FALSE );
	$form_bi->form2db_update_full( $boodle->db, $boodle->table_binomes );
	echo "<p class=\"resu\">{$label['moded']}</p>";
	$boodle->list_binomes( true );
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

echo "</div>\n";
echo '<div id="sidebar"><button type="button" id="closebtn" onclick="closeNav()">&lt;&lt;</button>';
$menu = $menua;
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
