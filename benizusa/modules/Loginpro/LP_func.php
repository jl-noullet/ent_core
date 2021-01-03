<?php
/**
 fonctions communes
 */

// obtenir les infos d'un élève (mettre 1 string vide aux elements desires, sinon NULL)
function LP_info_eleve( $student_id, &$last_name, &$first_name=NULL, &$middle_name=NULL, &$date_naissance=NULL )
{
$sqlrequest = 'SELECT last_name';
if	( is_string( $first_name ) )
	{
	$sqlrequest .= ', first_name';
	if	( is_string( $middle_name ) )
		{
		$sqlrequest .=	', middle_name';
		if	( is_string( $date_naissance ) )
			{
			$sqlrequest .= ', custom_200000004';
			}
		}
	}
$sqlrequest .= ' FROM students WHERE student_id=' . $student_id;
$result = db_query( $sqlrequest, true );
if	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
	{
	$last_name = $row['last_name'];
	if	( is_string( $first_name ) )
		{
		$first_name = $row['first_name'];
		if	( is_string( $middle_name ) )
			{
			$middle_name = $row['middle_name'];
			if	( is_string( $date_naissance ) )
				$date_naissance = $row['custom_200000004'];
			}
		}
	}
}

// separer les champs du title d'une course_period
//	donner un array (optionnel) pour les tranches horaires
//	donner 2 strings (optionnelles) pour nom court et prof
function LP_split_course_period( $title, &$tranches, &$nom_court=NULL, &$prof=NULL )
{
$splitted = explode( ' - ', $title );
// echo '<pre>'; var_dump( $splitted ); echo '</pre><hr>';
$cnt = count( $splitted );
if	( ( is_string( $prof ) ) && ( $cnt > 0 ) )
	$prof = $splitted[$cnt-1];
if	( ( is_string( $nom_court ) ) && ( $cnt > 1 ) )
	$nom_court = $splitted[$cnt-2];
if	( ( is_array( $tranches ) ) && ( $cnt > 2 ) )
	$tranches = array_slice( $splitted, 0, $cnt-2 ); 
}

// trier les course_periods dont les IDs sont les keys du set $activites
// accessoirement les short_names sont injectes comme valeurs dans $activites
function LP_sort_course_set( &$activites )
{
if	( !is_array( $activites ) )
	return;
if	( !count( $activites ) )
	return;
// partie commune de la requete SQL, qui va etre comletee avec $k dans le foreach
$sqlrequest = 'SELECT short_name FROM course_periods WHERE course_period_id = ';
foreach	( $activites as $k => $v)
	{
	$result = db_query( $sqlrequest . $k, true );
	if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		$activites[$k] = $row['short_name'];
	}
// trier par ordre alphabetique des noms
natcasesort( $activites );
}

// afficher une table de course_periods dont les IDs sont les keys du set $activites
// accessoirement les short_names sont injectes comme valeurs dans $activites
function LP_display_course_set( &$activites, $show_prof, $show_times, $url_kill=NULL )
{
if	( !is_array( $activites ) )
	return;
if	( !count( $activites ) )
	return;
LP_sort_course_set( $activites );
echo '<table class="lp">';
// partie commune de la requete SQL, qui va etre comletee avec $k dans le foreach
$sqlrequest = 'SELECT ';
if	( $show_times )
	$sqlrequest .= 'title, ';
$sqlrequest .= 'short_name, ';
if	( $show_prof )
	$sqlrequest .= 'teacher_id, ';
$sqlrequest .= 'credits FROM course_periods WHERE course_period_id = ';
foreach	( $activites as $k => $v)
	{
	$result = db_query( $sqlrequest . $k, true );
	if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		{
		echo '<tr><td>', $row['short_name'], '</td>';
		$activites[$k] = $row['short_name'];	// service annexe : injection de short_name dans $activites
		if	( $show_prof )
			{
			$sqlrequest2 = 'SELECT title, first_name, last_name FROM staff WHERE staff_id=' . $row['teacher_id'];
			$result2 = db_query( $sqlrequest2, true );
			if	( $row2 = pg_fetch_array( $result2, null, PGSQL_ASSOC ) )
				echo '<td>', $row2['title'], ' ', $row2['first_name'], ' ', $row2['last_name'], '</td>';
			else	echo '<td>prof inconnu</td>';
			}
		echo '<td>', $row['credits'], '</td>';
		if	( $show_times )
			{		// exemple de title: "10h30 - 12h20 Jeu. - 12h50 - 14h40 Lun. - PCT 4eme ALL & ESP - Corine NGOMA"
			$times = array();	// exemple de $times: |10h30|12h20 Jeu.|12h50|14h40 Lun.|
			LP_split_course_period( $row['title'], $times );
			echo '<td>';
			//$tcnt = count( $times );
			//if	( tcnt )
			//echo $time[0];
			//foreach	( $i = 1; $i < $tcnt; $i++ )
			$old_elem = '';
			foreach	( $times as $elem )  
				{
				if	( $old_elem )
					{	// separateur
					if	( strlen($old_elem) >= 7 )
						echo ' | ';
					else	echo '-';
					}
				echo $elem;
				$old_elem = $elem;
				}
			echo '</td>';
			}
		if	( $url_kill )
			{
			echo '<td><a href="', $url_kill, $k, '">Retirer ce cours</a></td>';
			}
		echo '</tr>'; 
		}
	}
echo '</table>';
}

// obtenir les 2 noms de la classe,
//	$lp_classe = clef dans school_gradelevels
//	$title = string pour recevoir le nom de la classe
//	$short_name = string pour recevoir le nom court (opt)
function LP_nom_classe( $lp_classe, &$title, &$short_name=NULL )
{
if	( is_string($title) )
	{
	$sqlrequest = 'SELECT short_name, title FROM school_gradelevels WHERE id=' . $lp_classe;
	$result = db_query( $sqlrequest, true );
	if	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		{
		$title = $row['title'];
		if	( is_string( $short_name ) )
			$short_name= $row['short-name'];
		}
	else	{
		$title = NULL; return;
		}
	}
}

// obtenir sur option les 2 noms de la classe,
// obtenir les id des eleves d'une classe, et sur option leur code de redoublement
//	$lp_classe = clef dans school_gradelevels
//	$title = string pour recevoir le nom de la classe, ou NULL
//	$short_name = string pour recevoir le nom court
// 	deux arrays tous indexes par le meme index arbitraire
//	$my_students = array pour recevoir les id
//	$my_redoub = array optionnel indiquant le code de redoublement aka next-school == 0
// note: redoublement indique par next_school dans table student_enrollment
// echec silencieusement signale par array vide ou title vide
function LP_liste_classe( $lp_classe, &$title, &$short_name, &$my_students, &$my_redoub=NULL )
{
if	( is_string($title) )
	{
	// chercher nom de la classe
	$sqlrequest = 'SELECT short_name, title FROM school_gradelevels WHERE id=' . $lp_classe;
	$result = db_query( $sqlrequest, true );
	if	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		{
		$title = $row['title']; $short_name = $row['short-name'];
		}
	else	{
		$title = NULL; return;
		}
	}
// identifier les eleves inscrits dans cette classe
$sqlrequest = 'SELECT student_id, drop_code, next_school FROM student_enrollment WHERE grade_id=' . $lp_classe .
	      ' AND syear=' . UserSyear(); 
// echo $sqlrequest;
$result = db_query( $sqlrequest, true );
while	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
	{
	// echo '<pre>'; var_dump( $row ); echo '</pre>';
	if	( !$row['drop_code'] )
		{
		if	( is_array($my_students) ) $my_students[] = (int)$row['student_id'];
		if	( is_array($my_redoub)   ) $my_redoub[] = (int)$row['next_school'];
		}
	}
}

// lire le set d'activites pour un eleve ( selon UserSyear() )
//	$activites = array pour recevoir le set (key = course-period-id, val = true) 
function LP_prog_1eleve( $my_student, &$activites )
{
if	( is_array($activites) )
	{
	$sqlrequest = 'SELECT course_period_id FROM schedule WHERE student_id=' . $my_student . ' AND syear=' . UserSyear();
	$result = db_query( $sqlrequest, true );
	while	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		{
		$activites[(int)$row['course_period_id']] = true;
		}
	}
} 

// splitter un set d'activites en subjects
//	$subjects_activities : array pour recevoir env. 3 subsets de $activites, un par subject
//	$subject_names : array pour recevoir les subject names, dans le meme ordre
function LP_split_by_subject( &$activites, &$subsets, &$subject_names )
{
// d'abord liste des subjects
$sqlrequest = 'SELECT subject_id, title FROM course_subjects WHERE school_id=' . UserSchool()
		. ' AND syear=' . UserSyear() . ' ORDER BY sort_order';
$result = db_query( $sqlrequest, true );
while	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
	{ $subject_names[$row['subject_id']] = $row['title']; $subsets[$row['subject_id']] = array(); }
// puis copier la table courses, le niveau intermediaire
$subjects_of_courses = array();
$sqlrequest = 'SELECT course_id, subject_id FROM courses WHERE school_id=' . UserSchool() . ' AND syear=' . UserSyear();
$result = db_query( $sqlrequest, true );
while	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
	$subjects_of_courses[$row['course_id']] = $row['subject_id'];
// boucler sur les cours de cette classe en garnissant les subsets
$sqlrequest = 'SELECT course_id FROM course_periods WHERE course_period_id=';
foreach	( $activites as $k => $v )
	{
	$result = db_query( $sqlrequest . $k, true );
	if	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		{
		$cou = $row['course_id'];
		$sub = $subjects_of_courses[$cou];
		if	( is_array( $subsets[$sub] ) )
			$subsets[$sub][] = $k;
		}
	}
}

// trouver le trimestre contenant marking_period (qui peut etre une eval ou le trimestre lui-meme)
// rendre son id et mettre a jour son nom
function LP_find_trimestre( $marking_period, &$MP_name )
{
$sqlrequest = 'SELECT title, mp, parent_id FROM school_marking_periods WHERE marking_period_id=' . $marking_period;
$result = db_query( $sqlrequest, true );
$MP_name = '';
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
return $marking_period;
}

// trouver toutes les evals contenues dans un trimestre
function LP_find_evals( $trimestre, &$evals )
{
$sqlrequest = 'SELECT marking_period_id, short_name FROM school_marking_periods WHERE parent_id=' . $trimestre;
$result = db_query( $sqlrequest, true );
while	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
	{
	$ieval = $row['marking_period_id'];
	$evals[$ieval] = $row['short_name'];
	}
natcasesort( $evals );
}

// reprogrammer une classe entiere avec les cours d'un eleve de ref. (il peut etre dans la classe ou non)
function LP_reprog_1classe( $target_class, $ref_student )
{
$colonnes = 'syear,school_id,student_id,start_date,course_id,course_period_id,mp,marking_period_id';
// extraire les lignes des cours de ref.
$full_rows = array();		// array de rows indexe par course_period_id
$sqlrequest = 'SELECT ' . $colonnes . ' FROM schedule WHERE student_id=' . $ref_student . ' AND syear=' . UserSyear();
$result = db_query( $sqlrequest, true );
while	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
	{
	$full_rows[(int)$row['course_period_id']] = $row;
	}
// echo '<pre>dump de full_rows\n"'; var_dump( $full_rows ); echo '</pre><hr>';
if	( count( full_rows ) == 0 )
	return;
// acquerir la liste des eleves
$my_students = array(); $my_null = NULL;
LP_liste_classe( $target_class, $my_null, $my_null, $my_students );
// effectuer la mise a jour pour chaque eleve
foreach	( $my_students as $elem )
	{
	if	( $elem != $ref_student )
		{
		// effacer tout ce qui concerne cet élève dans l'année courante
		$sqlrequest = 'DELETE FROM schedule WHERE student_id=' . $elem . ' AND syear=' . UserSyear();
		$result = db_query( $sqlrequest, true );
		// inserer les nouvelles lignes
		foreach	( $full_rows as $k => $v )
			{
			// echo "<pre>dump de v\n"; var_dump( $v ); echo '</pre><hr>';
			$colonnes = array_keys( $v );	// on reprend les noms de colonnes car l'ordre a pu changer
			$values = array_values( $v );
			$ncol = count( $colonnes );
			// inserer la liste des colonnes dans la requete SQL
			$sqlrequest = 'INSERT INTO schedule (';
			for	( $i = 0; $i < $ncol; $i++ )
				{
				$sqlrequest .= $colonnes[$i]; $sqlrequest .= ',';
				if	( $colonnes[$i] == 'student_id' )
					$isi = $i;
				}
			$sqlrequest = rtrim( $sqlrequest, ',' );
			$sqlrequest .= ') VALUES (';
			// et la liste des valeurs
			for	( $i = 0; $i < $ncol; $i++ )
				{
				$sqlrequest .= "'";
				if	( $i == $isi )
					$sqlrequest .= $elem;
				else	$sqlrequest .= $values[$i];
				$sqlrequest .= "',";
				}
			$sqlrequest = rtrim( $sqlrequest, ',' );
			$sqlrequest .= ')';
			// echo '<p>', $sqlrequest, '</p>';
			$result = db_query( $sqlrequest, true );
			}
		}
	}
}

/* snippet de rosario Schedule.php et MassSchedule.php :

"INSERT INTO SCHEDULE (SYEAR,SCHOOL_ID,STUDENT_ID,START_DATE,COURSE_ID,COURSE_PERIOD_ID,MP,MARKING_PERIOD_ID) VALUES('"
 . UserSyear() . "','"
 . UserSchool() . "','"
 . UserStudentID() . "','"
 . $date . "','"
 . $_REQUEST['course_id'] . "','"
 . $_REQUEST['course_period_id'] . "','"
 . $mp_RET[1]['MP'] . "','"
 . $mp_RET[1]['MARKING_PERIOD_ID'] . "')" );

"INSERT INTO SCHEDULE (SYEAR,SCHOOL_ID,STUDENT_ID,COURSE_ID,COURSE_PERIOD_ID,MP,MARKING_PERIOD_ID,START_DATE) values('"
 . UserSyear() . "','"
 . UserSchool() . "','"
 . $student_id . "','"
 . $course_to_add['course_id'] . "','"
 . $course_to_add['course_period_id'] . "','"
 . $mp_table . "','" . $_REQUEST['marking_period_id'] . "','"
 . $start_date . "')"

la question de date
$min_date = DBGetOne( "SELECT min(SCHOOL_DATE) AS MIN_DATE
	FROM ATTENDANCE_CALENDAR
	WHERE SYEAR='" . UserSyear() . "'
	AND SCHOOL_ID='" . UserSchool() . "'" );

$date = DBDate();
if	( $min_date && $date < $min_date )
	{
	$date = $min_date;
	}

*/

  
// comparer deux sets (i.e. les ensemble de clefs de 2 arrays) (les valeurs sont ignorees)
// hyp. restrictive : $set est inclus dans $ref_set
// resultat en string, $elem sert a formuler le message d'erreur
function LP_compare_sets_inc( &$set, &$ref_set, $elem='item' )
{
$cnt1 = count( $set ); $cnt2 = count( $ref_set );
if	( $cnt1 == $cnt2 )
	return '';
$retval = "$cnt1 $elem au lieu de $cnt2 $elem";
foreach	( $ref_set as $k => $v )
	{
	if	( !array_key_exists( $k, $set ) )
		$retval .= ", $elem $k manquant";
	}
return $retval;
}

// appreciation textuelle
$LP_level_texts = [ 
	0 => 'Compétence non acquise (NA)',
	1 => 'En cours d\'acquisition (ECA)',
	2 => 'Compétence acquise (CA)',
	3 => 'Expert (E)'
	];
$LP_level_colors = [ 
	0 => '#F44',
	1 => '#FB0',
	2 => '#0E0',
	3 => '#08F'
	];

function LP_note2level( $note )
{
if	( $note >= 14.0 ) return 3;
else if	( $note >= 12.0 ) return 2;
else if	( $note >= 10.0 ) return 1;
else if	( $note >= 0.0 )  return 0;
else	return -1;
}

// convertir un array PHP en JS, du '[' ou ']' inclus (strings)
// attention : utilise l'ordre de creation des elements, danger s'il est different de l'ordre des clefs
function LP_t_array_to_JS( $a )
{
$js = '[';
foreach	( $a as $v )
	$js .= "\"$v\",";
$js .= ']';
return $js;
}

// convertir un array PHP en JS, du '[' ou ']' inclus (numbers)
// attention : utilise l'ordre de creation des elements,  danger s'il est different de l'ordre des clefs
function LP_n_array_to_JS( $a )
{
$js = '[';
foreach	( $a as $v )
	$js .= "$v,";
$js .= ']';
return $js;
}
