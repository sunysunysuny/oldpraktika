<?php
//  praktika.de ajax File - KANDIDATENAUSGABE -  WENN FIRMEN SUCHEN

require('/home/praktika/etc/config.inc.php');

$spaltenarray = init_spaltenarray();

$rueckgabe = '<script type="text/javascript" src="/scripts/ajax/search_candidates.js"></script>';

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
		$content .= '<a href="#" onclick="Load_SearchData(\''.$ajaxtype.'\', document.getElementById(\'sortierung\').value,document.getElementById(\'ksuchstring\').value,document.getElementById(\'categoryids\').value,\''.($dcount).'\');">&lt;</a>&nbsp;';
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
			$content .= '<a href="#" onclick="Load_SearchData(\''.$ajaxtype.'\',document.getElementById(\'sortierung\').value,document.getElementById(\'ksuchstring\').value,document.getElementById(\'categoryids\').value,\''.$fwd.'\');">'.$i.'</a>'.$slash;
		}
	}

	if ($seitenzaehler < $Seiten) {
		$content .= '&nbsp;<a href="#" onclick="Load_SearchData(\''.$ajaxtype.'\',document.getElementById(\'sortierung\').value,document.getElementById(\'ksuchstring\').value,document.getElementById(\'categoryids\').value,\''.($fwd+$ds_pro_seite).'\');">&gt;</a>';
	}

	return $content;
}

function suchergebnis($ds_count, $num_rows, $fineselect) {
	global $results,$praktdbmaster,$praktdbslave,$database_db,$database_temp,$database_comm,$spaltenarray,$language,$stellenausgabe,$suchstring,$wk_inhalt;

	if (!isset($_SESSION['s_sprache'])) {
		$_SESSION['s_sprache'] = 'german';
	}

	if ($fineselect == true) {
		$aktdate = strtotime(date('Y-m-d'));

		$i = 0;
		while (($i < 500) && ($result_inhalt = mysql_fetch_array($results, MYSQL_ASSOC))) {
			$suchinhalte['userid'][$result_inhalt['nutzerid']] = true;
			
			foreach ($result_inhalt as $key => $value) {
				$suchinhalte[$key][$value] = true;
			}
			
			$i++;
		}

		unset($_SESSION['s_ergebnisarry']['sucheingabe']);

		$_SESSION['s_ergebnisarry']['sucheingabe']['userid'] = array_keys($suchinhalte['userid'] );
		
		if (is_array($suchinhalte['land'])) {
			$_SESSION['s_ergebnisarry']['sucheingabe']['land'] = array_keys($suchinhalte['land'] );
		}
		if (is_array($suchinhalte['bundesland'])) {
			$_SESSION['s_ergebnisarry']['sucheingabe']['bundesland'] = array_keys($suchinhalte['bundesland'] );
		}
		if (is_array($suchinhalte['grossraum'])) {
			$_SESSION['s_ergebnisarry']['sucheingabe']['grossraum'] = array_keys($suchinhalte['grossraum'] );
		}
		if (is_array($suchinhalte['plz'])) {
			$_SESSION['s_ergebnisarry']['sucheingabe']['plz'] = array_keys($suchinhalte['plz'] );
		}
		if (is_array($suchinhalte['einsatzgebiet'])) {
			$_SESSION['s_ergebnisarry']['sucheingabe']['berufsfeld'] = array_keys($suchinhalte['einsatzgebiet'] );
		}
		if (is_array($suchinhalte['bereich'])) {
			$_SESSION['s_ergebnisarry']['sucheingabe']['bereich'] = array_keys($suchinhalte['bereich'] );
		}
		if (is_array($suchinhalte['geschlecht'])) {
			$_SESSION['s_ergebnisarry']['sucheingabe']['geschlecht'] = array_keys($suchinhalte['geschlecht'] );
		}
		if (is_array($suchinhalte['karierrestatus'])) {
			$_SESSION['s_ergebnisarry']['sucheingabe']['karierrestatus'] = array_keys($suchinhalte['karierrestatus'] );
		}
		if (is_array($suchinhalte['profilquali'])) {
			$_SESSION['s_ergebnisarry']['sucheingabe']['profilqualitaet'] = array_keys($suchinhalte['profilquali'] );
		}
		if (is_array($suchinhalte['lastlogin'])) {
			$_SESSION['s_ergebnisarry']['sucheingabe']['lastlogin'] = array_keys($suchinhalte['lastlogin'] );
		}
		if (is_array($suchinhalte['kandidatenalter'])) {
			$_SESSION['s_ergebnisarry']['sucheingabe']['kandidatenalter'] = array_keys($suchinhalte['kandidatenalter'] );
		}
		if (is_array($suchinhalte['karierrestatus'])) {
			$_SESSION['s_ergebnisarry']['sucheingabe']['karierrestatus'] = array_keys($suchinhalte['karierrestatus'] );
		}
		if (is_array($suchinhalte['studiengang'])) {
			$_SESSION['s_ergebnisarry']['sucheingabe']['studiengang'] = array_keys($suchinhalte['studiengang'] );
		}
		if (is_array($suchinhalte['gesuch_sprachkenntnisse'])) {
			$_SESSION['s_ergebnisarry']['sucheingabe']['praktikumssprache'] = array_keys($suchinhalte['gesuch_sprachkenntnisse'] );
		}

		if (is_array($_SESSION['s_ergebnisarry']['sucheingabe']['kandidatenalter'])) {
			foreach ($_SESSION['s_ergebnisarry']['sucheingabe']['kandidatenalter'] as $altervalues) {
				if ($altervalues >= 15 && $altervalues <= 18) {
					$_SESSION['s_ergebnisarry']['sucheingabe']['alter'][] = '15-18';
				} elseif ($altervalues >= 19 && $altervalues <= 23) {
					$_SESSION['s_ergebnisarry']['sucheingabe']['alter'][] = '19-23';
				} elseif ($altervalues >= 24 && $altervalues <= 28) {
					$_SESSION['s_ergebnisarry']['sucheingabe']['alter'][] = '24-28';
				} elseif ($altervalues >= 29 && $altervalues <= 35) {
					$_SESSION['s_ergebnisarry']['sucheingabe']['alter'][] = '29-35';
				} elseif ($altervalues >= 36 && $altervalues <= 65) {
					$_SESSION['s_ergebnisarry']['sucheingabe']['alter'][] = '36-65';
				}
			}
			unset($_SESSION['s_ergebnisarry']['sucheingabe']['kandidatenalter']);
		}

		if (is_array($_SESSION['s_ergebnisarry']['sucheingabe']['lastlogin'])) {
			foreach ($_SESSION['s_ergebnisarry']['sucheingabe']['lastlogin'] as $lastloginvalues) {
				if ( $lastloginvalues >= ($aktdate - 604800) ) {
					$_SESSION['s_ergebnisarry']['sucheingabe']['suchlastlogin'][] = ($aktdate - 604800);
				} elseif ($lastloginvalues <= ($aktdate - 604800) && $lastloginvalues >= ($aktdate - 1209600)) {
					$_SESSION['s_ergebnisarry']['sucheingabe']['suchlastlogin'][] = ($aktdate - 1209600);
				} elseif ($lastloginvalues <= ($aktdate - 1209600) && $lastloginvalues >= ($aktdate - 2592000)) {
					$_SESSION['s_ergebnisarry']['sucheingabe']['suchlastlogin'][] = ($aktdate - 2592000);
				} elseif ($lastloginvalues <= ($aktdate - 2592000) && $lastloginvalues >= ($aktdate - 5184000)) {
					$_SESSION['s_ergebnisarry']['sucheingabe']['suchlastlogin'][] = ($aktdate - 5184000);
				} elseif ($lastloginvalues <= ($aktdate - 5184000) && $lastloginvalues >= ($aktdate - 7776000)) {
					$_SESSION['s_ergebnisarry']['sucheingabe']['suchlastlogin'][] = ($aktdate - 7776000);
				} elseif ($lastloginvalues <= ($aktdate - 7776000) && $lastloginvalues >= ($aktdate - 15552000)) {
					$_SESSION['s_ergebnisarry']['sucheingabe']['suchlastlogin'][] = ($aktdate - 15552000);
				}
			}
			unset($_SESSION['s_ergebnisarry']['sucheingabe']['lastlogin']);
		}

		if (!$wk_inhalt) {
			$rueckgabe .= '	<p class="control"><a class="button alternative small" onclick="hideDetails(); return false;">Suchergebnis verfeinern</a></p>'."\n";
			$rueckgabe .= '	<form action="" id="vertical_slider" class="hide">'."\n";
			$rueckgabe .= '		<fieldset>'."\n";
			
			if (is_array($_SESSION['s_ergebnisarry']['sucheingabe']['land'])) {
				$rueckgabe .= '			<p>'."\n";
				$rueckgabe .= '				<label for="land">Land:</label>'."\n";
				$rueckgabe .= '				<select id="land" name="land">'."\n";
				$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";
				$rueckgabe .= '					<optgroup label="deutschsprachig">'."\n";
				
				$row[0] = 68;
				$row[1] = 'Deutschland';

				if (in_array($row[0], $_SESSION['s_ergebnisarry']['sucheingabe']['land'])) {
					$rueckgabe .= '					<option value="'.$row[0].'"'.((count($_SESSION['s_ergebnisarry']['sucheingabe']['land']) && in_array($row[0],$_SESSION['s_ergebnisarry']['sucheingabe']['land'])) ? ' selected="selected"' : '').'>'.$row[1].'</option>'."\n";
				}
				
				$row[0] = 14;
				$row[1] = '&Ouml;sterreich';
				
				if (in_array($row[0], $_SESSION['s_ergebnisarry']['sucheingabe']['land'])) {
					$rueckgabe .= '					<option value="'.$row[0].'"'.((count($_SESSION['s_ergebnisarry']['sucheingabe']['land']) && in_array($row[0],$_SESSION['s_ergebnisarry']['sucheingabe']['land'])) ? ' selected="selected"' : '').'>'.$row[1].'</option>'."\n";
				}
				
				$row[0] = 156;
				$row[1] = 'Schweiz';
				
				if (in_array($row[0], $_SESSION['s_ergebnisarry']['sucheingabe']['land'])) {
					$rueckgabe .= '					<option value="'.$row[0].'"'.((count($_SESSION['s_ergebnisarry']['sucheingabe']['land']) && in_array($row[0],$_SESSION['s_ergebnisarry']['sucheingabe']['land'])) ? ' selected="selected"' : '').'>'.$row[1].'</option>'."\n";
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
								implode(",", $_SESSION['s_ergebnisarry']['sucheingabe']['land']) );
				
				$laender = mysql_db_query($database_db, $sql, $praktdbslave);
				
				while ($row = mysql_fetch_array($laender, MYSQL_NUM)) {
					$rueckgabe .= '					<option value="'.$row[0].'"'.((count($_SESSION['s_ergebnisarry']['sucheingabe']['land']) == 1 && in_array($row[0],$_SESSION['s_ergebnisarry']['sucheingabe']['land'])) ? ' selected="selected"' : '').'>'.$row[1].'</option>'."\n";
				}
				
				$rueckgabe .= '					</optgroup>'."\n";
				$rueckgabe .= '				</select>'."\n";
				$rueckgabe .= '			</p>'."\n";
			}
			
			if (is_array($_SESSION['s_ergebnisarry']['sucheingabe']['bundesland'])) {
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
								implode(",", $_SESSION['s_ergebnisarry']['sucheingabe']['bundesland']));
				
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
								@implode(",",$land_ids));

				$landresult = mysql_db_query($database_db, $sql, $praktdbslave);

				$rueckgabe .= '			<p>'."\n";
				$rueckgabe .= '				<label for="bundesland">Bundesland:</label>'."\n";
				$rueckgabe .= '				<select id="bundesland" name="bundesland">'."\n";
				$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";

				while ($result_land = @mysql_fetch_array($landresult, MYSQL_ASSOC)) {
					$rueckgabe .= '					<optgroup label="'.$result_land[$_SESSION['s_sprache']].'">'."\n";
					
					foreach ($blandarray[$result_land['id']] as $row) {
						$rueckgabe .= '						<option value="'.$row['id'].'"'.((count($_SESSION['s_ergebnisarry']['sucheingabe']['bundesland']) == 1 && in_array($row['id'],$_SESSION['s_ergebnisarry']['sucheingabe']['bundesland'])) ? ' selected="selected"' : '').'>'.$row[$_SESSION['s_sprache']].'</option>'."\n";
					}

					$rueckgabe .= '					</optgroup>'."\n";
				}
			
				$rueckgabe .= '				</select>'."\n";
				$rueckgabe .= '			</p>'."\n";
			}
			
			if (is_array($_SESSION['s_ergebnisarry']['sucheingabe']['grossraum'])) {
				$sql = sprintf("SELECT
									id,
									grossraum
								FROM 
									grossraum 
								WHERE  
									id IN (%1\$s) 
								ORDER BY 
									grossraum",
								implode(",", $_SESSION['s_ergebnisarry']['sucheingabe']['grossraum']));

				$grossraum = mysql_db_query($database_db, $sql, $praktdbslave);
				
				$rueckgabe .= '			<p>'."\n";
				$rueckgabe .= '				<label for="grossraum">Gro&szlig;raum:</label>'."\n";
				$rueckgabe .= '				<select id="grossraum" name="grossraum">'."\n";
				$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";

				while ($row = mysql_fetch_array($grossraum, MYSQL_NUM)) {
					$rueckgabe .= '					<option value="'.$row[0].'"'.((count($_SESSION['s_ergebnisarry']['sucheingabe']['grossraum']) == 1 && in_array($row[0],$_SESSION['s_ergebnisarry']['sucheingabe']['grossraum'])) ? ' selected="selected"' : '').'>'.$row[1].'</option>'."\n";
				}

				$rueckgabe .= '				</select>'."\n";
				$rueckgabe .= '			</p>'."\n";
			}
			
			if (is_array($_SESSION['s_ergebnisarry']['sucheingabe']['plz'])) {
				$a_plz = array();
				
				foreach ($_SESSION['s_ergebnisarry']['sucheingabe']['plz'] as $value) {
					$a_plz[] = substr($value, 0, 1);
				}
				
				$a_plz = array_unique($a_plz);
			}

			if (is_array($a_plz)) {
				$rueckgabe .= '			<p>'."\n";
				$rueckgabe .= '				<label for="plz">PLZ-Gebiet:</label>'."\n";
				$rueckgabe .= '				<select id="plz" name="plz">'."\n";
				$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";

				for ($i = 0; $i < 10; $i++) {
					if ( in_array($i, $a_plz) ) {
						$rueckgabe .= '					<option value="'.$i.'"'.(count($a_plz) == 1 ? ' selected="selected"' : '').'>'.$i.'</option>'."\n";
					}				
				}

				$rueckgabe .= '				</select>'."\n";
				$rueckgabe .= '			</p>'."\n";
			}
			
			if (is_array($_SESSION['s_ergebnisarry']['sucheingabe']['berufsfeld'])) {
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
								implode(",", $_SESSION['s_ergebnisarry']['sucheingabe']['berufsfeld']));
				
				$berufsfelder = mysql_db_query($database_db, $sql, $praktdbslave);

				$rueckgabe .= '			<p>'."\n";
				$rueckgabe .= '				<label for="berufsfeld">Berufsfeld:</label>'."\n";
				$rueckgabe .= '				<select id="berufsfeld" name="berufsfeld">'."\n";
				$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";
				
				while ($row = mysql_fetch_array($berufsfelder, MYSQL_NUM)) {
					$rueckgabe .= '					<option value="'.$row[0].'"'.((count($_SESSION['s_ergebnisarry']['sucheingabe']['berufsfeld']) == 1 && in_array($row[0], $_SESSION['s_ergebnisarry']['sucheingabe']['berufsfeld'])) ? ' selected="selected"' : '').'>'.$row[1].'</option>'."\n";
				}

				$rueckgabe .= '				</select>'."\n";
				$rueckgabe .= '			</p>'."\n";
			}
			
			if (is_array($_SESSION['s_ergebnisarry']['sucheingabe']['studiengang'])) {
				$a_studiengang = $_SESSION['s_ergebnisarry']['sucheingabe']['studiengang'];
				
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
				
				$rueckgabe .= '			<p>'."\n";
				$rueckgabe .= '				<label for="studiengang">Studiengang:</label>'."\n";
				$rueckgabe .= '				<select id="studiengang" name="studiengang">'."\n";
				$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";

				while ($row = mysql_fetch_array($studiengaenge, MYSQL_NUM)) {
					$rueckgabe .= '					<option value="'.$row[0].'"'.((count($a_studiengang) == 1 && in_array($row[0], $a_studiengang)) ? ' selected="selected"' : '').'>'.$row[1].'</option>'."\n";
				}

				$rueckgabe .= '				</select>'."\n";
				$rueckgabe .= '			</p>'."\n";
			}
			
			if (is_array($_SESSION['s_ergebnisarry']['sucheingabe']['suchlastlogin'])) {
				$a_lastlogin = array_unique($_SESSION['s_ergebnisarry']['sucheingabe']['suchlastlogin']);
				$day = 86400;
				
				$days = array(7 => '7 Tage', 14 => '14 Tage', 30 => '1 Monat', 60 => '2 Monate', 90 => '3 Monate', 180 => '6 Monate');

				$rueckgabe .= '			<p>'."\n";
				$rueckgabe .= '				<label for="lastlogin">letztes Login:</label>'."\n";
				$rueckgabe .= '				<select id="lastlogin" name="lastlogin">'."\n";
				$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";

				foreach ($days as $key => $value){
					if (in_array(($aktdate - $key * $day), $a_lastlogin)) {
						$rueckgabe .= '					<option value="'.($aktdate - $key * $day).'"'.((count($a_lastlogin) == 1 && in_array(($aktdate - $key * $day), $a_lastlogin )) ? ' selected="selected"' : '').'>max. '.$value.' Tage</option>';
					}
				}

				$rueckgabe .= '				</select>'."\n";
				$rueckgabe .= '			</p>'."\n";
			}

			if (is_array($_SESSION['s_ergebnisarry']['sucheingabe']['praktikumssprache'])) {
				$a_sprache = $_SESSION['s_ergebnisarry']['sucheingabe']['praktikumssprache'];
				
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
				
				$rueckgabe .= '			<p>'."\n";
				$rueckgabe .= '				<label for="sprache">Sprachkenntnisse:</label>'."\n";
				$rueckgabe .= '				<select id="sprache" name="sprache">'."\n";
				$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";

				while ($row = mysql_fetch_array($sprachen, MYSQL_NUM)) {
					$rueckgabe .= '					<option value="'.$row[0].'"'.((count($a_sprache) == 1 && in_array($row[0], $a_sprache)) ? ' selected="selected"' : '').'>'.$row[1].'</option>'."\n";
				}

				$rueckgabe .= '				</select>'."\n";
				$rueckgabe .= '			</p>'."\n";
			}
			
			if (is_array($_SESSION['s_ergebnisarry']['sucheingabe']['alter'])) {
				$a_alter = array_unique($_SESSION['s_ergebnisarry']['sucheingabe']['alter']);
				
				$alter = array('15-18','19-23','24-28','29-35','36-65');
					
				$rueckgabe .= '			<p>'."\n";
				$rueckgabe .= '				<label for="alter">Altersgruppe:</label>'."\n";
				$rueckgabe .= '				<select id="alter" name="alter">'."\n";
				$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";

				foreach ($alter as $key) {
					if (in_array($key, $a_alter)) {
						$rueckgabe .= '					<option value="'.$key.'"'.(count($a_alter) == 1 ? ' selected="selected"' : '').'>'.$key.' Jahre</option>'."\n";
					}
				}

				$rueckgabe .= '				</select>'."\n";
				$rueckgabe .= '			</p>'."\n";
			}

			$rueckgabe .= '			<p>'."\n";
			$rueckgabe .= '				<label for="geschlecht">Geschlecht:</label>'."\n";
			$rueckgabe .= '				<select id="geschlecht" name="geschlecht">'."\n";
			$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";
			$rueckgabe .= '					<option value="m"'.((count($_SESSION['s_ergebnisarry']['sucheingabe']['geschlecht']) == 1 && in_array("m", $_SESSION['s_ergebnisarry']['sucheingabe']['geschlecht'])) ? ' selected="selected"' : '').'>m&auml;nnlich</option>'."\n";
			$rueckgabe .= '					<option value="w"'.((count($_SESSION['s_ergebnisarry']['sucheingabe']['geschlecht']) == 1 && in_array("w", $_SESSION['s_ergebnisarry']['sucheingabe']['geschlecht'])) ? ' selected="selected"' : '').'>weiblich</option>'."\n";
			$rueckgabe .= '				</select>'."\n";
			$rueckgabe .= '			</p>'."\n";
			
			if (is_array( $_SESSION['s_ergebnisarry']['sucheingabe']['karierrestatus'])) {
				$a_karierrestatus = $_SESSION['s_ergebnisarry']['sucheingabe']['karierrestatus'];
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
				
				$rueckgabe .= '			<p>'."\n";
				$rueckgabe .= '				<label for="karierrestatus">Karrierestatus:</label>'."\n";
				$rueckgabe .= '				<select id="karierrestatus" name="karierrestatus">'."\n";
				$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";

				while ($row = mysql_fetch_array($karierrestatus, MYSQL_NUM)) {
					$rueckgabe .= '					<option value="'.$row[0].'"'.((count($a_karierrestatus) == 1 && in_array($row[0], $a_karierrestatus)) ? ' selected="selected"' : '').'>'.$row[1].'</option>'."\n";
				}

				$rueckgabe .= '				</select>'."\n";
				$rueckgabe .= '			</p>'."\n";
			}

			if (is_array( $_SESSION['s_ergebnisarry']['sucheingabe']['bereich'])) {
				$rueckgabe .= '			<p>'."\n";
				$rueckgabe .= '				<label for="bereich">Art des Gesuches:</label>'."\n";
				$rueckgabe .= '				<select id="bereich" name="bereich">'."\n";
				$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";

				foreach ($spaltenarray as $spaltenkey => $spaltenvalue) {
					if (in_array($spaltenkey, $_SESSION['s_ergebnisarry']['sucheingabe']['bereich'])) {
						$rueckgabe .= '					<option value="'.$spaltenkey.'"'.(count($_SESSION['s_ergebnisarry']['sucheingabe']['bereich']) == 1 ? ' selected="selected"' : '').'>'.$spaltenvalue.'</option>'."\n";
					}
				}

				$rueckgabe .= '				</select>'."\n";
				$rueckgabe .= '			</p>'."\n";
			}

			if (is_array($_SESSION['s_ergebnisarry']['sucheingabe']['profilqualitaet'])) {
				sort( $_SESSION['s_ergebnisarry']['sucheingabe']['profilqualitaet'] );
				
				$rueckgabe .= '			<p>'."\n";
				$rueckgabe .= '				<label for="profilqualitaet">Profilqualit&auml;t:</label>'."\n";
				$rueckgabe .= '				<select id="profilqualitaet" name="profilqualitaet">'."\n";
				$rueckgabe .= '					<option value="">- Bitte w&auml;hlen -</option>'."\n";

				foreach ($_SESSION['s_ergebnisarry']['sucheingabe']['profilqualitaet'] as $key => $quali ) {
					$rueckgabe .= '					<option value="'.$quali.'"'.(count($_SESSION['s_ergebnisarry']['sucheingabe']['profilqualitaet']) == 1 ? ' selected="selected"' : '').'>gr&ouml;&szlig;er gleich '.$quali.' Punkte</option>'."\n";
				}
				
				$rueckgabe .= '				</select>'."\n";
				$rueckgabe .= '			</p>'."\n";
			}
			
			$rueckgabe .= '		</fieldset>'."\n";
			$rueckgabe .= '		<fieldset class="control_panel">'."\n";
			$rueckgabe .= '			<p>'."\n";
			$rueckgabe .= '				<input type="button" name="finesearchsubmit" class="button red small" value="Suche verfeinern" onclick="FineSearchSubmit()" />'."\n";
			$rueckgabe .= '			</p>'."\n";
			$rueckgabe .= '		</fieldset>'."\n";
			$rueckgabe .= '	</form>'."\n";
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

	$seiten = '	<p>'.seitenausgabe2($num_rows, $ds_pro_seite, $ds_count,$ajaxtype).'</p>'."\n";
	$rueckgabe .= $seiten;

	// Datensaetze anzeigen
	mysql_data_seek($results, $ds_count);

	$end_ds = $ds_count + $ds_pro_seite;
	while (($ds_count < $end_ds) && ($result_row = mysql_fetch_array($results))) {
		$rueckgabe .= '<div class="kandidatensuche_kandidat">'."\n";

		if ($result_row['taetigkeit']) {
			if (strlen($result_row['taetigkeit']) > 55) {
				$headline = substr($result_row['taetigkeit'],0,52).'...';
			} else {
				$headline = $result_row['taetigkeit'];
			}
		} else {
			$headline = "Chiffre #".$result_row['nutzerid'];
		}
		
		$rueckgabe .= '	<div class="daten">'."\n";
		$rueckgabe .= '		<p class="chiffre"><strong>'.($ds_count + 1).'. '.$headline.'</strong></p>'."\n";

		$geschlecht = $result_row['geschlecht'] == 'm' ? 'm&auml;nnlich' : 'weiblich';
	
		$rueckgabe .= '		<p class="left_cell">Alter:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell">'.$result_row['kandidatenalter'].' Jahre</p>'."\n";
		/*$rueckgabe .= '	<p class="left_cell">Geschlecht:</p>'."\n";
		$rueckgabe .= '	<p class="right_cell">'.$geschlecht.'</p>'."\n";*/
		$rueckgabe .= '		<p class="left_cell">Karrierestatus:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell">'.$karierrestatus[$result_row['karierrestatus']].'</p>'."\n";

		if ($result_row['land'] > 0) {
			$landid = $result_row['land'];
			$land = mysql_db_query($database_db, 'SELECT german FROM land WHERE id = '.$landid, $praktdbslave);
	
			if ($result_row['bundesland'] > 0) {
				$bundeslandid = $result_row['bundesland'];
				$bland = mysql_db_query($database_db, 'SELECT '.$_SESSION['s_sprache'].' FROM bundesland WHERE id = '.$bundeslandid, $praktdbslave);
				$raum = mysql_result($bland, 0, $_SESSION['s_sprache']) . ',&nbsp;' . mysql_result($land, 0, $_SESSION['s_sprache']);
			} else {
				$raum = mysql_result($land, 0, $_SESSION['s_sprache']);
			}
		}
		
		$rueckgabe .= '		<p class="left_cell">Raum:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell">'.$raum.'</p>'."\n";
		$rueckgabe .= '		<p class="left_cell">Ort:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell">'.$result_row['ort'].'</p>'."\n";
	
		if ($result_row['studiengang'] > 0) {
			$studiengangid = $result_row['studiengang'];
			$studiengang = mysql_db_query($database_db, 'SELECT studiengang FROM studiengaenge WHERE id = '.$studiengangid,$praktdbslave);
	
			$rueckgabe .= '		<p class="left_cell">Studium:</p>'."\n";
			$rueckgabe .= '		<p class="right_cell">'.mysql_result($studiengang, 0, "studiengang").'</p>'."\n";
		}

        $rueckgabe .= '		<p class="left_cell">Zuletzt Aktiv:</p>'."\n";
        $rueckgabe .= '		<p class="right_cell">'.date("d.m.Y", $result_row['lastlogin']).'</p>'."\n";

		$rueckgabe .= '	</div>'."\n";		
		
		$rueckgabe .= '	<div class="photo_quali">'."\n";
		
		$num_rows = 0;
		// check auf buchung der kandidatensuche
		if (isset($_SESSION['s_firmenid'])) {
			$sql = sprintf("SELECT 
								kandidatensuche_ts 
							FROM 
								firmen_prop_assign 
							WHERE 
								firmenid = %1\$d
								AND FROM_UNIXTIME(kandidatensuche_ts) > CURRENT_TIMESTAMP",
							$_SESSION['s_firmenid']);
			mysql_db_query($database_db, $sql, $praktdbslave);
			$result = mysql_db_query($database_db, $sql, $praktdbslave);
			$num_rows = mysql_num_rows( $result );	
		
			// Wer darf was sehen
			if ($num_rows > 0) {
				/*Profile quality*/
				$w1 = (120 * intval($result_row['profilquali'])) / 100; //H�he des Farbverlaufes in Pixel
				$w2 = 60; //H�he des Farbverlaufes in Pixel
				
				//Startfarbe im RGB Format
				$r1 = 255; //R
				$g1 = 16; //G
				$b1 = 14; //B
				
				//Endfarbe im RGB Format
				$r2 = 246; //R
				$g2 = 211; //G
				$b2 = 0; //B
				
				//Endfarbe im RGB Format
				$r3 = 19; //R
				$g3 = 204; //G
				$b3 = 2; //B
				
				$s = array($r1,$g1,$b1);
				$m = array($r2,$g2,$b2);
				$e = array($r3,$g3,$b3);

				$sql = sprintf("SELECT 
									id 
								FROM 
									bewerbungsfoto 
								WHERE 
									nutzerid = '%1\$d'",
								$result_row['nutzerid']);
				$result = mysql_db_query($database_db, $sql, $praktdbslave);
				$rueckgabe .= mysql_num_rows($result) > 0 ? '	<img src="/community/passbild.php?id='.mysql_result($result, 0, "id").'" width="120" alt="Bewerbungsfoto" />'."\n" : '	<img src="/styles/images/companies/kandidatensuche/no_pic'.($geschlecht == 'weiblich' ? '_w' : '_m').'.gif" width="120" alt="Bewerbungsfoto" />'."\n";

				$rueckgabe .= '		<p class="profilquali_label">Profilqualit&auml;t</p>'."\n";
				$rueckgabe .= '		<div style="width: '.$w1.'px;" class="profilquali">'."\n";
				
				for ($i = 0; $i < $w2; $i++) {
				    $c1 = max(0, $s[0] - ((($m[0] - $s[0]) / -$w2) * $i));
				    $c2 = max(0, $s[1] - ((($m[1] - $s[1]) / -$w2) * $i));
				    $c3 = max(0, $s[2] - ((($m[2] - $s[2]) / -$w2) * $i));
				    
					$rueckgabe .= '			<div style="float: left; font-size: 0; height: 10px; width: 1px; border: 0; background-color: rgb('.round($c1, 0).', '.round($c2, 0).', '.round($c3, 0).');"></div>'."\n";
				}
				
				for ($i = 0; $i < $w2; $i++) {
				    $c1 = max(0, $m[0] - ((($e[0] - $m[0]) / -$w2) * $i));
				    $c2 = max(0, $m[1] - ((($e[1] - $m[1]) / -$w2) * $i));
				    $c3 = max(0, $m[2] - ((($e[2] - $m[2]) / -$w2) * $i));
				    
					$rueckgabe .= '			<div style="float: left; font-size: 0; height: 10px; width: 1px; border: 0; background-color: rgb('.round($c1, 0).', '.round($c2, 0).', '.round($c3, 0).');\"></div>'."\n";
				}
				
				$rueckgabe .= '		</div>'."\n";
				/*Profile quality*/

				$rueckgabe .= '	</div>'."\n";
				
				$sql = 'SELECT * FROM firmenbookmark WHERE nutzerid = '.$result_row['nutzerid'].' AND firmenid = '.$_SESSION['s_firmenid'];
				$nutzeridSchonDa = mysql_db_query($database_db, $sql, $praktdbmaster);
				
				if ($nutzeridSchonDa != false && mysql_num_rows($nutzeridSchonDa) > 0) {
					$rueckgabe .= '	<a href="/recruiting/hauptordner/4/" id="ka_'.$result_row['nutzerid'].'" class="button green small">bereits ausgew&auml;hlt</a>'."\n";
				} else {
					$rueckgabe .= '	<a href="#" class="button red small" id="ka_'.$result_row['nutzerid'].'" onclick="Load_SearchData(\'merken\',\'\',\'\',\''.$result_row['nutzerid'].'\',\''.$_REQUEST['ds_count'].'\');">Kandidat ausw&auml;hlen</a>'."\n";
				}
			} else {
				$sql = sprintf("SELECT 
									id 
								FROM 
									bewerbungsfoto 
								WHERE 
									nutzerid = '%1\$d'",
								$result_row['nutzerid']);
				$result = mysql_db_query($database_db, $sql, $praktdbslave);
				$rueckgabe .= mysql_num_rows($result) > 0 ? '	<img src="/styles/images/companies/kandidatensuche/pic_b_'.($geschlecht == 'weiblich' ? 'w' : 'm').'.jpg" width="120" alt="Bewerbungsfoto" style="text-align: right;" />'."\n" : '	<img src="/styles/images/companies/kandidatensuche/no_pic'.($geschlecht == 'weiblich' ? '_w' : '_m').'.gif" width="120" alt="Bewerbungsfoto" style="text-align: right;" />'."\n";
				
				$rueckgabe .= '	</div>'."\n";
				$rueckgabe .= '	<a class="button small red" type="button" id="ka_'.$result_row['nutzerid'].'" onclick="smallbox.loadUrl(\'\', \'/mitteilung_buchung/\')" class="auswahl_nein">Kandidat ausw&auml;hlen</a>'."\n";
			}
		} else {
			$sql = sprintf("SELECT 
								id 
							FROM 
								bewerbungsfoto 
							WHERE 
								nutzerid = '%1\$d'",
							$result_row['nutzerid']);
			$result = mysql_db_query($database_db, $sql, $praktdbslave);
			$rueckgabe .= mysql_num_rows($result) > 0 ? '	<img src="/styles/images/companies/kandidatensuche/pic_l_'.($geschlecht == 'weiblich' ? 'w' : 'm').'.jpg" width="120" alt="Bewerbungsfoto" style="text-align: right;" />'."\n" : '	<img src="/styles/images/companies/kandidatensuche/no_pic'.($geschlecht == 'weiblich' ? '_w' : '_m').'.gif" width="120" alt="Bewerbungsfoto" style="text-align: right;" />'."\n";

			$rueckgabe .= '	</div>'."\n";
			$rueckgabe .= '	<a class="button small red" id="ka_'.$result_row['nutzerid'].'" onclick="smallbox.loadLogin();">Kandidat ausw&auml;hlen</a>'."\n";
		}
			
		$rueckgabe .= '	<a class="button alternative small" onclick="Load_SearchData(\'detail\', \'\', \''.$ajaxtype.'\', \''.$result_row['nutzerid'].'\', \''.$ds_start.'\');">Kandidatendetails</a>'."\n";

		$rueckgabe .= '</div>'."\n";		

		$ds_count++;
	}

	$rueckgabe .= $seiten;
	
	if ($ds_start > 9) {
		$rueckgabe .=  '	<p><a href="#" onclick="Load_SearchData(\''.$ajaxtype.'\',document.getElementById(\'sortierung\').value,document.getElementById(\'ksuchstring\').value,document.getElementById(\'categoryids\').value,\''.($ds_start - 10).'\');">&lt;&lt;</a></p>'."\n";
	}
	
	if ($fineselect) {
		if (isset($_REQUEST['sortierung']) && (!empty($_REQUEST['sortierung']) || $_REQUEST['sortierung'] != 'keine')) {
			$rueckgabe .= '	<p class="control"><a href="#" class="button small alternative" name="back" onclick="Load_SearchData(\'categories\',document.getElementById(\'sortierung\').value,document.getElementById(\'ksuchstring\').value); return false;">Sortierung<a/></p>'."\n";
		} else {
			$rueckgabe .= '	<p class="control"><a class="button small alternative" name="back" href="/kandidatensuche/">zur Eingabemaske</a></p>'."\n";
		}
	} else {
		$rueckgabe .= '	<p class="control"><a class="button small alternative" name="back" href="/home/firmen/suchen/wk.phtml">zur Kandidatenliste</a></p>'."\n";
	}
	
	if (($ds_start + 10) < $num_rows) {
		$rueckgabe .=  '	<p class="control"><a href="#" class="button small alternative" value="&lt;&lt;" onclick="Load_SearchData(\''.$ajaxtype.'\',document.getElementById(\'sortierung\').value,document.getElementById(\'ksuchstring\').value,document.getElementById(\'categoryids\').value,\''.($ds_start + 10).'\'); return false;">&lt;&lt;<a/>'."\n";
	}

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
if($suchstringfinal == "+") $suchstringfinal = "";

if (isset($_REQUEST['sortierung']) && !empty($_REQUEST['sortierung']) && $_REQUEST['sortierung'] != 'keine') {
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
							'.(!empty($suchstringfinal)?'MATCH (volltext) AGAINST (\''.$suchstringfinal.'\' IN BOOLEAN MODE) AND':'').'
							berufsfelder.id = tmp_kandidatensuche.gesuch_berufsfeld
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
							'.(!empty($suchstringfinal)?'MATCH (volltext) AGAINST (\''.$suchstringfinal.'\' IN BOOLEAN MODE) AND':'').'
							studiengaenge.id = tmp_kandidatensuche.studiengang
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
							'.(!empty($suchstringfinal)?'MATCH (volltext) AGAINST (\''.$suchstringfinal.'\' IN BOOLEAN MODE) AND':'').'
							semester > 0
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
							'.(!empty($suchstringfinal)?'MATCH (volltext) AGAINST (\''.$suchstringfinal.'\' IN BOOLEAN MODE) AND':'').'
							karierrestatus.id = tmp_kandidatensuche.karierrestatus
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
						'.(!empty($suchstringfinal)?' WHERE MATCH (volltext) AGAINST (\''.$suchstringfinal.'\' IN BOOLEAN MODE)':'').'
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
							'.(!empty($suchstringfinal)?'MATCH (volltext) AGAINST (\''.$suchstringfinal.'\' IN BOOLEAN MODE) AND':'').'
							 bundesland.id = tmp_kandidatensuche.bundesland
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
							'.(!empty($suchstringfinal)?'MATCH (volltext) AGAINST (\''.$suchstringfinal.'\' IN BOOLEAN MODE) AND':'').'
							land.id = tmp_kandidatensuche.land
						GROUP BY
							land
						ORDER BY land.GERMAN';
			break;
	}

	$rueckgabe = '	<form action="" id="checkform">'."\n";
	$rueckgabe .= '		<fieldset><input type="hidden" name="sortierung" value="'.$_POST["sortierung"].'" />'."\n";
	
	$resultquery = mysql_db_query($database_temp,$sql,$praktdbmaster);
	
	if ($resultquery && mysql_numrows($resultquery) > 0) {
		echo '<p class="checkboxes">';
		while ($row = mysql_fetch_array($resultquery, MYSQL_NUM)) {
			$rueckgabe .= '		<label for="'.$sortierung.$row[0].'" class="komplette_breite"><input type="checkbox" id="'.$sortierung.$row[0].'" name="'.$sortierung.$row[0].'" value="'.$row[0].'" />&nbsp;&nbsp;'.$row[1].'&nbsp;('.$row[2].')</label>';
		}
		echo "</p>";
		$rueckgabe .= '		<p class="control noMargin"><a class="button red small" onclick="CheckboxSubmit(); return false;">Auswahl suchen</a>'."\n";
	} else {
		$rueckgabe .= '		<p class="hint error">Es wurden keine Kandidaten gefunden, die auf Ihre Sucheingabe passen, bitte versuchen Sie es erneut.</p>'."\n";
	}
	
	$rueckgabe .= '		<a class="button alternative small" href="/kandidatensuche/" name="back">zur Eingabemaske</a></p>'."\n";
    $rueckgabe .= "</form>";
} else {
	if (is_array($_REQUEST['categoyid'])) {
		$seriids = implode(',',$_REQUEST['categoyid']);
	}

	$_SESSION['s_suchstring'] = $_REQUEST['suchstring'];

	if (!$_SESSION['s_firmenproparray']) {
		$_SESSION['s_firmenproparray']['kandidatensuche']['finesearch']['values'] = array();

		if (is_array($_REQUEST['finesearch'])) {
			$_SESSION['s_firmenproparray']['kandidatensuche']['finesearch']['values'] = $_REQUEST['finesearch'];
		}
		
		$_SESSION['s_firmenproparray']['kandidatensuche']['finesearch']['category'] = $_REQUEST['category'];
		$_SESSION['s_firmenproparray']['kandidatensuche']['finesearch']['category'] = $_REQUEST['category'];
		$_SESSION['s_firmenproparray']['kandidatensuche']['finesearch']['seriids'] = $seriids;
	}

	if ($_SESSION['s_firmenproparray']['kandidatensuche']['finesearch']['category'] != $_REQUEST['category']) {
		$_SESSION['s_firmenproparray']['kandidatensuche']['finesearch']['values'] = array();
	}
	
	if ($_SESSION['s_firmenproparray']['kandidatensuche']['finesearch']['seriids'] != $seriids) {
		$_SESSION['s_firmenproparray']['kandidatensuche']['finesearch']['values'] = array();
	}
	
	$_SESSION['s_firmenproparray']['kandidatensuche']['finesearch']['category'] = $_REQUEST['category'];
	$_SESSION['s_firmenproparray']['kandidatensuche']['finesearch']['seriids'] = $seriids;

	if (is_array($_REQUEST['finesearch'])) {
		$errorcount = 0;
		foreach ($_REQUEST['finesearch'] as $finesearchkey => $finesearchvalue) {
			if ($finesearchvalue != $_SESSION['s_firmenproparray']['kandidatensuche']['finesearch']['values'][$finesearchkey]) {
				$errorcount++;
			}
		}
	}

	if (is_array($_REQUEST['finesearch']) && $errorcount != 0) {
		$_SESSION['s_firmenproparray']['kandidatensuche']['finesearch']['values'] = $_REQUEST['finesearch'];
	}

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
			$sqlext .= "AND bundesland IN ($seriids) AND land = 68";
			break;
		case "land":
			$sqlext .= "AND land IN ($seriids)";
			break;
	}

	if ( is_array( $_SESSION['s_firmenproparray']['kandidatensuche']['finesearch']['values'])) {
		foreach ($_SESSION['s_firmenproparray']['kandidatensuche']['finesearch']['values'] as $finesearchkey => $finesearchvalue) {
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
						tmp_kandidatensuche.studiengang,
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
						".($suchstringfinal != "" ? "MATCH (volltext) AGAINST ('%2\$s' IN BOOLEAN MODE) ":"1=1")."
						%3\$s ORDER BY lastlogin DESC",
					'tmp_kandidatensuche'.$exttable,
					$suchstringfinal,
					$sqlext);#var_dump($sql);
	$results = mysql_db_query($database_temp, $sql, $praktdbmaster);
	
	$num_rows = mysql_num_rows($results);

	if ($num_rows > 0) {
		$rueckgabe .= suchergebnis($_REQUEST['ds_count'], $num_rows, true);
	} else {
		$rueckgabe .= '	<p class="hint error">Es wurden keine Kandidaten gefunden, die auf Ihre Sucheingabe passen, bitte versuchen Sie es erneut.</p>'."\n";
	}
}

/*$submitarray = array();
$submitarray['mainvalue'] = $rueckgabe;*/

$content .= '<?xml version="1.0" encoding="ISO-8859-1"?>'."\n";
$content .= '<root>'."\n";
$content .= '	<htmlinhalte>'."\n";
/*
foreach ($submitarray as $arraykey => $arrayelement) {
	if ( !empty($arrayelement) ) {
		$arrayelement = str_split($arrayelement, 4096);
		$i = 0;
		foreach ($arrayelement as $arrayvalue) {
			$content .= '		<'.$arraykey.$i.'>'.htmlspecialchars($arrayvalue).'</'.$arraykey.$i.'>'."\n";
			$i++;
		}
	}
}*/
$content .= "<div class='resultlist'>".$rueckgabe."</div>";
$content .= '	</htmlinhalte>'."\n";
$content .= '</root>'."\n";

header('Content-Type: text/xml');
header('Content-Length: '.strlen($content));

echo $content;
?>