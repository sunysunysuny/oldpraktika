<?php
$compatibleIE = 9;

require("/home/praktika/etc/config.inc.php");

if (!isset($_SESSION['s_loggedin']) || $_SESSION['s_loggedin'] == false) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/login');
	exit();
}

if (isset($_REQUEST['unternehmens_id']) && !isset($_SESSION['unternehmens_id'])) {
	$_SESSION['unternehmens_id'] = intval($_REQUEST['unternehmens_id']);
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

			if(substr($_REQUEST["link_anzeige"],-4) == ".pdf") {
				$_POST['beschreibung'] = Praktika_Pdf::pdf2text($_REQUEST['link_anzeige']);
				#var_dump($_POST["beschreibung"]);
				#exit();
				# $_SESSION['jobAd']["beschreibung"] = $_POST["be"];
				# echo "AUSLESEN"; exit();
			}
			$_SESSION['jobAd']['link_anzeige'] = $_REQUEST['link_anzeige'];
		}
		
		if (isset($_REQUEST['link_bewerbung']) && !empty($_REQUEST['link_bewerbung'])) {
			if (strpos($_REQUEST['link_bewerbung'], 'http://', 0) === false) {
				$_REQUEST['link_bewerbung'] = 'http://'.$_REQUEST['link_bewerbung'];
			}
			
			$_SESSION['jobAd']['link_bewerbung'] = $_REQUEST['link_bewerbung'];
			$_SESSION['jobAd']['bewerbungpereigen'] = 'true';
		}
		
		if (isset($_REQUEST['taetigkeit']) && !empty($_REQUEST['taetigkeit'])) {
			$_SESSION['jobAd']['taetigkeit'] = $_REQUEST['taetigkeit'];
		}
		
		if (intval($_SESSION['jobAd']['stellenid']) == 0) {
			$_POST = array_merge($_POST, $_SESSION['jobAd']);
			$sql = 'INSERT INTO '.$database_db.'.stellen SET ';
		} else {
			$sql = 'UPDATE '.$database_db.'.stellen SET ';
		}

		if(!empty($_POST['einsatzort'])) {
			$_POST['einsatzort'] = html_entity_decode($_POST['einsatzort']);
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

		$result = $hDB->query($sql, $praktdbmaster);
		
		if (intval($_SESSION['jobAd']['stellenid']) == 0) {
			unset($_SESSION['jobAd']);
		
			$_SESSION['jobAd']['stellenid'] = mysql_insert_id();
		}
	
		$objStelle = new Praktika_Stelle($_SESSION['jobAd']['stellenid']);
		$objStelle->cleanCache();
	
		$_SESSION['jobAd_sidebar']['content'] = true;

		// Stelle einer Agentur?
		if (isset($_SESSION['unternehmens_id'])) {
			$agentur = new Praktika_Firmen_Agentur($_SESSION['s_firmenid']);
			$stelle = $agentur->insertStelle($_SESSION['jobAd']['stellenid'], $_SESSION['unternehmens_id']);
		}

		header('Location: /firmen/stelle/'.$_SESSION['jobAd']['stellenid'].'/layout.html');
		exit();
	}
}

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
						'.$database_db.'.stellen 
						LEFT JOIN '.$database_db.'.stellentemplates ON (stellentemplates.id = stellen.templateid)				
					WHERE
						stellen.id = '.$_SESSION['jobAd']['stellenid'].'
						AND stellen.firmenid = '.$_SESSION['s_firmenid'];

	$stelle = $hDB->query($sqlStelle, $praktdbslave);

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
		# $beschreibung = Praktika_Pdf::pdf2text($link_anzeige);		
	}


	if ($link_anzeige_abfr == 'true') {
		$_SESSION['jobAdExtern'] = true;
		$isEigenes = 'false';
	}

	$_SESSION['jobAd_sidebar']['content'] = true;
	$_SESSION['jobAd_sidebar']['layout'] = true;
	$_SESSION['jobAd_sidebar']['category'] = (empty($stichwort) || ($schuelerprakt == 'false' && $hochschulprakt == 'false' && $wprakt == 'false' && $sonstprakt == 'false')) ? false : true;
	$_SESSION['jobAd_sidebar']['active'] = $inactive == 'false' ? true : false;
} else {
	$_SESSION['neue_stelle'] = true;
	
	if (!isset($_SESSION['jobAd'])) {
		$_SESSION['jobAd']['stellenid'] = 0;

		// Stelle einer Agentur?
		if ($_SESSION['unternehmens_id']) {
			//Daten der Firma
			$sql = 'SELECT
						name AS firma,
						CONCAT(name, \'<br />\', strasse, \'<br />\', plz, \' \', ort, \'<br />eMail: \', email, \'<br />Telefon: \', telefon, \'<br />Fax: \', fax, \'<br />Webseite: \', webseite) AS firmenkontakt,
						strasse,
						plz,
						land,
						bundesland,
						grossraum,
						ort AS einsatzort,
						email,
						telefon AS tel,
						fax,
						webseite AS homepage,
						branche
					FROM
						' . $database_db . '.firmen_agentur
					WHERE
						id = ' . intval($_SESSION['unternehmens_id']);
		} else {
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
						' . $database_db . '.firmen
					WHERE
						id = ' . intval($_SESSION['s_firmenid']);
		}

		$result = $hDB->query($sql, $praktdbslave);
		$row = mysql_fetch_assoc($result);
		
		$row['einsatzort'] = Praktika_String::getUtf8String($row['einsatzort']);
		foreach ($row as $key => $value) {
			$kontakt[$key] = $value;
		}
		
		//Bereich bestimmen
		$sqlBranche = $hDB->query('SELECT german FROM '.$database_db.'.branchen WHERE id = '.$kontakt['branche'], $praktdbslave);
		$branche = mysql_result($sqlBranche, 0, 'german');
		
		//Vorgeplaenkel
		$_SESSION['jobAd']['inactive'] = 'true';
		$_SESSION['jobAd']['bearbeiterid'] = intval($_SESSION['s_loginid']);
		$_SESSION['jobAd']['firmenid'] = intval($_SESSION['s_firmenid']);
		$_SESSION['jobAd']['firmen_agentur_id'] = isset($_SESSION['unternehmens_id']) ? intval($_SESSION['unternehmens_id']) : 0;
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
		$_SESSION['jobAd']['einsatzort'] = htmlentities($kontakt['einsatzort']);
		
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
		$_SESSION['jobAd']['taetigkeit'] = 'Praktikum im Bereich '.$branche;
		$_SESSION['jobAd']['subtitle'] = 'am Standort '.$kontakt['einsatzort'];
		$_SESSION['jobAd']['firmeninfosHead'] = 'Zu unserem Unternehmen';
		$_SESSION['jobAd']['firmentaetigkeitHead'] = 'Das kommt auf Sie zu';
		$_SESSION['jobAd']['firmenqualiHead'] = 'Ihr Profil';
		$_SESSION['jobAd']['firmenperspHead'] = 'Unsere Leistungen';
		$_SESSION['jobAd']['firmenkontaktHead'] = 'Kontaktaufnahme';
		$_SESSION['jobAd']['firmeninfos'] = 'Wir sind ein innovatives und expandierendes Medienunternehmen. Als f&uuml;hrender Dienstleister in diesem Bereich liegt unser FoKus auf Kunden-L&ouml;sungen.';
		$_SESSION['jobAd']['firmentaetigkeit'] = 'Ihre Aufgabe ist die Unterst&uuml;tzung der Manager bei der Strategieplanung und -umsetzung. In der Regel werden immer kleine Projekte vergeben, an denen Sie selbstst&auml;ndig arbeiten k&ouml;nnen. Den wesentlichen Teil der T&auml;tigkeit bilden die Analysen sowie die Erstellung betriebswirtschaftlicher Auswertungen. Des Weiteren sind Sie zust&auml;ndig f&uuml;r die Verwaltung und Strukturierung der ben&ouml;tigten Datenbasis. Au&szlig;erdem stellt die Vorbereitung von Pr&auml;sentationen der Projektergebnisse in turnusm&auml;&szlig;igen Managementpr&auml;sentationen einen wichtigen T&auml;tigkeitsbereich dar. Dar&uuml;ber hinaus soll dem Praktikant Gelegenheit gegeben werden, die Aufgabengebiete benachbarter Bereiche kennenzulernen sowie von der Lehranstalt vorgeschriebene Praktikumsberichte etc. anzufertigen.';
		$_SESSION['jobAd']['firmenquali'] = '- immatrikulierter Student (m/w) folgender oder vergleichbarer Studieng&auml;nge: Betriebswirtschaft<br />- gute Deutsch- und Englisch-Kenntnisse<br />- sehr gute Kenntnisse der g&auml;ngigen MS Office-Anwendungen (Excel, Word, PowerPoint, Access)<br />- Teamf&auml;higkeit und Zuverl&auml;ssigkeit<br />- Eigeninitiative und Einsatzbereitschaft<br />- eigenverantwortliche und selbst&auml;ndige Arbeitsweise';
		$_SESSION['jobAd']['firmenpersp'] = '- Wir sind ein junges, dynamisches Team, in dem eigenst&auml;ndiges Arbeiten gef&ouml;rdert wird<br />- Wir bieten verantwortungsvolle Mitarbeit in Projekten f&uuml;r weltweit agierende Unternehmen<br />- Unsere Mitarbeiter werden fortw&auml;hrend weiterqualifiziert, die Umsetzung erfolgt direkt im Projekt<br />- Wir bieten die M&ouml;glichkeit, in F&uuml;hrungsaufgaben hineinzuwachsen<br />- Wir sind loyal, zuverl&auml;ssig und verantwortungsbewusst unseren Kunden sowie Mitarbeitern gegen&uuml;ber';
		$_SESSION['jobAd']['firmenkontakt'] = $kontakt['firmenkontakt'];
		$_SESSION['jobAd']['link_anzeige_abfr'] = ((isset($_REQUEST['extern']) && $_REQUEST['extern'] == '1') || (isset($_REQUEST['pdf']) && $_REQUEST['pdf'] == '1')) ? 'true' : 'false';
		$_SESSION['jobAd']['link_anzeige'] = '';
		$_SESSION['jobAd']['link_bewerbung'] = '';
		$_SESSION['jobAd']['beschreibung'] = 'Als Dauer f&uuml;r ein solches Praktikum sehen wir idealerweise 3 bis 6 Monate vor. Bitte nutzen Sie f&uuml;r Ihre Bewerbung das Online-System von praktika.de. Wir freuen uns auf Ihre Bewerbung.';
		$_SESSION['jobAd']['entgelt'] = 'Diese Stelle wird nicht verg&uuml;tet.';
		
		//Adressdaten
		$_SESSION['jobAd']['kontakt2strasse'] = ($kontakt['strasse']);
		$_SESSION['jobAd']['kontakt2plz'] = ($kontakt['plz']);
		$_SESSION['jobAd']['kontakt2ort'] = ($kontakt['einsatzort']);
		$_SESSION['jobAd']['kontakt2ansprechpartner'] = ($bearbeiterKontakt['anrede']." ".$bearbeiterKontakt['vname']." ".$bearbeiterKontakt['name']);
		$_SESSION['jobAd']['kontakt2tel'] = $bearbeiterKontakt['tel'];
		$_SESSION['jobAd']['kontakt2fax'] = $bearbeiterKontakt['fax'];
		$_SESSION['jobAd']['kontakt2email'] = ($bearbeiterKontakt['email_bewerbung']);
		$_SESSION['jobAd']['kontakt2webseite'] = ($kontakt['homepage']);
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
<script type="text/javascript">promptButtons = ['<' + 'a onclick="smallbox.hide(\'upgrade\')" class="button red small">' + 'Ja' + '<' + '/a' + '>','<' + 'a onclick="smallbox.hide()" class="button red small">' + 'Nein' + '<' + '/a' + '>']</script>
<script type="text/javascript">promptButtons2 = ['<' + 'a onclick="smallbox.hide(\'upgrade\')" class="button red small">' + 'Ja' + '<' + '/a' + '>','<' + 'a onclick="smallbox.hide(\'layout\')" class="button red small">' + 'Nein' + '<' + '/a' + '>']</script>
<script type="text/javascript">
	function checkEditor() {
		if(top.layout.editor != undefined) {
			top.layout.save(top.layout.editor);
		}
	}
	
	function resizeIframe() {
		if (document.getElementById('layout') != undefined) {
			document.getElementById('layout').style.height = $(document.getElementById('layout').contentWindow.document).height() + "px";
		}
	}
	
	function showDiv1(value) {
		if (document.getElementById('bewerbungperemail').checked == true) {
			document.getElementById('email_bewerbung').disabled = false;
			document.getElementById('email_bewerbung').focus();
		} else {
			document.getElementById('email_bewerbung').disabled = true;
			document.getElementById('email_bewerbung').value = '';
		}
	}

	function showDiv2(value) {
		if (document.getElementById('bewerbungpertel').checked == true) {
			document.getElementById('tel_bewerbung').disabled = false;
			document.getElementById('tel_bewerbung').focus();
		} else {
			document.getElementById('tel_bewerbung').disabled = true;
			document.getElementById('tel_bewerbung').value = '';
		}
	}
	
	function showDiv3(value) {
		if (document.getElementById('bewerbungpereigen').checked == true) {
			document.getElementById('link_bewerbung').disabled = false;
			document.getElementById('link_bewerbung').focus();
		} else {
			document.getElementById('link_bewerbung').disabled = true;
			document.getElementById('link_bewerbung').value = '';
		}
	}

	function prompt_message(message_number) {		
		switch (message_number) {
			case 1:
				smallbox.confirm('Diese Auswahlm&ouml;glichkeit steht Ihnen erst ab dem Stellenpaket ' + '<' + 'strong' + '>' + 'BASIS' + '<' + '/strong' + '>' + ' zur Verf&uuml;gung.' + '<' + 'br /' + '>' + '<' + 'br /' + '>' + 'M&ouml;chten Sie diese Auswahlm&ouml;glichkeit freischalten?' + '<' + 'br /' + '>', promptButtons, function(e) { if (e=='upgrade') {location.href = '/firmen/stelle/paket.html';}}); return true;
				break;
			case 2:
				smallbox.confirm('Diese Auswahlm&ouml;glichkeit steht Ihnen erst ab dem Stellenpaket ' + '<' + 'strong' + '>' + 'KOMFORT' + '<' + '/strong' + '>' + ' zur Verf&uuml;gung.' + '<' + 'br /' + '>' + '<' + 'br /' + '>' + 'M&ouml;chten Sie diese Auswahlm&ouml;glichkeit freischalten?' + '<' + 'br /' + '>', promptButtons, function(e) { if (e=='upgrade') {location.href = '/firmen/stelle/paket.html';}}); return true;
				break;
		}
	}
</script>

<?php
if (isset($templateid) && intval($templateid) != 1000 && (($_SESSION['s_firmenlevel'] == 0 && intval($templateid) != 109) || ($_SESSION['s_firmenlevel'] == 1 && intval($templateid) != 109 && intval($templateid) != 11 && in_array(intval($templateid), range(2, 101))))) {
	echo '	<form action="/firmen/stelle/paket/" method="post" class="error">'."\n";
	echo '		<fieldset class="control_panel">'."\n";
	if($_SESSION['s_firmenlevel'] == 0 && intval($templateid) != 109) {
		echo '			<p>In dieser Stellenanzeige nutzen Sie momentan ein Layout, welches in der FREE Lizenz nicht verfügbar ist.<br />Sie m&uuml;ssen zuerst ein neues Layout ausw&auml;hlen oder in ein höheres Paket wechseln, bevor Sie diese Stelle bearbeiten k&ouml;nnen.<br /><br />M&ouml;chten Sie dieses Layout weiterhin nutzen?</p>'."\n";
	}
	echo '			<p class="center">'."\n";
	echo '				<input class="button" type="submit" name="confirm" value="Ja" />'."\n";
	echo '				<input class="button" type="button" name="deny" onclick="location.href = \'/firmen/stelle/'.$_SESSION['jobAd']['stellenid'].'/layout.html\';" value="Nein, Layout &auml;ndern" />'."\n";
	echo '			</p>'."\n";
	echo '		</fieldset>'."\n";
	echo '	</form>'."\n";
} else {
	if ($templateid != 0 && ($isEigenes == 'false' || $isEditable == 'true') && $link_anzeige_abfr == 'false') {
?>

<div id="stelleneditor">
	<p class="hint info">Zum Bearbeiten klicken Sie den Text an, den Sie ver&auml;ndern m&ouml;chten.<br />Ab einer KOMFORT-Lizenz stehen Ihnen erweiterte Formatierungsm&ouml;glichkeiten zur Verf&uuml;gung.<br />Vergessen Sie nicht, die &Auml;nderungen mit dem <strong>"Speichern"</strong>-Button zu best&auml;tigen.</p>

    <h3>F&uuml;llen Sie Ihre Stellenanzeige mit Inhalt:</h3>
	<div id="editorField" class="editorField">
		<iframe id="layout" name="layout" designMode="on" frameborder="0" src="/home/firmen/stelle/content_iframe.php"></iframe>
	</div>
</div>

<?php
		echo '<form method="post" onsubmit="return checkExit();" id="jobAdvertisementForm" action="http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '').'://'.$_SERVER['HTTP_HOST'].'/firmen/stelle/'.$_SESSION['jobAd']['stellenid'].'/content.html">'."\n";
		if($isEditable == 'true') {
			$alreadyURL = true;
			echo '	<fieldset>'."\n";
			echo '		<h4>Geben Sie hier einen Link zur eigenen Bewerbungsseite an, um direkte Bewerbungen zu erhalten.</h4>'."\n";
			echo '		<p>'."\n";
			echo '			<input type="text" id="link_bewerbung" name="link_bewerbung" class="link_bewerbung" value="'.((isset($link_bewerbung) && !empty($link_bewerbung)) ? $link_bewerbung : '').'" maxlength="400" '.($_SESSION['s_firmenlevel'] < 2 ? ' readonly="readonly" onclick="prompt_message(2); return false;"' : '').' />'."\n";
			echo '		</p>'."\n";
			echo '	</fieldset>'."\n";
		}
	} else if ($isEigenes == 'false' && $link_anzeige_abfr == 'true') {
	    echo '<script type="text/javascript" src="/scripts/microajax.js"></script><form method="post" id="jobAdvertisementForm" action="http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '').'://'.$_SERVER['HTTP_HOST'].'/firmen/stelle/'.$_SESSION['jobAd']['stellenid'].'/content_extern.html">'."\n";
		echo '	<h4>Link zur Stellenanzeige</h4>'."\n";
		echo '	<fieldset>'."\n";
		echo '	  <p>'."\n";
		echo '	    <label for="taetigkeit">T&auml;tigkeit:</label>'."\n";
		echo '	    <input value="'.((isset($taetigkeit) && !empty($taetigkeit) ) ? htmlentities($taetigkeit) : 'PLATZHALTER Text zur Stellenbezeichnung').'" id="taetigkeit" name="taetigkeit" size="70" class="html_controlled" type="text" />'."\n";
		echo '	  </p>'."\n";
		
		if (isset($error) && $error == true) {
			echo '	<p class="error">Bitte geben Sie einen Link zur Stellenanzeige ein.</p>'."\n";
		}
		
		echo '	  <p>'."\n";
		echo '	    <label for="link_anzeige"'.((isset($error) && $error == true) ? ' class="error"' : '').'>Link zu Ihre'.((isset($_SESSION['jobPDF']) && $_SESSION['jobPDF'] == true) ? 'm PDF-Dokument: (<a href="#" onclick=\'smallbox.loadFrame("/filemanager.html",{width:"600", height:"400", callback:function(e) { if(e != false) $("#link_anzeige").val("https://www.praktika.de" + e)}}); return false;\'>Dateimanagement</a>)' : 'r Stellenanzeige:').'</label>'."\n";
		echo '	    <input'.((isset($link_anzeige) && !empty($link_anzeige) ) ? ' value="'.$link_anzeige.'"' : ' value="http://"').' id="link_anzeige" name="link_anzeige" size="70" class="html_controlled"  type="text" />'."\n";
		echo '	  </p>'."\n";
		echo '	</fieldset>'."\n";
	} else {
		echo '<form method="post" id="jobAdvertisementForm" action="http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '').'://'.$_SERVER['HTTP_HOST'].'/firmen/stelle/'.$_SESSION['jobAd']['stellenid'].'/content.html">'."\n";
		echo '	<h4>Taetigkeit</h4>'."\n";
		echo '	<fieldset>'."\n";
		echo '	  <p>'."\n";
		echo '	    <label for="taetigkeit">T&auml;tigkeit:</label>'."\n";
		echo '	    <input value="'.((isset($taetigkeit) && !empty($taetigkeit) ) ? htmlentities($taetigkeit) : 'PLATZHALTER Text zur Stellenbezeichnung').'" id="taetigkeit" name="taetigkeit" maxlength="255" type="text" />'."\n";
		echo '	  </p>'."\n";
		echo '	</fieldset>'."\n";	
	}
	
	if ($link_anzeige_abfr == 'true' || $isEigenes == 'true') {
		$bearbeiter = mysql_db_query($database_db,'SELECT id,anrede,vname,name,email,abttelefon FROM bearbeiter WHERE firmenid='.$_SESSION['s_firmenid'].' AND inactive = \'false\'',$praktdbslave);
		if ($bearbeiter != false) {
			$anz_bearbeiter = mysql_num_rows($bearbeiter);
		} else {
			$anz_bearbeiter = 0;
		}
		
		if ($_SESSION['s_firmenlevel'] == 0) {
			$bewerbungperonline = 'true';
		}
		
		if($alreadyURL != true) {
			echo '		<h4>Geben Sie hier einen Link zur eigenen Bewerbungsseite an, um direkte Bewerbungen zu erhalten.</h4>'."\n";
			echo '	<fieldset>'."\n";
			echo '		<p>'."\n";
			echo '			<input type="text" id="link_bewerbung" name="link_bewerbung" class="link_bewerbung" value="'.((isset($link_bewerbung) && !empty($link_bewerbung)) ? $link_bewerbung : '').'" maxlength="400" '.($_SESSION['s_firmenlevel'] < 2 ? ' readonly="readonly" onclick="prompt_message(2); return false;"' : '').' />'."\n";
			echo '		</p><br />'."\n";
			echo '	</fieldset>'."\n"; 
		}

		echo '	<h4>&Uuml;ber welche anderen Wege, m&ouml;chten Sie Ihre Bewerbung erhalten?</h4>'."\n";
		echo '	<fieldset>'."\n";
		echo '		<p class="checkboxes">'."\n";
		echo '			<label for="bewerbungperonline" class="komplette_breite" title="'.(($_SESSION['s_firmenlevel'] < 1) ? 'Auswahl erst ab Stellenpaket BASIS m&ouml;glich" onclick="prompt_message(1);' : 'Ausw&auml;hlen').'"><input type="checkbox" style="" id="bewerbungperonline"'.(($_SESSION['s_firmenlevel'] < 1) ? ' disabled="disabled" ' : '').'name="bewerbungperonline" value="true"'.((isset($bewerbungperonline) && $bewerbungperonline == 'true') ? ' checked="checked"' : '').' /> praktika.de Online-Bewerbungsmappe'.($_SESSION['s_firmenlevel'] < 1 ? '<sup>1</sup>' : '').'</label>'."\n";
		# echo '			<label for="bewerbungperpost" class="komplette_breite" title="'.(($_SESSION['s_firmenlevel'] < 1) ? 'Auswahl erst ab Stellenpaket BASIS m&ouml;glich" onclick="prompt_message(1);' : 'Ausw&auml;hlen').'"><input type="checkbox" id="bewerbungperpost"'.(($_SESSION['s_firmenlevel'] < 1) ? ' disabled="disabled" ' : '').'name="bewerbungperpost" value="true"'.((isset($bewerbungperpost) && $bewerbungperpost == 'true' && $_SESSION['s_firmenlevel'] >= 1) ? ' checked="checked"' : '').' /> per Post'.($_SESSION['s_firmenlevel'] < 1 ? '<sup>1</sup>' : '').'</label>'."\n";
		echo '			<div class="clear"></div><label for="bewerbungperemail" style="text-align:left;" title="'.(($_SESSION['s_firmenlevel'] < 1) ? 'Auswahl erst ab Stellenpaket BASIS m&ouml;glich" onclick="prompt_message(1);' : 'Ausw&auml;hlen').'"><input type="checkbox" id="bewerbungperemail"'.(($_SESSION['s_firmenlevel'] < 1) ? ' disabled="disabled" ' : '').'name="bewerbungperemail" value="true"'.((isset($bewerbungperemail) && $bewerbungperemail == 'true' && $_SESSION['s_firmenlevel'] >= 1) ? ' checked="checked"' : '').' onclick="showDiv1(this.value);" /> per eMail'.($_SESSION['s_firmenlevel'] < 1 ? '<sup>1</sup>' : '').' </label><input'.(($_SESSION['s_firmenlevel'] < 1) ? ' disabled="disabled" ' : ' ').'type="text" id="email_bewerbung" name="email_bewerbung" value="'.($_SESSION['s_firmenlevel'] >= 1 ? ((isset($email_bewerbung) && !empty($email_bewerbung)) ? $email_bewerbung : htmlspecialchars(mysql_result($bearbeiter, 0, 'email'))) : '').'" size="50" class="bewerbung_firma html_controlled"'.((isset($bewerbungperemail) && $bewerbungperemail == 'true' && $_SESSION['s_firmenlevel'] >= 1) ? '' : ' disabled="disabled"').' />'."\n";
		# echo '			<div class="clear"></div><label for="bewerbungpertel" style="text-align:left;" title="'.(($_SESSION['s_firmenlevel'] < 1) ? 'Auswahl erst ab Stellenpaket BASIS m&ouml;glich" onclick="prompt_message(1);' : 'Ausw&auml;hlen').'"><input type="checkbox" id="bewerbungpertel"'.(($_SESSION['s_firmenlevel'] < 1) ? ' disabled="disabled" ' : '').'name="bewerbungpertel" value="true"'.((isset($bewerbungpertel) && $bewerbungpertel == 'true') ? ' checked="checked"' : '').' onclick="showDiv2(this.value);" /> per Telefon'.($_SESSION['s_firmenlevel'] < 1 ? '<sup>1</sup>' : '').' </label><input type="text" id="tel_bewerbung" name="tel_bewerbung" value="'.((isset($tel_bewerbung) && !empty($tel_bewerbung)) ? $tel_bewerbung : htmlspecialchars(mysql_result($bearbeiter,0,'abttelefon'))).'" size="50" class="bewerbung_firma html_controlled"'.((isset($bewerbungpertel) && $bewerbungpertel == 'true') ? '' : ' disabled="disabled"').' /><br>'."\n";
		# echo '			<div class="clear"></div><label for="bewerbungpereigen" style="text-align:left;" title="'.(($_SESSION['s_firmenlevel'] < 2) ? 'Auswahl erst ab KOMFORT m&ouml;glich" onclick="prompt_message(2);' : 'Ausw&auml;hlen').'"><input type="checkbox" id="bewerbungpereigen" name="bewerbungpereigen" value="true"'.(($_SESSION['s_firmenlevel'] < 2) ? 'onclick="prompt_message(2); disabled="disabled"' : ((isset($bewerbungpereigen) && $bewerbungpereigen == 'true') ? ' checked="checked"' : '')).' onclick="showDiv3(this.value);" /> &uuml;ber eigene Webseite'.($_SESSION['s_firmenlevel'] < 1 ? '<sup>2</sup>' : ($_SESSION['s_firmenlevel'] < 2 ? '<sup>1</sup>' : '')).' </label><input type="text" id="link_bewerbung" name="link_bewerbung" value="'.((isset($link_bewerbung) && !empty($link_bewerbung)) ? $link_bewerbung : '').'" size="50" class="bewerbung_firma html_controlled"'.((isset($bewerbungpereigen) && $bewerbungpereigen == 'true') ? '' : ' disabled="disabled"').' /><br>'."\n";

		// work around, damit die Stellen von Shell trotz KOMFORT als PREMIUM bewerbungpereigen besitzen können
		if (intval($_SESSION['s_firmenid']) == 8499) {
			echo '			<div class="clear"></div><label for="bewerbungpereigen" style="text-align:left;" title="'.(($_SESSION['s_firmenlevel'] < 2) ? 'Auswahl erst ab KOMFORT m&ouml;glich" onclick="prompt_message(2);' : 'Ausw&auml;hlen').'"><input type="checkbox" id="bewerbungpereigen" name="bewerbungpereigen" value="true"'.(($_SESSION['s_firmenlevel'] < 2) ? 'onclick="prompt_message(2); disabled="disabled"' : ((isset($bewerbungpereigen) && $bewerbungpereigen == 'true') ? ' checked="checked"' : '')).' onclick="showDiv3(this.value);" /> &uuml;ber eigene Webseite'.($_SESSION['s_firmenlevel'] < 1 ? '<sup>2</sup>' : ($_SESSION['s_firmenlevel'] < 2 ? '<sup>1</sup>' : '')).' </label><input type="text" id="link_bewerbung" name="link_bewerbung" value="'.((isset($link_bewerbung) && !empty($link_bewerbung)) ? $link_bewerbung : '').'" size="50" class="bewerbung_firma html_controlled"'.((isset($bewerbungpereigen) && $bewerbungpereigen == 'true') ? '' : ' disabled="disabled"').' /><br>'."\n";
		}
		
		echo '		</p>'."\n";
		
		$i = 1;
		
		if ($_SESSION['s_firmenlevel'] < 1) {
			echo '		<p class="hint info"><sup>'.$i.'</sup> Diese Auswahlm&ouml;glichkeit steht Ihnen erst ab dem Stellenpaket BASIS zur Verf&uuml;gung.</p>'."\n";

			$i++;
		}
		
		if ($_SESSION['s_firmenlevel'] < 2) {
			echo '		<p class="hint info"><sup>'.$i.'</sup> Diese Auswahlm&ouml;glichkeit steht Ihnen erst ab dem Stellenpaket KOMFORT zur Verf&uuml;gung.<br />M&ouml;chten Sie diese Funktion freischalten? Hier finden Sie eine <a href="/firmen/stelle/paket/"><strong>&Uuml;bersicht unserer Produkte</strong></a>.</p>'."\n";

			$i++;
		}
		
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
	}

	echo '	<fieldset class="control_panel">'."\n";
	echo '		<p>'."\n";
	echo '			<input type="submit" name="save_content" onclick="checkEditor();" value="WEITER" class="button red" />'."\n";
	echo '		</p>'."\n";
	echo '	</fieldset>'."\n";
	echo '</form>'."\n";
}

$_SESSION['sidebar'] = '';
bodyoff();
?>
