<?php
//  praktika.de ajax File - KANDIDATENAUSGABE -  WENN FIRMEN SUCHEN

require("/home/praktika/etc/dbzugang.inc.php");
require("/home/praktika/etc/lib.inc.php");

$spaltenarray=init_spaltenarray();


/* FUNCTIONS */

function seitenausgabe2($num_rows, $ds_pro_seite, $ds_count, $ajaxtype) {
	if ( !$z && !$seitenzaehler && $ds_count > 90 ) {
		$seitenzaehler = ( ($ds_count / 10) + 10 );

		$laenge = strlen($ds_count);
		if ( $ds_count % 100 > 0) 	{
			$splitzahl = substr( $ds_count, 0, ($laenge - ($laenge - 1) ) );
			$z = $splitzahl * 10 + 1;
			$seitenzaehler = ($splitzahl + 1) * 10;
		}
	}

	if($num_rows > $ds_pro_seite) {
		$Seiten = intval($num_rows / $ds_pro_seite);
		if($num_rows % $ds_pro_seite) {
			$Seiten++;
		}
	}

	if ($Seiten > 0) $content = "Seiten:&nbsp;";
	if (!$z && !$seitenzaehler) { $seitenzaehler = 10; $z = 1;}
	if (!$seitenzaehler && $Seiten >= 10) { $seitenzaehler = 10; $z = 1;}
	if ($seitenzaehler > $Seiten) $seitenzaehler = $Seiten;
	$z = ( floor($ds_count / 100) * 10 ) + 1;

	if ($ds_count % ($ds_pro_seite * 10) == 0) { $dcount = ($ds_count - $ds_pro_seite); } else { $dcount = ( $ds_count - ($ds_pro_seite * 10) ); }
	if ($z >= 10) $content .= '<a href="javascript:Load_SearchData("'.$ajaxtype.'", document.getElementById("sortierung").value,document.getElementById("ksuchstring").value,document.getElementById("categoryids").value,"'.($dcount).'");">&lt;</a>&nbsp;';

	for ($i = $z; $i <= $seitenzaehler; $i++) {
		$fwd = ($i - 1) * $ds_pro_seite;
		if ($i < $seitenzaehler) {$slash="&nbsp;|&nbsp;"; } else {$slash="";}

		if ($fwd == $ds_count) {$content .= $i.$slash;}
		else {
			$content .= '<a href="javascript:Load_SearchData("'.$ajaxtype.'",document.getElementById("sortierung").value,document.getElementById("ksuchstring").value,document.getElementById("categoryids").value,"'.$fwd.'");">$i</a>'.$slash;
		}
	}

	if ($seitenzaehler < $Seiten) $content .= '&nbsp;<a href="javascript:Load_SearchData("'.$ajaxtype.'",document.getElementById("sortierung").value,document.getElementById("ksuchstring").value,document.getElementById("categoryids").value,"'.($fwd+$ds_pro_seite).'");">&gt;</a>';

	return $content;
}


function suchergebnis($ds_count, $num_rows, $fineselect) {
	global $_REQUEST,$results,$s_ergebnisarry,$praktdbmaster,$praktdbslave,$database_db,$database_temp,$database_comm,$spaltenarray,$s_spalte,$language,$s_loggedin,$s_firmenproparray,$s_loggedinnutzer,$stellenausgabe,$suchstring,$wk_inhalt;

	if (!$s_sprache) $s_sprache = "german";

	if ($fineselect) {

		$aktdate = strtotime( date("Y-m-d") );

		$i = 0;
		while ( ($i < 500) && ( $result_inhalt = mysql_fetch_array ($results, MYSQL_ASSOC) ) ) {
			$suchinhalte["userid"][$result_inhalt["nutzerid"]] = true;
			if($result_inhalt["gesuch_spalte"] != 0)
				$suchinhalte["bereiche"][$result_inhalt["gesuch_spalte"]] = true;
			if($result_inhalt["gesuch_berufsfeld"] != 0)
				$suchinhalte["einsatzgebiet"][$result_inhalt["gesuch_berufsfeld"]] = true;
			if($result_inhalt["geschlecht"] != "")
				$suchinhalte["geschlecht"][$result_inhalt["geschlecht"]] = true;
			if($result_inhalt["karierrestatus"] != 0)
				$suchinhalte["karierrestatus"][$result_inhalt["karierrestatus"]] = true;
			if($result_inhalt["profilquali"] != 0)
				$suchinhalte["profilquali"][$result_inhalt["profilquali"]] = true;
			if($result_inhalt["lastlogin"] != 0)
				$suchinhalte["lastlogin"][$result_inhalt["lastlogin"]] = true;
			if($result_inhalt["land"] != 0)
				$suchinhalte["land"][$result_inhalt["land"]] = true;
			if($result_inhalt["bundesland"] != 0)
				$suchinhalte["bundesland"][$result_inhalt["bundesland"]] = true;
			if($result_inhalt["grossraum"] != 0)
				$suchinhalte["grossraum"][$result_inhalt["grossraum"]] = true;
			if($result_inhalt["kandidatenalter"] != 0)
				$suchinhalte["kandidatenalter"][$result_inhalt["kandidatenalter"]] = true;
			if($result_inhalt["karierrestatus"] != 0)
				$suchinhalte["karierrestatus"][$result_inhalt["karierrestatus"]] = true;
			if($result_inhalt["studiengang"] != 0)
				$suchinhalte["studiengang"][$result_inhalt["studiengang"]] = true;
			if($result_inhalt["gesuch_sprachkenntnisse1"] != 0)
				$suchinhalte["gesuch_sprachkenntnisse"][$result_inhalt["gesuch_sprachkenntnisse1"]] = true;
			if($result_inhalt["gesuch_sprachkenntnisse2"] != 0)
				$suchinhalte["gesuch_sprachkenntnisse"][$result_inhalt["gesuch_sprachkenntnisse2"]] = true;
			if($result_inhalt["gesuch_sprachkenntnisse3"] != 0)
				$suchinhalte["gesuch_sprachkenntnisse"][$result_inhalt["gesuch_sprachkenntnisse3"]] = true;
			$i++;
		}

		unset($s_ergebnisarry["sucheingabe"]);

		$s_ergebnisarry["sucheingabe"]["userid"] = array_keys( $suchinhalte["userid"] );
		if ( is_array( $suchinhalte["bereiche"] ) )
			$s_ergebnisarry["sucheingabe"]["bereiche"] = array_keys( $suchinhalte["bereiche"] );
		if ( is_array( $suchinhalte["einsatzgebiet"] ) )
			$s_ergebnisarry["sucheingabe"]["einsatzgebiet"] = array_keys( $suchinhalte["einsatzgebiet"] );
		if ( is_array( $suchinhalte["geschlecht"] ) )
			$s_ergebnisarry["sucheingabe"]["geschlecht"] = array_keys( $suchinhalte["geschlecht"] );
		if ( is_array( $suchinhalte["karierrestatus"] ) )
			$s_ergebnisarry["sucheingabe"]["karierrestatus"] = array_keys( $suchinhalte["karierrestatus"] );
		if ( is_array( $suchinhalte["profilquali"] ) )
			$s_ergebnisarry["sucheingabe"]["profilqualitaet"] = array_keys( $suchinhalte["profilquali"] );
		if ( is_array( $suchinhalte["lastlogin"] ) )
			$s_ergebnisarry["sucheingabe"]["lastlogin"] = array_keys( $suchinhalte["lastlogin"] );
		if ( is_array( $suchinhalte["land"] ) )
			$s_ergebnisarry["sucheingabe"]["land"] = array_keys( $suchinhalte["land"] );
		if ( is_array( $suchinhalte["bundesland"] ) )
			$s_ergebnisarry["sucheingabe"]["bundesland"] = array_keys( $suchinhalte["bundesland"] );
		if ( is_array( $suchinhalte["grossraum"] ) )
			$s_ergebnisarry["sucheingabe"]["grossraum"] = array_keys( $suchinhalte["grossraum"] );
		if ( is_array( $suchinhalte["kandidatenalter"] ) )
			$s_ergebnisarry["sucheingabe"]["kandidatenalter"] = array_keys( $suchinhalte["kandidatenalter"] );
		if ( is_array( $suchinhalte["karierrestatus"] ) )
			$s_ergebnisarry["sucheingabe"]["karierrestatus"] = array_keys( $suchinhalte["karierrestatus"] );
		if ( is_array( $suchinhalte["studiengang"] ) )
			$s_ergebnisarry["sucheingabe"]["studiengang"] = array_keys( $suchinhalte["studiengang"] );
		if ( is_array( $suchinhalte["gesuch_sprachkenntnisse"] ) )
			$s_ergebnisarry["sucheingabe"]["praktikumssprache"] = array_keys( $suchinhalte["gesuch_sprachkenntnisse"] );


		if (is_array($s_ergebnisarry["sucheingabe"]["kandidatenalter"])) {
			foreach ($s_ergebnisarry["sucheingabe"]["kandidatenalter"] as $altervalues) {
				if ($altervalues >= 15 && $altervalues <= 18) {
					$s_ergebnisarry["sucheingabe"]["alter"][] = "15-18";
				} elseif ($altervalues >= 19 && $altervalues <= 23) {
					$s_ergebnisarry["sucheingabe"]["alter"][] = "19-23";
				} elseif ($altervalues >= 24 && $altervalues <= 28) {
					$s_ergebnisarry["sucheingabe"]["alter"][] = "24-28";
				} elseif ($altervalues >= 29 && $altervalues <= 35) {
					$s_ergebnisarry["sucheingabe"]["alter"][] = "29-35";
				} elseif ($altervalues >= 36 && $altervalues <= 65) {
					$s_ergebnisarry["sucheingabe"]["alter"][] = "36-65";
				}
			}
			unset($s_ergebnisarry["sucheingabe"]["kandidatenalter"]);
		}

		if (is_array($s_ergebnisarry["sucheingabe"]["lastlogin"])) {
			foreach ($s_ergebnisarry["sucheingabe"]["lastlogin"] as $lastloginvalues) {
				if ($lastloginvalues >= ($aktdate - 604800)) {
					$s_ergebnisarry["sucheingabe"]["suchlastlogin"][] = ($aktdate - 604800);
				} elseif ($lastloginvalues <= ($aktdate - 604800) && $lastloginvalues >= ($aktdate - 1209600)) {
					$s_ergebnisarry["sucheingabe"]["suchlastlogin"][] = ($aktdate - 1209600);
				} elseif ($lastloginvalues <= ($aktdate - 1209600) && $lastloginvalues >= ($aktdate - 2592000)) {
					$s_ergebnisarry["sucheingabe"]["suchlastlogin"][] = ($aktdate - 2592000);
				} elseif ($lastloginvalues <= ($aktdate - 2592000) && $lastloginvalues >= ($aktdate - 5184000)) {
					$s_ergebnisarry["sucheingabe"]["suchlastlogin"][] = ($aktdate - 5184000);
				} elseif ($lastloginvalues <= ($aktdate - 5184000) && $lastloginvalues >= ($aktdate - 7776000)) {
					$s_ergebnisarry["sucheingabe"]["suchlastlogin"][] = ($aktdate - 7776000);
				} elseif ($lastloginvalues <= ($aktdate - 7776000) && $lastloginvalues >= ($aktdate - 15552000)) {
					$s_ergebnisarry["sucheingabe"]["suchlastlogin"][] = ($aktdate - 15552000);
				}
			}
			unset($s_ergebnisarry["sucheingabe"]["lastlogin"]);
		}


		//print_pr($s_ergebnisarry[warenkorb]);
		//print_pr($s_ergebnisarry);
		$nl = "\n";

		if (!$wk_inhalt) {
			$reuckgabe .=  $nl;
			$reuckgabe .=  '  <form action=""><table width="100%" border="0">'.$nl;
			$reuckgabe .=  '    <tr bgcolor="#F7E7CE">'.$nl;
			$reuckgabe .=  '      <td colspan="2"><table width="100%" border="0">'.$nl;
			$reuckgabe .=  '      <tr><td colspan="2"><b>Suchergebnis verfeinern</b></td>'.$nl;
			$reuckgabe .=  "	  </table></td>\n";
			$reuckgabe .=  "  </tr>\n";
			$reuckgabe .=  "  <tr>\n";
			$reuckgabe .=  "    <td>\n";
			$reuckgabe .=  '      <select name="land" id="landselect" class="eingabe210">'."\n";
			$reuckgabe .=  "        <option value=\"\">- Land -</option>\n";
			if (is_array($s_ergebnisarry["sucheingabe"]["land"])) {
				$reuckgabe .=  "<optgroup label=\"Deutschsprachig\">";
				$row[0] = 68; $row[1]="Deutschland";
				if (in_array($row[0],$s_ergebnisarry["sucheingabe"]["land"])) {
					$reuckgabe .=  "        <option value=\"".$row[0]."\"";
					if (count($s_ergebnisarry[sucheingabe][land]) == 1 && in_array($row[0],$s_ergebnisarry["sucheingabe"]["land"])) $reuckgabe .=  " selected";
					$reuckgabe .=  ">".$row[1]."</option>";
				}
				$row[0] = 14; $row[1]="Österreich";
				if (in_array($row[0],$s_ergebnisarry["sucheingabe"]["land"])) {
					$reuckgabe .=  "        <option value=\"".$row[0]."\"";
					if (count($s_ergebnisarry[sucheingabe][land]) == 1 && in_array($row[0],$s_ergebnisarry["sucheingabe"]["land"])) $reuckgabe .=  " selected";
					$reuckgabe .=  ">".$row[1]."</option>";
				}
				$row[0] = 156; $row[1]="Schweiz";
				if (in_array($row[0],$s_ergebnisarry["sucheingabe"]["land"])) {
					$reuckgabe .=  "        <option value=\"".$row[0]."\"";
					if (count($s_ergebnisarry[sucheingabe][land]) == 1 && in_array($row[0],$s_ergebnisarry["sucheingabe"]["land"])) $reuckgabe .=  " selected";
					$reuckgabe .=  ">".$row[1]."</option>";
				}
				$reuckgabe .=  "</optgroup>";
				$reuckgabe .=  "<optgroup label=\"andere\">";
				$laender=mysql($database_db,"SELECT id,$s_sprache from land WHERE sprachid != 2 AND id IN (".implode(",",$s_ergebnisarry["sucheingabe"]["land"]).") ORDER BY $s_sprache",$praktdbslave);
				while ($row = mysql_fetch_array($laender, MYSQL_NUM)) {
					$reuckgabe .=  "        <option value=\"".$row[0]."\"";
					if (count($s_ergebnisarry[sucheingabe][land]) == 1 && in_array($row[0],$s_ergebnisarry["sucheingabe"]["land"])) $reuckgabe .=  " selected";
					$reuckgabe .=  ">".$row[1]."</option>";
				}
				$reuckgabe .=  "</optgroup>";
			}
			$reuckgabe .=  "      </select>\n";
			$reuckgabe .=  "    </td>\n";
			$reuckgabe .=  "    <td align=\"right\">\n";
			$reuckgabe .=  '      <select name="sprache" id="spracheselect" class="eingabe210">'."\n";
			$reuckgabe .=  "        <option value=\"\">- Sprachkenntnisse -</option>\n";
			if (is_array($s_ergebnisarry["sucheingabe"]["praktikumssprache"])) {
				$sprachen=mysql($database_db,"select id,$s_sprache from sprachen WHERE id IN (".implode(",",$s_ergebnisarry["sucheingabe"]["praktikumssprache"]).") ORDER BY id",$praktdbslave);
				while ($row = mysql_fetch_array($sprachen, MYSQL_NUM)) {
					$reuckgabe .=  "        <option value=\"".$row[0]."\"";
					if (count($s_ergebnisarry[sucheingabe][praktikumssprache]) == 1 && in_array($row[0],$s_ergebnisarry[sucheingabe][praktikumssprache])) $reuckgabe .=  " selected";
					$reuckgabe .=  ">".$row[1]."</option>";
				}
			}
			$reuckgabe .=  "      </select>\n";
			$reuckgabe .=  "    </td>\n";

			$reuckgabe .=  "  </tr>\n";

			$reuckgabe .=  "  <tr>\n";
			$reuckgabe .=  "    <td>\n";
			$reuckgabe .=  '      <select name="bundesland" id="bundeslandselect" class="eingabe210">'."\n";
			$reuckgabe .=  "        <option value=\"\">- Bundesland -</option>\n";
			if (is_array($s_ergebnisarry["sucheingabe"]["bundesland"])) {
				$rs=mysql($database_db,"select id,landid, $s_sprache from bundesland WHERE id IN (".implode(",",$s_ergebnisarry["sucheingabe"]["bundesland"]).") ORDER BY $s_sprache",$praktdbslave);
				while ($result_row = mysql_fetch_array ( $rs, MYSQL_ASSOC )) {
					$blandarray[$result_row["landid"]][] = $result_row;
					$land_ids[] = $result_row["landid"];
				}
				$landresult=mysql($database_db,"select id,$s_sprache from land where id IN (".implode(",",$land_ids).") order by $s_sprache",$praktdbslave);
				while ($result_land = mysql_fetch_array ( $landresult, MYSQL_ASSOC )) {
					$reuckgabe .=  "<optgroup label=\"".$result_land[$s_sprache]."\">";
					foreach ($blandarray[$result_land["id"]] as $row) {
						$reuckgabe .=  "        <option value=\"".$row["id"]."\"";
						if (count($s_ergebnisarry[sucheingabe][bundesland]) == 1 && in_array($row["id"],$s_ergebnisarry["sucheingabe"]["bundesland"])) $reuckgabe .=  " selected";
						$reuckgabe .=  ">".$row[$s_sprache]."</option>";
					}
					$reuckgabe .=  "</optgroup>";
				}
			}
			$reuckgabe .=  "      </select>\n";
			$reuckgabe .=  "    </td>\n";
			$reuckgabe .=  "    <td align=\"right\">\n";
			$reuckgabe .=  '      <select name="alter" id="alterselect" class="eingabe210">'."\n";
			$reuckgabe .=  "        <option value=\"\">- Altersgruppe -</option>\n";
			if (is_array($s_ergebnisarry["sucheingabe"]["alter"])) {
				$s_ergebnisarry["sucheingabe"]["alter"] = array_unique($s_ergebnisarry["sucheingabe"]["alter"]);
				if (count($s_ergebnisarry["sucheingabe"]["alter"]) > 0 && in_array("15-18",$s_ergebnisarry["sucheingabe"]["alter"])) {
					$reuckgabe .=  "        <option value=\"15-18\"";
					if (count($s_ergebnisarry["sucheingabe"]["alter"]) == 1 && in_array("15-18",$s_ergebnisarry["sucheingabe"]["alter"])) $reuckgabe .=  " selected";
					$reuckgabe .=  ">15-18 Jahre</option>";
				}
				if (count($s_ergebnisarry["sucheingabe"]["alter"]) > 0 && in_array("19-23",$s_ergebnisarry["sucheingabe"]["alter"])) {
					$reuckgabe .=  "        <option value=\"19-23\"";
					if (count($s_ergebnisarry["sucheingabe"]["alter"]) == 1 && in_array("19-23",$s_ergebnisarry["sucheingabe"]["alter"])) $reuckgabe .=  " selected";
					$reuckgabe .=  ">19-23 Jahre</option>";
				}
				if (count($s_ergebnisarry["sucheingabe"]["alter"]) > 0 && in_array("24-28",$s_ergebnisarry["sucheingabe"]["alter"])) {
					$reuckgabe .=  "        <option value=\"24-28\"";
					if (count($s_ergebnisarry["sucheingabe"]["alter"]) == 1 && in_array("24-28",$s_ergebnisarry["sucheingabe"]["alter"])) $reuckgabe .=  " selected";
					$reuckgabe .=  ">24-28 Jahre</option>";
				}
				if (count($s_ergebnisarry["sucheingabe"]["alter"]) > 0 && in_array("29-35",$s_ergebnisarry["sucheingabe"]["alter"])) {
					$reuckgabe .=  "        <option value=\"29-35\"";
					if (count($s_ergebnisarry["sucheingabe"]["alter"]) == 1 && in_array("29-35",$s_ergebnisarry["sucheingabe"]["alter"])) $reuckgabe .=  " selected";
					$reuckgabe .=  ">29-35 Jahre</option>";
				}
				if (count($s_ergebnisarry["sucheingabe"]["alter"]) > 0 && in_array("36-65",$s_ergebnisarry["sucheingabe"]["alter"])) {
					$reuckgabe .=  "        <option value=\"36-65\"";
					if (count($s_ergebnisarry["sucheingabe"]["alter"]) == 1 && in_array("36-65",$s_ergebnisarry["sucheingabe"]["alter"])) $reuckgabe .=  " selected";
					$reuckgabe .=  ">&auml;lter als 35 Jahre</option>";
				}
			}
			$reuckgabe .=  "      </select>\n";
			$reuckgabe .=  "    </td>\n";
			$reuckgabe .=  "  </tr>\n";
			$reuckgabe .=  "  <tr>\n";
			$reuckgabe .=  "    <td>\n";
			$reuckgabe .=  '      <select name="grossraum" id="grossraumselect" class="eingabe210">'."\n";
			$reuckgabe .=  "        <option value=\"\">- Gro&szlig;raum -</option>\n";
			if (is_array($s_ergebnisarry["sucheingabe"]["grossraum"])) {

				$grossraum=mysql($database_db,"select id,grossraum from grossraum WHERE  id IN (".implode(",",$s_ergebnisarry["sucheingabe"]["grossraum"]).") ORDER BY grossraum",$praktdbslave);
				while ($row = mysql_fetch_array($grossraum, MYSQL_NUM)) {
					$reuckgabe .=  "        <option value=\"".$row[0]."\"";
					if (count($s_ergebnisarry[sucheingabe][grossraum]) == 1 && in_array($row[0],$s_ergebnisarry[sucheingabe][grossraum])) $reuckgabe .=  " selected";
					$reuckgabe .=  ">".$row[1]."</option>";
				}
			}
			$reuckgabe .=  "      </select>\n";
			$reuckgabe .=  "    </td>\n";
			$reuckgabe .=  "    <td align=\"right\">\n";
			$reuckgabe .=  '      <select name="geschlecht" id="geschlechtselect" class="eingabe210">'."\n";
			$reuckgabe .=  "        <option value=\"\">- Geschlecht -</option>\n";
			$reuckgabe .=  "        <option value=\"m\"";
			if (count($s_ergebnisarry["sucheingabe"]["geschlecht"]) == 1 && in_array("m",$s_ergebnisarry["sucheingabe"]["geschlecht"])) $reuckgabe .=  " selected";
			$reuckgabe .=  ">männlich</option>";
			$reuckgabe .=  "        <option value=\"w\"";
			if (count($s_ergebnisarry["sucheingabe"]["geschlecht"]) == 1 && in_array("w",$s_ergebnisarry["sucheingabe"]["geschlecht"])) $reuckgabe .=  " selected";
			$reuckgabe .=  ">weiblich</option>";
			$reuckgabe .=  "      </select>";
			$reuckgabe .=  "    </td>\n";
			$reuckgabe .=  "  </tr>\n";
			$reuckgabe .=  "  <tr>\n";
			$reuckgabe .=  "    <td>\n";
			$reuckgabe .=  "      <select name=\"plz\" id=\"plzselect\" class=\"eingabe210\">\n";
			$reuckgabe .=  "        <option value=\"\">- PLZ-Zone -</option>\n";
			$i=0;
			while ($i < 10) {
				$reuckgabe .=  "        <option value=\"".$i."\"";
				if (count($s_ergebnisarry[sucheingabe][grossraum]) == 1 && in_array($row[0],$s_ergebnisarry[sucheingabe][grossraum])) $reuckgabe .=  " selected";
				$reuckgabe .=  ">PLZ Zone: ".$i."</option>";
				$i++;
			}
			$reuckgabe .=  "      </select>\n";
			$reuckgabe .=  "    </td>\n";
			$reuckgabe .=  "    <td align=\"right\">\n";
			$reuckgabe .=  '      <select name="karierrestatus" id="karierrestatusselect" class="eingabe210">'."\n";
			$reuckgabe .=  "        <option value=\"\">- Karierrestatus -</option>\n";
			if (is_array($s_ergebnisarry["sucheingabe"]["karierrestatus"])) {
				$karierrestatus=mysql($database_db,"select id,$s_sprache from karierrestatus WHERE id IN (".implode(",",$s_ergebnisarry["sucheingabe"]["karierrestatus"]).") ORDER BY anzeigeorder",$praktdbslave);
				while ($row = mysql_fetch_array($karierrestatus, MYSQL_NUM)) {
					$reuckgabe .=  "<option value=\"".$row[0]."\"";
					if (count($s_ergebnisarry[sucheingabe][karierrestatus]) == 1 && in_array($row[0],$s_ergebnisarry[sucheingabe][karierrestatus])) $reuckgabe .=  " selected";
					$reuckgabe .=  ">".$row[1]."</option>";
				}
			}
			$reuckgabe .=  "      </select>";
			$reuckgabe .=  "    </td>\n";
			$reuckgabe .=  "  </tr>\n";
			$reuckgabe .=  "  <tr>\n";
			$reuckgabe .=  "    <td>\n";
			$reuckgabe .=  '      <select name="berufsfeld" id="berufsfeldselect" class="eingabe210">'."\n";
			$reuckgabe .=  "        <option value=\"\">- Berufsfeld -</option>\n";
			if (is_array($s_ergebnisarry["sucheingabe"]["einsatzgebiet"])) {

				$berufsfelder=mysql($database_db,"select id,$s_sprache from berufsfelder WHERE id IN (".implode(",",$s_ergebnisarry["sucheingabe"]["einsatzgebiet"]).") ORDER BY $s_sprache",$praktdbslave);
				while ($row = mysql_fetch_array($berufsfelder, MYSQL_NUM)) {
					$reuckgabe .=  "<option value=\"".$row[0]."\"";
					if (count($s_ergebnisarry[sucheingabe][einsatzgebiet]) == 1 && in_array($row[0],$s_ergebnisarry[sucheingabe][einsatzgebiet])) $reuckgabe .=  " selected";
					$reuckgabe .=  ">".$row[1]."</option>";
				}
			}
			$reuckgabe .=  "      </select>\n";
			$reuckgabe .=  "    </td>\n";
			$reuckgabe .=  "    <td align=\"right\">\n";
			$reuckgabe .=  '      <select name="spalte" id="spalteselect" class="eingabe210">'."\n";
			$reuckgabe .=  "        <option value=\"\">- Art des Gesuches -</option>\n";
			if (is_array($s_ergebnisarry["sucheingabe"]["bereiche"])) {

				foreach ($spaltenarray as $spaltenkey => $spaltenvalue) {
					if (in_array($spaltenkey,$s_ergebnisarry["sucheingabe"]["bereiche"])) {
						$reuckgabe .=  "        <option value=\"".$spaltenkey."\"";
						if (count($s_ergebnisarry["sucheingabe"]["bereiche"]) == 1 && in_array($spaltenkey,$s_ergebnisarry["sucheingabe"]["bereiche"])) $reuckgabe .=  " selected";
						$reuckgabe .=  ">".$spaltenvalue."</option>\n";
					}
				}
			}
			$reuckgabe .=  "      </select>\n";
			$reuckgabe .=  "    </td>\n";
			$reuckgabe .=  "  </tr>\n";

			$reuckgabe .=  "  <tr>\n";
			$reuckgabe .=  "    <td>\n";
			$reuckgabe .=  '      <select name="studiengang" id="studiengangselect" class="eingabe210">'."\n";
			$reuckgabe .=  "        <option value=\"\">- Studiengang -</option>\n";
			if (is_array($s_ergebnisarry["sucheingabe"]["studiengang"])) {

				$studiengaenge=mysql($database_db,"select id,studiengang from studiengaenge WHERE id IN (".implode(",",$s_ergebnisarry["sucheingabe"]["studiengang"]).") ORDER BY studiengang",$praktdbslave);
				while ($row = mysql_fetch_array($studiengaenge, MYSQL_NUM)) {
					$reuckgabe .=  "<option value=\"".$row[0]."\"";
					if (count($s_ergebnisarry[sucheingabe][studiengang]) == 1 && in_array($row[0],$s_ergebnisarry["sucheingabe"]["studiengang"])) $reuckgabe .=  " selected";
					$reuckgabe .=  ">".$row[1]."</option>";
				}
			}
			$reuckgabe .=  "      </select>\n";
			$reuckgabe .=  "    </td>\n";
			$reuckgabe .=  "    <td align=\"right\">\n";
			$reuckgabe .=  '      <select name="profilqualitaet" id="profilqualitaetselect" class="eingabe210">'."\n";
			$reuckgabe .=  "        <option value=\"\">- Profilqualit&auml;t -</option>\n";
			if (is_array($s_ergebnisarry["sucheingabe"]["profilqualitaet"])) {
				sort($s_ergebnisarry["sucheingabe"]["profilqualitaet"]);
				foreach ($s_ergebnisarry["sucheingabe"]["profilqualitaet"] as $quali) {
					$reuckgabe .=  "<option value=\"".$quali."\"";
					if (count($s_ergebnisarry["sucheingabe"]["profilqualitaet"]) == 1 && in_array($quali,$s_ergebnisarry["sucheingabe"]["profilqualitaet"])) $reuckgabe .=  " selected";
					$reuckgabe .=  ">Profilqualit&auml;t gr&ouml;&szlig;er gleich ".$quali." Punkte</option>";
				}
			}
			$reuckgabe .=  "      </select>";
			$reuckgabe .=  "    </td>\n";
			$reuckgabe .=  "  </tr>\n";

			$reuckgabe .=  "  <tr>\n";
			$reuckgabe .=  "    <td>\n";
			$reuckgabe .=  '      <select name="suchlastlogin" id="suchlastloginselect" class="eingabe210">'."\n";
			$reuckgabe .=  "        <option value=\"\">- letztes Login -</option>\n";
			if (is_array($s_ergebnisarry["sucheingabe"]["suchlastlogin"])) {
				$s_ergebnisarry["sucheingabe"]["suchlastlogin"] = array_unique($s_ergebnisarry["sucheingabe"]["suchlastlogin"]);
				if (in_array(($aktdate - 604800),$s_ergebnisarry["sucheingabe"]["suchlastlogin"])) {
					$reuckgabe .=  "        <option value=\"".($aktdate - 604800)."\"";
					if (count($s_ergebnisarry["sucheingabe"]["suchlastlogin"]) == 1 && in_array(($aktdate - 604800),$s_ergebnisarry["sucheingabe"]["suchlastlogin"])) $reuckgabe .=  " selected";
					$reuckgabe .=  ">max. 7 Tage</option>";
				}
				if (in_array(($aktdate - 1209600),$s_ergebnisarry["sucheingabe"]["suchlastlogin"])) {
					$reuckgabe .=  "        <option value=\"".($aktdate - 1209600)."\"";
					if (count($s_ergebnisarry["sucheingabe"]["suchlastlogin"]) == 1 && in_array(($aktdate - 1209600),$s_ergebnisarry["sucheingabe"]["suchlastlogin"])) $reuckgabe .=  " selected";
					$reuckgabe .=  ">max. 14 Tage</option>";
				}
				if (in_array(($aktdate - 2592000),$s_ergebnisarry["sucheingabe"]["suchlastlogin"])) {
					$reuckgabe .=  "        <option value=\"".($aktdate - 2592000)."\"";
					if (count($s_ergebnisarry["sucheingabe"]["suchlastlogin"]) == 1 && in_array(($aktdate - 2592000),$s_ergebnisarry["sucheingabe"]["suchlastlogin"])) $reuckgabe .=  " selected";
					$reuckgabe .=  ">max. 1 Monat</option>";
				}
				if (in_array(($aktdate - 5184000),$s_ergebnisarry["sucheingabe"]["suchlastlogin"])) {
					$reuckgabe .=  "        <option value=\"".($aktdate - 5184000)."\"";
					if (count($s_ergebnisarry["sucheingabe"]["suchlastlogin"]) == 1 && in_array(($aktdate - 5184000),$s_ergebnisarry["sucheingabe"]["suchlastlogin"])) $reuckgabe .=  " selected";
					$reuckgabe .=  ">max. 2 Monate</option>";
				}
				if (in_array(($aktdate - 7776000),$s_ergebnisarry["sucheingabe"]["suchlastlogin"])) {
					$reuckgabe .=  "        <option value=\"".($aktdate - 7776000)."\"";
					if (count($s_ergebnisarry["sucheingabe"]["suchlastlogin"]) == 1 && in_array(($aktdate - 7776000),$s_ergebnisarry["sucheingabe"]["suchlastlogin"])) $reuckgabe .=  " selected";
					$reuckgabe .=  ">max. 3 Monate</option>";
				}
				if (in_array(($aktdate - 15552000),$s_ergebnisarry["sucheingabe"]["suchlastlogin"])) {
					$reuckgabe .=  "        <option value=\"".($aktdate - 15552000)."\"";
					if (count($s_ergebnisarry["sucheingabe"]["suchlastlogin"]) == 1 && in_array(($aktdate - 15552000),$s_ergebnisarry["sucheingabe"]["suchlastlogin"])) $reuckgabe .=  " selected";
					$reuckgabe .=  ">max. 6 Monate</option>";
				}
			}
			$reuckgabe .=  "      </select>";
			$reuckgabe .=  "    </td>\n";
			$reuckgabe .=  "    <td align=\"right\">\n";
			$reuckgabe .=  '      <input type="button" name="finesearchsubmit" value="Suche verfeinern" onclick="FineSearchSubmit()">'."\n";
			$reuckgabe .=  "    </td>\n";
			$reuckgabe .=  "  </tr>\n";
			$reuckgabe .=  "  <tr bgcolor=\"#F7E7CE\">\n";
			$reuckgabe .=  '    <td colspan="2" height="2"></td>'."\n";
			$reuckgabe .=  "  </tr>\n";


			$reuckgabe .=  '<tr><td><img src=/gifs/zentralelemente/spacer.gif width="10" height="10"></td></tr>';
			if (count($s_firmenproparray["kandidatensuche"]["warenkorb"]) > 0) {
				$wanzahl=count($s_firmenproparray["kandidatensuche"]["warenkorb"]);
				$reuckgabe .=  "<tr>\n";
				$reuckgabe .=  "<td colspan=\"2\"><span class=normal><a href=\"wk.phtml\">Weiter zu meinen $wanzahl Kandidaten auf der Kandidatenliste</a></span></td>\n";
				$reuckgabe .=  "</tr>";
			}
			$reuckgabe .=  "<tr>\n";
			$reuckgabe .=  "<td colspan=\"2\"><span class=normal><a href=\"wk.phtml?gesamt=true&dest=mailing\">".count($s_ergebnisarry["sucheingabe"]["userid"])." gefundene Kandidaten auf der Kandidatenliste notieren</a></span></td>\n";
			$reuckgabe .=  "</tr>";

			$reuckgabe .=  "</table>\n";
			$reuckgabe .=  "</form>\n";
		}
		$ajaxtype = "results";
	} else {
		$ajaxtype = "warenkorbanzeige";
	}

	// karierrestatus array bauen //
	$karierrestatus = array();
	$karierrestatusresult=mysql($database_db,"select id,german from karierrestatus",$praktdbslave);
	while ($result_row = mysql_fetch_array ( $karierrestatusresult, MYSQL_NUM )) {
		$karierrestatus[$result_row[0]]=$result_row[1];
	}

	// ++++++++++++++++ erste seite? +++++++++++++++++ //
	if ( ! $ds_count ) $ds_count = 0;
	if ( ! $ds_start ) $ds_start = $ds_count;

	// datensaetze pro seite ueberpruefen und setzen //
	if ( ! ( ($ds_pro_seite == 5) || ($ds_pro_seite == 10) || ($ds_pro_seite == 20) ) ) $ds_pro_seite = 10; //


	$reuckgabe .=  "Kandidaten " . ($ds_count + 1) . " - " . min (($ds_count + $ds_pro_seite ), ($num_rows)) . " von " . $num_rows . "<br>\n";

	$reuckgabe .=  '<table border="0" cellspacing="0" cellpadding="2" width="100%">'."\n";
	$reuckgabe .=  '<tr><td align="right" colspan="2">'.seitenausgabe2($num_rows,$ds_pro_seite,$ds_count,$ajaxtype).'</td></tr>'."\n";

	// Datensaetze anzeigen

	mysql_data_seek( $results, $ds_count );

	$end_ds = $ds_count + $ds_pro_seite;
	while ( ($ds_count < $end_ds) && ($result_row = mysql_fetch_array ( $results )) ) {

		if ($result_row["taetigkeit"]) {
			if (strlen($result_row["taetigkeit"]) > 55) {$headline = substr($result_row["taetigkeit"],0,52) . "...";}  else {$headline = $result_row["taetigkeit"];}
		} else { $headline = "Chiffre #".$result_row["nutzerid"]; }
		$reuckgabe .=  "<tr><td colspan=\"2\"><br></td></tr>\n";
		$reuckgabe .=  "<tr><td colspan=\"2\"><fieldset style=\"margin:0; border:0; border-top:1px solid #F7E7CE\"><legend>&nbsp;<i><strong>" . ($ds_count + 1) . ".&nbsp;" . $headline . "</strong></i>&nbsp;</legend></fieldset></td></tr>\n";

		// Bild heraussuchen
		$fotoschonda = mysql($database_db,"select nutzerid,id from bewerbungsfoto where nutzerid='".$result_row[nutzerid]."'",$praktdbslave);
		if (mysql_NumRows($fotoschonda) > 0) {
			$photoid = mysql_result($fotoschonda,0,"id");
			$photo="<img src=\"/community/passbild.php?id=".$photoid."\" width=\"120\" alt=\"Bewerbungsfoto\" align=\"right\">";
		} else {
			$photo="";
		}


		$reuckgabe .=  "<tr valign=\"top\"><td>";

		// Innere Tabelle
		$reuckgabe .=  "<table cellspacing=\"2\" cellpadding=\"2\" border=\"0\" valign=\"top\" align=\"left\">";

		$reuckgabe .=  "<tr><td valign=\"top\">Alter:</td>\n";
		$reuckgabe .=  "<td valign=\"top\">" . $result_row["kandidatenalter"] . " Jahre</td>\n";
		$reuckgabe .=  "</tr>";

		// Geschlecht
		if ( $result_row["geschlecht"] == "m") {$geschlecht = "m&auml;nnlich";} else {$geschlecht = "weiblich";}
		$reuckgabe .=  "<tr><td valign=\"top\">Geschlecht:</td>\n";
		$reuckgabe .=  "<td valign=\"top\">" . $geschlecht . "</td></tr>\n";

		// Karierrestatus

		$reuckgabe .=  "<tr><td valign=\"top\">Karierrestatus:</td>\n";
		$reuckgabe .=  "<td valign=\"top\">" . $karierrestatus[$result_row["karierrestatus"]] . "</td></tr>\n";

		// Raum
		$reuckgabe .=  "<tr><td valign=\"top\">Raum:</td>\n";
		if ($result_row["land"] > 0) {
			$landid=$result_row["land"];
			$land=mysql($database_db,"select german from land where id=$landid",$praktdbslave);
			if ($result_row["bundesland"] > 0) {
				$bundeslandid=$result_row["bundesland"];
				$bland=mysql($database_db,"select $s_sprache from bundesland where id=$bundeslandid",$praktdbslave);
				$reuckgabe .=  "<td valign=\"top\">" . mysql_result($bland,0,$s_sprache) . ",&nbsp;" . mysql_result($land,0,$s_sprache) . "</td></tr>\n";
			} else {
				$reuckgabe .=  "<td valign=\"top\">" . mysql_result($land,0,$s_sprache) . "</td></tr>\n";
			}
		}

		// Ort
		$reuckgabe .=  "<tr><td valign=\"top\">Ort:</td>\n";
		$reuckgabe .=  "<td valign=\"top\">" . $result_row["ort"] . "</td></tr>\n";

		if ($result_row["studiengang"] > 0) {
			// Studiengang
			$studiengangid=$result_row["studiengang"];
			$studiengang=mysql($database_db,"select studiengang from studiengaenge where id=$studiengangid",$praktdbslave);
			$reuckgabe .=  "<tr><td valign=\"top\">Studium:</td>\n";
			$reuckgabe .=  '<td valign="top">' . mysql_result($studiengang,0,"studiengang") . "</td></tr>\n";
		}
		// Aktion

		$reuckgabe .=  "<tr><td width=20% valign=\"top\">Aktion:</td>\n";
		$reuckgabe .=  '<td valign="top">';
		$reuckgabe .=  "<span class=normal><a href=\"javascript:Load_SearchData('detail','','".$ajaxtype."','".$result_row["nutzerid"]."','".$ds_start."')\">Kandidatendetails</a></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$reuckgabe .=  "</td></tr>\n";

		// Innere Tabelle
		$reuckgabe .=  "</table></td>";
		$reuckgabe .=  "<td align=\"right\">";
		if (!$s_firmenproparray["kandidatensuche"]["warenkorb"][$result_row["nutzerid"]]) {
			$reuckgabe .=  "<span align=\"right\"><a href=\"javascript:WarenkorbSubmit('".$result_row["nutzerid"]."','".$ajaxtype."','".$ds_start."');\">Kandidat ausw&auml;hlen</a><br><br></span>";
		} else {
			$reuckgabe .=  "<span align=\"right\"><a href=\"javascript:WarenkorbSubmit('".$result_row["nutzerid"]."','".$ajaxtype."','".$ds_start."');\">Kandidat entfernen<br><br></a></span>";
		}

		$reuckgabe .=  $photo."</td></tr>";

		$reuckgabe .=  '<tr><td colspan="2" valign="top"><hr style="height: 1px; color: #F7E7CE; background-color: #F7E7CE; border: none;"></td></tr>'."\n";
		$reuckgabe .=  '<tr><td colspan="2" valign="top"><br></td></tr>'."\n";


		$ds_count++;
	}

	$reuckgabe .=  '<tr><td colspan="2" valign="top">';
	$reuckgabe .=  "<table cellspacing=0 cellpadding=0 width=100%><tr><td align=\"left\" width=\"33%\">";
	if ($ds_start > 9)
	$reuckgabe .=  "<input type=\"button\" value=\"&lt;&lt;\" onclick=\"Load_SearchData('".$ajaxtype."',document.getElementById('sortierung').value,document.getElementById('ksuchstring').value,document.getElementById('categoryids').value,'".($ds_start - 10)."');\">";
	$reuckgabe .=  "</td><td align=\"center\" width=\"33%\">\n";
	if ($fineselect) {
		if ($_REQUEST["sortierung"] != "" || $_REQUEST["sortierung"] != "keine") {
			$reuckgabe .=  "<a href=\"javascript:Load_SearchData('categories',document.getElementById('sortierung').value,document.getElementById('ksuchstring').value)\">Sortierung</a>";
		} else {
			$reuckgabe .=  '<a href="/home/firmen/einblick.phtml">zur Eingabemaske</a>';
		}
	}else {
		$reuckgabe .=  '<a href="/home/firmen/suchen/wk.phtml">zur Kandidatenliste</a>';
	}
	$reuckgabe .=  '</td><td align="right" width="33%">'."\n";
	if (($ds_start + 10) < $num_rows)
	$reuckgabe .=  '<input type="button" value="&gt;&gt;" onclick="Load_SearchData("'.$ajaxtype.'",document.getElementById("sortierung").value,document.getElementById("ksuchstring").value,document.getElementById("categoryids").value,"'.($ds_start + 10).'");">';
	$reuckgabe .=  "</td></tr>\n";
	$reuckgabe .=  "</table>\n";
	$reuckgabe .=  "</td></tr>\n";

	$reuckgabe .=  "</table>\n";

	return $reuckgabe;
}



// Start *************************************
srand ((double)microtime()*1000000);
$num = rand(0,(count($dbarray)-1));

$praktdbmaster = @mysql_pconnect($dbarray[0]["host"],$dbarray[0]["mysqluser"],$dbarray[0]["mysqlpassword"]);
$praktdbslave = @mysql_pconnect($dbarray[$num]["host"],$dbarray[$num]["mysqluser"],$dbarray[$num]["mysqlpassword"]);

$sucharray = explode(" ",$_REQUEST["suchstring"]);
foreach ($sucharray as $suchvalpart) {
	if ($suchvalpart{0} != "+" && $suchvalpart{0} != "-" && $suchvalpart{0} != "~"  && $suchvalpart{0} != "*")
	$suchvalpart = "+".$suchvalpart;
	$sucharrayfinal[] = $suchvalpart;
}
$suchstringfinal = implode(" ", $sucharrayfinal);

if ($_REQUEST["sortierung"] == "warenkorbanzeige") {

	session_name(praktika_id);
	session_save_path("/home/praktika/sessions");
	session_start();
	$wk_db_array = array_keys($s_firmenproparray["kandidatensuche"]["warenkorb"]);

	$sqlvar = implode(",",$wk_db_array);

	$sql = "SELECT `nutzerid` , `ort` , `land` , `bundesland` , `grossraum` , `kandidatenalter`, `gesuch_spalte`, `gesuch_berufsfeld`, tmp_kandidatensuche.`studiengang`, `gesuch_sprachkenntnisse1`, `gesuch_sprachkenntnisse2`, `gesuch_sprachkenntnisse3` , `geschlecht` , `karierrestatus` , `profilquali`, `lastlogin` FROM `tmp_kandidatensuche` WHERE nutzerid IN (".$sqlvar.")";
	$results=mysql($database_temp,$sql,$praktdbmaster);
	$num_rows = mysql_num_rows($results);

	if ($num_rows > 0) {
		$rueckgabe .= suchergebnis($_REQUEST["ds_count"],$num_rows,false);
	} else {
		$rueckgabe .= "Es wurden keine Kandidaten gefunden, die auf Ihre Sucheingabe passen, bitte versuchen Sie es erneut.";
	}
	
	// Neuaufbau des Kandidatensuch array bei älterer Buchung
	mysql_data_seek($results, 0);
	while ( $result_id = mysql_fetch_array ( $results, MYSQL_ASSOC )) {
		$new_wk_array[$result_id['nutzerid']] = true;
	}
	if (count($new_wk_array) > 0) {
		$s_firmenproparray["kandidatensuche"]["warenkorb"] = $new_wk_array;
	}
} 

elseif ($_REQUEST["sortierung"] != "" && $_REQUEST["sortierung"] != "keine") {
	switch ($_REQUEST["sortierung"]) {
		case "einsatzgebiet":
			$sql = "SELECT berufsfelder.id,berufsfelder.german AS kategorie,count(tmp_kandidatensuche.`gesuch_berufsfeld`) AS anzahl FROM `tmp_kandidatensuche`,prakt2.berufsfelder WHERE MATCH (volltext) AGAINST ('".$suchstringfinal."' IN BOOLEAN MODE) AND berufsfelder.id = tmp_kandidatensuche.gesuch_berufsfeld GROUP BY `gesuch_berufsfeld` ORDER BY berufsfelder.german";
			break;
		case "studienrichtung":
			$sql = "SELECT studienrichtungen.id,studienrichtungen.german AS kategorie,count(tmp_kandidatensuche.`studiengang`) AS anzahl FROM `tmp_kandidatensuche`,prakt2.studiengaenge,prakt2.studienrichtungen WHERE MATCH (volltext) AGAINST ('".$suchstringfinal."' IN BOOLEAN MODE) AND studiengaenge.id = tmp_kandidatensuche.studiengang AND studiengaenge.richtungid = studienrichtungen.ID GROUP BY `richtungid` ORDER BY studienrichtungen.german";
			break;
		case "semester":
			$sql = "SELECT semester AS id,semester AS kategorie,count(tmp_kandidatensuche.`semester`) AS anzahl FROM `tmp_kandidatensuche` WHERE MATCH (volltext) AGAINST ('".$suchstringfinal."' IN BOOLEAN MODE) AND semester > 0 GROUP BY `semester` ORDER BY semester";
			break;
		case "karierrestatus":
			$sql = "SELECT karierrestatus.id,karierrestatus.german AS kategorie,count(tmp_kandidatensuche.`karierrestatus`) AS anzahl FROM `tmp_kandidatensuche`,prakt2.karierrestatus WHERE MATCH (volltext) AGAINST ('".$suchstringfinal."' IN BOOLEAN MODE) AND karierrestatus.id = tmp_kandidatensuche.karierrestatus GROUP BY `karierrestatus` ORDER BY karierrestatus.anzeigeorder";
			break;
		case "alter":
			$sql = "SELECT kandidatenalter AS id,kandidatenalter AS kategorie,count(tmp_kandidatensuche.`kandidatenalter`) AS anzahl FROM `tmp_kandidatensuche` WHERE MATCH (volltext) AGAINST ('".$suchstringfinal."' IN BOOLEAN MODE) GROUP BY `kandidatenalter` ORDER BY kandidatenalter";
			break;
		case "bundesland":
			$sql = "SELECT bundesland.id,bundesland.german AS kategorie,count(tmp_kandidatensuche.`bundesland`) AS anzahl FROM `tmp_kandidatensuche`,prakt2.bundesland WHERE MATCH (volltext) AGAINST ('".$suchstringfinal."' IN BOOLEAN MODE) AND bundesland.id = tmp_kandidatensuche.bundesland AND land=68 GROUP BY `bundesland` ORDER BY bundesland.german";
			break;
		case "land":
			$sql = "SELECT land.id,land.german AS kategorie,count(tmp_kandidatensuche.`land`) AS anzahl FROM `tmp_kandidatensuche`,prakt2.land WHERE MATCH (volltext) AGAINST ('".$suchstringfinal."' IN BOOLEAN MODE) AND land.id = tmp_kandidatensuche.land GROUP BY `land` ORDER BY land.GERMAN";
			break;
	}
	$rueckgabe = '<form action="" id="checkform"><input type="hidden" name="sortierung" value="'.$sortierung.'">';
	$resultquery=mysql($database_temp,$sql,$praktdbmaster);
	if ($resultquery && mysql_numrows($resultquery) > 0) {
		while ($row = mysql_fetch_array($resultquery, MYSQL_NUM)) {
			$rueckgabe .= '<input type="checkbox" name="'.$sortierung.$row[0].'" value="'.$row[0].'" class="checkbox_radio">&nbsp;<a href="javascript:LinkSubmit('.$_REQUEST["sortierung"].','.$row[0].')">'.$row[1].'</a>&nbsp;('.$row[2].')<br>';
		}
		$rueckgabe .= '<hr><input type="button" value="Auswahl suchen" onclick="CheckboxSubmit()">';
	} else {
		$rueckgabe .= "Es wurden keine Kandidaten gefunden, die auf Ihre Sucheingabe passen, bitte versuchen Sie es erneut.";
	}
	$rueckgabe .= '<br><br><a href="/home/firmen/einblick.phtml">zur Eingabemaske</a></form>';


} else {
	if (is_array($categoyid))
	$seriids = implode(",",$categoyid);


	session_name(praktika_id);
	session_save_path("/home/praktika/sessions");
	session_start();

	$s_suchstring=$_REQUEST["suchstring"];

	if (!$s_firmenproparray) {
		session_register("s_firmenproparray");
		$s_firmenproparray["kandidatensuche"]["finesearch"]["values"] = array();
		if (is_array($_REQUEST["finesearch"])) $s_firmenproparray["kandidatensuche"]["finesearch"]["values"] = $_REQUEST["finesearch"];
		$s_firmenproparray["kandidatensuche"]["finesearch"]["category"] = $_REQUEST["category"];
		$s_firmenproparray["kandidatensuche"]["finesearch"]["category"] = $_REQUEST["category"];
		$s_firmenproparray["kandidatensuche"]["finesearch"]["seriids"] = $seriids;
	}

	if ($s_firmenproparray["kandidatensuche"]["finesearch"]["category"] != $_REQUEST["category"]) $s_firmenproparray["kandidatensuche"]["finesearch"]["values"] = array();
	if ($s_firmenproparray["kandidatensuche"]["finesearch"]["seriids"] != $seriids) $s_firmenproparray["kandidatensuche"]["finesearch"]["values"] = array();
	$s_firmenproparray["kandidatensuche"]["finesearch"]["category"] = $_REQUEST["category"];
	$s_firmenproparray["kandidatensuche"]["finesearch"]["seriids"] = $seriids;

	if (is_array($_REQUEST["finesearch"])) {
		$errorcount = 0;
		foreach ($_REQUEST["finesearch"] as $finesearchkey => $finesearchvalue) {
			if ($finesearchvalue != $s_firmenproparray["kandidatensuche"]["finesearch"]["values"][$finesearchkey])
			$errorcount++;
		}
	}

	if (is_array($_REQUEST["finesearch"]) && $errorcount != 0) $s_firmenproparray["kandidatensuche"]["finesearch"]["values"]=$_REQUEST["finesearch"];

	switch ($_REQUEST["category"]) {
		case "einsatzgebiet":
			$sqlext .= "AND gesuch_berufsfeld IN ($seriids)";
			break;
		case "studienrichtung":
			$sqlext .= "AND studiengaenge.id = tmp_kandidatensuche.studiengang AND studiengaenge.richtungid IN ($seriids)";
			$exttable = ",prakt2.studiengaenge";
			break;
		case "semester":
			$sqlext .= "AND semester IN ($seriids)";
			break;
		case "karierrestatus":
			$sqlext .= "AND karierrestatus IN ($seriids)";
			break;
		case "alter":
			$sqlext .= "AND kandidatenalter IN ($seriids)";
			break;
		case "bundesland":
			$sqlext .= "AND bundesland IN ($seriids) AND land=68";
			break;
		case "land":
			$sqlext .= "AND land IN ($seriids)";
			break;
	}

	if (is_array($s_firmenproparray["kandidatensuche"]["finesearch"]["values"])) {
		foreach ($s_firmenproparray["kandidatensuche"]["finesearch"]["values"] as $finesearchkey => $finesearchvalue) {
			if ($finesearchvalue != "")
			switch ($finesearchkey) {
				case "berufsfeldselect":
					$sqlext .= " AND gesuch_berufsfeld = '$finesearchvalue'";
					break;
				case "studiengangselect":
					$sqlext .= " AND tmp_kandidatensuche.studiengang = '$finesearchvalue'";
					break;
				case "karierrestatusselect":
					$sqlext .= " AND karierrestatus = '$finesearchvalue'";
					break;
				case "grossraumselect":
					$sqlext .= " AND grossraum = '$finesearchvalue'";
					break;
				case "bundeslandselect":
					$sqlext .= " AND bundesland = '$finesearchvalue'";
					break;
				case "landselect":
					$sqlext .= " AND land = '$finesearchvalue'";
					break;
				case "spracheselect":
					$sqlext .= " AND (gesuch_sprachkenntnisse1 = '$finesearchvalue' OR gesuch_sprachkenntnisse2 = '$finesearchvalue' OR gesuch_sprachkenntnisse3 = '$finesearchvalue')";
					break;
				case "alterselect":
					$altersbereich = explode ("-",$finesearchvalue);
					$sqlext .= " AND kandidatenalter >= '".$altersbereich[0]."' AND kandidatenalter <= '".$altersbereich[1]."'";
					break;
				case "geschlechtselect":
					$sqlext .= " AND geschlecht = '$finesearchvalue'";
					break;
				case "plzselect":
					$sqlext .= " AND plz LIKE '$finesearchvalue%'";
					break;
				case "spalteselect":
					$sqlext .= " AND gesuch_spalte = '$finesearchvalue'";
					break;
				case "profilqualitaetselect":
					$sqlext .= " AND profilquali >= '$finesearchvalue'";
					break;
				case "suchlastloginselect":
					$sqlext .= " AND lastlogin > '$finesearchvalue'";
					break;
			}
		}
	}

	$sql = "SELECT `nutzerid` , `ort` , `land` , `bundesland` , `grossraum` , `kandidatenalter`, `gesuch_spalte`, `gesuch_berufsfeld`, tmp_kandidatensuche.`studiengang`, `gesuch_sprachkenntnisse1`, `gesuch_sprachkenntnisse2`, `gesuch_sprachkenntnisse3` , `geschlecht` , `karierrestatus` , `profilquali`, `lastlogin` FROM `tmp_kandidatensuche`".$exttable." WHERE MATCH (volltext) AGAINST ('".$suchstringfinal."' IN BOOLEAN MODE) ".$sqlext;


	$results=mysql($database_temp,$sql,$praktdbmaster);
	$num_rows = mysql_num_rows($results);

	if ($num_rows > 0) {
		$rueckgabe .= suchergebnis($_REQUEST["ds_count"],$num_rows,true);
	} else {
		$rueckgabe .= "Es wurden keine Kandidaten gefunden, die auf Ihre Sucheingabe passen, bitte versuchen Sie es erneut.";
	}
}

//$rueckgabe .= $sql;

$submitarray = Array();
$submitarray["mainvalue"] = $rueckgabe;

$content .= '<?xml version="1.0" encoding="ISO-8859-1"?>'."\n";
$content .= "<root>\n";
$content .= "<htmlinhalte>\n";

foreach ($submitarray as $arraykey => $arrayelement) {
	if (!empty($arrayelement)) {
		$arrayelement = str_split($arrayelement,4096);
		$i = 0;
		foreach ($arrayelement as $arrayvalue) {
			$content .= "<".$arraykey.$i.">".htmlspecialchars($arrayvalue)."</".$arraykey.$i.">\n";
			$i++;
		}
	}
}
$content .= "<wkcount>".count($s_firmenproparray["kandidatensuche"]["warenkorb"])."</wkcount>\n";
$content .= "</htmlinhalte>\n";
$content .= "</root>";

header("Content-Type: text/xml");
header("Content-Length: ".strlen($content));

echo $content;
?>