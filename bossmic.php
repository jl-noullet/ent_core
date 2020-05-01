<?php
session_start();

require_once('ink/longage.php');
require_once('ink/db.php');
require_once('ink/def.php');
require_once('ink/boodle.php');
require_once('ink/boodladm.php');
require_once('ink/head.php');

if  	( !isset($_SESSION['cacique']) )
	{	// traiter login
	if	( ( isset( $_REQUEST['id_1136'] ) ) && ( isset( $_REQUEST['id_1137'] ) ) )
		{
		if	( $_REQUEST['id_1137'] == 'risc' )
			{
			if	( $_REQUEST['id_1136'] == 'stanford' )
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

$self = $_SERVER['PHP_SELF'];
if	( preg_match( '/(mic|imacs|pro)[.]php$/', $self, $apo ) == 0 )
	{
	echo "<p>[ {$_SERVER['PHP_SELF']} ]</p>";
	mostra_fatal('access denied');
	}

$menua = new menu;
// $menua->add( "$self?op=init", 'Initialiser la base de données' );
$menua->add( "$self?op=binome_add", 'Ajouter un binome' );
$menua->add( "$self?op=binome_list_k", 'Editer liste des binomes' );
$menua->add( "$self?op=login_list", 'Editer liste des logins' );
$menua->add( "$self?op=reponse&e=1&g=1&q=Q1A1", 'reponses Q1A1' );
$menua->add( "$self?op=reponse&e=1&g=1&q=Q1A2", 'reponses Q1A2' );
$menua->add( "$self?op=reponse&e=1&g=1&q=Q1A3", 'reponses Q1A3' );
$menua->add( "$self?op=reponse&e=1&g=1&q=Q1A4", 'reponses Q1A4' );
$menua->add( "$self?op=reponse&e=1&g=1&q=Q1X1", 'reponses Q1X1' );
$menua->add( "$self?op=reponse&e=1&g=1&q=Q1X2", 'reponses Q1X2' );
$menua->add( "$self?op=reponse&e=1&g=1&q=Q1X3", 'reponses Q1X3' );
$menua->add( "$self?op=reponse&e=1&g=1&q=Q1X4", 'reponses Q1X4' );
$menua->add( "$self?op=reponse&e=1&g=1&q=Q1Y1", 'reponses Q1Y1' );
$menua->add( "$self?op=reponse&e=1&g=1&q=Q1Y2", 'reponses Q1Y2' );

$menua->add( "$self?op=reponse&e=2&g=1&q=Q2A1", 'reponses Q2A1' );
$menua->add( "$self?op=reponse&e=2&g=1&q=Q2A2", 'reponses Q2A2' );
$menua->add( "$self?op=reponse&e=2&g=1&q=Q2A4", 'reponses Q2A4' );
$menua->add( "$self?op=reponse&e=2&g=1&q=Q2X1", 'reponses Q2X1' );
$menua->add( "$self?op=reponse&e=2&g=1&q=Q2X2", 'reponses Q2X2' );
$menua->add( "$self?op=reponse&e=2&g=1&q=Q2X4", 'reponses Q2X4' );
$menua->add( "$self?op=reponse&e=2&g=1&q=Q2X5", 'reponses Q2X5' );
$menua->add( "$self?op=reponse&e=2&g=1&q=Q2X6", 'reponses Q2X6' );
$menua->add( "$self?op=reponse&e=2&g=1&q=Q2X7", 'reponses Q2X7' );
$menua->add( "$self?op=reponse&e=2&g=1&q=Q2Y1", 'reponses Q2Y1' );
$menua->add( "$self?op=reponse&e=2&g=1&q=Q2Y2", 'reponses Q2Y2' );

$menua->add( "$self?op=logout", 'Logout' );

$boodle = new boodladm;
$boodle->init( $apo[1] );

echo '<div id="main">';
echo '<h2><button type="button" id="openbtn" onclick="openNav()">&#9776;</button>&nbsp; ', $label['header1'] . $apo[1], '</h2>';

$form_bi->itsa['eleve1']->topt = $boodle->liste_eleves;
$form_bi->itsa['eleve2']->topt = $boodle->liste_eleves;
$form_bi->itsa['eleve3']->topt = $boodle->liste_eleves;

$boodle->db->connect();


// traiter les commandes par GET
if	( isset($_GET['op']) )
	{
	if	( $_GET['op'] == 'init' )	// URL ?op=init&tab=logins | binomes | 1-5
		{
		if	( $apo[1] == 'mic' )   $binfirst = 1;
		else if	( $apo[1] == 'imacs' ) $binfirst = 200;
		else if	( $apo[1] == 'pro' )   $binfirst = 500;
		$boodle->create_tables( $_GET['tab'], $binfirst );
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
	else if	( $_GET['op'] == 'reponse' )	// URL ?op=reponse&g=1&e=1&q=Q1A1
		{
		$boodle->liste_reponse( $_GET['e'], $_GET['q'], $_GET['g'] );
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
document.getElementById("sidebar").style.width = "<?php echo $menuwidth; ?>";
document.getElementById("main").style.marginLeft = "<?php echo $menuwidth; ?>";
document.getElementById("openbtn").style.display = "none"; }
function closeNav() {
document.getElementById("sidebar").style.width = "0";
document.getElementById("main").style.marginLeft= "0";
document.getElementById("openbtn").style.display = "inline"; }
</script>
</body></html>
