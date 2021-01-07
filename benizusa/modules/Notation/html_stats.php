<?php
$html_css = '<style type="text/css">'
	. '.cen { text-align: center }'
	. '.eff { float: right; font-style: italic; }'
	. '.bc { text-align: center; font-weight: bold }'
	. 'table.nobo { width: 100%; margin: 0px; }'
	. 'table.lp { border-collapse:collapse; margin-top: 10px; margin-bottom: 25px }'
	. 'table.lp td { border:1px solid black; padding: 2px 5px 2px 5px; }'
	. 'canvas { margin: 20px 6px 4px 6px;  }'
	. '.bo1 { font-weight: bold }'
	. '.bk { page-break-before: always }'
	. '</style>';
$html_stu = $html_css;

// bandeau
$lelogo = 'assets/benisuza4_logo.png';
if	( isset( $_REQUEST['_ROSARIO_PDF'] ) )
	$lelogo = 'file:///' . $RosarioPath . $lelogo;
$html_stu .= '<table class="nobo cen"><tr><td width="40%">COMPLEXE ACADÉMIQUE BILINGUE BENISUZA</td>'
. '<td rowspan="2"><img src="' . $lelogo . '"></td><td width="40%"><b>RÉPUBLIQUE DU CAMEROUN</b></td></tr>'
. '<tr><td>BP 13396 Tél. 242 77 12 68</td>'
. '<td>Année Scolaire ' . UserSyear() . '/' . (UserSyear()+1) . '</td></tr></table><hr>';

// titre
$html_stu .= '<h2 class="cen">STATISTIQUES PAR MATIERE, CLASSE DE ' . strtoupper($class_name) . '</h2>'
. '<h2 class="cen">TRIMESTRE ' . $trim_num . '</h2>';

//<span class="eff"> Effectif : ' . $effectif . '</span>

// javascript commun
$le_script = 'modules/Notation/LP_func.js?1001';
if	( isset( $_REQUEST['_ROSARIO_PDF'] ) )
	$le_script = 'file:///' . $RosarioPath . $le_script;
$html_stu .= '<script src="' . $le_script . '"></script>';
$html_stu .= '<script> var canvas, ctx; var levels = [ ';
// les niveaux d'appreciation par note entiere de 0 a 20 pour coloriage
// exemple [ 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 2, 2, 3, 3, 3, 3, 3, 3 ];'
for	( $i = 0; $i <= 20; $i++ )
	$html_stu .= LP_note2level($i) . ',';
$html_stu .= '];';
// les couleurs officielles
$html_stu .= 'var colors = ' . LP_t_array_to_JS( $LP_level_colors ) . ';</script>';

$bk_cnt = 1;
// la boucle des subjects
foreach	( $subject_names as $isub => $subject_name )
	{
	$html_stu .= '<h3>' . $subject_name . '</h3>'; 
	// la boucle des cours du subject
	foreach	( $subjects_activities[$isub] as $idi )
		{
		if	( $coeffs[$idi] > 0 )
			{
			// calcul preliminaire de l'histogramme
			$histo = [ 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 ];
			foreach	( $notesDS[$idi] as $v )	// boucle des élèves
				{
				if	( ( $v >= 0.0 ) && ( $v <= 20.0 ) )
					{ $n = (int)floor($v); $histo[$n] += 1;	}
				}
			// le header
			if	( ( $bk_cnt++ % 5 ) == 0 )
				$html_stu .= '<table class="lp bk">';
			else	$html_stu .= '<table class="lp">';
			$html_stu .= '<tr><td colspan="2"><b>' . $course_names[$idi] . '</b> - '
			. $prof_names[$idi] . '<span class="eff"> Effectif : ' . $effectif . '</span></td></tr>';
			$html_stu .= '<tr><td>maximum<br>' . $maxD[$idi] . '</td><td rowspan="3" class="his">';
			// le graphique
			$html_stu .= '<canvas id="stat_' . $idi . '" width="660" height="160" style="border: 0"></canvas>';
			// production de l'histogramme : javascript
			$html_stu .= '<script> canvas = document.getElementById("stat_' . $idi . '");'
				. ' ctx = canvas.getContext("2d"); LP_histo_notes( ctx, 660, 160, '
				. LP_n_array_to_JS( $histo ) . ', levels, colors ); </script>';
			// fin du graphique
			$html_stu .= '</td></tr><tr><td><b>moyenne<br>' . $moyD[$idi] . '</b></td></tr><tr><td>minimum<br>'
			. $minD[$idi] . '</td></tr></table>';
			}
		}
	}
