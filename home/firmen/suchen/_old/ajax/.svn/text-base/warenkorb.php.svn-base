<?
// config
 $preis1 = "1.3";
 $preis2 = "1.6";
 $preis3 = "2.4";
// config ende

// ---------------------------------------------------

header("Content-Type: text/xml");

require("/home/praktika/etc/dbzugang.inc.php");
require("/home/praktika/etc/lib.inc.php");

$praktdbmaster = @mysql_pconnect($dbarray[0]["host"],$dbarray[0]["mysqluser"],$dbarray[0]["mysqlpassword"]);
session_name(praktika_id);
session_save_path("/home/praktika/sessions");
session_start();


$rueckgabe .= "<h1 class=\"layout1\">Warenkorb - Kandidatensuche</h1>";

//if (!strpos($HTTP_REFERER,"warenkorb.phtml") && !strpos($HTTP_REFERER,"minilogin.phtml")) $s_ergebnisarry[sucheingabe][referer] = $HTTP_REFERER;

if ($gesamt) {

include_once ("DB.php");
$dsn = "mysql://".$dbarray[0][mysqluser].":".$dbarray[0][mysqlpassword]."@".$dbarray[0][host]."/".$database_temp;
$db = DB::connect($dsn);
$datarow =& $db->getCol('SELECT nutzerid FROM '.$s_kandidatentable);
$datarow = array_flip($datarow);

function array_set_value(&$item1, $key, $prefix) {
   $item1 = $prefix;
}
array_walk($datarow, 'array_set_value',$s_ergebnisarry[sucheingabe][suchart]);

if (!is_array($s_ergebnisarry[warenkorb])) $s_ergebnisarry[warenkorb] = array();

//print_pr($datarow);

$s_ergebnisarry[warenkorb] += $datarow;

//print_pr($s_ergebnisarry[warenkorb]);

// Datensätze filtern, die schon gebucht sind
if ($s_loggedin=="yes") {
$bookmarkquery="SELECT nutzerid FROM firmenbookmark WHERE firmenid = $s_firmenid";

$bookmarkresults=mysql($database_db,$bookmarkquery,$praktdbslave);

if (mysql_num_rows($bookmarkresults) > 0) $error = "<br><br>Die gew&auml;hlten Kandidaten sind bereits auf Ihrem Merkzettel gespeichert!";

// datensaetze gefunden ? //
while ($bookmarkresult_row = mysql_fetch_array ( $bookmarkresults, MYSQL_ASSOC )) {
	unset($s_ergebnisarry[warenkorb][$bookmarkresult_row["nutzerid"]]);
}
}

$anzahl = count ($s_firmenproparray["kandidatensuche"]["warenkorb"]);

} else {

// Datensätze filtern, die schon gebucht sind
if ($s_loggedin=="yes") {
$bookmarkquery="SELECT nutzerid FROM firmenbookmark WHERE firmenid = $s_firmenid";

$bookmarkresults=mysql($database_db,$bookmarkquery,$praktdbslave);

if (mysql_num_rows($bookmarkresults) > 0) $error = "Die gew&auml;hlten Kandidaten sind bereits auf Ihrem Merkzettel gespeichert!";

// datensaetze gefunden ? //
while ($bookmarkresult_row = mysql_fetch_array ( $bookmarkresults, MYSQL_ASSOC )) {
	unset($s_ergebnisarry[warenkorb][$bookmarkresult_row["nutzerid"]]);
}
}

if ($s_firmenproparray["kandidatensuche"]["warenkorb"]) {
if ($delete) {
foreach ($s_firmenproparray["kandidatensuche"]["warenkorb"] as $ergebniskey => $ergebnisvalue) {
	if ($delete == $ergebnisvalue) {unset($s_firmenproparray["kandidatensuche"]["warenkorb"][$ergebniskey]);}
}}

$anzahl = count ($s_firmenproparray["kandidatensuche"]["warenkorb"]);

}
}

if ($dest) $s_firmenproparray["kandidatensuche"]["destination"] = $dest;


if ($s_loggedin!="yes" || $s_firmenlevel < 2 || $s_firmenlevel > 2) {
	if (!$s_firmenproparray["kandidatensuche"]["destination"]) $s_firmenproparray["kandidatensuche"]["destination"]="merkzettel";
} else {
	if (!$s_firmenproparray["kandidatensuche"]["destination"]) $s_firmenproparray["kandidatensuche"]["destination"]="mailing";
}


if ( $anzahl  > 0) {

$rueckgabe .= "<a href=\"wk_inhalt.phtml?".session_name()."=".session_id()."\">Im Warenkorb befinden derzeit insgesamt <B>".$anzahl."</B> Kandidaten.</a><br><br>";

$rueckgabe .= "<table width=\"100%\" border=\"0\">\n";
$rueckgabe .= "<tr>\n";
$rueckgabe .= "<td width=\"10%\">&nbsp;</td>\n";
$rueckgabe .= "<td width=\"35%\"><b>Suchm&ouml;glichkeit</b></td>\n";
$rueckgabe .= "<td align=\"right\"><b>Menge</b></td>\n";
if ($s_loggedin!="yes" || $s_firmenlevel < 2 || $s_firmenlevel > 2) {
$rueckgabe .= "<td width=\"15%\" align=\"right\"><b>Einzelpreis</b></td>\n";
$rueckgabe .= "<td width=\"15%\" align=\"right\"><b>Rabatt</b></td>\n";
$rueckgabe .= "<td width=\"15%\" align=\"right\"><b>Gesamtpreis</b></td>\n";
}
$rueckgabe .= "</tr>\n";
$rueckgabe .= "<tr align=\"center\">\n";
if ($s_loggedin!="yes" || $s_firmenlevel < 2 || $s_firmenlevel > 2) {
$rueckgabe .= "<td colspan=\"6\" height=\"1\" bgcolor=\"#F7E7CE\"></td>\n";
} else {
$rueckgabe .= "<td colspan=\"3\" height=\"1\" bgcolor=\"#F7E7CE\"></td>\n";
}
$rueckgabe .= "</tr>\n";

if ($s_loggedin=="yes") {
$query="SELECT `gesuchedb` FROM `firmen_prop_assign` WHERE firmenid = $s_firmenid";
$gesucheresults=mysql($database_db,$query,$praktdbmaster);

$gesuche_anzahl = mysql_result($gesucheresults,0,"gesuchedb");
} else {
$gesuche_anzahl = 0;
}

$gesuche_anzahl_berechnung = $gesuche_anzahl;

if ($anzahl > 0) {
$rabattsatz = rabattstaffel($anzahl);
$rueckgabe .= "<tr>\n";
$rueckgabe .= "<td align=\"right\" width=\"10%\"><a href=\"warenkorb.phtml?delete=3&".session_name()."=".session_id()."\"><IMG SRC=\"/home/gifs/symbole/loeschen.gif\" ALT=\"diese Suche löschen\" border=\"0\"></a></td>\n";
$rueckgabe .= "<td width=\"55%\"><a href=\"wk_inhalt.phtml?profilsuche=true&".session_name()."=".session_id()."\">Profilsuche</a></td>\n";
$rueckgabe .= "<td align=\"right\">".$anzahl."</td>\n";
if ($s_loggedin!="yes" || $s_firmenlevel < 2 || $s_firmenlevel > 2) {
$rueckgabe .= "<td align=\"right\" width=\"15%\">EUR&nbsp;".sprintf("%01.2f",$preis3)."</td>\n";
$rueckgabe .= "<td align=\"right\" width=\"15%\">".((1 - $rabattsatz)*100)."%</td>\n";
$guthabenanzahl = $anzahl - $gesuche_anzahl_berechnung;
if ($guthabenanzahl3 < 0) { $guthabenanzahl=0; }
$gesuche_anzahl_berechnung = $gesuche_anzahl_berechnung-($anzahl-$guthabenanzahl);
$rueckgabe .= "<td align=\"right\" width=\"15%\">EUR&nbsp;".sprintf("%01.2f",($gesamtpreis = $preis3*$guthabenanzahl*$rabattsatz))."</td>\n";
}
$rueckgabe .= "</tr>\n";
}

$gesamtpreis = $gesamtpreis1 + $gesamtpreis2 + $gesamtpreis3;

if ($gesuche_anzahl >= $anzahl) {
$gesamtpreis = 0;

}
if ($s_loggedin!="yes" || $s_firmenlevel < 2 || $s_firmenlevel > 2) {

if ($gesamtpreis < 12 && $gesamtpreis != 0) {
$gesamtpreis = 12;
$rueckgabe .= "<tr>\n";
$rueckgabe .= "<td colspan=\"5\" align=\"right\"><b><u>Mindestsumme:</u></b></td>\n";
$rueckgabe .= "<td align=\"right\" width=\"15%\"><b><u>EUR&nbsp;".sprintf("%01.2f",($gesamtpreis))."</u></b></td>\n";
$rueckgabe .= "</tr>\n";
} else {
$rueckgabe .= "<tr>\n";
$rueckgabe .= "<td colspan=\"5\" align=\"right\"><b><u>Summe:</u></b></td>\n";
$rueckgabe .= "<td align=\"right\" width=\"15%\"><b><u>EUR&nbsp;".sprintf("%01.2f",($gesamtpreis))."</u></b></td>\n";
$rueckgabe .= "</tr>\n";
}
$rueckgabe .= "<form action=\"warenkorb.phtml\" method=\"get\">\n";
$rueckgabe .= "<tr><td colspan=6 class=small><br><br>Alle Preise verstehen sich zuzüglich gesetzlicher MwSt.</td></tR>";

$rueckgabe .= "<tr valign=\"top\"><td colspan=6><hr></td></tr>";

$rueckgabe .= "<tr valign=\"top\"><td colspan=6>Bitte wählen Sie die Aktion nach der Buchung: <select name=\"dest\" onChange=\"submit();\">";
$rueckgabe .= "<option value=\"mailing\"";
if ($s_firmenproparray["kandidatensuche"]["destination"] == "mailing") $rueckgabe .= " selected";
$rueckgabe .= ">Email an alle Kandidaten</option>";
$rueckgabe .= "<option value=\"warenkorb\"";
if ($s_firmenproparray["kandidatensuche"]["destination"] == "warenkorb") $rueckgabe .= " selected";
$rueckgabe .= ">auf Merkzettel setzen</option>";
$rueckgabe .= "</select></td></tr></form>\n";


} else {

$rueckgabe .= "<form action=\"warenkorb.phtml\" method=\"get\">\n";
$rueckgabe .= "<tr valign=\"top\"><td colspan=6><br><br>Bitte wählen Sie die ensprechende Aktion: <select name=\"dest\" onChange=\"submit();\">";
$rueckgabe .= "<option value=\"mailing\"";
if ($s_firmenproparray["kandidatensuche"]["destination"] == "mailing") $rueckgabe .= " selected";
$rueckgabe .= ">Email an alle Kandidaten</option>";
$rueckgabe .= "<option value=\"warenkorb\"";
if ($s_firmenproparray["kandidatensuche"]["destination"] == "warenkorb") $rueckgabe .= " selected";
$rueckgabe .= ">auf Merkzettel setzen</option>";
$rueckgabe .= "</select></td></tr></form>\n";

}

$rueckgabe .= "</table><hr><table width=\"100%\" border=0>";

if ($gesamtpreis != 0) {
$rueckgabe .= "<form><tr><td colspan=\"2\"><img src=\"/home/gifs/symbole/neu.gif\">&nbsp;<a href=\"wk_drucken.phtml?".session_name()."=".session_id()."\" target=\"wk_drucken\">Diesen Warenkorb drucken und per <b>Fax</b> bestellen...</a></td></tr></form>";
}
$rueckgabe .= "<tr><td colspan=\"2\"><hr></td></tr>";
if ($s_loggedin=="yes") {

if ($gesuche_anzahl > 0) $rueckgabe .= "<tr><td colspan=\"2\">Sie haben noch ein Guthaben vom $gesuche_anzahl Kandidaten.</td></tr>";

}
			$rueckgabe .= "<tr><td align=\"left\">";

if ($s_loggedin=="yes" && ($s_firmenlevel < 2  || $s_firmenlevel > 2) && $gesuche_anzahl < $anzahl) {
                  $rueckgabe .="<form action=\"/home/firmen/commerce/produktbuchung/kandidatensuche/bestaetigen.phtml\" method=\"POST\">\n";
			$rueckgabe .="<input type=\"submit\" value=\"Buchung vornehmen\" name=\"submit\">\n";
			$rueckgabe .="<input type=\"hidden\" value=\"". session_id() ."\" name=\"". session_name()."\">\n";
			$rueckgabe .="<input type=\"hidden\" value=\"". $gesamtpreis ."\" name=\"preis\">\n";
			$rueckgabe .="<input type=\"hidden\" value=\"3\" name=\"pid\">\n";

} elseif ($s_loggedin=="yes") {
	if ($s_firmenproparray["kandidatensuche"]["destination"] == "mailing") {
                  $rueckgabe .="<form action=\"mailing.phtml\" method=\"POST\">\n";
			$rueckgabe .="<input type=\"submit\" value=\"Mailing vorbereiten\" name=\"submit\">\n";
			$rueckgabe .="<input type=\"hidden\" value=\"yes\" name=\"mailinsert\">\n";
			$rueckgabe .="<input type=\"hidden\" value=\"". session_id() ."\" name=\"". session_name()."\">\n";
		}
	else {
                  $rueckgabe .="<form action=\"uebertrag.phtml\" method=\"POST\">\n";
			$rueckgabe .="<input type=\"submit\" value=\"auf Merkzettel übertragen\" name=\"submit\">\n";
			$rueckgabe .="<input type=\"hidden\" value=\"". session_id() ."\" name=\"". session_name()."\">\n";
		}

} else {
            $rueckgabe .="<form>\n";
			$rueckgabe .="<input type=\"button\" value=\"Einloggen\" onClick=\"windowanm=window.open('/home/quicklogin/minilogin.phtml?loginwer=Unternehmen&".session_name()."=".session_id()."','messageWindow','scrollbars=yes,statusbar=no,toolbar=no,width=380,height=250'); return false;\">\n";
}
            if ($s_ergebnisarry[sucheingabe][referer]=="") { $link = "/home/firmen/einblick.phtml?".session_name()."=".session_id(); } else { $link = $s_ergebnisarry[sucheingabe][referer]; }

			$rueckgabe .= "</td>\n";
			$rueckgabe .= "<td align=right><input type=\"BUTTON\" name=\"y\" value=\"zurück zur Ergebnisliste\" onClick=\"location.replace('".$link."');\"></td>\n";
			$rueckgabe .= "</tr></table>\n";
            $rueckgabe .= "</form>\n";
            if ($s_loggedin !="yes") {
            	$rueckgabe .= '<br>
                  Um eine Buchung des Produktes "Kandidatensuche" vorzunehmen, müssen Sie angemeldet sein. <span class=normal><a href="#" onClick="windowanm=window.open(\'/home/quicklogin/minilogin.phtml?loginwer=Unternehmen&'.session_name().'='.session_id().'\',\'messageWindow\',\'scrollbars=yes,statusbar=no,toolbar=no,width=380,height=250\'); return false;"><b>Loggen Sie sich jetzt ein!</b></a></span><br>
                  Sollten Sie noch nicht bei praktika.de registriert sein, können Sie sich hier <span class=normal><a href="#" onClick="windowanm=window.open(\'/home/firmen/firmencheck.phtml\',\'messageWindow\',\'scrollbars=yes,statusbar=no,toolbar=no,width=515,height=380\'); return false;"><b>kostenlos anmelden</b></a></span>!';
                  }
             } else {
              	if ($s_ergebnisarry[sucheingabe][referer]=="") { $link = "/home/firmen/einblick.phtml?".session_name()."=".session_id();} else { $link = $s_ergebnisarry[sucheingabe][referer];}
            $rueckgabe .= "<table width=\"100%\">\n";
              $rueckgabe .= "<tr>\n";
                $rueckgabe .= "<td>Es liegen derzeit keine Kandidaten im Warenkorb!<br>\n";
                  $rueckgabe .= $error;
                  $rueckgabe .= "<form>\n";
                    $rueckgabe .= "<hr>\n";
                      $rueckgabe .= "<input type=\"BUTTON\" name=\"y\" value=\"zur Suche\" onClick=\"location.replace('".$link."');\">\n";
                  $rueckgabe .= "</form>\n";
                $rueckgabe .= "</td>\n";
              $rueckgabe .= "</tr>\n";
            $rueckgabe .= "</table>\n";
            }

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
echo "</htmlinhalte>\n";
echo "</root>";

?>
