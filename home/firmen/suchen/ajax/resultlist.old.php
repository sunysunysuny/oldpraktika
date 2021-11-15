<?php
//  praktika.de ajax File - KANDIDATENAUSGABE -  WENN FIRMEN SUCHEN

require("/home/praktika/etc/dbzugang.inc.php");
require("/home/praktika/etc/lib_reworked.inc.php");

$spaltenarray = init_spaltenarray();

/* FUNCTIONS */
function seitenausgabe2($num_rows, $ds_pro_seite, $ds_count, $ajaxtype) {
	if (!$z && !$seitenzaehler && $ds_count > 90) {
		$seitenzaehler = (($ds_count / 10) + 10);

		$laenge = strlen($ds_count);
		if ($ds_count % 100 > 0) {
			$splitzahl = substr($ds_count, 0, ($laenge - ($laenge - 1) ));
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

	if ($Seiten > 0) {
		$content = 'Seiten:&nbsp;';
	}
	
	if (!$z && !$seitenzaehler) {
		$seitenzaehler = 10;
		$z = 1;
	}
	
	if (!$seitenzaehler && $Seiten >= 10) {
		$seitenzaehler = 10;
		$z = 1;
	}
	
	if ($seitenzaehler > $Seiten) {
		$seitenzaehler = $Seiten;
	}
	
	$z = (floor($ds_count / 100) * 10) + 1;

	if ($ds_count % ($ds_pro_seite * 10) == 0) {
		$dcount = $ds_count - $ds_pro_seite;
	} else {
		$dcount = $ds_count - ($ds_pro_seite * 10);
	}
	
	if ($z >= 10) {
		$content .= '<a href="javascript:Load_SearchData(\''.$ajaxtype.'\', document.getElementById(\'sortierung\').value,document.getElementById(\'ksuchstring\').value,document.getElementById(\'categoryids\').value,\''.($dcount).'\');">&lt;</a>&nbsp;';
	}

	for ($i = $z; $i <= $seitenzaehler; $i++) {
		$fwd = ($i - 1) * $ds_pro_seite;
		if ($i < $seitenzaehler) {
			$slash = '&nbsp;|&nbsp;';
		} else {
			$slash = '';
		}

		if ($fwd == $ds_count) {
			$content .= $i.$slash;
		} else {
			$content .= '<a href="javascript:Load_SearchData(\''.$ajaxtype.'\',document.getElementById(\'sortierung\').value,document.getElementById(\'ksuchstring\').value,document.getElementById(\'categoryids\').value,\''.$fwd.'\');">$i</a>'.$slash;
		}
	}

	if ($seitenzaehler < $Seiten) {
		$content .= '&nbsp;<a href="javascript:Load_SearchData(\''.$ajaxtype.'\',document.getElementById(\'sortierung\').value,document.getElementById(\'ksuchstring\').value,document.getElementById(\'categoryids\').value,\''.($fwd+$ds_pro_seite).'\');">&gt;</a>';
	}

	return $content;
}


function suchergebnis($ds_count, $num_rows, $fineselect) {
	global $_REQUEST,$results,$s_ergebnisarry,$praktdbmaster,$praktdbslave,$database_db,$database_temp,$database_comm,$spaltenarray,$s_spalte,$language,$s_loggedin,$s_firmenproparray,$s_loggedinnutzer,$stellenausgabe,$suchstring,$wk_inhalt;

	if (!isset($_SESSION['s_sprache'])) {
		$_SESSION['s_sprache'] = 'german';
	}

	if ($fineselect) {
		$aktdate = strtotime(date('Y-m-d'));

		$i = 0;
		while (($i < 500) && ($result_inhalt = mysql_fetch_array($results, MYSQL_ASSOC))) {
			$suchinhalte['userid'][$result_inhalt['nutzerid']] = true;
			
			foreach ($result_inhalt as $key => $value) {
				if ($key == 'geschlecht' && empty($value)) {
					$suchinhalte[$key][$result_inhalt[$value]] = true;
				} elseif ($key != 0) {
					$suchinhalte[$key][$result_inhalt[$value]] = true;
				}
			}
			/*
			if($result_inhalt['land'] != 0)
				$suchinhalte['land'][$result_inhalt['land']] = true;			
			if($result_inhalt['bundesland'] != 0)
				$suchinhalte['bundesland'][$result_inhalt['bundesland']] = true;
			if($result_inhalt['grossraum'] != 0)
				$suchinhalte['grossraum'][$result_inhalt['grossraum']] = true;
			if($result_inhalt['plz'] != 0)
				$suchinhalte['plz'][$result_inhalt['plz']] = true;
			if($result_inhalt['gesuch_berufsfeld'] != 0)
				$suchinhalte['einsatzgebiet'][$result_inhalt['gesuch_berufsfeld']] = true;
			if($result_inhalt['studiengang'] != 0)
				$suchinhalte['studiengang'][$result_inhalt['studiengang']] = true;
			if($result_inhalt['lastlogin'] != 0)
				$suchinhalte['lastlogin'][$result_inhalt['lastlogin']] = true;
			if($result_inhalt['gesuch_sprachkenntnisse1'] != 0)
				$suchinhalte['gesuch_sprachkenntnisse'][$result_inhalt['gesuch_sprachkenntnisse1']] = true;
			if($result_inhalt['gesuch_sprachkenntnisse2'] != 0)
				$suchinhalte['gesuch_sprachkenntnisse'][$result_inhalt['gesuch_sprachkenntnisse2']] = true;
			if($result_inhalt['gesuch_sprachkenntnisse3'] != 0)
				$suchinhalte['gesuch_sprachkenntnisse'][$result_inhalt['gesuch_sprachkenntnisse3']] = true;
			if($result_inhalt['kandidatenalter'] != 0)
				$suchinhalte['kandidatenalter'][$result_inhalt['kandidatenalter']] = true;
			if($result_inhalt['geschlecht'] != "")
				$suchinhalte['geschlecht'][$result_inhalt['geschlecht']] = true;
			if($result_inhalt['karierrestatus'] != 0)
				$suchinhalte['karierrestatus'][$result_inhalt['karierrestatus']] = true;
			if($result_inhalt['gesuch_spalte'] != 0)
				$suchinhalte['bereich'][$result_inhalt['gesuch_spalte']] = true;
			if($result_inhalt['profilquali'] != 0)
				$suchinhalte['profilquali'][$result_inhalt['profilquali']] = true;*/
			$i++;
		}

		unset($s_ergebnisarry['sucheingabe']);

		$s_ergebnisarry['sucheingabe']['userid'] = array_keys( $suchinhalte['userid'] );
		if ( is_array( $suchinhalte['land'] ) )
			$s_ergebnisarry['sucheingabe']['land'] = array_keys( $suchinhalte['land'] );
		if ( is_array( $suchinhalte['bundesland'] ) )
			$s_ergebnisarry['sucheingabe']['bundesland'] = array_keys( $suchinhalte['bundesland'] );
		if ( is_array( $suchinhalte['grossraum'] ) )
			$s_ergebnisarry['sucheingabe']['grossraum'] = array_keys( $suchinhalte['grossraum'] );
		if ( is_array( $suchinhalte['plz'] ) )
			$s_ergebnisarry['sucheingabe']['plz'] = array_keys( $suchinhalte['plz'] );
		if ( is_array( $suchinhalte['einsatzgebiet'] ) )
			$s_ergebnisarry['sucheingabe']['berufsfeld'] = array_keys( $suchinhalte['einsatzgebiet'] );
		if ( is_array( $suchinhalte['bereich'] ) )
			$s_ergebnisarry['sucheingabe']['bereich'] = array_keys( $suchinhalte['bereich'] );
		if ( is_array( $suchinhalte['geschlecht'] ) )
			$s_ergebnisarry['sucheingabe']['geschlecht'] = array_keys( $suchinhalte['geschlecht'] );
		if ( is_array( $suchinhalte['karierrestatus'] ) )
			$s_ergebnisarry['sucheingabe']['karierrestatus'] = array_keys( $suchinhalte['karierrestatus'] );
		if ( is_array( $suchinhalte['profilquali'] ) )
			$s_ergebnisarry['sucheingabe']['profilqualitaet'] = array_keys( $suchinhalte['profilquali'] );
		if ( is_array( $suchinhalte['lastlogin'] ) )
			$s_ergebnisarry['sucheingabe']['lastlogin'] = array_keys( $suchinhalte['lastlogin'] );
		if ( is_array( $suchinhalte['kandidatenalter'] ) )
			$s_ergebnisarry['sucheingabe']['kandidatenalter'] = array_keys( $suchinhalte['kandidatenalter'] );
		if ( is_array( $suchinhalte['karierrestatus'] ) )
			$s_ergebnisarry['sucheingabe']['karierrestatus'] = array_keys( $suchinhalte['karierrestatus'] );
		if ( is_array( $suchinhalte['studiengang'] ) )
			$s_ergebnisarry['sucheingabe']['studiengang'] = array_keys( $suchinhalte['studiengang'] );
		if ( is_array( $suchinhalte['gesuch_sprachkenntnisse'] ) )
			$s_ergebnisarry['sucheingabe']['praktikumssprache'] = array_keys( $suchinhalte['gesuch_sprachkenntnisse'] );


		if (is_array($s_ergebnisarry['sucheingabe']['kandidatenalter'])) {
			foreach ($s_ergebnisarry['sucheingabe']['kandidatenalter'] as $altervalues) {
				if ($altervalues >= 15 && $altervalues <= 18) {
					$s_ergebnisarry['sucheingabe']['alter'][] = '15-18';
				} elseif ($altervalues >= 19 && $altervalues <= 23) {
					$s_ergebnisarry['sucheingabe']['alter'][] = '19-23';
				} elseif ($altervalues >= 24 && $altervalues <= 28) {
					$s_ergebnisarry['sucheingabe']['alter'][] = '24-28';
				} elseif ($altervalues >= 29 && $altervalues <= 35) {
					$s_ergebnisarry['sucheingabe']['alter'][] = '29-35';
				} elseif ($altervalues >= 36 && $altervalues <= 65) {
					$s_ergebnisarry['sucheingabe']['alter'][] = '36-65';
				}
			}
			unset($s_ergebnisarry['sucheingabe']['kandidatenalter']);
		}

		if (is_array($s_ergebnisarry['sucheingabe']['lastlogin'])) {
			foreach ($s_ergebnisarry['sucheingabe']['lastlogin'] as $lastloginvalues) {
				if ( $lastloginvalues >= ($aktdate - 604800) ) {
					$s_ergebnisarry['sucheingabe']['suchlastlogin'][] = ($aktdate - 604800);
				} elseif ($lastloginvalues <= ($aktdate - 604800) && $lastloginvalues >= ($aktdate - 1209600)) {
					$s_ergebnisarry['sucheingabe']['suchlastlogin'][] = ($aktdate - 1209600);
				} elseif ($lastloginvalues <= ($aktdate - 1209600) && $lastloginvalues >= ($aktdate - 2592000)) {
					$s_ergebnisarry['sucheingabe']['suchlastlogin'][] = ($aktdate - 2592000);
				} elseif ($lastloginvalues <= ($aktdate - 2592000) && $lastloginvalues >= ($aktdate - 5184000)) {
					$s_ergebnisarry['sucheingabe']['suchlastlogin'][] = ($aktdate - 5184000);
				} elseif ($lastloginvalues <= ($aktdate - 5184000) && $lastloginvalues >= ($aktdate - 7776000)) {
					$s_ergebnisarry['sucheingabe']['suchlastlogin'][] = ($aktdate - 7776000);
				} elseif ($lastloginvalues <= ($aktdate - 7776000) && $lastloginvalues >= ($aktdate - 15552000)) {
					$s_ergebnisarry['sucheingabe']['suchlastlogin'][] = ($aktdate - 15552000);
				}
			}
			unset($s_ergebnisarry['sucheingabe']['lastlogin']);
		}

		//print_pr($s_ergebnisarry[warenkorb]);
		//print_pr($s_ergebnisarry);
		if (!$wk_inhalt) {
			$rueckgabe .= '<h4>Suchergebnis verfeinern</h4>'."\n";
			$rueckgabe .= '	<form action="">'."\n";
			$rueckgabe .= '		<fieldset style="margin-top: 1em;">'."\n";
			$rueckgabe .= '			<p>'."\n";
			$rueckgabe .= '				<label for="land">Land:</label>'."\n";
			$rueckgabe .= '				<select id="land" name="land">'."\n";
			$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";
			
			if (is_array($s_ergebnisarry['sucheingabe']['land'])) {
				$rueckgabe .= '					<optgroup label="Deutschsprachig">'."\n";
				
				$row[0] = 68;
				$row[1] = 'Deutschland';
				
				if (in_array($row[0], $s_ergebnisarry['sucheingabe']['land'])) {
					$rueckgabe .= '					<option value="'.$row[0].'"'.(count($s_ergebnisarry[sucheingabe][land]) ? ' selected="selected"' : '').'>'.$row[1].'</option>'."\n";
				}
				
				$row[0] = 14;
				$row[1] = '&Ouml;sterreich';
				
				if (in_array($row[0], $s_ergebnisarry['sucheingabe']['land'])) {
					$rueckgabe .= '					<option value="'.$row[0].'"'.(count($s_ergebnisarry['sucheingabe']['land']) ? ' selected="selected"' : '').'>'.$row[1].'</option>'."\n";
				}
				
				$row[0] = 156;
				$row[1] = 'Schweiz';
				
				if (in_array($row[0], $s_ergebnisarry['sucheingabe']['land'])) {
					$rueckgabe .= '					<option value="'.$row[0].'"'.(count($s_ergebnisarry['sucheingabe']['land']) ? ' selected="selected"' : '').'>'.$row[1].'</option>'."\n";
				}

				$rueckgabe .= '					</optgroup>'."\n";
				$rueckgabe .= '					<optgroup label="andere">'."\n";

				$sql = sprintf("SELECT 
									id,
									%1\$s
								FROM 
									land 
								WHERE 
									sprachid != 2 AND 
									id IN (%2\$s) 
								ORDER BY 
									%1\$s",
								$_SESSION['s_sprache'],
								implode(",", $s_ergebnisarry['sucheingabe']['land']) );
				
				$laender = mysql_db_query($database_db, $sql, $praktdbslave);
				
				while ($row = mysql_fetch_array($laender, MYSQL_NUM)) {
					$rueckgabe .= '					<option value="'.$row[0].'"'.((count($s_ergebnisarry['sucheingabe']['land']) == 1 && in_array($row[0],$s_ergebnisarry['sucheingabe']['land'])) ? ' selected="selected"' : '').'>'.$row[1].'</option>'."\n";
				}
				
				$rueckgabe .= '					</optgroup>'."\n";
			}
			
			$rueckgabe .= '				</select>'."\n";
			$rueckgabe .= '			</p>'."\n";
			$rueckgabe .= '			<p>'."\n";
			$rueckgabe .= '				<label for="bundesland">Bundesland:</label>'."\n";
			$rueckgabe .= '				<select id="bundesland" name="bundesland">'."\n";
			$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";
			
			if (is_array($s_ergebnisarry['sucheingabe']['bundesland'])) {
				$sql = sprintf("SELECT
									id,
									landid,
									%1\$s
								FROM 
									bundesland 
								WHERE  
									id IN (%2\$s) 
								ORDER BY 
									%1\$s",
								$_SESSION['s_sprache'],
								implode(",", $s_ergebnisarry['sucheingabe']['bundesland']));
				
				$rs = mysql_db_query($database_db, $sql, $praktdbslave);
				
				while ($result_row = mysql_fetch_array($rs, MYSQL_ASSOC)) {
					$blandarray[$result_row['landid']][] = $result_row;
					$land_ids[] = $result_row['landid'];
				}
				
				$sql = sprintf("SELECT 
									id,
									%1\$s
								FROM 
									land 
								WHERE  
									id IN (%2\$s) 
								ORDER BY 
									%1\$s",
								$_SESSION['s_sprache'],
								implode(",",$land_ids));

				$landresult = mysql_db_query($database_db, $sql, $praktdbslave);

				while ($result_land = mysql_fetch_array($landresult, MYSQL_ASSOC)) {
					$rueckgabe .= '					<optgroup label="'.$result_land[$_SESSION['s_sprache']].'">'."\n";
					
					foreach ($blandarray[$result_land['id']] as $row) {
						$rueckgabe .= '						<option value="'.$row['id'].'"'.((count($s_ergebnisarry['sucheingabe']['bundesland']) == 1 && in_array($row['id'],$s_ergebnisarry['sucheingabe']['bundesland'])) ? ' selected="selected"' : '').'>'.$row[$_SESSION['s_sprache']].'</option>'."\n";
					}

					$rueckgabe .= '					</optgroup>'."\n";
				}
			}
			
			$rueckgabe .= '				</select>'."\n";
			$rueckgabe .= '			</p>'."\n";
			$rueckgabe .= '			<p>'."\n";
			$rueckgabe .= '				<label for="grossraum">Gro&szlig;raum:</label>'."\n";
			$rueckgabe .= '				<select id="grossraum" name="grossraum">'."\n";
			$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";
			
			if (is_array($s_ergebnisarry['sucheingabe']['grossraum'])) {
				$sql = sprintf("SELECT
									id,
									grossraum
								FROM 
									grossraum 
								WHERE  
									id IN (%1\$s) 
								ORDER BY 
									grossraum",
								implode(",", $s_ergebnisarry['sucheingabe']['grossraum']));

				$grossraum = mysql_db_query($database_db, $sql, $praktdbslave);
				
				while ($row = mysql_fetch_array($grossraum, MYSQL_NUM)) {
					$rueckgabe .= '					<option value="'.$row[0].'"'.((count($s_ergebnisarry['sucheingabe']['grossraum']) == 1 && in_array($row[0],$s_ergebnisarry['sucheingabe']['grossraum'])) ? ' selected="selected"' : '').'>'.$row[1].'</option>'."\n";
				}
			}

			$rueckgabe .= '				</select>'."\n";
			$rueckgabe .= '			</p>'."\n";
			$rueckgabe .= '			<p>'."\n";
			$rueckgabe .= '				<label for="plz">PLZ-Gebiet:</label>'."\n";
			$rueckgabe .= '				<select id="plz" name="plz">'."\n";
			$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";
			
			if (is_array($s_ergebnisarry['sucheingabe']['plz'])) {
				$a_plz = array();
				
				foreach ($s_ergebnisarry['sucheingabe']['plz'] as $value) {
					$a_plz[] = substr($value, 0, 1);
				}
				
				$a_plz = array_unique($a_plz);
			}

			if (is_array($a_plz)) {
				for ($i = 0; $i < 10; $i++) {
					if ( in_array($i, $a_plz) ) {
						$rueckgabe .= '					<option value="'.$i.'"'.(count($a_plz) == 1 ? ' selected="selected"' : '').'>'.$i.'</option>'."\n";
					}				
				}
			}
			$rueckgabe .= '				</select>'."\n";
			$rueckgabe .= '			</p>'."\n";
			$rueckgabe .= '			<p>'."\n";
			$rueckgabe .= '				<label for="berufsfeld">Berufsfeld:</label>'."\n";
			$rueckgabe .= '				<select id="berufsfeld" name="berufsfeld">'."\n";
			$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";
			
			if (is_array($s_ergebnisarry['sucheingabe']['berufsfeld'])) {
				$sql = sprintf("SELECT 
									id,
									%1\$s
								FROM 
									berufsfelder 
								WHERE 
									id IN (%2\$s) 
								ORDER BY 
									%1\$s",
								$_SESSION['s_sprache'],
								implode(",", $s_ergebnisarry['sucheingabe']['berufsfeld']));
				
				$berufsfelder = mysql_db_query($database_db, $sql, $praktdbslave);
				
				while ($row = mysql_fetch_array($berufsfelder, MYSQL_NUM)) {
					$rueckgabe .= '					<option value="'.$row[0].'"'.((count($s_ergebnisarry['sucheingabe']['berufsfeld']) == 1 && in_array($row[0], $s_ergebnisarry['sucheingabe']['berufsfeld'])) ? ' selected="selected"' : '').'>'.$row[1].'</option>'."\n";
				}
			}
			
			$rueckgabe .= '				</select>'."\n";
			$rueckgabe .= '			</p>'."\n";
			$rueckgabe .= '			<p>'."\n";
			$rueckgabe .= '				<label for="studiengang">Studiengang:</label>'."\n";
			$rueckgabe .= '				<select id="studiengang" name="studiengang">'."\n";
			$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";
			
			if (is_array($s_ergebnisarry['sucheingabe']['studiengang'])) {
				$a_studiengang = $s_ergebnisarry['sucheingabe']['studiengang'];
				
				$sql = sprintf("SELECT 
									id,
									studiengang
								FROM 
									studiengaenge 
								WHERE 
									id IN (%1\$s) 
								ORDER BY 
									studiengang",
								implode(",", $a_studiengang) );

				$studiengaenge = mysql_db_query($database_db, $sql, $praktdbslave);
				
				while ($row = mysql_fetch_array($studiengaenge, MYSQL_NUM)) {
					$rueckgabe .= '					<option value="'.$row[0].'"'.((count($a_studiengang) == 1 && in_array($row[0], $a_studiengang)) ? ' selected="selected"' : '').'>'.$row[1].'</option>'."\n";
				}
			}
			$rueckgabe .= '				</select>'."\n";
			$rueckgabe .= '			</p>'."\n";
			$rueckgabe .= '			<p>'."\n";
			$rueckgabe .= '				<label for="lastlogin">letztes Login:</label>'."\n";
			$rueckgabe .= '				<select id="lastlogin" name="lastlogin">'."\n";
			$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";
			
			if (is_array($s_ergebnisarry['sucheingabe']['suchlastlogin'])) {
				$a_lastlogin = array_unique($s_ergebnisarry['sucheingabe']['suchlastlogin']);
				$day = 86400;
				
				$days = array(7 => '7 Tage', 14 => '14 Tage', 30 => '1 Monat', 60 => '2 Monate', 90 => '3 Monate', 180 => '6 Monate');

				foreach ($days as $key => $value){
					if (in_array(($aktdate - $key * $day), $a_lastlogin)) {
						$rueckgabe .= '					<option value="'.($aktdate - $key * $day).'"'.((count($a_lastlogin) == 1 && in_array(($aktdate - $key * $day), $a_lastlogin )) ? ' selected="selected"' : '').'>max. '.$value.' Tage</option>';
					}
				}
			}
			$rueckgabe .= '				</select>'."\n";
			$rueckgabe .= '			</p>'."\n";
			$rueckgabe .= '			<p>'."\n";
			$rueckgabe .= '				<label for="sprache">Sprachkenntnisse:</label>'."\n";
			$rueckgabe .= '				<select id="sprache" name="sprache">'."\n";
			$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";
			
			if (is_array($s_ergebnisarry['sucheingabe']['praktikumssprache'])) {
				$a_sprache = $s_ergebnisarry['sucheingabe']['praktikumssprache'];
				
				$sql = sprintf("SELECT 
									id,
									%1\$s
								FROM 
									sprachen 
								WHERE 
									id IN (%2\$s) 
								ORDER BY 
									%1\$s",
								$_SESSION['s_sprache'],
								implode(",", $a_sprache));

				$sprachen = mysql_db_query($database_db, $sql, $praktdbslave);
				
				while ($row = mysql_fetch_array($sprachen, MYSQL_NUM)) {
					$rueckgabe .= '					<option value="'.$row[0].'"'.((count($a_sprache) == 1 && in_array($row[0], $a_sprache)) ? ' selected="selected"' : '').'>'.$row[1].'</option>'."\n";
				}
			}
			$rueckgabe .= '				</select>'."\n";
			$rueckgabe .= '			</p>'."\n";
			$rueckgabe .= '			<p>'."\n";
			$rueckgabe .= '				<label for="alter">Altersgruppe:</label>'."\n";
			$rueckgabe .= '				<select id="alter" name="alter">'."\n";
			$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";
			
			if (is_array($s_ergebnisarry['sucheingabe']['alter'])) {
				$a_alter = array_unique($s_ergebnisarry['sucheingabe']['alter']);
				
				$alter = array('15-18','19-23','24-28','29-35','36-65');
				
				foreach ($alter as $key) {
					if (in_array($key, $a_alter)) {
						$rueckgabe .= '					<option value="'.$key.'"'.(count($a_alter) == 1 ? ' selected="selected"' : '').'>'.$key.' Jahre</option>';
					}
				}
			}

			$rueckgabe .= '				</select>'."\n";
			$rueckgabe .= '			</p>'."\n";
			$rueckgabe .= '			<p>'."\n";
			$rueckgabe .= '				<label for="geschlecht">Geschlecht:</label>'."\n";
			$rueckgabe .= '				<select id="geschlecht" name="geschlecht">'."\n";
			$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";
			$rueckgabe .= '					<option value="m"'.((count($s_ergebnisarry['sucheingabe']['geschlecht']) == 1 && in_array("m", $s_ergebnisarry['sucheingabe']['geschlecht'])) ? ' selected="selected"' : '').'>m&auml;nnlich</option>'."\n";
			$rueckgabe .= '					<option value="w"'.((count($s_ergebnisarry['sucheingabe']['geschlecht']) == 1 && in_array("w", $s_ergebnisarry['sucheingabe']['geschlecht'])) ? ' selected="selected"' : '').'>weiblich</option>'."\n";
			$rueckgabe .= '				</select>'."\n";
			$rueckgabe .= '			</p>'."\n";
			$rueckgabe .= '			<p>'."\n";
			$rueckgabe .= '				<label for="karierrestatus">Karierrestatus:</label>'."\n";
			$rueckgabe .= '				<select id="karierrestatus" name="karierrestatus">'."\n";
			$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";
			
			if (is_array( $s_ergebnisarry['sucheingabe']['karierrestatus'])) {
				$a_karierrestatus = $s_ergebnisarry['sucheingabe']['karierrestatus'];
				$sql = sprintf("SELECT 
									id,
									%1\$s
								FROM 
									karierrestatus 
								WHERE 
									id IN (%2\$s) 
								ORDER BY 
									%1\$s",
								$_SESSION['s_sprache'],
								implode(",", $a_karierrestatus));

				$karierrestatus = mysql_db_query($database_db, $sql, $praktdbslave);
				
				while ($row = mysql_fetch_array($karierrestatus, MYSQL_NUM)) {
					$rueckgabe .= '					<option value="'.$row[0].'"'.((count($a_karierrestatus) == 1 && in_array($row[0], $a_karierrestatus)) ? ' selected="selected"' : '').'>'.$row[1].'</option>'."\n";
				}
			}

			$rueckgabe .= '				</select>'."\n";
			$rueckgabe .= '			</p>'."\n";
			$rueckgabe .= '			<p>'."\n";
			$rueckgabe .= '				<label for="bereich">Art des Gesuches:</label>'."\n";
			$rueckgabe .= '				<select id="bereich" name="bereich">'."\n";
			$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";
			
			if (is_array( $s_ergebnisarry['sucheingabe']['bereich'])) {
				foreach ($spaltenarray as $spaltenkey => $spaltenvalue) {
					if (in_array($spaltenkey, $s_ergebnisarry['sucheingabe']['bereich'])) {
						$rueckgabe .= '					<option value="'.$spaltenkey.'"'.(count($s_ergebnisarry['sucheingabe']['bereich']) == 1 ? ' selected="selected"' : '').'>'.$spaltenvalue.'</option>'."\n";
					}
				}
			}

			$rueckgabe .= '				</select>'."\n";
			$rueckgabe .= '			</p>'."\n";
			$rueckgabe .= '			<p>'."\n";
			$rueckgabe .= '				<label for="profilqualitaet">Profilqualit&auml;t:</label>'."\n";
			$rueckgabe .= '				<select id="profilqualitaet" name="profilqualitaet">'."\n";
			$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";
			
			if (is_array($s_ergebnisarry['sucheingabe']['profilqualitaet'])) {
				sort( $s_ergebnisarry['sucheingabe']['profilqualitaet'] );
				
				foreach ($s_ergebnisarry['sucheingabe']['profilqualitaet'] as $key => $quali ) {
					$rueckgabe .= '					<option value="'.$quali.'"'.(count($s_ergebnisarry['sucheingabe']['profilqualitaet']) == 1 ? ' selected="selected"' : '').'>gr&ouml;&szlig;er gleich '.$quali.' Punkte</option>'."\n";
				}
			}
			
			$rueckgabe .= '				</select>'."\n";
			$rueckgabe .= '			</p>'."\n";
			$rueckgabe .= '		</fieldset>'."\n";
			$rueckgabe .= '		<fieldset class="control_panel">'."\n";
			$rueckgabe .= '			<p>'."\n";
			$rueckgabe .= '				<input type="button" name="finesearchsubmit" value="Suche verfeinern" onclick="FineSearchSubmit()" />'."\n";
			$rueckgabe .= '			</p>'."\n";
			$rueckgabe .= '		</fieldset>'."\n";
			$rueckgabe .= '	</form>'."\n";

			/*if (count($s_firmenproparray['kandidatensuche']['warenkorb']) > 0) {
				$wanzahl=count($s_firmenproparray['kandidatensuche']['warenkorb']);
				$rueckgabe .=  "<a href=\"wk.phtml\">Weiter zu meinen $wanzahl Kandidaten auf der Kandidatenliste</a>\n";
			}
			$rueckgabe .=  "<p><a href=\"wk.phtml?gesamt=true&dest=mailing\">".count($s_ergebnisarry['sucheingabe']['userid'])." gefundene Kandidaten auf der Kandidatenliste notieren</a></p>\n";*/
		}
		
		$ajaxtype = "results";
	} else {
		$ajaxtype = "warenkorbanzeige";
	}

	// karierrestatus array bauen //
	$karierrestatus = array();
	$karierrestatusresult = mysql_db_query($database_db,'SELECT id, german FROM karierrestatus',$praktdbslave);
	
	while ($result_row = mysql_fetch_array($karierrestatusresult, MYSQL_NUM )) {
		$karierrestatus[$result_row[0]] = $result_row[1];
	}

	// ++++++++++++++++ erste seite? +++++++++++++++++ //
	if (!$ds_count) {
		$ds_count = 0;
	}
	if (!$ds_start) {
		$ds_start = $ds_count;
	}

	// datensaetze pro seite ueberpruefen und setzen //
	if (!($ds_pro_seite == 5 || $ds_pro_seite == 10 || $ds_pro_seite == 20)) {
		$ds_pro_seite = 10;
	}

	$rueckgabe .= '	<p>Kandidaten '.($ds_count + 1).' - '.min(($ds_count + $ds_pro_seite), $num_rows).' von '.$num_rows.'</p>'."\n";
	$rueckgabe .= '	<table border="0" cellspacing="0" cellpadding="2" width="100%">'."\n";
	$rueckgabe .= '		<colgroup>'."\n";
	$rueckgabe .= '			<col width="30%">'."\n";
	$rueckgabe .= '			<col width="40%">'."\n";
	$rueckgabe .= '			<col width="30%">'."\n";
	$rueckgabe .= '		</colgroup>'."\n";
	$rueckgabe .= '		<tr>'."\n";
	$rueckgabe .= '			<td align="right" colspan="3">'.seitenausgabe2($num_rows, $ds_pro_seite, $ds_count,$ajaxtype).'</td>'."\n";
	$rueckgabe .= '		</tr>'."\n";

	// Datensaetze anzeigen
	mysql_data_seek($results, $ds_count);

	$end_ds = $ds_count + $ds_pro_seite;
	while (($ds_count < $end_ds) && ($result_row = mysql_fetch_array($results))) {
		if ($result_row['taetigkeit']) {
			if (strlen($result_row['taetigkeit']) > 55) {
				$headline = substr($result_row['taetigkeit'],0,52).'...';
			} else {
				$headline = $result_row['taetigkeit'];
			}
		} else {
			$headline = "Chiffre #".$result_row['nutzerid'];
		}
		
		$rueckgabe .= '		<tr>'."\n";
		$rueckgabe .= '			<td colspan="3">'."\n";
		$rueckgabe .= '				<fieldset style="margin:0; border:0; border-top:1px solid #F7E7CE">'."\n";
		$rueckgabe .= '					<legend>&nbsp;<strong>' . ($ds_count + 1) . '.&nbsp;' . $headline . '</strong>&nbsp;</legend>'."\n";
		$rueckgabe .= '				</fieldset>'."\n";
		$rueckgabe .= '			</td>'."\n";
		$rueckgabe .= '		</tr>'."\n";
		$rueckgabe .= '		<tr>'."\n";
		$rueckgabe .= '			<td colspan="3" style="text-align: right;">'."\n";

		if (!$s_firmenproparray['kandidatensuche']['warenkorb'][$result_row['nutzerid']]) {
			$rueckgabe .= '					<a href="javascript:WarenkorbSubmit('."'".$result_row['nutzerid']."','".$ajaxtype."','".$ds_start."'".');">Kandidat ausw&auml;hlen</a>'."\n";
		} else {
			$rueckgabe .= '					<a href="javascript:WarenkorbSubmit('."'".$result_row['nutzerid']."','".$ajaxtype."','".$ds_start."'".');">Kandidat entfernen</a>'."\n";
		}
	
		$rueckgabe .= '			</td>'."\n";
		$rueckgabe .= '		</tr>'."\n";
	
		// Bild heraussuchen
		$sql = sprintf("SELECT 
							id 
						FROM 
							bewerbungsfoto 
						WHERE 
							nutzerid = '%1\$d'",
						$result_row['nutzerid']);
		$result = mysql_db_query($database_db, $sql, $praktdbslave);
		$photo = mysql_num_rows($result) > 0 ? '<img src="/community/passbild.php?id='.mysql_result($result, 0, "id").'" width="120" alt="Bewerbungsfoto" style="text-align: right;" />' : '';
	
		$rueckgabe .= '		<tr>'."\n";
		$rueckgabe .= '			<td>Alter:</td>'."\n";
		$rueckgabe .= '			<td>'.$result_row['kandidatenalter'].' Jahre</td>'."\n";
		$rueckgabe .= '			<td rowspan="7" style="text-align: right;">'."\n";
		/*if (!$s_firmenproparray['kandidatensuche']['warenkorb'][$result_row['nutzerid']]) {
			$rueckgabe .= '
      <span align="right"><a href="javascript:WarenkorbSubmit('."'".$result_row['nutzerid']."','".$ajaxtype."','".$ds_start."'".');">Kandidat ausw&auml;hlen</a><br/><br/></span>';
		} else {
			$rueckgabe .= '
      <span align="right"><a href="javascript:WarenkorbSubmit('."'".$result_row['nutzerid']."','".$ajaxtype."','".$ds_start."'".');">Kandidat entfernen<br/><br/></a></span>';
		}*/

		$rueckgabe .= '			</td>'."\n";
		$rueckgabe .= '		</tr>'."\n";
	
		// Geschlecht
		$geschlecht = $result_row['geschlecht'] == 'm' ? 'm&auml;nnlich' : 'weiblich';
	
		$rueckgabe .= '		<tr>'."\n";
		$rueckgabe .= '			<td>Geschlecht:</td>'."\n";
		$rueckgabe .= '			<td>'.$geschlecht.'</td>'."\n";
		$rueckgabe .= '		</tr>'."\n";
	
		// Karierrestatus
		$rueckgabe .= '		<tr>'."\n";
		$rueckgabe .= '			<td>Karierrestatus:</td>'."\n";
		$rueckgabe .= '			<td>'.$karierrestatus[$result_row['karierrestatus']].'</td>'."\n";
		$rueckgabe .= '		</tr>'."\n";
	
		// Raum
		$rueckgabe .= '		<tr>'."\n";
		$rueckgabe .= '			<td>Raum:</td>'."\n";
		$rueckgabe .= '			<td>';
		
		if ($result_row['land'] > 0) {
			$landid = $result_row['land'];
			$land = mysql_db_query($database_db, 'SELECT german FROM land WHERE id = '.$landid, $praktdbslave);
	
			if ($result_row['bundesland'] > 0) {
				$bundeslandid = $result_row['bundesland'];
				$bland = mysql_db_query($database_db, 'SELECT '.$_SESSION['s_sprache'].' FROM bundesland WHERE id = '.$bundeslandid, $praktdbslave);
				$rueckgabe .= mysql_result($bland, 0, $_SESSION['s_sprache']) . ',&nbsp;' . mysql_result($land, 0, $_SESSION['s_sprache']);
			} else {
				$rueckgabe .= mysql_result($land, 0, $_SESSION['s_sprache']);
			}
		}

		// Ort
		$rueckgabe .= '</td>'."\n";
		$rueckgabe .= '		</tr>'."\n";
		$rueckgabe .= '		<tr>'."\n";
		$rueckgabe .= '			<td>Ort:</td>'."\n";
		$rueckgabe .= '			<td>'.$result_row['ort'].'</td>'."\n";
		$rueckgabe .= '		</tr>'."\n";
	
		if ($result_row['studiengang'] > 0) {
			// Studiengang
			$studiengangid = $result_row['studiengang'];
			$studiengang = mysql_db_query($database_db, 'SELECT studiengang FROM studiengaenge WHERE id = '.$studiengangid,$praktdbslave);
	
			$rueckgabe .= '		<tr>'."\n";
			$rueckgabe .= '			<td>Studium:</td>'."\n";
			$rueckgabe .= '			<td>'.mysql_result($studiengang, 0, "studiengang").'</td>'."\n";
			$rueckgabe .= '		</tr>'."\n";
		}
			
		// Aktion
		$rueckgabe .= '		<tr>'."\n";
		$rueckgabe .= '			<td>Aktion:</td>'."\n";
	    $rueckgabe .= '			<td><a href="javascript: Load_SearchData(\'detail\', \'\', \''.$ajaxtype.'\', \''.$result_row['nutzerid'].'\', \''.$ds_start.'\')">Kandidatendetails</a></td>'."\n";
		$rueckgabe .= '		</tr>'."\n";
	
		$rueckgabe .= '		<tr>'."\n";
		$rueckgabe .= '			<td colspan="3"><hr style="height: 1px; color: #F7E7CE; background-color: #F7E7CE; border: none;" /></td>'."\n";
		$rueckgabe .= '		</tr>'."\n";
		$rueckgabe .= '		<tr>'."\n";
		$rueckgabe .= '			<td colspan="3" valign="top"><br /></td>'."\n";
		$rueckgabe .= '		</tr>'."\n";
	
		$ds_count++;
	}

	$rueckgabe .= '		<tr>'."\n";
	$rueckgabe .= '			<td>'."\n";
	
	if ($ds_start > 9) {
		$rueckgabe .=  '				<input type="button" value="&lt;&lt;" onclick="Load_SearchData(\''.$ajaxtype.'\',document.getElementById(\'sortierung\').value,document.getElementById(\'ksuchstring\').value,document.getElementById(\'categoryids\').value,\''.($ds_start - 10).'\');" />'."\n";
	}
	
	$rueckgabe .=  '		</td>'."\n";
	$rueckgabe .=  '		<td style="text-align: center;">'."\n";
	
	if ($fineselect) {
		if (isset($_REQUEST['sortierung']) && (!empty($_REQUEST['sortierung']) || $_REQUEST['sortierung'] != 'keine')) {
			$rueckgabe .= '				<a href="javascript:Load_SearchData(\'categories\',document.getElementById(\'sortierung\').value,document.getElementById(\'ksuchstring\').value)">Sortierung</a>'."\n";
		} else {
			$rueckgabe .= '				<button type="button" name="back" onclick="location.href = \'/kandidatensuche/\'"><span><span><span>zur Eingabemaske</span></span></span></button>'."\n";
		}
	} else {
		$rueckgabe .= '				<a href="/home/firmen/suchen/wk.phtml">zur Kandidatenliste</a>'."\n";
	}
	
	$rueckgabe .= '			</td>'."\n";
	$rueckgabe .= '			<td style="text-align: right;">'."\n";
	
	if (($ds_start + 10) < $num_rows) {
		$rueckgabe .=  '				<input type="button" value="&lt;&lt;" onclick="Load_SearchData(\''.$ajaxtype.'\',document.getElementById(\'sortierung\').value,document.getElementById(\'ksuchstring\').value,document.getElementById(\'categoryids\').value,\''.($ds_start + 10).'\');" />'."\n";
	}

	$rueckgabe .= '			</td>'."\n";
	$rueckgabe .= '		</tr>'."\n";
	$rueckgabe .= '	</table>'."\n";

	return $rueckgabe;
}



// Start *************************************
srand((double) microtime() * 1000000);
$num = rand(0, (count($dbarray) - 1));

$praktdbmaster = @mysql_pconnect($dbarray[0]['host'],$dbarray[0]['mysqluser'],$dbarray[0]['mysqlpassword']);
$praktdbslave = @mysql_pconnect($dbarray[$num]['host'],$dbarray[$num]['mysqluser'],$dbarray[$num]['mysqlpassword']);

$sucharray = explode(' ',$_REQUEST['suchstring']);

foreach ($sucharray as $suchvalpart) {
	if ($suchvalpart{0} != "+" && $suchvalpart{0} != "-" && $suchvalpart{0} != "~"  && $suchvalpart{0} != "*") {
		$suchvalpart = "+".$suchvalpart;
	}
	
	$sucharrayfinal[] = $suchvalpart;
}

$suchstringfinal = implode(' ', $sucharrayfinal);

/*
if ($_REQUEST['sortierung'] == 'warenkorbanzeige') {
	session_name(praktika_id);
	session_save_path("/home/praktika/sessions");
	session_start();
	$wk_db_array = array_keys($s_firmenproparray['kandidatensuche']['warenkorb']);

	$sqlvar = implode(",",$wk_db_array);

	$sql = "SELECT `nutzerid` , `ort` , `land` , `bundesland` , `grossraum` , `kandidatenalter`, `gesuch_spalte`, `gesuch_berufsfeld`, tmp_kandidatensuche.`studiengang`, `gesuch_sprachkenntnisse1`, `gesuch_sprachkenntnisse2`, `gesuch_sprachkenntnisse3` , `geschlecht` , `karierrestatus` , `profilquali`, `lastlogin` FROM `tmp_kandidatensuche` WHERE nutzerid IN (".$sqlvar.")";
	$results=mysql_db_query($database_temp, $sql, $praktdbmaster);
	$num_rows = mysql_num_rows($results);

	if ($num_rows > 0) {
		$rueckgabe .= suchergebnis($_REQUEST['ds_count'],$num_rows,false);
	} else {
		$rueckgabe .= "Es wurden keine Kandidaten gefunden, die auf Ihre Sucheingabe passen, bitte versuchen Sie es erneut.";
	}
	
	// Neuaufbau des Kandidatensuch array bei älterer Buchung
	mysql_data_seek($results, 0);
	while ( $result_id = mysql_fetch_array ( $results, MYSQL_ASSOC )) {
		$new_wk_array[$result_id['nutzerid']] = true;
	}
	if (count($new_wk_array) > 0) {
		$s_firmenproparray['kandidatensuche']['warenkorb'] = $new_wk_array;
	}
} else*/if (isset($_REQUEST['sortierung']) && !empty($_REQUEST['sortierung']) && $_REQUEST['sortierung'] != 'keine') {
	switch ($_REQUEST['sortierung']) {
		case 'einsatzgebiet':
			$sql = '	SELECT
							berufsfelder.id,
							berufsfelder.german AS kategorie,
							COUNT(tmp_kandidatensuche.gesuch_berufsfeld) AS anzahl
						FROM
							tmp_kandidatensuche,
							prakt2.berufsfelder
						WHERE
							MATCH (volltext) AGAINST (\''.$suchstringfinal.'\' IN BOOLEAN MODE)
							AND berufsfelder.id = tmp_kandidatensuche.gesuch_berufsfeld
						GROUP BY
							gesuch_berufsfeld
						ORDER BY
							berufsfelder.german';
			break;
		case 'studienrichtung':
			$sql = '	SELECT
							studienrichtungen.id,
							studienrichtungen.german AS kategorie,
							COUNT(tmp_kandidatensuche.studiengang) AS anzahl
						FROM
							tmp_kandidatensuche,
							prakt2.studiengaenge,
							prakt2.studienrichtungen
						WHERE
							MATCH (volltext) AGAINST (\''.$suchstringfinal.'\' IN BOOLEAN MODE)
							AND studiengaenge.id = tmp_kandidatensuche.studiengang
							AND studiengaenge.richtungid = studienrichtungen.id
						GROUP BY
							richtungid
						ORDER BY
							studienrichtungen.german';
			break;
		case 'semester':
			$sql = '	SELECT
							semester AS id,
							semester AS kategorie,
							COUNT(tmp_kandidatensuche.semester) AS anzahl
						FROM
							tmp_kandidatensuche
						WHERE
							MATCH (volltext) AGAINST (\''.$suchstringfinal.'\' IN BOOLEAN MODE)
							AND semester > 0
						GROUP BY
							semester
						ORDER BY
							semester';
			break;
		case 'karierrestatus':
			$sql = '	SELECT
							karierrestatus.id,
							karierrestatus.german AS kategorie,
							COUNT(tmp_kandidatensuche.karierrestatus) AS anzahl
						FROM
							tmp_kandidatensuche,
							prakt2.karierrestatus
						WHERE
							MATCH (volltext) AGAINST (\''.$suchstringfinal.'\' IN BOOLEAN MODE)
							AND karierrestatus.id = tmp_kandidatensuche.karierrestatus
						GROUP BY
							karierrestatus
						ORDER BY
							karierrestatus.anzeigeorder';
			break;
		case 'alter':
			$sql = '	SELECT
							kandidatenalter AS id,
							kandidatenalter AS kategorie,
							COUNT(tmp_kandidatensuche.kandidatenalter) AS anzahl
						FROM
							tmp_kandidatensuche
						WHERE
							MATCH (volltext) AGAINST (\''.$suchstringfinal.'\' IN BOOLEAN MODE)
						GROUP BY
							kandidatenalter
						ORDER BY
							kandidatenalter';
			break;
		case 'bundesland':
			$sql = '	SELECT
							bundesland.id,
							bundesland.german AS kategorie,
							COUNT(tmp_kandidatensuche.bundesland) AS anzahl
						FROM
							tmp_kandidatensuche,
							prakt2.bundesland
						WHERE
							MATCH (volltext) AGAINST (\''.$suchstringfinal.'\' IN BOOLEAN MODE)
							AND bundesland.id = tmp_kandidatensuche.bundesland
							AND land = 68
						GROUP BY
							bundesland
						ORDER BY
							bundesland.german';
			break;
		case 'land':
			$sql = '	SELECT
							land.id,
							land.german AS kategorie,
							COUNT(tmp_kandidatensuche.land) AS anzahl
						FROM
							tmp_kandidatensuche,
							prakt2.land
						WHERE
							MATCH (volltext) AGAINST (\''.$suchstringfinal.'\' IN BOOLEAN MODE)
							AND land.id = tmp_kandidatensuche.land
						GROUP BY
							land
						ORDER BY land.GERMAN';
			break;
	}
	
	$rueckgabe = '	<form action="" id="checkform">'."\n";
	$rueckgabe .= '		<input type="hidden" name="sortierung" value="'.$sortierung.'" />'."\n";
	
	$resultquery = mysql_db_query($database_temp,$sql,$praktdbmaster);
	
	if ($resultquery && mysql_numrows($resultquery) > 0) {
		while ($row = mysql_fetch_array($resultquery, MYSQL_NUM)) {
			$rueckgabe .= '		<p><input type="checkbox" name="'.$sortierung.$row[0].'" value="'.$row[0].'" />&nbsp;<a href="javascript: LinkSubmit('.$_REQUEST['sortierung'].','.$row[0].')">'.$row[1].'</a>&nbsp;('.$row[2].')</p>';
		}
		$rueckgabe .= '		<p class="control_panel"><button type="button" value="Auswahl suchen" onclick="CheckboxSubmit()"><span><span><span>Auswahl suchen</span></span></span></button></p>'."\n";
	} else {
		$rueckgabe .= '		<p class="error">Es wurden keine Kandidaten gefunden, die auf Ihre Sucheingabe passen, bitte versuchen Sie es erneut.</p>'."\n";
	}
	
	$rueckgabe .= '		<p class="control_panel"><button type="button" name="back" onclick="location.href = \'/kandidatensuche/\'"><span><span><span>zur Eingabemaske</span></span></span></button></p>'."\n";
} else {
	if (is_array($categoyid)) {
		$seriids = implode(',',$categoyid);
	}

/*	session_name(praktika_id);
	session_save_path("/home/praktika/sessions");
	session_start();*/

	$s_suchstring = $_REQUEST['suchstring'];

	if (!$s_firmenproparray) {
		//session_register("s_firmenproparray");
		$s_firmenproparray['kandidatensuche']['finesearch']['values'] = array();
		if (is_array($_REQUEST['finesearch'])) $s_firmenproparray['kandidatensuche']['finesearch']['values'] = $_REQUEST['finesearch'];
		$s_firmenproparray['kandidatensuche']['finesearch']['category'] = $_REQUEST['category'];
		$s_firmenproparray['kandidatensuche']['finesearch']['category'] = $_REQUEST['category'];
		$s_firmenproparray['kandidatensuche']['finesearch']['seriids'] = $seriids;
	}

	if ($s_firmenproparray['kandidatensuche']['finesearch']['category'] != $_REQUEST['category']) $s_firmenproparray['kandidatensuche']['finesearch']['values'] = array();
	if ($s_firmenproparray['kandidatensuche']['finesearch']['seriids'] != $seriids) $s_firmenproparray['kandidatensuche']['finesearch']['values'] = array();
	$s_firmenproparray['kandidatensuche']['finesearch']['category'] = $_REQUEST['category'];
	$s_firmenproparray['kandidatensuche']['finesearch']['seriids'] = $seriids;

	if (is_array($_REQUEST['finesearch'])) {
		$errorcount = 0;
		foreach ($_REQUEST['finesearch'] as $finesearchkey => $finesearchvalue) {
			if ($finesearchvalue != $s_firmenproparray['kandidatensuche']['finesearch']['values'][$finesearchkey])
			$errorcount++;
		}
	}

	if (is_array($_REQUEST['finesearch']) && $errorcount != 0) $s_firmenproparray['kandidatensuche']['finesearch']['values']=$_REQUEST['finesearch'];

	switch ($_REQUEST['category']) {
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

	if ( is_array( $s_firmenproparray['kandidatensuche']['finesearch']['values'] ) ) {
		foreach ($s_firmenproparray['kandidatensuche']['finesearch']['values'] as $finesearchkey => $finesearchvalue) {
			if ($finesearchvalue != "")
			switch ($finesearchkey) {
				case "land":
					$sqlext .= " AND land = '".$finesearchvalue."'";
					break;
				case "bundesland":
					$sqlext .= " AND bundesland = '".$finesearchvalue."'";
					break;
				case "grossraum":
					$sqlext .= " AND grossraum = '".$finesearchvalue."'";
					break;
				case "plz":
					$sqlext .= " AND plz LIKE '".$finesearchvalue."%'";
					break;
				case "berufsfeld":
					$sqlext .= " AND gesuch_berufsfeld = '".$finesearchvalue."'";
					break;
				case "studiengang":
					$sqlext .= " AND tmp_kandidatensuche.studiengang = '".$finesearchvalue."'";
					break;
				case "lastlogin":
					$sqlext .= " AND lastlogin > '".$finesearchvalue."'";
					break;
				case "sprache":
					$sqlext .= " AND (gesuch_sprachkenntnisse1 = '".$finesearchvalue."' OR gesuch_sprachkenntnisse2 = '$finesearchvalue' OR gesuch_sprachkenntnisse3 = '$finesearchvalue')";
					break;
				case "alter":
					$altersbereich = explode ("-",$finesearchvalue);
					$sqlext .= " AND kandidatenalter >= '".$altersbereich[0]."' AND kandidatenalter <= '".$altersbereich[1]."'";
					break;
				case "geschlecht":
					$sqlext .= " AND geschlecht = '".$finesearchvalue."'";
					break;
				case "karierrestatus":
					$sqlext .= " AND karierrestatus = '".$finesearchvalue."'";
					break;
				case "bereich":
					$sqlext .= " AND gesuch_spalte = '".$finesearchvalue."'";
					break;
				case "profilqualitaet":
					$sqlext .= " AND profilquali >= '".$finesearchvalue."'";
					break;
			}
		}
	}

	$sql = sprintf("SELECT
						nutzerid,
						plz,
						ort,
						land,
						bundesland,
						grossraum,
						kandidatenalter,
						gesuch_spalte,
						gesuch_berufsfeld, 
						studiengang,
						gesuch_sprachkenntnisse1,
						gesuch_sprachkenntnisse2,
						gesuch_sprachkenntnisse3,
						geschlecht,
						karierrestatus,
						profilquali,
						lastlogin
					FROM 
						%1\$s 
					WHERE 
						MATCH (volltext) AGAINST ('%2\$s' IN BOOLEAN MODE) 
						%3\$s",
					'tmp_kandidatensuche'.$exttable,
					$suchstringfinal,
					$sqlext);//var_dump($sql);
	$results = mysql_db_query($database_temp, $sql, $praktdbmaster);
	
	$num_rows = mysql_num_rows($results);

	if ($num_rows > 0) {
		$rueckgabe .= suchergebnis($_REQUEST['ds_count'], $num_rows, true);
	} else {
		$rueckgabe .= '	<p class="error">Es wurden keine Kandidaten gefunden, die auf Ihre Sucheingabe passen, bitte versuchen Sie es erneut.</p>'."\n";
	}
}

$submitarray = array();
$submitarray['mainvalue'] = $rueckgabe;

$content .= '<?xml version="1.0" encoding="ISO-8859-1"?>'."\n";
$content .= '<root>'."\n";
$content .= '	<htmlinhalte>'."\n";

foreach ($submitarray as $arraykey => $arrayelement) {
	if ( !empty($arrayelement) ) {
		$arrayelement = str_split($arrayelement,4096);
		$i = 0;
		foreach ($arrayelement as $arrayvalue) {
			$content .= '		<'.$arraykey.$i.'>'.htmlspecialchars($arrayvalue).'</'.$arraykey.$i.'>'."\n";
			$i++;
		}
	}
}

//$content .= '		<wkcount>'.count($s_firmenproparray['kandidatensuche']['warenkorb']).'</wkcount>'."\n";
$content .= '	</htmlinhalte>'."\n";
$content .= '</root>'."\n";

header('Content-Type: text/xml');
header('Content-Length: '.strlen($content));

echo $content;
//echo $_REQUEST['suchstring'];
?>