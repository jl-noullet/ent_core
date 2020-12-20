<?php
/**
 saisie des heures d'absence
 */

require_once( 'modules/Loginpro/LP_func.php' );

$my_school = UserSchool();
$my_year = UserSyear();
$my_user = $_SESSION['STAFF_ID'];

$url0 = 'Modules.php?modname=' . $_REQUEST['modname'];

function reset_saisie()
{ unset($_SESSION['lp_classe']); unset($_SESSION['lp_students']); }

// echo '<pre>'; var_dump( $_SESSION ); echo '</pre>';

echo	'<style type="text/css">', "\n",
	"table.lp { border-collapse:collapse; }\n",
	"table.lp td { border:1px solid black; padding: 2px 8px 2px 10px; }\n",
	"table.fn td { border:0; padding: 2px 8px 10px 10px; }\n",
	"td.note { width: 4em }\n",
	".butgreen { cursor: pointer; padding: 6px 18px;  margin: 8px 8px; border: solid 2px; ",
	"border-color: #4D4; background: #AFA; color: #048; font-weight: bold; font-size: 16px }\n",
	".butamber { cursor: pointer; padding: 6px 18px;  margin: 8px 8px; border: solid 2px; ",
	"border-color: #DA4; background: #FDA; color: #840; font-weight: bold; font-size: 16px }\n",
	".hmenu { margin: 20px }\n",
	'</style>';

if	( isset( $_REQUEST['reset'] ) )
	reset_saisie(); 

if	( isset( $_REQUEST['lp_classe'] ) )
	$_SESSION['lp_classe'] = (int)$_REQUEST['lp_classe'];
if	( !isset($_SESSION['lp_classe']) ) 
	{
	// ETAPE 1 : choix de la classe
	echo '<h2>Choisir une classe</h2>';
	// obtenir la liste des classes
	$sqlrequest = 'SELECT id, short_name, title FROM school_gradelevels WHERE school_id=' . $my_school . ' ORDER BY short_name'; // DESC';
	$result = db_query( $sqlrequest, true );
	$class_names = array();
	while	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		{
		// echo '<pre>'; var_dump( $row ); echo '</pre>';
		$class_names[$row['id']] = $row['short_name'] . ' (' . $row['title'] . ')';
		}
	// afficher la form
	echo '<form action="', $_SERVER['REQUEST_URI'], '" method="GET"> ';
	echo '<select name="lp_classe"> ';
	foreach	($class_names as $k => $v) {
		echo '<option value="', $k, '">', $v, '</option> ';
		}
	echo '</select><br>';
	echo '<div class="hmenu"><button type="submit" class="butgreen"> Ok </button></div></form>';
	}
else	{
	// partie commune aux etapes suivantes : le contexte
	// trouver nom de la classe
	$sqlrequest = 'SELECT short_name, title FROM school_gradelevels WHERE id=' . $_SESSION['lp_classe'];
	$result = db_query( $sqlrequest, true );
	if	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		{ $class_title = $row['title']; $classe_short = $row['short_name']; }
	else	{ $class_title = $_SESSION['lp_classe']; $classe_short = $_SESSION['lp_classe']; }

	// interpreter identite utilisateur
	$sqlrequest = 'SELECT profile_id FROM staff WHERE staff_id=' . $my_user;
	$result = db_query( $sqlrequest, true );
	if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		{
		$my_profile = $row['profile_id'];
		}
	else	$my_profile = -1;

	// securite
	if	( $my_profile != 1 )
		{ reset_saisie(); exit('<p>Droits insuffisants pour continuer</p>'); }

	// chercher l'id et le nom du trimestre
	$trim_name = '';
	$trimestre = LP_find_trimestre( UserMP(), $trim_name );

	// commencer la table de contexte
	$label_table = '<table class="fn"><tr><td>Période :</td><td>' . $trim_name . '</td></tr>'
			. '<tr><td>Classe :</td><td>' . $class_title . '</td></tr>';
	// N.B. ici cette table n'est pas finie

	if	( !isset($_POST['lp_les_abs']) ) 
		{
		echo '<h2>Heures d\'absences injustifiées</h2>';

		echo $label_table . '</table>';

		// Soyons sur d'avoir la liste des eleves concernes
		if	( !is_array($_SESSION['lp_students']) )
			{
			$my_students = array();
			$nada = NULL;
			LP_liste_classe( $_SESSION['lp_classe'], $nada, $nada, $my_students );
			$_SESSION['lp_students'] = $my_students;
			}
		if	( count( $_SESSION['lp_students'] ) < 1 )
			{ reset_saisie(); exit( '<p>Aucun élève dans cette classe</p>' ); }
		// Préparons la lecture de leurs absences actuelles pour la periode courante
		$sqlrequest = 'SELECT comment FROM student_mp_comments WHERE syear=' . $my_year
			. ' AND marking_period_id=\'' . $trimestre
			. '\' AND student_id=';		// a completer dans le foreach
		$last_name = ''; $first_name = '';
		// des tableaux tous indexes par student_id, ainsi si on trie l'un on trie les autres
		$noms_complets = array();
		$anjs = array();
		// remplir les tableaux
		foreach	( $_SESSION['lp_students'] as $elem )
			{
			// lecture table student_mp_comments (requete preparee ci-dessus)
			$result = db_query( $sqlrequest . $elem, true );
			if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
				{
				$tmp_comment = $row['comment'];
				// extraction des heures d'abs non justifiee
				$anjs[$elem] = (int)substr( $tmp_comment, 3 );
				if	( $anjs[$elem] == 0 )
					$anjs[$elem] = '';
				}
			else	$anjs[$elem] = '';
			// lecture table students
			LP_info_eleve( $elem, $last_name, $first_name );
			$noms_complets[$elem] = $last_name . ' ' . $first_name;
			}
		// trier par ordre alphabetique des noms
		natcasesort( $noms_complets );
		// Affichons le formulaire de notation (view ou edit)
		$edit_flag = isset($_REQUEST['edit_flag']);
		if	( $edit_flag )
			{
			echo '<form action="', $url0, '" method="POST">';
			echo '<input type="hidden" name="lp_les_abs" value="', $_SESSION['lp_classe'], '">';
			echo '<table class="lp">';
			foreach	( $noms_complets as $k => $v )
				{
				echo '<tr><td>', $v, '</td><td>', 
				'<input type="text" name="anj', $k, '" size="4" maxlength="4" value="', $anjs[$k], '">',
				"</td></tr>\n";
				}
			echo '</table><div class="hmenu"><button type="submit" class="butamber"> Enregistrer </button></div></form>';
			}
		else	{
			echo '<table class="lp">';
			foreach	( $noms_complets as $k => $v )
				echo '<tr><td>', $v, '</td><td class="note">', $anjs[$k], '</td></tr>';
			echo '</table>';
			echo '<hr><div class="hmenu"><a class="butamber" href="' . $url0 . '&edit_flag">Introduire ou modifier les absences</a></div>';
			}
		}
	else	{
		if	( $_POST['lp_les_abs'] != $_SESSION['lp_classe'] )
			{ reset_saisie(); exit('<p>Erreur 666</p>'); }
		// echo '<pre>', var_dump($_POST), '</pre>';
		// on procede par DELETE + INSERT plutot que UPDATE pour etre sur d'eliminer les doublons
		// preparer la requete DELETE (on delete 1 student a la fois par precaution)(sinon il faudrait ajouter school_id)
		$sql_delete = 'DELETE FROM student_mp_comments WHERE'
			. ' syear=' . $my_year
			. " AND marking_period_id='" . $trimestre . "'"
			. ' AND student_id=';
		// preparer la requete INSERT
		$sql_insert = 'INSERT INTO student_mp_comments'
			. '(syear,marking_period_id,student_id,comment) VALUES ('
			. $my_year . ',' . $trimestre . ',';
		foreach	( $_POST as $k => $v )
			{
			if	( substr( $k, 0, 3 ) == 'anj' )
				{
				if	( is_numeric($v) )
					$comment = sprintf("ANJ%d", (int)$v );
				else	$comment = '';
				$stu = (int)substr( $k, 3 );
				$sqld = $sql_delete . $stu;
				$sqli = $sql_insert . $stu . ",'" . $comment . "')";
				// echo '<p>', $sqld, '</p>';
				$result = db_query( $sqld, true );
				// echo '<pre>affected rows: ', pg_affected_rows($result), '</pre>';
				// echo '<p>', $sqli, '</p>';
				$result = db_query( $sqli, true );
				// echo '<pre>affected rows: ', pg_affected_rows($result), '</pre>';
				}
			}
		// affichage de la feuille mise a jour
		echo '<script>window.location.assign("', $url0, '");</script>';
		}
	echo '<hr><div class="hmenu"><a class="butgreen" href="' . $url0 . '&reset">Retour au choix de classe</a></div>';
	}
