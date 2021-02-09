<?php
/**
 liste d'eleves par classes aux format Kmer
 */

require_once( 'LP_func.php' );
$my_school = UserSchool();
$my_year = UserSyear();
// params a mettre dans la config du module
$my_prefix = '2020A';
$my_place = 'Yaoundé';
$my_boss = 'Le Principal';

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
	$lp_classe = (int)$_REQUEST['lp_classe'];	// petit filtrage de securite
	$class_name = ''; $class_short_name = '';
	// des arrays tous indexes par le meme index arbitraire
	$my_students = array();
	$my_redoub = array();
	LP_liste_classe( $lp_classe, $class_name, $class_short_name, $my_students, $my_redoub );
	if	( !$class_name )
		echo "<p>Classe $lp_classe inconnue</p>";
	else if	( count($my_students) == 0 )
		echo "<p>Classe $class_name n'a pas d'élèves</p>";
	else	{
		// echo '<pre>'; var_dump( $my_students ); echo '</pre>';
		// des tableaux tous indexes par student_id, ainsi si on trie l'un on trie les autres
		$noms_complets = array();
		$dates_naissance = array();
		$sexes = array();
		$statuses = array();
		// les comptages
		$cntGN = 0; $cntFN = 0; $cntN = 0; $cntGR = 0; $cntFR = 0; $cntR = 0; $cntG = 0; $cntF = 0;
		// remplir les tableaux
		foreach	( $my_students as $k => $v ) {
			$sqlrequest = 'SELECT first_name, middle_name, last_name, custom_200000000, custom_200000004 '
				. 'FROM students WHERE student_id=' . $v;
			$result = db_query( $sqlrequest, true );
			if	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
				{
				$noms_complets[$v] = $row['last_name'] . ' ' . $row['first_name'] . ' ' . $row['middle_name'];
				$dates_naissance[$v] = $row['custom_200000004'];
				if	( $row['custom_200000000'][0] == 'F') { $sexes[$v] = 'F'; $cntF++; }
				else if	( $row['custom_200000000'][0] == 'M') { $sexes[$v] = 'G'; $cntG++; }
				else	$sexes[$v] = ' ';
				if	( $my_redoub[$k] == 0 )
					{
					$statuses[$v] = 'R';
					$cntR++;
					if	( $sexes[$v] == 'G' )
						$cntGR++;
					else if	( $sexes[$v] == 'F' )
						$cntFR++;
					}
				else	{
					$statuses[$v] = 'N';
					$cntN++;
					if	( $sexes[$v] == 'G' )
						$cntGN++;
					else if	( $sexes[$v] == 'F' )
						$cntFN++;
					}
				}
			}
		// trier par ordre alphabetique des noms
		natcasesort( $noms_complets );

		if	( $_REQUEST['modfunc'] === 'savePDF' ) // Print PDF.
			{
			ob_start();	// redirect stdout to a buffer
			}
		else	{	// Le contenu interactif, exclu du PDF
			$url1 = 'Modules.php?modname=' . $_REQUEST['modname'];
			$url2 = $url1 . '&modfunc=savePDF&_ROSARIO_PDF=1';
			// propagation d'un argument (pourrait aussi se faire par $_SESSION)
			$url2 .= ( '&lp_classe=' . (int)$_REQUEST['lp_classe'] );
			// echo '<h3>le style FORM</h3>';
			// UWAGA! methode GET ne marche pas car l'URL contient deja un '?'
			// echo '<form action="' . $url2 . '" method="POST">';
			// echo '<input type="submit" value="Do the PDF" class="button-primary" />';
			// echo '</form>';
			// echo '<hr>';
			// echo '<h3>le style LINK</h3>';
			// UWAGA! target="_blank" indispensable dans ce cas
			echo	'<style type="text/css">', "\n",
				".butgreen { cursor: pointer; padding: 6px 18px;  margin: 8px 8px; border: solid 2px; ",
				"border-color: #4D4; background: #AFA; font-weight: bold; }\n",
				".hmenu { margin: 20px };\n",
				'</style>';
			echo '<div class="hmenu">';
			echo '<a class="butgreen" href="' . $url2 . '" target="_blank">Ce document en PDF</a>';
			echo '<a class="butgreen" href="' . $url1 . '">Retour au choix de la classe</a>';
			echo '</div>';
			echo '<hr>';
			}

		// produire le HTML, tout dans une grande table avec 2 colonnes
		echo	'<style type="text/css">', "\n",
			"#pdfpage { background-color: #FFF }\n",
			"table.nobo { width: 100%; margin-top: 12px; margin-bottom: 12px; }\n",
			"table.nobo td { border:0; padding: 6px; }\n",
			"table.lp { width: 100%; border-collapse:collapse; }\n",
			"table.lp td { border:1px solid black; padding: 2px 4px 2px 6px; }\n",
			"table.lp2 { width: 99%; float: right; }\n table.lp2 td { width: 11%; text-align: center }\n",
			'</style>';
		// UWAGA il faut une URL absolue pour l'image
		$pos = strpos( $_SERVER['PHP_SELF'], 'Modules.php' );
		$root = substr( $_SERVER['PHP_SELF'], 0, $pos );
		$img_URL = 'http://' . $_SERVER['HTTP_HOST'] . $root . 'assets/benisuza3.png';
		echo '<div id="pdfpage">';
		echo '<table class="nobo">';
		echo '<tr><td colspan="2" style="text-align: center"><img src="' . $img_URL . '"></td></tr>';
		echo '<tr><td colspan="2" style="text-align: right"><b>ANNEE SCOLAIRE ', $my_year, '/', $my_year+1, '</b></td></tr>';
		echo '<tr><td colspan="2" style="text-align: center"><b>LISTE DES ELEVES</b></td></tr>';
		echo '<tr><td>CLASSE : <b>' . $class_name . '</b></td>';
		// a table in the table
		echo '<td><table class="lp lp2"><tr><td colspan="3">NOUVEAUX</td><td colspan="3">REDOUBLANTS</td><td colspan="3">TOTAL</td></tr>', "\n";
		echo '<tr><td>G</td><td>F</td><td>Total</td><td>G</td><td>F</td><td>Total</td><td>G</td><td>F</td><td>Total</td></tr>', "\n";
		echo "<tr><td>$cntGN</td><td>$cntFN</td><td>$cntN</td>",
		         "<td>$cntGR</td><td>$cntFR</td><td>$cntR</td>",
			 "<td>$cntG</td><td>$cntF</td><td>", count($my_students), "</td></tr>\n";
		echo "</table></td>\n";
		// got out of the table in the table
		echo "</tr></table>\n";
		// got out of the nobo table
		// main table now
		echo '<table class="lp"><tr><td>N°</td><td>MATRICULE</td><td>NOMS ET PRENOMS</td><td>DATE DE NAISSANCE</td><td>SEXE</td><td>STATUT</td></tr>',
		     "\n";
		$cnt = 1;
		foreach	( $noms_complets as $k => $v ) {
			echo '<tr><td>', $cnt, '</td><td>', $my_prefix, sprintf( "%04u", $k ), '</td><td>', $v, '</td><td>',
			     LP_date_reverse($dates_naissance[$k]), '</td><td>', $sexes[$k], '</td><td>', $statuses[$k], "</td></tr>\n";	
			$cnt++;
			}
		echo "</table>\n";
		// got out of the main table
		echo '<table class="nobo">';
		echo '<tr><td>', $my_place, ', le</td><td style="text-align: right">', $my_boss, '</td></tr>';
		echo "</table>";
		echo '</div>';	// id="pdfpage"

		// convertir en PDF s'il y a lieu
		if	( $_REQUEST['modfunc'] === 'savePDF' ) // Print PDF.
			{
			$html  = '<!doctype html>' . "\n" . '<html><head><meta charset="UTF-8">';
			$html .= '<title>' . $class_name . '</title></head><body>' . "\n";	// <title> completement ignore ?
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
			$wkhtmltopdf->setTitle( utf8_decode($class_name) );
			// execute la conversion
			// UWAGA si on met juste MODE_EMBEDDED c'est considere comme zero qui est MODE_DOWNLOAD
			$wkhtmltopdf->output( Wkhtmltopdf::MODE_EMBEDDED, utf8_decode($class_name) . '.pdf' );
			}
		}
	}

?>