<?php
/**
 gestion des programmes des enseignement par classe
 */

require_once( 'LP_func.php' );

$my_school = UserSchool();
$my_year = UserSyear();
$url0 = 'Modules.php?modname=' . $_REQUEST['modname'];

// echo '<pre>'; var_dump( $_SESSION ); echo '</pre>';

echo	'<style type="text/css">', "\n",
	"table.lp { border-collapse:collapse; }\n",
	"table.lp td { border:1px solid black; padding: 2px 4px 2px 6px; }\n",
	".butgreen { cursor: pointer; padding: 6px 18px;  margin: 8px 8px; border: solid 2px; ",
	"border-color: #4D4; background: #AFA; font-weight: bold; }\n",
	".hmenu { margin: 20px }\n",
	'</style>';

if	( isset( $_REQUEST['lp_classe'] ) )
	{
	$lp_classe = (int)$_REQUEST['lp_classe'];	// petit filtrage de securite
	$_SESSION['lp_classe'] = $lp_classe;
	$show_prof = isset($_REQUEST['show_prof']);
	$class_name = ''; $class_short_name = '';
	// un array indexe par index arbitraire
	$my_students = array();
	LP_liste_classe( $lp_classe, $class_name, $class_short_name, $my_students );
	$effectif = count( $my_students );
	if	( !$class_name )
		echo "<h3>Classe $lp_classe inconnue</h3>";
	else if	( $effectif == 0 )
		echo "<h3>Classe $class_name n'a pas d'élèves</h3>";
	else	{
		echo "<h3>Classe $class_name : $effectif élèves</h3>";
		// on va travailler sur des sets de cours (course-period), representes par des arrays
		// keys = course_period_id, vals = true 
		$union_set = array();	// le set final ou "total"
		$all_sets = array();	// un array de sets de cours, indexe par student_id
		for	( $i = 0; $i < count($my_students); ++$i )
			{
			$j = $my_students[$i];
			$all_sets[$j] = [];
			LP_prog_1eleve( $j, $all_sets[$j] );
			$union_set += $all_sets[$j];		// union
			}
		echo '<p>', count($union_set), ' cours au total</p>';
		// afficher table des cours (et accessoirement mettre les noms cours des cours dans $union_set)
		LP_display_course_set( $union_set, isset($_REQUEST['show_prof']), true );
		// verification exhaustive
		if	( isset($_REQUEST['check']) )
			{
			$badcnt = 0; $goodcnt = 0;
			for	( $i = 0; $i < count($my_students); ++$i )
				{
				$check = LP_compare_sets_inc( $all_sets[$my_students[$i]], $union_set, 'cours' );
				if	( $check )
					{
					// $last_name = ''; $first_name = '';
					// LP_info_eleve( $my_students[$i], $last_name, $first_name );
					// echo '<p>', 'élève ', $last_name, ' ', $first_name, ' : ', $check, '</p>';
					$badcnt++;
					}
				else	$goodcnt++;
				}
			echo '<h3>Résultat de la vérification :</h3>';
			if	( $badcnt == 0 )
				echo '<p>Tous les élèves ont le même programme</p>';
			else	{
				echo "<p>$goodcnt élèves Ok, $badcnt élèves avec erreur</p>";
				// matrice des cours vs eleves (1 ligne par eleve)
				echo '<table class="lp">';
				// header de la matrice = liste des cours
				echo '<tr><td style="text-align: right">les cours -&gt;</td>';
				foreach	( $union_set as $elem )
					{
					echo "<td>$elem[0]</td>";
					}
				echo '</tr>';
				// la matrice
				$last_name = ''; $first_name = '';
				$url1 = $url0 . '&ref_student=';
				foreach	( $my_students as $elem )
					{
					LP_info_eleve( $elem, $last_name );
					echo '<tr><td><a href="', $url1, $elem, '">', $last_name, '</a></td>';
					foreach	( $union_set as $k => $v )
						{
						if	( isset( $all_sets[$elem][$k] ) )
							echo '<td>X</td>';
						else	echo '<td></td>';
						}
					echo '</tr>';
					}
				echo '<table>';
				echo '<p>Les élèves de cette classe n\'ont pas tous le même programme d\'enseignements.<br>',
				'Vous pouvez <b>unifier</b> le programme de cette classe, en choisissant un <b>élève de référence</b>, ',
				'puis en demandant la copie automatique de son programme à tous les élèves de sa classe.<br>',
				'Pour cela, commencez par cliquer sur le nom de cet élève dans le tableau ci-dessus.</p>';
				}
			}
		}
	echo '<div class="hmenu"><a class="butgreen" href="' . $url0 . '">Retour au choix de classe</a></div>';
	}
else if	( ( isset($_REQUEST['ref_student']) ) && ( isset($_SESSION['lp_classe']) ) )
	{
	$ref_student = $_REQUEST['ref_student'];
	$lp_classe = $_SESSION['lp_classe'];
	$ref_last_name = ''; $ref_first_name = ''; $nom_classe = '';
	LP_info_eleve( $ref_student, $ref_last_name, $ref_first_name );
	LP_nom_classe( $lp_classe, $nom_classe );
	$title = '<p>Elève de référence : ' . $ref_last_name . ' ' . $ref_first_name
		. '</p><p>pour la classe : <b>' . $nom_classe . '</b></p>';

	if	( isset($_REQUEST['reprog']) )
		{
		echo '<h2>Programme de Référence</h2>';
		echo $title;
		LP_reprog_1classe( $lp_classe, $ref_student );
		unset ($_SESSION['lp_classe']);
		echo '<h2>Copie effectuée</h2>';
		echo '<div class="hmenu"><a class="butgreen" href="' . $url0 . '&lp_classe=' . $lp_classe . '&check">Vérifier</a></div>';
		echo '<hr><div class="hmenu"><a class="butgreen" href="' . $url0 . '">Retour au choix de classe</a></div>';
		}
	else	{
		echo '<h2>Programme de Référence</h2>';
		echo $title;
		$ref_set = array();
		LP_prog_1eleve( $ref_student, $ref_set );
		echo '<p>', count($ref_set), ' cours</p>';
		LP_display_course_set( $ref_set, false, true );

		echo '<p>Si le programme ci-dessus convient pour toute la classe, vous pouvez le copier à tous les élèves ',
		'(Attention cette opération est irréversible):</p>';
		$url1 = $_SERVER['REQUEST_URI'] . '&reprog';
		echo '<div class="hmenu"><a class="butgreen" href="' . $url1 . '">Ok pour copier ce programme à toute la classe</a></div>';

		echo '<p>Sinon vous pouvez aller ajouter des cours dans le programme de cet élève, puis revenir sur cette page:</p>';
		$url2 = 'Modules.php?modname=Scheduling/Schedule.php&next_modname=Scheduling/Schedule.php&stuid='. $ref_student;
		// $_SESSION['student_id'] = $ref_student;
		SetUserStudentID( $ref_student );
		echo '<div class="hmenu"><a class="butgreen" href="' . $url2 . '">Aller modifier le programme de l\'élève de référence</a></div>';

		echo '<p>Vous pouvez aussi supprimer tous les cours de cet élève et repartir à zéro:</p>';
		$url3 = $url0 . '&zap_student=' . $ref_student;
		$_SESSION['zap_student'] = $ref_student;	// securite
		echo '<div class="hmenu"><a class="butgreen" href="' . $url3 . '">Effacer le programme de l\'élève de référence</a></div>';

		echo '<hr><div class="hmenu"><a class="butgreen" href="' . $url0 . '">Retour au choix de classe</a></div>';
		}

	}
else if	( ( isset($_REQUEST['zap_student']) ) && ( isset($_SESSION['zap_student']) ) && ( isset($_SESSION['lp_classe']) ) )
	{
	$zap_student = $_REQUEST['zap_student'];
	if	( $_SESSION['zap_student'] == $zap_student )
		{
		unset($_SESSION['zap_student']);
		// effacer tout ce qui concerne cet élève dans l'année courante
		$sqlrequest = 'DELETE FROM schedule WHERE student_id=' . $zap_student . ' AND syear=' . UserSyear();
		$result = db_query( $sqlrequest, true );

		echo '<h2>Effacement effectué</h2>';
		$ref_last_name = ''; $ref_first_name = '';
		LP_info_eleve( $zap_student, $ref_last_name, $ref_first_name );

		echo '<p>Les cours de l\'élève ', $ref_last_name . ' ' . $ref_first_name . ' ont été <b>effacés</b></p>';

		echo '<p>A présent, vous pouvez aller ajouter des cours dans le programme de cet élève:</p>';
		$url2 = 'Modules.php?modname=Scheduling/Schedule.php&next_modname=Scheduling/Schedule.php&stuid='. $zap_student;
		// $_SESSION['student_id'] = $zap_student;
		SetUserStudentID( $zap_student );
		echo '<div class="hmenu"><a class="butgreen" href="' . $url2 . '">Aller modifier le programme de l\'élève de référence</a></div>';
		}
	echo '<hr><div class="hmenu"><a class="butgreen" href="' . $url0 . '">Retour au choix de classe</a></div>';
	}
else	{
	DrawHeader( 'Choisir une classe' );
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
	echo '<p><input type="checkbox" name="show_prof" checked> afficher noms des profs</p>';
	echo '<p><input type="checkbox" name="check" checked> verif. du programme pour chaque élève</p>';
	echo '<p><button type="submit" class="button-primary"> Ok </button></p> </form>';
	}

?>
