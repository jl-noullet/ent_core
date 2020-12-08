<?php

require_once( 'modules/Loginpro/LP_func.php' );

if	( isset( $_REQUEST['explo'] ) )
	{
	require_once( 'modules/Notation/explo.php' );
	exit('<hr>');
	}

$my_school = UserSchool();
$my_year = UserSyear();

$url0 = 'Modules.php?modname=' . $_REQUEST['modname'];

echo	'<style type="text/css">', "\n",
	"table.lp { border-collapse:collapse; }\n",
	"table.lp td { border:1px solid black; padding: 2px 8px 2px 10px; }\n",
	"td.cn { text-align: center; font-weight: bold; background-color: #00D; color: #DDF; }\n",
	".ari { text-align: right; }",
	'</style>';

// preparer boucle sur les classes
$sqlrequest = 'SELECT id, short_name, title FROM school_gradelevels WHERE school_id=' . $my_school . ' ORDER BY short_name'; // DESC';
$result = db_query( $sqlrequest, true );
$class_names = array();
while	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
	{
	// echo '<pre>'; var_dump( $row ); echo '</pre>';
	$class_names[$row['id']] = $row['short_name'] . ' (' . $row['title'] . ')';
	}
echo '<h2>Etat d\'avancement de la saisie des notes</h2>';

// chercher le nom de la MP
$marking_period = UserMP();
if	( $marking_period )
	{
	$sqlrequest = 'SELECT title FROM school_marking_periods WHERE marking_period_id=' . $marking_period;
	$result = db_query( $sqlrequest, true );
	if	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		$MP_name = $row['title'];
	else	$MP_name = '[' . $marking_period . ']';
	}
else $MP_name = '[?]';
echo "<p>$MP_name</p>";
echo '<p><i><b>Colonne de droite :</b> nombre de notes attribuées / effectif de la classe</i></p>';

echo '<table class="lp">';
// boucle des classes
foreach	( $class_names as $lp_classe => $lp_class_name )
	{
	// liste des eleves inscrits dans cette classe
	$my_students = array();
	$sqlrequest = 'SELECT student_id, drop_code FROM student_enrollment WHERE grade_id=' . $lp_classe . ' AND syear=' . UserSyear(); 
	$result = db_query( $sqlrequest, true );
	while	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		{
		if	( !$row['drop_code'] )
			$my_students[] = (int)$row['student_id'];
		}
	$effectif = count($my_students);
	if	( ( $effectif > 0 ) && ( $lp_class_name[0] != '_' ) )
		{
		echo '<tr><td colspan="3" class="cn">', $lp_class_name, '</td></tr>';
		// preparer la liste des disciplines (basee sur le premier eleve)
		$activites = array();	//	array pour recevoir le set (key = course-period-id, val = true) 
		LP_prog_1eleve( $my_students[0], $activites );
		$nada = NULL; $my_prof = '';
		$sqlrequest = 'SELECT title, short_name FROM course_periods WHERE course_period_id = ';
		// boucle des disciplines
		foreach	( $activites as $lp_cours => $v )
			{
			$result = db_query( $sqlrequest . $lp_cours, true );
			if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
				{
				$lp_course_name = $row['short_name'];
				LP_split_course_period( $row['title'], $nada, $nada, $my_prof );
				}
			else	$lp_course_name = $k;
			// preparer boucle des eleves
			$note_count = 0;
			$sqlrequest2 = 'SELECT grade_letter FROM student_report_card_grades WHERE syear=' . UserSyear()
					. ' AND course_period_id=' . $lp_cours
					. " AND marking_period_id='" . UserMP() . "'"
					. ' AND student_id='; 
			foreach	( $my_students as $elem )
				{
				$result = db_query( $sqlrequest2 . $elem, true );
				//echo '<tr><td colspan="2">', $sqlrequest2 . $elem, '</td><td>';
				if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
					{
					//echo $row['grade_letter'], '</td></tr>';
					if	( strlen( $row['grade_letter'] ) > 0 )
						$note_count++;
					}
				}
			echo "<tr><td>$lp_course_name</td><td>$my_prof</td><td class=\"ari\">",
				($note_count)?($note_count.' / '.$effectif):(''), '</td></tr>';
			} // boucle des disciplines
		}
	} // boucle des classes
	echo '</table>';



/* methode rudimentaire : scan de student_report_card_grades, peut voir cours inexistants
   ou eleves disparus *
// liste des cours ayant des notes
$set = array();
$sqlrequest = 'SELECT course_period_id FROM student_report_card_grades';
$result = db_query( $sqlrequest, true );
while	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
	{
	$k = $row['course_period_id'];
	if	( isset($set[$k]) )
		$set[$k] += 1;
	else	$set[$k] = 1;
	}

echo '<table>';
$nada = NULL; $my_prof = '';
$sqlrequest = 'SELECT title, short_name FROM course_periods WHERE course_period_id = ';
foreach	( $set as $k => $v )
	{
	$result = db_query( $sqlrequest . $k, true );
	if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		{
		$lp_course_name = $row['short_name'];
		LP_split_course_period( $row['title'], $nada, $nada, $my_prof );
		}
	else	$lp_course_name = $k;
	echo "<tr><td>$lp_course_name</td><td>$my_prof</td><td>$v</td></tr>";
	}
echo '</table>';
//*/