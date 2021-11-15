<?php
//  praktika.de ajax File - KANDIDATENAUSGABE -  WENN FIRMEN SUCHEN

header("Content-Type: text/xml");

require("/home/praktika/etc/dbzugang.inc.php");
require("/home/praktika/etc/lib_reworked.inc.php");

if ($suchstring == "warenkorbanzeige") {
	$ajaxtype = "warenkorbanzeige";
} else {
	$ajaxtype = "results";
}

//if (!$spalte2quicklogindunkel) $spalte2quicklogindunkel = "#E8C9BA";

// Start *************************************
srand ( (double)microtime() * 1000000 );
$num = rand(0, ( count($dbarray) - 1 ) );

$praktdbmaster = @mysql_pconnect($dbarray[0]["host"], $dbarray[0]["mysqluser"], $dbarray[0]["mysqlpassword"]);
$praktdbslave = @mysql_pconnect($dbarray[$num]["host"], $dbarray[$num]["mysqluser"], $dbarray[$num]["mysqlpassword"]);

session_name(praktika_id);
session_save_path("/home/praktika/sessions");
session_start();

// Session-Variablen
$s_firmenlevel = isset($_SESSION["s_firmenlevel"]) ? $_SESSION["s_firmenlevel"] : 0;
$s_loggedin = isset($_SESSION["s_loggedin"]) ? $_SESSION["s_loggedin"] : false;
$s_sprache = isset($_SESSION["s_sprache"]) ? $_SESSION["s_sprache"] : "german";

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
$nutzerid = $eintrag["nutzerid"];
$sql = sprintf("INSERT INTO 
					viewstatspraktikanten (nutzerid, userid, spalte, modify) 
				VALUES 
					('%1\$d', '%2\$d', '%3\$s', '%4\$s')",
				$nutzerid,
				$s_loginid,
				$s_spalte,
				$aktdatum);
mysql($database_db, $sql, $praktdbmaster);

// alter berechnen
$now = time();
$birthday = strtotime($eintrag["geburtsdatum"]);
$age = ($now - $birthday) / 31560000; // 365 Tage

// Firmenbookmark Beginn
if ($nutzerid) {
	$sql = sprintf("SELECT 
						id 
					FROM 
						firmenbookmark 
					WHERE 
						firmenid = %1\$d AND 
						nutzerid = %2\$d",
					$s_firmenid,
					$nutzerid);
	mysql($database_db, $sql, $praktdbslave);
	#$check = "SELECT id FROM firmenbookmark WHERE firmenid='$s_firmenid' AND nutzerid=$nutzerid";
	$result = mysql($database_db, $sql, $praktdbslave);
	$num_rows = mysql_num_rows( $result );
}
// Firmenbookmark Ende

$rueckgabe .= '<script type="text/javascript" language="JavaScript">
    <!--
    function load() {
        Load_SearchData('."'detail', '', 'results', '".$_REQUEST["nutzerid"]."', '".$_REQUEST["ds_count"]."'".');
    }
    //-->
    </script>
<table border="0" width="100%">';

// auf Merkzettel setzen
if ($s_firmenproparray["kandidatensuche"]["warenkorb"][$nutzerid]) $rueckgabe .= '
  <tr valign="baseline">
    <td><a href="javascript:Load_SearchData('."'".$ajaxtype."', document.getElementById('sortierung').value, document.getElementById('ksuchstring').value, document.getElementById('categoryids').value, '".$_REQUEST["ds_count"]."'".');">zur&uuml;ck zur Ergebnisliste</a></td>
    <td align="right">Der Kandidat wurde ausw&auml;hlt!<br></td>
  </tr>
</table>';


// Beginn Firmenbookmark Anzeige
if (!$s_firmenproparray["kandidatensuche"]["warenkorb"][$nutzerid]) {
	$rueckgabe .= '
  <tr valign="baseline">
    <td><a href="javascript:Load_SearchData('."'".$ajaxtype."', document.getElementById('sortierung').value, document.getElementById('ksuchstring').value, document.getElementById('categoryids').value, '".$_REQUEST["ds_count"]."'".');">zur&uuml;ck zur Ergebnisliste</a></td>
    <td align="right"><a href="javascript:WarenkorbSubmit('."'".$nutzerid."','detail','".$_REQUEST["ds_count"]."'".');">Kandidat ausw&auml;hlen</a></td>
  </tr>
</table>';
}
// Ende Firmenbookmark Anzeige

$sql = sprintf("SELECT 
					lebenslauftemplate 
				FROM 
					einstellungen 
				WHERE 
					nutzerid = %1\$d",
				$id);
$select = mysql_db_query($database_bprofil, $sql, $praktdbslave);

if (mysql_num_rows($select) > 0) {
	$lebenlaufwahl = mysql_result($select, 0, "lebenslauftemplate");
}
switch ($lebenlaufwahl) {
	case "":
	case "10":
	case "20":
		$lebenlaufwahl="01";
	case "01":
	case "02":
	case "03":
		$bewerbsprache = 2;
		break;
	default:
		$bewerbsprache = 1;
		break;
}

// Wer darf was sehen
if ($num_rows == 0) $einsicht = 0;
if ($num_rows > 0 || $s_firmenlevel == 2) $einsicht = 1;

$login = '\'Login\', \'/home/quicklogin/minilogin.phtml\', 250, 380';

if ($s_loggedin == true) {
	$caption = "Bewerbungsmappe";
	$url = "/home/praktikanten/profilausgabe/einzelansicht.phtml?id=" . $nutzerid . "&bewerbsprache=2&einstellungen=true&gesuche=no";
	$params = "600, 720";
	// TODO - testing
	$link = '<a href="/greybox.phtml?url=/home/praktikanten/profil/previewlebenslauf'.$lebenlaufwahl.'.phtml&amp;menu=mappe&amp;mappe=1&amp;b='.$id.'" onclick="window.scrollTo(0,0);" rel="pb_page_fs[]">';
	//$link = '<a href="#" onclick="windowanm=GB_showCenter(\''.$caption.'\', \''.$url.'\', '.$params.')">';
} else {
	$caption = "Login";
	$url = "/home/quicklogin/minilogin.phtml";
	$params = "300, 400";
	// TODO - testing test
	$link = '<a href="#" onclick="windowanm=GB_showCenter(\''.$caption.'\', \''.$url.'\', '.$params.')">';
}

// Bild heraussuchen
$sql = sprintf("SELECT 
					id 
				FROM 
					bewerbungsfoto 
				WHERE 
					nutzerid = '%1\$d'",
				$nutzerid);
$result = mysql_db_query($database_db, $sql, $praktdbslave);
$photo = (mysql_num_rows($result) > 0) ? '<img src="/community/passbild.php?id='.mysql_result($result, 0, "id").'" width="120" alt="Bewerbungsfoto" align="right">' : "";

$rueckgabe .= '
<h4>Bewerber</h4>
<table>
  <colgroup>
    <col width="30%" />
    <col width="40%" />
    <col width="30%" />
  </colgroup>
  <tr valign="baseline">
    <!--<td align="right" class="normal" colspan="3"><a href="javascript:Load_SearchData('."'detail', '', 'results', '".$_REQUEST["nutzerid"]."', '".$_REQUEST["ds_count"]."'".')" onclick="windowanm=GB_showCenter(\''.$caption.'\', \''.$url.'\', '.$params.')">Bewerbungsmappe ansehen</a></td>-->
    <td align="right" class="normal" colspan="3">'.$link.'Bewerbungsmappe ansehen</a></td>
    <!--<td class="normal" align="right"><a href="#" onclick="windowanm=window.open('."'".$link."', 'messageWindow', 'scrollbars=yes, statusbar=no, toolbar=no, ".$params."'".'); return false;">Bewerbungsmappe ansehen</a></td>-->
  </tr>
  <tr valign="baseline">
    <td>Name:</td>
    <td>';
if ( $eintrag["geschlecht"] == "weiblich" ) {
	$rueckgabe .= $language["strFrau"]." ";
}
if ( $eintrag["geschlecht"] == "männlich" ) {
	$rueckgabe .= $language["strHerr"]." ";
}
$rueckgabe .= ($eintrag["geschlecht"] == "männlich") ? $language["strHerr"]." " : $language["strFrau"]." ";
if ( $eintrag["titel"] != "" ) {
	$rueckgabe .= $eintrag["titel"] . " \n";
}
$rueckgabe .= '<b>' . ucfirst($eintrag["vname"]) . ' ';
if($s_loggedin != true) {
	$rueckgabe .= substr(ucfirst($eintrag["name"]), 0, 1).".";
} else {
	$rueckgabe .= ucfirst($eintrag["name"]);
}
// photo
$rueckgabe .= '</b></td>
    <td rowspan="7">'.$photo.'</td>
  </tr>';

// adress
if ($s_loggedin == true && $einsicht == 1) {
	$rueckgabe .= '
  <tr valign="baseline">
    <td>Anschrift:</td>
    <td>' . $eintrag["strasse"] . '</td>
  </tr>
  <tr valign="baseline">
    <td></td>
    <td>' . $eintrag["plz"] . ' ' . $eintrag["ort"] . '</td>
  </tr>';
} else {
	$rueckgabe .= '
  <tr valign="baseline">
    <td>Anschrift:</td>
    <td class="normal"><a href="#" onclick="var windowanm=GB_showCenter('.$login.'); //Load_SearchData('."'detail', '', 'results', '".$_REQUEST["nutzerid"]."', '".$_REQUEST["ds_count"]."'".')">Anzeige nach Login</a></td>
  </tr>';
}


if ( $einsicht == 1 && ($eintrag["grossraum"] <> 94 || $eintrag["grossraum"] > 0) ) {
	$such = $eintrag["grossraum"];
	$suchres = @mysql($database_db, "SELECT grossraum FROM grossraum WHERE id = '$such'", $praktdbslave);
		$rueckgabe .= '
  <tr valign="baseline">
    <td>Gro&szlig;raum</td>
    <td>' . mysql_result($suchres, 0, "grossraum") . '</td>
  </tr>';
}

if ($eintrag["bundesland"] != 0) {
	$bundeslandid = $eintrag["bundesland"];
	$bland = mysql($database_db,"SELECT $s_sprache FROM bundesland WHERE id = $bundeslandid", $praktdbslave);
	$rueckgabe .= '
  <tr valign="baseline">
    <td>Bundesland:</td>
    <td>' . mysql_result($bland, 0, $s_sprache) . '</td>
  </tr>';
}

// country
$landid = $eintrag["land"];
if ($landid > 0) {
	$land = mysql($database_db, "SELECT $s_sprache FROM land WHERE id = $landid", $praktdbslave);
	$rueckgabe .= '
  <tr valign="baseline">
    <td>Land:</td>
    <td>' . mysql_result($land, 0, $s_sprache) . '</td>
  </tr>';
}

// age
$rueckgabe .=  '
  <tr valign="baseline">
    <td>Alter:</td>
    <td>' . sprintf("%01.0u", $age) . ' ' . $language["strJahre"] . '</td>
  </tr>';

// phone
if ($s_loggedin == true && $einsicht == 1 && $eintrag["tel"] != "") {
			$rueckgabe .= '
  <tr valign="baseline">
    <td>Telefon:</td>
    <td>' . $eintrag["tel"] . '</td>
  </tr>';
} else {
	$rueckgabe .=  '
  <tr valign="baseline">
    <td>Telefon:</td>
    <td class=normal><a href="#" onclick="windowanm=GB_showCenter('.$login.'); return false;">Anzeige nach Login</a></td>
  </tr>'; 
}

// fax
if ($s_loggedin == true && $einsicht == 1 && $eintrag["fax"] != "") {
			$rueckgabe .= '
  <tr valign="baseline">
    <td>Telefax:</td>
    <td>' . $eintrag["fax"] . '</td>
  </tr>';
} else {
	$rueckgabe .= '
  <tr valign="baseline">
    <td>Telefax:</td>
    <td class=normal><a href="#" onclick="windowanm=GB_showCenter('.$login.'); return false;">Anzeige nach Login</a></td>
  </tr>';
}

// cellular phone
if ($s_loggedin == true && $einsicht == 1 && $eintrag["funktel"] != "") {
			$rueckgabe .= '
  <tr valign="baseline">
    <td>Funktelefon:</td>
    <td>' . $eintrag["funktel"] . '</td>
  </tr>';
} else {
	$rueckgabe .=  '
  <tr valign="baseline">
    <td>Funktelefon:</td>
    <td class=normal><a href="#" onclick="windowanm=GB_showCenter('.$login.'); return false;">Anzeige nach Login</a></td>
  </tr';
}

// email
if ($s_loggedin == true && $einsicht == 1) {
		$rueckgabe .= '
  <tr valign="baseline">
    <td>E-Mail:</td>
    <td><a href="../email/?prakt=yes&id='.$id.'">E-Mail verfassen</a></td>
  </tr>
  <tr valign="baseline">
    <td>Kurznachricht:</td>
    <td class="normal"><a href="#" onclick="windowanm=window.open('."'/home/firmen/firstkontakt_nutzer.phtml?gesuchid=".$eintrag["pid"]."&nutzerid=".$eintrag["nutzerid"]."&spaltenid=".$s_spalte."&firmenid=".$s_firmenid."','messageWindow','scrollbars=yes,statusbar=no,toolbar=no,width=450,height=500'".'); return false;">'.$language["strEmailKN"].'</a></td>
  </tr>';
} else {
	$rueckgabe .= '
  <tr valign="baseline">
    <td>E-Mail:</td>
    <td class=normal><a href="#" onclick="windowanm=GB_showCenter('.$login.'); return false;">Anzeige nach Login</a></td>
  </tr>
  <tr valign="baseline">
    <td>Kurznachricht:</td>
    <td class=normal><a href="#" onclick="windowanm=GB_showCenter('.$login.'); return false;">Anzeige nach Login</a></td>
  </tr>';
}

if ($einsicht == 1 && $eintrag["homepage"] != "") {
		$rueckgabe .= '
  <tr valign="baseline">
    <td>Homepage:</td>
    <td><a href="' . $eintrag["homepage"] . '" target="_blank">' . $eintrag["homepage"] . '</a></td>
  </tr>';
}
$rueckgabe .= '
  <tr valign="baseline">
    <td>&nbsp;</td>
    <td></td>
  </tr>
</table>';

// Qualifikation
$rueckgabe .= '
<h4>Qualifikation</h4>';
if ( $eintrag["studiengang"] != "" && $eintrag["studiengang"] != "0") {
	$ins = $eintrag["studiengang"];
	$suchres = mysql($database_db,"SELECT studiengang FROM studiengaenge WHERE id='$ins'",$praktdbslave);
	$rueckgabe .= '
<table>
  <colgroup>
    <col width="30%" />
    <col width="70%" />
  </colgroup>
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
  </tr>
  <tr>
    <td colspan="2"><br/></td>
  </tr>
</table>';
	}
}


// Praktikumsstellen ermitteln

// 2 aktive Praktikumsstellen ermitteln
$sql = sprintf("SELECT 
					id, 
					taetigkeit, 
					zeitraum, 
					zeitraum_ab_m, 
					zeitraum_ab_y 
				FROM 
					praktikanten 
				WHERE 
					inactive = 'false' AND 
					nutzerid = '%1\$d' 
				ORDER BY 
					id", 
				$nutzerid);
$results = mysql_db_query($database_db, $sql, $praktdbslave);

// datensaetze gefunden ? //
$num_rows = ($results != false) ? mysql_num_rows( $results ) : 0;

if ( $num_rows  > 0) {

	// Vorhandene Praktikumstellen anzeigen
	$rueckgabe .= '
<h4>Praktikumsgesuche</h4>
<table>
  <colgroup>
    <col width="30%" />
    <col width="70%" />
  </colgroup>';
	$i = 1;
	while( $result_row = mysql_fetch_array($results) ) {
		if ($i > 1)
			$rueckgabe .= '
  <tr>
    <td colspan="2"><hr/></td>
  </tr>';
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
		$caption = "Kandidatengesuch: Praktikum";
		$link = "/home/firmen/detail.phtml?id=".$result_row["id"]."&amp;nutzerid=".$eintrag["nutzerid"]."&amp;spalte=1";
		$rueckgabe .= '</td>
  </tr>
  <tr>
    <td colspan="2" valign="top"><a href="#" onclick="GB_showCenter(\''.$caption.'\', \''.$link.'\', 600, 720)">Gesuch ansehen</a></td>
  </tr>';
	}
	$rueckgabe .= '
  <tr>
    <td colspan="2"><br/></td>
  </tr>
</table>';
}

// Diplomthemen ermitteln //
// 2 aktive Diplomthemen ermitteln
$sql = sprintf("SELECT 
					id, 
					titel, 
					zeitraum, 
					studiengang, 
					zeitraum_ab_m, 
					zeitraum_ab_y 
				FROM 
					diplomgesuch 
				WHERE 
					inactive='false' AND 
					nutzerid ='%1\$d' 
				ORDER BY 
					id desc", 
				$nutzerid);
$results = mysql_db_query($database_db, $sql, $praktdbslave);

// datensaetze gefunden ? //
$num_rows = ($results != false) ? mysql_num_rows( $results ) : 0;

if ( $num_rows  > 0 ) {
	// Vorhandene Diplomthemen anzeigen
	$rueckgabe .= '
<h4>Diplomthemen</h4>
<table>
  <colgroup>
    <col width="30%" />
    <col width="70%" />
  </colgroup>';
	$i = 1;
	while( $result_row = mysql_fetch_array($results) ) {
		if ($i > 1)
			$rueckgabe .= '
  <tr>
    <td colspan="2"><hr/></td>
  </tr>';
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
				$rueckgabe .= 'ab ' . $result_row["zeitraum_ab_m"] . '/' . $result_row["zeitraum_ab_y"] . '<br/>' . $result_row["zeitraum"];
			}
		}
		$rueckgabe .= '</td>
  </tr>';
		if (isset($result_row["studiengang"])) {
			//$studiengangid = $result_row["studiengang"];
			$sql = sprintf("SELECT 
								studiengang 
							FROM 
								studiengaenge 
							WHERE 
								id = '%1\$d'",
								$result_row["studiengang"]);
			$studiengang = mysql_db_query($database_db, $sql, $praktdbslave);
			if (mysql_num_rows($studiengang) > 0) {
				$rueckgabe .= '
  <tr>
    <td valign="top">Studiengang:</td>
    <td valign="top">' . mysql_result($studiengang, 0, "studiengang") . '</td>
  </tr>';
			}
		}
		$caption = "Kandidatengesuch: Diplom";
		$link = "/home/firmen/detail.phtml?id=".$result_row["id"]."&amp;nutzerid=".$eintrag["nutzerid"]."&amp;spalte=2";
		$rueckgabe .= '
  <tr>
    <td colspan="2" valign="top"><a href="#" onclick="GB_showCenter(\''.$caption.'\', \''.$link.'\', 600, 720)">Gesuch ansehen</a></td>
  </tr>';
	}
	$rueckgabe .= '
  <tr>
    <td colspan="2"><br/></td>
  </tr>
</table>';
}


// Berufseinstiegsstellen ermitteln

// 2 aktive Berufseinstiegsstellen ermitteln
$results = mysql($database_db,"SELECT berufsfeld,bezeichnung as taetigkeit,id,zeitraum_ab_m,zeitraum_ab_y FROM berufseinstieggesuch WHERE inactive='false' AND nutzerid ='$nutzerid' ORDER BY id DESC",$praktdbslave);

// datensaetze gefunden ? //
$num_rows = ($results != false) ? mysql_num_rows( $results ) : 0;

if ( $num_rows  > 0) {

	$rueckgabe .= '
<h4>Berufseinstiegsgesuche</h4>
<table>
  <colgroup>
    <col width="30%" />
    <col width="70%" />
  </colgroup>';
	$i = 1;
	while ( $result_row = mysql_fetch_array($results ) ) {
		if ($i > 1)
			$rueckgabe .= '
  <tr>
    <td colspan="2"><hr/></td>
  </tr>';
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
		$caption = "Kandidatengesuch: Berufseinstieg";
		$link = "/home/firmen/detail.phtml?id=".$result_row["id"]."&amp;nutzerid=".$eintrag["nutzerid"]."&amp;spalte=5";
		$rueckgabe .= '
  <tr>
    <td colspan="2" valign="top"><a href="#" onclick="GB_showCenter(\''.$caption.'\', \''.$link.'\', 600, 720)">Gesuch ansehen</a></td>
  </tr>';
	}
	$rueckgabe .= '
  <tr>
    <td colspan="2"><br/></td>
  </tr>
</table>';
}

// Nebenjobs ermitteln //

// 2 aktive Nebenjobs ermitteln
$results = mysql($database_db,"SELECT berufsfeld,taetigkeit,zeitraum,id,zeitraum_ab_m,zeitraum_ab_y FROM nebenjobgesuch WHERE inactive='false' AND nutzerid ='$nutzerid' ORDER BY id DESC",$praktdbslave);

// datensaetze gefunden ? //
$num_rows = ($results != false) ? mysql_num_rows( $results ) : 0;

if ( $num_rows  > 0) {

	$rueckgabe .= '
<h4>Nebenjobgesuche</h4>
<table>
  <colgroup>
    <col width="30%" />
    <col width="70%" />
  </colgroup>';
	$i = 1;
	while ( $result_row = mysql_fetch_array ($results) ) {
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
		$caption = "Kandidatengesuch: Nebenjob";
		$link = "/home/firmen/detail.phtml?id=".$result_row["id"]."&amp;nutzerid=".$eintrag["nutzerid"]."&amp;spalte=3";
		$rueckgabe .= '
  <tr>
    <td colspan="2" valign="top"><a href="#" onclick="GB_showCenter(\''.$caption.'\', \''.$link.'\', 600, 720)">Gesuch ansehen</a></td>
  </tr>';
	}
	$rueckgabe .= '
  <tr>
    <td colspan="2"><br/></td>
  </tr>
</table>';
}

// Ausbildungsplätze ermitteln //

// 2 aktive Ausbildungsplätze ermitteln
$results = mysql($database_db,"SELECT lehre,taetigkeit,id,zeitraum_ab_m,zeitraum_ab_y,zeitraum FROM ausbildungsgesuch WHERE inactive='false' AND nutzerid ='$nutzerid' ORDER BY id DESC",$praktdbslave);

// datensaetze gefunden ? //
$num_rows = ($results != false) ? mysql_num_rows( $results ) : 0;


if ( $num_rows  > 0) {
	$rueckgabe .= '
<h4>Ausbildungplatzgesuche</h4>
<table>
  <colgroup>
    <col width="30%" />
    <col width="70%" />
  </colgroup>';
	$i = 1;
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
				$lehre=mysql($database_db,"SELECT berufswahl FROM berufswahl_mann WHERE id = $lehreid",$praktdbslave);
			} else {
				$lehre=mysql($database_db,"SELECT berufswahl FROM berufswahl_frau WHERE id = $lehreid",$praktdbslave);
			}
			// wenn noch nicht eingeloggt //
		} else {
			$lehre=mysql($database_db,"SELECT berufswahl FROM berufswahl_mann WHERE id = $lehreid",$praktdbslave);
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
		$caption = "Kandidatengesuch: Ausbildungplatz";
		$link = "/home/firmen/detail.phtml?id=".$result_row["id"]."&amp;nutzerid=".$eintrag["nutzerid"]."&amp;spalte=4";
		$rueckgabe .= '</td>
  </tr>
  <tr>
    <td colspan="2" valign="top"><a href="#" onclick="GB_showCenter(\''.$caption.'\', \''.$link.'\', 600, 720)">Gesuch ansehen</a></td>
  </tr>
  <tr>
    <td colspan="2"><hr/></td>
  </tr>';
	}
	$rueckgabe .= '
  <tr>
    <td colspan="2"><br/></td>
  </tr>
</table>';
}

$rueckgabe .= '
<p></p>
<form>
  <fieldset class="control_panel">
    <p>
      <input type="button" value="zur&uuml;ck zur Ergebnisliste" onclick="Load_SearchData('."'".$ajaxtype."', document.getElementById('sortierung').value, document.getElementById('ksuchstring').value, document.getElementById('categoryids').value, '".$_REQUEST["ds_count"]."'".');" />
    </p>
  </fieldset>
</form>';


/* Anzeige der Bewerbungsmappe
//if ($s_loggedin=="yes" && ($s_firmenlevel>1 || $num_rows == 1 || $gesuchedbzahl > 0)) {
if ($s_loggedin=="yes" && $nutzerid) {
$modifydatum=$eintrag["modifydatum"];
bewerbermappeeinzelansicht($nutzerid,$modifydatum,$num_rows);
}
*/



$submitarray = Array();
$submitarray["mainvalue"] = $rueckgabe;


echo '<?xml version="1.0" encoding="ISO-8859-1"?>
<root>
<htmlinhalte>'."\n";

foreach ($submitarray as $arraykey => $arrayelement) {
	if (!empty($arrayelement)) {
		$arrayelement = str_split($arrayelement, 4096);
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
