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
	// acquerir les donnees communes
	$lp_classe = (int)$_REQUEST['lp_classe'];	// petit filtrage de securite
	$class_name = ''; $class_short_name = '';
	// d'abord la liste des eleves
	// des arrays tous indexes par le meme index arbitraire
	$my_students = array();
	$my_redoub = array();
	LP_liste_classe( $lp_classe, $class_name, $class_short_name, $my_students, $my_redoub );
	if	( !$class_name )
		exit( "<p>Classe $lp_classe inconnue</p>" ); 
	if	( count($my_students) == 0 )
		exit( "<p>Classe $class_name n'a pas d'élèves</p>" );
	// le set des cours
	$activites = array();
	LP_prog_1eleve( $my_students[0], $activites );
	$subjects_activities = array();
	$subject_names = array();
	LP_split_by_subject( $activites, $subjects_activities, $subject_names );
		/* echo '<pre>'; var_dump( $subjects_activities ); echo '</pre><hr>'; */
		/* echo '<pre>'; var_dump( $subject_names ); echo '</pre>'; */
	// les data des cours, indexes par course_id
	$course_names = array(); $prof_names = array(); $coeffs = array();
	$sqlrequest = 'SELECT title, credits FROM course_periods WHERE course_period_id=';
	$nada = NULL;
	foreach	( $activites as $k => $v )
		{
		$result = db_query( $sqlrequest . $k, true );
		if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
			{
			$course_names[$k] = ''; $prof_names[$k] = '';
			LP_split_course_period( $row['title'], $nada, $course_names[$k], $prof_names[$k] );
			$coeffs[$k] = $row['credits'];
			}
		}
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
	echo '<div class="gro"><table class="lp">';
	echo '<tr><td></td><td>Discipline</td><td>Coef</td></tr>';
	// la boucle des subjects
	foreach	( $subject_names as $ksub => $subject_name )
		{
		$rowspan = 1 + count( $subjects_activities[$ksub] );
		$first = true;
		// la boucle des cours du subject
		foreach	( $subjects_activities[$ksub] as $kact => $v )
			{
			$k = $subjects_activities[$ksub][$kact];
			echo '<tr>';
			if	( $first )
				{
				echo '<td rowspan="', $rowspan, '" class="vv"><div class="vr"><b>', $subject_name, '&nbsp;</b></div></td>';
				$first = false;
				}
			echo '<td>', $course_names[$k], '<br>', $prof_names[$k], '</td>';
			echo '<td>', $coeffs[$k], '</td></tr>';
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
