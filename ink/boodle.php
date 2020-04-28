<?php

class boodle
{
public $db;
public $table_binomes;
public $liste_eleves;

// cree toutes les tables d'un boodle de base
function create_tables() {
	// table des logins (non editable sous longage)
	$sqlrequest = "DROP TABLE IF EXISTS `{$this->table_logins}`";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	$sqlrequest = "CREATE TABLE `{$this->table_logins}`
		( `uchave` VARCHAR(32), `binome` INT,
		PRIMARY KEY (`uchave`), INDEX(`binome`) )
		ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	echo "<p>$sqlrequest</p>\n";
	// table des binomes
	global $form_bi;
	$form_bi->mk_table( $this->db, $this->table_binomes, true );
	}

// // // objet login // // //

// rend l'index du binome ou -1 si pas trouve
function find_login( $login ) {
	$sqlrequest = "SELECT `binome` FROM `{$this->table_logins}` WHERE `uchave` = '{$login}'";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	if	( $row = mysqli_fetch_assoc($result) )
		return $row['binome'];
	return -1;
	}

// ajoute le login dans la table
function add_login( $login, $binome ) {
	$sqlrequest = "INSERT INTO `{$this->table_logins}` SET `uchave` = '{$login}', `binome` = '{$binome}'";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	}


// // // objet binome // // //
// affichage forms
function form_add_binome() {
	global $form_bi;
	$form_bi->show_form( 'add', TRUE, 0 );
	}

function form_edit_binome( $indix, $killflag=0 ) {
	global $form_bi;
	$indix = (int)$indix;
	$form_bi->db2form( $this->db, $this->table_binomes, $indix );
	$form_bi->show_form( ( $killflag ? 'kill' : 'mod' ), FALSE, 2 );
	}

// action BD hors longage
function kill_binome( $indix ) {
	$sqlrequest = "DELETE FROM `{$this->table_binomes}` WHERE `indix` = $indix;";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	}

// affichage liste
function list_binomes( $killable=false ) {
	global $form_bi;
	$sqlrequest = "SELECT `indix` FROM `{$this->table_binomes}`"; // . "WHERE `groupe` = '$g'";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	echo "<table>\n";
	$form_bi->form2th( 2, 3 );
	while	( $row = mysqli_fetch_assoc($result) )
		{
		$form_bi->db2form( $this->db, $this->table_binomes, $row['indix'] );
		$form_bi->form2tr( 2, ($killable?3:1), 30 );
		}
	echo '</table>';
	}

// affichage 1 binome sur 1 ligne
function list_1binome( $binome ) {
	global $form_bi; global $groupes;
	$form_bi->db2form( $this->db, $this->table_binomes, $binome );
	$eleve = $form_bi->itsa['eleve1']->val;
	if	( $eleve > 0 )
		echo '[ ', $this->liste_eleves[$eleve], ' ]';
	$eleve = $form_bi->itsa['eleve2']->val;
	if	( $eleve > 0 )
		echo '[ ', $this->liste_eleves[$eleve], ' ]';
	$eleve = $form_bi->itsa['eleve3']->val;
	if	( $eleve > 0 )
		echo '[ ', $this->liste_eleves[$eleve], ' ]';
	echo ', gr. ', $groupes[$form_bi->itsa['groupe']->val];
	}

} // class boodle

?>
