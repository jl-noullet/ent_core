<?php

// production de html pour toutes les notes d'une classe en une fois
// utilise les donnees calculees par calc_notes.php
// resultat dans $html_stu


	$css_subject_width = 200;	// param au pif pour textes verticaux...
	$html_css = '<style type="text/css">'
		. 'table.lp { border-collapse:collapse; font-family: \'Lato\', sans-serif; }'
		. 'table.lp td { border:1px solid black; padding: 2px 8px 2px 10px; }'
		. '.bo1 { font-weight: bold }'
		. '.bul { padding-bottom: 20px; page-break-before: always; }'
		. 'td.vv { position: relative; width: 25px; height: ' . $css_subject_width . 'px; overflow: hidden  }'
		. 'div.vr { position: absolute; top: ' . $css_subject_width . 'px; left: 0;'
		. '-webkit-transform: rotate(-90deg); -ms-transform: rotate(-90deg); transform: rotate(-90deg);'
		. '-webkit-transform-origin: top left; -ms-transform-origin: top left; transform-origin: top left;'
		. 'width:  ' . $css_subject_width . 'px; text-align: right; }'
		. '</style>';

$html_stu = $html_css;


// les notes des 2 evals

$html_stu .= '<div class="bul"><table class="lp"><tr><td>'
	. $class_name . '<br>' . $evals[$ieva_1] . '/' . $evals[$ieva_2] . '</td>';

// la boucle des subjects
foreach	( $subject_names as $isub => $subject_name )
	// la boucle des cours du subject
	foreach	( $subjects_activities[$isub] as $idi )
		{
		$html_stu .= '<td class="vv"><div class="vr"><b>' . $course_names[$idi] . '&nbsp;</b></div></td>';
		}
$html_stu .= '</tr>';
// la boucle des eleves
foreach	( $noms_complets as $istu => $nom )
	{
	$html_stu .= '<tr><td>' . $nom . '</td>';
	$ptrnote1D = &$notesESD[$ieva_1][$istu];
	$ptrnote2D = &$notesESD[$ieva_2][$istu];
	foreach	( $subject_names as $isub => $subject_name )
		foreach	( $subjects_activities[$isub] as $idi )
			{
			$note1 = $ptrnote1D[$idi];
			$note2 = $ptrnote2D[$idi];
			$html_stu .= '<td>' . (($note1 < 0.0)?(''):($note1))
				. '<br>' . (($note2 < 0.0)?(''):($note2)) . '</td>';
			}
	$html_stu .= '</tr>';
	}
$html_stu .= '</table></div>';


// les moyennes des 2 evals, avec les rangs

$html_stu .= '<div class="bul"><table class="lp"><tr><td>'
	. $class_name . '<br>' . $trim_name . '<br>moy / rang</td>';
// la double boucle des cours
foreach	( $subject_names as $isub => $subject_name )
	foreach	( $subjects_activities[$isub] as $idi )
		{
		$html_stu .= '<td class="vv"><div class="vr"><b>' . $course_names[$idi] . '&nbsp;</b></div></td>';
		}
$html_stu .= '</tr>';
// la boucle des eleves
foreach	( $noms_complets as $istu => $nom )
	{
	$html_stu .= '<tr><td>' . $nom . '</td>';
	$ptrnotesD = &$notesSD[$istu];
	$ptrrangsD = &$rangsSD[$istu];
	foreach	( $subject_names as $isub => $subject_name )
		foreach	( $subjects_activities[$isub] as $idi )
			{
			$note = $ptrnotesD[$idi];
			$html_stu .= '<td>' . (($note < 0.0)?(''):($note)) . '<br>' . $ptrrangsD[$idi] . 'e</td>';
			}
	$html_stu .= '</tr>';
	}

$html_stu .= '</table></div>';

