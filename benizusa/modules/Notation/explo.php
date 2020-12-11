<?php

require_once( 'modules/Loginpro/LP_func.php' );
$my_school = UserSchool();
$my_year = UserSyear();

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
	$my_redoub = array();		// array des "next_school" (meme index)
	$class_name = '';		// nom de la classe
	$class_short_name = '';		// petit nom de la classe
	$activites = array();		// le set des disciplines, index=idi, val=short_name 
	$subjects_activities = array();	// le set des subsets de disciplines par subjects, index=isub
					// chaque subset est un array de idi (index arbitraire)
	$subject_names = array();	// les noms des subsets, index=isub
	$course_names = array();	// noms des disciplines, indexes par idi
	$prof_names = array();		// noms des profs, indexes par idi
	$coeffs = array();		// coeffs des disciplines, indexes par idi
	$evals = array();		// array des evaluations du trimestre (index arbitraire)
	$eval_names = array();		// array des noms courts des evaluations indexe par ieval
	$notesSD = array();		// array 3D de toutes les notes de la classe : $notesSD[ieva][istu][idi]
	$notesDS = array();		// array 3D redondant des notes de la classe : $notesDS[ieva][idi][istu]
	$rangSD = array();		// array 2D du rang de chaque note dans sa discipline : $rangSD[istu][idi]
	// 1. acquerir les donnees communes
	$lp_classe = (int)$_REQUEST['lp_classe'];	// petit filtrage de securite
	// 1.1 la liste des eleves (remplir $my_students et $my_redoub)
	LP_liste_classe( $lp_classe, $class_name, $class_short_name, $my_students, $my_redoub );
	if	( !$class_name )
		exit( "<p>Classe $lp_classe inconnue</p>" ); 
	if	( count($my_students) == 0 )
		exit( "<p>Classe $class_name n'a pas d'élèves</p>" );
	// 1.2 le set des cours
	LP_prog_1eleve( $my_students[0], $activites );
	LP_split_by_subject( $activites, $subjects_activities, $subject_names );
		/* echo '<pre>'; var_dump( $subjects_activities ); echo '</pre><hr>'; */
		/* echo '<pre>'; var_dump( $subject_names ); echo '</pre>'; */
	// 1.3 les data des cours, indexes par course_id
	$nada = NULL;
	$sqlrequest = 'SELECT title, credits FROM course_periods WHERE course_period_id=';
	foreach	( $activites as $idi => $v )
		{
		$result = db_query( $sqlrequest . $idi, true );
		if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
			{
			$course_names[$idi] = ''; $prof_names[$idi] = '';
			LP_split_course_period( $row['title'], $nada, $course_names[$idi], $prof_names[$idi] );
			$coeffs[$idi] = $row['credits'];
			foreach	( $evals as $ieva )
				$notesDS[$ieva][$idi] = array();
			}
		}
	// 1.4 les evaluations
	$trim_name = '';
	$trimestre = LP_find_trimestre( UserMP(), $trim_name );
	LP_find_evals( $trimestre, $evals, $eval_names );
	// 2. acquerir toutes les notes : notesSD[ieva][istu][idi] et notesDS[ieva][idi][istu]
	// Préparons la lecture des notes de chaque eleve pour la periode courante
	$sqlrequ0 = 'SELECT grade_letter, course_period_id FROM student_report_card_grades WHERE syear='
		. $my_year . ' AND marking_period_id=\'';	// a completer dans le 2 foreaches
	foreach	( $evals as $ieva )
		{
		$sqlrequest = $sqlrequ0 . $ieva . '\' AND student_id=';		// a completer dans le foreach
		foreach	( $my_students as $istu )
			{
			$notesSD[$ieva][$istu] = array();
			$result = db_query( $sqlrequest . $istu, true );
			while	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
				{
				$tmp_note = $row['grade_letter'];
				if	( is_numeric( $tmp_note ) )
					$tmp_note = (float)$tmp_note;
				else	$tmp_note = -1.0;
				$idi = $row['course_period_id'];
				if	( is_array($notesDS[$idi] ) )		// eviter note dans matiere non repertoriee
					{
					$notesSD[$ieva][$istu][$idi] = $tmp_note;
					$notesDS[$ieva][$idi][$istu] = $tmp_note;
					}
				}
			// completer les notes manquantes
			foreach	( $activites as $idi => $v )
				if	( !isset( $notesSD[$istu][$idi] ) )
					{
					$notesSD[$ieva][$istu][$idi] = -2.0;
					$notesDS[$ieva][$idi][$istu] = -2.0;
					}
			}
		}
	// 3. calculer les rangs et moyennes par matiere
	foreach	( $activites as $idi => $v )
		{
		
		}
	
	// 4. calculer les rangs et moyennes generaux

	// 5. produire la page
	if	( $_REQUEST['modfunc'] === 'savePDF' ) // Print PDF.
		{
		ob_start();	// redirect stdout to a buffer
		}
	else	{	// Le contenu interactif, exclu du PDF
		$url1 = 'Modules.php?modname=' . $_REQUEST['modname'] . '&explo';
		$url2 = $url1 . '&lp_classe=' . $lp_classe;
		$url3 = $url2 . '&modfunc=savePDF&_ROSARIO_PDF=1';
		// propagation d'un argument (pourrait aussi se faire par $_SESSION)
		// $url2 .= ( '&lp_classe=' . (int)$_REQUEST['lp_classe'] );
		echo	'<style type="text/css">', "\n",
			".butgreen { cursor: pointer; padding: 6px 18px;  margin: 8px 8px; border: solid 2px; ",
			"border-color: #4D4; background: #AFA; font-weight: bold; }\n",
			".hmenu { margin: 20px };\n",
			'</style>';
		echo '<div class="hmenu">';
		echo '<a class="butgreen" href="' . $url3 .                '" target="_blank">Ce document en PDF</a>';
		echo '<a class="butgreen" href="' . $url3 . '&landscape' . '" target="_blank">Ce document en PDF lanscape</a>';
		echo '</div>';
		echo '<hr>';
		}
	// produire le HTML imprimable
	// d'abord du CSS
	$subject_width = 300;	// param au pif pour textes verticaux...
	?>
	<style type="text/css">
	table.lp { border-collapse:collapse; font-family: 'Lato', sans-serif; }
	table.lp td { border:1px solid black; padding: 2px 8px 2px 10px; }
	.gro { padding: 30px; }
	td.vv { position: relative; width: 25px; overflow: hidden  }
	div.vr { position: absolute; top: <?php echo $subject_width; ?>px; left: 0;
		-webkit-transform: rotate(-90deg); -ms-transform: rotate(-90deg); transform: rotate(-90deg);
		-webkit-transform-origin: top left; -ms-transform-origin: top left; transform-origin: top left;
		width:  <?php echo $subject_width; ?>px; text-align: right; }
	</style> <?php
	// le header du bulletin
	echo '<h2>', $class_name, '</h2>';
	echo '<p>', $trim_name, ' : [ ';
	foreach	( $evals as $ieva )
		echo $eval_names[$ieva], ' ';
	echo ']</p>';
	echo '<div class="gro"><table class="lp">';
	echo '<tr><td></td><td>Discipline</td><td>Coef</td></tr>';
	// la boucle des subjects
	foreach	( $subject_names as $isub => $subject_name )
		{
		$rowspan = 1 + count( $subjects_activities[$isub] );
		$first = true;
		// la boucle des cours du subject
		foreach	( $subjects_activities[$isub] as $idi )
			{
			echo '<tr>';
			if	( $first )
				{
				echo '<td rowspan="', $rowspan, '" class="vv"><div class="vr"><b>', $subject_name, '&nbsp;</b></div></td>';
				$first = false;
				}
			echo '<td>', $course_names[$idi], '<br>', $prof_names[$idi], '</td>';
			echo '<td>', $coeffs[$idi], '</td></tr>';
			}
		// la ligne de totaux
		echo '<tr><td>Total</td>';
		echo '<td>', ' ', '</td></tr>';
		}
	echo '</table></div>';

	// convertir en PDF s'il y a lieu
	if	( $_REQUEST['modfunc'] === 'savePDF' ) // Print PDF.
		{
		$html  = '<!doctype html>' . "\n" . '<html><head><meta charset="UTF-8">';
		$html .= '<title>' . 'Explo' . '</title></head><body>' . "\n";	// <title> completement ignore ?
		$html .= ob_get_clean();
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
	}
