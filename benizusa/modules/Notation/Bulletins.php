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
	// toutes les lecture de DB et calculs pour produire les bulletins trimestriels d'une classe
	// le choix du trimestre est determine selon UserMP()
	require_once( 'modules/Notation/calc_notes.php' );

	// Produire du HTML imprimable dans $html_stu
	if	( isset( $_REQUEST['stat_view'] ) )
		{
		if	( $_REQUEST['stat_view'] == 'tables' )
			require_once( 'modules/Notation/html_tabl.php' );
		else if	( $_REQUEST['stat_view'] == 'merite' )
			require_once( 'modules/Notation/html_merite.php' );
		else if	( $_REQUEST['stat_view'] == 'stats' )
			require_once( 'modules/Notation/html_stats.php' );
		}
	else	require_once( 'modules/Notation/html_bull.php' );

	// convertir en PDF s'il y a lieu
	if	( $_REQUEST['modfunc'] === 'savePDF' ) // Print PDF.
		{
		$html  = '<!doctype html>' . "\n" . '<html><head><meta charset="UTF-8">';
		$html .= '<title>' . 'Bulletins' . '</title></head><body>' . "\n";	// <title> completement ignore ?
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
		$wkhtmltopdf->setTitle( utf8_decode($class_name) );
		if	( isset($_REQUEST['landscape'] ) )
			$wkhtmltopdf->setOrientation( Wkhtmltopdf::ORIENTATION_LANDSCAPE );
		// execute la conversion
		// UWAGA si on met juste MODE_EMBEDDED c'est considere comme zero qui est MODE_DOWNLOAD
		$wkhtmltopdf->output( Wkhtmltopdf::MODE_EMBEDDED, utf8_decode($class_name) . '.pdf' );
		}
	else	{
		// Le contenu interactif, exclu du PDF
		$url1 = 'Modules.php?modname=' . $_REQUEST['modname'];
		$url2 = $url1 . '&lp_classe=' . $lp_classe;
		$url3 = $url2 . '&modfunc=savePDF&_ROSARIO_PDF=1';
		echo	'<style type="text/css">', "\n",
			".butgreen { cursor: pointer; padding: 6px 18px;  margin: 8px 8px; border: solid 2px; ",
			"border-color: #4D4; background: #AFA; font-weight: bold; }\n",
			".hmenu { margin: 20px };\n",
			'</style>';
		echo '<div class="hmenu">';
		if	( isset( $_REQUEST['stat_view'] ) )
			{
			if	( $_REQUEST['stat_view'] == 'merite' )
				{
				echo '<a class="butgreen" href="' . $url3 . '&stat_view=' . $_REQUEST['stat_view']
				. '" target="_blank">PDF couleur</a>';
				echo '<a class="butgreen" href="' . $url3 . '&stat_view=' . $_REQUEST['stat_view']
				. '&BW" target="_blank">PDF N/B</a>';
				echo '<a class="butgreen" href="' . $url2 . '">Les bulletins</a>';
				}
			else if	( $_REQUEST['stat_view'] == 'stats' )
				{
				echo '<a class="butgreen" href="' . $url3 . '&stat_view=' . $_REQUEST['stat_view']
				. '" target="_blank">PDF couleur</a>';
				// echo '<a class="butgreen" href="' . $url3 . '&stat_view=' . $_REQUEST['stat_view']
				// . '&BW" target="_blank">PDF N/B</a>';
				echo '<a class="butgreen" href="' . $url2 . '">Les bulletins</a>';
				}
			else if	( $_REQUEST['stat_view'] == 'tables' )
				{
				echo '<a class="butgreen" href="' . $url3 . '&stat_view=' . $_REQUEST['stat_view']
				. '" target="_blank">PDF</a>';
				echo '<a class="butgreen" href="' . $url2 . '">Les bulletins</a>';
				}
			}
		else	{
			echo '<a class="butgreen" href="' . $url3 . '&landscape'
			. '" target="_blank">PDF</a>';
			echo '<a class="butgreen" href="' . $url2 . '&stat_view=tables'. '">Tables Vérif.</a>';
			echo '<a class="butgreen" href="' . $url2 . '&stat_view=merite'. '">Classt. / Mérite</a>';
			echo '<a class="butgreen" href="' . $url2 . '&stat_view=stats'.  '">Statistiques</a>';
			}
		echo '<a class="butgreen" href="' . $url1 . '">Retour aux choix de la classe</a>';
		echo '</div>';
		echo '<hr>';
		// le bulletin
		echo $html_stu;
		}

	}