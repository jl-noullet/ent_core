<?php

class school
{
public $db;
public $table_eleves;
public $table_classes;

// cree toutes les tables d'une school de base
function create_tables() {
	$sqlrequest = "DROP TABLE IF EXISTS `{$this->table_eleves}`";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	$sqlrequest = "CREATE TABLE `$this->table_eleves`
		( `indix` INT NOT NULL AUTO_INCREMENT, `nom` VARCHAR(64), `prenom` VARCHAR(64), `date_n` BIGINT, `classe` VARCHAR(64),
		PRIMARY KEY (`indix`), INDEX(`nom`), INDEX(`classe`) )
		ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";  
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );

	$sqlrequest = "DROP TABLE IF EXISTS `{$this->table_classes}`";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	$sqlrequest = "CREATE TABLE `$this->table_classes`
		( `indix` INT AUTO_INCREMENT, `nom` VARCHAR(64),
		PRIMARY KEY (`indix`), INDEX(`nom`) )
		ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";  
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );

	$sqlrequest = "DROP TABLE IF EXISTS `{$this->table_activites}`";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	$sqlrequest = "CREATE TABLE `$this->table_activites`
		( `indix` INT NOT NULL AUTO_INCREMENT, `nom` VARCHAR(64), `duree` INT,
		PRIMARY KEY (`indix`) )
		ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";  
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );

	$sqlrequest = "DROP TABLE IF EXISTS `{$this->table_events}`";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	$sqlrequest = "CREATE TABLE `$this->table_events`
		( `indix` INT NOT NULL AUTO_INCREMENT, `activite` INT, `week` INT, `offset` INT, `duree` INT, `a_faire` VARCHAR(1024),
		PRIMARY KEY (`indix`), INDEX(`activite`), INDEX(`week`)  )
		ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";  
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	}
// // // objet eleve // // //
// affichage forms
function form_add_eleve( $class=0 ) {
	global $form_s;
	if	( $class )
		{
		$form_s->clear();
		$form_s->itsa['classe']->val = $class;
		$form_s->show_form( 'add', FALSE, 0 );
		}
	else	$form_s->show_form( 'add', TRUE, 0 );
	}

function form_edit_eleve( $indix, $killflag=0 ) {
	global $form_s;
	$indix = (int)$indix;
	$form_s->db2form( $this->db, $this->table_eleves, $indix );
	$form_s->show_form( ( $killflag ? 'kill' : 'mod' ), FALSE, 2 );
	}

function form_find_eleve() {
	global $label;
	echo "<form action=\"{$_SERVER['PHP_SELF']}\" method=\"POST\">\n";
	echo "<table>\n";
	echo "<tr><td class=\"ag\">{$label['lastname']}</td><td><input class=\"textin\" type=\"text\" name=\"nom\" id=\"nom\" value=\"\"></td></tr>\n";
	echo "<tr><td class=\"ag\">{$label['orfirstname']}</td><td><input class=\"textin\" type=\"text\" name=\"prenom\" id=\"prenom\" value=\"\"></td></tr>\n";
	echo "<tr class=\"lastrow\"><td colspan=\"2\" class=\"ar\"><input type=\"submit\" class=\"boutfind\" name=\"eleve_find\" value=\"{$label['find']}\"></td></tr>\n";
	echo "</table>\n";
	echo "</form>";
	}
// affichage liste
function show_found_eleves( $nom, $prenom ) {
	global $form_s, $label;
	if	( $nom )
		$sqlrequest = "SELECT `indix`, `nom`, `prenom`, `date_n`, `classe` FROM `{$this->table_eleves}`" .
			"WHERE `nom` = '$nom' ORDER BY `prenom`";
	else if	( $prenom )
		$sqlrequest = "SELECT `indix`, `nom`, `prenom`, `date_n`, `classe` FROM `{$this->table_eleves}`" .
			"WHERE `prenom` = '$prenom' ORDER BY `nom`";
	else	$sqlrequest = "SELECT `indix`, `nom`, `prenom`, `date_n`, `classe` FROM `{$this->table_eleves}` ORDER BY `classe`";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	$this->extract_liste_classes( $liste_classes );
	echo "<table><tr><td>{$form_s->itsa['indix']->desc}</td><td>{$form_s->itsa['nom']->desc}</td><td>{$form_s->itsa['prenom']->desc}</td>",
		"<td>{$form_s->itsa['date_n']->desc}</td><td>{$form_s->itsa['classe']->desc}</td><td>Actions</td></tr>";
	$self = $_SERVER['PHP_SELF'] . '?';
	while	( $row = mysqli_fetch_assoc($result) )
		{
		$mat    = $row['indix'];
		$nom    = $row['nom'];
		$prenom = $row['prenom'];
		$date   = date( 'Y-m-d', $row['date_n'] );
		if	( isset($liste_classes[$row['classe']]) )
			$classe = $liste_classes[$row['classe']];
		else	$classe = '??';
		echo "<tr><td>$mat</td><td>$nom</td><td>$prenom</td><td>$date</td><td>$classe</td><td>",
		"<a href=\"{$self}es={$mat}\"><img src=\"img/edit.png\" title=\"{$label['edit']}\"></a> ",
		"<a href=\"{$self}ks={$mat}\"><img src=\"img/kill.png\" title=\"{$label['kill']}\"></a>",
		"</td></tr>";
		}
	echo '</table>';
	}
// action BD hors longage
function kill_eleve( $mat ) {
	$sqlrequest = "DELETE FROM `{$this->table_eleves}` WHERE `indix` = $mat;";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	}

// // // objet classe // // //
// affichage forms
function form_add_class() {
	global $form_c;
	$form_c->show_form( 'add', TRUE, 0 );
	}

function form_edit_class( $indix, $killflag=0 ) {
	global $form_c;
	$indix = (int)$indix;
	$form_c->db2form( $this->db, $this->table_classes, $indix );
	$form_c->show_form( ( $killflag ? 'kill' : 'mod' ), FALSE, 1 );
	}

// affichage listes
function show_liste_classes() {
	global $label;
	$this->extract_liste_classes( $liste );
	$this->extract_effectifs_classes( $effectifs, $total );
	echo "<table><tr><td>{$label['classe']}</td><td>{$label['effectif']}</td><td>Actions</td></tr>";
	$self = $_SERVER['PHP_SELF'] . '?';
	foreach ($liste as $k => $v)
		{
		$e = isset($effectifs[$k])?$effectifs[$k]:0;
		echo "<tr><td><a href=\"{$self}lc={$k}\">{$v}</a></td><td>{$e}</td><td>",
		"<a href=\"{$self}ec={$k}\"><img src=\"img/edit.png\" title=\"{$label['edit']}\"></a>",
		// "<a href=\"{$self}kc={$mat}\"><img src=\"img/kill.png\" title=\"{$label['kill']}\"></a>",
		"</td></tr>";
		}
	echo '</table>';
	echo "Total $total eleves<br>";
	}

function show_1_classe( $classe ) {
	global $form_s, $label;
	$self = $_SERVER['PHP_SELF'] . '?';
	$c = (int)$classe;
	$sqlrequest = "SELECT `indix`, `nom` FROM `{$this->table_classes}` WHERE `indix` = {$c};";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	if	( $row = mysqli_fetch_assoc($result) )
		$nom_classe = $row['nom'];
	else	$nom_classe = "??";
	echo "<h2> {$label['classe']} {$nom_classe} <a href=\"{$self}op=add1&amp;c={$classe}\"><img src=\"img/plus.png\" title=\"{$label['add1']}\"></a></h2>";
	$sqlrequest = "SELECT `indix`, `nom`, `prenom`, `date_n` FROM `{$this->table_eleves}`" .
			"WHERE `classe` = '$c' ORDER BY `nom`";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	echo "<table><tr><td>{$form_s->itsa['indix']->desc}</td><td>{$form_s->itsa['nom']->desc}</td><td>{$form_s->itsa['prenom']->desc}</td>",
		"<td>{$form_s->itsa['date_n']->desc}</td><td>{$form_s->itsa['classe']->desc}</td><td>Actions</td></tr>";
	while	( $row = mysqli_fetch_assoc($result) )
		{
		$mat    = $row['indix'];
		$nom    = $row['nom'];
		$prenom = $row['prenom'];
		$date   = date( 'Y-m-d', $row['date_n'] );
		$classe = $nom_classe;
		echo "<tr><td>$mat</td><td>$nom</td><td>$prenom</td><td>$date</td><td>$classe</td><td>",
		"<a href=\"{$self}es={$mat}\"><img src=\"img/edit.png\" title=\"{$label['edit']}\"></a> ",
		"<a href=\"{$self}ks={$mat}\"><img src=\"img/kill.png\" title=\"{$label['kill']}\"></a>",
		"</td></tr>";
		}
	echo '</table>';
	}

// utilitaires
// fournit une liste des classes (par reference)
function extract_liste_classes( &$liste_classes ) {
	$sqlrequest = "SELECT `indix`, `nom` FROM `{$this->table_classes}`";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	$liste_classes = array();
	while	( $row = mysqli_fetch_assoc($result) )
		{
		$i = $row['indix'];
		$liste_classes[$i] = $row['nom'];
		}
	}
// extrait de la liste des eleves les effectifs des classes (par reference), donne aussi l'effectif total
function extract_effectifs_classes( &$effect_classes, &$total ) {
	$sqlrequest = "SELECT `classe`, COUNT(*) FROM `{$this->table_eleves}` GROUP BY `classe`";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
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

} // class school

?>
