<?php
/**
 saisie des notes, sur 20 uniquement
 */

require_once( 'modules/Loginpro/LP_func.php' );

$my_school = UserSchool();
$my_year = UserSyear();
$my_user = $_SESSION['STAFF_ID'];

$url0 = 'Modules.php?modname=' . $_REQUEST['modname'];

function reset_saisie()
{ unset($_SESSION['lp_classe']); unset($_SESSION['lp_cours']); unset($_SESSION['lp_students']); }

// echo '<pre>'; var_dump( $_SESSION ); echo '</pre>';

echo	'<style type="text/css">', "\n",
	"table.lp { border-collapse:collapse; }\n",
	"table.lp td { border:1px solid black; padding: 2px 4px 2px 6px; }\n",
	".butgreen { cursor: pointer; padding: 6px 18px;  margin: 8px 8px; border: solid 2px; ",
	"border-color: #4D4; background: #AFA; font-weight: bold; }\n",
	".hmenu { margin: 20px }\n",
	'</style>';

if	( isset( $_REQUEST['reset'] ) )
	reset_saisie(); 

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
			$my_prof_name = $row['title'] . ' ' . $row['first_name'] . ' ' . $row['last_name'];
		}
	else	$my_profile = -1;
	if	( $my_profile == 2 )
		echo '<p>Prof. <b>', $my_prof_name, '</b></p>';

	// securite
	if	( ( $my_profile < 1 ) || ( $my_profile > 2 ) )
		{ reset_saisie(); exit('<p>Droits insuffisants pour continuer</p>'); }

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
		// On doit afficher la liste des cours, limitee au prof concerne si user est 1 prof
		// il faut d'abord au moins un eleve :
		$my_students = array();
		$nada = NULL;
		LP_liste_classe( $_SESSION['lp_classe'], $nada, $nada, $my_students );
		if	( count( $my_students ) < 1 )
			{ reset_saisie(); exit( '<p>Aucun élève dans cette classe</p>' ); }
		$_SESSION['lp_students'] = $my_students;
		// echo '<p>student ', $my_students[0], '</p>';
		// Prenons le set des cours de cet eleve $my_students[0]
		$activites = array();
		LP_prog_1eleve( $my_students[0], $activites );
		// echo '<p>', count($activites), ' cours trouves</p>';
		// Prenons les données de ces cours pour les mettre dans les valeurs de $activites
		// partie commune de la requete SQL, qui va etre comletee avec $k dans le foreach
		$sqlrequest = 'SELECT title, short_name FROM course_periods WHERE ';
		if	( $my_profile == 2 )
			$sqlrequest .= 'teacher_id = ' . $my_user . ' AND ';
		$sqlrequest .= 'course_period_id = ';
		foreach	( $activites as $k => $v)
			{
			$result = db_query( $sqlrequest . $k, true );
			if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
				{
				if	( $my_profile == 2 )
					$activites[$k] = $row['short_name'];	// prof n'a pas besoin de revoir son nom
				else	$activites[$k] = $row['title'];		// admin a besoin de voir nom du prof
				}
			// else : le cours n'appartient pas au bon prof, on laisse $activites[$k] = true
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
		echo '<p>', $cnt, ' cours trouvés</p>';
		echo '<button type="submit" class="button-primary"> Ok </button> </form>';
		}
	else if	( !isset($_POST['lp_les_notes']) ) 
		{
		echo '<h2>Feuille de Notes pour une discipline</h2>';
		$sqlrequest = 'SELECT title, short_name FROM course_periods WHERE course_period_id = ' . $_SESSION['lp_cours'];
		$result = db_query( $sqlrequest, true );
		if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
			{
			if	( $my_profile == 2 )
				$lp_course_name = $row['short_name'];	// prof n'a pas besoin de revoir son nom
			else	$lp_course_name = $row['title'];	// admin a besoin de voir nom du prof
			}
		else	$lp_course_name = '[' . $_SESSION['lp_cours'] . ']';
		echo '<p>', $lp_course_name, '</p>';
		// Soyons sur d'avoir la liste des eleves concernes
		if	( !is_array($_SESSION['lp_students']) )
			{
			$my_students = array();
			$nada = NULL;
			LP_liste_classe( $_SESSION['lp_classe'], $nada, $nada, $my_students );
			$_SESSION['lp_students'] = $my_students;
			}
		if	( count( $_SESSION['lp_students'] ) < 1 )
			{ reset_saisie(); exit( '<p>Aucun élève dans cette classe</p>' ); }
		// Préparons la lecture de leurs notes actuelles pour la periode courante
		$sqlrequest = 'SELECT grade_letter FROM student_report_card_grades WHERE syear=' . $my_year
			. ' AND course_period_id=' . $_SESSION['lp_cours']
			. ' AND marking_period_id=\'' . $marking_period
			. '\' AND student_id=';		// a completer dans le foreach
		$last_name = ''; $first_name = '';
		// des tableaux tous indexes par student_id, ainsi si on trie l'un on trie les autres
		$noms_complets = array();
		$notes = array();
		// remplir les tableaux
		foreach	( $_SESSION['lp_students'] as $elem )
			{
			// lecture table student_report_card_grades (requete preparee ci-dessus)
			$result = db_query( $sqlrequest . $elem, true );
			if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
				$note[$elem] = $row['grade_letter'];
			else	$note[$elem] = '';
			if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
				$note[$elem] = 'ERR';		// erreur duplication
			// lecture table students
			LP_info_eleve( $elem, $last_name, $first_name );
			$noms_complets[$elem] = $last_name . ' ' . $first_name;
			}
		// trier par ordre alphabetique des noms
		natcasesort( $noms_complets );
		// Affichons le formulaire de notation (view ou edit)
		$edit_flag = true;
		if	( $edit_flag )
			{
			echo '<form action="', $url0, '" method="POST">';
			echo '<input type="hidden" name="lp_les_notes" value="', $_SESSION['lp_cours'], '">';
			echo '<table class="lp">';
			foreach	( $noms_complets as $k => $v )
				{
				echo '<tr><td>', $v, '</td><td>', 
				'<input type="text" name="note', $k, '" size="5" maxlength="5" value="', $note[$k], '">',
				"</td></tr>\n";
				}
			echo '</table><br><button type="submit" class="button-primary"> Sauver </button></form>';
			}
		else	{
			echo '<table class="lp">';
			foreach	( $noms_complets as $k => $v )
				echo '<tr><td>', $v, '</td><td>', $note[$k], '</td></tr>';
			echo '</table>';
			}
		}
	else	{
		if	( $_POST['lp_les_notes'] != $_SESSION['lp_cours'] )
			{ reset_saisie(); exit('<p>Erreur 666</p>'); }
		echo '<pre>', var_dump($_POST), '</pre>';

		}
	echo '<hr><div class="hmenu"><a class="butgreen" href="' . $url0 . '&reset">Retour au choix de classe</a></div>';
	}
