<?php

// affichage erreur
function mostra_erro( $tbuf ) {
	echo '<p class="lerreur">' . $tbuf . '</p>';
	}

function mostra_fatal( $tbuf ) {
	require_once('head.php');
	echo '<p class="lerreur">Erreur: ', $tbuf, '</p>', 
	     '<p> <a href="', $_SERVER['PHP_SELF'], '">Retour</a></p></body></html>';
	exit();
	}

// be comme big-endian je suppose
function input_date_be( $prefix, $y0, $y1, $yy, $mm, $dd ) {
	global $monthname;
	echo "<select name=\"{$prefix}_y\">";
	for	( $y = $y0; $y < $y1; $y++ )
		{
		echo "<option value=\"$y\"";
		if	( $y == $yy )
			echo ' selected';
		echo ">$y</option>";
		}
	echo "</select>-<select name=\"{$prefix}_m\">";
	for	( $m = 0; $m < 12; $m++ )
		{
		echo "<option value=\"$m\"";
		if	( $m == $mm )
			echo ' selected';
		echo ">$monthname[$m]</option>";
		}
	echo "</select>-<select name=\"{$prefix}_d\">";
	for	( $d = 1; $d <= 31; $d++ )
		{
		echo "<option value=\"$d\"";
		if	( $d == $dd )
			echo ' selected';
		echo ">$d</option>";
		}
	echo '</select>';
	}

class database
{
public $server;
public $base;
public $user;
public $pass;

public $conn;

function connect() {	// commande de connexion au serveur SQL 'p:' pour persistent connection
	// $this->conn = mysqli_connect( 'p:' . $this->server, $this->user, $this->pass, $this->base );
	$this->conn = mysqli_connect( $this->server, $this->user, $this->pass, $this->base );
        if (!$this->conn) { mostra_fatal("échec connexion serveur et base"); }
	}

function close() {
	$this->conn->close();
	}
}

class formit
{
public $desc;
public $type='T'; 	//  Text, Link, Check, Image, Document, Readonly, Hidden
public $topt=1;
public $indexflag=false;
public $val;
public $check;		// 0 = unchecked, -1 = err, 1 = checked ok

function __construct( $tdesc, $ttype, $ttopt, $tindexflag ) {
	$this->desc = $tdesc;	// nom long, avec accents, espaces etc
	$this->type = $ttype;	// type pour longage
	$this->topt = $ttopt;	// option specifique du type
	$this->indexflag = $tindexflag;	// flag pour index
	$this->check = 0;
	}
}

class form
{
public $itsa;		// array de formits
public $uploadpath;	// pour les fichiers de donnees
public $checkreport;
// fonction pour creer un item (normalement appelee dans def.php) 
// $tid est la clef qui va servir a indexer le formit - cela peut et doit etre un nom court
function add( $tid, $tdesc, $ttype, $ttopt=1, $tindexflag=false ) {
	$this->itsa[$tid] = new formit( $tdesc, $ttype, $ttopt, $tindexflag );
	}
// effacer les valeurs
function clear() {
	foreach ($this->itsa as $k => $v)
		$v->val = NULL;
	}

// show_form(): affiche l'objet form sous forme de form HTML editable, compatible avec post2form_full()
// 3 options traitees independamment :
//	$opcode : va servir de suffixe pour le 'name' du bouton submit principal, et pour
//	le nom de classe CSS du bouton, et d'index dans $label[] pour la 'value' du bouton
//	(exemples : "add", "mod", "kill")
//	N.B. le second bouton est cree automatiquement avec l'opcode "abort"
//	N.B. l'index d'entree dans $_POST[] sera le 'name' du bouton, constitue du nom de la form
//	     suivi d'un '_' et de l'opcode, la 'value' du bouton est juste pour display 
//	$blankflag : tous les items sont vides, sinon form initialisees avec valeurs lues dans $this->itsa
//	$indixflag = 0 : indix totalement omis
//	$indixflag = 1 : indix inclus mais invisible
//	$indixflag = 2 : indix visible
function show_form( $opcode, $blankflag, $indixflag ) {
	global $label;
	echo "<form action=\"{$_SERVER['PHP_SELF']}\" method=\"POST\">\n";
	// eventuel "hidden input" en dehors de la table
	if	( $indixflag == 1 )
		{
		$laval = (int)$this->itsa['indix']->val;
		echo "<input type=\"hidden\" name=\"indix\" id=\"indix\" value=\"{$laval}\">";
		}
	echo "<table>\n";
	foreach ($this->itsa as $k => $v)
		{
		// evaluation visibilite
		if	( $k == 'indix' )
			$show = ( $indixflag == 2 );
		else	$show = ( $v->type != 'H' );
		if	( $show )	// visible
			{
			// traitement commun a toutes les lignes
			if	( ( $blankflag ) && ( $k != 'indix' ) )						// valeur
				$laval = '';
			else	$laval = htmlspecialchars( $v->val, ENT_COMPAT, 'UTF-8', true );
			echo "<tr><td class=\"ag\">$v->desc</td><td>";			// description
			// traitement par type
			if	( $v->type == 'R' )	// Readonly (implicitement text 1 ligne)
				{
				echo "<input class=\"roin\" type=\"text\" name=\"$k\" id=\"$k\" readonly value=\"{$laval}\">";
				}
			else if	( $v->type == 'T' )	// Text
				{
				if	( $v->topt == 1 )
					echo "<input class=\"textin\" type=\"text\" name=\"$k\" id=\"$k\" value=\"{$laval}\">";
				else	echo "<textarea class=\"areain\" name=\"$k\" id=\"$k\" rows=\"$v->topt\" >{$laval}</textarea>";
				}
			else if	( $v->type == 'S' )	// Dropdown list, typiquement $k2 = index de l'item, $v2 = nom affichable
				{
				echo "<select name=\"$k\">";
				if	( is_array( $v->topt ) )
					{
					foreach ($v->topt as $k2 => $v2)
						{
						echo "<option value=\"$k2\"";
						if	( $v->val == $k2 )
							echo " selected";
						echo ">$v2</option>";
						}
					}
				echo "</select>";
				}
			else if	( $v->type == 'D' )	// Date (big endian)
				{
				if	( $laval )	// unix time
					$ladate = getdate( $laval );
				else	$ladate = getdate( 946731600 );
				$y = $ladate["year"];
				input_date_be( 'date_n', $y-20, $y+20, $y, $ladate["mon"]-1, $ladate["mday"] );
				}
			echo "</td></tr>\n";
			}
		}	// fin foreach
	echo '<tr class="lastrow"><td colspan="2" class="ar"><input type="submit" class="bout', $opcode,
	     '" name="', $this->nom, '_', $opcode, '" value="', $label[$opcode], "\"></td></tr>\n";
	echo '<tr class="lastrow"><td colspan="2" class="ar"><input type="submit" class="boutabt',
	     '" name="', $this->nom, '_abt',       '" value="', $label['abort'], "\"></td></tr>\n";
	echo "</table>\n";
	echo "</form>";
	}

// form2tr(): affiche l'objet form sous forme de ligne de table HTML
//	$boutflag commande l'affichage de boutons edit (poids 1) et kill (poids 2)
//	Le bit de poids 4 permet de supprimer le </tr> a la fin, pour ajouter des boutons custom
//	les boutons engendrent des <a> avec ?op=nomform_edit&ind=xx ou ?op=nomform_kill&ind=xx
//	$indixflag = 0 : indix totalement omis
//	$indixflag = 2 : indix visible
//	$maxlen : limite de longueur pour les textes
function form2tr( $indixflag, $boutflag, $maxlen ) {
	echo '<tr>';
	foreach ($this->itsa as $k => $v)
		{
		// evaluation visibilite
		if	( $k == 'indix' )
			$show = ( $indixflag == 2 );
		else	$show = ( $v->type != 'H' );
		if	( $show )	// visible
			{
			// traitement commun a toutes les lignes
			echo '<td>';
			// traitement par type
			if	(
				( $v->type == 'R' ) ||	// Readonly (implicitement text 1 ligne)
				( $v->type == 'T' )	// Text
				)
				{
				$laval = htmlspecialchars( $v->val, ENT_COMPAT, 'UTF-8', true );
				if	( strlen($laval) > $maxlen )
					$laval = substr( $laval, 0, ($maxlen-3) ) . '...';
				echo $laval;
				}
			else if	( $v->type == 'S' )	// Dropdown list, $v->val = index de l'item dans $v->topt
				{
				if	( is_array( $v->topt ) )
					{
					$laval = htmlspecialchars( $v->topt[$v->val], ENT_COMPAT, 'UTF-8', true );
					echo $laval;
					}
				}
			else if	( $v->type == 'D' )	// Date (big endian)
				{
				$laval = date( 'Y-m-d', $v->val );
				echo $laval;
				}
			echo '</td>';
			}
		}	// fin foreach
	if	( $boutflag & 3 )
		{
		global $label;
		echo '<td>';
		$indix = $this->itsa['indix']->val;
		if	( $boutflag & 1 )
			echo "<a href=\"{$_SERVER['PHP_SELF']}?op={$this->nom}_edit&ind={$indix}\"><img src=\"img/edit.png\" title=\"{$label['edit']}\"></a> ";
			//echo '<a href="', $_SERVER['PHP_SELF'], '?op=', $this->nom, '_edit&ind=', $indix, '"><img src="img/edit.png" title="', $label['edit'], '"></a> ';
		if	( $boutflag & 2 )
			echo "<a href=\"{$_SERVER['PHP_SELF']}?op={$this->nom}_kill&ind={$indix}\"><img src=\"img/kill.png\" title=\"{$label['kill']}\"></a>";
		echo '</td>';
		}
	if	( ( $boutflag & 4 ) == 0 )
		echo "</tr>\n";
	}

// form2th(): affiche une ligne de header de table HTML pour servir avant form2tr()
//	$boutflag commande l'affichage de boutons edit et kill
//	$indixflag = 0 : indix totalement omis
//	$indixflag = 2 : indix visible
function form2th( $indixflag, $boutflag ) {
	global $label;
	echo '<tr>';
	foreach ($this->itsa as $k => $v)
		{
		// evaluation visibilite
		if	( $k == 'indix' )
			$show = ( $indixflag == 2 );
		else	$show = ( $v->type != 'H' );
		if	( $show )	// visible
			{
			echo "<td>$v->desc</td>";	// description
			}
		}	// fin foreach
	if	( $boutflag & 3 )
		echo "<th>Actions</th>";
	if	( ( $boutflag & 4 ) == 0 )
		echo "</tr>\n";
	}

// lire les valeurs d'une ligne de la BD, les copier dans la form
// retourne false si indix non trouve
function db2form( $db, $table, $indix ) {
	$sqlrequest = "SELECT * FROM `{$table}` WHERE `indix` = '{$indix}';";
	$result = $db->conn->query( $sqlrequest );
	// echo "<p>---{$sqlrequest}---</p>";
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($db->conn) );
	else if ( $row = mysqli_fetch_assoc($result) )
		{
		// print_r( $row );
		foreach ($this->itsa as $k => $v)
			{
			if	( isset($row[$k]) )
				$v->val = $row[$k];
			}
		}
	else	return FALSE;	// mostra_fatal( "table {$table} : clef manquante {$indix}" );
	return TRUE;
	}
// lire les valeurs d'un POST, les copier dans la form, attend TOUS les items de la form
// sauf eventuellement `indix`
// pas de filtrage de secu a ce niveau
function post2form_full( $skipindix ) {
	foreach ($this->itsa as $k => $v)
		{
		if	( $v->type == 'D' )	// Date en 3 valeurs, cf function input_date_be()
			{
			if	( isset( $_POST[$k.'_y'] ) && isset( $_POST[$k.'_m'] ) && isset( $_POST[$k.'_d'] ) )
				$v->val = mktime( 13, 0, 0, $_POST[$k.'_m']+1, $_POST[$k.'_d'], $_POST[$k.'_y'] );
			else	mostra_fatal( "date icomplete, item $k dans le formulaire" );
			}
		else if	( ( !$skipindix ) || ( $k != 'indix' ) )
			{			// simple valeur
			if	( isset( $_POST[$k] ) )
				$v->val = $_POST[$k];
			else	mostra_fatal( "manque item $k dans le formulaire" );
			}
		}
	}
// copier toutes les valeurs d'une form dans une ligne existante de la table, `indix` doit exister
// filtrage injection SQL ici (provisoirement addslashes)
function form2db_update_full( $db, $table ) {
	$sqlrequest = "UPDATE `{$table}` SET ";
	$i = -1;	// pour forcer erreur si indix non defini
	$prem = TRUE;	// pour gerer les virgules
 	foreach ($this->itsa as $k => $v)
		{
		if	( $k == 'indix' )
			$i = (int)$v->val;
		else	{
			$zeval = addslashes( $v->val );
			if	( $prem )
				$prem = FALSE;
			else	$sqlrequest .= ' ,';
			$sqlrequest .= "{$k} = '{$zeval}'";
			}	
		}
	$sqlrequest .= " WHERE `indix` = {$i}";
	// echo "<p>---{$sqlrequest}---</p>";
	$result = $db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($db->conn) );
	}
// creer une ligne de la table avec toutes les valeurs d'une form,
// $skipindix pour omettre indix si on veut profiter de l'auto-increment
// dans ce cas, retourne la valeur d'indix generee automatiquement 
// filtrage injection SQL ici (provisoirement addslashes)
function form2db_insert_full( $db, $table, $skipindix ) {
	$sqlrequest = "INSERT INTO `{$table}` SET ";
	$prem = TRUE;	// pour gerer les virgules
 	foreach ($this->itsa as $k => $v)
		{
		if	( $k == 'indix' )
			{
			if	( !$skipindix )
				{
				$zeval = (int)$v->val;
				if	( $prem )
					$prem = FALSE;
				else	$sqlrequest .= ' ,';
				$sqlrequest .= "{$k} = '{$zeval}'";
				}
			}
		else	{
			$zeval = addslashes( $v->val );
			if	( $prem )
				$prem = FALSE;
			else	$sqlrequest .= ' ,';
			$sqlrequest .= "{$k} = '{$zeval}'";
			}	
		}
	// echo "<p>---{$sqlrequest}---</p>";
	$result = $db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($db->conn) );
	if	( $skipindix )
		{	// LAST_INSERT_ID() est "per connection", i.e. on ne reference pas la table !!
		$sqlrequest = 'SELECT LAST_INSERT_ID()';
		$result = $db->conn->query( $sqlrequest );
		if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($db->conn) );
		if	( $row = mysqli_fetch_assoc($result) )
			{
			// print_r($row);
			return $row['LAST_INSERT_ID()'];
			}
		}
	return -1;
	}

// creer la table pour une form
/*
0123456789----------0123456789----------0123456789----------0123456789-----80-->0123456789----100-->0123456789----120-->
*/
function mk_table( $db, $table, $dropflag, $autoflag=true ) {
	if	( $dropflag )
		{
		$sqlrequest = "DROP TABLE IF EXISTS `{$table}`";
		$result = $db->conn->query( $sqlrequest );
		if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($db->conn) );
		}
	$sqlrequest = "CREATE TABLE `{$table}` ( ";
	foreach ($this->itsa as $k => $v) {		// noms et type SQL des colonnes
		$colu = "`$k` ";
		if	( $k == 'indix' )
			$colu .= ($autoflag?'INT NOT NULL AUTO_INCREMENT':'INT NOT NULL');
		else if	( $v->type == 'S' )
			$colu .= 'VARCHAR(32)';
		else if	( $v->type == 'D' )
			$colu .= 'BIGINT';
		else	{	// types texte : 'T', 'R', 'H'
			if	( $v->topt == 1 )
				$colu .= 'VARCHAR(128)';
			else	$colu .= 'TEXT';
			}
		$colu .= ', ';
		$sqlrequest .= $colu;
		}  // foreach
	$sqlrequest .= 'PRIMARY KEY (`indix`)';		// pas de virgule ici, cela pourrait etre la fin
	foreach ($this->itsa as $k => $v) {		// indexes optionnels
		if	( $v->indexflag )
			{
			$sqlrequest .= ', ';	// maniere d'eviter une virgule a la fin
			$sqlrequest .= "INDEX(`{$k}`)";
			}
		}  // foreach
	$sqlrequest .= ' ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
	// CHARSET case-sensitive : utf8mb4_bin
	// CHARSET case-insensitive : utf8mb4_general_ci, utf8mb4_unicode_ci
	echo "<p>$sqlrequest</p>\n";
	$result = $db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($db->conn) );
	}

} // class form



class menuitem
{
public $url;	// relatif a la racine du serveur (sauf si externe)
public $desc;
function __construct( $turl, $tdesc ) {
	$this->url = $turl;
	$this->desc = $tdesc;	// nom long, avec accents
	}
}

class menu
{
public $itsa;	// array de menuitem

function add( $turl, $tdesc ) {
	$this->itsa[] = new menuitem( $turl, $tdesc );
	}
function display() {
	foreach ($this->itsa as &$v) {
		if   ( $v->url == $_SERVER['PHP_SELF'] ) 
		     echo "<a class=\"current\" href=\"$v->url\">$v->desc</a>\n";
		else echo "<a href=\"$v->url\">$v->desc</a>\n";
		}
	}
}

?>
