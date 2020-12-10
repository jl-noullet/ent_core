<?php
/**
 saisie du texte de competence

table program_user_config
	user_id		school_id	program		title				value
	-1		UserSchool()	'Competence'	trim_id.'_'.course_period_id	'blabla'

 */

require_once( 'modules/Loginpro/LP_func.php' );

$my_school = UserSchool();
$my_year = UserSyear();
$my_user = $_SESSION['STAFF_ID'];

$url0 = 'Modules.php?modname=' . $_REQUEST['modname'];

function reset_saisie()
{ unset($_SESSION['lp_classe']); unset($_SESSION['lp_cours']); }

// echo '<pre>'; var_dump( $_SESSION ); echo '</pre>';

echo	'<style type="text/css">', "\n",
	"table.lp { border-collapse:collapse; }\n",
	"table.lp td { border:1px solid black; padding: 2px 8px 2px 10px; }\n",
	"table.fn td { border:0; padding: 2px 8px 10px 10px; }\n",
	".butgreen { cursor: pointer; padding: 6px 18px;  margin: 8px 8px; border: solid 2px; ",
	"border-color: #4D4; background: #AFA; color: #048; font-weight: bold; font-size: 16px }\n",
	".butamber { cursor: pointer; padding: 6px 18px;  margin: 8px 8px; border: solid 2px; ",
	"border-color: #DA4; background: #FDA; color: #840; font-weight: bold; font-size: 16px }\n",
	".hmenu { margin: 20px }\n",
	"div.compet { padding: 4px; background: #FFF; width: 300px; min-height: 60px }\n",
	"textarea.lp { width: auto; color: blue }\n",	// auto car on veut cols="35" mais Rosario avait mis 100%
	'</style>';

if	( isset( $_REQUEST['reset'] ) )
	reset_saisie(); 

if	( isset( $_REQUEST['lp_classe'] ) )
	$_SESSION['lp_classe'] = (int)$_REQUEST['lp_classe'];
if	( !isset($_SESSION['lp_classe']) ) 
	{
	// ETAPE 1 : choix de la classe
	echo '<h2>Choisir une classe</h2>';
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
	echo '<div class="hmenu"><button type="submit" class="butgreen"> Ok </button></div></form>';
	}
else	{
	// partie commune aux etapes suivantes : le contexte
	// trouver nom de la classe
	$sqlrequest = 'SELECT short_name, title FROM school_gradelevels WHERE id=' . $_SESSION['lp_classe'];
	$result = db_query( $sqlrequest, true );
	if	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		{ $class_title = $row['title']; $classe_short = $row['short_name']; }
	else	{ $class_title = $_SESSION['lp_classe']; $classe_short = $_SESSION['lp_classe']; }

	// interpreter identite utilisateur
	$sqlrequest = 'SELECT title, first_name, last_name, profile_id FROM staff WHERE staff_id=' . $my_user;
	$result = db_query( $sqlrequest, true );
	if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		{
		$my_profile = $row['profile_id'];
		if	( $my_profile == 2 )
			$my_prof_name = $row['title'] . ' ' . $row['first_name'] . ' ' . $row['last_name'];
		}
	else	$my_profile = -1;

	// securite
	if	( ( $my_profile < 1 ) || ( $my_profile > 2 ) )
		{ reset_saisie(); exit('<p>Droits insuffisants pour continuer</p>'); }

	// traiter la MP
	// si UserMP() est une 'EVAL', il faudra remplacer eval par son trimestre "parent"
	$marking_period = UserMP();
	if	( $marking_period )
		{
		$sqlrequest = 'SELECT title, mp, parent_id FROM school_marking_periods WHERE marking_period_id=' . $marking_period;
		$result = db_query( $sqlrequest, true );
		if	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
			{
			if	( $row['mp'] == 'QTR' )
				{
				$marking_period = $row['parent_id'];
				$sqlrequest = 'SELECT title FROM school_marking_periods WHERE marking_period_id=' . $marking_period;
				$result = db_query( $sqlrequest, true );
				if	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
					$MP_name = $row['title'];
				else	$MP_name = '[' . $marking_period . ']';
				}
			else	$MP_name = $row['title'];
			}
		else	$MP_name = '[' . $marking_period . ']';
		}
	else	$MP_name = '[?]';

	// commencer la table de contexte
	$label_table = '<table class="fn"><tr><td>Etape :</td><td>' . $MP_name . '</td></tr>'
			. '<tr><td>Classe :</td><td>' . $class_title . '</td></tr>';
	if	( $my_profile == 2 )
		$label_table .= '<tr><td>Prof. :</td><td>' . $my_prof_name . '</td></tr>';
	// N.B. ici cette table n'est pas finie

	if	( isset( $_REQUEST['lp_cours'] ) )
		$_SESSION['lp_cours'] = (int)$_REQUEST['lp_cours'];
	if	( !isset($_SESSION['lp_cours']) ) 
		{
		// ETAPE 2 : choix de la discipline
		echo '<h2>Choisir une discipline</h2>';
		echo $label_table . '</table>';
		// On doit afficher la liste des cours, limitee au prof concerne si user est 1 prof
		// il faut d'abord au moins un eleve :
		$my_students = array();
		$nada = NULL;
		LP_liste_classe( $_SESSION['lp_classe'], $nada, $nada, $my_students );
		if	( count( $my_students ) < 1 )
			{ reset_saisie(); exit( '<p>Aucun élève dans cette classe</p>' ); }
		// Prenons le set des cours de cet eleve $my_students[0]
		$activites = array();
		LP_prog_1eleve( $my_students[0], $activites );
		// echo '<p>', count($activites), ' cours trouves</p>';
		// trions ces cours
		LP_sort_course_set( $activites );
		// on va re scanner ces cours pour une de ces 2 raisons :
		//	- admin : a besoin de voir les noms des profs... on va les mettre dans les valeurs de $activites
		//	- prof : on va neutraliser les activites qui ne le concernent pas
		// partie commune de la requete SQL, qui va etre comletee avec $k dans le foreach
		$sqlrequest = 'SELECT title, short_name FROM course_periods WHERE ';
		if	( $my_profile == 2 )
			$sqlrequest .= 'teacher_id = ' . $my_user . ' AND ';
		$sqlrequest .= 'course_period_id = ';
		$nada = NULL; $my_prof = '';
		foreach	( $activites as $k => $v)
			{
			$result = db_query( $sqlrequest . $k, true );
			if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
				{
				if	( $my_profile == 2 )
					$activites[$k] = $row['short_name'];			// prof n'a pas besoin de revoir son nom
				else	{
					LP_split_course_period( $row['title'], $nada, $nada, $my_prof );
					$activites[$k] = $row['short_name'] . ' - ' . $my_prof;	// admin a besoin de voir les noms de profs
					}
				}
			else	$activites[$k] = false;	// else : le cours n'appartient pas au bon prof, $activites[$k] = false i.e. non-string
			}
		// echo '<hr><pre>'; var_dump( $activites ); echo '</pre><hr>';
		// On fait une form avec select input base sur cette liste
		echo '<form action="', $url0, '" method="GET"> ';
		echo '<select name="lp_cours"> ';
		$cnt = 0;
		foreach	( $activites as $k => $v)
			{
			if	( is_string( $v ) )
				{ echo '<option value="', $k, '">', $v, '</option> '; $cnt++; }
			}
		if	( $cnt < 1 )
			{ reset_saisie(); exit( '<p>Aucun cours dans cette classe</p>' ); }
		echo '</select><br>';
		// echo '<p>', $cnt, ' cours trouvés</p>';
		echo '<div class="hmenu"><button type="submit" class="butgreen"> Ok </button></div></form>';
		}
	else if	( !isset($_POST['lp_la_competence']) ) 
		{
		echo '<h2>Description de la compétence pour une discipline</h2>';
		// ETAPE 3 : la competence
		// retrouver le nom de ce cours et du prof
		$nada = NULL; $my_prof = '';
		$sqlrequest = 'SELECT title, short_name FROM course_periods WHERE course_period_id = ' . $_SESSION['lp_cours'];
		$result = db_query( $sqlrequest, true );
		if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
			{
			$lp_course_name = $row['short_name'];
			if	( $my_profile != 2 )
				LP_split_course_period( $row['title'], $nada, $nada, $my_prof );
			}
		else	$lp_course_name = '[' . $_SESSION['lp_cours'] . ']';

		$label_table .= '<tr><td>Discipline :</td><td>' . $lp_course_name . '</td></tr>';
		if	( $my_prof )
			$label_table .= '<tr><td>Prof.</td><td>' . $my_prof . '</td></tr>';
		echo $label_table . '</table>';

		// allons lire la competence
		$comp_title = $marking_period . '_' . $_SESSION['lp_cours'];
		$sqlrequest = "SELECT value FROM program_user_config WHERE program='Competence' AND user_id='-1'"
			. ' AND school_id=' . UserSchool() . " AND title='" . $comp_title . "'";		
		// echo '<p>', $sqlrequest, '</p>';
		$result = db_query( $sqlrequest, true );
		if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
			$le_texte = $row['value'];
		else	$le_texte = '';



		// Affichons le formulaire de saisie (view ou edit)
		$edit_flag = isset($_REQUEST['edit_flag']);
		if	( $edit_flag )
			{
			echo '<form action="', $url0, '" method="POST">';
			echo '<input type="hidden" name="lp_la_competence" value="', $_SESSION['lp_cours'], '">';
			echo '<textarea class="lp" name="description" rows="8" cols="35" maxlength="2000">', $le_texte, '</textarea>';
			echo '<div class="hmenu"><button type="submit" class="butamber"> Enregistrer </button></div></form>';
			}
		else	{
			$le_texte = nl2br( htmlspecialchars( $le_texte, ENT_HTML5 ) );
			echo '<div class="compet">', $le_texte, '</div>';
			echo '<hr><div class="hmenu"><a class="butamber" href="' . $url0 . '&edit_flag">Introduire ou modifier la description</a></div>';
			}
		}
	else	{
		if	( $_POST['lp_la_competence'] != $_SESSION['lp_cours'] )
			{ reset_saisie(); exit('<p>Erreur 666</p>'); }
		if	( !isset($_POST['description']) )
			{ reset_saisie(); exit('<p>Erreur 667</p>'); }
		// on doit escaper les single quotes du texte saisi
		// !!! UWAGA !!! pour que postgres le prenne, il faut inserer un 'E' devant l'opening quote !
		$compet = addslashes($_POST['description']);	
		$comp_title = $marking_period . '_' . $_SESSION['lp_cours'];
		// on procede par DELETE + INSERT plutot que UPDATE pour etre sur d'eliminer les doublons
		$sql_delete = "DELETE FROM program_user_config WHERE program='Competence'"
			. ' AND school_id=' . UserSchool() . " AND title='" . $comp_title . "'";		
		$sql_insert = 'INSERT INTO program_user_config (user_id,school_id,program,title,value) VALUES ('
			. "-1," . UserSchool() . ",'Competence','" . $comp_title . "',E'" . $compet . "')";
		// echo '<p>', $sql_delete, '</p>';
		$result = db_query( $sql_delete, true );
		// echo '<pre>affected rows: ', pg_affected_rows($result), '</pre>';
		// echo '<p>', $sql_insert, '</p>';
		$result = db_query( $sql_insert, true );
		// echo '<pre>affected rows: ', pg_affected_rows($result), '</pre>';
		// affichage de la feuille mise a jour
		echo '<script>window.location.assign("', $url0, '");</script>';
		}
	echo '<hr><div class="hmenu"><a class="butgreen" href="' . $url0 . '&reset">Retour au choix de classe</a></div>';
	}
