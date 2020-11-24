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

// lire le set d'activites pour un eleve
//	$activites = array pour recevoir le set (key = course-period-id, val = true) 
function LP_prog_1eleve( $my_student, &$activites )
{
if	( is_array($activites) )
	{
	$sqlrequest = 'SELECT course_period_id FROM schedule WHERE student_id=' . $my_student;
	$result = db_query( $sqlrequest, true );
	while	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		{
		$activites[(int)$row['course_period_id']] = true;
		}
	}
} 

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
