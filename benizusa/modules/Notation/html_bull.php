<?php

// production de html pour tous les bulletins d'une classe en une fois
// utilise les donnees calculees par calc_notes.php
// resultat dans $html_stu

$my_prefix = '2020A';	// prefix pour matricule

// Produire du HTML imprimable independant de l'eleve
$css_subject_width = 300;	// param au pif pour textes verticaux...
$html_css = '<style type="text/css">'
	. '.cen { text-align: center }'
	. '.bc { text-align: center; font-weight: bold }'
	. '.gri { font-weight: bold; background-color: #ccc }'
	. 'table.nobo { width: 100%; margin: 0px; }'
	. 'hr { margin-bottom: 10px; margin-top: 0 }'
	. 'td.buln { text-align: center; font-weight: bold; padding: 12px; font-size: 110% }'
	. 'td.w1 { width: 10em } td.w2 { width: 6em }'
	. 'img.foto { width: 150px; max-height: 150px; }'
	. 'div.foto { width: 150px; height: 150px; border: 1px solid black }'
	. 'td.foto { width: 160px }'
	. 'table.lp { border-collapse:collapse; margin-top: 10px }'
	. 'table.lp td { border:1px solid black; padding: 2px 5px 2px 5px; }'
	. 'td.comp { font-size: 88% }'
	. 'table.bot { width: 100%; border-collapse:collapse; margin-top: 10px }'
	. 'table.bot td { border:1px solid black; vertical-align: top; padding: 2px 0px 2px 0px; font-size: 88%; }'
	. 'table.sub { width: 100%; border-collapse:collapse; }'
	. 'table.sub td { border:2px solid #bbb }'
	. '.bo1 { font-weight: bold }'
	. '.bul {  font-family: \'Lato\', sans-serif; padding-bottom: 20px; page-break-before: always; }'
	. 'td.vv { position: relative; width: 25px; overflow: hidden  }'
	. 'div.vr { position: absolute; top: ' . $css_subject_width . 'px; left: 0;'
	. '-webkit-transform: rotate(-90deg); -ms-transform: rotate(-90deg); transform: rotate(-90deg);'
	. '-webkit-transform-origin: top left; -ms-transform-origin: top left; transform-origin: top left;'
	. 'width:  ' . $css_subject_width . 'px; text-align: right; }'
	. '</style>';
// bandeau
$lelogo = 'assets/benisuza4_logo.png';
if	( isset( $_REQUEST['_ROSARIO_PDF'] ) )
	$lelogo = 'file:///' . $RosarioPath . $lelogo;
$html_top = '<div class="bul"><table class="nobo cen"><tr><td width="40%">COMPLEXE ACADÉMIQUE BILINGUE BENISUZA</td>'
. '<td rowspan="2"><img src="' . $lelogo . '"></td><td width="40%"><b>RÉPUBLIQUE DU CAMEROUN</b></td></tr>'
. '<tr><td>BP 13396 Tél. 242 77 12 68</td>'
. '<td>Année Scolaire ' . UserSyear() . '/' . (UserSyear()+1) . '</td></tr></table><hr>';
// header de la table principale
$htmlt = '<table class="lp">'
	. '<tr class="gri"><td></td><td>Discipline</td><td width="30%">Compétence</td><td>EVAL<br>1</td><td>EVAL<br>2</td><td>Moy</td>'
	. '<td>Coef</td><td>N x C</td><td>Rang</td><td>Moy.<br>Classe</td><td>Min</td><td>Max</td><td>Appréciation</td></tr>';
// fragments de la table de conclusions (proxies)
$html_b1 = '<table class="bot"><tr><td width="32%" class="cen">DISCIPLINE'
	// sous-table de gauche
	. '<table class="sub"><tr><td width="30%">Abs. Inj. (h):</td><td width="20%"><b>';
// $lesabs
$html_b2 = '</b></td><td width="30%">Cons. (h):</td><td width="20%"></td></tr>'
	. '<tr><td>Abs. Just. (h):</td><td></td><td>Avertiss.</td><td></td></tr>'
	. '<tr><td>Retards:</td><td></td><td>Blâmes:</td><td></td></tr>'
	. '<tr><td>Retenues:</td><td></td><td>Excl. (j):</td><td></td></tr></table></td>'
	// sous_table centrale
	. '<td width="36%">&nbsp;APPRECIATION TRAVAIL : <b>';
// $appr
$html_b3 = '</b><table class="sub">'
	// sous-table centrale
	. '<td width="30%">Tableau d\'Honneur</td><td width="20%"></td><td colspan="2" class="cen gri">PROFIL CLASSE</td></tr>'
	. '<tr><td>Encouragements</td><td></td><td width="30%">Moy. de la classe</td><td class="bc" width="20%">';
// $class_moy
$html_b4 = '</td></tr><tr><td>Félicitations</td><td></td><td>Moy. Max</td><td class="bc">';
// $class_max
$html_b5 = '</td></tr><tr><td>Avertissements</td><td></td><td>Moy. Min</td><td class="bc">';
// $class_min
$html_b6 = '</td></tr>'	. '<tr><td>Blâme</td><td></td></tr></table></td>'
	// cases pour signatures
	. '<td class="cen">OBSERVATIONS ET VISA DU PROFESSEUR PRINCIPAL</td></tr>'
	. '<tr class="cen" height="120px"><td>VISA DU PARENT</td><td>APPRECIATIONS ET VISA DU SURVEILLANT GÉNÉRAL</td>'
	. '<td>VISA DU PRINCIPAL</td></tr></table>';

$fotopath = 'assets/StudentPhotos/' . UserSyear() . '/';

// $html_stu va recevoir tous les bulletins
$html_stu = $html_css;

// la boucle des eleves
foreach	( $noms_complets as $istu => $nom )
	{				// Produire du HTML imprimable specifique de l'eleve
	$YMD = explode("-", $dates_naissance[$istu] );
		if	( ( count($YMD) == 3 ) && ( (int)$YMD[0] > 1950 ) )
			$ladate = $YMD[2] . '-' . $YMD[1] . '-' . $YMD[0];
		else	$ladate = $dates_naissance[$istu];
	// N.B. le path est relatif au dir de Modules.php qui est le main script,
	// par chance cela marche pour file_exists et pour src="" en HTML, mais pas en PDF!
	$laphoto = $fotopath . $istu . '.jpg';
	// bandeau et table d'en-tête
	$html_stu .= $html_top . '<table class="nobo"><tr><td rowspan="5" class="foto">';
	if	( file_exists( $laphoto ) )
		{
		if	( isset( $_REQUEST['_ROSARIO_PDF'] ) )
			$laphoto = 'file:///' . $RosarioPath . $laphoto;
		$html_stu .= '<img class="foto" src="' . $laphoto . '"></td>';
		}
	else	$html_stu .= '<div class="foto"></div></td>';
	$html_stu .= '<td class="buln" colspan="4">BULLETIN DE NOTES TRIMESTRIEL N° ' . $trim_num . '</td></tr>'
	. '<tr><td class="w1">Noms et Prénoms :</td><td><b>' . $nom . '</b></td><td class="w2">Classe :</td><td><b>' . $class_name . '</b></td></tr>'
	. '<tr><td>Né(e) le :</td><td>' . $ladate . '</td><td>Effectif :</td><td>' . $effectif . '</td></tr>'
	. '<tr><td>Adresse élève :</td><td>' . $numtels[$istu] . '</td><td>Situation :</td><td>'
	. (($statuts[$istu]=='R')?('Redoublant(e)'):('Non Redoublant(e)')) . '</td></tr>'
	. '<tr><td>Professeur principal :</td><td>' . $prof_principal . '</td><td>Matricule :</td><td>'
	. $my_prefix . sprintf( "%04u", $istu ) . '</td></tr></table>';
	// table principale
	$html_stu .= $htmlt;

	// les proxies
	$prox_note1D = &$notesESD[$ieva_1][$istu];
	$prox_note2D = &$notesESD[$ieva_2][$istu];
	$prox_noteMD = &$notesSD[$istu];
	$prox_rangsD = &$rangsSD[$istu];
	// la boucle des subjects
	foreach	( $subject_names as $isub => $subject_name )
		{
		$rowspan = 1;	// + count( $subjects_activities[$isub] );
		$first = true;
		$totNxC = 0.0;
		$totCoeff = 0.0;
		// la premiere boucle des cours du subject, juste pour compter les ligne effectivement utilisees
		foreach	( $subjects_activities[$isub] as $idi )
			{
			if	( $coeffs[$idi] != 0 )
				$rowspan++;
			}
		// la seconde boucle des cours du subject
		foreach	( $subjects_activities[$isub] as $idi )
			{
			if	( $coeffs[$idi] != 0 )
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
					. '<br>' . $prof_names[$idi] . '</td><td class="comp">'
					. $competences[$idi] . '</td><td>'
					. (($note1 < 0.0)?(''):($note1)) . '</td><td>'
					. (($note2 < 0.0)?(''):($note2)) . '</td><td class="bo1">'
					. (($noteM < 0.0)?(''):($noteM)) . '</td><td>'
					. $coeffs[$idi] . '</td><td>'
					. (($noteM < 0.0)?(''):($noteNxC)) . '</td><td>'
					. $prox_rangsD[$idi] . '</td><td>';
				if	( $minD[$idi] <= 20.0 ) 
					$html_stu .= $moyD[$idi] . '</td><td>'
						. $minD[$idi] . '</td><td>'
						. $maxD[$idi] . '</td><td class="comp">';
				else	$html_stu .= '</td><td></td><td></td><td class="comp">';
				$html_stu .= $appr . '</td></tr>';
				}
			}
		// la ligne de totaux
		if	( $totCoeff > 0 )
			$subMoy = round( $totNxC / $totCoeff, 2 );
		else	$subMoy='';
		$html_stu .= '<tr class="gri"><td colspan="5">Total</td><td>'
			. $totCoeff . '</td><td>'
			. $totNxC . '</td><td colspan="5">'
			. 'Moyenne du groupe de matières : ' . $subMoy . '</td></tr>';
		} // boucle des subjects
	// derniere ligne de la table principale : moyenne ponderee et rang de cet eleve
	$html_stu .= '<tr class="gri"><td colspan="6">Total général</td><td>'
		. $totCoeffS[$istu] . '</td><td>'
		. $totNxCS[$istu] . '</td><td colspan="2">'
		. 'Rang : ' . $rangsS[$istu] . '</td><td colspan="3">'
		. 'Moyenne trim. : ' . $moyS[$istu] . '</td></tr>';
	$html_stu .= '</table>';
	// conclusions
	$appr = &LP_apprec( $moyS[$istu] );
	$lesabs = $anjS[$istu];
	if	( $lesabs == 0 )
		$lesabs = '';

	$html_stu .= $html_b1 . $lesabs . $html_b2 . $appr . $html_b3 . $class_moy
	. $html_b4 . $class_max . $html_b5 . $class_min . $html_b6
	. '</div>';	// class = "bul"
	} // boucle des bulletins

/*	$html_stu .= '<table class="bot"><tr><td width="32%" class="cen">DISCIPLINE';
	// sous-table de gauche
	$html_stu .= '<table class="sub"><tr><td width="30%">Abs. Inj. (h):</td><td width="20%">'
	. $labs
	. '</td><td width="30%">Cons. (h):</td><td width="20%"></td></tr>'
	. '<tr><td>Abs. Just. (h):</td><td></td><td>Avertiss.</td><td></td></tr>'
	. '<tr><td>Retards:</td><td></td><td>Blâmes:</td><td></td></tr>'
	. '<tr><td>Retenues:</td><td></td><td>Excl. (j):</td><td></td></tr></table></td>'
	// sous_table centrale
	. '<td width="36%">&nbsp;APPRECIATION TRAVAIL : <b>'
	. $appr
	. '</b><table class="sub">'
	. '<td width="30%">Tableau d\'Honneur</td><td width="20%"></td><td colspan="2" class="cen gri">PROFIL CLASSE</td></tr>'
	. '<tr><td>Encouragements</td><td></td><td width="30%">Moy. de la classe</td><td class="bc" width="20%">'
	. $class_moy . '</td></tr>'
	. '<tr><td>Félicitations</td><td></td><td>Moy. Max</td><td class="bc">'
	. $class_max
	. '</td></tr>'
	. '<tr><td>Avertissements</td><td></td><td>Moy. Min</td><td class="bc">'
	. $class_min . '</td></tr>'
	. '<tr><td>Blâme</td><td></td></tr>'
	. '</table></td>'
	// cases pour signatures
	. '<td class="cen">OBSERVATIONS ET VISA DU PROFESSEUR PRINCIPAL</td></tr>'
	. '<tr class="cen" height="100px"><td>VISA DU PARENT</td><td>APPRECIATIONS ET VISA DU SURVEILLANT GÉNÉRAL</td>'
	. '<td>VISA DU PRINCIPAL</td></tr></table>';
*/

