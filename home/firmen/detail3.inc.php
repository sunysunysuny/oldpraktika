<?php
 $gruppe = "praktikanten";

 //if (!$s_kandidatentable)
 //require("/home/praktika/etc/kandidatensuchecache.inc.php");

// datensatz abfragen //
$sql = sprintf("SELECT 
					*, 
					date_format(nebenjobgesuch.modify, '%%d.%%m.%%Y - %%H:%%i') AS modifydatum, 
					nutzer.id AS nutzerid, 
					nebenjobgesuch.id AS pid 
				FROM 
					nutzer, 
					nebenjobgesuch 
				WHERE 
					nebenjobgesuch.id = '%1\$d' AND 
					(nebenjobgesuch.nutzerid = nutzer.id)",
				$id);
//echo $sql."<hr>";
$result = mysql_db_query($database_db, $sql, $praktdbslave);
$num_rows = mysql_num_rows($result);

// array erzeugen //
$row = mysql_fetch_array($result);

$metatags["Title"] = mysql_result($result,0,"taetigkeit").", ".mysql_result($result,0,"vname")." (".sprintf("%01.0u",$alter).") - ".mysql_result($result,0,"geschlecht")." / ".mysql_result($result,0,"arbeitsort") . " Nebenjob ab: ".mysql_result($result,0,"zeitraum_ab_m")."/".mysql_result($result,0,"zeitraum_ab_y")." ".mysql_result($result,0,"zeitraum");
$metatags["Keywords"] = mysql_result($result,0,"kenntnisse").", Profil, Qualifikation, Berufsstart, Foto, Student, Stelle, Gesuch, Nebenjob";
$metatags["Description"] = mysql_result($result,0,"geschlecht")." / ".mysql_result($result,0,"hochschule") . " " . mysql_result($result,0,"branchentext")." Nebenjob ab: ".mysql_result($result,0,"zeitraum_ab_m")."/".mysql_result($result,0,"zeitraum_ab_y")." ".mysql_result($result,0,"zeitraum");

echo '
      <h4>' . $language["strQuallifikationen"] . '</h4>
      <table width="100%">
        <colgroup>
          <col width="30%" />
          <col width="70%" />
        </colgroup>';
if ($row["qualifikation"] != "") {
	$ins = $row["qualifikation"];
	$suchres = mysql_db_query($database_db, "SELECT qualifikation FROM qualifikation WHERE id='$ins'", $praktdbslave);
	echo '
        <tr>
          <td>' . $language["strQualifikation"] . '</td>
          <td>' . mysql_result($suchres, 0, "qualifikation") . '</td>
        </tr>';
}
if ($row["sonst_qualifikation"] != "") {
	echo '
        <tr>
          <td>' . $language["strSonstige"] . '</td>
          <td>' . $row["sonst_qualifikation"] . '</td>
        </tr>';
}
if ($row["fuehrerschein"] != "") {
	echo '
        <tr>
          <td>' . $language["strFuehrerschein"] . '</td>
          <td>' . $row["fuehrerschein"] . '</td>
        </tr>';
}
if ($row["kenntnisse"] != "") {
	echo '
        <tr>
          <td>' . $language["strUmschulung"] . '</td>
          <td>' . $row["kenntnisse"] . '</td>
        </tr>';
}
if ($row["vorh_unternehmen"] != "") {
	echo '
        <tr>
          <td>vorherige Unternehmen:</td>
          <td>' . $row["vorh_unternehmen"] . '</td>
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
      <h4>Nebenjobgesuch</h4>
      <table width="100%">
        <colgroup>
          <col width="30%" />
          <col width="70%" />
        </colgroup>';
if ( $row["taetigkeit"] != "" && ($row["taetigkeit"] != $row["beschreibung"]) ) {
	echo '
        <tr>
          <td>' . $language["strAngestrebte"] . '</td>
          <td>' . nl2br($row["taetigkeit"]) . '</td>
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
if ($row["arbeitsort"] != "") {
	echo '
        <tr valign="baseline">
          <td>Arbeitsort:</td>
          <td>' . $row["arbeitsort"] . '</td>
        </tr>';
}
if (!$row["einsatzland"] == "") {
	$ins = $row["einsatzland"];
	$suchres = mysql_db_query($database_db, "SELECT german FROM land WHERE id = $ins", $praktdbslave);
	echo '
        <tr>
          <td>' . $language["strLand"] . '</td>
          <td>' . mysql_result($suchres, 0, "german") . '</td>
        </tr>';
}

if ($row["zeitraum"] == "") {
	if($row["zeitraum_ab_m"] == 0 || $row["zeitraum_ab_y"] == 0) {
		echo '
        <tr valign="baseline">
          <td>' . $language["strZeitraum"] . '</td>
          <td valign="top">' . $language["strKeineAngaben"] . '</td>
        </tr>';
	} else {
		echo '
        <tr valign="baseline">
          <td>' . $language["strZeitraum"] . '</td>
          <td>ab ' . $row["zeitraum_ab_m"] . '/' . $row["zeitraum_ab_y"] . '</td>
        </tr>';
	}
} else {
	if($row["zeitraum_ab_m"] == 0 || $row["zeitraum_ab_y"] == 0) {
		echo '
        <tr valign="baseline">
          <td>' . $language["strZeitraum"] . '</td>
          <td valign="top">' . $row["zeitraum"] . '</td>
        </tr>';
	} else {
		echo '
        <tr valign="baseline">
          <td>' . $language["strZeitraum"] . '</td>
          <td>ab ' . $row["zeitraum_ab_m"] . '/' . $row["zeitraum_ab_y"] . '<br/>' . $row["zeitraum"] . '</td>
        </tr>';
	}
}
echo '
      </table>';
?>