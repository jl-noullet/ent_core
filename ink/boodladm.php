<?php

class boodladm extends boodle
{
// cree toutes les tables d'un boodle de base
function create_tables( $tab ) {
	// table des logins (non editable sous longage)
	if	( $tab == 'logins' )
		{
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
		}
	// table des binomes
	else if	( $tab == 'binomes' )
		{
		global $form_bi;
		$form_bi->mk_table( $this->db, $this->table_binomes, true );
		}
	// tables des experiences
	else 	{
		global $formexp;
		$i = (int)$tab;
		if	( ( $i >= 1 ) && ( $i <= 5 ) )
			$formexp[$i]->mk_table( $this->db, $this->table_exp[$i], true, false );
		}
	}

// // // objet binome // // //

// action BD hors longage
function kill_binome( $indix ) {
	$sqlrequest = "DELETE FROM `{$this->table_binomes}` WHERE `indix` = $indix;";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	}

// // // objet login // // //
function list_logins() {
	$sqlrequest = "SELECT * FROM `{$this->table_logins}`";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	echo "<table>\n";
	while	( $row = mysqli_fetch_assoc($result) )
		{
		$login = $row['uchave'];
		echo '<tr><td>', $login, '</td><td>';
		$this->list_1binome( $row['binome'] ); echo '</td><td>';
		echo "<a href=\"{$_SERVER['PHP_SELF']}?op=login_kill&ind={$login}\"><img src=\"img/kill.png\" title=\"kill\"></a>";
		echo "</td></tr>\n";
		}
	echo '</table>';
	}
function kill_login( $login, $confirm ) {
	if	( $confirm )
		{
		$sqlrequest = "DELETE FROM `{$this->table_logins}` WHERE `uchave` = '{$login}';";
		$result = $this->db->conn->query( $sqlrequest );
		if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
		global $label;
		echo "<p class=\"resu\">{$label['moded']}</p>";
		}
	else	{
		echo	"Kill {$login} ?<ul>",
			"<li><a href=\"{$_SERVER['PHP_SELF']}?op=login_kill&ind={$login}&confirmed=1\">yes</a></li>",
			"<li><a href=\"{$_SERVER['PHP_SELF']}\"> no </a></li></ul>\n";
		}
	}

// // // objet experience // // //

// pour 1 question, afficher les reponses de tous les binomes d'un groupe
function liste_reponse( $expid, $question, $groupe ) {
	global $formexp;
	// d'abord le libelle de la question
	echo '<h3>', $formexp[$expid]->itsa[$question]->desc, "</h3>\n";
	// puis trier les binomes
	global $form_bi;
	$bins = array();
	$sqlrequest = "SELECT `indix` FROM `{$this->table_binomes}` WHERE `groupe` = '$groupe'";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	while	( $row = mysqli_fetch_assoc($result) )
		{
		$bins[] = $row['indix'];
		}
	// puis lire les reponses
	echo "<table>\n";
	foreach	( $bins as $lebin )
		{
		echo '<tr><td>'; $this->list_1binome( $lebin ); echo "</td></tr>\n";
		$sqlrequest = "SELECT `{$question}` FROM `{$this->table_exp[$expid]}` WHERE `indix` = '{$lebin}'";
		$result = $this->db->conn->query( $sqlrequest );
		if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
		if	( $row = mysqli_fetch_assoc($result) )
			{
			echo '<tr><td>';
			//print_r($row);
			echo $row[$question];
			echo "</td></tr>\n";
			}
		}
	echo "</table>\n";
	
	}

} // class boodladm

?>
