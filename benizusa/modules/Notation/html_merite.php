<?php
$html_css = '<style type="text/css">'
	. '.cen { text-align: center }'
	. '.bc { text-align: center; font-weight: bold }'
	. 'table.nobo { width: 100%; margin: 0px; }'
	. 'table.lp { border-collapse:collapse; margin-top: 10px }'
	. 'table.lp td { border:1px solid black; padding: 2px 5px 2px 5px; }'
	. 'td.comp { font-size: 88% }'
	. '.bo1 { font-weight: bold }'
	. '</style>';
$html_stu = $html_css;

$my_prefix = '2020A';	// prefix pour matricule

// bandeau
$lelogo = 'assets/benisuza4_logo.png';
if	( isset( $_REQUEST['_ROSARIO_PDF'] ) )
	$lelogo = 'file:///' . $RosarioPath . $lelogo;
$html_stu .= '<table class="nobo cen"><tr><td width="40%">COMPLEXE ACADÉMIQUE BILINGUE BENISUZA</td>'
. '<td rowspan="2"><img src="' . $lelogo . '"></td><td width="40%"><b>RÉPUBLIQUE DU CAMEROUN</b></td></tr>'
. '<tr><td>BP 13396 Tél. 242 77 12 68</td>'
. '<td>Année Scolaire ' . UserSyear() . '/' . (UserSyear()+1) . '</td></tr></table><hr>';
// titre
$html_stu .= '<h2 class="cen">CLASSEMENT PAR ORDRE DE MÉRITE, CLASSE DE ' . strtoupper($class_name) . '</h2>'
	. '<h2 class="cen">TRIMESTRE ' . $trim_num . '</h2>';
// table
$html_stu .= '<table class="lp"><tr class="bo1"><td>Nom(s) et prénom(s)</td><td>Matricule</td><td>Sexe</td>'
	. '<td>Statut</td><td>Rang<br>sur ' . $effectif . '</td><td>Moy.</td><td>Appréciation</td></tr>';
foreach	( $rangsS as $istu => $rang )
	{				// Produire une ligne de table par eleve
	$appr = &LP_apprec( $moyS[$istu] );
	$html_stu .= '<tr><td>' . $noms_complets[$istu] . '</td><td>' . $my_prefix . sprintf( "%04u", $istu )
	. '</td><td>' . $sexes[$istu] . '</td><td>' . (($statuts[$istu]=='R')?('R'):('')) . '</td><td>' . $rang
	. '</td><td>' . $moyS[$istu] . '</td><td class="comp">' . $appr . '</td></tr>';
	}
$html_stu .= '</table>';
