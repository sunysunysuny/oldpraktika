<?php
 $gruppe = "praktikanten";

 //if (!$s_kandidatentable)
 //require("/home/praktika/etc/kandidatensuchecache.inc.php");


// datensatz abfragen //
$sql = sprintf("SELECT 
					*, 
					date_format(ausbildungsgesuch.modify, '%%d.%%m.%%Y - %%H:%%i') AS modifydatum, 
					nutzer.id AS nutzerid, 
					ausbildungsgesuch.id AS pid,
					ausbildungsgesuch.grossraum AS grossraum1, 
					nutzer.grossraum AS grossraum2
				FROM 
					nutzer, 
					ausbildungsgesuch 
				WHERE 
					ausbildungsgesuch.id = '%1\$d' AND 
					ausbildungsgesuch.nutzerid = nutzer.id",
				$id);
//echo $sql."<hr>";
$result = mysql_db_query($database_db, $sql, $praktdbslave);
$num_rows = mysql_num_rows($result);

// array erzeugen //
$row = mysql_fetch_array($result);

$metatags["Title"] = $row["taetigkeit"].", ".mysql_result($result,0,"vname")." (".sprintf("%01.0u",$alter).") - " . mysql_result($result,0,"geschlecht")." / Ausbildung ab: ".mysql_result($result,0,"zeitraum_ab_m")."/".mysql_result($result,0,"zeitraum_ab_y")." ".mysql_result($result,0,"zeitraum");
$metatags["Keywords"] = "Ausbildung, Profil, Azubi, Berufsstart, Foto, Student, Stelle, Gesuch, Ausbildungsplatz";
$metatags["Description"] = mysql_result($result,0,"geschlecht")." / " . mysql_result($result,0,"branchentext")." Ausbildung ab: ".mysql_result($result,0,"zeitraum_ab_m")."/".mysql_result($result,0,"zeitraum_ab_y")." ".mysql_result($result,0,"zeitraum");

echo '
      <h4>Ausbildungsgesuch</h4>
      <table width="100%">
        <colgroup>
          <col width="30%" />
          <col width="70%" />
        </colgroup>';
$lehreid = $row["lehre"];
if ($lehreid >0) {
	if ($row["anrede"] == "Herr") {
		$lehre=mysql_db_query($database_db, "SELECT berufswahl FROM berufswahl_mann WHERE id = $lehreid", $praktdbslave);
	} else {
		$lehre=mysql_db_query($database_db, "SELECT berufswahl FROM berufswahl_frau WHERE id = $lehreid", $praktdbslave);
	}
	echo '
        <tr>
          <td>Lehrausbildung zum:</td>
          <td>' . mysql_result($lehre,0,"berufswahl") . '</td>
        </tr>';
}
if (! $row["grossraum1"] == "") {
	$grossraumid = $row["grossraum1"];
	$grossraum = mysql_db_query($database_db, "SELECT grossraum FROM grossraum WHERE id = $grossraumid", $praktdbslave);
	echo '
        <tr valign="top">
          <td>gesucht im Gro&szlig;raum:</td>
          <td>' . mysql_result($grossraum, 0, "grossraum") . '</td>
        </tr>';
}
echo '
        <tr valign="top">
          <td>Beschreibung der Lehrstelle:</td>
          <td>' . $row["beschreibung"] . '</td>
        </tr>';
echo '
        <tr valign="baseline">
          <td>frühester Beginn:</td>
          <td>ab ' . $row["zeitraum_ab_m"] . '/' . $row["zeitraum_ab_y"] . '</td></tr>';
if ($row["fuehrerschein"] != "") {
	echo '
        <tr valign="baseline">
          <td>F&uuml;hrerschein:</td>
          <td>' . $row["fuehrerschein"] . '</td>
        </tr>';
}
if ($row["berufsfeld"] != "") {
	$ins = $row["berufsfeld"];
	$suchres = @mysql_db_query($database_db, "SELECT german FROM berufsfelder WHERE id = $ins", $praktdbslave);
	echo '
        <tr>
          <td>' . $language["strBerufsfeld"] . '</td>
          <td>' . @mysql_result($suchres, 0, "german") . '</td>
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
echo '
        <tr valign="top">
          <td>T&auml;tigkeit:</td>
          <td>' . $row["taetigkeit"] . '</td>
        </tr>';
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
      </table>';
?>


