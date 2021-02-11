<?php
/**
  les pourcentages de recouvrement en fonction des classes
  (les classes en abscisses et les pourcentages de recouvrement en ordonnées)
  toujours organisés en tranches <-- cela, ce n'est pas possible
 */

require_once( 'LP_func.php' );
$my_school = UserSchool();
$my_year = UserSyear();


// tableaux indexes par id de school_gradelevels
$class_names = array();
$class_effectif = array();
$class_totalfee = array();
$class_totalpaid = array();

// preparer boucle sur les classes
$sqlrequest = 'SELECT id, short_name FROM school_gradelevels WHERE school_id=' . $my_school . ' ORDER BY short_name'; // DESC';
$result = db_query( $sqlrequest, true );
while	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
	{
	if	( $row['short_name'][0] != '_' )
		$class_names[$row['id']] = $row['short_name'];
	}
// boucle sur les classes pour acquisition des donnees
foreach	( $class_names as $lp_classe => $s_name )
	{
	$class_name = ''; $class_short_name = '';
	// des arrays tous indexes par le meme index arbitraire
	$my_students = array();
	LP_liste_classe( $lp_classe, $class_name, $class_short_name, $my_students );
	$class_effectif[$lp_classe] = count($my_students);
	if	( count($my_students) )
		{
		$class_totalfee[$lp_classe] = 0;
		$class_totalpaid[$lp_classe] = 0;
		// calculer les totaux de la classe
		foreach	( $my_students as $k => $v ) {
			// les appels de fonds
			$sqlrequest = 'SELECT amount FROM billing_fees WHERE syear=\'' . $my_year . '\' AND student_id=' . $v;
			$result = db_query( $sqlrequest, true );
			while	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
				{
				$a = (int)$row['amount'];
				$class_totalfee[$lp_classe] += $a;
				}
			// les paiements
			$sqlrequest = 'SELECT amount FROM billing_payments WHERE syear=\'' . $my_year . '\' AND student_id=' . $v;
			$result = db_query( $sqlrequest, true );
			// echo $sqlrequest, '<br>';
			while	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
				{
				$a = (int)$row['amount'];
				$class_totalpaid[$lp_classe] += $a;
				}
			}
		}
	}


if	( $_REQUEST['modfunc'] === 'savePDF' ) // Print PDF.
	{
	ob_start();	// redirect stdout to a buffer
	}
else	{	// Le contenu interactif, exclu du PDF
	$url1 = 'Modules.php?modname=' . $_REQUEST['modname'];
	$url2 = $url1 . '&modfunc=savePDF&_ROSARIO_PDF=1';
	echo	'<style type="text/css">', "\n",
		".butgreen { cursor: pointer; padding: 6px 18px;  margin: 8px 8px; border: solid 2px; ",
		"border-color: #4D4; background: #AFA; font-weight: bold; }\n",
		".hmenu { margin: 20px };\n",
		'</style>';
	echo '<div class="hmenu">';
	// le style LINK, target="_blank" indispensable dans ce cas
	echo '<a class="butgreen" href="' . $url2 . '" target="_blank">Ce document en PDF</a>';
	// echo '<a class="butgreen" href="' . $url1 . '">Retour au choix de la classe</a>';
	echo '</div>';
	echo '<hr>';
	}
// echo '<pre>'; var_dump( $class_totalfee ); echo '</pre>';

// produire le HTML

// javascript commun
$le_script = 'modules/Notation/LP_func.js?1002';
if	( isset( $_REQUEST['_ROSARIO_PDF'] ) )
	$le_script = 'file:///' . $RosarioPath . $le_script;

// css
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

$today = date( "d-m-Y" );
echo $html_css, '<script src="' . $le_script . '"></script>', '<div id=pdfpage>';
echo '<h2>Statistiques des frais de scolarité au ', $today, '</h2>';

echo '<p>Pourcentage de recouvrement par classe</p>';

// graphique en barres
echo '<canvas id="myCanvas1" width="800" height="250" style="border: 0;"></canvas><br><br>';
// preparer les arrays en javascript
$js_names ='['; $js_vals = '['; $js_colors = '[';
foreach	( $class_names as $k => $v )
	{
	$js_names .= "'$v',";
	if	( $class_totalfee[$k] > 0 )
		$pourcent = sprintf( "%.1f,", 100.0 * ($class_totalpaid[$k] / $class_totalfee[$k]) );
	else	$pourcent = "0,";
	$js_vals .= $pourcent;
	if	( $pourcent < 25 )
		$color = '#F44';
	else if	( $pourcent < 50 )
		$color = '#FB0';
	else if	( $pourcent < 80 )
		$color = '#0E0';
	else	$color = '#08F';
	$js_colors .= "'$color',";
	}
$js_names .= '],'; $js_vals .= '],'; $js_colors .= '],';
echo '<script>var canvas = document.getElementById("myCanvas1"); var ctx = canvas.getContext("2d");';
echo 'LP_N_bars( ctx, 800, 250, ', $js_names, $js_vals, $js_colors, '10, 100.1, "%" );</script>';
	
// echo 'test{', LP_thousands(-450000000), '}<br>';

// resultats sous forme de table
echo '<table class="lp">';
echo '<tr class="ce"><td>Classe</td><td>Effectif</td><td>Total facturé</td><td>Total payé</td><td><b>Reste dû</b></td><td>% payé</td></tr>';
	
// boucle sur les classes pour presentation en tableau
foreach	( $class_names as $k => $v )
	{
	if	( $class_totalfee[$k] > 0 )
		$pourcent = sprintf( "%.1f%%", 100.0 * ($class_totalpaid[$k] / $class_totalfee[$k]) );
	else	$pourcent = "";

	echo '<tr><td class="le">', $v, '</td><td>', $class_effectif[$k], '</td><td>',
		LP_thousands($class_totalfee[$k]), '</td><td>', LP_thousands($class_totalpaid[$k]), '</td><td><b>', LP_thousands($class_totalfee[$k] - $class_totalpaid[$k]), '</td><td>', $pourcent, '</b></td></tr>';
	}
echo '</table></div>';

// convertir en PDF s'il y a lieu
if	( $_REQUEST['modfunc'] === 'savePDF' ) // Print PDF.
	{
	$html  = '<!doctype html>' . "\n" . '<html><head><meta charset="UTF-8">';
	$html .= '<title>' . 'stat_frais_scolarite' . '</title></head><body>' . "\n";	// <title> completement ignore ?
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
	$wkhtmltopdf->setTitle( utf8_decode('stat_frais_scolarite') );
	// execute la conversion
	// UWAGA si on met juste MODE_EMBEDDED c'est considere comme zero qui est MODE_DOWNLOAD
	$wkhtmltopdf->output( Wkhtmltopdf::MODE_EMBEDDED, utf8_decode('stat_frais_scolarite') . '.pdf' );
	}

?>

