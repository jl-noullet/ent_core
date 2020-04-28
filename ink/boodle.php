<?php

class boodle
{
public $db;
public $table_binomes;
public $table_logins;
public $table_exp1;
public $liste_eleves;


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

// objet experience
function exp_edit( $expid, $binome ) {
	global $form1;
	$latable = $this->table_exp1;
	$laform = $form1;
	if	( $laform->db2form( $this->db, $latable, $binome ) )
		$laform->show_form( 'mod', FALSE, 1 );
	else	{
		$laform->itsa['indix']->val = $binome;
		$laform->show_form( 'add', TRUE, 1 );
		}
	}

} // class boodle

?>
