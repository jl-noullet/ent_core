<?php
/**
 liste d'eleves avec resultats comptables
 */

require_once( 'LP_func.php' );
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
	echo '<p><input type="checkbox" name="date"> afficher dates de paiement</p>';
	echo '<p><input type="checkbox" name="comm" checked> afficher commentaires</p>';
	echo '<button type="submit" class="button-primary"> Ok </button> </form>';
	}
else	{
	$lp_classe = (int)$_REQUEST['lp_classe'];	// petit filtrage de securite
	$date_flag = isset( $_REQUEST['date'] );	// options
	$comm_flag = isset( $_REQUEST['comm'] );
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
		// des tableaux tous indexes par student_id, ainsi si on trie l'un on trie les autres
		$noms_complets = array();
		$fees = array();			// les appels de fonds
		$totalfee = array();			// le total de appels
		$paid = array();			// les versements
		if ( $date_flag ) $dates = array();	// les dates de versement
		if ( $comm_flag ) $comments = array();	// les commentaires
		$totalpaid = array();	// le total versé
		$paycnt = 4;		// le max du nombre de versements, pour ajuster les colonnes
		// remplir les tableaux
		foreach	( $my_students as $k => $v ) {
			// les noms complets
			$sqlrequest = 'SELECT first_name, middle_name, last_name  '
				. 'FROM students WHERE student_id=' . $v;
			$result = db_query( $sqlrequest, true );
			if	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
				{
				$noms_complets[$v] = $row['last_name'] . ' ' . $row['first_name'] . ' ' . $row['middle_name'];
				}
			// les appels de fonds
			$fees[$v] = array();
			$totalfee[$v] = 0;
			$sqlrequest = 'SELECT amount FROM billing_fees WHERE syear=\'' . $my_year . '\' AND student_id=' . $v;
			//	. ' ORDER BY due_date';
			$result = db_query( $sqlrequest, true );
			while	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
				{
				$a = (int)$row['amount'];
				// echo $row['title'], ' ', $row['amount'], '<br>';
				$fees[$v][] = $a;
				$totalfee[$v] += $a;
				}
			// echo '<pre>'; var_dump( $fees[$v] ); echo '</pre>';
			// les paiements
			$sqlrequest = 'SELECT amount';
			$paid[$v] = array();
			if ( $date_flag ) { $dates[$v] = array(); $sqlrequest .= ', payment_date'; }
			if ( $comm_flag ) { $comments[$v] = array(); $sqlrequest .= ', comments'; }
			$totalpaid[$v] = 0;
			$sqlrequest .= ' FROM billing_payments WHERE syear=\'' . $my_year . '\' AND student_id=' . $v;
			$result = db_query( $sqlrequest, true );
			// echo $sqlrequest, '<br>';
			while	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
				{
				$a = (int)$row['amount'];
				// echo $row['title'], ' ', $row['amount'], '<br>';
				$paid[$v][] = $a;
				$totalpaid[$v] += $a;
				if ( $date_flag ) $dates[$v][] = $row['payment_date'];
				if ( $comm_flag ) $comments[$v][] = $row['comments'];
				}
			// echo '<pre>'; var_dump( $comments[$v] ); echo '</pre>';
			if	( count($paid[$v]) > $paycnt )
				$paycnt = count($paid[$v]);
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
			if ( $date_flag ) $url2 .= '&date';
			if ( $comm_flag ) $url2 .= '&comm';
			echo	'<style type="text/css">', "\n",
				".butgreen { cursor: pointer; padding: 6px 18px;  margin: 8px 8px; border: solid 2px; ",
				"border-color: #4D4; background: #AFA; font-weight: bold; }\n",
				".hmenu { margin: 20px };\n",
				'</style>';
			echo '<div class="hmenu">';
			// le style LINK, target="_blank" indispensable dans ce cas
			echo '<a class="butgreen" href="' . $url2 . '" target="_blank">Ce document en PDF</a>';
			echo '<a class="butgreen" href="' . $url1 . '">Retour au choix de la classe</a>';
			echo '</div>';
			echo '<hr>';
			}
		// produire le HTML

		$html_css = '<style type="text/css">'
			. '#pdfpage { background-color: #FFF }'
			. 'table.lp { border-collapse:collapse; font-family: \'Lato\', sans-serif; }'
			. 'table.lp td { border:1px solid black; padding: 2px 10px 2px 10px; text-align: right; vertical-align:top }'
			. 'table.lp td.le { text-align: left }'
			. 'table.lp tr.ce td { text-align: center }'
			. '.bo1 { font-weight: bold }'
			. '.red { background-color: #Fa9; }'
			. '.green { background-color: #6F6; }'
			. '</style>';

		echo $html_css, '<div id=pdfpage><h3>CLASSE: ', $class_name, 'ajouter date et effectif</h3>';
		echo '<table class="lp">';
		echo '<tr class="ce"><td>Nom complet</td><td>Total<br>facturé</td><td colspan="', $paycnt,
			'">Versements</td><td>Total payé</td><td><b>Reste dû</b></td></tr>';
			
		foreach	( $noms_complets as $k => $v ) {
			echo '<tr><td class="le">', $v, '</td><td>', $totalfee[$k], '</td>';
			for	( $i = 0; $i < $paycnt; ++$i )
				{
				echo '<td>';
				if	( isset( $paid[$k][$i] ) )
					{
					echo $paid[$k][$i];
					if ( $date_flag ) echo '<br>', LP_date_reverse( $dates[$k][$i] );
					if ( $comm_flag ) echo '<br>', $comments[$k][$i];
					}
				echo '</td>';
				}
			$solde = $totalfee[$k] - $totalpaid[$k];
			echo '<td>', $totalpaid[$k], '</td>';
			if	( ( $solde == 0 ) && ( $totalfee[$k] != 0 ) )
				echo '<td class="green">';
			else if	( ( $totalpaid[$k] == 0 ) && ( $totalfee[$k] != 0 ) )
				echo '<td class="red">';
			else	echo '<td>';
			echo '<b>', $solde, '</b></td></tr>';
			}
		echo '</table></div>';
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

