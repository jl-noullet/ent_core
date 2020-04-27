<?php

class boodle
{
public $db;
public $table_binomes;
public $liste_eleves;

// cree toutes les tables d'un boodle de base
function create_tables() {
	global $form_bi;
	$form_bi->mk_table( $this->db, $this->table_binomes, true );
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
function list_binomes() {
	global $form_bi;
	$sqlrequest = "SELECT `indix` FROM `{$this->table_binomes}`"; // . "WHERE `groupe` = '$g'";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	echo "<table>\n";
	$form_bi->form2th( 2, 3 );
	while	( $row = mysqli_fetch_assoc($result) )
		{
		$form_bi->db2form( $this->db, $this->table_binomes, $row['indix'] );
		$form_bi->form2tr( 2, 3, 30 );
		}
	echo '</table>';
	}
} // class boodle

?>
