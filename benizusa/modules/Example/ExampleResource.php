<?php
/**
 */

echo	'<style type="text/css">', "\n",
	"table.lp {border-collapse:collapse; }\n",
	"table.lp, td, th { border:1px solid black; padding: 2px 4px 2px 6px; }\n",
	'</style>';

// Display main header with Module icon and Program title.
// DrawHeader( ProgramTitle() );

// provisoire
$my_school = 1;
$my_year = 2020;
$my_prefix = '2020A0';


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
		$class_names[$row['id']] = $row['short_name'] . ' ' . $row['title'];
		}
	// afficher la form
	echo '<form action="', $_SERVER['REQUEST_URI'], '" method="GET"> ';
	echo '<select name="lp_classe"> ';
	foreach	($class_names as $k => $v) {
		echo '<option value="', $k, '">', $v, '</option> ';
		}
	echo '</select><br>';
	echo '<button type="submit"> Ok </button> </form>';
	}
else	{
	$lp_classe = (int)$_REQUEST['lp_classe'];	// petit filtrage de securite
	// checher nom de la classe
	$sqlrequest = 'SELECT short_name, title FROM school_gradelevels WHERE id=' . $lp_classe;
	$result = db_query( $sqlrequest, true );
	if	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		{
		$class_name = $row['short_name'] . ' ' . $row['title'];
		// identifier les eleves inscrits dans cette classe
		$sqlrequest = 'SELECT student_id FROM student_enrollment WHERE grade_id=' . $lp_classe . ' AND syear=' . $my_year; 
		// echo $sqlrequest;
		$result = db_query( $sqlrequest, true );
		$my_students = array();
		while	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
			{
			// echo '<pre>'; var_dump( $row ); echo '</pre>';
			$my_students[] = (int)$row['student_id'];
			}
		DrawHeader( 'Liste de la classe ' . $class_name . ' : ' . count($my_students) . ' élèves' );
		// echo '<pre>'; var_dump( $my_students ); echo '</pre>';
		echo '<table class="lp"><tr><td>N°</td><td>MATRICULE</td><td>NOMS ET PRENOMS</td><td>DATE DE NAISSANCE</td><td>SEXE</td><td>STATUT</td></tr>',
		     "\n";
		foreach	( $my_students as $k => $v ) {
			$sqlrequest = 'SELECT first_name, middle_name, last_name, custom_200000000, custom_200000004 '
				. 'FROM students WHERE student_id=' . $v;
			$result = db_query( $sqlrequest, true );
			if	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
				{
				if	( $row['custom_200000000'][0] == 'F') $sexe = 'F';
				else if	( $row['custom_200000000'][0] == 'M') $sexe = 'G';
				else	$sexe = ' ';
				echo '<tr><td>', 1 + $k, '</td><td>', $my_prefix, $v, '</td><td>',
				     $row['last_name'], ' ', $row['first_name'], ' ', $row['middle_name'],
				     '</td><td>', $row['custom_200000004'], '</td><td>', $sexe,'</td><td>',
				     'N', "</td></tr>\n";	
				}
			}
		echo '</table>';
		}
	}

?>