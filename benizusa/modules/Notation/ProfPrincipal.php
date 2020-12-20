<?php
/**
 saisie du nom du prof principal parmi les profs de la classe

table program_user_config
	user_id		school_id	program		title			value
	-1		UserSchool()	'ProfPrincip'	grade_level_id		user_id

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
	$sqlrequest = 'SELECT profile_id FROM staff WHERE staff_id=' . $my_user;
	$result = db_query( $sqlrequest, true );
	if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		{
		$my_profile = $row['profile_id'];
		}
	else	$my_profile = -1;

	// securite
	if	( $my_profile != 1 )
		{ reset_saisie(); exit('<p>Droits insuffisants pour continuer</p>'); }

	// commencer la table de contexte
	$label_table = '<table class="fn">'
			. '<tr><td>Classe :</td><td>' . $class_title . '</td></tr>';
	// N.B. ici cette table n'est pas finie

	if	( !isset($_POST['lp_ppid']) ) 
		{
		if	( isset($_REQUEST['edit_flag']) )
			{
			// ETAPE 2 : choix du prof, au travers du choix de la discipline
			echo '<h2>Choisir le professeur principal de cette classe</h2>';
			echo $label_table . '</table>';
			// On doit recuperer la liste des cours, et n'en garder que les profs
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
			// on va re scanner ces cours pour voir les noms des profs...
			// et mettre leurs id les valeurs de $activites
			// partie commune de la requete SQL, qui va etre completee avec $k dans le foreach
			$sqlrequest = 'SELECT title, teacher_id FROM course_periods WHERE ';
			$sqlrequest .= 'course_period_id = ';
			$nada = NULL; $my_prof = ''; $nom_prof = array();
			foreach	( $activites as $k => $v)
				{
				$result = db_query( $sqlrequest . $k, true );
				if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
					{
					$activites[$k] = $row['teacher_id'];
					// solution cheap pour avoir le nom du prof
					LP_split_course_period( $row['title'], $nada, $nada, $my_prof );
					$nom_prof[$row['teacher_id']] = $my_prof;
					}
				else	$activites[$k] = false;
				}
			// echo '<hr><pre>'; var_dump( $activites ); echo '</pre><hr>';
			// On fait une form avec select input base sur cette liste
			echo '<form action="', $url0, '" method="POST"> ';
			echo '<select name="lp_ppid"> ';
			$cnt = 0;
			foreach	( $activites as $k => $v)
				{
				if	( is_string( $v ) )
					{ echo '<option value="', $v, '">', $nom_prof[$v], '</option> '; $cnt++; }
				}
			if	( $cnt < 1 )
				{ reset_saisie(); exit( '<p>Aucun cours dans cette classe</p>' ); }
			echo '</select><br>';
			// echo '<p>', $cnt, ' cours trouvés</p>';
			echo '<div class="hmenu"><button type="submit" class="butgreen"> Ok </button></div></form>';
			}
		else	{
			echo '<h2>Désignation du professeur principal d\'une classe</h2>';
			// allons lire la designation
			$sqlrequest = "SELECT value FROM program_user_config WHERE program='ProfPrincip' AND user_id='-1'"
				. ' AND school_id=' . UserSchool() . " AND title='" . $_SESSION['lp_classe'] . "'";		
			// echo '<p>', $sqlrequest, '</p>';
			$result = db_query( $sqlrequest, true );
			if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
				{
				$prof_id = $row['value'];
				$sqlrequest = 'SELECT title, first_name, last_name FROM staff'
					. ' WHERE staff_id=' . $prof_id;
				$result = db_query( $sqlrequest, true );
				if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
				$le_prof = $row['title'] . ' ' . $row['first_name'] . ' ' . $row['last_name'];
				}
			else	$le_prof = '';
			$label_table .= '<tr><td>Professeur Principal :</td><td>' . $le_prof . '</td></tr>';
			echo $label_table . '</table>';
			echo '<hr><div class="hmenu"><a class="butamber" href="' . $url0
				. '&edit_flag">Introduire ou modifier le prof. principal</a></div>';	
			}
		}
	else	{
		// on procede par DELETE + INSERT plutot que UPDATE pour etre sur d'eliminer les doublons
		$sql_delete = "DELETE FROM program_user_config WHERE program='ProfPrincip'"
			. ' AND school_id=' . UserSchool() . " AND title='" . $_SESSION['lp_classe'] . "'";		
		$sql_insert = 'INSERT INTO program_user_config (user_id,school_id,program,title,value) VALUES ('
			. "-1," . UserSchool() . ",'ProfPrincip','"
			. $_SESSION['lp_classe'] . "','" . (int)$_POST['lp_ppid'] . "')";
		// echo '<p>', $sql_delete, '</p>';
		$result = db_query( $sql_delete, true );
		// echo '<pre>affected rows: ', pg_affected_rows($result), '</pre>';
		// echo '<p>', $sql_insert, '</p>';
		$result = db_query( $sql_insert, true );
		// echo '<pre>affected rows: ', pg_affected_rows($result), '</pre>';
		// affichage de la désignation mise a jour
		echo '<script>window.location.assign("', $url0, '");</script>';
		}
	echo '<hr><div class="hmenu"><a class="butgreen" href="' . $url0 . '&reset">Retour au choix de classe</a></div>';
	}
/*
		// allons lire la competence
		$comp_title = $marking_period . '_' . $_SESSION['lp_cours'];
		$sqlrequest = "SELECT value FROM program_user_config WHERE program='Competence' AND user_id='-1'"
			. ' AND school_id=' . UserSchool() . " AND title='" . $comp_title . "'";		
		// echo '<p>', $sqlrequest, '</p>';
		$result = db_query( $sqlrequest, true );
		if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
			$le_texte = $row['value'];
		else	$le_texte = '';
*/



/*
		}
*/

