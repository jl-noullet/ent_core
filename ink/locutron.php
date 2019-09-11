<?php
/*  Le Locutron est un generateur de vocabulaire cybernetique de la 2eme
    generation.

    La fonction  vocable(res,iv,maj)  rend une chaine de 4 a 6 caracteres,
    differente et stable pour chaque valeur de l'entier de 16 bits iv.
    ( 65536 chaines differentes ).
    Le premier caractere ( une consonne ) est majuscule si le booleen maj
    est affirmatif.
*/

function vocable( $iv, $maj )
{
$cons = "sybcdfjlmnprtvwzxk";
$voys = "aiouaeio";
$cns2 = "blbrchclcrdjfrflgrglqupstrssspst";
$fins = " iuflnrx uflnrty fklnrxs iulnrsz";
$resu = "";

$i1 = ($iv << 8) & 0xff00;
$iv ^= $i1;

$c = $cons[$iv & 15];
if	( $maj )
	$c = strtoupper($c);
$resu .= $c;
$iv >>= 4;
$resu .= $voys[$iv & 3];
$iv >>= 2;
$i1 = $iv & 15;
$i2 = $iv & 16;
if	( $i2 > 0 )
	{
	$i1 += $i1;
	$resu .= $cns2[$i1];
	$resu .= $cns2[$i1+1];
	}
else	{
	$resu .= $cons[$i1+2];
	}
$iv >>= 5;
$i1 = $iv & 3;
$resu .= $voys[$i1+4];
$iv >>= 2;
$i2 = $iv & 7;
if	( $i2 > 0 )
	{
	$i2 += ($i1 << 3);
	$resu .= $fins[$i2];
	}
return $resu;
}

// le matricule sera genere automatiquement (auto-increment)
// cette fonction est seulement pour generation automatique, pas pour traitement POST
function add_eleve( $school, $nom, $prenom, $date, $classe ) {
	$sqlrequest = "INSERT INTO `{$school->table_eleves}` (`nom`, `prenom`, `date_n`, `classe` ) " .
			"VALUES ('$nom', '$prenom', '$date', '$classe' );";
	$result = $school->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($school->db->conn) );
	}

// cette fonction est seulement pour generation automatique, pas pour traitement POST
function add_classe( $school, $index, $nom ) {
	$sqlrequest = "INSERT INTO `{$school->table_classes}` (`indix`, `nom`) VALUES ('$index', '$nom');";
	// echo $sqlrequest, '<br>';
	$result = $school->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($school->db->conn) );
	}

// rend bool
function exist_classe( $school, $index ) {
	$sqlrequest = "SELECT COUNT(*) FROM `{$school->table_classes}` WHERE `indix` = $index;";
	$result = $school->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($school->db->conn) );
	if	( $row = mysqli_fetch_assoc($result) )
		{
		// echo $index, "~~"; print_r($row); echo '<br>';
		$i = $row['COUNT(*)'];
		if	( $i == 0 ) return FALSE;
		else if	( $i == 1 ) return TRUE;
		}
	}

// cette fonction depend de longage.php et school.php
function add_random_eleves( $school, $quant ) {
	for	( $i = 0; $i < $quant; $i++ )
		{
		$r = random_int( 0, 65535 );
		$nom = vocable( $r, 1 );
		$r = random_int( 0, 65535 );
		$prenom = vocable( $r, 1 );
		$cla = ( ( $r & 0x1FF ) % 7 );		// de 0 a 6
		$abc = ( ( $r >> 9 ) & 7 ) + 1;		// de 1 a 8 --> A a H
		$classe = $cla * 100 + $abc;
		if	( !exist_classe( $school, $classe ) )
			{
			$nomcla = (( $cla == 0 )?("Term "):("{$cla}e "));
			$nomcla .= chr( ord('A') + $abc - 1 );
			add_classe( $school, $classe, $nomcla );
			}
		$y = 2002 + $cla;
		$m = ( ( $r >> 12 ) & 15 ) + 1;		// de 1 a 16
		$d = $m + $cla;
		if	( $m > 12 )
			{ $m -= 12; $y += 1; }
		// $date = "$y-$m-$d";
		$utime = mktime( 13, 0, 0, $m+1, $d, $y );
		// echo "$nom, $prenom, $utime, $classe<br>";
		add_eleve( $school, $nom, $prenom, $utime, $classe );
		}
	$sqlrequest = "SELECT COUNT(*) FROM `{$school->table_eleves}`";
	$result = $school->db->conn->query( $sqlrequest );
	if	(!$result) mostra_fatal( $sqlrequest . "<br>" . mysqli_error($school->db->conn) );
	if	( $row = mysqli_fetch_assoc($result) )
		{
		$cnt = $row['COUNT(*)'];
		echo "Total $cnt eleves<br>";
		}
	}

?>