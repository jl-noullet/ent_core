<?php
/**
 programmes d'enseignement par classe
	options show_prof, 
 */

require_once( 'LP_func.php' );

$my_school = UserSchool();
$my_year = UserSyear();

if	( !isset( $_REQUEST['lp_classe'] ) )
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
	echo '<form action="', $_SERVER['REQUEST_URI'], '&check_eleves', '" method="GET"> ';
	echo '<select name="lp_classe"> ';
	foreach	($class_names as $k => $v) {
		echo '<option value="', $k, '">', $v, '</option> ';
		}
	echo '</select><br>';
	echo '<button type="submit" class="button-primary"> Ok </button> </form>';
	}
else	{
	$lp_classe = (int)$_REQUEST['lp_classe'];	// petit filtrage de securite
	$show_prof = isset($_REQUEST['show_prof']);
	$class_name = ''; $class_short_name = '';
	// un array indexe par index arbitraire
	$my_students = array();
	LP_liste_classe( $lp_classe, $class_name, $class_short_name, $my_students );
	$effectif = count( $my_students );
	if	( !$class_name )
		echo "<p>Classe $lp_classe inconnue</p>";
	else if	( $effectif == 0 )
		echo "<p>Classe $class_name n'a pas d'élèves</p>";
	else	{
		echo "<p>Classe $class_name a $effectif élèves</p>";
		echo	'<style type="text/css">', "\n",
			"table.lp { border-collapse:collapse; }\n",
			"table.lp td { border:1px solid black; padding: 2px 4px 2px 6px; }\n",
			'</style>';
		// un array de set de cours (course-period) indexe par leur title
		$activites = array();
		// lire le set pour le premier eleve de la classe
		LP_prog_1eleve( $my_students[0], $activites ); 
		echo '<p>', count($activites), ' cours pour le premier élève</p>';
		echo '<table class="lp">';
		foreach	( $activites as $k => $v) {
			$sqlrequest = 'SELECT title, short_name, teacher_id, credits FROM course_periods' .
				      " WHERE course_period_id = $k";
			$result = db_query( $sqlrequest, true );
			if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
				{
				echo '<tr><td>', $row['title'], '</td><td>', $row['short_name'], '</td><td>',
				     $row['credits'], '</td><td>';
				if	( $show_prof )
					{
					$sqlrequest = 'SELECT title, first_name, last_name, profile_id FROM staff' .
						      ' WHERE staff_id=' . $row['teacher_id'];
					$result = db_query( $sqlrequest, true );
					if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
						echo $row['title'], ' ', $row['first_name'], ' ', $row['last_name'], ' [', $row['profile_id'], ']</td>';
					else	echo 'prof inconnu</td>';
					}
				else	echo $row['teacher_id'], '</td>';
				echo '</tr>'; 
				}
			}
		echo '</table>';
		if	( isset($_REQUEST['check_eleves']) )
			{
			$activites2 = array(); $badcnt = 0; $goodcnt = 1;
			for	( $i = 1; $i < count($my_students); ++$i )
				{
				$activites2 = [];
				LP_prog_1eleve( $my_students[$i], $activites2 );
				$check = LP_compare_sets( $activites, $activites2 );
				if	( $check )
					{ echo '<p>', 'élève ', $i, ' : ', $check, '</p>'; $badcnt++; }
				else	$goodcnt++;
				}
			echo "<p>$goodcnt eleves Ok, $badcnt erreurs</p>";
			}
		}
	}
?>
