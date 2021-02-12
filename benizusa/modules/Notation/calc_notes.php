<?php
//require_once( 'modules/Loginpro/LP_func.php' );

// toutes les lecture de DB et calculs pour produire les bulletins trimestriels d'une classe
// ou autres vues
// le choix du trimestre est determine selon UserMP()

	// 0. conventions
	// istu = student_id de l'eleve courant
	// idi = course_period_id de la discipline courante
	// isub = subject_id  du subject courant (groupe de matieres)
	// ieva = marking_period_id de l'evaluation courante

	// arrays de l'objet "trimestre d'une classe"
	$my_students = array();		// array des istu (index arbitraire)
	$noms_complets = array();	// noms des eleves, eventuellement tries: istu => nom
	$dates_naissance = array();	// istu => date
	$numtels = array();		// istu => numero de tel du pere ou de la mere
	$sexes = array();		// istu => 'M' ou 'F'
	$statuts = array();		// R pour redoublant: istu => statut
	$class_name = '';		// nom de la classe
	$class_short_name = '';		// petit nom de la classe
	$activites = array();		// le set des disciplines, index=idi, val=short_name 
	$subjects_activities = array();	// le set des subsets de disciplines par subjects, index=isub
					// chaque subset est un array de idi (index arbitraire)
	$subject_names = array();	// les noms des subsets, index=isub
	$course_names = array();	// noms des disciplines, indexes par idi
	$prof_names = array();		// noms des profs, indexes par idi
	$competences = array();		// textes de competences, convertis en html, indexes par idi
	$coeffs = array();		// coeffs des disciplines, indexes par idi
	$evals = array();		// set des evals du trimestre (ieval => eval_name)
	$notesESD = array();		// array 3D de toutes les notes de la classe : $notesESD[ieva][istu][idi]
	$notesSD = array();		// array 2D de toutes les moyennes eval1,eval2 : $notesSD[istu][idi]
	$notesDS = array();		// array redondant pour calculer les classements : $notesDS[idi][istu]
	$rangsSD = array();		// array 2D du rang de chaque note dans sa discipline : $rangsSD[istu][idi]
	$moyD = array();		// moyenne par matiere : $moyD[idi]
	$minD = array();		// min par matiere : $minD[idi]
	$maxD = array();		// max par matiere : $maxD[idi]
	$totNxCS = array();		// total des produits NxC par eleve $totNxCS[istu]
	$totCoeffS = array();		// total des coeffs par eleve $totCoeffS[istu]
	$moyS = array();		// moyenne ponderee par eleve $moyS[istu]
	$rangsS = array();		// classement général $rangsS [istu]
	$anjS = array();		// absences non justifiees
	// les variables scalaires de l'objet "trimestre d'une classe" ne sont pas regroupees ici :-(

	// 1. acquerir les donnees communes
	$lp_classe = (int)$_REQUEST['lp_classe'];	// petit filtrage de securite
	// prof principal
	$sqlrequest = "SELECT value FROM program_user_config WHERE program='ProfPrincip' AND user_id='-1'"
		. ' AND school_id=' . UserSchool() . " AND title='" . $lp_classe . "'";		
	// echo '<p>', $sqlrequest, '</p>';
	$result = db_query( $sqlrequest, true );
	if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		{
		$prof_id = $row['value'];
		$sqlrequest = 'SELECT title, first_name, last_name FROM staff'
			. ' WHERE staff_id=' . $prof_id;
		$result = db_query( $sqlrequest, true );
		if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
			$prof_principal = $row['title'] . ' ' . $row['first_name'] . ' ' . $row['last_name'];
		else	$prof_principal = '';
		}
	else	$prof_principal = '';
	// 1.1 la liste des eleves (remplir $my_students et $my_redoub)
	$my_redoub = array();
	LP_liste_classe( $lp_classe, $class_name, $class_short_name, $my_students, $my_redoub );
	if	( !$class_name )
		exit( "<p>Classe $lp_classe inconnue</p>" ); 
	if	( count($my_students) == 0 )
		exit( "<p>Classe $class_name n'a pas d'élèves</p>" );
	// il faut acquerir les noms maintenant pour pouvoir trier les eleves par ordre alphabetique 
	$sqlrequest = 'SELECT first_name, middle_name, last_name, custom_200000000, custom_200000004, '
		. 'custom_200000020, custom_200000025, custom_200000028 FROM students WHERE student_id=';
	foreach	( $my_students as $k => $v )
		{
		$result = db_query( $sqlrequest . $v, true );
		if	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
			{
			$noms_complets[$v] = $row['last_name'] . ' ' . $row['first_name'] . ' ' . $row['middle_name'];
			$dates_naissance[$v] = $row['custom_200000004'];
			if	( $row['custom_200000000'][0] == 'F') { $sexes[$v] = 'F'; $cntF++; }
			else if	( $row['custom_200000000'][0] == 'M') { $sexes[$v] = 'G'; $cntG++; }
			else	$sexes[$v] = ' ';
			$tel = $row['custom_200000020'];
			if	( !$tel )
				$tel = $row['custom_200000025'];
			if	( !$tel )
				$tel = $row['custom_200000028'];
			$numtels[$v] = $tel;
			if	( $my_redoub[$k] == 0 )
				$statuts[$v] = 'R';
			else	$statuts[$v] = 'N';
			}
		else	$noms_complets[$v] = 'ERREUR 766';
		}
	// trier par ordre alphabetique des noms
	natcasesort( $noms_complets );

	// 1.2 les evaluations
	$trim_name = '';
	$trimestre = LP_find_trimestre( UserMP(), $trim_name );
	LP_find_evals( $trimestre, $evals );
	$trim_num = array();
	if	( preg_match('/(\d+)/', $trim_name, $trim_num ) )
		$trim_num = (int)$trim_num[1];
	else	$trim_num = 0;

	// 1.3 le set des cours
	LP_prog_1eleve( $my_students[0], $activites );
	LP_split_by_subject( $activites, $subjects_activities, $subject_names );
		/* echo '<pre>'; var_dump( $subjects_activities ); echo '</pre><hr>'; */
		/* echo '<pre>'; var_dump( $subject_names ); echo '</pre>'; */

	// 1.4 les data des cours, indexes par course_id
	$nada = NULL;
	$sqlrequest  = 'SELECT title, credits FROM course_periods WHERE course_period_id=';
	$sqlrequest2 = "SELECT value FROM program_user_config WHERE program='Competence' AND user_id='-1'"
			. ' AND school_id=' . UserSchool() . " AND title='";
	foreach	( $activites as $idi => $v )
		{
		// nom du cours, prof, coeff
		$result = db_query( $sqlrequest . $idi, true );
		if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
			{
			$course_names[$idi] = ''; $prof_names[$idi] = '';
			LP_split_course_period( $row['title'], $nada, $course_names[$idi], $prof_names[$idi] );
			$coeffs[$idi] = $row['credits'];
			}
		// competence
		$comp_title = $trimestre . '_' . $idi;
		$result = db_query( $sqlrequest2 . $comp_title . "'", true );
		// echo '<p>', $sqlrequest2 . $comp_title . "'", '</p>';
		if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
			$competences[$idi] = nl2br( htmlspecialchars( $row['value'], ENT_HTML5 ) );
		else	$competences[$idi] = '<br><br>';
		}

	// 2. acquerir toutes les notes brutes : notesESD[ieva][istu][idi]
	// Préparons la lecture des notes de chaque eleve pour la periode courante
	$sqlrequ0 = 'SELECT grade_letter, course_period_id FROM student_report_card_grades WHERE syear='
		. UserSyear() . ' AND marking_period_id=\'';	// a completer dans le 2 foreaches
	foreach	( $evals as $ieva => $eval_name )
		{
		$sqlrequest = $sqlrequ0 . $ieva . '\' AND student_id=';		// a completer dans le foreach
		foreach	( $my_students as $istu )
			{
			$tmpnotesD = &$notesESD[$ieva][$istu];	// reference sur notes de cet eleve par discipline
			$tmpnotesD = array();
			$result = db_query( $sqlrequest . $istu, true );
			while	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
				{
				$tmp_note = $row['grade_letter'];
				if	( is_numeric( $tmp_note ) )
					$tmp_note = (float)$tmp_note;
				else	$tmp_note = -100.0;
				$idi = $row['course_period_id'];
				if	( isset( $activites[$idi] ) )		// ignorer note dans matiere non repertoriee
					{
					// $notesESD[$ieva][$istu][$idi] = $tmp_note;
					$tmpnotesD[$idi] = $tmp_note;		// idem mais plus efficace
					}
				}
			// completer les notes manquantes
			foreach	( $activites as $idi => $v )
				if	( !isset( $tmpnotesD[$idi] ) )
					{
					// $notesESD[$ieva][$istu][$idi] = -200.0;
					$tmpnotesD[$idi] =  -200.0;		// idem mais plus efficace
					}
			}
		}
	// echo '<pre>'; var_dump($notesESD); echo '</pre>'; 

	// 3. calculer les moyennes des evals du trimestre -> $notesSD[istu][idi]
	// (N.B. jusqu'a ce point le code supportait un nombre arbitraire d'evals par trimestre, maintenant c'est 2)
	if	( count( $evals ) != 2 )
		{
		echo '<pre>'; var_dump($evals); echo'</pre>';
		exit('<p>ERREUR : ce prog exige exactement 2 evaluations par trimestre</p>');
		}
	foreach	( $evals as $ieva => $eval_name )
		{
		if	( isset( $ieva_1 ) )
			$ieva_2 = $ieva;
		else	$ieva_1 = $ieva;
		}
	$first = true;
	foreach	( $my_students as $istu )
		{
		$ptrnotes1D = &$notesESD[$ieva_1][$istu];	// references sur notes de cet eleve par discipline
		$ptrnotes2D = &$notesESD[$ieva_2][$istu];
		$ptrmoyD = &$notesSD[$istu];
		$ptrmoyD = array();
		foreach	( $activites as $idi => $v )
			{
			if	( $ptrnotes1D[$idi] < 0.0 )
				$tmpmoy = $ptrnotes2D[$idi];
			else if	( $ptrnotes2D[$idi] < 0.0 )
				$tmpmoy = $ptrnotes1D[$idi];
			else	$tmpmoy = round( ( $ptrnotes1D[$idi] + $ptrnotes2D[$idi] ) / 2.0, 2 );	// 2 digits
			$ptrmoyD[$idi] = $tmpmoy;
			// array redondant pour calculer les classements
			if	( $first )
				$notesDS[$idi] = array();
			$notesDS[$idi][$istu] = $tmpmoy;
			}
		$first = false;
		$rangsSD[$istu] = array();
		}

	// 4. calculer les rangs et moyennes par matiere
	foreach	( $activites as $idi => $v )
		{
		arsort( $notesDS[$idi] );
		$cnt = 1; $prev_rang = 1; $prev_note = 100.0;
		$moy = 0.0; $min = 100.0; $max = 0.0;
		foreach	( $notesDS[$idi] as $istu => $v )
			{
			if	( $v >= 0.0 )
				{
				// rangs
				if	( $v == $prev_note )
					$rang = $prev_rang;
				else	$rang = $cnt;
				$rangsSD[$istu][$idi] = $rang;
				$prev_note = $v; $prev_rang = $rang;
				$cnt++;
				// moyenne, min, max
				$moy += $v;
				if	( $v > $max ) $max = $v;
				if	( $v < $min ) $min = $v;
				}
			}
		$cnt--;
		if	( $cnt > 0 )
			$moy /= $cnt;
		$moyD[$idi] = round( $moy, 2 );
		$minD[$idi] = $min;
		$maxD[$idi] = $max;
		}
	// echo '<pre>'; var_dump( $notesDS ); echo '</pre>';
	// echo '<pre>'; var_dump( $rangsSD ); echo '</pre>';

	// 5. calculer la moyenne generale de chaque eleve, garder les sommes intermediaires
	foreach	( $my_students as $istu )
		{
		$ptrmoyD = &$notesSD[$istu];
		$totNxC = 0.0;
		$totCoeff = 0.0;
		foreach	( $activites as $idi => $v )
			{
			$note = $ptrmoyD[$idi];
			if	( $note >= 0.0 )
				{
				$totCoeff += $coeffs[$idi];
				$totNxC += $note * $coeffs[$idi];
				}
			}
		$totNxCS[$istu] = $totNxC;
		$totCoeffS[$istu] = $totCoeff;
		if	( $totCoeff > 0 )
			$moyS[$istu] = round( $totNxC / $totCoeff, 2 );
		else	$moyS[$istu] = -100.0;
		}
	// 6. calculer le classement général
	arsort( $moyS );
	$cnt = 1; $prev_rang = 1; $prev_note = 100.0;
	$class_moy = 0.0; $class_min = 100.0; $class_max = 0.0;
	foreach	( $moyS as $istu => $v )
		{
		if	( $v >= 0.0 )
			{
			// rangs
			if	( $v == $prev_note )
				$rang = $prev_rang;
			else	$rang = $cnt;
			$rangsS[$istu] = $rang;
			$prev_note = $v; $prev_rang = $rang;
			$cnt++;
			// moyenne, min, max
			$class_moy += $v;
			if	( $v > $class_max ) $class_max = $v;
			if	( $v < $class_min ) $class_min = $v;
			}
		}
	$effectif = $cnt - 1;
	if	( $effectif > 0 )
		$class_moy = round( $class_moy / $effectif, 2 );

	// 7. lire les heures d'absence non justifiées pour la periode courante
	// Préparons la lecture 
	$sqlrequest = 'SELECT comment FROM student_mp_comments WHERE syear=' . UserSyear()
		. ' AND marking_period_id=\'' . $trimestre
		. '\' AND student_id=';		// a completer dans le foreach
	// remplir les tableaux
	foreach	( $my_students as $istu )
		{
		// lecture table student_mp_comments (requete preparee ci-dessus)
		$result = db_query( $sqlrequest . $istu, true );
		if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
			{
			$tmp_comment = $row['comment'];
			// extraction des heures d'abs non justifiee
			$anjS[$istu] = (int)substr( $tmp_comment, 3 );
			}
		else	$anjS[$istu] = 0;
		}

// the end
