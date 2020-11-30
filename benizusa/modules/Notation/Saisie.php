<?php
/**
 saisie des notes, sur 20 uniquement
 */

// require_once( 'modules/Loginpro/LP_func.php' );
$my_school = UserSchool();
$my_year = UserSyear();
$my_user = $_SESSION['STAFF_ID'];

$url0 = 'Modules.php?modname=' . $_REQUEST['modname'];

// echo '<pre>'; var_dump( $_SESSION ); echo '</pre>';

echo	'<style type="text/css">', "\n",
	"table.lp { border-collapse:collapse; }\n",
	"table.lp td { border:1px solid black; padding: 2px 4px 2px 6px; }\n",
	".butgreen { cursor: pointer; padding: 6px 18px;  margin: 8px 8px; border: solid 2px; ",
	"border-color: #4D4; background: #AFA; font-weight: bold; }\n",
	".hmenu { margin: 20px }\n",
	'</style>';

if	( isset( $_REQUEST['reset'] ) )
	{ unset($_SESSION['lp_classe']); unset($_SESSION['lp_cours']); }

if	( isset( $_REQUEST['lp_classe'] ) )
	$_SESSION['lp_classe'] = (int)$_REQUEST['lp_classe'];
if	( !isset($_SESSION['lp_classe']) ) 
	{
	DrawHeader( 'Choisir une classe' );
	// obtenir la liste des classes
	$sqlrequest = 'SELECT id, short_name, title FROM school_gradelevels WHERE school_id=' . $my_school . ' ORDER BY short_name'; // DESC';
	$result = db_query( $sqlrequest, true );
	$class_names = array();
	while	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		{
		// echo '<pre>'; var_dump( $row ); echo '</pre>';
		$class_names[$row['id']] = $row['short_name'] . ' (' . $row['title'] . ')';
		}
	// afficher la form
	echo '<form action="', $_SERVER['REQUEST_URI'], '" method="GET"> ';
	echo '<select name="lp_classe"> ';
	foreach	($class_names as $k => $v) {
		echo '<option value="', $k, '">', $v, '</option> ';
		}
	echo '</select><br>';
	echo '<button type="submit" class="button-primary"> Ok </button> </form>';
	}
else	{
	// chercher et afficher nom de la classe
	$sqlrequest = 'SELECT short_name, title FROM school_gradelevels WHERE id=' . $_SESSION['lp_classe'];
	$result = db_query( $sqlrequest, true );
	if	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		{
		$class_title = $row['title']; $classe_short = $row['short_name'];
		echo '<p>Classe <b>', $classe_short, '</b> : ', $class_title, '</p>';
		}
	else	echo '<p>Classe <b>[', $_SESSION['lp_classe'], ']</b></p>';
	// interpreter identite utilisateur, afficher si prof
	$sqlrequest = 'SELECT title, first_name, last_name, profile_id FROM staff WHERE staff_id=' . $my_user;
	$result = db_query( $sqlrequest, true );
	if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		{
		$my_profile = $row['profile_id'];
		if	( $my_profile == 2 )
			$my_prof = $row['title'] . ' ' . $row['first_name'] . ' ' . $row['last_name'];
		}
	else	$my_profile = -1;
	if	( $my_profile == 2 )
		echo '<p>Prof. <b>', $my_prof, '</b></p>';

	// securite
	if	( ( $my_profile < 1 ) || ( $my_profile > 2 ) )
		{ unset($_SESSION['lp_classe']); unset($_SESSION['lp_cours']); exit('<p>Droits insuffisants pour continuer</p>'); }

	// chercher et afficher le nom de la MP
	$marking_period = UserMP();
	if	( $marking_period )
		{
		$sqlrequest = 'SELECT title FROM school_marking_periods WHERE marking_period_id=' . $marking_period;
		$result = db_query( $sqlrequest, true );
		if	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
			$MP_name = $row['title'];
		else	$MP_name = '[' . $marking_period . ']';
		}
	else	$MP_name = '[?]';
	echo '<p><b>', $MP_name, '</b></p>';	

	if	( isset( $_REQUEST['lp_cours'] ) )
		$_SESSION['lp_cours'] = (int)$_REQUEST['lp_cours'];
	if	( !isset($_SESSION['lp_cours']) ) 
		{
		// afficher la liste des cours, pour le prof concerne si user est 1 prof
		echo 'Euh';
		}
	else	{
		echo '<p>cours choisi ', $_SESSION['lp_cours'], '</p>';

		// afficher le formulaire de notation (option view ou edit), pour la periode courante
		}
	echo '<hr><div class="hmenu"><a class="butgreen" href="' . $url0 . '&reset">Retour au choix de classe</a></div>';
	}
