<?php
/**
 liste d'eleves par classes aux format Kmer
 */

echo	'<style type="text/css">', "\n",
	"table.nobo { background-color: #FFF }\n",
	"table.nobo td { border:0; padding: 6px; }\n",
	"table.lp { width: 100%; border-collapse:collapse; }\n",
	"table.lp td { border:1px solid black; padding: 2px 4px 2px 6px; }\n",
	"table.lp2 { width: 99%; float: right; }\n table.lp2 td { width: 11%; text-align: center }\n",
	'</style>';

// Display main header with Module icon and Program title.
// DrawHeader( ProgramTitle() );

$my_school = UserSchool();
$my_year = UserSyear();
// params a mettre dans la config du module
$my_prefix = '2020A0';
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
	echo '<button type="submit"> Ok </button> </form>';
	}
else	{
	$lp_classe = (int)$_REQUEST['lp_classe'];	// petit filtrage de securite
	// checher nom de la classe
	$sqlrequest = 'SELECT short_name, title FROM school_gradelevels WHERE id=' . $lp_classe;
	$result = db_query( $sqlrequest, true );
	if	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		{
		$class_name = $row['title'];
		// identifier les eleves inscrits dans cette classe
		$sqlrequest = 'SELECT student_id, drop_code, next_school FROM student_enrollment WHERE grade_id=' . $lp_classe . ' AND syear=' . $my_year; 
		// echo $sqlrequest;
		$result = db_query( $sqlrequest, true );
		// des arrays tous indexes par le meme index arbitraire
		$my_students = array();
		$my_redoub = array();
		while	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
			{
			// echo '<pre>'; var_dump( $row ); echo '</pre>';
			if	( !$row['drop_code'] )
				{
				$my_students[] = (int)$row['student_id'];
				$my_redoub[] = (int)$row['next_school'];
				}
			}
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

		// produire le HTML, tout dans une grande table avec 2 colonnes
		echo '<table class="nobo">';
		echo '<tr><td colspan="2" style="text-align: center"><img src="/benizusa/assets/benisuza3.png"></td></tr>';
		echo '<tr><td colspan="2" style="text-align: right"><b>ANNEE SCOLAIRE ', $my_year, '/', $my_year+1, '</b></td></tr>';
		echo '<tr><td colspan="2" style="text-align: center"><b>LISTE DES ELEVES</b></td></tr>';
		echo '<tr><td>CLASSE : <b>' . $class_name . '</b></td><td>';
		echo '<table class="lp lp2"><tr><td colspan="3">NOUVEAUX</td><td colspan="3">REDOUBLANTS</td><td colspan="3">TOTAL</td></tr>', "\n";
		echo '<tr><td>G</td><td>F</td><td>Total</td><td>G</td><td>F</td><td>Total</td><td>G</td><td>F</td><td>Total</td></tr>', "\n";
		echo "<tr><td>$cntGN</td><td>$cntFN</td><td>$cntN</td>",
		         "<td>$cntGR</td><td>$cntFR</td><td>$cntR</td>",
			 "<td>$cntG</td><td>$cntF</td><td>", count($my_students), "</td></tr>\n";
		echo "</table>\n";
		echo '</td></tr><tr><td colspan="2">';
		echo '<table class="lp"><tr><td>N°</td><td>MATRICULE</td><td>NOMS ET PRENOMS</td><td>DATE DE NAISSANCE</td><td>SEXE</td><td>STATUT</td></tr>',
		     "\n";
		$cnt = 1;
		foreach	( $noms_complets as $k => $v ) {
			$YMD = explode("-", $dates_naissance[$k] );
			if	( ( count($YMD) == 3 ) && ( (int)$YMD[0] > 1950 ) )
				$date = $YMD[2] . '/' . $YMD[1] . '/' . $YMD[0];
			else	$date = $dates_naissance[$k];
			echo '<tr><td>', $cnt, '</td><td>', $my_prefix, $k, '</td><td>', $v, '</td><td>',
			     $date, '</td><td>', $sexes[$k], '</td><td>', $statuses[$k], "</td></tr>\n";	
			$cnt++;
			}
		echo "</table></td></tr>\n";
		echo '<tr><td>', $my_place, ', le</td><td style="text-align: right">', $my_boss, '</td></tr>';
		echo "</table>";
		}
	}

?>