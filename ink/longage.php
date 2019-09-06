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
public $val;
public $check;		// 0 = unchecked, -1 = err, 1 = checked ok

function __construct( $tdesc, $ttype, $ttopt ) {
	$this->desc = $tdesc;	// nom long, avec accents, espaces etc
	$this->type = $ttype;	// type pour longage
	$this->topt = $ttopt;	// option specifique du type
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
function add( $tid, $tdesc, $ttype, $ttopt ) {
	$this->itsa[$tid] = new formit( $tdesc, $ttype, $ttopt );
	}
/* function dump()
	{
	echo "<table border=1 cellpadding=3>\n";
	echo "<tr><td>id</td><td>desc</td><td>type</td><td>lignes</td></tr>\n";
	foreach ($this->itsa as $k => $v) {
		echo "<tr><td>$k</td><td>$v->desc</td><td>$v->type</td><td>$v->topt</td></tr>\n";
		}
	echo "</table>\n";
	} */

// option :
//	$addflag = 1  : form vierge, bouton submit avec prefixe "add_" en vue creation d'une ligne (INSERT)
//	$addflag = 0  : form initialisees avec valeurs lues dans $this->itsa, , bouton submit avec prefixe "mod_" en vue update
//	$addflag = -1 : idem plus bouton submit avec prefixe "kill_" en vue suppression
function show_form( $addflag=0 ) {
	global $label;
	echo "<form action=\"{$_SERVER['PHP_SELF']}\" method=\"POST\">\n";
	echo "<table>\n";
	foreach ($this->itsa as $k => $v)
		{
		// traitement commun a toutes les lignes affichees
		if	( $v->type != 'H' )	// not Hidden
			{
			echo "<tr><td class=\"ag\">$v->desc";			// description
			echo '</td><td>';
			if	( $addflag > 0 )				// valeur
				$laval = '';
			else	$laval = htmlspecialchars( $v->val, ENT_COMPAT, 'UTF-8', true );
			}
		// traitement par type
		if	( ( $v->type == 'R' ) && ( $addflag <= 0 ) )	// Readonly (implicitement text 1 ligne)
			echo "<input class=\"roin\" type=\"text\" name=\"$k\" id=\"$k\" readonly value=\"{$laval}\"></td>";
		else if	( $v->type == 'T' )	// Text
			{
			if	( $v->topt == 1 )
				echo "<input class=\"textin\" type=\"text\" name=\"$k\" id=\"$k\" value=\"{$laval}\"></td>";
			else	echo "<textarea class=\"areain\" name=\"$k\" id=\"$k\" rows=\"$v->topt\" >{$laval}</textarea></td>";
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
						echo "  selected";
					echo ">$v2</option>";
					}
				}
			echo "</select>";
			}
		if	( $v->type != 'H' )	// not Hidden
			echo "</tr>\n";
		}	// fin foreach
	
	if	( $addflag < 0 )
		echo "<tr class=\"lastrow\"><td colspan=\"2\" class=\"ar\"><input type=\"submit\" class=\"boutkill\" name=\"",
		     'kill_', $this->nom, "\" value=\"", $label['kill'], "\"></td></tr>\n";
	else	echo "<tr class=\"lastrow\"><td colspan=\"2\" class=\"ar\"><input type=\"submit\" class=\"boutfini\" name=\"",
		     ( $addflag > 0 ) ? 'add_' : 'mod_', $this->nom, "\" value=\"", $label['save'], "\"></td></tr>\n";
	echo "<tr class=\"lastrow\"><td colspan=\"2\" class=\"ar\"><input type=\"submit\" class=\"boutabt\" name=\"",
		     'abt_', $this->nom, "\" value=\"", $label['abort'], "\"></td></tr>\n";
	echo "</table>\n";
	echo "</form>";
	}
}

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
	echo "<ul class=\"lemenu\">\n";
	foreach ($this->itsa as &$v) {
		if   ( $v->url == $_SERVER['PHP_SELF'] ) 
		     echo "<li class=\"current\"><a href=\"$v->url\">$v->desc</a></li>\n";
		else echo "<li class=\"\"><a href=\"$v->url\">$v->desc</a></li>\n";
		}
	echo "</ul>\n";
	}
}

?>
