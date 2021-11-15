<?php
ob_start();
require("/home/praktika/etc/gb_config.inc.php");

$praktdbmaster = @mysql_pconnect($dbarray[0][host],$dbarray[0][mysqluser],$dbarray[0][mysqlpassword]);

if(empty($_SESSION['s_firmenid'])) { echo "Bitte einloggen"; exit(); }

$content = '';
$stellenid = (int)$_POST["id"];

$sql = $hDB->query('SELECT bearbeiter.vname, bearbeiter.anrede, bearbeiter.name, 
			stellen.id, stellen.inactive, stellen.taetigkeit, stellen.jobcode, stellen.spalte, 
			stellen.link_anzeige_abfr, stellen.templateid, stellen.von_monat, stellen.von_jahr,
			UNIX_TIMESTAMP(stellen.datum_eintrag) `datum_eintrag`,UNIX_TIMESTAMP(stellen.modify ) `modify`
				FROM '.$database_db.'.stellen 
					LEFT OUTER JOIN '.$database_db.'.bearbeiter ON(bearbeiter.id = stellen.bearbeiterid)
				WHERE stellen.id = '.$stellenid, $praktdbmaster);

// Die Anzahl der Abrufe wird für 1 Stunde in Session gecached, damit der DB-Server eine Chance hat
if(!isset($_SESSION["cache"]["stStatTime_".$stellenid]) || $_SESSION["cache"]["stStatTime_".$stellenid] < time() - 3600 || $_SERVER['HTTP_HOST'] == 'lokal.praktika.de') {
	$viewstat = $hDB->query('SELECT COUNT(id) AS anzahl FROM '.$database_db.'.viewstatsfirmen WHERE firmenid = '.$_SESSION['s_firmenid'].' AND stellenid = '.$stellenid, $praktdbslave);
	$statsarray = mysql_fetch_array($viewstat);
	
	$_SESSION["cache"]["stStat_".$stellenid] = $statsarray["anzahl"];
	$_SESSION["cache"]["stStatTime_".$stellenid] = time();
	
}

$abrufe = &$_SESSION["cache"]["stStat_".$stellenid];

if (mysql_num_rows($sql) > 0) {
	$stellentemplates = array();
	$stellentemplatesresult = $hDB->query('SELECT * FROM '.$database_db.'.stellentemplates WHERE firmenid = '.$_SESSION['s_firmenid'],$praktdbslave);
	
	while ($row = mysql_fetch_array($stellentemplatesresult, MYSQL_ASSOC)) {
		$stellentemplates[$row['id']] = $row['templatename'];
	}
	
	$row = mysql_fetch_assoc($sql);
	
	if (array_key_exists($row['templateid'], $stellentemplates)) {
	    $templatetype = 'eigenes Template';
	} elseif ($row['link_anzeige_abfr'] == "true") {
		$templatetype = 'eigene Anzeige';
	} else {
		$templatetype = 'praktika.de Template';
	}
	
	$style = ($row['inactive'] == 'false') ? 'style="color:#060"': 'style="color:#f00"';
	switch ($row["spalte"]) {
		case 1: // Praktikum
			$bereich = "Praktikum";
			break;
		case 2: // Bachelor- / Masterarbeit
			$bereich = "Bachelor- / Masterarbeit";	
			break;
		case 3:
			$bereich = 'Nebenjob';
			break;
		case 4:
			$bereich = 'Ausbildung';
			break;
		case 5:
			$bereich = 'Berufseinstieg';
			break;
		case 6:
			$bereich = 'Projekt';
			break;
		case 7:
			$bereich = 'Trainee';
			break;
	}
	
	$content .= '<span class="statLabel">Titel:<br /><br /></span> '.(!empty($row['taetigkeit']) ? "<b>".strip_tags(stripslashes($row['taetigkeit']))."</b>" : 'keine Angabe').'<br /><br />';

if($row["von_jahr"] < date("Y") || $row["von_jahr"] == date("Y") && $row["von_monat"] < date("m")) {
	$content .= '<div style="border:1px dashed #800000; padding:3px;margin:0px -10px 0px -10px;"><span class="statLabel"><b>Tipp:</b><br /><br /><br /></span>Der angegebene Einsatzzeitraum liegt in der Vergangenheit und wird den Bewerbern somit <b>nicht angezeigt</b>. Bewerber suchen hingegen meistens Angebote mit einem angegebenen Startzeitpunkt. <a href="/firmen/stelle/category/'.$row["id"].'/">Zeitraum jetzt ändern</a></div><br />';	
}
	
	$content .= '<span class="statLabel">Stellen-Id:</span> '.$row['id'].'<br />';
	$content .= '<span class="statLabel">Jobcode:</span> '.(isset($row['jobcode']) ? $row['jobcode'] : 'keine Angabe').'<br />';
	$content .= '<span class="statLabel">Bereich:</span> '.$bereich.'<br /><br />';
	

	
	if (isset($templatetype) && $row['link_anzeige_abfr'] == "false") {
		$content .= '<span class="statLabel">Templatetyp:</span> '.$templatetype.'<br />';
	}
	$content .= '<span class="statLabel">Status:</span> <span '.(($row['inactive'] == 'false') ? 'style="color:#060">Anzeige ist aktiviert': 'style="color:#f00">Anzeige ist deaktiviert').'</span><br />';
	
	$content .= '<span class="statLabel">Bearbeiter:</span> '.(!empty($row['vname']) ? utf8_encode($row["anrede"]." ".$row['vname']." ".$row["name"]) : 'keine Angabe')."<br /><br />";
	
	$content .= '<span class="statLabel">Abrufe:</span> '.($_SESSION['s_firmenlevel'] == 0 ? '<a href="/firmen/stelle/paket/">Anzeige erst ab Stellenpaket BASIS</a>' : '<a href="/firmen/stelle/'.$row['id'].'/statistik-detail.html">'.(int)$abrufe.' Abrufe</a>').'<br /><br />';
			$content .= '<span class="statLabel">Eintrag vom:</span> '.date("d.m.Y",$row["datum_eintrag"]).'<br />';
	$content .= '<span class="statLabel">letzte &Auml;nderung:</span> '.date("d.m.Y",$row["modify"]).'<br />';
	}



echo $content;

