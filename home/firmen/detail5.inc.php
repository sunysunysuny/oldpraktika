<?php
 //if (!$s_kandidatentable)
 //require("/home/praktika/etc/kandidatensuchecache.inc.php");


// datensatz abfragen //
if ($nutzerid) $sqlquery = "AND berufseinstieggesuch.nutzerid=$nutzerid";
$sql = sprintf("SELECT 
					*, 
					date_format(berufseinstieggesuch.modify, '%%d.%%m.%%Y - %%H:%%i') AS modifydatum, 
					nutzer.id AS nutzerid, 
					berufseinstieggesuch.id AS pid
				FROM 
					nutzer, 
					berufseinstieggesuch 
				WHERE 
					berufseinstieggesuch.id = '%1\$d' AND 
					berufseinstieggesuch.nutzerid = nutzer.id",
				$id);
$result = mysql_db_query($database_db, $sql, $praktdbslave);

// array erzeugen //
$row = mysql_fetch_array($result);

$metatags["Title"] = mysql_result($result,0,"beschaeftigung").", ".mysql_result($result,0,"vname")." (".sprintf("%01.0u",$alter).") - ".mysql_result($result,0,"geschlecht")." / ".mysql_result($result,0,"regionales") . " Berufseinstieg ab: ".mysql_result($result,0,"zeitraum_ab_m")."/".mysql_result($result,0,"zeitraum_ab_y")." ".mysql_result($result,0,"gehalt");
$metatags["Keywords"] = "Berufseinsteiger, Profil, Qualifikation, Berufsstart, Foto, Student, Stelle, Gesuch, Berufseinstieg";
$metatags["Description"] = mysql_result($result,0,"geschlecht")." / ".mysql_result($result,0,"hochschule") . " " . mysql_result($result,0,"branchentext")." Berufseinstieg ab: ".mysql_result($result,0,"zeitraum_ab_m")."/".mysql_result($result,0,"zeitraum_ab_y")." ".mysql_result($result,0,"gehalt");

echo '
      <h4>' . $language["strQuallifikationen"] . '</h4>
      <table width="100%">
        <colgroup>
          <col width="30%" />
          <col width="70%" />
        </colgroup>';
$ins = ($row["karierrestatus"] != 0) ? $row["karierrestatus"]: 1;
$suchres = mysql_db_query($database_db, "SELECT german FROM karierrestatus WHERE id = $ins", $praktdbslave);
echo '
        <tr valign="baseline">
          <td>Karrierestatus:</td>
          <td>' . mysql_result($suchres, 0, "german") . '</td>
        </tr>';
if ( $row["studiengang"] != 0) {
	$ins = $row["studiengang"];var_dump("SELECT studiengang FROM studiengaenge WHERE id = '$ins'");
	$suchres = mysql_db_query($database_db, "SELECT studiengang FROM studiengaenge WHERE id = '$ins'", $praktdbslave);
	echo '
        <tr valign="baseline">
          <td>' . $language["strStudien"] . '</td>
          <td>' . mysql_result($suchres, 0, "studiengang") . '</td>
        </tr>';
}

if ( $row["lehre"] != 0 && !empty($nutzerid)) {
	$geschlecht=mysql_db_query($database_db, "SELECT anrede FROM nutzer WHERE id = $nutzerid", $praktdbslave);
	$lehreid=$row["lehre"];
	if (mysql_result($geschlecht,0,"anrede") == "Herr") {
		$berufswahl = mysql_db_query($database_db, "SELECT berufswahl,id FROM berufswahl_mann WHERE id = $lehreid", $praktdbslave);
	} else {
		$berufswahl = mysql_db_query($database_db, "SELECT berufswahl,id FROM berufswahl_frau WHERE id = $lehreid", $praktdbslave);
	}
	echo '
        <tr valign="baseline">
          <td>Berufsabschluss:</td>
          <td>' . mysql_result($berufswahl, 0, "berufswahl") . '</td>
        </tr>';
}

if ($row["qualifikation"] != "") {
	$ins = $row["qualifikation"];
	$suchres = mysql_db_query($database_db, "SELECT qualifikation FROM qualifikation WHERE id='$ins'", $praktdbslave);
	echo '
        <tr>
          <td>' . $language["strQualifikation"] . '</td>
          <td>' . mysql_result($suchres, 0, "qualifikation") . '</td>
        </tr>';
}
if ($row["fuehrerschein"] != "") {
	echo '
        <tr>
          <td>' . $language["strFuehrerschein"] . '</td>
          <td>' . $row["fuehrerschein"] . '</td>
        </tr>';
}

echo '
        <tr>
          <td>' . $language["strSprachk"] . '</td>
          <td>';
if ($row["sprachkenntnisse1"]  > 0) {
	$ins = $row["sprachkenntnisse1"];
	$suchres = mysql_db_query($database_db, "SELECT german FROM sprachen WHERE id = '$ins'", $praktdbslave);
	echo mysql_result($suchres,0,"german");
}
if ($row["sprachkenntnisse2"]  > 0) {
	$ins = $row["sprachkenntnisse2"];
	$suchres = mysql_db_query($database_db, "SELECT german FROM sprachen WHERE id = '$ins'", $praktdbslave);
	echo ",<br />" . mysql_result($suchres,0,"german");
}
if ($row["sprachkenntnisse3"]  > 0) {
	$ins = $row["sprachkenntnisse3"];
	$suchres = mysql_db_query($database_db, "SELECT german FROM sprachen WHERE id = '$ins'", $praktdbslave);
	echo ",<br />" . mysql_result($suchres,0,"german");
}
echo '</td>
        </tr>
        <tr>
          <td colspan="2"></td>
        </tr>
      </table>';

echo '
      <h4>Gesuchter Arbeitsplatz</h4>
      <table width="100%">
        <colgroup>
          <col width="30%" />
          <col width="70%" />
        </colgroup>
        <tr valign="baseline">
          <td>Berufswunsch:</td>
          <td>' . nl2br($row["bezeichnung"]) . '</td>
        </tr>';
$ins = $row["berufsfeld"];
$suchres = @mysql_db_query($database_db, "SELECT german FROM berufsfelder WHERE id = $ins", $praktdbslave);
echo '
        <tr valign="baseline">
          <td>' . $language["strBerufsfeld"] . '</td>
          <td>' . @mysql_result($suchres,0,"german") . '</td>
        </tr>
        <tr valign="baseline">
          <td>Berufseinstieg als:</td>
          <td>' . nl2br($row["beschaeftigung"]) . '</td>
        </tr>
        <tr valign="baseline">
          <td>' . $language["strZeitraum"] . '</td>
          <td valign="top">ab '.sprintf("%02.0f",$row["zeitraum_ab_m"]).' / '.$row["zeitraum_ab_y"].'</td>
        </tr>';

if ($row["beschreibung"] != "") {
	echo '
        <tr valign="baseline">
          <td>Vorstellungen zur beruflichen Tätigkeit:</td>
          <td>' . nl2br($row["beschreibung"]) . '</td>
        </tr>';
}

$waehrungsid = $row["waehrung"];
if ($waehrungsid >0) {
	$waehrung = @mysql_db_query($database_comm, "SELECT bezeichner FROM waehrungen WHERE id = $waehrungsid", $praktdbslave);
	$waehungausgabe = mysql_result($waehrung, 0, "bezeichner");
}
if ($row["gehalt"] != "keine Angabe") {
	echo '
        <tr valign="baseline">
          <td>Gehaltsvorstellungen p.a.:</td>
          <td>' . $row["gehalt"] . ' '.$waehungausgabe.'</td>
        </tr>';
} else {
	echo '
        <tr valign="baseline">
          <td>Gehaltsvorstellungen p.a.:</td>
          <td>' . $row["gehalt"] . '</td>
        </tr>';
}

if ($einsicht == 1)  {
	if ($row["regionales"] != "") {
		echo '
        <tr valign="baseline">
          <td>Regionale Wünsche:</td>
          <td>' . nl2br($row["regionales"]) . '</td>
        </tr>';
	}
}
echo '
      </table>';
?>