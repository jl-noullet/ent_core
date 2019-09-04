<?php

class school
{
public $table_eleves;
public $table_classes;

// cree toutes les tables d'une school de base
// rend NULL ou message d'erreur
function create_tables() {
	global $db1;
	$sqlrequest = "DROP TABLE IF EXISTS `{$this->table_eleves}`";
	$result = $db1->conn->query( $sqlrequest );
	if	(!$result) return "erreur " . $sqlrequest . "<br>" . mysqli_error($db1->conn);
	$sqlrequest = "CREATE TABLE `$this->table_eleves`
		( `indix` INT NOT NULL AUTO_INCREMENT, `nom` VARCHAR(64), `prenom` VARCHAR(64), `date_n` DATE, `classe` VARCHAR(64),
		PRIMARY KEY (`indix`), INDEX(`nom`), INDEX(`classe`) )
		ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";  
	$result = $db1->conn->query( $sqlrequest );
	if	(!$result) return "erreur " . $sqlrequest . "<br>" . mysqli_error($db1->conn);
	$sqlrequest = "DROP TABLE IF EXISTS `{$this->table_classes}`";
	$result = $db1->conn->query( $sqlrequest );
	if	(!$result) return "erreur " . $sqlrequest . "<br>" . mysqli_error($db1->conn);
	$sqlrequest = "CREATE TABLE `$this->table_classes`
		( `indix` INT, `nom` VARCHAR(64),
		PRIMARY KEY (`indix`), INDEX(`nom`) )
		ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";  
	$result = $db1->conn->query( $sqlrequest );
	if	(!$result) return "erreur " . $sqlrequest . "<br>" . mysqli_error($db1->conn);
	}

function form_add_eleve() {
	global $form1;
	$form1->show_form( 1 );
	}

// rend NULL ou message d'erreur
function form_edit_eleve( $indix, $killflag=0 ) {
	global $form1;
	global $db1;
	$indix = (int)$indix;
	$sqlrequest = "SELECT * FROM `$this->table_eleves` WHERE `indix` = '{$indix}';";
	$result = $db1->conn->query( $sqlrequest );
	if	(!$result) return "erreur base de donnees " . $sqlrequest;
	else if ( $row = mysqli_fetch_assoc($result) )
		{
		foreach ($form1->itsa as $k => $v)
			{
			if	( isset($row[$k]) )
				$v->val = $row[$k];
			}
		}
	else	return "erreur base de donnees clef manquante " . $lenom;
	$form1->show_form( $killflag ? -1 : 0 );
	}

function form_find_eleve() {
	global $msug;
	echo "<form action=\"{$_SERVER['PHP_SELF']}\" method=\"POST\">\n";
	echo "<table>\n";
	echo "<tr><td align=left>Nom</td><td><input class=\"textin\" type=\"text\" name=\"nom\" id=\"nom\" value=\"\"></td></tr>\n";
	echo "<tr><td align=left>ou Prénom</td><td><input class=\"textin\" type=\"text\" name=\"prenom\" id=\"prenom\" value=\"\"></td></tr>\n";
	echo "<tr class=\"lastrow\"><td colspan=\"2\" align=\"right\"><input type=\"submit\" class=\"boutfini\" name=\"find_eleve\" value=\"{$msug['find']}\"></td></tr>\n";
	echo "</table>\n";
	echo "</form>";
	}

// rend NULL ou message d'erreur
// le matricule sera genere automatiquement (auto-increment)
function add_eleve( $nom, $prenom, $date, $classe ) {
	global $db1;
	$sqlrequest = "INSERT INTO `{$this->table_eleves}` (`nom`, `prenom`, `date_n`, `classe` ) " .
			"VALUES ('$nom', '$prenom', '$date', '$classe' );";
	$result = $db1->conn->query( $sqlrequest );
	if	(!$result) return "erreur " . $sqlrequest . "<br>" . mysqli_error($db1->conn);
	}

// rend NULL ou message d'erreur
function add_classe( $index, $nom ) {
	global $db1;
	$sqlrequest = "INSERT INTO `{$this->table_classes}` (`indix`, `nom`) VALUES ('$index', '$nom');";
	// echo $sqlrequest, '<br>';
	$result = $db1->conn->query( $sqlrequest );
	if	(!$result) return "erreur " . $sqlrequest . "<br>" . mysqli_error($db1->conn);
	}

// rend bool ou NULL ou message d'erreur
function exist_classe( $index ) {
	global $db1;
	$sqlrequest = "SELECT COUNT(*) FROM `{$this->table_classes}` WHERE `indix` = $index;";
	$result = $db1->conn->query( $sqlrequest );
	if	(!$result) return "erreur " . $sqlrequest . "<br>" . mysqli_error($db1->conn);
	if	( $row = mysqli_fetch_assoc($result) )
		{
		// echo $index, "~~"; print_r($row); echo '<br>';
		$i = $row['COUNT(*)'];
		if	( $i == 0 ) return FALSE;
		else if	( $i == 1 ) return TRUE;
		}
	}

// rend NULL ou message d'erreur
function mod_eleve( $mat, $nom, $prenom, $date, $classe ) {
	global $db1;
	$sqlrequest = "UPDATE `{$this->table_eleves}` SET `nom` = '$nom', `prenom` = '$prenom', `date_n` = '$date', `classe` = '$classe' WHERE `indix` = $mat;";
	$result = $db1->conn->query( $sqlrequest );
	if	(!$result) return "erreur " . $sqlrequest . "<br>" . mysqli_error($db1->conn);
	}

// rend NULL ou message d'erreur
function kill_eleve( $mat ) {
	global $db1;
	$sqlrequest = "DELETE FROM `{$this->table_eleves}` WHERE `indix` = $mat;";
	$result = $db1->conn->query( $sqlrequest );
	if	(!$result) return "erreur " . $sqlrequest . "<br>" . mysqli_error($db1->conn);
	}

// fournit une liste des classes (par reference), rend NULL ou message d'erreur
function extract_liste_classes( &$liste_classes ) {
	global $db1;
	$sqlrequest = "SELECT `indix`, `nom` FROM `{$this->table_classes}`";
	$result = $db1->conn->query( $sqlrequest );
	if	(!$result) return "erreur " . $sqlrequest . "<br>" . mysqli_error($db1->conn);
	$liste_classes = array();
	while	( $row = mysqli_fetch_assoc($result) )
		{
		// print_r($row);
		$i = $row['indix'];
		$liste_classes[$i] = $row['nom'];
		}
	}

// extrait de la liste des eleves les effectifs des classes (par reference), rend NULL ou message d'erreur
// donne aussi l'effectif total
function extract_effectifs_classes( &$effect_classes, &$total ) {
	global $db1;
	$sqlrequest = "SELECT `classe`, COUNT(*) FROM `{$this->table_eleves}` GROUP BY `classe`";
	$result = $db1->conn->query( $sqlrequest );
	if	(!$result) return "erreur " . $sqlrequest . "<br>" . mysqli_error($db1->conn);
	$total = 0;
	$effect_classes = array();
	while	( $row = mysqli_fetch_assoc($result) )
		{
		// print_r($row);
		$cnt = $row['COUNT(*)'];
		$effect_classes[ $row['classe'] ] = $cnt;
		$total += $cnt; 
		}
	}

// rend NULL ou message d'erreur
function show_liste_classes() {
	$this->extract_liste_classes( $liste );
	$this->extract_effectifs_classes( $effectifs, $total );
	echo '<table><tr><td>classe</td><td>effectif</td></tr>';
	$self = $_SERVER['PHP_SELF'] . '?c=';
	foreach ($liste as $k => $v)
		{
		$e = isset($effectifs[$k])?$effectifs[$k]:0;
		echo "<tr><td><a href=\"{$self}{$k}\">{$v}</a></td><td>{$e}</td></tr>";
		}
	echo '</table>';
	echo "Total $total eleves<br>";
	}

// rend NULL ou message d'erreur
function show_1_classe( $classe ) {
	global $db1;
	$c = (int)$classe;
	$sqlrequest = "SELECT `indix`, `nom` FROM `{$this->table_classes}` WHERE `indix` = {$c};";
	$result = $db1->conn->query( $sqlrequest );
	if	(!$result) return "erreur " . $sqlrequest . "<br>" . mysqli_error($db1->conn);
	if	( $row = mysqli_fetch_assoc($result) )
		$nom_classe = $row['nom'];
	else	$nom_classe = "??";
	$sqlrequest = "SELECT `indix`, `nom`, `prenom`, `date_n` FROM `{$this->table_eleves}`" .
			"WHERE `classe` = '$c' ORDER BY `nom`";
	$result = $db1->conn->query( $sqlrequest );
	if	(!$result) return "erreur " . $sqlrequest . "<br>" . mysqli_error($db1->conn);
	echo '<table><tr><td>matricule</td><td>nom</td><td>prenom</td><td>date de naissance</td><td>classe</td><td>commande</td></tr>';
	$self = $_SERVER['PHP_SELF'] . '?';
	while	( $row = mysqli_fetch_assoc($result) )
		{
		$mat    = $row['indix'];
		$nom    = $row['nom'];
		$prenom = $row['prenom'];
		$date   = $row['date_n'];
		$classe = $nom_classe;
		echo "<tr><td>$mat</td><td>$nom</td><td>$prenom</td><td>$date</td><td>$classe</td><td>",
		"<a href=\"{$self}e={$mat}\"><img src=\"img/edit.png\" title=\"Editer\"></a> ",
		"<a href=\"{$self}k={$mat}\"><img src=\"img/kill.png\" title=\"Supprimer\"></a>",
		"</td></tr>";
		}
	echo '</table>';
	}

// rend NULL ou message d'erreur
function show_found_eleves( $nom, $prenom ) {
	global $db1;
	if	( $nom )
		$sqlrequest = "SELECT `indix`, `nom`, `prenom`, `date_n`, `classe` FROM `{$this->table_eleves}`" .
			"WHERE `nom` = '$nom' ORDER BY `prenom`";
	else if	( $prenom )
		$sqlrequest = "SELECT `indix`, `nom`, `prenom`, `date_n`, `classe` FROM `{$this->table_eleves}`" .
			"WHERE `prenom` = '$prenom' ORDER BY `nom`";
	else	$sqlrequest = "SELECT `indix`, `nom`, `prenom`, `date_n`, `classe` FROM `{$this->table_eleves}` ORDER BY `classe`";
	$result = $db1->conn->query( $sqlrequest );
	if	(!$result) return "erreur " . $sqlrequest . "<br>" . mysqli_error($db1->conn);
	$this->extract_liste_classes( $liste_classes );
	echo '<table><tr><td>matricule</td><td>nom</td><td>prenom</td><td>date de naissance</td><td>classe</td><td>commande</td></tr>';
	$self = $_SERVER['PHP_SELF'] . '?';
	while	( $row = mysqli_fetch_assoc($result) )
		{
		$mat    = $row['indix'];
		$nom    = $row['nom'];
		$prenom = $row['prenom'];
		$date   = $row['date_n'];
		if	( isset($liste_classes[$row['classe']]) )
			$classe = $liste_classes[$row['classe']];
		else	$classe = '??';
		echo "<tr><td>$mat</td><td>$nom</td><td>$prenom</td><td>$date</td><td>$classe</td><td>",
		"<a href=\"{$self}e={$mat}\"><img src=\"img/edit.png\" title=\"Editer\"></a> ",
		"<a href=\"{$self}k={$mat}\"><img src=\"img/kill.png\" title=\"Supprimer\"></a>",
		"</td></tr>";
		}
	echo '</table>';
	}

} // class school


?>
