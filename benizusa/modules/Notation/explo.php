<?php

require_once( 'modules/Loginpro/LP_func.php' );
$my_school = UserSchool();
$my_year = UserSyear();

function &note2apprec( $note )
{
if	( $note >= 14.0 ) return 'Expert (E)';
else if	( $note >= 12.0 ) return 'Compétence acquise (CA)';
else if	( $note >= 10.0 ) return 'En cours d\'acquisition (ECA)';
else if	( $note >= 0.0 )  return 'Compétence non acquise (NA)';
else	return '';
}

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
	echo '<form action="', $_SERVER['REQUEST_URI'], '" method="GET"> ';
	echo '<select name="lp_classe"> ';
	foreach	($class_names as $k => $v) {
		echo '<option value="', $k, '">', $v, '</option> ';
		}
	echo '</select><br>';
	echo '<button type="submit" class="button-primary"> Ok </button> </form>';
	}
else	{
	// 0. conventions
	// istu = student_id de l'eleve courant
	// idi = course_period_id de la discipline courante
	// isub = subject_id  du subject courant (groupe de matieres)
	// ieva = marking_period_id de l'evaluation courante
	$my_students = array();		// array des istu (index arbitraire)
	$noms_complets = array();	// noms des eleves, eventuellement tries: istu => nom
	$dates_naissance = array();	// istu => date
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

	// 1. acquerir les donnees communes
	$lp_classe = (int)$_REQUEST['lp_classe'];	// petit filtrage de securite

	// 1.1 la liste des eleves (remplir $my_students et $my_redoub)
	$my_redoub = array();
	LP_liste_classe( $lp_classe, $class_name, $class_short_name, $my_students, $my_redoub );
	if	( !$class_name )
		exit( "<p>Classe $lp_classe inconnue</p>" ); 
	if	( count($my_students) == 0 )
		exit( "<p>Classe $class_name n'a pas d'élèves</p>" );
	// il faut acquerir les noms maintenant pour pouvoir trier les eleves par ordre alphabetique 
	foreach	( $my_students as $k => $v )
		{
		$sqlrequest = 'SELECT first_name, middle_name, last_name, custom_200000000, custom_200000004 '
			. 'FROM students WHERE student_id=' . $v;
		$result = db_query( $sqlrequest, true );
		if	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
			{
			$noms_complets[$v] = $row['last_name'] . ' ' . $row['first_name'] . ' ' . $row['middle_name'];
			$dates_naissance[$v] = $row['custom_200000004'];
			//if	( $row['custom_200000000'][0] == 'F') { $sexes[$v] = 'F'; $cntF++; }
			//else if	( $row['custom_200000000'][0] == 'M') { $sexes[$v] = 'G'; $cntG++; }
			//else	$sexes[$v] = ' ';
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
		. $my_year . ' AND marking_period_id=\'';	// a completer dans le 2 foreaches
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
	$cnt--;
	if	( $cnt > 0 )
		$class_moy = round( $class_moy / $cnt, 2 );

	// 7. produire du HTML imprimable independant de l'eleve
	$css_subject_width = 300;	// param au pif pour textes verticaux...
	$html_css = '<style type="text/css">'
		. 'table.lp { border-collapse:collapse; font-family: \'Lato\', sans-serif; }'
		. 'table.lp td { border:1px solid black; padding: 2px 8px 2px 10px; }'
		. '.bo1 { font-weight: bold }'
		. '.bul { padding-bottom: 20px; page-break-before: always; }'
		. 'td.vv { position: relative; width: 25px; overflow: hidden  }'
		. 'div.vr { position: absolute; top: ' . $css_subject_width . 'px; left: 0;'
		. '-webkit-transform: rotate(-90deg); -ms-transform: rotate(-90deg); transform: rotate(-90deg);'
		. '-webkit-transform-origin: top left; -ms-transform-origin: top left; transform-origin: top left;'
		. 'width:  ' . $css_subject_width . 'px; text-align: right; }'
		. '</style>';
	// le header du bulletin
	$html0 = '<div class="bul"><h2>' . $class_name . '</h2>'
		. '<p>' . $trim_name . ' : [ ' . $evals[$ieva_1] . ' ' . $evals[$ieva_2] . ' ]</p>';
	// header de la table
	$htmlt = '<table class="lp">'
		. '<tr class="bo1"><td></td><td>Discipline</td><td>Compétence</td><td>EVAL<br>1</td><td>EVAL<br>2</td><td>Moy</td>'
		. '<td>Coef</td><td>N x C</td><td>Rang</td><td>Moy.<br>Classe</td><td>Min</td><td>Max</td><td>Appréciation</td></tr>';
	
	$html_stu = $html_css;
	// la boucle des eleves
	foreach	( $noms_complets as $istu => $nom )
		{
		$html_sid = "<p>Noms et prénoms: $nom<br>Né(e) le " . $dates_naissance[$istu] . " Matricule $istu</p>";
		$html_stu .= $html0 . $html_sid . $htmlt;
		// la boucle des subjects
		foreach	( $subject_names as $isub => $subject_name )
			{
			$rowspan = 1 + count( $subjects_activities[$isub] );
			$first = true;
			$totNxC = 0.0;
			$totCoeff = 0.0;
			// la boucle des cours du subject
			foreach	( $subjects_activities[$isub] as $idi )
				{
				$html_stu .= '<tr>';
				if	( $first )
					{
					$html_stu .= '<td rowspan="' . $rowspan	. '" class="vv"><div class="vr"><b>'
						. $subject_name . '&nbsp;</b></div></td>';
					$first = false;
					}
				$note1 = $notesESD[$ieva_1][$istu][$idi];
				$note2 = $notesESD[$ieva_2][$istu][$idi];
				$noteM = $notesSD[$istu][$idi];
				$noteNxC = $noteM*$coeffs[$idi];
				if	( $noteM >= 0.0 )
					{
					$totNxC += $noteNxC;
					$totCoeff += $coeffs[$idi];
					}
				$appr = &note2apprec( $noteM );
				$html_stu .= '<td>' . $course_names[$idi]
					// . '[' . $idi . ']'	// debug
					. '<br>' . $prof_names[$idi] . '</td><td>'
					. $competences[$idi] . '</td><td>'
					. (($note1 < 0.0)?(''):($note1)) . '</td><td>'
					. (($note2 < 0.0)?(''):($note2)) . '</td><td class="bo1">'
					. (($noteM < 0.0)?(''):($noteM)) . '</td><td>'
					. $coeffs[$idi] . '</td><td>'
					. (($noteM < 0.0)?(''):($noteNxC)) . '</td><td>'
					. $rangsSD[$istu][$idi] . '</td><td>'
					. $moyD[$idi] . '</td><td>'
					. $minD[$idi] . '</td><td>'
					. $maxD[$idi] . '</td><td>'
					. $appr . '</td></tr>';
				}
			// la ligne de totaux
			if	( $totCoeff > 0 )
				$subMoy = round( $totNxC / $totCoeff, 2 );
			else	$subMoy='';
			$html_stu .= '<tr><td colspan="5">Total</td><td>'
				. $totCoeff . '</td><td>'
				. $totNxC . '</td><td colspan="5">'
				. 'Moyenne du groupe de matières : ' . $subMoy . '</td></tr>';
			} // boucle des subjects
		// derniere ligne de la table principale : moyenne ponderee et rang de cet eleve
		$html_stu .= '<tr class="bo1"><td colspan="6">Total général</td><td>'
			. $totCoeffS[$istu] . '</td><td>'
			. $totNxCS[$istu] . '</td><td colspan="2">'
			. 'Rang : ' . $rangsS[$istu] . '</td><td colspan="3">'
			. 'Moyenne trim. : ' . $moyS[$istu] . '</td></tr>';
		$html_stu .= '</table></div>';
		// conclusions
		$appr = &note2apprec( $moyS[$istu] );
		$html_stu .= '<p>APPRECIATION TRAVAIL : ' . $appr . '</p>'
			. '<p>Moy. de la classe ' . $class_moy . '<br>'
			. 'Moy. Max ' . $class_max . '<br>'
			. 'Moy. Min ' . $class_min . '</p>';
		} // boucle des bulletins

	// convertir en PDF s'il y a lieu
	if	( $_REQUEST['modfunc'] === 'savePDF' ) // Print PDF.
		{
		$html  = '<!doctype html>' . "\n" . '<html><head><meta charset="UTF-8">';
		$html .= '<title>' . 'Explo' . '</title></head><body>' . "\n";	// <title> completement ignore ?
		$html .= $html_stu;
		$html .= '</body></html>';
		require_once 'classes/Wkhtmltopdf.php';
		// cree l'objet wrapper
		$wkhtmltopdf = new Wkhtmltopdf( array( 'path' => sys_get_temp_dir() ) );
		// passe les params essentiels au wrapper
		$wkhtmltopdf->setBinPath( $wkhtmltopdfPath );
		$wkhtmltopdf->setHtml( $html );
		// ce titre n'est pas affiche par acroread, mais par le browser oui, bon pour identifier les onglets
		// il est visible dans les proprietes du pdf. Il doit etre en ISO-8859-1 !!!
		$wkhtmltopdf->setTitle( utf8_decode('Explo') );
		if	( isset($_REQUEST['landscape'] ) )
			$wkhtmltopdf->setOrientation( Wkhtmltopdf::ORIENTATION_LANDSCAPE );
		// execute la conversion
		// UWAGA si on met juste MODE_EMBEDDED c'est considere comme zero qui est MODE_DOWNLOAD
		$wkhtmltopdf->output( Wkhtmltopdf::MODE_EMBEDDED, utf8_decode('Explo') . '.pdf' );
		}
	else	{
		// Le contenu interactif, exclu du PDF
		$url1 = 'Modules.php?modname=' . $_REQUEST['modname'] . '&explo';
		$url2 = $url1 . '&lp_classe=' . $lp_classe;
		$url3 = $url2 . '&modfunc=savePDF&_ROSARIO_PDF=1';
		echo	'<style type="text/css">', "\n",
			".butgreen { cursor: pointer; padding: 6px 18px;  margin: 8px 8px; border: solid 2px; ",
			"border-color: #4D4; background: #AFA; font-weight: bold; }\n",
			".hmenu { margin: 20px };\n",
			'</style>';
		echo '<div class="hmenu">';
		echo '<a class="butgreen" href="' . $url3 .                '" target="_blank">Ce document en PDF</a>';
		echo '<a class="butgreen" href="' . $url3 . '&landscape' . '" target="_blank">Ce document en PDF lanscape</a>';
		echo '<a class="butgreen" href="' . $url1 . '">Retour aux choix de la classe</a>';
		echo '</div>';
		echo '<hr>';
		// le bulletin
		echo $html_stu;
		}

	}
