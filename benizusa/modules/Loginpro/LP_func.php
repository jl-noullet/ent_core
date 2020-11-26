<?php
/**
 fonctions communes
 */

// obtenir les infos d'un élève (mettre 1 string vide aux elements desires, sinon NULL)
function LP_info_eleve( $student_id, &$last_name, &$first_name, &$middle_name=NULL, &$date_naissance=NULL )
{
$sqlrequest = 'SELECT first_name, middle_name, last_name, custom_200000004 FROM students WHERE student_id=' . $student_id;
$result = db_query( $sqlrequest, true );
if	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
	{
	$last_name = $row['last_name'];
	$first_name = $row['first_name'];
	if	( is_string( $middle_name ) )
		$middle_name = $row['middle_name'];
	if	( is_string( $date_naissance ) )
		$date_naissance = $row['custom_200000004'];
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

/* ecrire le set d'activites pour un eleve (efface l'ancien) selon UserSchool() et UserSyear()
//	$activites = array contenant le set (key = course-period-id)
//	N.B. course_id (categorie de cours) est un truc obligatoire (mais redondant) pour table schedule...
// 2 inconvenients:
//	il faut mettre une start date (ici on a mis une constante)
//	il faut mettre course_id (categorie de cours) mais c'est redondant (il faudrait le lire dans table course_period)
//	mettre une valeur arbitrire peut avoir des effets peu previsibles
function LP_set_prog_1eleve( $my_student, &$activites )
{
// effacer tout ce qui concerne cet élève dans l'année courante
$sqlrequest = 'DELETE FROM schedule WHERE student_id=' . $my_student . ' AND syear=' . UserSyear();
// echo '<p>', $sqlrequest, '</p>';
$result = db_query( $sqlrequest, true );
foreach	( $activites as $k => $v )
	{
	$sqlrequest = 'INSERT INTO schedule ( student_id, syear, school_id, course_period_id, course_id, start_date ) VALUES ' .
	      '(' . $my_student . ',' . UserSyear() . ',' .  UserSchool() . ',' .  (int)$k . ',' . '7' . ',' . "'2020-10-05'". ')'; 
	// echo '<p>', $sqlrequest, '</p>';
	$result = db_query( $sqlrequest, true );
	}
}

// reprogrammer une classe entiere avec les cours d'un eleve de ref. (il peut etre dans la classe ou non)
function LP_set_prog_1classe( $target_class, $ref_student )
{
$activites = array();
LP_prog_1eleve( $ref_student, $activites );
$my_students = array(); $my_null = NULL;
LP_liste_classe( $target_class, $my_null, $my_null, $my_students );
foreach	( $my_students as $elem )
	{
	if	( $elem != $ref_student )
		LP_set_prog_1eleve( $elem, $activites );
		// LP_copy_prog_1eleve( $elem, $ref_student, $activites );
	}
}

*/

/* utiliser INSERT INTO ... SELECT pour copier une row peut marcher, mais pas toujours, ici echec 
function LP_copy_prog_1eleve( $my_student, $ref_student, &$activites )
{
// effacer tout ce qui concerne cet élève dans l'année courante
$sqlrequest = 'DELETE FROM schedule WHERE student_id=' . $my_student . ' AND syear=' . UserSyear();
// echo '<p>', $sqlrequest, '</p>';
$result = db_query( $sqlrequest, true );
foreach	( $activites as $k => $v )
	{
	$sqlrequest = 'INSERT INTO schedule ( student_id, syear, school_id, course_period_id, course_id, start_date ) ' .
		'SELECT ' . $my_student . ', syear, school_id, course_period_id, course_id, start_date ' .
		'FROM schedule WHERE student_id=' . $ref_student . ' AND syear=' . UserSyear() . ' AND course_period_id=' . (int)$k; 
	 echo '<p>', $sqlrequest, '</p>';
	// $result = db_query( $sqlrequest, true );
	}
}
*/

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
			echo '<p>', $sqlrequest, '</p>';
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
