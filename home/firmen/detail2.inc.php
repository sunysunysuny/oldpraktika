<?php
 $gruppe = "praktikanten";
 $sprachbasisdatei = "/home/firmen/detail.phtml";
 $sprachbasisdatei2="/absolventen/praktikanten/detail.phtml";
 
 //if (!$s_kandidatentable)
 //require("/home/praktika/etc/kandidatensuchecache.inc.php");

// datensatz abfragen //
$sql = sprintf("SELECT 
					*, 
					date_format(diplomgesuch.modify, '%%d.%%m.%%Y - %%H:%%i') AS modifydatum, 
					nutzer.titel AS nutzertitel, 
					diplomgesuch.id AS pid 
				FROM 
					nutzer, 
					diplomgesuch 
				WHERE 
					diplomgesuch.id = '%1\$d' AND 
					(diplomgesuch.nutzerid = nutzer.id)",
				$id);
$result = mysql_db_query($database_db, $sql, $praktdbslave);
$num_rows = mysql_num_rows($result);

// array erzeugen //
$row = mysql_fetch_array($result);

$metatags["Title"] = $row["titel"].", ".$row["vname"]." (".sprintf("%01.0u",$alter).") - ".$row["geschlecht"]." / ".$row["hochschule"]." Diplomarbeit ab: ".$row["zeitraum_ab_m"]."/".$row["zeitraum_ab_y"]." ".$row["zeitraum"];
$metatags["Keywords"] = $row["schlagwort"].", Diplomand, Profil, Qualifikation, Berufsstart, Foto, Student, Stelle, Gesuch, Diplom";
$metatags["Description"] = $row["geschlecht"]." / ".$row["hochschule"]." ".$row["branchentext"]." Diplomarbeit ab: ".$row["zeitraum_ab_m"]."/".$row["zeitraum_ab_y"]." ".$row["zeitraum"];

if ($s_loggedin == true) {
	$link = "/home/praktikanten/profilausgabe/einzelansicht.phtml?id=" . $nutzerid . "&amp;bewerbsprache=2&amp;einstellungen=true&amp;gesuche=no"; $params="width=720,height=600";
} else {
	$link = "/home/quicklogin/minilogin.phtml"; $params="width=380,height=250";
}

echo '
      <h4>Diplomgesuch</h4>
      <table width="100%">
        <colgroup>
          <col width="30%" />
          <col width="70%" />
        </colgroup>';
if ($row["studiengang"] != "" && $row["studiengang"] > 0) {
	$ins = $row["studiengang"];
	$suchres = mysql($database_db, "SELECT studiengang FROM studiengaenge WHERE id = '$ins'", $praktdbslave);
	echo '
        <tr valign="baseline">
          <td>Studienrichtung:</td>
          <td>' . mysql_result($suchres, 0, "studiengang") . '</td>
        </tr>';
}

if ($row["studienort"] != "") {
	echo '
        <tr valign="baseline">
          <td>Studienort:</td>
          <td>' . $row["studienort"] . '</td>
        </tr>';
}
echo '
        <tr valign="baseline">
          <td>Titel der Diplomarbeit:</td>
          <td>' . $row["titel"] . '</td>
        </tr>
        <tr valign="baseline">
          <td>Beschreibung der Diplomarbeit:</td>
          <td>' . nl2br($row["beschreibung"]) . '</td>
        </tr>';
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
$landid = $row["einsatzland"];
if ($landid > 0) {
	$landselect = mysql_db_query($database_db,"SELECT german,id FROM land WHERE id = $landid",$praktdbslave);
	echo '
        <tr valign="baseline">
          <td>Einsatzland:</td>
          <td>' . mysql_result($landselect, 0, "german") . '</td>
        </tr>';
}
echo '
      </table>';
?>
