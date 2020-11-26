<?php
/**
 programmes d'enseignement par classe
	options show_prof, union
 */

require_once( 'LP_func.php' );

$my_school = UserSchool();
$my_year = UserSyear();

if	( isset( $_REQUEST['test'] ) )
	{
	$sqlrequest = 'INSERT INTO program_config (syear, school_id, program, title, value)' .
			"VALUES ( '2020', '1', 'loginpro', 'pipo', 'pipo3')";
	$result = db_query( $sqlrequest, true );
	echo '<h2>DONE</h2>';
	}
else if	( !isset( $_REQUEST['lp_classe'] ) )
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
	echo '<p><input type="checkbox" name="show_prof"> verif. noms des profs</p>';
	echo '<input type="hidden" name="union">';	// finalement cette option est permanente
	echo '<p><input type="checkbox" name="check1" checked> verif. du programme pour chaque élève</p>';
	echo '<p><button type="submit" class="button-primary"> Ok </button></p> </form>';
	}
else	{
	$lp_classe = (int)$_REQUEST['lp_classe'];	// petit filtrage de securite
	$show_prof = isset($_REQUEST['show_prof']);
	if	( isset($_REQUEST['check1']) ) $_REQUEST['union'] = true;
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
		echo "<p>Classe $class_name : $effectif élèves</p>";
		echo	'<style type="text/css">', "\n",
			"table.lp { border-collapse:collapse; }\n",
			"table.lp td { border:1px solid black; padding: 2px 4px 2px 6px; }\n",
			'</style>';
		// on va travailler sur des sets de cours (course-period), representes par des arrays
		// keys = course_period_id, vals = true 
		$class_set = array();
		if	( isset($_REQUEST['union']) )	// lire les sets de chaque eleve
			{
			$all_sets = array();	// un array de sets de cours, indexe par student_id
			for	( $i = 0; $i < count($my_students); ++$i )
				{
				$j = $my_students[$i];
				$all_sets[$j] = [];
				LP_prog_1eleve( $j, $all_sets[$j] );
				$class_set += $all_sets[$j];	// union
				}
			}
		else	{				// lire le set du premier eleve
			LP_prog_1eleve( $my_students[0], $class_set );
			}
		echo '<p>', count($class_set), ' cours ', isset($_REQUEST['union'])?'au total':'premier élève', '</p>';
		// table des cours
		echo '<table class="lp">';
		foreach	( $class_set as $k => $v) {
			$sqlrequest = 'SELECT title, short_name, teacher_id, credits FROM course_periods' .
				      " WHERE course_period_id = $k";
			$result = db_query( $sqlrequest, true );
			if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
				{
				echo '<tr><td>', $k, '</td><td>', $row['title'], '</td><td>', $row['short_name'], '</td><td>',
				     $row['credits'], '</td>';
				if	( $show_prof )
					{
					$sqlrequest = 'SELECT title, first_name, last_name, profile_id FROM staff' .
						      ' WHERE staff_id=' . $row['teacher_id'];
					$result = db_query( $sqlrequest, true );
					if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
						echo '<td>', $row['title'], ' ', $row['first_name'], ' ', $row['last_name'],
						// ' [', $row['profile_id'], ']',	// verif du profile_id, doit etre 2
						'</td>';
					else	echo '<td>prof inconnu</td>';
					}
				echo '</tr>'; 
				}
			}
		echo '</table>';
		// verification exhaustive
		if	( isset($_REQUEST['check1']) )
			{
			$badcnt = 0; $goodcnt = 0;
			for	( $i = 0; $i < count($my_students); ++$i )
				{
				$check = LP_compare_sets_inc( $all_sets[$my_students[$i]], $class_set, 'cours' );
				if	( $check )
					{
					$last_name = ''; $first_name = '';
					LP_info_eleve( $my_students[$i], $last_name, $first_name );
					echo '<p>', 'élève ', $last_name, ' ', $first_name, ' : ', $check, '</p>'; $badcnt++; }
				else	$goodcnt++;
				}
			echo "<p>$goodcnt élèves Ok, $badcnt élèves avec erreur</p>";
			}
		if	( isset($_REQUEST['reprog']) )
			{
			$last_name = ''; $first_name = '';
			LP_info_eleve( $my_students[0], $last_name, $first_name );
			echo '<p>Ref student is ', $last_name, ' ', $first_name, '</p>';
			LP_reprog_1classe( $lp_classe, $my_students[0] );
			}
		}
	echo	'<style type="text/css">', "\n",
		".butgreen { cursor: pointer; padding: 6px 18px;  margin: 8px 8px; border: solid 2px; ",
		"border-color: #4D4; background: #AFA; font-weight: bold; }\n",
		".hmenu { margin: 20px }\n",
		'</style>';
	$url1 = 'Modules.php?modname=' . $_REQUEST['modname'];
	echo '<div class="hmenu"><a class="butgreen" href="' . $url1 . '">Choisir une autre classe</a></div>';
	}
?>
