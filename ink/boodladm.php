<?php

class boodladm extends boodle
{
// cree toutes les tables d'un boodle de base
function create_tables( $tab, $binfirst=1 ) {
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
		// fixer le numero du premier binome pour isoler les promos
		$sqlrequest = "ALTER TABLE `{$this->table_binomes}` AUTO_INCREMENT={$binfirst}";
		$result = $this->db->conn->query( $sqlrequest );
		if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
		echo "<p>$sqlrequest</p>\n";
		}
	// tables des notes
	else if	( $tab == 'notes' )
		{
		$sqlrequest = "DROP TABLE IF EXISTS `{$this->table_notes}`";
		$result = $this->db->conn->query( $sqlrequest );
		if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
		$sqlrequest = "CREATE TABLE `{$this->table_notes}` (`indix` INT, ";
		// preparer la liste des questions
		global $formexp;
		for	( $i = 1; $i <= 5; $i++ )
			{
			foreach	( $formexp[$i]->itsa as $k => $v )
				{
				if	( $k != 'indix' )
					$sqlrequest .= "`{$k}` INT, ";
				}
			}
		$sqlrequest .= 'PRIMARY KEY (`indix`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
		$result = $this->db->conn->query( $sqlrequest );
		if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
		echo "<p>$sqlrequest</p>\n";
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
	$indix = (int)$indix;
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
		$login = addslashes($login);
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

// lire une note chez un binome
// rend 0 si la note n'a ete enregistree pour ce binome, ou s'il n'y a pas de ligne pour ce binome
// (on pourrait distinguer ces 2 cas mais... non)
function extract_note( $bin, $question )
	{
	// prevention SQL injection sur les 2 arguments
	$bin = (int)$bin;
	if	( strlen( $question ) > 4 )
		return;
	$sqlrequest = "SELECT `{$question}` FROM `{$this->table_notes}` WHERE `indix` = '$bin'";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	if	( $row = mysqli_fetch_assoc($result) )
		$note = (int)$row[$question];
	else	$note = 0;
	return $note;
	}

// mettre a jour note chez un binome
function save_note( $bin, $question, $note )
	{
	// prevention SQL injection sur les 3 arguments
	$bin = (int)$bin;
	if	( strlen( $question ) > 4 )
		return;
	$note = (int)$note;
	// savoir si la ligne existe deja (UPDATE ne le dit pas, INSERT le dit sous forme d'error :-(
	$sqlrequest = "SELECT `indix` FROM `{$this->table_notes}` WHERE `indix` = '$bin'";
	$result = $this->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
	if	( $result->num_rows > 0 )
		{			// row existe deja
		$sqlrequest = "UPDATE `{$this->table_notes}` SET `{$question}` = '{$note}' WHERE `indix` = '{$bin}'";
		// echo "<p>$sqlrequest</p>\n";
		$result = $this->db->conn->query( $sqlrequest );
		if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
		}
	else	{			// premiere fois qu'on sauve une note pour ce binome
		$sqlrequest = "INSERT INTO `{$this->table_notes}` SET `indix` = '{$bin}', `{$question}` = '{$note}'";
		// echo "<p>$sqlrequest</p>\n";
		$result = $this->db->conn->query( $sqlrequest );
		if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
		}
	}

// pour 1 question, afficher les reponses de tous les binomes d'un groupe
function liste_reponse( $expid, $question, $groupe ) {
	global $formexp;
	// prevention SQL injection sur les 3 arguments
	$expid = (int)$expid;
	if	( strlen( $question ) > 4 )
		return;
	$groupe = (int)$groupe;
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
		$bins[] = (int)$row['indix'];
		}
	// puis creer une table, 1 ligne sur 2 etant la designation du binome, l'autre sa reponse
	echo '<form action="', '#', '" method="POST">', "<table>\n";	// '#' permet de cumuler GET et POST
	foreach	( $bins as $lebin )
		{
		// chercher la reponse
		$sqlrequest = "SELECT `{$question}` FROM `{$this->table_exp[$expid]}` WHERE `indix` = '{$lebin}'";
		$result = $this->db->conn->query( $sqlrequest );
		if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($this->db->conn) );
		if	( $row = mysqli_fetch_assoc($result) )
			{
			//print_r($row);
			$reponse = $row[$question];
			if	( strlen( $reponse ) == 0 )
				$reponse = '&nbsp;';
			else	{
				$reponse = htmlspecialchars( $reponse, ENT_COMPAT, 'UTF-8', true );
				$reponse = preg_replace( '/\R+/', '<br>', $reponse );
				}
			}
		else	$reponse = '&nbsp;';
		// chercher la note
		$note = $this->extract_note( $lebin, $question );
		// creer les 2 lignes dans la table
		echo '<tr style="padding: 0"><td style="padding: 1px 0 2px 0"><table width="100%" style="">';
		echo '<tr class="bin"><td class="bin">'; $this->list_1binome( $lebin ); echo "</td>";
		echo '<td class="rad1n"><input type="radio" name="', $lebin, '" ', (($note==1)?('checked '):('')), 'value="1"></td>';
		echo '<td class="rad2n"><input type="radio" name="', $lebin, '" ', (($note==2)?('checked '):('')), 'value="2"></td>';
		echo '<td class="rad3n"><input type="radio" name="', $lebin, '" ', (($note==3)?('checked '):('')), 'value="3"></td>';
		/*
		if	( $note == 1 )
			echo '<td class="rad1c"><input type="radio" name="', $lebin, '" checked value="1"></td>';
		else	echo '<td class="rad1n"><input type="radio" name="', $lebin, '" value="1"></td>';
		if	( $note == 2 )
			echo '<td class="rad2c"><input type="radio" name="', $lebin, '" checked value="2"></td>';
		else	echo '<td class="rad2n"><input type="radio" name="', $lebin, '" value="2"></td>';
		if	( $note == 3 )
			echo '<td class="rad3c"><input type="radio" name="', $lebin, '" checked value="3"></td>';
		else	echo '<td class="rad3n"><input type="radio" name="', $lebin, '" value="3"></td>';
		*/
		if	( $note == 1 )
			echo "</tr>\n", '<tr class="rep1"><td colspan="4">', $reponse, "</td></tr>\n";
		else if	( $note == 2 )
			echo "</tr>\n", '<tr class="rep2"><td colspan="4">', $reponse, "</td></tr>\n";
		else if	( $note == 3 )
			echo "</tr>\n", '<tr class="rep3"><td colspan="4">', $reponse, "</td></tr>\n";
		else	echo "</tr>\n", '<tr class="rep"><td colspan="4">', $reponse, "</td></tr>\n";
		echo '</table></td></tr>';
		}
	echo	'<tr class="lastrow"><td colspan="4" class="ar">',
		'<input type="hidden" name="Q" value="', $question, '">',
		'<input type="submit" class="boutmod" name="notes_mod" value="Sauver Notes"',
		"</td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
	}

} // class boodladm

?>
