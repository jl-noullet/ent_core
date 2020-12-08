<?php

if	( $_REQUEST['modfunc'] === 'savePDF' ) // Print PDF.
	{
	ob_start();	// redirect stdout to a buffer
	}
else	{	// Le contenu interactif, exclu du PDF
	$url1 = 'Modules.php?modname=' . $_REQUEST['modname'] . '&explo';
	$url2 = $url1 . '&modfunc=savePDF&_ROSARIO_PDF=1';
	// propagation d'un argument (pourrait aussi se faire par $_SESSION)
	// $url2 .= ( '&lp_classe=' . (int)$_REQUEST['lp_classe'] );
	echo	'<style type="text/css">', "\n",
		".butgreen { cursor: pointer; padding: 6px 18px;  margin: 8px 8px; border: solid 2px; ",
		"border-color: #4D4; background: #AFA; font-weight: bold; }\n",
		".hmenu { margin: 20px };\n",
		'</style>';
	echo '<div class="hmenu">';
	echo '<a class="butgreen" href="' . $url2 . '" target="_blank">Ce document en PDF</a>';
	echo '<a class="butgreen" href="' . $url2 . '&landscape' . '" target="_blank">Ce document en PDF lanscape</a>';
	echo '</div>';
	echo '<hr>';
	}

// produire le HTML imprimable
$subject_width = 300;	// param au pif pour textes verticaux...
?>
<style type="text/css">
table.lp { border-collapse:collapse; font-family: 'Lato', sans-serif; }
table.lp td { border:1px solid black; padding: 2px 8px 2px 10px; }
.gro { padding: 90px;
<?php //if ( $_REQUEST['modfunc'] === 'savePDF' ) echo 'letter-spacing: 0.1em; ';
 ?> }
td.vv { position: relative; width: 25px; overflow: hidden  }
div.vr { position: absolute; top: <?php echo $subject_width; ?>px; left: 0;
	-webkit-transform: rotate(-90deg); -ms-transform: rotate(-90deg); transform: rotate(-90deg);
	-webkit-transform-origin: top left; -ms-transform-origin: top left; transform-origin: top left;
	width:  <?php echo $subject_width; ?>px; text-align: right; color: blue; background: #cfc }
div.vl { position: absolute; bottom: 0; left: 0; 
	-webkit-transform: rotate(-90deg); -ms-transform: rotate(-90deg); transform: rotate(-90deg);
	-webkit-transform-origin: top left; -ms-transform-origin: top left; transform-origin: top left;
	transform: rotate(-90deg); transform-origin: top left;
	width:  <?php echo $subject_width; ?>px; text-align: left; color: red; background: #fcc }
</style>

<div class="gro">
<table class="lp">
<tr>
	<td rowspan="2" class="vv"><div class="vr"><b>subject group nice and even&nbsp;</b></div></td>
	<td>diziplin</td><td>competenz</td>
</tr>

<tr><td>
<ul><li>Le div est en absolute pour que la longueur de son texte n'impacte pas la largeur de la td, car le browser 
ne sait pas prendre en compte la rotation dans le calcul de la largeur de la td, qui doit etre fixee par nous-memes</li>
<li>la width de ce div se comprend avant rotation, on doit lui donner une valeur, sinon il herite de celle de la td qui est trop petite
 (c'est le meme pb, dans l'autre sens)</li>
<li>apres rotation autour de son top left corner, on doit translater ce div vers le bas de sa largeur exactement, pour le caler dans
la td en haut a gauche.<br>ceci se fait au moyen de {top:}</li>
<li>c'est coherent avec le fait de justifier le texte en haut de la td {text-align: right}</li>
</ul>
</td><td>compet 1</td></tr>
<tr>
	<td rowspan="2" class="vv"><div class="vl"><b>subject group debilos&nbsp;</b></div></td>
	<td>diziplin</td><td>competenz</td>
</tr>
<td>
<ul><li>A l'oppose, on peut vouloir caler le div en bas de la td, alors on fait une translation {bottom: 0}</li>
<li>C'est coherent avec le fait de justifier le texte en bas de la td {text-align: left}</li>
<li>petit glitch: le bottom etant aligne avant rotation, finalement le div est cale plus haut, de la valeur de sa hauteur
avant rotation...</li>
</td><td>compet 2</td></tr>
</table>
Dans les 2 cas on doit fixer <b>au pif</b> la width du div, t.q. il contienne tout le texte mais ne depasse pas de la td...<br>
s'il est trop petit son texte va wrapper, s'il est trop grand ce n'est pas trop grave, il va baver hors de la td
ce qu'on peut eviter avec un { overflow: hidden } (evidemment on lui donnera un background transparent)
</div>

<?php
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
