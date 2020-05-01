<?php

class boodle
{
public $db;
public $table_binomes;
public $table_logins;
public $table_exp;
public $liste_eleves;

function init( $po ) {
	global $db1;
	$this->db = $db1;
	$this->table_logins  = "boo_{$po}_logins";
	$this->table_binomes = "boo_{$po}_binomes";
	for	( $i = 1; $i <= 5; $i++ )
		$this->table_exp[$i]  = "boo_{$po}_exp{$i}";
	require_once("ink/liste_3{$po}.php");	// va creer un array $liste_3
	$this->liste_eleves = $liste_3;
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

// affichage liste
function list_binomes( $killable=false ) {
	global $form_bi;
	$sqlrequest = "SELECT `indix` FROM `{$this->table_binomes}` ORDER BY `groupe`"; // . " WHERE `groupe` = '$g'";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	echo "<table>\n";
	if	( $killable )
		{
		$form_bi->form2th( 2, 3 );
		while	( $row = mysqli_fetch_assoc($result) )
			{
			$form_bi->db2form( $this->db, $this->table_binomes, $row['indix'] );
			$form_bi->form2tr( 2, 3, 30 );
			}
		echo '</table>';
		}
	else	{
		$form_bi->form2th( 0, 3 );
		while	( $row = mysqli_fetch_assoc($result) )
			{
			$form_bi->db2form( $this->db, $this->table_binomes, $row['indix'] );
			$form_bi->form2tr( 0, 1, 30 );
			}
		echo '</table>';
		echo '<p><i>Cliquez sur <img src="img/edit.png"> du binôme choisi, puis Ok pour confirmer</i></p>';
		}
	}

// affichage 1 binome sur 1 ligne, ou plusieurs selon $sep
function list_1binome( $binome, $sep=NULL ) {
	global $form_bi; global $groupes;
	$form_bi->db2form( $this->db, $this->table_binomes, $binome );
	$eleve1 = $form_bi->itsa['eleve1']->val;
	$eleve2 = $form_bi->itsa['eleve2']->val;
	$eleve3 = $form_bi->itsa['eleve3']->val;
	if	( $sep )
		{
		if	( $eleve1 > 0 )	echo $this->liste_eleves[$eleve1], $sep;
		if	( $eleve2 > 0 )	echo $this->liste_eleves[$eleve2], $sep;
		if	( $eleve3 > 0 )	echo $this->liste_eleves[$eleve3], $sep;
		echo 'gr. ', $groupes[$form_bi->itsa['groupe']->val];
		}
	else	{
		if	( $eleve1 > 0 )	echo '[ ', $this->liste_eleves[$eleve1], ' ]';
		if	( $eleve2 > 0 )	echo '[ ', $this->liste_eleves[$eleve2], ' ]';
		if	( $eleve3 > 0 )	echo '[ ', $this->liste_eleves[$eleve3], ' ]';
		echo ', gr. ', $groupes[$form_bi->itsa['groupe']->val];
		}
	}

// objet experience
function exp_edit( $expid, $binome ) {
	global $formexp;
	$latable = $this->table_exp[$expid];
	$laform = $formexp[$expid];
	if	( $laform->db2form( $this->db, $latable, $binome ) )
		$laform->show_form( 'mod', FALSE, 1 );
	else	{
		$laform->itsa['indix']->val = $binome;
		$laform->show_form( 'add', TRUE, 1 );
		}
	}

function exp_insert( $expid ) {
	global $formexp; global $label;
	$formexp[$expid]->post2form_full( FALSE );
	$formexp[$expid]->form2db_insert_full( $this->db, $this->table_exp[$expid], FALSE );
	echo "<p class=\"resu\">{$label['added']}</p>";
	}

function exp_mod( $expid ) {
	global $formexp; global $label; 
	$formexp[$expid]->post2form_full( FALSE );
	$formexp[$expid]->form2db_update_full( $this->db, $this->table_exp[$expid] );
	echo "<p class=\"resu\">{$label['moded']}</p>";
	}

} // class boodle

?>
