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
srand ((double)microtime()*1000000);
$num = rand(0,(count($dbarray)-1));

$praktdbmaster = @mysql_pconnect($dbarray[0]["host"],$dbarray[0]["mysqluser"],$dbarray[0]["mysqlpassword"]);
$praktdbslave = @mysql_pconnect($dbarray[$num]["host"],$dbarray[$num]["mysqluser"],$dbarray[$num]["mysqlpassword"]);

session_name(praktika_id);
session_save_path("/home/praktika/sessions");
session_start();

$rueckgabe .=  "<table width=\"100%\">";

// datensatz abfragen //
$query="SELECT *,nutzer.id as nutzerid,date_format(nutzer.modify, '%d.%m.%Y - %H:%i') as modifydatum FROM nutzer WHERE id = '".$_REQUEST["nutzerid"]."'";
$result = mysql($database_db,$query,$praktdbslave);

// array erzeugen //
$eintrag = mysql_fetch_array($result);

// Statistikinfos speichern
$aktdatum=date('YmdHis');
$userid=$eintrag["nutzerid"];
mysql($database_db,"INSERT INTO viewstatspraktikanten (nutzerid, userid, spalte, modify) VALUES ('$userid', '$s_loginid', '$s_spalte', '$aktdatum')",$praktdbmaster);

// alter berechnen
$zeit=time();
$geburtstag = strtotime($eintrag["geburtsdatum"]);
$alterdiff=$zeit-$geburtstag;
$diff=31560000; // 365 Tage
$alter=$alterdiff/$diff;

// Firmenbookmark Beginn
if ($userid) {
$check="SELECT id FROM firmenbookmark WHERE firmenid='$s_firmenid' AND nutzerid=$userid";
$result = mysql($database_db,$check,$praktdbslave);
$num_rows = mysql_num_rows( $result );
}
// Firmenbookmark Ende

// auf Merkzettel setzen
if ($s_firmenproparray["kandidatensuche"]["warenkorb"][$userid]) $rueckgabe .=  "<tr valign=\"baseline\"><td colspan=\"2\"><a href=\"javascript:Load_SearchData('".$ajaxtype."',document.getElementById('sortierung').value,document.getElementById('ksuchstring').value,document.getElementById('categoryids').value,'".$_REQUEST["ds_count"]."');\">zur Ergebnisliste</a></td><td align=\"right\">Der Kandidat wurde ausw&auml;hlt!<br></td></tr>\n";


// Beginn Firmenbookmark Anzeige
if (!$s_firmenproparray["kandidatensuche"]["warenkorb"][$userid]) {
	$rueckgabe .=  "<tr valign=\"baseline\"><td colspan=\"2\"><a href=\"javascript:Load_SearchData('".$ajaxtype."',document.getElementById('sortierung').value,document.getElementById('ksuchstring').value,document.getElementById('categoryids').value,'".$_REQUEST["ds_count"]."');\">zur Ergebnisliste</a></td><td align=\"right\"><a href=\"javascript:WarenkorbSubmit('".$userid."','detail','".$_REQUEST["ds_count"]."');\">Kandidat ausw&auml;hlen</A></td></tr>\n";
}
// Ende Firmenbookmark Anzeige


// Wer darf was sehen
if ($num_rows == 0) $einsicht=0;
if ($num_rows > 0 || $s_firmenlevel == 2) $einsicht=1;

$rueckgabe .=  "<tr valign=\"baseline\"><td colspan=\"3\" bgCOLOR=\"".$spalte2quicklogindunkel."\"><b>Bewerber</b></td></tr>\n";
$rueckgabe .=  "<tr><td colspan=\"3\">\n";
$rueckgabe .=  "<table width=\"100%\">\n";
if ($s_loggedin=="yes") { $link = "/home/praktikanten/profilausgabe/einzelansicht.phtml?id=" . $userid . "&bewerbsprache=2&einstellungen=true&gesuche=no&" . session_name() . "=" . session_id(); $params="width=720,height=600"; } else { $link = "/home/quicklogin/minilogin.phtml?loginwer=Unternehmen&".session_name()."=".session_id(); $params="width=380,height=250"; }
$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td>&nbsp;</td><td class=\"normal\" align=\"right\"><a href=\"#?". session_name()."=". session_id()." \" onMouseOver=\"return Describe('".$linkdescribe."')\" onMouseOut=\"return Describe('')\" onClick=\"windowanm=window.open('".$link."','messageWindow','scrollbars=yes,statusbar=no,toolbar=no,$params'); return false;\">Bewerbungsmappe ansehen</A></td></tr>\n";
$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td width=\"35%\">Name:</td><td>";
if ( $eintrag["geschlecht"] == "weiblich") {
	$rueckgabe .=  $language["strFrau"]." ";
	}
if ( $eintrag["geschlecht"] == "männlich") {
	$rueckgabe .=  $language["strHerr"]." ";
	}
if (!$eintrag["titel"] == "") {
$rueckgabe .=  $eintrag["titel"] . " \n"; }
$rueckgabe .=  "<B>" . ucfirst($eintrag["vname"]) . " ";
if($s_loggedin != "yes") { $rueckgabe .=  substr(ucfirst($eintrag["name"]), 0, 1)."."; } else { $rueckgabe .=  ucfirst($eintrag["name"]); }
$rueckgabe .=  "</B></td></tr>\n";

if ($s_loggedin=="yes") {
if ($einsicht ==1) {
$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td>Anschrift:</td><td>" . $eintrag["plz"] . " " . $eintrag["ort"] . "</td></tr>\n";
$rueckgabe .=  "</TD></TR>\n";$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td></td><td>" . $eintrag["strasse"] . "</td></tr>\n"; }}
else {
$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td>Anschrift:</td><td class=normal><a href=\"#?". session_name()."=". session_id()." \" onMouseOver=\"return Describe('".$linkdescribe."')\" onMouseOut=\"return Describe('')\" onClick=\"windowanm=window.open('".$link."','messageWindow','scrollbars=yes,statusbar=no,toolbar=no,$params'); return false;\">Anzeige nach Login</A></td></tr>\n"; }
if ($eintrag["grossraum"] <> 94 || $eintrag["grossraum"] > 0) {
$such=$eintrag["grossraum"];
$suchres=@mysql($database_db,"select grossraum from grossraum where id='$such'",$praktdbslave);
if ($einsicht ==1) {
$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td>Gro&szlig;raum</td><td>" . mysql_result($suchres,0,"grossraum") . "</td></tr>\n";}}

if ($eintrag["bundesland"] != 0) {
$bundeslandid=$eintrag["bundesland"];
$bland=mysql($database_db,"select $s_sprache from bundesland where id=$bundeslandid",$praktdbslave);
$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td>Bundesland:</td><td>" . mysql_result($bland,0,$s_sprache) . "</td></tr>\n";}

$landid=$eintrag["land"];
if ($landid >0) {
$land=mysql($database_db,"select $s_sprache from land where id=$landid",$praktdbslave);
$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td>Land:</td><td>" . mysql_result($land,0,$s_sprache) . "</td></tr>\n";
}

$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td>Alter:</td><td>" . sprintf("%01.0u",$alter) . " " . $language["strJahre"] . "</td></tr>\n";
if ($s_loggedin=="yes") {
if (!$eintrag["tel"] == "") {
if ($einsicht == 1) {
$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td>Telefon:</td><td>" . $eintrag["tel"] . "</td></tr>\n";}
}} else {
$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td>Telefax:</td><td class=normal><a href=\"#?". session_name()."=". session_id()." \" onMouseOver=\"return Describe('".$linkdescribe."')\" onMouseOut=\"return Describe('')\" onClick=\"windowanm=window.open('".$link."','messageWindow','scrollbars=yes,statusbar=no,toolbar=no,$params'); return false;\">Anzeige nach Login</A></td></tr>\n"; }
if ($s_loggedin=="yes") {
if (!$eintrag["fax"] == "") {
if ($einsicht == 1) {
$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td>Telefax:</td><td>" . $eintrag["fax"] . "</td></tr>\n";}
}} else {
$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td>Telefax:</td><td class=normal><a href=\"#?". session_name()."=". session_id()." \" onMouseOver=\"return Describe('".$linkdescribe."')\" onMouseOut=\"return Describe('')\" onClick=\"windowanm=window.open('".$link."','messageWindow','scrollbars=yes,statusbar=no,toolbar=no,$params'); return false;\">Anzeige nach Login</A></td></tr>\n"; }
if ($s_loggedin=="yes") {
if (!$eintrag["funktel"] == "") {
if ($einsicht==1) {
$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td>Funktelefon:</td><td>" . $eintrag["funktel"] . "</td></tr>\n";}
}} else {
$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td>Funktelefon:</td class=normal><td><a href=\"#?". session_name()."=". session_id()." \" onMouseOver=\"return Describe('".$linkdescribe."')\" onMouseOut=\"return Describe('')\" onClick=\"windowanm=window.open('".$link."','messageWindow','scrollbars=yes,statusbar=no,toolbar=no,$params'); return false;\">Anzeige nach Login</A></td></tr>\n"; }



if ($s_loggedin=="yes") {
    if ($einsicht == 1) {
		$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td>Email:</td><td><a href=\"../email/?".session_name()."=".session_id()."&prakt=yes&id=".$id."\" onMouseOver=\"return Describe('dem Kandidaten eine Nachricht zukommen lassen')\" onMouseOut=\"return Describe('')\">eMail verfassen</A></td></tr>\n";
		$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td>Kurznachricht:</td><td class=\"normal\"><a href=\"#\" onMouseOver=\"return Describe('dem Kandidaten eine Nachricht zukommen lassen')\" onMouseOut=\"return Describe('')\" onClick=\"windowanm=window.open('/home/firmen/firstkontakt_nutzer.phtml?gesuchid=" . $eintrag["pid"] ."&nutzerid=".$eintrag["nutzerid"] . "&spaltenid=" . $s_spalte . "&firmenid=" . $s_firmenid ."&" . session_name() . "=" .  session_id() . "','messageWindow','scrollbars=yes,statusbar=no,toolbar=no,width=450,height=500'); return false;\">" . $language["strEmailKN"] . "</a></td></tr>\n";
	}


} else {
$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td>Email:</td><td class=normal><a href=\"#?". session_name()."=". session_id()." \" onMouseOver=\"return Describe('".$linkdescribe."')\" onMouseOut=\"return Describe('')\" onClick=\"windowanm=window.open('".$link."','messageWindow','scrollbars=yes,statusbar=no,toolbar=no,$params'); return false;\">Anzeige nach Login</A></td></tr>\n";
$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td>Kurznachricht:</td><td class=normal><a href=\"#?". session_name()."=". session_id()." \" onMouseOver=\"return Describe('".$linkdescribe."')\" onMouseOut=\"return Describe('')\" onClick=\"windowanm=window.open('".$link."','messageWindow','scrollbars=yes,statusbar=no,toolbar=no,$params'); return false;\">Anzeige nach Login</A></td></tr>\n";
}

if (!$eintrag["homepage"] == "") {
if ($einsicht == 1) {
$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td>Homepage:</td><td><a href=\"" . $eintrag["homepage"] . "\" target=\"_blank\">" . $eintrag["homepage"] . "</A></td></tr>\n";
}}
$rueckgabe .=  "<tr valign=\"baseline\"><td>&nbsp;</td><td></td></tr>\n";

$rueckgabe .=  "<tr valign=\"baseline\"><td colspan=\"3\" bgCOLOR=\"".$spalte2quicklogindunkel."\"><b>Qualifikation:</b></td></tr>\n";
if ( $eintrag["studiengang"] != "" && $eintrag["studiengang"] != "0") {
$ins=$eintrag["studiengang"];
$suchres=mysql($database_db,"select studiengang from studiengaenge where id='$ins'",$praktdbslave);
$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td>Studiengang:</td><td>" . mysql_result($suchres,0,"studiengang") . "</td></tr>\n";
if (!$eintrag["hochschule"] == "") {
$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td>Studienort:</td><td>" . $eintrag["hochschule"] . "</td></tr>\n";}
if (!$eintrag["karierrestatus"] == "") {
$ins=$eintrag["karierrestatus"];
$suchres=mysql($database_db,"select german from karierrestatus where id='$ins'",$praktdbslave);
$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td>Karierrestatus:</td><td>" . mysql_result($suchres,0,"german") . "</td></tr>\n";}
}


// Praktikumsstellen ermitteln

// 2 aktive Praktikumsstellen ermitteln
$results = mysql($database_db,"SELECT taetigkeit,zeitraum,id,zeitraum_ab_m,zeitraum_ab_y FROM praktikanten WHERE inactive='false' AND nutzerid = '$nutzerid' ORDER BY ID",$praktdbslave);

// datensaetze gefunden ? //
$num_rows = ($results != false) ? mysql_num_rows( $results ) : 0;

$rueckgabe .=  "<tr><td colspan=\"2\"><br></td></tr>\n";
$rueckgabe .=  "<tr valign=\"baseline\"><td colspan=\"3\" bgCOLOR=\"".$spalte2quicklogindunkel."\"><b>Praktikumsgesuche</b></td></tr>\n";
if ( $num_rows  > 0) {

	// Vorhandene Praktikumstellen anzeigen
	$i = 1;
	while($result_row = mysql_fetch_array($results)) {
		$rueckgabe .=  "<TR><td>".$einruecken."</td><TD COLSPAN=\"2\" VALIGN=\"top\">" . $i++ . ". Praktikumsgesuch</TD></TR>\n";
		$rueckgabe .=  "<TR><td>".$einruecken."</td><TD VALIGN=\"top\">T&auml;tigkeit:</TD>\n";
		$rueckgabe .=  "<TD VALIGN=\"top\">" . $result_row["taetigkeit"] . "</TD></TR>\n";
		$rueckgabe .=  "<TR><td>".$einruecken."</td><TD VALIGN=\"top\">Zeitraum:</TD>\n";
		if ($result_row["zeitraum"]  == "" || $result_row["zeitraum"]  == "NULL") {
			if ($result_row["zeitraum_ab_m"] == 0 || $result_row["zeitraum_ab_y"] == 0) {
				$rueckgabe .=  "<TD VALIGN=\"top\">keine genauen Angaben hinterlegt</TD></TR>\n";
			} else {
				$rueckgabe .=  "<TD VALIGN=\"top\">ab " . $result_row["zeitraum_ab_m"] . "/" . $result_row["zeitraum_ab_y"] . "</TD></TR>\n";

			}
		} else {
			if($result_row["zeitraum_ab_m"] == 0 || $result_row["zeitraum_ab_y"] == 0) {
				$rueckgabe .=  "<TD VALIGN=\"top\">" . $result_row["zeitraum"] . "</TD></TR>\n";
			} else {
				$rueckgabe .=  "<TD VALIGN=\"top\">ab " . $result_row["zeitraum_ab_m"] . "/" . $result_row["zeitraum_ab_y"] . "<br>" . $result_row["zeitraum"] . "</TD></TR>\n";
			}
		}
		$rueckgabe .=  "<TR><td>".$einruecken."</td><TD COLSPAN=\"2\" VALIGN=\"top\"><A href=\"#\" onClick=\"window.open('../detail.phtml?id=" . $result_row["id"] . "&spalte=1&miniwindow=yes&noadvertise=yes','messageWindow','scrollbars=yes,statusbar=no,toolbar=no,width=720,height=600'); return false;\">Gesuch ansehen</a></TD></TR>\n";
		$rueckgabe .=  "<TR><TD COLSPAN=\"3\"><HR></TD></TR>\n";
	}
}


// Diplomthemen ermitteln //


// 2 aktive Diplomthemen ermitteln
$results = mysql($database_db,"SELECT titel,zeitraum,studiengang,id,zeitraum_ab_m,zeitraum_ab_y FROM diplomgesuch WHERE inactive='false' AND nutzerid ='$nutzerid' ORDER BY ID desc",$praktdbslave);

// datensaetze gefunden ? //
$num_rows = ($results != false) ? mysql_num_rows( $results ) : 0;

$rueckgabe .=  "<tr><td colspan=\"3\"><br></td></tr>\n";
$rueckgabe .=  "<tr valign=\"baseline\"><td colspan=\"3\" bgCOLOR=\"".$spalte2quicklogindunkel."\"><b>Diplomthemen</b></td></tr>\n";
if ( $num_rows  > 0) {
	// Vorhandene Diplomthemen anzeigen
	$i = 1;
	while($result_row = mysql_fetch_array($results)) {
		$rueckgabe .=  "<TR><td>".$einruecken."</td><TD COLSPAN=\"2\" VALIGN=\"top\">" . $i++ . ". Diplomthema:</TD></TR>\n";
		$rueckgabe .=  "<TR><td>".$einruecken."</td><TD VALIGN=\"top\">Titel der Diplomarbeit:</TD>\n";
		$rueckgabe .=  "<TD VALIGN=\"top\">" . $result_row["titel"] . "</TD></TR>\n";
		$rueckgabe .=  "<TR><td>".$einruecken."</td><TD VALIGN=\"top\">Zeitraum:</TD>\n";
		if ($result_row["zeitraum"]  == "" || $result_row["zeitraum"]  == "NULL") {
			if ($result_row["zeitraum_ab_m"] == 0 || $result_row["zeitraum_ab_y"] == 0) {
				$rueckgabe .=  "<TD VALIGN=\"top\">keine genauen Angaben hinterlegt</TD></TR>\n";
			} else {
				$rueckgabe .=  "<TD VALIGN=\"top\">ab " . $result_row["zeitraum_ab_m"] . "/" . $result_row["zeitraum_ab_y"] . "</TD></TR>\n";

			}
		} else {
			if($result_row["zeitraum_ab_m"] == 0 || $result_row["zeitraum_ab_y"] == 0) {
				$rueckgabe .=  "<TD VALIGN=\"top\">" . $result_row["zeitraum"] . "</TD></TR>\n";
			} else {
				$rueckgabe .=  "<TD VALIGN=\"top\">ab " . $result_row["zeitraum_ab_m"] . "/" . $result_row["zeitraum_ab_y"] . "<br>" . $result_row["zeitraum"] . "</TD></TR>\n";
			}
		}
		$studiengangid=$result_row["studiengang"];
		$studiengang=mysql($database_db,"select studiengang from studiengaenge where id=$studiengangid",$praktdbslave);
		if (mysql_numrows($studiengang) > 0) {
			$rueckgabe .=  "<TR><td>".$einruecken."</td><TD VALIGN=\"top\">Studiengang:</TD>\n";
			$rueckgabe .=  "<TD VALIGN=\"top\">" . mysql_result($studiengang,0,"studiengang") . "</TD></TR>\n";
		}
		$rueckgabe .=  "<TR><td>".$einruecken."</td><TD COLSPAN=\"2\" VALIGN=\"top\"><A href=\"#\" onClick=\"window.open('../detail.phtml?id=" . $result_row["id"] . "&spalte=2&miniwindow=yes&noadvertise=yes','messageWindow','scrollbars=yes,statusbar=no,toolbar=no,width=720,height=600'); return false;\">Gesuch ansehen</a></TD></TR>\n";
		$rueckgabe .=  "<TR><TD COLSPAN=\"3\"><HR></TD></TR>\n";
	}
}


// Berufseinstiegsstellen ermitteln

// 2 aktive Berufseinstiegsstellen ermitteln
$results = mysql($database_db,"SELECT berufsfeld,bezeichnung as taetigkeit,id,zeitraum_ab_m,zeitraum_ab_y FROM berufseinstieggesuch WHERE inactive='false' AND nutzerid ='$nutzerid' ORDER BY id DESC",$praktdbslave);

// datensaetze gefunden ? //
$num_rows = ($results != false) ? mysql_num_rows( $results ) : 0;

$rueckgabe .=  "<tr><td colspan=\"2\"><br></td></tr>\n";
$rueckgabe .=  "<tr valign=\"baseline\"><td colspan=\"3\" bgCOLOR=\"".$spalte2quicklogindunkel."\"><b>Berufseinstiegsgesuche</b></td></tr>\n";
if ( $num_rows  > 0) {

	$i = 1;
	while ($result_row = mysql_fetch_array($results )) {
		$rueckgabe .=  "<TR><td>".$einruecken."</td><TD COLSPAN=\"2\" VALIGN=\"top\">" . $i++ . ". Berufseinstiegsstelle</TD></TR>\n";
		$rueckgabe .=  "<TR><td>".$einruecken."</td><TD VALIGN=\"top\">T&auml;tigkeit:</TD>\n";
		$rueckgabe .=  "<TD VALIGN=\"top\">" . $result_row["taetigkeit"] . "</TD></tr>\n";
		$rueckgabe .=  "<TR><td>".$einruecken."</td><TD VALIGN=\"top\">Zeitraum:</TD>\n";
		$rueckgabe .=  "<TD VALIGN=\"top\">ab " . $result_row["zeitraum_ab_m"] . "/" . $result_row["zeitraum_ab_y"] . "</TD></TR>\n";

		if ($result_row["berufsfeld"] != "" && $result_row["berufsfeld"] != 0) {
			$berufsfeldid=$result_row["berufsfeld"];
			$berufsfeld=mysql($database_db,"select $s_sprache from berufsfelder where id=$berufsfeldid",$praktdbslave);
			if (mysql_numrows($berufsfeld) > 0) {
				$rueckgabe .=  "<TR><td>".$einruecken."</td><TD VALIGN=\"top\">Berufsfeld:</TD>\n";
				$rueckgabe .=  "<TD VALIGN=\"top\">" . mysql_result($berufsfeld,0,$s_sprache) . "</TD></TR>\n";
			}
		}
		$rueckgabe .=  "<TR><td>".$einruecken."</td><TD COLSPAN=\"2\" VALIGN=\"top\"><A href=\"#\" onClick=\"window.open('../detail.phtml?id=" . $result_row["id"] . "&spalte=5&miniwindow=yes&noadvertise=yes','messageWindow','scrollbars=yes,statusbar=no,toolbar=no,width=720,height=600'); return false;\">Gesuch ansehen</a></TD></TR>\n";
		$rueckgabe .=  "<TR><TD COLSPAN=\"3\"><HR></TD></TR>\n";

	}
}

// Nebenjobs ermitteln //

// 2 aktive Nebenjobs ermitteln
$results = mysql($database_db,"SELECT berufsfeld,taetigkeit,zeitraum,id,zeitraum_ab_m,zeitraum_ab_y FROM nebenjobgesuch WHERE inactive='false' AND nutzerid ='$nutzerid' ORDER BY id DESC",$praktdbslave);

// datensaetze gefunden ? //
$num_rows = ($results != false) ? mysql_num_rows( $results ) : 0;

$rueckgabe .=  "<tr><td colspan=\"2\"><br></td></tr>\n";
$rueckgabe .=  "<tr valign=\"baseline\"><td colspan=\"3\" bgCOLOR=\"".$spalte2quicklogindunkel."\"><b>Nebenjobgesuche</b></td></tr>\n";
if ( $num_rows  > 0) {

	$i = 1;
	while ($result_row = mysql_fetch_array ($results)) {
		$rueckgabe .=  "<TR><td>".$einruecken."</td><TD COLSPAN=\"2\" VALIGN=\"top\">" . $i++ . ". Nebenjobgesuch</TD></TR>\n";
		$rueckgabe .=  "<TR><td>".$einruecken."</td><TD VALIGN=\"top\">T&auml;tigkeit:</TD>\n";
		$rueckgabe .=  "<TD VALIGN=\"top\">" . $result_row["taetigkeit"] . "</TD></tr>\n";
		$rueckgabe .=  "<TR><td>".$einruecken."</td><TD VALIGN=\"top\">Zeitraum:</TD>\n";
		if ($result_row["zeitraum"]  == "" || $result_row["zeitraum"]  == "NULL") {
			if ($result_row["zeitraum_ab_m"] == 0 || $result_row["zeitraum_ab_y"] == 0) {
				$rueckgabe .=  "<TD VALIGN=\"top\">keine genauen Angaben hinterlegt</TD></TR>\n";
			} else {
				$rueckgabe .=  "<TD VALIGN=\"top\">ab " . $result_row["zeitraum_ab_m"] . "/" . $result_row["zeitraum_ab_y"] . "</TD></TR>\n";

			}
		} else {
			if($result_row["zeitraum_ab_m"] == 0 || $result_row["zeitraum_ab_y"] == 0) {
				$rueckgabe .=  "<TD VALIGN=\"top\">" . $result_row["zeitraum"] . "</TD></TR>\n";
			} else {
				$rueckgabe .=  "<TD VALIGN=\"top\">ab " . $result_row["zeitraum_ab_m"] . "/" . $result_row["zeitraum_ab_y"] . "<br>" . $result_row["zeitraum"] . "</TD></TR>\n";
			}
		}
		$berufsfeldid=$result_row["berufsfeld"];
		$berufsfeld=mysql($database_db,"select $s_sprache from berufsfelder where id=$berufsfeldid",$praktdbslave);
		if (mysql_numrows($berufsfeld) > 0) {
			$rueckgabe .=  "<TR><td>".$einruecken."</td><TD VALIGN=\"top\">Berufsfeld:</TD>\n";
			$rueckgabe .=  "<TD VALIGN=\"top\">" . mysql_result($berufsfeld,0,$s_sprache) . "</TD></TR>\n";
		}
		$rueckgabe .=  "<TR><td>".$einruecken."</td><TD COLSPAN=\"2\" VALIGN=\"top\"><A href=\"#\" onClick=\"window.open('../detail.phtml?id=" . $result_row["id"] . "&spalte=3&miniwindow=yes&noadvertise=yes','messageWindow','scrollbars=yes,statusbar=no,toolbar=no,width=720,height=600'); return false;\">Gesuch ansehen</a></TD></TR>\n";
		$rueckgabe .=  "<TR><TD COLSPAN=\"3\"><HR></TD></TR>\n";
	}
}

// Ausbildungsplätze ermitteln //


// 2 aktive Ausbildungsplätze ermitteln
$results = mysql($database_db,"SELECT lehre,taetigkeit,id,zeitraum_ab_m,zeitraum_ab_y,zeitraum FROM ausbildungsgesuch WHERE inactive='false' AND nutzerid ='$nutzerid' ORDER BY id DESC",$praktdbslave);

// datensaetze gefunden ? //
$num_rows = ($results != false) ? mysql_num_rows( $results ) : 0;

$rueckgabe .=  "<tr><td colspan=\"3\"><br></td></tr>\n";
$rueckgabe .=  "<tr valign=\"baseline\"><td colspan=\"3\" bgCOLOR=\"".$spalte2quicklogindunkel."\"><b>Ausbildungplatzgesuche</b></td></tr>\n";
if ( $num_rows  > 0) {
	$i=1;
	while ($result_row = mysql_fetch_array ( $results )) {
		$rueckgabe .=  "<TR><td>".$einruecken."</td><TD COLSPAN=\"2\" VALIGN=\"top\">" . $i++ . ". Ausbildungplatzgesuch</TD></TR>\n";
		$lehreid=$result_row["lehre"];
		// Nutzerprüfung ob Mann oder Frau wegen Anzeige des richtigen Berufsgeschlechtes //
		if ($s_loggedin=="yes" || $s_loggedinnutzer == true) {
			$resultn = mysql($database_db,"SELECT anrede from nutzer WHERE id = '$nutzerid'",$praktdbslave);
			// array erzeugen //
			$eintragn = mysql_fetch_array ( $resultn );
			if ($eintragn["anrede"] == "Herr") {
				$lehre=mysql($database_db,"select berufswahl from berufswahl_mann where id=$lehreid",$praktdbslave);
			} else {
				$lehre=mysql($database_db,"select berufswahl from berufswahl_frau where id=$lehreid",$praktdbslave);
			}
			// wenn noch nicht eingeloggt //
		} else {
			$lehre=mysql($database_db,"select berufswahl from berufswahl_mann where id=$lehreid",$praktdbslave);
		}
		$rueckgabe .=  "<tr valign=\"baseline\"><td>".$einruecken."</td><td>Ausbildung zum:</b></td><td>" . mysql_result($lehre,0,"berufswahl") . "\n";
		$rueckgabe .=  "<TR><td>".$einruecken."</td><TD VALIGN=\"top\">T&auml;tigkeit:</TD>\n";
		$rueckgabe .=  "<TD VALIGN=\"top\">" . $result_row["taetigkeit"] . "</TD></tr>\n";
		$rueckgabe .=  "<TR><td>".$einruecken."</td><TD VALIGN=\"top\">Zeitraum:</TD>\n";
		if ($result_row["zeitraum"]  == "" || $result_row["zeitraum"]  == "NULL") {
			if ($result_row["zeitraum_ab_m"] == 0 || $result_row["zeitraum_ab_y"] == 0) {
				$rueckgabe .=  "<TD VALIGN=\"top\">keine genauen Angaben hinterlegt</TD></TR>\n";
			} else {
				$rueckgabe .=  "<TD VALIGN=\"top\">ab " . $result_row["zeitraum_ab_m"] . "/" . $result_row["zeitraum_ab_y"] . "</TD></TR>\n";

			}
		} else {
			if($result_row["zeitraum_ab_m"] == 0 || $result_row["zeitraum_ab_y"] == 0) {
				$rueckgabe .=  "<TD VALIGN=\"top\">" . $result_row["zeitraum"] . "</TD></TR>\n";
			} else {
				$rueckgabe .=  "<TD VALIGN=\"top\">ab " . $result_row["zeitraum_ab_m"] . "/" . $result_row["zeitraum_ab_y"] . "<br>" . $result_row["zeitraum"] . "</TD></TR>\n";
			}
		}
		$rueckgabe .=  "<TR><td>".$einruecken."</td><TD COLSPAN=\"2\" VALIGN=\"top\"><A href=\"#\" onClick=\"window.open('../detail.phtml?id=" . $result_row["id"] . "&spalte=4&miniwindow=yes&noadvertise=yes','messageWindow','scrollbars=yes,statusbar=no,toolbar=no,width=720,height=600'); return false;\">Gesuch ansehen</a></TD></TR>\n";
		$rueckgabe .=  "<TR><TD COLSPAN=\"3\"><HR></TD></TR>\n";
	}
}

		$rueckgabe .=  "<TR><TD COLSPAN=\"3\"><form><hr><input type=\"BUTTON\" value=\"zurück zur Ergebnisliste\" onClick=\"Load_SearchData('".$ajaxtype."',document.getElementById('sortierung').value,document.getElementById('ksuchstring').value,document.getElementById('categoryids').value,'".$_REQUEST["ds_count"]."');\"></form></TD></TR>\n";


/* Anzeige der Bewerbungsmappe
//if ($s_loggedin=="yes" && ($s_firmenlevel>1 || $num_rows == 1 || $gesuchedbzahl > 0)) {
if ($s_loggedin=="yes" && $userid) {
$modifydatum=$eintrag["modifydatum"];
bewerbermappeeinzelansicht($userid,$modifydatum,$num_rows);
}
*/
$rueckgabe .=  "</table>\n";



$submitarray = Array();
$submitarray["mainvalue"] = $rueckgabe;


echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
echo "<root>\n";
echo "<htmlinhalte>\n";

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
