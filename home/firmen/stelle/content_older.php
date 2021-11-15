<?php
require("/home/praktika/etc/config.inc.php");

if (!isset($_SESSION['s_loggedin']) || $_SESSION['s_loggedin'] == false) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/login');
	exit();
}

//Datensatz speichern und Weiterleitung zum naechsten
if (isset($_POST['save_content'])) {
	$error = false;
	
	if (!isset($_POST['bewerbungpertel']) || (isset($_POST['tel_bewerbung']) && empty($_POST['tel_bewerbung']))) {
		$_POST['bewerbungpertel'] = 'false';
		$_SESSION['bewerbungpertel'] = 'false';
	}
	if (!isset($_POST['bewerbungperpost'])) {
		$_POST['bewerbungperpost'] = 'false';
		$_SESSION['bewerbungperpost'] = 'false';
	}
	if (!isset($_POST['bewerbungperemail']) || (isset($_POST['email_bewerbung']) && empty($_POST['email_bewerbung']))) {
		$_POST['bewerbungperemail'] = 'false';
		$_SESSION['bewerbungperemail'] = 'false';
	}
	if (!isset($_POST['bewerbungperonline'])) {
		$_POST['bewerbungperonline'] = 'false';
		$_SESSION['bewerbungperonline'] = 'false';
	}
	if (!isset($_POST['bewerbungpereigen']) || (isset($_POST['link_bewerbung']) && empty($_POST['link_bewerbung']))) {
		$_POST['bewerbungpereigen'] = 'false';
		$_SESSION['bewerbungpereigen'] = 'false';
	}

	if ($_POST['bewerbungpertel'] == 'false' && $_POST['bewerbungperpost'] == 'false' && $_POST['bewerbungperemail'] == 'false' && $_POST['bewerbungperonline'] == 'false' && $_POST['bewerbungpereigen'] == 'false') {
		$_POST['bewerbungperpost'] = 'true';
		$_POST['bewerbungperonline'] = 'true';
		$_SESSION['bewerbungperpost'] = 'false';
		$_SESSION['bewerbungperonline'] = 'false';
	}

	if (isset($_REQUEST['link_anzeige']) && (empty($_REQUEST['link_anzeige']) || $_REQUEST['link_anzeige'] == 'http://')) {
		$error = true;
		$_SESSION['jobAd_sidebar']['content'] = true;
		$isEigenes = 'false';
		$link_anzeige_abfr = 'true';
	}

	if ($error == false) {
		if (isset($_REQUEST['link_anzeige']) && !empty($_REQUEST['link_anzeige'])) {
			if (strpos($_REQUEST['link_anzeige'], 'http://', 0) === false) {
				$_REQUEST['link_anzeige'] = 'http://'.$_REQUEST['link_anzeige'];
			}
			
			$_SESSION['jobAd']['link_anzeige'] = $_REQUEST['link_anzeige'];
		}
		
		if (isset($_REQUEST['taetigkeit']) && !empty($_REQUEST['taetigkeit'])) {
			$_SESSION['jobAd']['taetigkeit'] = $_REQUEST['taetigkeit'];
		}
		
		if (intval($_SESSION['jobAd']['stellenid']) == 0) {
			$_POST = array_merge($_POST, $_SESSION['jobAd']);
			$sql = 'INSERT INTO stellen SET ';
		} else {
			$sql = 'UPDATE stellen SET ';
		}
	
		foreach ($_POST as $key => $value) {
			if ($key != 'save_content' && $key != 'stellenid' && $key != 'firma') {
				$sql .= $key.' = \''.mysql_real_escape_string($value).'\', ';
			}
		}
	
		$sql = substr($sql, 0, (strlen($sql)-2));
		
		if (intval($_SESSION['jobAd']['stellenid']) != 0) {
			$sql .= ' WHERE id = '.intval($_SESSION['jobAd']['stellenid']);
		}

		$result = mysql_db_query($database_db, $sql, $praktdbmaster);
		
		if (intval($_SESSION['jobAd']['stellenid']) == 0) {
			unset($_SESSION['jobAd']);
		
			$_SESSION['jobAd']['stellenid'] = mysql_insert_id();
		}
	
		$dateiname = '/home/praktika/cache/stellen/stelle_'.$_SESSION['jobAd']['stellenid'].'.html';
		unlink($dateiname);
	
		$_SESSION['jobAd_sidebar']['content'] = true;
		
		header('Location: /firmen/stelle/'.$_SESSION['jobAd']['stellenid'].'/layout.html');
		exit();
	}
}

$result = mysql_db_query($database_db, 'SELECT COUNT(id) AS anzahl FROM stellen WHERE firmenid = '.$_SESSION['s_firmenid'].' AND deleted=\'false\'', $praktdbslave);
$row = mysql_fetch_array($result);
$anzahl = $row['anzahl'];

$_SESSION['sidebar'] = 'stelle';
$_SESSION['restricted'] = RESTRICTED_COMPANY;
$_SESSION['current_user_group'] = USER_GROUP_COMPANIES;
$_SESSION['current_page'] = PAGE_PUBLISH_JOBS;

//vorhandene Stelle bearbeiten?
//wenn nicht, dann neue Stelle
if ((isset($_REQUEST['stellenid']) && is_int(intval($_REQUEST['stellenid'])) && !empty($_REQUEST['stellenid'])) || (isset($_SESSION['jobAd']['stellenid']) && is_int(intval($_SESSION['jobAd']['stellenid'])) && $_SESSION['jobAd']['stellenid'] != 0)) {
	$_SESSION['jobAd']['stellenid'] = (isset($_REQUEST['stellenid']) && intval($_REQUEST['stellenid']) != 0) ? intval($_REQUEST['stellenid']) : intval($_SESSION['jobAd']['stellenid']);

	$sqlStelle = '
					SELECT
						stellentemplates.isEigenes,
						stellentemplates.isEditable,
						stellen.*,
						UNIX_TIMESTAMP(stellen.modify) AS modifyTS,
						DATE_FORMAT(von_monat, \'%m\') AS monat,
						DATE_FORMAT(von_jahr, \'%Y\') AS jahr,
						DATE_FORMAT(datum_verfall, \'%d\') AS tagv,
						DATE_FORMAT(datum_verfall, \'%m\') AS monatv,
						DATE_FORMAT(datum_verfall, \'%Y\') AS jahrv 
					FROM
						stellen 
						LEFT JOIN stellentemplates ON (stellentemplates.id = stellen.templateid)				
					WHERE
						stellen.id = '.$_SESSION['jobAd']['stellenid'].'
						AND stellen.firmenid = '.$_SESSION['s_firmenid'];

	$stelle = mysql_db_query($database_db, $sqlStelle, $praktdbslave);

	if (($stelle != false) && (mysql_num_rows($stelle) > 0)) {
		$row = mysql_fetch_array($stelle);
		
		foreach ($row as $key => $value) {
			$$key = strip_tags(stripslashes(stripslashes(stripslashes($value))));
		}
	
		$bereich = $spalte;
		
		//Verhinderung von sinnfreien Aktualisierungen der Stelle, um als erster auf der Startseite gelistet zu werden
		//4 Tage = (4 * 86400)
		//Soll modify-Flag gesetzt werden?
		if ($modifyTS > time() - (4 * 86400)) {
			$_SESSION['stelle_dont_modify']	= true;
		} else {
			$_SESSION['stelle_dont_modify']	= false;
		}	
	}

	if (strpos($link_anzeige, 'https://www.praktika.de/') !== false) {
		$_SESSION['jobPDF'] = true;
	}
	
	if ($link_anzeige_abfr == 'true') {
		$_SESSION['jobAdExtern'] = true;
	}
	
	$_SESSION['jobAd_sidebar']['content'] = true;
	$_SESSION['jobAd_sidebar']['layout'] = true;
	$_SESSION['jobAd_sidebar']['category'] = (empty($stichwort) || ($schuelerprakt == 'false' && $hochschulprakt == 'false' && $wprakt == 'false' && $sonstprakt == 'false')) ? false : true;
	$_SESSION['jobAd_sidebar']['active'] = $inactive == 'false' ? true : false;
} else {
	$_SESSION['neue_stelle'] = true;
	
	if (!isset($_SESSION['jobAd'])) {
		$_SESSION['jobAd']['stellenid'] = 0;

		//Daten der Firma
		$sql = 'SELECT
					firma,
					CONCAT(firma, \'<br />\', strasse, \'<br />\', plz, \' \', ort, \'<br />eMail: \', email, \'<br />Telefon: \', tel, \'<br />Fax: \', fax, \'<br />Webseite: \', homepage) AS firmenkontakt,
					strasse,
					plz,
					land, 
					bundesland, 
					grossraum, 
					ort AS einsatzort,
					email,
					tel,
					fax,
					homepage,
					branche
				FROM
					firmen
				WHERE
					id = '.intval($_SESSION['s_firmenid']);

		$result = mysql_db_query($database_db, $sql, $praktdbslave);
		$row = mysql_fetch_assoc($result);
		
		foreach ($row as $key => $value) {
			$kontakt[$key] = $value;
		}
		
		//Bereich bestimmen
		$sqlBranche = mysql_db_query($database_db, 'SELECT german FROM branchen WHERE id = '.$kontakt['branche'], $praktdbslave);
		$branche = mysql_result($sqlBranche, 0, 'german');

		//Vorgeplaenkel
		$_SESSION['jobAd']['inactive'] = 'true';
		$_SESSION['jobAd']['bearbeiterid'] = intval($_SESSION['s_loginid']);
		$_SESSION['jobAd']['firmenid'] = intval($_SESSION['s_firmenid']);
		$_SESSION['jobAd']['sprache'] = intval($_SESSION['s_sprachid']);
		$_SESSION['jobAd']['spalte'] = 1;
		$_SESSION['jobAd']['stichwort'] = 'z. B. Deutsch, Englisch, Web-Programmierer, MySQL, PHP, ...';
		$_SESSION['jobAd']['firma'] = $kontakt['firma'];
		
		//Mindestqualifikation
		$_SESSION['jobAd']['qualifikation'] = 3;
		$_SESSION['jobAd']['studienrichtung'] = 2;
		$_SESSION['jobAd']['branche'] = $kontakt['branche'];
		$_SESSION['jobAd']['berufsfeld'] = 5;
		$_SESSION['jobAd']['beschaeftigung'] = '';
		$_SESSION['jobAd']['position'] = '';
		
		//Beginn der Taetigkeit
		$_SESSION['jobAd']['von_monat'] = (date('m') > 10) ? ((date('m') + 2) % 12) : (date('m') + 2);
		$_SESSION['jobAd']['von_jahr'] = (date('m') > 10) ? (date('Y') + 1) : date('Y');
		
		//Dauer der Taetigkeit
		$_SESSION['jobAd']['zeitraum'] = '6 Monate';
	
		$_SESSION['jobAd']['land'] = $kontakt['land'];
		$_SESSION['jobAd']['bundesland'] = $kontakt['bundesland'];
		$_SESSION['jobAd']['grossraum'] = $kontakt['grossraum'];
		$_SESSION['jobAd']['einsatzort'] = $kontakt['einsatzort'];
		
		//Daten des Bearbeiters
		$sql = 'SELECT
					anrede,
					vname,
					name,
					tel,
					fax,
					email AS email_bewerbung, 
					tel AS tel_bewerbung
				FROM
					bearbeiter
				WHERE
					id = '.intval($_SESSION['s_loginid']);

		$result = mysql_db_query($database_db, $sql, $praktdbslave);
		$row = mysql_fetch_assoc($result);

		foreach ($row as $key => $value) {
			$bearbeiterKontakt[$key] = $value;
		}

		//Inhalt
		$_SESSION['jobAd']['jobcode'] = '';
		$_SESSION['jobAd']['taetigkeit'] = 'Praktikum'.($anzahl + 1).' im Bereich '.$branche;
		$_SESSION['jobAd']['subtitle'] = 'am Standort '.$kontakt['einsatzort'];
		$_SESSION['jobAd']['firmeninfosHead'] = 'Zu unserem Unternehmen';
		$_SESSION['jobAd']['firmentaetigkeitHead'] = 'Das kommt auf Sie zu';
		$_SESSION['jobAd']['firmenqualiHead'] = 'Ihr Profil';
		$_SESSION['jobAd']['firmenperspHead'] = 'Unsere Leistungen';
		$_SESSION['jobAd']['firmenkontaktHead'] = 'Kontaktaufnahme';
		$_SESSION['jobAd']['firmeninfos'] = 'Wir sind ein innovatives und expandierendes Medienunternehmen. Als f&uuml;hrender Dienstleister in diesem Bereich liegt unser FoKus auf Kunden-L&ouml;sungen.';
		$_SESSION['jobAd']['firmentaetigkeit'] = 'Ihre Aufgabe ist die Unterst&uuml;tzung der Manager bei der Strategieplanung und -umsetzung. In der Regel werden immer kleine Projekte vergeben, an denen Sie selbstst&auml;ndig arbeiten k&ouml;nnen. Den wesentlichen Teil der T&auml;tigkeit bilden die Analysen sowie die Erstellung betriebswirtschaftlicher Auswertungen. Des Weiteren sind Sie zust&auml;ndig f&uuml;r die Verwaltung und Strukturierung der ben&ouml;tigten Datenbasis. Au&szlig;erdem stellt die Vorbereitung von Pr&auml;sentationen der Projektergebnisse in turnusm&auml;&szlig;igen Managementpr&auml;sentationen einen wichtigen T&auml;tigkeitsbereich dar. Dar&uuml;ber hinaus soll dem Praktikant Gelegenheit gegeben werden, die Aufgabengebiete benachbarter Bereiche kennenzulernen sowie von der Lehranstalt vorgeschriebene Praktikumsberichte etc. anzufertigen.';
		$_SESSION['jobAd']['firmenquali'] = '- immatrikulierter Student (m/w) folgender oder vergleichbarer Studieng&auml;nge: Betreibeswirtschaft<br />- gute Deutsch- und Englisch-Kenntnisse<br />- sehr gute Kenntnisse der g&auml;ngigen MS Office-Anwendungen (Excel, Word, PowerPoint, Access)<br />- Teamf&auml;higkeit und Zuverl&auml;ssigkeit<br />- Eigeninitiative und Einsatzbereitschaft<br />- eigenverantwortliche und selbst&auml;ndige Arbeitsweise';
		$_SESSION['jobAd']['firmenpersp'] = '- Wir sind ein junges, dynamisches Team, in dem eigenst&auml;ndiges Arbeiten gef&ouml;rdert wird<br />- Wir bieten verantwortungsvolle Mitarbeit in Projekten f&uuml;r weltweit agierende Unternehmen<br />- Unsere Mitarbeiter werden fortw&auml;hrend weiterqualifiziert, die Umsetzung erfolgt direkt im Projekt<br />- Wir bieten die M&ouml;glichkeit, in F&uuml;hrungsaufgaben hineinzuwachsen<br />- Wir sind loyal, zuverl&auml;ssig und verantwortungsbewusst unseren Kunden sowie Mitarbeitern gegen&uuml;ber';
		$_SESSION['jobAd']['firmenkontakt'] = $kontakt['firmenkontakt'];
		$_SESSION['jobAd']['link_anzeige_abfr'] = ((isset($_REQUEST['extern']) && $_REQUEST['extern'] == '1') || (isset($_REQUEST['pdf']) && $_REQUEST['pdf'] == '1')) ? 'true' : 'false';
		$_SESSION['jobAd']['link_anzeige'] = '';
		$_SESSION['jobAd']['link_bewerbung'] = '';
		$_SESSION['jobAd']['beschreibung'] = 'Als Dauer f&uuml;r ein solches Praktikum sehen wir idealerweise 3 bis 6 Monate vor. Bitte nutzen Sie f&uuml;r Ihre Bewerbung das Online-System von praktika.de. Wir freuen uns auf Ihre Bewerbung.';
		$_SESSION['jobAd']['entgelt'] = 'Diese Stelle wird nicht verg&uuml;tet.';
		
		//Adressdaten
		$_SESSION['jobAd']['kontakt2strasse'] = $kontakt['strasse'];
		$_SESSION['jobAd']['kontakt2plz'] = $kontakt['plz'];
		$_SESSION['jobAd']['kontakt2ort'] = $kontakt['einsatzort'];
		$_SESSION['jobAd']['kontakt2ansprechpartner'] = $bearbeiterKontakt['anrede']." ".$bearbeiterKontakt['vname']." ".$bearbeiterKontakt['name'];
		$_SESSION['jobAd']['kontakt2tel'] = $bearbeiterKontakt['tel'];
		$_SESSION['jobAd']['kontakt2fax'] = $bearbeiterKontakt['fax'];
		$_SESSION['jobAd']['kontakt2email'] = $bearbeiterKontakt['email_bewerbung'];
		$_SESSION['jobAd']['kontakt2webseite'] = $kontakt['homepage'];
		$_SESSION['jobAd']['ansprechpartnerid'] = intval($_SESSION['s_loginid']);
		
		//Templatefestlegung
		$_SESSION['jobAd']['templateid'] = (isset($_REQUEST['extern']) && $_REQUEST['extern'] == '1') ? '0' : ($_SESSION['s_firmenlevel'] == 0 ? 109 : ($_SESSION['s_firmenlevel'] == 1 ? 1 : ($_SESSION['s_firmenlevel'] == 2 ? 2 : ($_SESSION['s_firmenlevel'] == 3 ? 1 : 109))));
		$_SESSION['jobAd']['flash'] = '';
		
		$_SESSION['jobAd']['host'] = $host;
		$_SESSION['jobAd']['ip'] = $ip;
		
		$_SESSION['jobAd']['datum_eintrag'] = date('Y-m-d');
		$_SESSION['jobAd']['modify'] = date('Y-m-d H:i:s');
	}

	$isEigenes = 'false'; //fuer eine neue Stelle
	$isEditable = 'true'; //fuer eine neue Stelle
	$templateid = 1000; //frei gewaehlt fuer eine neue Stelle

	if ((isset($_REQUEST['extern']) && $_REQUEST['extern'] == '1') || (isset($_REQUEST['pdf']) && $_REQUEST['pdf'] == '1')) {
		$link_anzeige_abfr = 'true';
		$_SESSION['jobAdExtern'] = true;
		
		if (isset($_REQUEST['pdf']) && $_REQUEST['pdf'] == '1') {
			$_SESSION['jobPDF'] = true;
		}
	} else {
		$link_anzeige_abfr = 'false';
	}
	
	$_SESSION['jobAd_sidebar']['content'] = false;
	$_SESSION['jobAd_sidebar']['layout'] = false;
	$_SESSION['jobAd_sidebar']['category'] = false;
	$_SESSION['jobAd_sidebar']['active'] = false;
}

pageheader(array('page_title' => 'Stellenanzeige: Inhalt'));

Praktika_Static::__('job_ad');
?>

<script type="text/javascript" src="/scripts/ajax/stellen_editor.js"></script>
<script type="text/javascript">
	function resizeIframe() {
		if (document.getElementById('layout') != undefined) {
			document.getElementById('layout').style.height = (document.getElementById('layout').contentWindow.document.body.scrollHeight) + "px";
		}
	}
	
	function showDiv1(value) {
		if (document.getElementById('bewerbungperemail').checked == true) {
			document.getElementById('email_bewerbung').readOnly = false;
			document.getElementById('email_bewerbung').className = 'bewerbung_firma enabled';
		} else {
			document.getElementById('email_bewerbung').readOnly = true;
			document.getElementById('email_bewerbung').className = 'bewerbung_firma disabled';
			document.getElementById('email_bewerbung').value = '';
		}
	}

	function showDiv2(value) {
		if (document.getElementById('bewerbungpertel').checked == true) {
			document.getElementById('tel_bewerbung').readOnly = false;
			document.getElementById('tel_bewerbung').className = 'bewerbung_firma enabled';
		} else {
			document.getElementById('tel_bewerbung').readOnly = true;
			document.getElementById('tel_bewerbung').className = 'bewerbung_firma disabled';
			document.getElementById('tel_bewerbung').value = '';
		}
	}
	
	function showDiv3(value) {
		if (document.getElementById('bewerbungpereigen').checked == true) {
			document.getElementById('link_bewerbung').readOnly = false;
			document.getElementById('link_bewerbung').className = 'bewerbung_firma enabled';
		} else {
			document.getElementById('link_bewerbung').readOnly = true;
			document.getElementById('link_bewerbung').className = 'bewerbung_firma disabled';
			document.getElementById('link_bewerbung').value = '';
		}
	}

	function prompt_message(message_number) {		
		switch (message_number) {
			case 1:
				smallbox.alert('Diese Auswahlm&ouml;glichkeit steht Ihnen erst ab dem Stellenpaket BASIS zur Verf&uuml;gung.<br /><br />M&ouml;chten Sie diese Funktion freischalten?<br />Hier finden Sie eine <a href="/firmen/stelle/paket/"><strong>&Uuml;bersicht unserer Produkte</strong></a>.');
				break;
			case 2:
				smallbox.alert('Diese Auswahlm&ouml;glichkeit steht Ihnen erst ab dem Stellenpaket KOMFORT zur Verf&uuml;gung.<br /><br />M&ouml;chten Sie diese Funktion freischalten?<br />Hier finden Sie eine <a href="/firmen/stelle/paket/"><strong>&Uuml;bersicht unserer Produkte</strong></a>.');
				break;
		}
	}
</script>


</script>

<?php
if ($templateid != 0 && ($isEigenes == 'false' || $isEditable == 'true') && $link_anzeige_abfr == 'false') {
?>

<div id="stelleneditor">
	<div class="faq_box hint2" id="faq_box">Zum Bearbeiten klicken Sie den Text an, den Sie ver&auml;ndern m&ouml;chten.<br />Ab einer KOMFORT-Lizenz stehen Ihnen erweiterte Formatierungsm&ouml;glichkeiten zur Verf&uuml;gung.<br />Vergessen Sie nicht, die &Auml;nderungen mit dem <strong>"Speichern"</strong>-Button zu best&auml;tigen.</div>
	
	<div id="editorField" class="editorField">
		<h4>F&uuml;llen Sie Ihre Stellenanzeige mit Inhalt:</h4>
		<iframe id="layout" designMode="on" frameborder="0" src="/home/firmen/stelle/content_iframe.php"></iframe>
	</div>
</div>

<?php
	echo '<form method="post" onsubmit="return checkExit();" id="jobAdvertisementForm" action="http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '').'://'.$_SERVER['HTTP_HOST'].'/firmen/stelle/'.$_SESSION['jobAd']['stellenid'].'/content.html">'."\n";
} else if($isEigenes == 'false' && $link_anzeige_abfr == 'true') {
    echo '<script type="text/javascript" src="/scripts/smallbox.js"></script><script type="text/javascript" src="/scripts/microajax.js"></script><form method="post" id="jobAdvertisementForm" action="http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '').'://'.$_SERVER['HTTP_HOST'].'/firmen/stelle/'.$_SESSION['jobAd']['stellenid'].'/content_extern.html">'."\n";
	echo '	<h4>Link zur Stellenanzeige</h4>'."\n";
	echo '	<fieldset>'."\n";
	echo '	  <p>'."\n";
	echo '	    <label for="taetigkeit">T&auml;tigkeit:</label>'."\n";
	echo '	    <input value="'.((isset($taetigkeit) && !empty($taetigkeit) ) ? $taetigkeit : 'PLATZHALTER Text zur Stellenbezeichnung').'" id="taetigkeit" name="taetigkeit" maxlength="255" type="text" />'."\n";
	echo '	  </p>'."\n";
	
	if (isset($error) && $error == true) {
		echo '	<p class="error">Bitte geben Sie einen Link zur Stellenanzeige ein.</p>'."\n";
	}
	
	echo '	  <p>'."\n";
	echo '	    <label for="link_anzeige"'.((isset($error) && $error == true) ? ' class="error"' : '').'>Link zu Ihre'.((isset($_SESSION['jobPDF']) && $_SESSION['jobPDF'] == true) ? 'm PDF-Dokument: (<a href="#" onclick=\'smallbox.loadFrame("/filemanager.html",{width:"600px", height:"400px", callback:function(e) { if(e != false) $("link_anzeige").value = "https://www.praktika.de" + e; }}); return false;\'>Dateimanagement</a>)' : 'r Stellenanzeige:').'</label>'."\n";
	echo '	    <input'.((isset($link_anzeige) && !empty($link_anzeige) ) ? ' value="'.$link_anzeige.'"' : ' value="http://"').' id="link_anzeige" name="link_anzeige" maxlength="255" type="text" />'."\n";
	echo '	  </p>'."\n";
	echo '	</fieldset>'."\n";
} else {
	echo '<form method="post" id="jobAdvertisementForm" action="http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '').'://'.$_SERVER['HTTP_HOST'].'/firmen/stelle/'.$_SESSION['jobAd']['stellenid'].'/content.html">'."\n";
	echo '	<h4>Taetigkeit</h4>'."\n";
	echo '	<fieldset>'."\n";
	echo '	  <p>'."\n";
	echo '	    <label for="taetigkeit">T&auml;tigkeit:</label>'."\n";
	echo '	    <input value="'.((isset($taetigkeit) && !empty($taetigkeit) ) ? $taetigkeit : 'PLATZHALTER Text zur Stellenbezeichnung').'" id="taetigkeit" name="taetigkeit" maxlength="255" type="text" />'."\n";
	echo '	  </p>'."\n";
	echo '	</fieldset>'."\n";	
}

$bearbeiter = mysql_db_query($database_db,'SELECT id,anrede,vname,name,email,abttelefon FROM bearbeiter WHERE firmenid='.$_SESSION['s_firmenid'].' AND inactive = \'false\'',$praktdbslave);
if ($bearbeiter != false) {
	$anz_bearbeiter = mysql_num_rows($bearbeiter);
} else {
	$anz_bearbeiter = 0;
}

if ($_SESSION['s_firmenlevel'] == 0) {
	$bewerbungperonline = 'true';
}

echo '	<h4>Wie m&ouml;chten Sie Ihre Bewerbungen erhalten?</h4>'."\n";
echo '	<fieldset>'."\n";
echo '		<p class="checkboxes">'."\n";
echo '			<input type="checkbox" style="" id="bewerbungperonline"'.(($_SESSION['s_firmenlevel'] < 1) ? ' disabled="disabled" ' : '').'name="bewerbungperonline" value="true"'.((isset($bewerbungperonline) && $bewerbungperonline == 'true') ? ' checked="checked"' : '').' />'."\n";
echo '			<label for="bewerbungperonline" title="'.(($_SESSION['s_firmenlevel'] < 1) ? 'Auswahl erst ab Stellenpaket BASIS m&ouml;glich" onclick="prompt_message(1);' : 'Ausw&auml;hlen').'">praktika.de Online-Bewerbungsmappe'.($_SESSION['s_firmenlevel'] < 1 ? '<sup>1</sup>' : '').'</label>'."\n";
echo '		</p>'."\n";
echo '		<p class="checkboxes">'."\n";
echo '			<input type="checkbox" id="bewerbungperpost"'.(($_SESSION['s_firmenlevel'] < 1) ? ' disabled="disabled" ' : '').'name="bewerbungperpost" value="true"'.((isset($bewerbungperpost) && $bewerbungperpost == 'true' && $_SESSION['s_firmenlevel'] >= 1) ? ' checked="checked"' : '').' />'."\n";
echo '			<label for="bewerbungperpost" title="'.(($_SESSION['s_firmenlevel'] < 1) ? 'Auswahl erst ab Stellenpaket BASIS m&ouml;glich" onclick="prompt_message(1);' : 'Ausw&auml;hlen').'">per Post'.($_SESSION['s_firmenlevel'] < 1 ? '<sup>1</sup>' : '').'</label>'."\n";
echo '		</p>'."\n";
echo '		<p class="checkboxes clearfix">'."\n";
echo '			<input type="checkbox" id="bewerbungperemail"'.(($_SESSION['s_firmenlevel'] < 1) ? ' disabled="disabled" ' : '').'name="bewerbungperemail" value="true"'.((isset($bewerbungperemail) && $bewerbungperemail == 'true' && $_SESSION['s_firmenlevel'] >= 1) ? ' checked="checked"' : '').' onclick="showDiv1(this.value);" />'."\n";
echo '			<label for="bewerbungperemail" class="bewerbung_firma" title="'.(($_SESSION['s_firmenlevel'] < 1) ? 'Auswahl erst ab Stellenpaket BASIS m&ouml;glich" onclick="prompt_message(1);' : 'Ausw&auml;hlen').'">per eMail'.($_SESSION['s_firmenlevel'] < 1 ? '<sup>1</sup>' : '').'</label> <input'.(($_SESSION['s_firmenlevel'] < 1) ? ' readonly="readonly" ' : ' ').'type="text" id="email_bewerbung" name="email_bewerbung" value="'.($_SESSION['s_firmenlevel'] >= 1 ? ((isset($email_bewerbung) && !empty($email_bewerbung)) ? $email_bewerbung : htmlspecialchars(mysql_result($bearbeiter, 0, 'email'))) : '').'" maxlength="255" class="bewerbung_firma'.((isset($bewerbungperemail) && $bewerbungperemail == 'true' && $_SESSION['s_firmenlevel'] >= 1) ? ' enabled"' : ' disabled" readonly="readonly"').' />'."\n";
echo '		</p>'."\n";
echo '		<p class="checkboxes clearfix">'."\n";
echo '			<input type="checkbox" id="bewerbungpertel"'.(($_SESSION['s_firmenlevel'] < 1) ? ' disabled="disabled" ' : '').'name="bewerbungpertel" value="true"'.((isset($bewerbungpertel) && $bewerbungpertel == 'true') ? ' checked="checked"' : '').' onclick="showDiv2(this.value);" />'."\n";
echo '			<label for="bewerbungpertel" class="bewerbung_firma" title="'.(($_SESSION['s_firmenlevel'] < 1) ? 'Auswahl erst ab Stellenpaket BASIS m&ouml;glich" onclick="prompt_message(1);' : 'Ausw&auml;hlen').'">per Telefon'.($_SESSION['s_firmenlevel'] < 1 ? '<sup>1</sup>' : '').'</label> <input type="text" id="tel_bewerbung" name="tel_bewerbung" value="'.((isset($tel_bewerbung) && !empty($tel_bewerbung)) ? $tel_bewerbung : htmlspecialchars(mysql_result($bearbeiter,0,'abttelefon'))).'" maxlength="255" class="bewerbung_firma'.((isset($bewerbungpertel) && $bewerbungpertel == 'true') ? ' enabled"' : ' disabled" readonly="readonly"').' />'."\n";
echo '		</p>'."\n";
echo '		<p class="checkboxes clearfix">'."\n";
echo '			<input type="checkbox" id="bewerbungpereigen" name="bewerbungpereigen" value="true"'.(($_SESSION['s_firmenlevel'] < 2) ? ' disabled="disabled"' : ((isset($bewerbungpereigen) && $bewerbungpereigen == 'true') ? ' checked="checked"' : '')).' onclick="showDiv3(this.value);" />'."\n";
echo '			<label for="bewerbungpereigen" class="bewerbung_firma" title="'.(($_SESSION['s_firmenlevel'] < 2) ? 'Auswahl erst ab KOMFORT m&ouml;glich" onclick="prompt_message(2);' : 'Ausw&auml;hlen').'">&uuml;ber eigene Webseite'.($_SESSION['s_firmenlevel'] < 1 ? '<sup>2</sup>' : ($_SESSION['s_firmenlevel'] < 2 ? '<sup>1</sup>' : '')).'</label> <input type="text" id="link_bewerbung" name="link_bewerbung" value="'.((isset($link_bewerbung) && !empty($link_bewerbung)) ? $link_bewerbung : '').'" maxlength="255" class="bewerbung_firma'.((isset($bewerbungpereigen) && $bewerbungpereigen == 'true') ? ' enabled"' : ' disabled" readonly="readonly"').' />'."\n";
echo '		</p>'."\n";
echo '		<p class="hint" style="text-align: left; width: 100%;">';

$i = 1;

if ($_SESSION['s_firmenlevel'] < 1) {
	echo '<sup>'.$i.'</sup> Diese Auswahlm&ouml;glichkeit steht Ihnen erst ab dem Stellenpaket BASIS zur Verf&uuml;gung.<br />'."\n";
	$i++;
}

if ($_SESSION['s_firmenlevel'] < 2) {
	echo '<sup>'.$i.'</sup> Diese Auswahlm&ouml;glichkeit steht Ihnen erst ab dem Stellenpaket KOMFORT zur Verf&uuml;gung.<br />M&ouml;chten Sie diese Funktion freischalten? Hier finden Sie eine <a href="/firmen/stelle/paket/"><strong>&Uuml;bersicht unserer Produkte</strong></a>.</p>'."\n";
	$i++;
}

echo '</p>'."\n";
echo '	</fieldset>'."\n";
echo '	<h4>Ansprechpartner f&uuml;r diese Stelle</h4>'."\n";
echo '	<fieldset>'."\n";
echo '		<p>'."\n";
echo '			<label for="ansprechpartnerid">Bewerbung an:</label>'."\n";
echo '			<select id="ansprechpartnerid" name="ansprechpartnerid">'."\n";

$i = 0;
while ($i < $anz_bearbeiter) {
	$html = htmlspecialchars(mysql_result($bearbeiter,$i,'name')).', '.htmlspecialchars(mysql_result($bearbeiter,$i,'vname'));
	echo '				<option'.((isset($ansprechpartnerid) && mysql_result($bearbeiter,$i,'id') == $ansprechpartnerid) ? ' selected="selected"' : '').' value="'.mysql_result($bearbeiter,$i,'id').'">'.$html.'</option>'."\n";
	
	$i++;
};

echo '			</select>'."\n";
echo '		</p>'."\n";		
echo '	</fieldset>'."\n";
echo '	<fieldset class="control_panel">'."\n";
echo '		<p>'."\n";
echo '			<button type="submit" name="save_content" value="weiter"><span><span><span>WEITER</span></span></span></button>'."\n";
echo '		</p>'."\n";
echo '	</fieldset>'."\n";
echo '</form>'."\n";
?>

<?php
$_SESSION['sidebar'] = '';
bodyoff();
?>