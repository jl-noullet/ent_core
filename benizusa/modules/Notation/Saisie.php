<?php
/**
 saisie des notes, sur 20 uniquement
 */

require_once( 'modules/Loginpro/LP_func.php' );

$my_school = UserSchool();
$my_year = UserSyear();
$my_user = $_SESSION['STAFF_ID'];

$url0 = 'Modules.php?modname=' . $_REQUEST['modname'];

function reset_saisie()
{ unset($_SESSION['lp_classe']); unset($_SESSION['lp_cours']); unset($_SESSION['lp_students']); }

// echo '<pre>'; var_dump( $_SESSION ); echo '</pre>';

echo	'<style type="text/css">', "\n",
	"table.lp { border-collapse:collapse; }\n",
	"table.lp td { border:1px solid black; padding: 2px 8px 2px 10px; }\n",
	"table.fn td { border:0; padding: 2px 8px 10px 10px; }\n",
	"td.note { width: 5em } td.red { background-color: #f66 }\n",
	".butgreen { cursor: pointer; padding: 6px 18px;  margin: 8px 8px; border: solid 2px; ",
	"border-color: #4D4; background: #AFA; color: #048; font-weight: bold; font-size: 16px }\n",
	".butamber { cursor: pointer; padding: 6px 18px;  margin: 8px 8px; border: solid 2px; ",
	"border-color: #DA4; background: #FDA; color: #840; font-weight: bold; font-size: 16px }\n",
	".hmenu { margin: 20px } input[type=text].red { background-color: #F66 }\n",
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
	$sqlrequest = 'SELECT title, first_name, last_name, profile_id FROM staff WHERE staff_id=' . $my_user;
	$result = db_query( $sqlrequest, true );
	if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
		{
		$my_profile = $row['profile_id'];
		if	( $my_profile == 2 )
			$my_prof_name = $row['title'] . ' ' . $row['first_name'] . ' ' . $row['last_name'];
		}
	else	$my_profile = -1;

	// securite
	if	( ( $my_profile < 1 ) || ( $my_profile > 2 ) )
		{ reset_saisie(); exit('<p>Droits insuffisants pour continuer</p>'); }

	// chercher le nom de la MP
	$marking_period = UserMP();
	if	( $marking_period )
		{
		$sqlrequest = 'SELECT title FROM school_marking_periods WHERE marking_period_id=' . $marking_period;
		$result = db_query( $sqlrequest, true );
		if	( $row = @pg_fetch_array( $result, null, PGSQL_ASSOC ) )
			$MP_name = $row['title'];
		else	$MP_name = '[' . $marking_period . ']';
		}
	else	$MP_name = '[?]';

	// commencer la table de contexte
	$label_table = '<table class="fn"><tr><td>Etape :</td><td>' . $MP_name . '</td></tr>'
			. '<tr><td>Classe :</td><td>' . $class_title . '</td></tr>';
	if	( $my_profile == 2 )
		$label_table .= '<tr><td>Prof. :</td><td>' . $my_prof_name . '</td></tr>';
	// N.B. ici cette table n'est pas finie

	if	( isset( $_REQUEST['lp_cours'] ) )
		$_SESSION['lp_cours'] = (int)$_REQUEST['lp_cours'];
	if	( !isset($_SESSION['lp_cours']) ) 
		{
		// ETAPE 2 : choix de la discipline
		echo '<h2>Choisir une discipline</h2>';
		echo $label_table . '</table>';
		// On doit afficher la liste des cours, limitee au prof concerne si user est 1 prof
		// il faut d'abord au moins un eleve :
		$my_students = array();
		$nada = NULL;
		LP_liste_classe( $_SESSION['lp_classe'], $nada, $nada, $my_students );
		if	( count( $my_students ) < 1 )
			{ reset_saisie(); exit( '<p>Aucun élève dans cette classe</p>' ); }
		$_SESSION['lp_students'] = $my_students;
		// echo '<p>student ', $my_students[0], '</p>';
		// Prenons le set des cours de cet eleve $my_students[0]
		$activites = array();
		LP_prog_1eleve( $my_students[0], $activites );
		// echo '<p>', count($activites), ' cours trouves</p>';
		// trions ces cours
		LP_sort_course_set( $activites );
		// on va re scanner ces cours pour une de ces 2 raisons :
		//	- admin : a besoin de voir les noms des profs... on va les mettre dans les valeurs de $activites
		//	- prof : on va neutraliser les activites qui ne le concernent pas
		// partie commune de la requete SQL, qui va etre comletee avec $k dans le foreach
		$sqlrequest = 'SELECT title, short_name FROM course_periods WHERE ';
		if	( $my_profile == 2 )
			$sqlrequest .= 'teacher_id = ' . $my_user . ' AND ';
		$sqlrequest .= 'course_period_id = ';
		$nada = NULL; $my_prof = '';
		foreach	( $activites as $k => $v)
			{
			$result = db_query( $sqlrequest . $k, true );
			if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
				{
				if	( $my_profile == 2 )
					$activites[$k] = $row['short_name'];			// prof n'a pas besoin de revoir son nom
				else	{
					LP_split_course_period( $row['title'], $nada, $nada, $my_prof );
					$activites[$k] = $row['short_name'] . ' - ' . $my_prof;	// admin a besoin de voir les noms de profs
					}
				}
			else	$activites[$k] = false;	// else : le cours n'appartient pas au bon prof, $activites[$k] = false i.e. non-string
			}
		// echo '<hr><pre>'; var_dump( $activites ); echo '</pre><hr>';
		// On fait une form avec select input base sur cette liste
		echo '<form action="', $url0, '" method="GET"> ';
		echo '<select name="lp_cours"> ';
		$cnt = 0;
		foreach	( $activites as $k => $v)
			{
			if	( is_string( $v ) )
				{ echo '<option value="', $k, '">', $v, '</option> '; $cnt++; }
			}
		if	( $cnt < 1 )
			{ reset_saisie(); exit( '<p>Aucun cours dans cette classe</p>' ); }
		echo '</select><br>';
		// echo '<p>', $cnt, ' cours trouvés</p>';
		echo '<div class="hmenu"><button type="submit" class="butgreen"> Ok </button></div></form>';
		}
	else if	( !isset($_POST['lp_les_notes']) ) 
		{
		echo '<h2>Feuille de Notes pour une discipline</h2>';
		// ETAPE 3 : les notes
		// retrouver le nom de ce cours et du prof
		$nada = NULL; $my_prof = '';
		$sqlrequest = 'SELECT title, short_name FROM course_periods WHERE course_period_id = ' . $_SESSION['lp_cours'];
		$result = db_query( $sqlrequest, true );
		if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
			{
			$lp_course_name = $row['short_name'];
			if	( $my_profile != 2 )
				LP_split_course_period( $row['title'], $nada, $nada, $my_prof );
			}
		else	$lp_course_name = '[' . $_SESSION['lp_cours'] . ']';

		$label_table .= '<tr><td>Discipline :</td><td>' . $lp_course_name . '</td></tr>';
		if	( $my_prof )
			$label_table .= '<tr><td>Prof.</td><td>' . $my_prof . '</td></tr>';
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
		// Préparons la lecture de leurs notes actuelles pour la periode courante
		$sqlrequest = 'SELECT grade_letter FROM student_report_card_grades WHERE syear=' . $my_year
			. ' AND course_period_id=' . $_SESSION['lp_cours']
			. ' AND marking_period_id=\'' . $marking_period
			. '\' AND student_id=';		// a completer dans le foreach
		$last_name = ''; $first_name = '';
		// des tableaux tous indexes par student_id, ainsi si on trie l'un on trie les autres
		$noms_complets = array();
		$notes = array();
		// remplir les tableaux
		foreach	( $_SESSION['lp_students'] as $elem )
			{
			// lecture table student_report_card_grades (requete preparee ci-dessus)
			$result = db_query( $sqlrequest . $elem, true );
			if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
				$note[$elem] = $row['grade_letter'];
			else	$note[$elem] = '';
			if	( $row = pg_fetch_array( $result, null, PGSQL_ASSOC ) )
				$note[$elem] = 'ERR';		// erreur duplication
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
			echo '<style>',
			// le CSS pour la fenetre modale (popup) pour les erreurs
			'.modal { display: none;position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%;',
			'background-color: rgba(0,0,80,0.3); overflow: auto; }',
			'.modal_box { background-color: #fff; margin: 5% auto auto auto; width: 300px;',
			'border: 5px solid #F00; padding: 0px; }',
			'.modal_content { padding: 40px }',
			'.closeX { float: right; margin-right: 10px; cursor: pointer; color: #F00; font-size: 32px; font-weight: bold; }',
			'</style>';
			echo '<script>',
			// les fonctions pour la fenetre modale (popup) pour les erreurs
			'function show_modal(message) {',
			'document.getElementById("lemessage").innerHTML = message;',
			'document.getElementById("lemodal").style["display"]="block";',
			'}',
			'function hide_modal() {',
			'document.getElementById("lemodal").style["display"]="none";',
			'}',
			// la fonction qui peut bloquer le submit si note > 20.0
			'function check_notes() {',
			'var entrees = document.getElementsByTagName("input");',
			'for (var ii = 0; ii < entrees.length; ii++) {',
			'var name = String(entrees[ii].name); var val = String(entrees[ii].value); ',
			'if	( ( name.match(/^note/) ) && ( val ) ) {',
			'val = val.trim();',
			// accepter seulement nombre ou seulement lettres
			'val = val.replace( ",", "." );',
			'if	( !val.match(/^(\d+[.]?\d*|[A-Za-z]+)$/) ) {',
			//'if	( !val.match(/^\d+[.]?\d*$/) ) {',
			'entrees[ii].className = "red";',
			'show_modal( "Note non conforme : " + entrees[ii].value ); return false;}',
			'if	( Number(val) > 20.0 ) {',
			'entrees[ii].className = "red";',
			'show_modal( "Note supérieure à 20 : " + entrees[ii].value ); return false;',
			'}}} return true; } </script>';
			// la form
			echo '<form action="', $url0, '" method="POST" onSubmit="return check_notes();">';
			echo '<input type="hidden" name="lp_les_notes" value="', $_SESSION['lp_cours'], '">';
			echo '<table class="lp">';
			foreach	( $noms_complets as $k => $v )
				{
				echo '<tr><td>', $v, '</td><td>', 
				'<input type="text" name="note', $k, '" size="5" maxlength="5" value="', $note[$k], '">',
				"</td></tr>\n";
				}
			echo '</table><div class="hmenu"><button type="submit" name="save" class="butamber"> Enregistrer </button></div></form>';
			// la fenetre modale pour les erreurs (normalement invisible)
			echo '<div id="lemodal" class="modal"><div class="modal_box"><div onclick="hide_modal()" class="closeX">&times;</div>';
			echo '<div class="modal_content"><h3>Erreur à corriger</h3><div id="lemessage"></div></div></div></div>';
			// le script qui permet de fermer la fenetre modale en cliquant à coté
			echo '<script>window.onclick = ',
			'function(event) { if ( event.target == document.getElementById("lemodal") ) hide_modal();}</script>';
			}
		else	{
			echo '<table class="lp">';
			foreach	( $noms_complets as $k => $v )
				{
				echo '<tr><td>', $v, '</td>';
				if	( (float)$note[$k] > 20.0 )
					echo '<td class="note red">';
				else	echo '<td class="note">';
				echo $note[$k], '</td></tr>';
				}
			echo '</table>';
			echo '<hr><div class="hmenu"><a class="butamber" href="' . $url0 . '&edit_flag">Introduire ou modifier les notes</a></div>';
			}
		}
	else	{
		if	( $_POST['lp_les_notes'] != $_SESSION['lp_cours'] )
			{ reset_saisie(); exit('<p>Erreur 666</p>'); }
		// echo '<pre>', var_dump($_POST), '</pre>';
		// on procede par DELETE + INSERT plutot que UPDATE pour etre sur d'eliminer les doublons
		// preparer la requete DELETE (on delete 1 student a la fois par precaution)(sinon il faudrait ajouter school_id)
		$sql_delete = 'DELETE FROM student_report_card_grades WHERE'
			. ' syear=' . $my_year
			. ' AND course_period_id=' . $_SESSION['lp_cours']
			. " AND marking_period_id='" . $marking_period . "'"
			. ' AND student_id=';
		// preparer la requete INSERT
		$sql_insert = 'INSERT INTO student_report_card_grades'
			. '(syear,school_id,course_period_id,marking_period_id,course_title,student_id,grade_letter) VALUES ('
			. $my_year . ',' . $my_school . ',' . $_SESSION['lp_cours'] . ",'" . $marking_period . "','-',";
		foreach	( $_POST as $k => $v )
			{
			if	( substr( $k, 0, 4 ) == 'note' )
				{
				// whitelistage parano de la valeur $v (multibyte UTF-8 not supported)
				$w = '';
				$len = strlen( $v );
				if	( $len > 5 )
					$len = 5;
				for	( $i = 0; $i < $len; $i++ )
					{
					$c = ord($v[$i]);
					if	(
						( ( $c >= 0x30 ) && ( $c <= 0x39 ) ) ||	// chiffres
 						( ( $c >= 0x41 ) && ( $c <= 0x5A ) ) ||	// A-Z
						( ( $c >= 0x61 ) && ( $c <= 0x79 ) ) ||	// a-z
						( $c == 0x2E )	// dot
						)
						$w .= $v[$i];
					else if	( $c == 0x2C )	// virgule
						$w .= '.';
					}
				$stu = (int)substr( $k, 4 );
				$sqld = $sql_delete . $stu;
				$sqli = $sql_insert . $stu . ",'" . $w . "')";
				//echo '<p>', $sqld, '</p>';
				$result = db_query( $sqld, true );
				//echo '<pre>affected rows: ', pg_affected_rows($result), '</pre>';
				//echo '<p>', $sqli, '</p>';
				$result = db_query( $sqli, true );
				//echo '<pre>affected rows: ', pg_affected_rows($result), '</pre>';
				}
			}
		// affichage de la feuille mise a jour
		echo '<script>window.location.assign("', $url0, '");</script>';

		// ** UWAGA ** dans cette table marking_period_id c'est 1 string, ici '4' au lieu de 4
		// ** UWAGA ** dans cette table school_id et course_title sont obligatoires "NOT NULL"

		/* experiences preliminaires
		// select count rend le resultat dans 1 row comme MySQL
		// $select = 'SELECT COUNT(*) FROM student_report_card_grades WHERE syear=2020 AND student_id=214 AND course_period_id=102 AND marking_period_id=\'4\'';
		// avec select ordinaire on peut compter les rows avec pg_num_rows($result) (mais pas $result qui n'est pas une variable PHP)
		$select = 'SELECT grade_letter FROM student_report_card_grades WHERE syear=2020 AND student_id=214 AND course_period_id=102 AND marking_period_id=\'4\'';

		// update semble Ok, pg_num_rows($result) donne 0, pg_affected_rows($result) donne 1 mais il echoue avec phppgadmin
		$update = 'UPDATE student_report_card_grades SET grade_letter = \'22.66\' WHERE syear=2020 AND student_id=214 AND course_period_id=102 AND marking_period_id=\'4\'';

		// delete semble Ok, pg_num_rows($result) donne 0, pg_affected_rows($result) donne 1 (ou plus) mais il echoue avec phppgadmin
		// delete est un bon cheval, il peut deleter plusieurs rows d'un coup et s'il n'y en a pas il ne met pas d'erreur
		$delete = 'DELETE FROM student_report_card_grades WHERE syear=2020 AND student_id=214 AND course_period_id=102 AND marking_period_id=\'4\'';

		// delete semble Ok, pg_num_rows($result) donne 0, pg_affected_rows($result) donne 1
		$insert = 'INSERT INTO student_report_card_grades (syear,school_id,student_id,course_period_id,marking_period_id,grade_letter,course_title)'
								. 'VALUES (2020,1,214,102,\'4\',\'77.77\',\'-\')';
		$sql = $delete;
		echo '<p>', $sql, '</p>';
		$result = db_query( $sql, true );
		echo '<pre>', pg_num_rows($result), ' ', pg_affected_rows($result), '</pre>';
		*/		
		}
	echo '<hr><div class="hmenu"><a class="butgreen" href="' . $url0 . '&reset">Retour au choix de classe</a></div>';
	}
