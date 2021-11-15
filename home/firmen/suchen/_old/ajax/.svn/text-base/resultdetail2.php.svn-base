<?
//  praktika.de ajax File - KANDIDATENAUSGABE -  WENN FIRMEN SUCHEN

header("Content-Type: text/xml");

require("/home/praktika/etc/dbzugang.inc.php");
require("/home/praktika/etc/lib.inc.php");

if ($suchstring == "warenkorbanzeige") {
	$ajaxtype = "warenkorbanzeige";
} else {
	$ajaxtype = "results";
}


if (!$s_sprache) $s_sprache = "german";
if (!$spalte2quicklogindunkel) $spalte2quicklogindunkel = "#E8C9BA";

// Start *************************************
srand ( (double)microtime() * 1000000 );
$num = rand(0, ( count($dbarray) - 1 ) );

$praktdbmaster = @mysql_pconnect($dbarray[0]["host"], $dbarray[0]["mysqluser"], $dbarray[0]["mysqlpassword"]);
$praktdbslave = @mysql_pconnect($dbarray[$num]["host"], $dbarray[$num]["mysqluser"], $dbarray[$num]["mysqlpassword"]);

session_name(praktika_id);
session_save_path("/home/praktika/sessions");
session_start();

$rueckgabe .=  '<table width="100%">';

// datensatz abfragen //
$sql = sprintf("SELECT 
					*,
					nutzer.id AS nutzerid,
					DATE_FORMAT(nutzer.modify, '%%d.%%m.%%Y - %%H:%%i') AS modifydatum 
				FROM 
					nutzer 
				WHERE 
					id = '%d'",
				$_REQUEST["nutzerid"]);
$result = mysql($database_db, $sql, $praktdbslave);

// array erzeugen
$eintrag = mysql_fetch_array($result);

// Statistikinfos speichern
$aktdatum = date('YmdHis');
$userid = $eintrag["nutzerid"];
$sql = sprintf("INSERT INTO 
					viewstatspraktikanten (nutzerid, userid, spalte, modify) 
				VALUES 
					('%1\$d', '%2\$s', '%3\$s', '%4\$s')",
				$userid,
				$s_loginid,
				$s_spalte,
				$aktdatum);
mysql($database_db, $sql, $praktdbmaster);

// alter berechnen
$zeit = time();
$geburtstag = strtotime($eintrag["geburtsdatum"]);
$alterdiff = $zeit - $geburtstag;
$diff = 31560000; // 365 Tage
$alter = $alterdiff / $diff;

// Firmenbookmark Beginn
if ($userid) {
	$sql = sprintf("SELECT 
						id 
					FROM 
						firmenbookmark 
					WHERE 
						firmenid = %1\$d AND 
						nutzerid = %2\$d",
					$s_firmenid,
					$userid);
	mysql($database_db, $sql, $praktdbslave);
	#$check = "SELECT id FROM firmenbookmark WHERE firmenid='$s_firmenid' AND nutzerid=$userid";
	$result = mysql($database_db, $sql, $praktdbslave);
	$num_rows = mysql_num_rows( $result );
}
// Firmenbookmark Ende

// auf Merkzettel setzen
if ($s_firmenproparray["kandidatensuche"]["warenkorb"][$userid]) $rueckgabe .= '
  <tr valign="baseline">
    <td><a href="javascript:Load_SearchData('."'".$ajaxtype."', document.getElementById('sortierung').value, document.getElementById('ksuchstring').value, document.getElementById('categoryids').value, '".$_REQUEST["ds_count"]."'".');">zur Ergebnisliste</a></td>
    <td align="right">Der Kandidat wurde ausw&auml;hlt!<br></td>
  </tr>';


// Beginn Firmenbookmark Anzeige
if (!$s_firmenproparray["kandidatensuche"]["warenkorb"][$userid]) {
	$rueckgabe .= '
  <tr valign="baseline">
    <td><a href="javascript:Load_SearchData('."'".$ajaxtype."', document.getElementById('sortierung').value, document.getElementById('ksuchstring').value, document.getElementById('categoryids').value, '".$_REQUEST["ds_count"]."'".');">zur Ergebnisliste</a></td>
    <td align="right"><a href="javascript:WarenkorbSubmit('."'".$userid."','detail','".$_REQUEST["ds_count"]."'".');">Kandidat ausw&auml;hlen</a></td>
  </tr>';
}
// Ende Firmenbookmark Anzeige


// Wer darf was sehen
if ($num_rows == 0) $einsicht=0;
if ($num_rows > 0 || $s_firmenlevel == 2) $einsicht=1;

$rueckgabe .= '
  <tr valign="baseline">
    <td colspan="2" bgcolor="'.$spalte2quicklogindunkel.'"><b>Bewerber</b></td>
  </tr>
  <tr>';
if ($s_loggedin == "yes") { 
	$link = "/home/praktikanten/profilausgabe/einzelansicht.phtml?id=" . $userid . "&bewerbsprache=2&einstellungen=true&gesuche=no&"; $params="width=720,height=600"; 
} else { 
	$link = "/home/quicklogin/minilogin.phtml?loginwer=Unternehmen&"; $params="width=380,height=250"; 
}
$rueckgabe .= '
  <tr valign="baseline">
    <td>&nbsp;</td>
    <td class="normal" align="right"><a href="#" onclick="windowanm=window.open('."'".$link."', 'messageWindow', 'scrollbars=yes, statusbar=no, toolbar=no, ".$params."'".'); return false;">Bewerbungsmappe ansehen</a></td>
  </tr>
  <tr valign="baseline">
    <td width="35%">Name:</td>
    <td>';
if ( $eintrag["geschlecht"] == "weiblich") {
	$rueckgabe .= $language["strFrau"]." ";
}
if ( $eintrag["geschlecht"] == "männlich") {
	$rueckgabe .= $language["strHerr"]." ";
}
$rueckgabe .= ($eintrag["geschlecht"] == "männlich") ? $language["strHerr"]." " : $language["strFrau"]." ";
if (!$eintrag["titel"] == "") {
	$rueckgabe .= $eintrag["titel"] . " \n";
}
$rueckgabe .=  "<b>" . ucfirst($eintrag["vname"]) . " ";
if($s_loggedin != "yes") {
	$rueckgabe .= substr(ucfirst($eintrag["name"]), 0, 1).".";
} else {
	$rueckgabe .= ucfirst($eintrag["name"]);
}
$rueckgabe .= '</b></td>
  </tr>';

if ($s_loggedin == "yes") {
	if ($einsicht == 1) {
	$rueckgabe .= '
  <tr valign="baseline">
    <td>Anschrift:</td>
    <td>' . $eintrag["strasse"] . '</td>
  </tr>
  <tr valign="baseline">
    <td></td>
    <td>' . $eintrag["plz"] . ' ' . $eintrag["ort"] . '</td>
  </tr>';
	}
} else {
	$rueckgabe .= '
  <tr valign="baseline">
    <td>Anschrift:</td>
    <td class=normal><a href="#" onclick="windowanm=window.open('."'".$link."', 'messageWindow', 'scrollbars=yes,statusbar=no,toolbar=no, '".$params."'".'); return false;">Anzeige nach Login</a></td>
    </tr>';
}
if ($eintrag["grossraum"] <> 94 || $eintrag["grossraum"] > 0) {
	$such = $eintrag["grossraum"];
	$suchres = @mysql($database_db,"SELECT grossraum FROM grossraum WHERE id='$such'",$praktdbslave);
	if ($einsicht == 1) {
		$rueckgabe .= '
  <tr valign="baseline">
    <td>Gro&szlig;raum</td>
    <td>' . mysql_result($suchres, 0, "grossraum") . '</td>
  </tr>';
	}
}

if ($eintrag["bundesland"] != 0) {
	$bundeslandid = $eintrag["bundesland"];
	$bland = mysql($database_db,"SELECT $s_sprache FROM bundesland WHERE id=$bundeslandid",$praktdbslave);
	$rueckgabe .= '
  <tr valign="baseline">
    <td>Bundesland:</td>
    <td>' . mysql_result($bland, 0, $s_sprache) . '</td>
  </tr>';
}

$landid=$eintrag["land"];
if ($landid >0) {
	$land = mysql($database_db,"SELECT $s_sprache FROM land WHERE id=$landid",$praktdbslave);
	$rueckgabe .= '
  <tr valign="baseline">
    <td>Land:</td>
    <td>' . mysql_result($land, 0, $s_sprache) . '</td>
  </tr>';
}

$rueckgabe .=  '
  <tr valign="baseline">
    <td>Alter:</td>
    <td>' . sprintf("%01.0u",$alter) . ' ' . $language["strJahre"] . '</td>
  </tr>';
if ($s_loggedin=="yes") {
	if (!$eintrag["tel"] == "") {
		if ($einsicht == 1) {
			$rueckgabe .= '
  <tr valign="baseline">
    <td>Telefon:</td>
    <td>' . $eintrag["tel"] . '</td>
  </tr>';
		}
	}
} else {
	$rueckgabe .=  '
  <tr valign="baseline">
    <td>Telefon:</td>
    <td class=normal><a href="#" onclick="windowanm=window.open('."'".$link."', 'messageWindow', 'scrollbars=yes,statusbar=no,toolbar=no, '".$params."'".'); return false;">Anzeige nach Login</a></td>
  </tr>'; 
}
if ($s_loggedin=="yes") {
	if (!$eintrag["fax"] == "") {
		if ($einsicht == 1) {
			$rueckgabe .= '
  <tr valign="baseline">
    <td>Telefax:</td>
    <td>' . $eintrag["fax"] . '</td>
  </tr>';
		}
	}
} else {
	$rueckgabe .= '
  <tr valign="baseline">
    <td>Telefax:</td>
    <td class=normal><a href="#" onclick="windowanm=window.open('."'".$link."', 'messageWindow', 'scrollbars=yes,statusbar=no,toolbar=no, '".$params."'".'); return false;");">Anzeige nach Login</a></td>
  </tr>';
}

if ($s_loggedin == "yes") {
	if (!$eintrag["funktel"] == "") {
		if ($einsicht==1) {
			$rueckgabe .= '
  <tr valign="baseline">
    <td>Funktelefon:</td>
    <td>' . $eintrag["funktel"] . '</td>
  </tr>';
		}
	}
} else {
	$rueckgabe .=  '
  <tr valign="baseline">
    <td>Funktelefon:</td>
    <td class=normal><a href="#" onclick="windowanm=window.open('."'".$link."', 'messageWindow', 'scrollbars=yes,statusbar=no,toolbar=no, '".$params."'".'); return false;">Anzeige nach Login</a></td>
  </tr';
}



if ($s_loggedin == "yes") {
    if ($einsicht == 1) {
		$rueckgabe .= '
  <tr valign="baseline">
    <td>E-Mail:</td>
    <td><a href="../email/?prakt=yes&id='.$id.'">eMail verfassen</a></td>
  </tr>
  <tr valign="baseline">
    <td>Kurznachricht:</td>
    <td class="normal"><a href="#" onclick="windowanm=window.open('."'/home/firmen/firstkontakt_nutzer.phtml?gesuchid=".$eintrag["pid"]."&nutzerid=".$eintrag["nutzerid"]."&spaltenid=".$s_spalte."&firmenid=".$s_firmenid."','messageWindow','scrollbars=yes,statusbar=no,toolbar=no,width=450,height=500'".'); return false;">'.$language["strEmailKN"].'</a></td>
  </tr>';
	}


} else {
	$rueckgabe .= '
  <tr valign="baseline">
    <td>E-Mail:</td>
    <td class=normal><a href="#" onclick="windowanm=window.open('."'".$link."', 'messageWindow', 'scrollbars=yes,statusbar=no,toolbar=no, '".$params."'".'); return false;">Anzeige nach Login</a></td>
  </tr>
  <tr valign="baseline">
    <td>Kurznachricht:</td>
    <td class=normal><a href="#" onclick="windowanm=window.open('."'".$link."', 'messageWindow', 'scrollbars=yes,statusbar=no,toolbar=no, '".$params."'".'); return false;">Anzeige nach Login</a></td>
  </tr>';
}

if (!$eintrag["homepage"] == "") {
	if ($einsicht == 1) {
		$rueckgabe .= '
  <tr valign="baseline">
    <td>Homepage:</td>
    <td><a href="' . $eintrag["homepage"] . '" target="_blank">' . $eintrag["homepage"] . '</a></td>
  </tr>';
	}
}
$rueckgabe .= '
  <tr valign="baseline">
    <td>&nbsp;</td>
    <td></td>
  </tr>';

// Qualifikation
$rueckgabe .= '
  <tr valign="baseline">
    <td colspan="2" bgcolor="'.$spalte2quicklogindunkel.'"><b>Qualifikation:</b></td>
  </tr>';
if ( $eintrag["studiengang"] != "" && $eintrag["studiengang"] != "0") {
	$ins = $eintrag["studiengang"];
	$suchres = mysql($database_db,"SELECT studiengang FROM studiengaenge WHERE id='$ins'",$praktdbslave);
	$rueckgabe .= '
  <tr valign="baseline">
    <td>Studiengang:</td>
    <td>' . mysql_result($suchres,0,"studiengang") . '</td>
  </tr>';
	if (!$eintrag["hochschule"] == "") {
		$rueckgabe .= '
  <tr valign="baseline">
    <td>Studienort:</td>
    <td>' . $eintrag["hochschule"] . '</td>
  </tr>';
	}
	if (!$eintrag["karierrestatus"] == "") {
		$ins = $eintrag["karierrestatus"];
		$suchres = mysql($database_db,"SELECT german FROM karierrestatus WHERE id='$ins'",$praktdbslave);
		$rueckgabe .= '
  <tr valign="baseline">
    <td>Karierrestatus:</td>
    <td>' . mysql_result($suchres,0,"german") . '</td>
  </tr>';
	}
}


// Praktikumsstellen ermitteln

// 2 aktive Praktikumsstellen ermitteln
$results = mysql($database_db,"SELECT taetigkeit,zeitraum,id,zeitraum_ab_m,zeitraum_ab_y FROM praktikanten WHERE inactive='false' AND nutzerid = '$nutzerid' ORDER BY ID",$praktdbslave);

// datensaetze gefunden ? //
$num_rows = ($results != false) ? mysql_num_rows( $results ) : 0;

$rueckgabe .= '
  <tr>
    <td colspan="2"><br/></td>
  </tr>
  <tr valign="baseline">
    <td colspan="2" bgcolor="'.$spalte2quicklogindunkel.'"><b>Praktikumsgesuche</b></td>
  </tr>';
if ( $num_rows  > 0) {

	// Vorhandene Praktikumstellen anzeigen
	$i = 1;
	while($result_row = mysql_fetch_array($results)) {
		$rueckgabe .= '
  <tr>
    <td colspan="2" valign="top">' . $i++ . '. Praktikumsgesuch</td>
  </tr>
  <tr>
    <td valign="top">T&auml;tigkeit:</td>
    <td valign="top">' . $result_row["taetigkeit"] . '</td>
  </tr>
  <tr>
    <td valign="top">Zeitraum:</td>
    <td valign="top">';
		if ($result_row["zeitraum"] == "" || $result_row["zeitraum"]  == "NULL") {
			if ($result_row["zeitraum_ab_m"] == 0 || $result_row["zeitraum_ab_y"] == 0) {
				$rueckgabe .= 'keine genauen Angaben hinterlegt';
			} else {
				$rueckgabe .= 'ab ' . $result_row["zeitraum_ab_m"] . '/' . $result_row["zeitraum_ab_y"];

			}
		} else {
			if($result_row["zeitraum_ab_m"] == 0 || $result_row["zeitraum_ab_y"] == 0) {
				$rueckgabe .= $result_row["zeitraum"];
			} else {
				$rueckgabe .= 'ab ' . $result_row["zeitraum_ab_m"] . '/' . $result_row["zeitraum_ab_y"] . '<br>' . $result_row["zeitraum"];
			}
		}
		$rueckgabe .= '</td>
  </tr>
  <tr>
    <td colspan="2" valign="top"><a href="#" onclick="window.open('."'../detail.phtml?id=" . $result_row["id"] . "&spalte=1&miniwindow=yes&noadvertise=yes','messageWindow','scrollbars=yes,statusbar=no,toolbar=no,width=720,height=600'".'); return false;">Gesuch ansehen</a></td>
  </tr>
  <tr>
    <td colspan="2"><hr></td>
  </tr>';
	}
}


// Diplomthemen ermitteln //


// 2 aktive Diplomthemen ermitteln
$results = mysql($database_db,"SELECT titel,zeitraum,studiengang,id,zeitraum_ab_m,zeitraum_ab_y FROM diplomgesuch WHERE inactive='false' AND nutzerid ='$nutzerid' ORDER BY ID desc",$praktdbslave);

// datensaetze gefunden ? //
$num_rows = ($results != false) ? mysql_num_rows( $results ) : 0;

$rueckgabe .= '
  <tr>
    <td colspan="2"><br></td>
  </tr>
  <tr valign="baseline">
    <td colspan="2" bgcolor="'.$spalte2quicklogindunkel.'"><b>Diplomthemen</b></td>
  </tr>';
if ( $num_rows  > 0) {
	// Vorhandene Diplomthemen anzeigen
	$i = 1;
	while($result_row = mysql_fetch_array($results)) {
		$rueckgabe .= '
  <tr>
    <td colspan="2" valign="top">' . $i++ . '. Diplomthema:</td>
  </tr>
  <tr>
    <td valign="top">Titel der Diplomarbeit:</td>
    <td valign="top">' . $result_row["titel"] . '</td>
  </tr>
  <tr>
    <td valign="top">Zeitraum:</td>
    <td valign="top">';
		if ($result_row["zeitraum"] == "" || $result_row["zeitraum"]  == "NULL") {
			if ($result_row["zeitraum_ab_m"] == 0 || $result_row["zeitraum_ab_y"] == 0) {
				$rueckgabe .= 'keine genauen Angaben hinterlegt';
			} else {
				$rueckgabe .= 'ab ' . $result_row["zeitraum_ab_m"] . '/' . $result_row["zeitraum_ab_y"];

			}
		} else {
			if($result_row["zeitraum_ab_m"] == 0 || $result_row["zeitraum_ab_y"] == 0) {
				$rueckgabe .= $result_row["zeitraum"];
			} else {
				$rueckgabe .= 'ab ' . $result_row["zeitraum_ab_m"] . '/' . $result_row["zeitraum_ab_y"] . '<br>' . $result_row["zeitraum"];
			}
		}
		$rueckgabe .= '</td>
  </tr>';
		$studiengangid = $result_row["studiengang"];
		$studiengang = mysql($database_db,"SELECT studiengang FROM studiengaenge WHERE id=$studiengangid",$praktdbslave);
		if (mysql_numrows($studiengang) > 0) {
			$rueckgabe .= '
  <tr>
    <td valign="top">Studiengang:</td>
    <td valign="top">' . mysql_result($studiengang,0,"studiengang") . '</td>
  </tr>';
		}
		$rueckgabe .= '
  <tr>
    <td colspan="2" valign="top"><a href="#" onclick="window.open('."'../detail.phtml?id=" . $result_row["id"] . "&spalte=2&miniwindow=yes&noadvertise=yes','messageWindow','scrollbars=yes,statusbar=no,toolbar=no,width=720,height=600'".'); return false;">Gesuch ansehen</a></td>
  </tr>
  <tr>
    <td colspan="2"><hr></td>
  </tr>';
	}
}


// Berufseinstiegsstellen ermitteln

// 2 aktive Berufseinstiegsstellen ermitteln
$results = mysql($database_db,"SELECT berufsfeld,bezeichnung as taetigkeit,id,zeitraum_ab_m,zeitraum_ab_y FROM berufseinstieggesuch WHERE inactive='false' AND nutzerid ='$nutzerid' ORDER BY id DESC",$praktdbslave);

// datensaetze gefunden ? //
$num_rows = ($results != false) ? mysql_num_rows( $results ) : 0;

$rueckgabe .= '
  <tr>
    <td colspan="2"><br></td>
  </tr>
  <tr valign="baseline">
    <td colspan="2" bgcolor="'.$spalte2quicklogindunkel.'"><b>Berufseinstiegsgesuche</b></td>
  </tr>';
if ( $num_rows  > 0) {

	$i = 1;
	while ($result_row = mysql_fetch_array($results )) {
		$rueckgabe .= '
  <tr>
    <td colspan="2" valign="top">' . $i++ . '. Berufseinstiegsstelle</td>
  </tr>
  <tr>
    <td valign="top">T&auml;tigkeit:</td>
    <td valign="top">' . $result_row["taetigkeit"] . '</td>
  </tr>
  <tr>
    <td valign="top">Zeitraum:</td>
    <td valign="top">ab ' . $result_row["zeitraum_ab_m"] . '/' . $result_row["zeitraum_ab_y"] . '</td>
  </tr>';

		if ($result_row["berufsfeld"] != "" && $result_row["berufsfeld"] != 0) {
			$berufsfeldid=$result_row["berufsfeld"];
			$berufsfeld=mysql($database_db,"SELECT $s_sprache FROM berufsfelder WHERE id=$berufsfeldid",$praktdbslave);
			if (mysql_numrows($berufsfeld) > 0) {
				$rueckgabe .= '
  <tr>
    <td valign="top">Berufsfeld:</td>
    <td valign="top">' . mysql_result($berufsfeld,0,$s_sprache) . '</td>
  </tr>';
			}
		}
		$rueckgabe .= '
  <tr>
    <td colspan="2" valign="top"><a href="#" onclick="window.open('."'../detail.phtml?id=" . $result_row["id"] . "&spalte=5&miniwindow=yes&noadvertise=yes','messageWindow','scrollbars=yes,statusbar=no,toolbar=no,width=720,height=600'".'); return false;">Gesuch ansehen</a></td>
  </tr>
  <tr>
    <td colspan="2"><hr></td>
  </tr>';

	}
}

// Nebenjobs ermitteln //

// 2 aktive Nebenjobs ermitteln
$results = mysql($database_db,"SELECT berufsfeld,taetigkeit,zeitraum,id,zeitraum_ab_m,zeitraum_ab_y FROM nebenjobgesuch WHERE inactive='false' AND nutzerid ='$nutzerid' ORDER BY id DESC",$praktdbslave);

// datensaetze gefunden ? //
$num_rows = ($results != false) ? mysql_num_rows( $results ) : 0;

$rueckgabe .= '
  <tr>
    <td colspan="2"><br></td>
  </tr>
  <tr valign="baseline">
    <td colspan="2" bgcolor="'.$spalte2quicklogindunkel.'"><b>Nebenjobgesuche</b></td>
  </tr>';
if ( $num_rows  > 0) {

	$i = 1;
	while ($result_row = mysql_fetch_array ($results)) {
		$rueckgabe .= '
  <tr>
    <td colspan="2" valign="top">' . $i++ . '. Nebenjobgesuch</td>
  </tr>
  <tr>
    <td valign="top">T&auml;tigkeit:</td>
    <td valign="top">' . $result_row["taetigkeit"] . '</td>
  </tr>
  <tr>
    <td valign="top">Zeitraum:</td>
    <td valign="top">';
		if ($result_row["zeitraum"] == "" || $result_row["zeitraum"]  == "NULL") {
			if ($result_row["zeitraum_ab_m"] == 0 || $result_row["zeitraum_ab_y"] == 0) {
				$rueckgabe .= 'keine genauen Angaben hinterlegt';
			} else {
				$rueckgabe .= 'ab ' . $result_row["zeitraum_ab_m"] . '/' . $result_row["zeitraum_ab_y"];

			}
		} else {
			if($result_row["zeitraum_ab_m"] == 0 || $result_row["zeitraum_ab_y"] == 0) {
				$rueckgabe .= $result_row["zeitraum"];
			} else {
				$rueckgabe .= 'ab ' . $result_row["zeitraum_ab_m"] . '/' . $result_row["zeitraum_ab_y"] . '<br>' . $result_row["zeitraum"];
			}
		}
		$rueckgabe .= '</td>
  </tr>';
		$berufsfeldid=$result_row["berufsfeld"];
		$berufsfeld=mysql($database_db,"SELECT $s_sprache FROM berufsfelder WHERE id=$berufsfeldid",$praktdbslave);
		if (mysql_numrows($berufsfeld) > 0) {
			$rueckgabe .= '
  <tr>
    <td valign="top">Berufsfeld:</td>
    <td valign="top">' . mysql_result($berufsfeld,0,$s_sprache) . '</td>
  </tr>';
		}
		$rueckgabe .= '
  <tr>
    <td colspan="2" valign="top"><a href="#" onclick="window.open('."'../detail.phtml?id=" . $result_row["id"] . "&spalte=3&miniwindow=yes&noadvertise=yes','messageWindow','scrollbars=yes,statusbar=no,toolbar=no,width=720,height=600'".'); return false;">Gesuch ansehen</a></td>
  </tr>
  <tr>
    <td colspan="2"><hr></td>
  </tr>';
	}
}

// Ausbildungsplätze ermitteln //

// 2 aktive Ausbildungsplätze ermitteln
$results = mysql($database_db,"SELECT lehre,taetigkeit,id,zeitraum_ab_m,zeitraum_ab_y,zeitraum FROM ausbildungsgesuch WHERE inactive='false' AND nutzerid ='$nutzerid' ORDER BY id DESC",$praktdbslave);

// datensaetze gefunden ? //
$num_rows = ($results != false) ? mysql_num_rows( $results ) : 0;

$rueckgabe .= '
  <tr>
    <td colspan="2"><br></td>
  </tr>
  <tr valign="baseline">
    <td colspan="2" bgcolor="'.$spalte2quicklogindunkel.'"><b>Ausbildungplatzgesuche</b></td>
  </tr>';
if ( $num_rows  > 0) {
	$i=1;
	while ($result_row = mysql_fetch_array ( $results )) {
		$rueckgabe .= '
  <tr>
    <td colspan="2" valign="top">' . $i++ . '. Ausbildungplatzgesuch</td>
  </tr>';
		$lehreid=$result_row["lehre"];
		// Nutzerprüfung ob Mann oder Frau wegen Anzeige des richtigen Berufsgeschlechtes //
		if ($s_loggedin=="yes" || $s_loggedinnutzer == true) {
			$resultn = mysql($database_db,"SELECT anrede FROM nutzer WHERE id = '$nutzerid'",$praktdbslave);
			// array erzeugen //
			$eintragn = mysql_fetch_array ( $resultn );
			if ($eintragn["anrede"] == "Herr") {
				$lehre=mysql($database_db,"SELECT berufswahl FROM berufswahl_mann WHERE id=$lehreid",$praktdbslave);
			} else {
				$lehre=mysql($database_db,"SELECT berufswahl FROM berufswahl_frau WHERE id=$lehreid",$praktdbslave);
			}
			// wenn noch nicht eingeloggt //
		} else {
			$lehre=mysql($database_db,"SELECT berufswahl FROM berufswahl_mann WHERE id=$lehreid",$praktdbslave);
		}
		$rueckgabe .= '
  <tr valign="baseline">
    <td>Ausbildung zum:</b></td>
    <td>' . mysql_result($lehre,0,"berufswahl") . '</td>
  </tr>
  <tr>
    <td valign="top">T&auml;tigkeit:</td>
    <td valign="top">' . $result_row["taetigkeit"] . '</td>
  </tr>
  <tr>
    <td valign="top">Zeitraum:</td>
    <td valign="top">';
		if ($result_row["zeitraum"] == "" || $result_row["zeitraum"]  == "NULL") {
			if ($result_row["zeitraum_ab_m"] == 0 || $result_row["zeitraum_ab_y"] == 0) {
				$rueckgabe .= 'keine genauen Angaben hinterlegt';
			} else {
				$rueckgabe .= 'ab ' . $result_row["zeitraum_ab_m"] . '/' . $result_row["zeitraum_ab_y"];

			}
		} else {
			if($result_row["zeitraum_ab_m"] == 0 || $result_row["zeitraum_ab_y"] == 0) {
				$rueckgabe .= $result_row["zeitraum"];
			} else {
				$rueckgabe .= 'ab ' . $result_row["zeitraum_ab_m"] . '/' . $result_row["zeitraum_ab_y"] . '<br>' . $result_row["zeitraum"];
			}
		}
		$rueckgabe .= '</td>
  </tr>
  <tr>
    <td colspan="2" valign="top"><a href="#" onclick="window.open('."'../detail.phtml?id=" . $result_row["id"] . "&spalte=4&miniwindow=yes&noadvertise=yes','messageWindow','scrollbars=yes,statusbar=no,toolbar=no,width=720,height=600'".'); return false;">Gesuch ansehen</a></td>
  </tr>
  <tr>
    <td colspan="2"><hr></td>
  </tr>';
	}
}

$rueckgabe .= '
  <tr>
    <td colspan="2"><br></td>
  </tr>
</table>
<p></p>
<form>
  <fieldset class="control_panel">
    <p>
      <input type="button" value="zurück zur Ergebnisliste" onclick="Load_SearchData('."'".$ajaxtype."', document.getElementById('sortierung').value, document.getElementById('ksuchstring').value, document.getElementById('categoryids').value, '".$_REQUEST["ds_count"]."'".');" />
    </p>
  </fieldset>
</form>';


/* Anzeige der Bewerbungsmappe
//if ($s_loggedin=="yes" && ($s_firmenlevel>1 || $num_rows == 1 || $gesuchedbzahl > 0)) {
if ($s_loggedin=="yes" && $userid) {
$modifydatum=$eintrag["modifydatum"];
bewerbermappeeinzelansicht($userid,$modifydatum,$num_rows);
}
*/



$submitarray = Array();
$submitarray["mainvalue"] = $rueckgabe;


echo '<?xml version="1.0" encoding="ISO-8859-1"?>
<root>
<htmlinhalte>'."\n";

foreach ($submitarray as $arraykey => $arrayelement) {
	if (!empty($arrayelement)) {
		$arrayelement = str_split($arrayelement,4096);
		$i=0;
		foreach ($arrayelement as $arrayvalue) {
			echo "<".$arraykey.$i.">".htmlspecialchars($arrayvalue)."</".$arraykey.$i.">\n";
			$i++;
		}
	}
}
echo "<wkcount>".count($s_firmenproparray["kandidatensuche"]["warenkorb"])."</wkcount>\n";
echo "</htmlinhalte>\n";
echo "</root>";

?>
