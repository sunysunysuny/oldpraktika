<?php
//  praktika.de ajax File - KANDIDATENAUSGABE -  WENN FIRMEN SUCHEN

require('/home/praktika/etc/config.inc.php');

if ($suchstring == "warenkorbanzeige") {
	$ajaxtype = "warenkorbanzeige";
} else {
	$ajaxtype = "results";
}

// Start *************************************
srand((double)microtime() * 1000000);
$num = rand(0, (count($dbarray) - 1));

$praktdbmaster = @mysql_pconnect($dbarray[0]['host'], $dbarray[0]['mysqluser'], $dbarray[0]['mysqlpassword']);
$praktdbslave = @mysql_pconnect($dbarray[$num]['host'], $dbarray[$num]['mysqluser'], $dbarray[$num]['mysqlpassword']);

// datensatz abfragen //
$sql = sprintf("SELECT 
					*,
					nutzer.id AS nutzerid,
					DATE_FORMAT(nutzer.modify, '%%d.%%m.%%Y - %%H:%%i') AS modifydatum 
				FROM 
					nutzer 
				WHERE 
					id = '%d'",
				$_REQUEST['nutzerid']);
$result = mysql_db_query($database_db, $sql, $praktdbslave);

// array erzeugen
$eintrag = mysql_fetch_array($result);

// Statistikinfos speichern
$aktdatum = date('YmdHis');
$nutzerid = $eintrag['nutzerid'];
$sql = sprintf("INSERT INTO 
					viewstatspraktikanten (nutzerid, userid, spalte, modify) 
				VALUES 
					('%1\$d', '%2\$d', '%3\$s', '%4\$s')",
				$nutzerid,
				$_SESSION['s_loginid'],
				$_SESSION['s_spalte'],
				$aktdatum);
mysql_db_query($database_db, $sql, $praktdbmaster);

// alter berechnen
$now = time();
$birthday = strtotime($eintrag['geburtsdatum']);
$age = ($now - $birthday) / 31560000; // 365 Tage

$rueckgabe .= '<script type="text/javascript">'."\n";
$rueckgabe .= '<!--'."\n";
$rueckgabe .= '	function load() {'."\n";
$rueckgabe .= '		Load_SearchData('."'detail', '', 'results', '".$_REQUEST['nutzerid']."', '".$_REQUEST['ds_count']."'".');'."\n";
$rueckgabe .= '	}'."\n";
$rueckgabe .= '//-->'."\n";
$rueckgabe .= '</script>'."\n";

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
		$einsicht = true;
	} else {
		$einsicht = false;
		$buchung = true;
	}
} else {
	$einsicht = false;
	$minilogin = true;
}

$sql = 'SELECT * FROM firmenbookmark WHERE nutzerid = '.$nutzerid.' AND firmenid = '.$_SESSION['s_firmenid'];
$nutzeridSchonDa = mysql_db_query($database_db, $sql, $praktdbmaster);

# $rueckgabe .= '<div id="errorText">Der Kandidat konnte nicht gespeichert werden.</div>'."\n";

$sql = sprintf("SELECT 
					lebenslauftemplate 
				FROM 
					einstellungen 
				WHERE 
					nutzerid = %1\$d",
				$nutzerid);
$select = mysql_db_query($database_bprofil, $sql, $praktdbslave);

if (mysql_num_rows($select) > 0) {
	$lebenlaufwahl = mysql_result($select, 0, "lebenslauftemplate");
}
switch ($lebenlaufwahl) {
	case '':
	case '10':
	case '20':
		$lebenlaufwahl = '01';
	case '01':
	case '02':
	case '03':
		$bewerbsprache = 2;
		break;
	default:
		$bewerbsprache = 1;
		break;
}

if ($_SESSION['s_loggedin'] == true) {
	if (isset($buchung) && $buchung == true) {
		$caption = "Mitteilung Buchung";
		$url = "/mitteilung_buchung/";
		$params = "300, 400";
	
		$link = 'smallbox.loadUrl(\'\', \''.$url.'\', \'\',  {cutBody:true})';
		$login = '\''.$caption.'\', \'/mitteilung_buchung/\', '.$params;
		$logintxt = 'Anzeige nach Buchung';
	} else {
		$caption = 'Bewerbungsmappe';
		$url = '/bewerbungsmappe_ansehen/'.$nutzerid.'/';
	
		$lebenslauf = mysql_db_query($database_bprofil,'SELECT lebenslauftemplate FROM einstellungen WHERE nutzerid = '.$nutzerid.' AND sprachid = 2',$praktdbslave);
		if (mysql_num_rows($lebenslauf) > 0) {
			$row = mysql_fetch_array($lebenslauf);
			$lebenslauftemplate = $row['lebenslauftemplate'];
		} else {
			$lebenslauftemplate = '01';
		}

		$link = "window.open('/kandidatenprofil/".$lebenslauftemplate."/".$nutzerid."/', '', 'width=800,height=1000'); return false;";
		#$link = 'smallbox.loadUrl(\'\', \'/kandidatenprofil/'.$lebenslauftemplate.'/'.$nutzerid.'/\', \'\',  {cutBody:true}); return false;';
	}
} else {
	if (isset($buchung) && $buchung == true) {
		$caption = "Mitteilung Buchung";
		$url = "/mitteilung_buchung/";
		$params = "300, 400";
	
		$link = 'smallbox.loadUrl(\'\', \''.$url.'\', \'\', {cutBody:true})';
		$login = '\''.$caption.'\', \'/mitteilung_buchung/\', '.$params;
		$logintxt = 'Anzeige nach Buchung';
	} elseif (isset($minilogin) && $minilogin == true) {
		$caption = "Login";
		$url = "/minilogin/";
		$params = "300, 400";
	
		$link = 'smallbox.loadLogin(); return false;';
		$login = '\'Login\', \'/minilogin/\', 250, 380';
		$logintxt = 'Anzeige nach Login';
	} else {
		$caption = "Login";
		$url = "/minilogin/";
		$params = "300, 400";
	
		$link = 'smallbox.loadLogin(); return false;';
		$login = '\'Login\', \'/minilogin/\', 250, 380';
		$logintxt = 'Anzeige nach Login';
	}
}

$rueckgabe .= '<div class="kandidatensuche_kandidat first">'."\n";
$rueckgabe .= '	<div class="daten">'."\n";
$rueckgabe .= '		<p class="chiffre"><strong>Bewerber</strong></p>'."\n";
$rueckgabe .= '		<p class="left_cell">Name:</p>'."\n";
$rueckgabe .= '		<p class="right_cell">';

$rueckgabe .= ($eintrag['geschlecht'] == 'weiblich') ? 'Frau ' : 'Herr ';

if (!empty($eintrag['titel'])) {
	$rueckgabe .= $eintrag['titel'];
}

$rueckgabe .= ' <strong>'.ucfirst($eintrag['vname']).' ';

if($_SESSION['s_loggedin'] != true) {
	$rueckgabe .= substr(ucfirst($eintrag['name']), 0, 1).'.';
} else {
	$rueckgabe .= ucfirst($eintrag['name']);
}

$rueckgabe .= '</strong></p>'."\n";

// adress
if ($_SESSION['s_loggedin'] == true && $einsicht == true) {
	$rueckgabe .= '		<p class="left_cell">Anschrift:</p>'."\n";
	$rueckgabe .= '		<p class="right_cell">'.$eintrag['strasse'].'<br />'.$eintrag['plz'].' '.$eintrag['ort'].'</td>'."\n";
} else {
	if($einsicht == true) {
		$rueckgabe .= '		<p class="left_cell">Anschrift:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell"><a href="#" onclick="smallbox.loadLogin(); return false; //Load_SearchData('."'detail', '', 'results', '".$_REQUEST['nutzerid']."', '".$_REQUEST['ds_count']."'".')">'.$logintxt.'</a></p>'."\n";
	} else {
		$rueckgabe .= '		<p class="left_cell">Anschrift:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell"><a href="#" onclick="smallbox.loadUrl(\'\', \'/mitteilung_buchung/\', \'\', {cutBody:true}); return false; //Load_SearchData('."'detail', '', 'results', '".$_REQUEST['nutzerid']."', '".$_REQUEST['ds_count']."'".')">'.$logintxt.'</a></p>'."\n";
	}
}


if ($einsicht == true && ($eintrag['grossraum'] <> 94 || $eintrag['grossraum'] > 0) ) {
	$such = $eintrag['grossraum'];
	$suchres = @mysql_db_query($database_db, "SELECT grossraum FROM grossraum WHERE id = '$such'", $praktdbslave);

	$rueckgabe .= '		<p class="left_cell">Gro&szlig;raum:</p>'."\n";
	$rueckgabe .= '		<p class="right_cell">'.@mysql_result($suchres, 0, 'grossraum').'</p>'."\n";
}

if ($eintrag['bundesland'] != 0) {
	$bundeslandid = $eintrag['bundesland'];
	$bland = mysql_db_query($database_db,'SELECT '.$_SESSION['s_sprache'].' FROM bundesland WHERE id = '.$bundeslandid, $praktdbslave);

	$rueckgabe .= '		<p class="left_cell">Bundesland:</p>'."\n";
	$rueckgabe .= '		<p class="right_cell">'.@mysql_result($bland, 0, $_SESSION['s_sprache']).'</p>'."\n";
}

// country
$landid = $eintrag['land'];
if ($landid > 0) {
	$land = mysql_db_query($database_db, 'SELECT '.$_SESSION['s_sprache'].' FROM land WHERE id = '.$landid, $praktdbslave);

	$rueckgabe .= '		<p class="left_cell">Land:</p>'."\n";
	$rueckgabe .= '		<p class="right_cell">'.@mysql_result($land, 0, $_SESSION['s_sprache']).'</p>'."\n";
}

// age
$rueckgabe .= '		<p class="left_cell">Alter:</p>'."\n";
$rueckgabe .= '		<p class="right_cell">'.sprintf("%01.0u", $age).' '.$language['strJahre'].'</p>'."\n";

// phone
if ($_SESSION['s_loggedin'] == true && $einsicht == true && !empty($eintrag['tel'])) {
	$rueckgabe .= '		<p class="left_cell">Telefon:</p>'."\n";
	$rueckgabe .= '		<p class="right_cell">'.$eintrag['tel'].'</p>'."\n";
} elseif (!empty($eintrag['tel'])) {
	if($insicht == true) {
		$rueckgabe .= '		<p class="left_cell">Telefon:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell"><a href="#" onclick="smallbox.loadLogin(); return false;">'.$logintxt.'</a></p>'."\n";
	} else {
		$rueckgabe .= '		<p class="left_cell">Telefon:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell"><a href="#" onclick="smallbox.loadUrl(\'\', \'/mitteilung_buchung/\', \'\', {cutBody:true}); return false; //Load_SearchData('."'detail', '', 'results', '".$_REQUEST['nutzerid']."', '".$_REQUEST['ds_count']."'".')">'.$logintxt.'</a></p>'."\n";
	}
}

// fax
if ($_SESSION['s_loggedin'] == true && $einsicht == true && !empty($eintrag['fax'])) {
	$rueckgabe .= '		<p class="left_cell">Telefax:</p>'."\n";
	$rueckgabe .= '		<p class="right_cell">'.$eintrag['fax'].'</p>'."\n";
} elseif (!empty($eintrag['fax'])) {
	if($einsicht == true) {
		$rueckgabe .= '		<p class="left_cell">Telefax:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell"><a href="#" onclick="smallbox.loadLogin(); return false;">'.$logintxt.'</a></p>'."\n";
	} else {
		$rueckgabe .= '		<p class="left_cell">Telefax:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell"><a href="#" onclick="smallbox.loadUrl(\'\', \'/mitteilung_buchung/\', \'\', {cutBody:true}); return false; //Load_SearchData('."'detail', '', 'results', '".$_REQUEST['nutzerid']."', '".$_REQUEST['ds_count']."'".')">'.$logintxt.'</a></p>'."\n";

	}
}

// cellular phone
if ($_SESSION['s_loggedin'] == true && $einsicht == true && !empty($eintrag['funktel'])) {
	$rueckgabe .= '		<p class="left_cell">Funktelefon:</p>'."\n";
	$rueckgabe .= '		<p class="right_cell">'.$eintrag['funktel'].'</p>'."\n";
} elseif (!empty($eintrag['funktel'])) {
	if($einsicht == true) {
		$rueckgabe .= '		<p class="left_cell">Funktelefon:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell"><a href="#" onclick="smallbox.loadLogin(); return false;">'.$logintxt.'</a></p>'."\n";
	} else {
		$rueckgabe .= '		<p class="left_cell">Funktelefon:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell"><a href="#" onclick="smallbox.loadUrl(\'\', \'/mitteilung_buchung/\', \'\', {cutBody:true}); return false; //Load_SearchData('."'detail', '', 'results', '".$_REQUEST['nutzerid']."', '".$_REQUEST['ds_count']."'".')">'.$logintxt.'</a></p>'."\n";

	}
}

// email
if ($_SESSION['s_loggedin'] == true && $einsicht == true) {
	$rueckgabe .= '		<p class="left_cell">eMail:</p>'."\n";
	$rueckgabe .= '		<p class="right_cell"><a href="/recruiting/nutzeremail/'.$nutzerid.'/" onclick="smallbox.loadUrl(\'\', this.href); return false;">eMail verfassen</a></p>'."\n";
	$rueckgabe .= '		<p class="left_cell">Kurznachricht:</p>'."\n";
	$rueckgabe .= '		<p class="right_cell"><a href="#" onclick="windowanm=window.open('."'/home/firmen/firstkontakt_nutzer.phtml?gesuchid=".$eintrag['pid']."&nutzerid=".$eintrag['nutzerid']."&spaltenid=".$s_spalte."&firmenid=".$s_firmenid."','messageWindow','scrollbars=yes,statusbar=no,toolbar=no,width=450,height=500'".'); return false;">'.$language['strEmailKN'].'</a></p>'."\n";
} else {
	if($einsicht == true) {
		$rueckgabe .= '		<p class="left_cell">eMail:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell"><a href="#" onclick="smallbox.loadLogin();  return false;">'.$logintxt.'</a></p>'."\n";
		$rueckgabe .= '		<p class="left_cell">Kurznachricht:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell"><a href="#" onclick="smallbox.loadLogin();  return false;">'.$logintxt.'</a></p>'."\n";
	} else {
		$rueckgabe .= '		<p class="left_cell">eMail:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell"><a href="#" onclick="smallbox.loadUrl(\'\', \'/mitteilung_buchung/\', \'\', {cutBody:true}); return false; //Load_SearchData('."'detail', '', 'results', '".$_REQUEST['nutzerid']."', '".$_REQUEST['ds_count']."'".')">'.$logintxt.'</a></p>'."\n";
		$rueckgabe .= '		<p class="left_cell">Kurznachricht:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell"><a href="#" onclick="smallbox.loadUrl(\'\', \'/mitteilung_buchung/\', \'\', {cutBody:true}); return false; //Load_SearchData('."'detail', '', 'results', '".$_REQUEST['nutzerid']."', '".$_REQUEST['ds_count']."'".')">'.$logintxt.'</a></p>'."\n";
	}
}

if ($_SESSION['s_loggedin'] == true && $einsicht == true && !empty($eintrag['homepage'])) {
	$rueckgabe .= '		<p class="left_cell">Homepage:</p>'."\n";
	$rueckgabe .= '		<p class="right_cell">'.($eintrag['homepage'] == 'NULL' ? 'keine Angabe' : '<a href="'.$eintrag['homepage'].'" target="_blank">'.$eintrag['homepage'].'</a>').'</p>'."\n";
}

$rueckgabe .= '	</div>'."\n";
$rueckgabe .= '	<div class="photo_quali">'."\n";

if ($nutzeridSchonDa != false && mysql_num_rows($nutzeridSchonDa) > 0) {
	$sql = sprintf("SELECT
						profilquali
					FROM 
						tmp_kandidatensuche
					WHERE 
						nutzerid = '%1\$d'",
					$nutzerid);
	$results = mysql_db_query($database_temp, $sql, $praktdbmaster);
	
	if ($result_row = mysql_fetch_array($results)) {
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
						$nutzerid);
		$result = mysql_db_query($database_db, $sql, $praktdbslave);
		$rueckgabe .= mysql_num_rows($result) > 0 ? '	<img src="/community/passbild.php?id='.mysql_result($result, 0, "id").'" width="120" alt="Bewerbungsfoto" />'."\n" : '	<img src="/styles/images/companies/kandidatensuche/no_pic'.($eintrag['geschlecht'] == 'weiblich' ? '_w' : '_m').'.gif" width="120" alt="Bewerbungsfoto" />'."\n";

		$rueckgabe .= '		<div class="profilquali_label">Profilqualit&auml;t</div>'."\n";
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
	}

	$rueckgabe .= '	</div>'."\n";

	$rueckgabe .= '	<a href="/recruiting/hauptordner/4/" id="ka_'.$result_row['nutzerid'].'" class="button small green">bereits ausgew&auml;hlt</a>'."\n";
} else {

	if (isset($buchung) && $buchung == true) {
		$sql = sprintf("SELECT 
							id 
						FROM 
							bewerbungsfoto 
						WHERE 
							nutzerid = '%1\$d'",
						$nutzerid);
		$result = mysql_db_query($database_db, $sql, $praktdbslave);
		$rueckgabe .= mysql_num_rows($result) > 0 ? '	<img src="/styles/images/companies/kandidatensuche/pic_b_'.($eintrag['geschlecht'] == 'weiblich' ? 'w' : 'm').'.jpg" width="120" alt="Bewerbungsfoto" style="text-align: right;" />'."\n" : '	<img src="/styles/images/companies/kandidatensuche/no_pic'.($eintrag['geschlecht'] == 'weiblich' ? '_w' : '_m').'.gif" width="120" alt="Bewerbungsfoto" style="text-align: right;" />'."\n";

		$rueckgabe .= '	</div><br /><br />'."\n";
		$rueckgabe .= '	<a type="button" id="ka_'.$result_row['nutzerid'].'" onclick="smallbox.loadUrl(\'\', \'/mitteilung_buchung/\',\'\', {cutBody:true}); return false;" class="button red small">Kandidat ausw&auml;hlen</a>'."\n";
	} elseif (isset($minilogin) && $minilogin == true) {
		$caption = "Login";
		$url = "/minilogin/";
		$params = "300, 400";

		$sql = sprintf("SELECT 
							id 
						FROM 
							bewerbungsfoto 
						WHERE 
							nutzerid = '%1\$d'",
						$nutzerid);
		$result = mysql_db_query($database_db, $sql, $praktdbslave);
		$rueckgabe .= mysql_num_rows($result) > 0 ? '	<img src="/styles/images/companies/kandidatensuche/pic_l_'.($eintrag['geschlecht'] == 'weiblich' ? 'w' : 'm').'.jpg" width="120" alt="Bewerbungsfoto" style="text-align: right;" />'."\n" : '	<img src="/styles/images/companies/kandidatensuche/no_pic'.($eintrag['geschlecht'] == 'weiblich' ? '_w' : '_m').'.gif" width="120" alt="Bewerbungsfoto" style="text-align: right;" />'."\n";

		$rueckgabe .= '	</div><br /><br />'."\n";
		$rueckgabe .= '	<a id="ka_'.$result_row['nutzerid'].'" onclick="smallbox.loadLogin(); return false;" class="button small red">Kandidat ausw&auml;hlen</a>'."\n";
	} else {
		$sql = sprintf("SELECT
							profilquali
						FROM 
							tmp_kandidatensuche
						WHERE 
							nutzerid = '%1\$d'",
						$nutzerid);//var_dump($sql);
		$results = mysql_db_query($database_temp, $sql, $praktdbmaster);

		if ($result_row = mysql_fetch_array($results)) {
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
							$nutzerid);
			$result = mysql_db_query($database_db, $sql, $praktdbslave);
			$rueckgabe .= mysql_num_rows($result) > 0 ? '	<img src="/community/passbild.php?id='.mysql_result($result, 0, "id").'" width="120" alt="Bewerbungsfoto" style="text-align: right;" />'."\n" : '	<img src="/styles/images/companies/kandidatensuche/no_pic'.($eintrag['geschlecht'] == 'weiblich' ? '_w' : '_m').'.gif" width="120" alt="Bewerbungsfoto" style="text-align: right;" />'."\n";
			
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
		}
		
		$rueckgabe .= '	</div>'."\n";
		$rueckgabe .= '	<a href="#" id="ka_'.$nutzerid.'" onclick="Load_SearchData(\'merken\',\'\',\'\',\''.$nutzerid.'\',\''.$_REQUEST['ds_count'].'\'); return false;" class="button red small">Kandidat ausw&auml;hlen</a>'."\n";
	}
}

$rueckgabe .= '	<a name="bewerbungsmappe" onclick="'.$link.'" class="button small alternative">Bewerbungsmappe</a>'."\n";

$rueckgabe .= '</div>'."\n";



// Qualifikation
$rueckgabe .= '<div class="kandidatensuche_kandidat">'."\n";
$rueckgabe .= '	<div class="daten">'."\n";
$rueckgabe .= '		<p class="chiffre"><strong>Qualifikation</strong></p>'."\n";

if (!empty($eintrag['studiengang']) && $eintrag['studiengang'] != '0') {
	$ins = $eintrag['studiengang'];

	$suchres = mysql_db_query($database_db,'SELECT studiengang FROM studiengaenge WHERE id = '.$ins,$praktdbslave);
	
	$rueckgabe .= '		<p class="left_cell">Studiengang:</p>'."\n";
	$rueckgabe .= '		<p class="right_cell">'.mysql_result($suchres,0,"studiengang").'</p>'."\n";
	
	if (!empty($eintrag['hochschule'])) {
		$rueckgabe .= '		<p class="left_cell">Studienort:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell">'.$eintrag['hochschule'].'</p>'."\n";
	}
	
	if (!empty($eintrag['karierrestatus'])) {
		$ins = $eintrag['karierrestatus'];
		
		$suchres = mysql_db_query($database_db,"SELECT german FROM karierrestatus WHERE id='$ins'",$praktdbslave);
		
		$rueckgabe .= '		<p class="left_cell">Karierrestatus:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell">'.mysql_result($suchres,0,"german").'</p>'."\n";
	}
}

$rueckgabe .= '	</div>'."\n";
$rueckgabe .= '</div>'."\n";



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
					id DESC", 
				$nutzerid);
$results = mysql_db_query($database_db, $sql, $praktdbslave);

// datensaetze gefunden ? //
if ($results != false && mysql_num_rows($results) > 0) {
	// Vorhandene Praktikumstellen anzeigen
	$rueckgabe .= '<div class="kandidatensuche_kandidat">'."\n";
	$rueckgabe .= '	<div class="daten">'."\n";
	$rueckgabe .= '		<p class="chiffre"><strong>Praktikumsgesuche</strong></p>'."\n";
		
	$i = 1;
	while( $result_row = mysql_fetch_array($results) ) {
		$rueckgabe .= '		<p class="title">'.$i++.'. Praktikumsgesuch</p>'."\n";
		$rueckgabe .= '		<p class="left_cell">T&auml;tigkeit:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell">'.$result_row['taetigkeit'].'</p>'."\n";
		$rueckgabe .= '		<p class="left_cell">Zeitraum:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell">';
	
		if (empty($result_row['zeitraum'])) {
			if ($result_row['zeitraum_ab_m'] == 0 || $result_row['zeitraum_ab_y'] == 0) {
				$rueckgabe .= 'keine genauen Angaben hinterlegt</p>'."\n";
			} else {
				$rueckgabe .= 'ab '.$result_row['zeitraum_ab_m'].'/'.$result_row['zeitraum_ab_y'].'</p>'."\n";
			}
		} else {
			if ($result_row['zeitraum_ab_m'] == 0 || $result_row['zeitraum_ab_y'] == 0) {
				$rueckgabe .= $result_row['zeitraum'].'</p>'."\n";
			} else {
				$rueckgabe .= 'ab '.$result_row['zeitraum_ab_m'].'/'.$result_row['zeitraum_ab_y'].'</p>'."\n";
				$rueckgabe .= '		<p class="left_cell">&nbsp;</p>'."\n";
				$rueckgabe .= '		<p class="right_cell">'.$result_row['zeitraum'].'</p>'."\n";
			}
		}
	
		if ($_SESSION['s_loggedin'] == true) {
			$rueckgabe .= '		<a onclick="smallbox.loadUrl(\'Kandidatengesuch: Praktikum\', \'/recruiting/gesuch_details/'.$result_row['id'].'/'.$eintrag['nutzerid'].'/1/\',  \'\',{cutBody:true});" class="button small alternative">Gesuch ansehen</a></p>'."\n";
		} else {
			$rueckgabe .= '		<p class="left_cell"><a href="/minilogin/" onclick="smallbox.loadLogin(); return false;">Gesuch ansehen</a></p>'."\n";
			$rueckgabe .= '		<p class="right_cell">&nbsp;</p>'."\n";
		}
	}
	
	$rueckgabe .= '	</div>'."\n";
	$rueckgabe .= '</div>'."\n";
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
					inactive = 'false'
					AND nutzerid ='%1\$d' 
				ORDER BY 
					id DESC", 
				$nutzerid);
$results = mysql_db_query($database_db, $sql, $praktdbslave);

// datensaetze gefunden ? //
if ($results != false && mysql_num_rows($results) > 0) {
	$rueckgabe .= '<div class="kandidatensuche_kandidat">'."\n";
	$rueckgabe .= '	<div class="daten">'."\n";
	$rueckgabe .= '		<p class="chiffre"><strong>Bachelor-/Masterthemen</strong></p>'."\n";
	
	$i = 1;
	while( $result_row = mysql_fetch_array($results) ) {
		$rueckgabe .= '		<p class="title">'.$i++.'. Bachelor-/Masterthema</p>'."\n";
		$rueckgabe .= '		<p class="left_cell">Titel der Arbeit:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell">'.$result_row['titel'].'</p>'."\n";
		$rueckgabe .= '		<p class="left_cell">Zeitraum:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell">';
		
		if (empty($result_row['zeitraum'])) {
			if ($result_row['zeitraum_ab_m'] == 0 || $result_row['zeitraum_ab_y'] == 0) {
				$rueckgabe .= 'keine genauen Angaben hinterlegt</p>'."\n";
			} else {
				$rueckgabe .= 'ab '.$result_row['zeitraum_ab_m'].'/'.$result_row['zeitraum_ab_y'].'</p>'."\n";
			}
		} else {
			if ($result_row['zeitraum_ab_m'] == 0 || $result_row['zeitraum_ab_y'] == 0) {
				$rueckgabe .= $result_row['zeitraum'].'</p>'."\n";
			} else {
				$rueckgabe .= 'ab '.$result_row['zeitraum_ab_m'].'/'.$result_row['zeitraum_ab_y'].'</p>'."\n";
				$rueckgabe .= '		<p class="left_cell">&nbsp;</p>'."\n";
				$rueckgabe .= '		<p class="right_cell">'.$result_row['zeitraum'].'</p>'."\n";
			}
		}
		
		if (isset($result_row['studiengang'])) {
			//$studiengangid = $result_row['studiengang'];
			$sql = sprintf("SELECT 
								studiengang 
							FROM 
								studiengaenge 
							WHERE 
								id = '%1\$d'",
								$result_row['studiengang']);
			$studiengang = mysql_db_query($database_db, $sql, $praktdbslave);

			if (mysql_num_rows($studiengang) > 0) {
				$rueckgabe .= '		<p class="left_cell">Studiengang:</p>'."\n";
				$rueckgabe .= '		<p class="right_cell">'.mysql_result($studiengang, 0, "studiengang").'</p>'."\n";
			}
		}
		
		if ($_SESSION['s_loggedin'] == true) {
			$rueckgabe .= '		<a onclick="smallbox.loadUrl(\'Kandidatengesuch: Praktikum\', \'/recruiting/gesuch_details/'.$result_row['id'].'/'.$eintrag['nutzerid'].'/2/\',  \'\',{cutBody:true});" class="button small alternative">Gesuch ansehen</a></p>'."\n";
		} else {
			$rueckgabe .= '		<p class="left_cell"><a href="/minilogin/" onclick="smallbox.loadLogin(); return false;">Gesuch ansehen</a></p>'."\n";
			$rueckgabe .= '		<p class="right_cell">&nbsp;</p>'."\n";
		}
	}
	
	$rueckgabe .= '	</div>'."\n";
	$rueckgabe .= '</div>'."\n";
}


// Berufseinstiegsstellen ermitteln

// 2 aktive Berufseinstiegsstellen ermitteln
$sql = sprintf("SELECT 
					id,
					berufsfeld,
					bezeichnung AS taetigkeit,
					zeitraum_ab_m,
					zeitraum_ab_y 
				FROM 
					berufseinstieggesuch 
				WHERE 
					inactive = 'false'
					AND nutzerid ='%1\$d' 
				ORDER BY 
					id DESC", 
				$nutzerid);
$results = mysql_db_query($database_db, $sql, $praktdbslave);

// datensaetze gefunden ? //
if ($results != false && mysql_num_rows($results) > 0) {
	$rueckgabe .= '<div class="kandidatensuche_kandidat">'."\n";
	$rueckgabe .= '	<div class="daten">'."\n";
	$rueckgabe .= '		<p class="chiffre"><strong>Berufseinstiegsgesuche</strong></p>'."\n";

	$i = 1;
	while ($result_row = mysql_fetch_array($results)) {
		$rueckgabe .= '		<p class="title">'.$i++.'. Berufseinstiegsstelle</p>'."\n";
		$rueckgabe .= '		<p class="left_cell">T&auml;tigkeit:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell">'.$result_row['taetigkeit'].'</p>'."\n";
		$rueckgabe .= '		<p class="left_cell">Zeitraum:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell">ab '.$result_row['zeitraum_ab_m'].'/'.$result_row['zeitraum_ab_y'].'</p>'."\n";

		if (isset($result_row['berufsfeld']) && $result_row['berufsfeld'] != 0) {
			$berufsfeldid = $result_row['berufsfeld'];
			$berufsfeld = mysql_db_query($database_db,'SELECT '.$_SESSION['s_sprache'].' FROM berufsfelder WHERE id = '.$berufsfeldid,$praktdbslave);
			
			if (mysql_num_rows($berufsfeld) > 0) {
				$rueckgabe .= '		<p class="left_cell">Berufsfeld:</p>'."\n";
				$rueckgabe .= '		<p class="right_cell">'.mysql_result($berufsfeld,0,$_SESSION['s_sprache']).'</p>'."\n";
			}
		}

		if ($_SESSION['s_loggedin'] == true) {
			$rueckgabe .= '		<a onclick="smallbox.loadUrl(\'Kandidatengesuch: Praktikum\', \'/recruiting/gesuch_details/'.$result_row['id'].'/'.$eintrag['nutzerid'].'/5/\',  \'\',{cutBody:true});" class="button small alternative">Gesuch ansehen</a></p>'."\n";
		} else {
			$rueckgabe .= '		<p class="left_cell"><a href="/minilogin/" onclick="smallbox.loadLogin(); return false;">Gesuch ansehen</a></p>'."\n";
			$rueckgabe .= '		<p class="right_cell">&nbsp;</p>'."\n";
		}
	}
	
	$rueckgabe .= '	</div>'."\n";
	$rueckgabe .= '</div>'."\n";
}



// Nebenjobs ermitteln //
// 2 aktive Nebenjobs ermitteln
$sql = sprintf("SELECT 
					id,
					berufsfeld,
					taetigkeit, 
					zeitraum, 
					zeitraum_ab_m, 
					zeitraum_ab_y 
				FROM 
					nebenjobgesuch 
				WHERE 
					inactive = 'false' AND 
					nutzerid = '%1\$d' 
				ORDER BY 
					id DESC", 
				$nutzerid);
$results = mysql_db_query($database_db, $sql, $praktdbslave);

if ($results != false && mysql_num_rows($results) > 0) {
	$rueckgabe .= '<div class="kandidatensuche_kandidat">'."\n";
	$rueckgabe .= '	<div class="daten">'."\n";
	$rueckgabe .= '		<p class="chiffre"><strong>Nebenjobgesuche</strong></p>'."\n";
	
	$i = 1;	
	while ($result_row = mysql_fetch_array($results)) {
		$rueckgabe .= '		<p class="title">'.$i++.'. Nebenjobgesuch</p>'."\n";
		$rueckgabe .= '		<p class="left_cell">T&auml;tigkeit:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell">'.$result_row['taetigkeit'].'</p>'."\n";
		$rueckgabe .= '		<p class="left_cell">Zeitraum:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell">';
		
		if (empty($result_row['zeitraum'])) {
			if ($result_row['zeitraum_ab_m'] == 0 || $result_row['zeitraum_ab_y'] == 0) {
				$rueckgabe .= 'keine genauen Angaben hinterlegt</p>'."\n";
			} else {
				$rueckgabe .= 'ab '.$result_row['zeitraum_ab_m'].'/'.$result_row['zeitraum_ab_y'].'</p>'."\n";
			}
		} else {
			if ($result_row['zeitraum_ab_m'] == 0 || $result_row['zeitraum_ab_y'] == 0) {
				$rueckgabe .= $result_row['zeitraum'].'</p>'."\n";
			} else {
				$rueckgabe .= 'ab '.$result_row['zeitraum_ab_m'].'/'.$result_row['zeitraum_ab_y'].'</p>'."\n";
				$rueckgabe .= '		<p class="left_cell">&nbsp;</p>'."\n";
				$rueckgabe .= '		<p class="right_cell">'.$result_row['zeitraum'].'</p>'."\n";
			}
		}
		
		$berufsfeldid = $result_row['berufsfeld'];
		$berufsfeld = mysql_db_query($database_db,'SELECT '.$_SESSION['s_sprache'].' FROM berufsfelder WHERE id = '.$berufsfeldid,$praktdbslave);
		
		if (mysql_num_rows($berufsfeld) > 0) {
			$rueckgabe .= '		<p class="left_cell">Berufsfeld:</p>'."\n";
			$rueckgabe .= '		<p class="right_cell">'.mysql_result($berufsfeld,0,$_SESSION['s_sprache']).'</p>'."\n";
		}
		
		if ($_SESSION['s_loggedin'] == true) {
			$rueckgabe .= '		<a onclick="smallbox.loadUrl(\'Kandidatengesuch: Praktikum\', \'/recruiting/gesuch_details/'.$result_row['id'].'/'.$eintrag['nutzerid'].'/3/\',  \'\',{cutBody:true});" class="button small alternative">Gesuch ansehen</a></p>'."\n";
		} else {
			$rueckgabe .= '		<p class="left_cell"><a href="/minilogin/" onclick="smallbox.loadLogin(); return false;">Gesuch ansehen</a></p>'."\n";
			$rueckgabe .= '		<p class="right_cell">&nbsp;</p>'."\n";
		}
	}
	
	$rueckgabe .= '	</div>'."\n";
	$rueckgabe .= '</div>'."\n";
}



// Ausbildungspl�tze ermitteln //
// 2 aktive Ausbildungspl�tze ermitteln
$sql = sprintf("SELECT 
					id,
					lehre,
					taetigkeit, 
					zeitraum, 
					zeitraum_ab_m, 
					zeitraum_ab_y 
				FROM 
					ausbildungsgesuch 
				WHERE 
					inactive = 'false' AND 
					nutzerid = '%1\$d' 
				ORDER BY 
					id DESC", 
				$nutzerid);
$results = mysql_db_query($database_db, $sql, $praktdbslave);

// datensaetze gefunden ? //
if ($results != false && mysql_num_rows($results) > 0) {
	$rueckgabe .= '<div class="kandidatensuche_kandidat">'."\n";
	$rueckgabe .= '	<div class="daten">'."\n";
	$rueckgabe .= '		<p class="chiffre"><strong>Ausbildungplatzgesuche</strong></p>'."\n";
	
	$i = 1;
	while ($result_row = mysql_fetch_array($results)) {
		$rueckgabe .= '		<p class="title">'.$i++.'. Ausbildungplatzgesuch</p>'."\n";
		
		$lehreid = $result_row['lehre'];
		// Nutzerpr�fung ob Mann oder Frau wegen Anzeige des richtigen Berufsgeschlechtes //
		if ($_SESSION['s_loggedin'] == true) {
			$resultn = mysql_db_query($database_db,'SELECT anrede FROM nutzer WHERE id = '.$nutzerid,$praktdbslave);
			
			// array erzeugen //
			$eintragn = mysql_fetch_array($resultn);
			
			if ($eintragn['anrede'] == 'Herr') {
				$lehre = mysql_db_query($database_db,'SELECT berufswahl FROM berufswahl_mann WHERE id = '.$lehreid,$praktdbslave);
			} else {
				$lehre = mysql_db_query($database_db,'SELECT berufswahl FROM berufswahl_frau WHERE id = '.$lehreid,$praktdbslave);
			}
		} else {
			$lehre = mysql_db_query($database_db,'SELECT berufswahl FROM berufswahl_mann WHERE id = '.$lehreid,$praktdbslave);
		}
		
		$rueckgabe .= '		<p class="left_cell">Ausbildung zum:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell">'.mysql_result($lehre,0,'berufswahl').'</p>'."\n";
		$rueckgabe .= '		<p class="left_cell">T&auml;tigkeit:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell">'.$result_row['taetigkeit'].'</p>'."\n";
		$rueckgabe .= '		<p class="left_cell">Zeitraum:</p>'."\n";
		$rueckgabe .= '		<p class="right_cell">';
		
		if (empty($result_row['zeitraum'])) {
			if ($result_row['zeitraum_ab_m'] == 0 || $result_row['zeitraum_ab_y'] == 0) {
				$rueckgabe .= 'keine genauen Angaben hinterlegt</p>'."\n";
			} else {
				$rueckgabe .= 'ab '.$result_row['zeitraum_ab_m'].'/'.$result_row['zeitraum_ab_y'].'</p>'."\n";
			}
		} else {
			if ($result_row['zeitraum_ab_m'] == 0 || $result_row['zeitraum_ab_y'] == 0) {
				$rueckgabe .= $result_row['zeitraum'].'</p>'."\n";
			} else {
				$rueckgabe .= 'ab '.$result_row['zeitraum_ab_m'].'/'.$result_row['zeitraum_ab_y'].'</p>'."\n";
				$rueckgabe .= '		<p class="left_cell">&nbsp;</p>'."\n";
				$rueckgabe .= '		<p class="right_cell">'.$result_row['zeitraum'].'</p>'."\n";
			}
		}
		
		if ($_SESSION['s_loggedin'] == true) {
			$rueckgabe .= '		<a onclick="smallbox.loadUrl(\'Kandidatengesuch: Praktikum\', \'/recruiting/gesuch_details/'.$result_row['id'].'/'.$eintrag['nutzerid'].'/4/\',  \'\',{cutBody:true});" class="button small alternative">Gesuch ansehen</a></p>'."\n";
		} else {
			$rueckgabe .= '		<p class="left_cell"><a href="/minilogin/" onclick="smallbox.loadLogin(); return false;">Gesuch ansehen</a></p>'."\n";
			$rueckgabe .= '		<p class="right_cell">&nbsp;</p>'."\n";
		}
	}
	
	$rueckgabe .= '	</div>'."\n";
	$rueckgabe .= '</div>'."\n";
}

$rueckgabe .= '<form>'."\n";
$rueckgabe .= '		<p>'."\n";
$rueckgabe .= '<br /><br /><br /><a class="button alternative small" onclick="Load_SearchData(\''.$ajaxtype.'\', document.getElementById(\'sortierung\').value, document.getElementById(\'ksuchstring\').value, document.getElementById(\'categoryids\').value, \''.$_REQUEST['ds_count'].'\');">zur&uuml;ck zur Ergebnisliste</a>'."\n";
$rueckgabe .= '		</p>'."\n";
$rueckgabe .= '</form>'."\n";

/*$submitarray = array();
$submitarray['mainvalue'] = $rueckgabe;*/

$content .= '<root>'."\n";
$content .= '	<htmlinhalte>'."\n";

/*
foreach ($submitarray as $arraykey => $arrayelement) {
	if (!empty($arrayelement)) {
		$arrayelement = str_split($arrayelement, 4096);
		$i = 0;
		
		foreach ($arrayelement as $arrayvalue) {
			$content .= '<'.$arraykey.$i.'>'.htmlspecialchars($arrayvalue).'</'.$arraykey.$i.'>'."\n";
			$i++;
		}
	}
}*/

//echo "<wkcount>".count($s_firmenproparray['kandidatensuche']['warenkorb'])."</wkcount>\n";
$content .= $rueckgabe;
$content .= '	</htmlinhalte>'."\n";
$content .= '</root>'."\n";

header('Content-Type: text/xml');
header('Content-Length: '.strlen($content));

echo $content;
?>