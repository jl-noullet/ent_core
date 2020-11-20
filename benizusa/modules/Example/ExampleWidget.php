<?php
/**
 une page HTML qui a un bouton pour se traduire en PDF...
 en deux style :
	Rosario : functions/PDF.php
	JLNstyle : acces direct au low layer: la classe Wkhtmltopdf
 */

if	( $_REQUEST['modfunc'] === 'savePDF' ) // Print PDF.
	{
	if	( isset( $_REQUEST['JLNstyle'] ) )
		{
		ob_start();	// redirect stdout to a buffer
		}
	else	$handle = PDFStart( array( 'css' => false ) );
	}
else	{	// Le contenu exclu du PDF
	$zeurl = 'Modules.php?modname=' . $_REQUEST['modname'] . '&modfunc=savePDF&_ROSARIO_PDF=1';
	// propagation d'un agument (pourrait aussi se faire par $_SESSION)
	if	( isset( $_REQUEST['qlin'] ) )
		$zeurl .= ( '&qlin=' . (int)$_REQUEST['qlin'] );
	echo '<h3>le style FORM</h3>';
	// UWAGA! methode GET ne marche pas car l'URL contient deja un '?'
	echo '<form action="' . $zeurl . '" method="POST">';
	echo '<input type="checkbox" name="JLNstyle">';
	echo '<input type="submit" value="Do the PDF" class="button-primary" />';
	echo '<hr>';
	echo '</form>';
	echo '<h3>le style LINK</h3>';
	// UWAGA! target="_blank" indispensable dans ce cas
	echo '<ul><li><a href="' . $zeurl . '" target="_blank">Do the PDF</a></li>';
	echo '<li><a href="' . $zeurl . '&JLNstyle=1" target="_blank">Do the PDF, JLNstyle</a></li></ul>';
	echo '<hr>';
	}

// le contenu a PDFiser

echo	'<style type="text/css">', "\n",
	"table.lp { width: 100%; border-collapse:collapse; }\n",
	"table.lp td { border:1px solid black; padding: 2px 4px 2px 6px; }\n",
//	".wkhtmltopdf-portrait{width:994px} .wkhtmltopdf-landscape{width:1405px}\n",	// FYI
	'</style>';

// echo '<div class="wkhtmltopdf-portrait">';
// echo '<div style="width: 700px">';
echo '<div>';
echo '<h2>C\'est IMPOSANT</h2>';
echo "<p>le path du wk : [$wkhtmltopdfPath]</p>";
echo "<p>l'host : " . $_SERVER['HTTP_HOST'] . "</p>";
echo "<p>l'URI : " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>le self : " . $_SERVER['PHP_SELF'] . "</p>";

if	( isset( $_REQUEST['JLNstyle'] ) )
	echo '<p>JLN Style is On</p>';
else	echo '<p>JLN Style is Off</p>';
// UWAGA il faut une URL absolue pour l'image
$pos = strpos( $_SERVER['PHP_SELF'], 'Modules.php' );
$root = substr( $_SERVER['PHP_SELF'], 0, $pos );
$img_URL = 'http://' . $_SERVER['HTTP_HOST'] . $root . 'assets/benisuza3.png';
echo '<p>img URL : ' . $img_URL . '<p>';
echo '<div style="text-align: center"><img src="' . $img_URL . '"></div>';

echo '<table class="lp">';		// table de hauteur parametrable, par exemple &qlin=100
$v = 1.0; $k = 1.2; $qlin = 12;
if	( isset( $_REQUEST['qlin'] ) )
	$qlin = (int)$_REQUEST['qlin'];
for	( $i = 0; $i < $qlin; $i++ )
	{
	echo '<tr><td>' . $i . '</td><td>' . $v . '</td></tr>';
	$v *= $k;
	}
echo '</table>' . "\n";
echo '</div>';

if	( $_REQUEST['modfunc'] === 'savePDF' ) // Print PDF.
	{
	if	( isset( $_REQUEST['JLNstyle'] ) )
		{
		$html  = '<!doctype html>' . "\n" . '<html><head><meta charset="UTF-8">';
		$html .= '<title>Pluft</title></head><body>' . "\n";	// <title> completement ignore ?
		$html .= ob_get_clean();
		$html .= '</body></html>';
		require_once 'classes/Wkhtmltopdf.php';
		// cree l'objet wrapper
		$wkhtmltopdf = new Wkhtmltopdf( array( 'path' => sys_get_temp_dir() ) );
		// passe les params essentiels au wrapper
		$wkhtmltopdf->setBinPath( $wkhtmltopdfPath );
		$wkhtmltopdf->setHtml( $html );
		$wkhtmltopdf->setTitle( 'Juste Imposant' );	// pas affiche par acroread, mais vu dans les props du pdf
		// execute la conversion
		// UWAGA si on met juste MODE_EMBEDDED c'est considere comme zero => MODE_DOWNLOAD
		$wkhtmltopdf->output( Wkhtmltopdf::MODE_EMBEDDED, 'imposant.pdf' );
		}
	else	PDFStop( $handle ); // Send PDF buffer to impression.
	}
