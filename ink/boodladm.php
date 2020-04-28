<?php

class boodladm extends boodle
{
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
	// tables des experiences
	global $form1;
	$form1->mk_table( $this->db, $this->table_exp1, true, false );
	}

// // // objet binome // // //

// action BD hors longage
function kill_binome( $indix ) {
	$sqlrequest = "DELETE FROM `{$this->table_binomes}` WHERE `indix` = $indix;";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	}

} // class boodladm

?>
