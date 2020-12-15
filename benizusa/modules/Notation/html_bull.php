<?php

// production de html pour tous les bulletins d'une classe en une fois
// utilise les donnees calculees par calc_notes.php
// resultat dans $html_stu

	// Produire du HTML imprimable independant de l'eleve
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
	$html0 = '<div class="bul"><h3>' . $class_name . '</h3>'
		. '<p>' . $trim_name . ' : [ ' . $evals[$ieva_1] . ' ' . $evals[$ieva_2] . ' ]</p>';
	// header de la table
	$htmlt = '<table class="lp">'
		. '<tr class="bo1"><td></td><td>Discipline</td><td>Compétence</td><td>EVAL<br>1</td><td>EVAL<br>2</td><td>Moy</td>'
		. '<td>Coef</td><td>N x C</td><td>Rang</td><td>Moy.<br>Classe</td><td>Min</td><td>Max</td><td>Appréciation</td></tr>';
	
	$html_stu = $html_css;

	// la boucle des eleves
	foreach	( $noms_complets as $istu => $nom )
		{
		// Produire du HTML imprimable specifique de l'eleve
		$html_sid = "<p>Noms et prénoms: $nom<br>Né(e) le " . $dates_naissance[$istu] . " Matricule $istu</p>";
		$html_stu .= $html0 . $html_sid . $htmlt;
		// les proxies
		$prox_note1D = &$notesESD[$ieva_1][$istu];
		$prox_note2D = &$notesESD[$ieva_2][$istu];
		$prox_noteMD = &$notesSD[$istu];
		$prox_rangsD = &$rangsSD[$istu];
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
				$note1 = $prox_note1D[$idi];
				$note2 = $prox_note2D[$idi];
				$noteM = $prox_noteMD[$idi];
				$noteNxC = $noteM*$coeffs[$idi];
				if	( $noteM >= 0.0 )
					{
					$totNxC += $noteNxC;
					$totCoeff += $coeffs[$idi];
					}
				$appr = &LP_apprec( $noteM );
				$html_stu .= '<td>' . $course_names[$idi]
					// . '[' . $idi . ']'	// debug
					. '<br>' . $prof_names[$idi] . '</td><td>'
					. $competences[$idi] . '</td><td>'
					. (($note1 < 0.0)?(''):($note1)) . '</td><td>'
					. (($note2 < 0.0)?(''):($note2)) . '</td><td class="bo1">'
					. (($noteM < 0.0)?(''):($noteM)) . '</td><td>'
					. $coeffs[$idi] . '</td><td>'
					. (($noteM < 0.0)?(''):($noteNxC)) . '</td><td>'
					. $prox_rangsD[$idi] . '</td><td>'
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
		$html_stu .= '</table>';
		// conclusions
		$appr = &LP_apprec( $moyS[$istu] );
		$html_stu .= '<p>APPRECIATION TRAVAIL : ' . $appr . '</p>'
			. '<p>Moy. de la classe ' . $class_moy . '<br>'
			. 'Moy. Max ' . $class_max . '<br>'
			. 'Moy. Min ' . $class_min . '</p>';
		$html_stu .= '</div>';	// class = "bul"
		} // boucle des bulletins

