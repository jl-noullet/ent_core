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

$db1->connect();
// traiter les commandes par GET
if	( isset($_GET['op']) )
	{
	if	( $_GET['op'] == 'init' )
		$ecole1->create_tables();
	else if	( $_GET['op'] == 'add500' )
		{
		require_once('ink/locutron.php');
		// cette fonction cree des classes (c'est la seule pour le moment)
		add_random_eleves( $db1, $ecole1, 500 );
		}
	else if	( $_GET['op'] == 'add1' )
		{
		$ecole1->extract_liste_classes( $form1->itsa['classe']->topt );
		$ecole1->form_add_eleve();
		}
	else if	( $_GET['op'] == 'classes' )
		$ecole1->show_liste_classes($label);
	else if	( $_GET['op'] == 'eleve' )
		$ecole1->form_find_eleve();
	}
else if	( isset($_GET['c']) )
	{
	$ecole1->show_1_classe($_GET['c']);
	}
else if	( isset($_GET['e']) )
	{
	$ecole1->extract_liste_classes( $form1->itsa['classe']->topt );
	$ecole1->form_edit_eleve($_GET['e']);
	}
else if	( isset($_GET['k']) )
	{
	$ecole1->extract_liste_classes( $form1->itsa['classe']->topt );
	$ecole1->form_edit_eleve( $_GET['k'], 1 );
	}

// traiter les retours de formulaire POST
else if	( isset( $_POST['add_eleve'] ) )
	{
	if	( isset( $_POST['nom'] ) )
		$nom = $_POST['nom'];
	else	$nom = 'noname';
	if	( isset( $_POST['prenom'] ) )
		$prenom = $_POST['prenom'];
	else	$prenom = 'noname';
	if	(
		( isset( $_POST['date_n_y'] ) )	&&
		( isset( $_POST['date_n_m'] ) ) &&
		( isset( $_POST['date_n_d'] ) )
		)
		$utime = mktime( 13, 0, 0, $_POST['date_n_m']+1, $_POST['date_n_d'], $_POST['date_n_y'] );
	else	$utime = 0;
	if	( isset( $_POST['classe'] ) )
		$classe = $_POST['classe'];
	else	$classe = 0;
	$ecole1->add_eleve( $nom, $prenom, $utime, $classe );
	echo "<p class=\"resu\">{$label['added']}</p>";
	$ecole1->show_1_classe($_POST['classe']);
	}
else if	( isset( $_POST['mod_eleve'] ) || isset( $_POST['kill_eleve'] ) )
	{
	if	(
		( isset( $_POST['indix'] ) ) &&
		( isset( $_POST['nom'] ) ) &&
		( isset( $_POST['prenom'] ) ) && 
		( isset( $_POST['date_n_y'] ) ) &&
		( isset( $_POST['date_n_m'] ) ) &&
		( isset( $_POST['date_n_d'] ) ) &&
		( isset( $_POST['classe'] ) )
		)
		{
		if	( isset( $_POST['kill_eleve'] ) )
			$ecole1->kill_eleve($_POST['indix']);
		else	{
			$utime = mktime( 13, 0, 0, $_POST['date_n_m']+1, $_POST['date_n_d'], $_POST['date_n_y'] );
			$ecole1->mod_eleve( $_POST['indix'], $_POST['nom'], $_POST['prenom'], $utime, $_POST['classe'] );
			}
		echo "<p class=\"resu\">{$label['moded']}</p>";
		$ecole1->show_1_classe($_POST['classe']);
		}
	else	mostra_fatal('formulaire incomplet');
	}
else if	( isset( $_POST['abt_eleve'] ) )
	{
	echo "<p class=\"resu\">{$label['aborted']}</p>";
	if	( isset( $_POST['classe'] ) )
	$ecole1->show_1_classe($_POST['classe']);
	}
else if	( isset( $_POST['find_eleve'] ) )
	{
	if	(
		( isset( $_POST['nom'] ) ) &&
		( isset( $_POST['prenom'] ) )
		)
		$ecole1->show_found_eleves( $_POST['nom'], $_POST['prenom'] );
	}

$menu1->display();
?>
</body></html>