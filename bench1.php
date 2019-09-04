<?php
require_once('ink/longage.php');
require_once('ink/school.php');
require_once('ink/def.php');
require_once('ink/head.php');

$db1->connect();
// traiter les commandes par GET
if	( isset($_GET['op']) )
	{
	if	( $_GET['op'] == 'init' )
		$retval = $ecole1->create_tables();
	else if	( $_GET['op'] == 'add100' )
		{
		require_once('ink/locutron.php');
		// cette fonction cree des classes (c'est la seule pour le moment)
		$retval = add_random_eleves( $db1, $ecole1, 100 );
		}
	else if	( $_GET['op'] == 'add1' )
		{
		// echo '<pre>'; var_dump($form1->itsa['classe']->topt); echo '</pre>';  
		$ecole1->extract_liste_classes( $form1->itsa['classe']->topt );
		// echo '<pre>'; var_dump($form1->itsa['classe']->topt); echo '</pre>';  
		$retval = $ecole1->form_add_eleve();
		}
	else if	( $_GET['op'] == 'classes' )
		$retval = $ecole1->show_liste_classes();
	else if	( $_GET['op'] == 'eleve' )
		$retval = $ecole1->form_find_eleve();
	if	( $retval )
		mostra_fatal($retval);
	}
else if	( isset($_GET['c']) )
	{
	$retval = $ecole1->show_1_classe($_GET['c']);
	if	( $retval )
		mostra_fatal($retval);
	}
else if	( isset($_GET['e']) )
	{
	$ecole1->extract_liste_classes( $form1->itsa['classe']->topt );
	$retval = $ecole1->form_edit_eleve($_GET['e']);
	if	( $retval )
		mostra_fatal($retval);
	}
else if	( isset($_GET['k']) )
	{
	$ecole1->extract_liste_classes( $form1->itsa['classe']->topt );
	$retval = $ecole1->form_edit_eleve( $_GET['k'], 1 );
	if	( $retval )
		mostra_fatal($retval);
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
	if	( isset( $_POST['date_n'] ) )
		$date = $_POST['date_n'];
	else	$date = '2000-01-01';
	if	( isset( $_POST['classe'] ) )
		$classe = $_POST['classe'];
	else	$classe = 0;
	$retval = $ecole1->add_eleve( $nom, $prenom, $date, $classe );
	if	( $retval )
		mostra_fatal($retval);
	else	echo '<p>ajout effectué</p>';
	}
else if	( isset( $_POST['mod_eleve'] ) || isset( $_POST['kill_eleve'] ) )
	{
	if	(
		( isset( $_POST['indix'] ) ) &&
		( isset( $_POST['nom'] ) ) &&
		( isset( $_POST['prenom'] ) ) &&
		( isset( $_POST['date_n'] ) ) &&
		( isset( $_POST['classe'] ) )
		)
		{
		if	( isset( $_POST['kill_eleve'] ) )
			$retval = $ecole1->kill_eleve($_POST['indix']);
		else	$retval = $ecole1->mod_eleve( $_POST['indix'], $_POST['nom'], $_POST['prenom'], $_POST['date_n'], $_POST['classe'] );
		if	( $retval )
			mostra_fatal($retval);
		else	echo '<p>modification effectuée</p>';
		}
	else	mostra_fatal('formulaire incomplet');
	}
else if	( isset( $_POST['abt_eleve'] ) )
	echo '<p>opération abandonnée</p>';
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