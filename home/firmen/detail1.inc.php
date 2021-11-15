<?php
 
 $sqlquery = isset($_REQUEST['sqlquery ']) ? $_REQUEST['sqlquery '] : '';

 //if (!$s_kandidatentable)
 //require("/home/praktika/etc/kandidatensuchecache.inc.php");

// datensatz abfragen //
$sql = sprintf("SELECT 
					*, 
					nutzer.id AS nutzerid, 
					nutzer.studiengang AS nutzerstudiengang,
					praktikanten.id AS pid, 
					date_format(praktikanten.modify, '%%d.%%m.%%Y - %%H:%%i') AS modifydatum 
				FROM 
					nutzer, 
					praktikanten 
				WHERE 
					praktikanten.id = '%1\$d' AND 
					praktikanten.nutzerid = nutzer.id 
					%2\$s
				LIMIT 
					1",
				$id,
				$sqlquery);
$result = mysql_db_query($database_db, $sql, $praktdbslave);
$num_rows = mysql_num_rows($result);

// array erzeugen //
$row = mysql_fetch_array($result);

// alter berechnen
$zeit = time();
$geburtstag = strtotime($row['geburtsdatum']);
$alterdiff = $zeit - $geburtstag;
$diff = 31560000; // 365 Tage
$alter = $alterdiff / $diff;

$metatags['Title'] = $row['taetigkeit'].", ".$row['vname']." (".sprintf("%01.0u", $alter).") - ".$row['geschlecht']." / ".$row['hochschule']." Praktikum ab: ".$row['zeitraum_ab_m']."/".$row['zeitraum_ab_y']." ".$row['zeitraum'];
$metatags['Keywords'] = "Praktikant, Profil, Qualifikation, Berufsstart, Foto, Student, Stelle, Gesuch, Praktikum";
$metatags['Description'] = $row['geschlecht']." / ".$row['hochschule']." ".$row['branchentext']." Praktikum ab: ".$row['zeitraum_ab_m']."/".$row['zeitraum_ab_y']." ".$row['zeitraum'];

//echo $sql."<hr/>";

/* Datumskonvertierfunktion MySQL -> de */
$row['zeitraum_von'] = strtotime($row['zeitraum_ab_y']."-".$row['zeitraum_ab_m']."-01");

echo '
      <h4>'.$language['strQuallifikationen'].'</h4>
      <table width="100%">
        <colgroup>
          <col width="30%" />
          <col width="70%" />
        </colgroup>';
if (!empty($row['nutzerstudiengang']) && $row['nutzerstudiengang'] != 'keiner') {
	$ins = $row['nutzerstudiengang'];
	$suchres = mysql_db_query($database_db, "SELECT studiengang FROM studiengaenge WHERE id = '$ins'", $praktdbslave);
	
	if (mysql_num_rows($suchres) > 0) {
		echo '
	        <tr>
	          <td>'.$language['strStudien'].'</td>
	          <td>'.mysql_result($suchres, 0, "studiengang").'</td>
	        </tr>';
	}

	if (!$row['semester'] == "") {
		echo '
        <tr>
          <td>'.$language['strSemester'].'</td>
          <td>'.$row['semester'].' '.$language['strVon'].' '.$row['regelstudienzeit'].' '.$language['strSemester2'].'</td>
        </tr>';
	}
}

if (!empty($row['qualifikation'])) {
	$ins = $row['qualifikation'];
	$suchres = mysql_db_query($database_db, "SELECT qualifikation FROM qualifikation WHERE id = '$ins'", $praktdbslave);
	echo '
        <tr>
          <td>'.$language['strQualifikation'].'</td>
          <td>'.mysql_result($suchres, 0, "qualifikation").'</td>
        </tr>';
}
if (!empty($row['sonst_qualifikation'])) {
	echo '
        <tr>
          <td>'.$language['strSonstige'].'</td>
          <td>'.$row['sonst_qualifikation'].'</td>
        </tr>';
}
if (!empty($row['fuehrerschein'])) {
	echo '
        <tr>
          <td>'.$language['strFuehrerschein'].'</td>
          <td>'.$row['fuehrerschein'].'</td>
        </tr>';
}
if (!empty($row['umschulung'])) {
	echo '
        <tr>
          <td>besondere Kenntnisse:</td>
          <td>'.$row['umschulung'].'</td>
        </tr>';
}
if (!empty($row['vorh_praktika'])) {
	echo '
        <tr>
          <td>'.$language['strVorherige'].'</td>
          <td>'.$row['vorh_praktika'].'</td>
        </tr>';
}

echo '
        <tr>
          <td>'.$language['strSprachk'].'</td>
          <td>';
if ($row['sprachkenntnisse1']  > 0) {
	$ins = $row['sprachkenntnisse1'];
	$suchres = mysql_db_query($database_db, 'SELECT '.$_SESSION['s_sprache'].' FROM sprachen WHERE id = '.$ins, $praktdbslave);
	echo mysql_result($suchres,0,$_SESSION['s_sprache']);
}
if ($row['sprachkenntnisse2']  > 0) {
	$ins = $row['sprachkenntnisse2'];
	$suchres = mysql_db_query($database_db, 'SELECT '.$_SESSION['s_sprache'].' FROM sprachen WHERE id = '.$ins, $praktdbslave);
	echo ",<br />".mysql_result($suchres,0,$_SESSION['s_sprache']);
}
if ($row['sprachkenntnisse3']  > 0) {
	$ins = $row['sprachkenntnisse3'];
	$suchres = mysql_db_query($database_db, 'SELECT '.$_SESSION['s_sprache'].' FROM sprachen WHERE id = '.$ins, $praktdbslave);
	echo ",<br />".mysql_result($suchres,0,$_SESSION['s_sprache']);
}
echo '</td>
        </tr>
        <tr>
          <td colspan="2"></td>
        </tr>
      </table>';

echo '
      <h4>Gesuchter Praktikumsplatz</h4>
      <table width="100%">
        <colgroup>
          <col width="30%" />
          <col width="70%" />
        </colgroup>';
if (!empty($row['taetigkeit']) && ($row['taetigkeit'] != $row['beschreibung']) ) {
	echo '
        <tr>
          <td>'.$language['strAngestrebte'].'</td>
          <td>'.nl2br($row['taetigkeit']).'</td>
        </tr>';
}
if (!empty($row['berufsfeld'])) {
	$ins = $row['berufsfeld'];
	$suchres = @mysql_db_query($database_db, 'SELECT '.$_SESSION['s_sprache'].' FROM berufsfelder WHERE id = '.$ins, $praktdbslave);
	echo '
        <tr>
          <td>'.$language['strBerufsfeld'].'</td>
          <td>'.@mysql_result($suchres, 0, $_SESSION['s_sprache']).'</td>
        </tr>';
}
if (!empty($row['praktikumsart'])) {
	$ins = $row['praktikumsart'];
	$suchres = mysql_db_query($database_db, "SELECT art FROM praktikumsart WHERE id = $ins", $praktdbslave);
	echo '
        <tr>
          <td>'.$language['strPraktikumsart'].':</td>
          <td>'.mysql_result($suchres,0,"art").'</td>
        </tr>';
}

if (!empty($row['studienort'])) {
	echo '
        <tr>
          <td>Praktikumsort:</td>
          <td>'.$row['studienort'].'</td>
        </tr>';
}

if (empty($row['zeitraum'])) {
	if($row['zeitraum_ab_m'] == 0 || $row['zeitraum_ab_y'] == 0) {
		echo '
        <tr>
          <td>'.$language['strZeitraum'].'</td>
          <td>'.$language['strKeineAngaben'].'</td>
        </tr>';
	} else {
		if ( $row['zeitraum_von'] > strtotime ("now") ) {
			echo '
        <tr>
          <td>'.$language['strZeitraum'].'</td>
          <td>ab '.$row['zeitraum_ab_m'].'/'.$row['zeitraum_ab_y'].'</td>
        </tr>';
		}
	}
} else {
	if ($row['zeitraum_ab_m'] == 0 || $row['zeitraum_ab_y'] == 0) {
		echo '
        <tr>
          <td>'.$language['strZeitraum'].'</td>
          <td>'.$row['zeitraum'].'</td>
        </tr>';
	} else {
		if ( $row['zeitraum_von'] > strtotime ("now") ) {
			echo '
        <tr>
          <td>'.$language['strZeitraum'].'</td>
          <td>ab '.$row['zeitraum_ab_m'].'/'.$row['zeitraum_ab_y'].'<br />'.$row['zeitraum'].'</td>
        </tr>';
		}
	}
}

if (!empty($row['beschreibung'])) {
	echo '
        <tr>
          <td>'.$language['strBeschreibung'].'</td>
          <td>'.nl2br($row['beschreibung']).'</td>
        </tr>';
}
echo '
      </table>';
?>