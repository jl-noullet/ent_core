<?php
session_start();
require_once('ink/longage.php');
require_once('ink/school.php');
require_once('ink/def.php');
if	( !isset($_SESSION['lang']) )
	$_SESSION['lang']='fr';
if	( ( isset($_GET['op']) ) && ( $_GET['op'] == 'lang' ) )
	$_SESSION['lang'] = ( $_SESSION['lang'] == 'en' )?'fr':'en';
if	( $_SESSION['lang'] == 'en' )
	require_once('ink/lang_en.php');
else	require_once('ink/lang_fr.php');
require_once('ink/head.php');

echo '<div id="main">';
echo '<h1><button type="button" id="openbtn" onclick="openNav()">&#9776;</button>&nbsp; ', $label['header1'], '</h1>';

$school = $ecole1;
$school->db->connect();
// traiter les commandes par GET
if	( isset($_GET['op']) )
	{
	if	( $_GET['op'] == 'init' )
		{
		$school->create_tables();
		echo "<p class=\"resu\">{$label['moded']}</p>";
		}
	else if	( $_GET['op'] == 'add500' )
		{
		require_once('ink/locutron.php');
		// cette fonction cree des classes (c'est la seule pour le moment)
		add_random_eleves( $school, 500 );
		echo "<p class=\"resu\">{$label['added']}</p>";
		}
	else if	( $_GET['op'] == 'add1' )
		{
		$school->extract_liste_classes( $form_s->itsa['classe']->topt );
		if	( isset( $_GET['c'] ) )
			$school->form_add_eleve( $_GET['c'] );
		else	$school->form_add_eleve();
		}
	else if	( $_GET['op'] == 'classes' )
		$school->show_liste_classes();
	else if	( $_GET['op'] == 'eleve' )
		$school->form_find_eleve();
	else if	( $_GET['op'] == 'add1c' )
		$school->form_add_class();
	}
else if	( isset($_GET['lc']) )
	{
	$school->show_1_classe($_GET['lc']);
	}
else if	( isset($_GET['es']) )
	{
	$school->extract_liste_classes( $form_s->itsa['classe']->topt );
	$school->form_edit_eleve($_GET['es']);
	}
else if	( isset($_GET['ec']) )
	{
	$school->form_edit_class($_GET['ec']);
	}
else if	( isset($_GET['ks']) )
	{
	$school->extract_liste_classes( $form_s->itsa['classe']->topt );
	$school->form_edit_eleve( $_GET['ks'], 1 );
	}

// traiter les retours de formulaire POST
// POSTS relatifs a l'objet eleve
else if	( isset( $_POST['eleve_add'] ) )
	{
	$form_s->post2form_full( TRUE );
	$form_s->form2db_insert_full( $school->db, $school->table_eleves, TRUE );
	echo "<p class=\"resu\">{$label['added']}</p>";
	$school->show_1_classe($_POST['classe']);
	}
else if	( isset( $_POST['eleve_mod'] ) || isset( $_POST['eleve_kill'] ) )
	{
	$form_s->post2form_full( FALSE );
	if	( isset( $_POST['eleve_kill'] ) )
		$school->kill_eleve($_POST['indix']);
	else	$form_s->form2db_update_full( $school->db, $school->table_eleves );
	echo "<p class=\"resu\">{$label['moded']}</p>";
	$school->show_1_classe($_POST['classe']);
	}
else if	( isset( $_POST['eleve_abt'] ) )
	{
	echo "<p class=\"resu\">{$label['aborted']}</p>";
	if	( isset( $_POST['classe'] ) )
		$school->show_1_classe($_POST['classe']);
	}
else if	( isset( $_POST['eleve_find'] ) )
	{
	if	(
		( isset( $_POST['nom'] ) ) &&
		( isset( $_POST['prenom'] ) )
		)
		$school->show_found_eleves( $_POST['nom'], $_POST['prenom'] );
	}
// POSTS relatifs a l'objet classe
else if	( isset( $_POST['classe_add'] ) )
	{
	$form_c->post2form_full( TRUE );
	$form_c->form2db_insert_full( $school->db, $school->table_classes, TRUE );
	echo "<p class=\"resu\">{$label['added']}</p>";
	$school->show_liste_classes();
	}
else if	( isset( $_POST['classe_mod'] ) )
	{
	$form_c->post2form_full( FALSE );
	//if	( isset( $_POST['kill_classe'] ) )
	//	$school->kill_classe($_POST['indix']);
	//else
		$form_c->form2db_update_full( $school->db, $school->table_classes );
	echo "<p class=\"resu\">{$label['moded']}</p>";
	$school->show_liste_classes();
	}
else if	( isset( $_POST['classe_abt'] ) )
	{
	echo "<p class=\"resu\">{$label['aborted']}</p>";
	$school->show_liste_classes();
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