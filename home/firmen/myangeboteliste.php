<?php
require("/home/praktika/etc/config.inc.php");

if (!isset($_SESSION['s_loggedin']) || $_SESSION['s_loggedin'] == false) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/login');
	exit();
}

if (isset($_POST['deny'])) {
	header('Location: /firmen/angeboteliste/');
	exit();
}

$_SESSION['sidebar'] = 'none';
$_SESSION['servicecenter'] = 'angeboteliste';
$_SESSION['restricted'] = RESTRICTED_COMPANY;
$_SESSION['current_user_group'] = USER_GROUP_COMPANIES;
$_SESSION['current_page'] = PAGE_PUBLISH_JOBS;

unset($_SESSION['jobAd']);
unset($_SESSION['buchungsseite']);
unset($_SESSION['jobAds_aktivierbar']);
unset($_SESSION['nextJobAdLocked']);
unset($_SESSION['jobAdExtern']);
unset($_SESSION['jobPDF']);
unset($_SESSION['neue_stelle']);
unset($_SESSION['jobAd_sidebar']['content']);
unset($_SESSION['jobAd_sidebar']['layout']);
unset($_SESSION['jobAd_sidebar']['category']);
unset($_SESSION['jobAd_sidebar']['active']);

$firma = new Praktika_Firma($_SESSION['s_firmenid']);

pageheader(array('page_title' => 'Stellenangebote verwalten', 'grid_system' => '6-0'));

Praktika_Static::__('job_ad_list');

$duplicateReload = true;

$stellen = new Praktika_Stellen();

$stellenAnzahl = $stellen->getStellenAnzahl();

if ($_SESSION['s_firmenlevel'] == 0) {
	$linkNewAdEigene = 'prompt_message(11, \'\'); return false;'."\n";
	$linkNewAdPDF = 'prompt_message(11, \'\'); return false;'."\n";
	$linkNewAdPDFEigene = false;
} elseif ($_SESSION['s_firmenlevel'] == 1) {
	$linkNewAdEigene = 'prompt_message(12, \'\'); return false;'."\n";
	$linkNewAdPDF = 'prompt_message(12, \'\'); return false;'."\n";
	$linkNewAdPDFEigene = false;
} else {
	$linkNewAdEigene = 'location.href = \'/firmen/stelle/0/content_extern.html\';'."\n";
	$linkNewAdPDF = 'location.href = \'/firmen/stelle/0/content_pdf.html\';'."\n";
	$linkNewAdPDFEigene = true;
}

/**
 * Darf neue Stelle angelegt werden, oder nicht? -- Begin
 */

if ($_SESSION['s_firmenlevel'] == 0) {
	if (intval($stellenAnzahl) > 1) {
		$linkNewAd = 'prompt_message(5, \'\'); return false;'."\n";
		$linkNewAdEigene = 'prompt_message(5, \'\'); return false;'."\n";
		$linkNewAdPDF = 'prompt_message(5, \'\'); return false;'."\n";
		
		$duplicateReload = false;
	} else {
		$linkNewAd = 'location.href = \'/firmen/stelle/0/content.html\';';
		
		$_SESSION['nextJobAdLocked'] = $stellenAnzahl == 1 ? true : false;
	}
} elseif ($_SESSION['s_firmenlevel'] == 1) {
	if (intval($stellenAnzahl) > 3) {
		$linkNewAd = 'prompt_message(6, \'\'); return false;'."\n";
		$linkNewAdEigene = 'prompt_message(6, \'\'); return false;'."\n";
		$linkNewAdPDF = 'prompt_message(6, \'\'); return false;'."\n";
		
		$duplicateReload = false;
	} else {
		$linkNewAd = 'location.href = \'/firmen/stelle/0/content.html\';';
		
		$_SESSION['nextJobAdLocked'] = $stellenAnzahl == 3 ? true : false;
	}
} elseif ($_SESSION['s_firmenlevel'] == 2) {
	if (intval($stellenAnzahl) > 20) {
		$linkNewAd = 'prompt_message(7, \'\'); return false;'."\n";
		$linkNewAdEigene = 'prompt_message(7, \'\'); return false;'."\n";
		$linkNewAdPDF = 'prompt_message(7, \'\'); return false;'."\n";
		
		$duplicateReload = false;
	} else {
		$linkNewAd = 'location.href = \'/firmen/stelle/0/content.html\';';
		
		$_SESSION['nextJobAdLocked'] = $stellenAnzahl == 20 ? true : false;
	}
} elseif ($_SESSION['s_firmenlevel'] == 3) {
	$linkNewAd = 'location.href = \'/firmen/stelle/0/content.html\';';
		
	$_SESSION['nextJobAdLocked'] = false;
}

/**
 * Darf neue Stelle angelegt werden, oder nicht? -- End
 */


/**
 * Auswertung der geposteten Aktionen -- Begin
 */

$notes = '';

//wenn von der activate kommend und FREE
if (isset($_SESSION['show_hint_free']) && $_SESSION['show_hint_free'] == true) {
	$notes .= '<p class="hint success">Die ausgew&auml;hlte Stellenanzeige wird innerhalb der n&auml;chsten <strong>24 Stunden</strong> (Gesch&auml;ftszeiten: Montag bis Freitag 9 - 18 Uhr) gepr&uuml;ft. Im Anschluss daran wird sie f&uuml;r die Suche freigeschalten.</p>'."\n";

	unset($_SESSION['show_hint_free']);
} elseif (isset($_SESSION['show_hint_free']) && $_SESSION['show_hint_free'] == false) {
	$notes .= '<p class="hint error">Beim Aktivieren der Stelle ist ein Fehler aufgetreten.</p>'."\n";

	unset($_SESSION['show_hint_free']);
}

//Aktivierung einer Stelle
if (isset($_POST['set_online']) && intval($_POST['set_online']) == 0) {
	$notes .= '	<p class="hint info">Die von Ihnen ausgew&auml;hlte Stelle kann nicht aktiviert werden, weil Sie keine Berechtigung besitzen, um einer weiteren Stelle den Online-Status zuzuordnen.<br /><br />M&ouml;chten Sie diese Stelle aktivieren?<br /><br />'."\n";
	$notes .= '		<a href="/firmen/stelle/paket/" class="button red small first">Ja</a>'."\n";
	$notes .= '		<a href="/firmen/angeboteliste/" class="button red small">Nein</a>'."\n";
	$notes .= '	</p>'."\n";
} elseif (isset($_POST['locked']) && !empty($_POST['locked']) && !isset($_POST['set_offline_hint'])) {
	$notes .= '	<p class="hint info">Im FREE-Account l&auml;sst sich lediglich <strong>eine</strong> Stelle pro Monat aktivieren.<br /><br />Diese Stelle wurde bereits einmal online gestellt.<br />Sie l&auml;sst sich erst nach dem <strong>'.$_POST['locked'].'</strong> wieder aktivieren.<br /><br />M&ouml;chten Sie Ihre Stellen ohne Einschr&auml;nkungen bearbeiten und aktivieren?<br /><br />'."\n";
	$notes .= '		<a href="/firmen/stelle/paket/" class="button red small first">Ja</a>'."\n";
	$notes .= '		<a href="/firmen/angeboteliste/" class="button red small">Nein</a>'."\n";
	$notes .= '	</p>'."\n";
} elseif (isset($_POST['set_online']) && is_int(intval($_POST['set_online']))) {
	$stelle = new Praktika_Stelle(intval($_POST['set_online']));
	
	if ($_SESSION['s_firmenlevel'] == 0) {
		$sql = 'UPDATE prakt2.stellen SET pending_status_free = \'waiting\' WHERE id = '.intval($_POST['set_online']);

		$query = mysql_query($sql, $praktdbmaster);

		if (mysql_affected_rows($praktdbmaster) > 0) {
			$result = true;
		} else {
			$result = false;
		}
		
		$sql = 'INSERT INTO prakt2.stellen_log SET stellenid = '.intval($_POST['set_online']).', bearbeiterid = '.$_SESSION['s_loginid'].', mode = 1, value = 1';
		
		$result = mysql_query($sql, $praktdbmaster);
	} else {
		$result = $stelle->setInactive('false', true);
	}

	if ($result == true) {
		if ($_SESSION['s_firmenlevel'] == 0) {
			$notes .= '<p class="hint success">Die ausgew&auml;hlte Stellenanzeige wird innerhalb der n&auml;chsten <strong>24 Stunden</strong> (Gesch&auml;ftszeiten: Montag bis Freitag 9 - 18 Uhr) gepr&uuml;ft. Im Anschluss daran wird sie f&uuml;r die Suche freigeschalten.</p>'."\n";
		} else {
			$notes .= '<p class="hint success">Die ausgew&auml;hlte Stellenanzeige wurde erfolgreich <strong>aktiviert</strong>.<br /><br /><strong>Ihre Stelle erscheint innerhalb der n&auml;chsten Stunde in den Suchergebnissen</strong>.</p>'."\n";
			
			$expirationDate = $firma->licenseExpirationDate(2);
			
			$sql = '	UPDATE
							'.$database_db.'.stellen
						SET
							datum_verfall = '.($expirationDate == false ? 'DATE_ADD(NOW(), INTERVAL 1 MONTH)' : '\''.$expirationDate.'\'').'
						WHERE
							id = '.intval($_POST['set_online']);
		
			mysql_query($sql, $praktdbmaster);
		}
	} else {
		$notes .= '<p class="hint error">Beim Aktivieren der Stelle ist ein Fehler aufgetreten.</p>'."\n";
	}
} elseif (isset($_POST['set_online'])) {
	$notes .= '<p class="hint success">Beim Aktivieren der Stelle ist ein Fehler aufgetreten.</p>'."\n";
}

//Deaktivierung einer Stelle
if (isset($_POST['set_offline_hint']) && is_int(intval($_POST['set_offline_hint']))) {
	$notes .= '<p class="hint info">Im FREE-Account l&auml;sst sich lediglich <strong>eine</strong> Stelle pro Monat aktivieren.<br /><br />Diese Stelle l&auml;sst sich erst nach dem <strong>'.$_POST['locked'].'</strong> wieder aktivieren.<br /><br />Falls Sie Ihre Stellen uneingeschr&auml;nkt bearbeiten und aktivieren m&ouml;chten, informieren Sie sich &uuml;ber <a href="/firmen/stelle/paket/"><strong>unsere Produkte</strong></a>.<br /><br /><strong>M&ouml;chten Sie diese Stelle wirklich deaktivieren?</strong></p>'."\n";
	$notes .= '<form action="/firmen/angeboteliste/" method="post" class="hint" name="deactivateForm">'."\n";
	$notes .= '	<fieldset>'."\n";
	$notes .= '		<p>'."\n";
	$notes .= '			<a href="#" class="button red small first" onclick="document.deactivateForm.submit(); return false;">Ja</a>'."\n";
	$notes .= '			<a href="'.$_SERVER['PHP_SELF'].'" class="button red small">Nein</a>'."\n";
	$notes .= '			<input type="hidden" name="set_offline" value="'.intval($_POST['set_offline_hint']).'" />'."\n";
	$notes .= '		</p>'."\n";
	$notes .= '	</fieldset>'."\n";
	$notes .= '</form>'."\n";
} elseif (isset($_POST['set_offline']) && is_int(intval($_POST['set_offline']))) {
	$stelle = new Praktika_Stelle(intval($_POST['set_offline']));
	
	if ($_SESSION['s_firmenlevel'] == 0) {
		$sql = 'UPDATE prakt2.stellen SET inactive = \'true\', pending_status_free = \'none\' WHERE id = '.intval($_POST['set_offline']);

		$query = mysql_query($sql, $praktdbmaster);

		if (mysql_affected_rows($praktdbmaster) > 0) {
			$result = true;
		} else {
			$result = false;
		}

		$sql = 'INSERT INTO prakt2.stellen_log SET stellenid = '.intval($_POST['set_offline']).', bearbeiterid = '.$_SESSION['s_loginid'].', mode = 1, value = 0';
		
		$result = mysql_query($sql, $praktdbmaster);
	} else {
		$result = $stelle->setInactive('true', true);
	}
		
	if ($result == true) {
		$notes .= '<p class="hint success">Die ausgew&auml;hlte Stellenanzeige wurde erfolgreich <strong>deaktiviert</strong>.</p>'."\n";
	} else {
		$notes .= '<p class="hint error">Beim Deaktivieren der Stelle ist ein Fehler aufgetreten.</p>'."\n";
	}
} elseif (isset($_POST['set_offline'])) {
	$notes .= '<p class="hint error">Beim Deaktivieren der Stelle ist ein Fehler aufgetreten.</p>'."\n";
}

//Duplizieren einer Stelle
if (isset($_POST['duplicate']) && (intval($_POST['duplicate']) == 0 || $duplicateReload == false)) {
	switch ($_SESSION['s_firmenlevel']) {
		case 0:
			$notes .= '	<p class="hint info">Im FREE-Account besitzen Sie lediglich <strong>einen</strong> Anzeigenstellplatz.<br /><br />Ben&ouml;tigen Sie weitere Anzeigenstellpl&auml;tze?<br /><br />'."\n";
			$notes .= '		<a href="/firmen/stelle/paket/" class="button red small first">Ja</a>'."\n";
			$notes .= '		<a href="/firmen/angeboteliste/" class="button red small">Nein</a>'."\n";
			$notes .= '	</p>'."\n";
			break;
		case 1:
			$notes .= '	<p class="hint info">Im BASIS-Account besitzen Sie lediglich <strong>drei</strong> Anzeigenstellpl&auml;tze.<br /><br />Ben&ouml;tigen Sie weitere Anzeigenstellpl&auml;tze?<br /><br />'."\n";
			$notes .= '		<a href="/firmen/stelle/paket/" class="button red small yes">Ja</a>'."\n";
			$notes .= '		<a href="/firmen/angeboteliste/" class="button red small">Nein</a>'."\n";
			$notes .= '	</p>'."\n";
			break;
		case 2:
			$notes .= '	<p class="hint info">Im KOMFORT-Account besitzen Sie lediglich <strong>zwanzig</strong> Anzeigenstellpl&auml;tze.<br /><br />Ben&ouml;tigen Sie weitere Anzeigenstellpl&auml;tze?<br /><br />'."\n";
			$notes .= '		<a href="/firmen/stelle/paket/" class="button red small yes">Ja</a>'."\n";
			$notes .= '		<a href="/firmen/angeboteliste/" class="button red small">Nein</a>'."\n";
			$notes .= '	</p>'."\n";
			break;
	}
} elseif (isset($_POST['duplicate']) && is_int(intval($_POST['duplicate'])) && $duplicateReload == true) {
	$stelle = new Praktika_Stelle(intval($_POST['duplicate']));
	$result = $stelle->duplicate();
	
	if ($result == true) {
		$notes .= '<p class="hint success">Ihre Stelle wurde erfolgreich dupliziert.</p>'."\n";
	} else {
		$notes .= '<p class="hint error">Beim Duplizieren Ihrer Stelle ist ein Fehler aufgetreten.</p>'."\n";
	}
} elseif (isset($_POST['duplicate'])) {
	$notes .= '<p class="hint error">Beim Duplizieren Ihrer Stelle ist ein Fehler aufgetreten.</p>'."\n";
}

//Loeschen einer Stelle
if (isset($_POST['delete']) && intval($_POST['delete']) == 0) {
	$notes .= '<p class="hint2">Sie haben keine Berechtigung diese Stelle zu l&ouml;schen.<br />Falls Sie eingeloggt sein sollten und es sich um Ihre Stelle handelt, kontaktieren Sie uns bitte.</p>'."\n";
} elseif (isset($_POST['delete']) && is_int(intval($_POST['delete']))) {
	$notes .= '<p class="hint warning"><strong>M&ouml;chten Sie diese Stelle wirklich l&ouml;schen?</strong></p>'."\n";
	$notes .= '<form action="/firmen/angeboteliste/" method="post" name="deleteForm" class="hint">'."\n";
	$notes .= '	<fieldset>'."\n";
	$notes .= '		<p>'."\n";
	$notes .= '			<a href="#" class="button red small first" onclick="document.deleteForm.submit(); return false;">Ja</a>'."\n";
	$notes .= '			<a href="'.$_SERVER['PHP_SELF'].'" class="button red small">Nein</a>'."\n";
	$notes .= '			<input type="hidden" name="delete_ad" value="'.intval($_POST['delete']).'" />'."\n";
	$notes .= '		</p>'."\n";
	$notes .= '	</fieldset>'."\n";
	$notes .= '</form>'."\n";
} elseif (isset($_POST['delete_ad']) && is_int(intval($_POST['delete_ad']))) {
	$stelle = new Praktika_Stelle(intval($_POST['delete_ad']));
	$result = $stelle->delete();
	
	if ($result == true) {
		$notes .= '<p class="hint success">Ihre Stelle wurde erfolgreich gel&ouml;scht.</p>'."\n";
	} else {
		$notes .= '<p class="hint error">Beim L&ouml;schen Ihrer Stelle ist ein Fehler aufgetreten.</p>'."\n";
	}
} elseif (isset($_POST['delete'])) {
	$notes .= '<p class="hint error">Beim L&ouml;schen Ihrer Stelle ist ein Fehler aufgetreten.</p>'."\n";
}

//gesperrte Stelle Aktivierung bzw. Duplizierung
if (
		(isset($_POST['locked_1_x']) && is_int(intval($_POST['locked_1_number'])))
		|| (isset($_POST['locked_2_x']) && is_int(intval($_POST['locked_2_number'])))
		|| (isset($_POST['locked_3_x']) && is_int(intval($_POST['locked_3_number'])))) {
	$number = (isset($_POST['locked_1_number']) && !empty($_POST['locked_1_number'])) ? intval($_POST['locked_1_number']) : ((isset($_POST['locked_2_number']) && !empty($_POST['locked_2_number'])) ? intval($_POST['locked_2_number']) : intval($_POST['locked_3_number']));
	
	switch ($number) {
		case 8:
			$notes .= '	<p class="hint info">Im FREE-Account besitzen Sie lediglich <strong>einen</strong> Anzeigenstellplatz, der bereits besetzt ist.<br />Diese Stelle ist f&uuml;r Sie abgespeichert worden, f&uuml;r den Fall, dass Sie mehr Anzeigenstellpl&auml;tze hinzubuchen.<br /><br />Die ausgew&auml;hlte Stellenanzeige kann solange weder online gestellt noch bearbeitet werden.<br /><br />M&ouml;chten Sie weitere Anzeigenstellpl&auml;tze?<br /><br />'."\n";
			$notes .= '		<a href="/firmen/stelle/paket/" class="button red small yes">Ja</a>'."\n";
			$notes .= '		<a href="/firmen/angeboteliste/" class="button red small">Nein</a>'."\n";
			$notes .= '	</p>'."\n";
			break;
		case 9:
			$notes .= '	<p class="hint info">Im BASIS-Account besitzen Sie lediglich <strong>drei</strong> Anzeigenstellpl&auml;tze, welche bereits besetzt sind.<br />Diese Stelle ist f&uuml;r Sie abgespeichert worden, f&uuml;r den Fall, dass Sie mehr Anzeigenstellpl&auml;tze hinzubuchen.<br /><br />Die ausgew&auml;hlte Stellenanzeige kann solange weder online gestellt noch bearbeitet werden.<br /><br />M&ouml;chten Sie weitere Anzeigenstellpl&auml;tze?<br /><br />'."\n";
			$notes .= '		<a href="/firmen/stelle/paket/" class="button red small yes">Ja</a>'."\n";
			$notes .= '		<a href="/firmen/angeboteliste/" class="button red small">Nein</a>'."\n";
			$notes .= '	</p>'."\n";
			break;
		case 10:
			$notes .= '	<p class="hint info">Im KOMFORT-Account besitzen Sie lediglich <strong>zwanzig</strong> Anzeigenstellpl&auml;tze, welche bereits besetzt sind.<br />Diese Stelle ist f&uuml;r Sie abgespeichert worden, f&uuml;r den Fall, dass Sie mehr Anzeigenstellpl&auml;tze hinzubuchen.<br /><br />Die ausgew&auml;hlte Stellenanzeige kann solange weder online gestellt noch bearbeitet werden.<br /><br />M&ouml;chten Sie weitere Anzeigenstellpl&auml;tze?<br /><br />'."\n";
			$notes .= '		<a href="/firmen/stelle/paket/" class="button red small yes">Ja</a>'."\n";
			$notes .= '		<a href="/firmen/angeboteliste/" class="button red small">Nein</a>'."\n";
			$notes .= '	</p>'."\n";
			break;
		case 13:
			$notes .= '	<p class="hint info">Diese Stelle besitzt ein Template, welches mindestens die Lizenz BASIS voraussetzt.<br /><br />M&ouml;chten Sie <strong>diese</strong> Stelle wieder aktivieren?<br /><br />'."\n";
			$notes .= '		<a href="/firmen/stelle/paket/" class="button red small yes">Ja</a>'."\n";
			$notes .= '		<a href="/firmen/angeboteliste/" class="button red small">Nein</a>'."\n";
			$notes .= '	</p>'."\n";
			break;
		case 14:
			$notes .= '	<p class="hint info">Diese Stelle besitzt ein Template, welches mindestens die Lizenz KOMFORT voraussetzt.<br /><br />M&ouml;chten Sie <strong>diese</strong> Stelle wieder aktivieren?<br /><br />'."\n";
			$notes .= '		<a href="/firmen/stelle/paket/" class="button red small yes">Ja</a>'."\n";
			$notes .= '		<a href="/firmen/angeboteliste/" class="button red small">Nein</a>'."\n";
			$notes .= '	</p>'."\n";
			break;
	}
}

/**
 * Auswertung der geposteten Aktionen -- End
 */

$stats = $firma->getStatistik();
$stats2 = $firma->getCountActiveJobs();
?>
<script type="text/javascript">promptButtons = ['<' + 'a class="button red small" onclick="smallbox.hide(\'upgrade\')"' + '>' + 'Ja' + '<' + '/a' + '>','<' + 'a class="button red small" onclick="smallbox.hide()"' + '>' + 'Nein' + '<' + '/a' + '>']</script>
<script type="text/javascript">
	function prompt_message(message_number, date) {		
		switch (message_number) {
			case 1:
				smallbox.confirm('Diese Funktion steht Ihnen erst ab dem Stellenpaket ' + '<' + 'strong' + '>' + 'BASIS' + '<' + '/strong' + '>' + ' zur Verf&uuml;gung.' + '<' + 'br /' + '>' + '<' + 'br /' + '>' + 'M&ouml;chten Sie diese Funktion freischalten?' + '<' + 'br /' + '>', promptButtons, function(e) { if (e=='upgrade') {location.href = '/firmen/stelle/paket.html';}}); return true;
				break;
			case 2:
				smallbox.confirm('Im FREE-Account l&auml;sst sich lediglich ' + '<' + 'strong' + '>' + 'eine' + '<' + '/strong' + '>' + ' Stelle pro Monat aktivieren.' + '<' + 'br /' + '>' + 'In diesen Zeitraum kann diese Stelle nicht bearbeitet werden.' + '<' + 'br /' + '>' + '<' + 'br /' + '>' + 'Diese Stelle l&auml;sst sich erst nach dem ' + '<' + 'strong' + '>' + date + '<' + '/strong' + '>' + ' wieder bearbeiten.' + '<' + 'br /' + '>' + '<' + 'br /' + '>' + 'M&ouml;chten Sie ohne Einschr&auml;nkungen Ihre Stellen bearbeiten und aktivieren?' + '<' + 'br /' + '>', promptButtons, function(e) { if (e=='upgrade') {location.href = '/firmen/stelle/paket.html';}}); return true;
				break;
			case 3:
				smallbox.confirm('Im FREE-Account l&auml;sst sich lediglich ' + '<' + 'strong' + '>' + 'eine' + '<' + '/strong' + '>' + ' Stelle pro Monat aktivieren.' + '<' + 'br /' + '>' + 'In dieser Zeit kann die aktivierte Stelle nicht bearbeitet werden, auch wenn sie wieder deaktiviert wurde.' + '<' + 'br /' + '>' + '<' + 'br /' + '>' + 'Diese Stelle wurde bereits einmal online gestellt.' + '<' + 'br /' + '>' + 'Sie l&auml;sst sich erst nach dem ' + '<' + 'strong' + '>' + date + '<' + '/strong' + '>' + ' wieder bearbeiten.' + '<' + 'br /' + '>' + '<' + 'br /' + '>' + 'M&ouml;chten Sie ohne Einschr&auml;nkungen Ihre Stellen bearbeiten und aktivieren?' + '<' + 'br /' + '>', promptButtons, function(e) { if (e=='upgrade') {location.href = '/firmen/stelle/paket.html';}}); return true;
				break;
			case 4:
				smallbox.confirm('Im FREE-Account l&auml;sst sich lediglich ' + '<' + 'strong' + '>' + 'eine' + '<' + '/strong' + '>' + ' Stelle pro Monat aktivieren.' + '<' + 'br /' + '>' + '<' + 'br /' + '>' + 'Diese Stelle wurde bereits einmal online gestellt.' + '<' + 'br /' + '>' + 'Sie l&auml;sst sich erst nach dem ' + '<' + 'strong' + '>' + date + '<' + '/strong' + '>' + ' wieder aktivieren.' + '<' + 'br /' + '>' + '<' + 'br /' + '>' + 'M&ouml;chten Sie ohne Einschr&auml;nkungen Ihre Stellen bearbeiten und aktivieren?' + '<' + 'br /' + '>', promptButtons, function(e) { if (e=='upgrade') {location.href = '/firmen/stelle/paket.html';}}); return true;
				break;
			case 5:
				smallbox.confirm('Diese Funktion steht Ihnen erst ab dem Stellenpaket ' + '<' + 'strong' + '>' + 'BASIS' + '<' + '/strong' + '>' + ' zur Verf&uuml;gung.' + '<' + 'br /' + '>' + '<' + 'br /' + '>' + 'M&ouml;chten Sie diese Funktion freischalten?' + '<' + 'br /' + '>', promptButtons, function(e) { if (e=='upgrade') {location.href = '/firmen/stelle/paket.html';}}); return true;
				break;
			case 6:
				smallbox.confirm('Im BASIS-Account besitzen Sie lediglich ' + '<' + 'strong' + '>' + 'drei' + '<' + '/strong' + '>' + ' Anzeigenstellpl&auml;tze.' + '<' + 'br /' + '>' + '<' + 'br /' + '>' + 'Ben&ouml;tigen Sie weitere Anzeigenstellpl&auml;tze?' + '<' + 'br /' + '>', promptButtons, function(e) { if (e=='upgrade') {location.href = '/firmen/stelle/paket.html';}}); return true;
				break;
			case 7:
				smallbox.confirm('Im KOMFORT-Account besitzen Sie lediglich ' + '<' + 'strong' + '>' + 'zwanzig' + '<' + '/strong' + '>' + ' Anzeigenstellpl&auml;tze.' + '<' + 'br /' + '>' + '<' + 'br /' + '>' + 'Ben&ouml;tigen Sie weitere Anzeigenstellpl&auml;tze?' + '<' + 'br /' + '>', promptButtons, function(e) { if (e=='upgrade') {location.href = '/firmen/stelle/paket.html';}}); return true;
				break;
			case 8:
				smallbox.confirm('Im FREE-Account besitzen Sie lediglich ' + '<' + 'strong' + '>' + 'einen' + '<' + '/strong' + '>' + ' Anzeigenstellplatz, der bereits besetzt ist.' + '<' + 'br /' + '>' + 'Diese Stelle ist f&uuml;r Sie abgespeichert worden, f&uuml;r den Fall, dass Sie mehr Anzeigenstellpl&auml;tze hinzubuchen.' + '<' + 'br /' + '>' + '<' + 'br /' + '>' + 'Die ausgew&auml;hlte Stellenanzeige kann solange weder online gestellt noch bearbeitet werden.' + '<' + 'br /' + '>' + '<' + 'br /' + '>' + 'M&ouml;chten Sie weitere Anzeigenstellpl&auml;tze?' + '<' + 'br /' + '>', promptButtons, function(e) { if (e=='upgrade') {location.href = '/firmen/stelle/paket.html';}}); return true;
				break;
			case 9:
				smallbox.confirm('Im BASIS-Account besitzen Sie lediglich ' + '<' + 'strong' + '>' + 'drei' + '<' + '/strong' + '>' + ' Anzeigenstellpl&auml;tze, welche bereits besetzt sind.' + '<' + 'br /' + '>' + 'Diese Stelle ist f&uuml;r Sie abgespeichert worden, f&uuml;r den Fall, dass Sie mehr Anzeigenstellpl&auml;tze hinzubuchen.' + '<' + 'br /' + '>' + '<' + 'br /' + '>' + 'Die ausgew&auml;hlte Stellenanzeige kann solange weder online gestellt noch bearbeitet werden.' + '<' + 'br /' + '>' + '<' + 'br /' + '>' + 'M&ouml;chten Sie weitere Anzeigenstellpl&auml;tze?' + '<' + 'br /' + '>', promptButtons, function(e) { if (e=='upgrade') {location.href = '/firmen/stelle/paket.html';}}); return true;
				break;
			case 10:
				smallbox.confirm('Im KOMFORT-Account besitzen Sie lediglich ' + '<' + 'strong' + '>' + 'zwanzig' + '<' + '/strong' + '>' + ' Anzeigenstellpl&auml;tze, welche bereits besetzt sind.' + '<' + 'br /' + '>' + 'Diese Stelle ist f&uuml;r Sie abgespeichert worden, f&uuml;r den Fall, dass Sie mehr Anzeigenstellpl&auml;tze hinzubuchen.' + '<' + 'br /' + '>' + '<' + 'br /' + '>' + 'Die ausgew&auml;hlte Stellenanzeige kann solange weder online gestellt noch bearbeitet werden.' + '<' + 'br /' + '>' + '<' + 'br /' + '>' + 'M&ouml;chten Sie weitere Anzeigenstellpl&auml;tze?' + '<' + 'br /' + '>', promptButtons, function(e) { if (e=='upgrade') {location.href = '/firmen/stelle/paket.html';}}); return true;
				break;
			case 11:
				smallbox.confirm('Im FREE-Account k&ouml;nnen Sie Stellen von anderen Job-Webseiten oder PDF-Stellenanzeigen ' + '<' + 'strong' + '>' + 'nicht' + '<' + '/strong' + '>' + ' verwenden.' + '<' + 'br /' + '>' + '<' + 'br /' + '>' + 'M&ouml;chten Sie dieses Feature nutzen?' + '<' + 'br /' + '>', promptButtons, function(e) { if (e=='upgrade') {location.href = '/firmen/stelle/paket.html';}}); return true;
				break;
			case 12:
				smallbox.confirm('Im BASIS-Account k&ouml;nnen Sie Stellen von anderen Job-Webseiten oder PDF-Stellenanzeigen ' + '<' + 'strong' + '>' + 'nicht' + '<' + '/strong' + '>' + ' verwenden.' + '<' + 'br /' + '>' + '<' + 'br /' + '>' + 'M&ouml;chten Sie dieses Feature nutzen?' + '<' + 'br /' + '>', promptButtons, function(e) { if (e=='upgrade') {location.href = '/firmen/stelle/paket.html';}}); return true;
				break;
			case 13:
				smallbox.confirm('Diese Stelle besitzt ein Template, welches mindestens die Lizenz BASIS voraussetzt.' + '<' + 'br /' + '>' + '<' + 'br /' + '>' + 'M&ouml;chten Sie ' + '<' + 'strong' + '>' + 'diese' + '<' + '/strong' + '>' + ' Stelle wieder aktivieren?' + '<' + 'br /' + '>', promptButtons, function(e) { if (e=='upgrade') {location.href = '/firmen/stelle/paket.html';}}); return true;
				break;
			case 14:
				smallbox.confirm('Diese Stelle besitzt ein Template, welches mindestens die Lizenz KOMFORT voraussetzt.' + '<' + 'br /' + '>' + '<' + 'br /' + '>' + 'M&ouml;chten Sie ' + '<' + 'strong' + '>' + 'diese' + '<' + '/strong' + '>' + ' Stelle wieder aktivieren?' + '<' + 'br /' + '>', promptButtons, function(e) { if (e=='upgrade') {location.href = '/firmen/stelle/paket.html';}}); return true;
				break;
		}
	}
	
	function hideHelp() {
		if (document.getElementById('helpBox').className == 'hide') {
			document.getElementById('helpBox').className = 'show';
		} else {
			document.getElementById('helpBox').className = 'hide';
		}
	}
</script>

<p class="firstP">Legen Sie neue Stellen an oder bearbeiten Sie bereits vorhandene.</p>

<div id="newAd_stats">
	<table cellpadding="0" cellspacing="0">
		<colgroup>
			<col width="*" />
			<col width="50" />
		</colgroup>
		<tbody>
			<tr>
				<td>Anzahl Ihrer Online-Stellen:</td>
				<td><strong><?php echo $stats2; ?></strong></td>
			</tr>
			<tr>
				<td>Einblendungen Ihrer Stellen in der Suche:</td>
				<td><strong><?php echo $stats['view']; ?></strong></td>
			</tr>
			<tr>
				<td>Bewerber, die Ihre Stellen angesehen haben:</td>
				<td><strong><?php echo $_SESSION['s_firmenlevel'] == 0 ? '<a href="/firmen/angeboteliste/" onclick="prompt_message(1, \'\'); return false;" title="Statistik ansehen">?</a>' :  $stats['read']; ?></strong></td>
			</tr>
			<tr>
				<td>Bewerber, die sich Ihre Stellen gemerkt haben:</td>
				<td><strong><?php echo $stats['save']; ?></strong></td>
			</tr>
		</tbody>
	</table>
	<div>
		<div>
			<a href="#" id="new_ad_eigene" onclick="<?php echo $linkNewAdEigene; ?>" class="button<?php echo $linkNewAdPDFEigene == false ? ' inactive' : ' alternative'; ?>">Eigene Job-Webseite verwenden</a>
			<a href="#" id="new_ad_pdf" onclick="<?php echo $linkNewAdPDF; ?>" class="button<?php echo $linkNewAdPDFEigene == false ? ' inactive' : ' alternative'; ?>">PDF-Stellenanzeige hochladen</a>
		</div>
		<a href="#" id="new_ad" class="button green" onclick="<?php echo $linkNewAd; ?>">Neue<br />Stelle<br />anlegen</a>
	</div>
</div>
	
<?php include('/home/praktika/public_html/cms/fileadmin/php_scripts/boldchat_firmen.inc') ?>

<form action="/firmen/angeboteliste/" method="post">
	<fieldset>
		<label class="filterLabel" for="praktikum"><strong>Zeige nur Stellen:</strong></label>
		<ul id="filter">
			<li><label for="praktikum"><input type="checkbox" id="praktikum" name="praktikum" value="1" onclick="submit();"<?php echo (isset($_POST['praktikum']) && $_POST['praktikum'] == '1') ? ' checked="checked"' : ''; ?> /> Praktikum</label></li>
			<li><label for="bachelor"><input type="checkbox" id="bachelor" name="bachelor" value="1" onclick="submit();"<?php echo (isset($_POST['bachelor']) && $_POST['bachelor'] == '1') ? ' checked="checked"' : ''; ?> /> Bachelor / Master</label></li>
			<li><label for="berufseinstieg"><input type="checkbox" id="berufseinstieg" name="berufseinstieg" onclick="submit();"<?php echo (isset($_POST['berufseinstieg']) && $_POST['berufseinstieg'] == '1') ? ' checked="checked"' : ''; ?> value="1" /> Berufseinstieg</label></li>
			<li><label for="ausbildung"><input type="checkbox" id="ausbildung" name="ausbildung" value="1" onclick="submit();"<?php echo (isset($_POST['ausbildung']) && $_POST['ausbildung'] == '1') ? ' checked="checked"' : ''; ?> /> Ausbildung</label></li>
			<li><label for="nebenjob"><input type="checkbox" id="nebenjob" name="nebenjob" value="1" onclick="submit();"<?php echo (isset($_POST['nebenjob']) && $_POST['nebenjob'] == '1') ? ' checked="checked"' : ''; ?> /> Nebenjob</label></li>
			<li><label for="trainee"><input type="checkbox" id="trainee" name="trainee" value="1" onclick="submit();"<?php echo (isset($_POST['trainee']) && $_POST['trainee'] == '1') ? ' checked="checked"' : ''; ?> /> Trainee</label></li>
			<li><label for="projekt"><input type="checkbox" id="projekt" name="projekt" value="1" onclick="submit();"<?php echo (isset($_POST['projekt']) && $_POST['projekt'] == '1') ? ' checked="checked"' : ''; ?> /> Projekt</label></li>
			<li class="leftSpace"><strong>Status:</strong> <label for="online"><input type="checkbox" id="online" name="online" value="1" onclick="submit();"<?php echo (isset($_POST['online']) && $_POST['online'] == '1') ? ' checked="checked"' : ((!isset($_POST['online']) && !isset($_POST['offline'])) ? ' checked="checked"' : ''); ?> /> Online</label></li>
			<li class="last"><label for="offline"><input type="checkbox" id="offline" name="offline" value="1" onclick="submit();"<?php echo (isset($_POST['offline']) && $_POST['offline'] == '1') ? ' checked="checked"' : ((!isset($_POST['online']) && !isset($_POST['offline'])) ? ' checked="checked"' : ''); ?> /> Offline</label></li>
		</ul>
	</fieldset>
</form>

<?php echo $notes; ?>

<table id="jobAdList" cellpadding="0" cellspacing="0">
	<colgroup>
		<col width="100" />
		<col width="5" />
		<col width="*" />
		<col width="61" />
		<col width="85" />
		<col width="95" />
		<col width="134" />
	</colgroup>		
	<thead>
		<tr>
			<th><span class="left_corner">&nbsp;</span><span class="middle_text">Status</span><span class="right_corner">&nbsp;</span></th>
			<th>&nbsp;</th>
			<th><span class="left_corner">&nbsp;</span><span class="middle_text">Stellen bearbeiten</span><span class="right_corner">&nbsp;</span></th>
			<th><span class="left_corner">&nbsp;</span><span class="middle_text">Views</span><span class="right_corner">&nbsp;</span></th>
			<th><span class="left_corner">&nbsp;</span><span class="middle_text">Datum</span><span class="right_corner">&nbsp;</span></th>
			<th><span class="left_corner">&nbsp;</span><span class="middle_text">verf&auml;llt am</span><span class="right_corner">&nbsp;</span></th>
			<th><span class="left_corner">&nbsp;</span><span class="middle_text">Optionen</span><span class="right_corner">&nbsp;</span></th>
		</tr>
	</thead>
<?php
/**
 * Vorbereitung der Query, fuer die Auflistung der Stellen -- Begin
 */

$tbody = '
	<tbody>
	';

$selectFields = array(
						'id',
						'spalte',
						'inactive',
						'pending_status_free',
						'templateid',
						'taetigkeit',
						'ansprechpartnerid',
						'link_anzeige_abfr',
						'templateid',
						'DATE_FORMAT(datum_verfall, \'%d.%m.%Y\') AS datum_verfall',
						'DATE_FORMAT(modify, \'%d.%m.%Y\') AS modify_date'
				);
				
$whereClause = array(
						'firmenid = '.intval($_SESSION['s_firmenid']),
						'deleted = \'false\''
				);

$filterArray = array(
						'praktikum' => 'spalte = 1',
						'bachelor' => 'spalte = 2',
						'berufseinstieg' => 'spalte = 5',
						'ausbildung' => 'spalte = 4',
						'nebenjob' => 'spalte = 3',
						'projekt' => 'spalte = 6',
						'trainee' => 'spalte = 7',
				);

if (isset($_POST)) {
	$filterCount = 0;
	
	foreach ($filterArray as $key => $value) {
		if (isset($_POST[$key]) && $_POST[$key] == '1') {
			$filterWhereClause[] = $value;

			$filterCount++;
		}
	}
}

if (isset($_POST['online']) && $_POST['online'] == '1' && !isset($_POST['offline'])) {
	$whereClause[] = 'inactive = \'false\'';
} elseif (isset($_POST['offline']) && $_POST['offline'] == '1' && !isset($_POST['online'])) {
	$whereClause[] = 'inactive = \'true\'';
}

$orderField = 'id';

if($stellenAnzahl == 4 && $_SESSION['s_firmenlevel'] == 1) {
	$orderField = "inactive='false' DESC,id";
}
$stellenarray = $stellen->getJobAdList($selectFields, $whereClause, $filterWhereClause, $orderField);
$stellenNumbers = $stellen->getJobAdListPosition($orderField);

/**
 * Vorbereitung der Query, fuer die Auflistung der Stellen -- End
 */

$_SESSION['jobAds_aktivierbar'] = array();

$views = $firma->getViews();

foreach ($stellenarray as $key) {
	$stelle = new Praktika_Stelle($key['id']);
	$freeHint = false;
	$locked = false;
	$tooManyAds = false;
	$templateFitToLicense = true;
    
	$position = $stelle->getJobPositionInArray($stellenNumbers, $key['id']);
	
	/**
	 * Berechtigungen fuer die einzelnen Pakete definieren -- Begin
	 */
	
	if ($_SESSION['s_firmenlevel'] == 0) {
		$activeAd = $stelle->checkActive(intval($key['id']));
		
		if ($activeAd == false) {
			$lockedDate = $stelle->checkLockedJobAd(intval($key['id']));

			if ($lockedDate !== false) {
				$link = 'prompt_message(3, \''.$lockedDate.'\'); return false;';
				$locked = true;
			} else {
				$link = '/firmen/stelle/'.$key['id'].'/content.html';
			}
		} else {
			$lockedDate = $stelle->checkLockedJobAd(intval($key['id']));

			if ($lockedDate !== false) {
				$link = 'prompt_message(2, \''.$lockedDate.'\'); return false;';
				$freeHint = true;
			} else {
				$link = '/firmen/stelle/'.$key['id'].'/content.html';
			}
		}
		
		if ($stellenAnzahl >= 1) {
	        $duplicate = false;
	        $duplicateMessage = ' onclick="prompt_message(5, \'\'); return false;"';
		} else {
			$duplicate = true;
		}
		
		if ($position > 1) {
			$tooManyAds = true;
			$promptNumber = 8;
		}
		
		if (intval($key['templateid']) != 109) {
			$templateFitToLicense = false;
			$promptNumber = 13;
		}
	}

	if ($_SESSION['s_firmenlevel'] == 1) {
		if ($stellenAnzahl >= 3) {
	        $duplicate = false;
	        $duplicateMessage = ' onclick="prompt_message(6, \'\'); return false;"';
		} else {
			$duplicate = true;
		}

		if ($position > 3) {
			$tooManyAds = true;
			$promptNumber = 9;
		}

		$link = '/firmen/stelle/'.$key['id'].'/content.html';
		
		if (intval($key['templateid']) != 109 && in_array(intval($key['templateid']), range(2, 101)) && $tooManyAds != true) {
			$templateFitToLicense = false;
			$promptNumber = 14;
		}
	}

	if ($_SESSION['s_firmenlevel'] == 2) {
		if ($stellenAnzahl > 20) {
	        $duplicate = false;
	        $duplicateMessage = ' onclick="prompt_message(7, \'\'); return false;"';
		} else {
			$duplicate = true;
		}

		if ($position > 20) {
			$tooManyAds = true;
			$promptNumber = 10;
		}

		$link = '/firmen/stelle/'.$key['id'].'/content.html';
	}

	if ($_SESSION['s_firmenlevel'] == 3) {
		$duplicate = true;

		$link = '/firmen/stelle/'.$key['id'].'/content.html';
	}
	
	$delete = true;
	
	/**
	 * Berechtigungen fuer die einzelnen Pakete definieren -- End
	 */
    
	if (isset($tooManyAds) && $tooManyAds == true) {
		$tbody .= '
		<tr class="locked">
			<td class="first">
				<form action="/firmen/angeboteliste/" method="post">
					<fieldset>
						<input type="image" src="/gifs/alpha.gif" name="locked_1" class="offline" value="'.$promptNumber.'" onclick="prompt_message('.$promptNumber.', \'\'); return false;" title="Stelle aktivieren" />
						<input type="hidden" src="/gifs/alpha.gif" name="locked_1_number" value="'.$promptNumber.'" />
					</fieldset>
				</form>
			</td>
			<td>'.$position.'.</td>
			<td><a href="#" onclick="prompt_message('.$promptNumber.', \'\'); return false;" title="Stelle bearbeiten">'.(empty($key['taetigkeit']) ? 'Kein Titel &raquo; Stelle bearbeiten' : stripcslashes($key['taetigkeit'])).'</a></td>
			<td>'.($_SESSION['s_firmenlevel'] == 0 ? '<a href="/firmen/angeboteliste/" onclick="prompt_message('.$promptNumber.', \'\'); return false;" title="Statistik ansehen">?' : '<a href="">'.$views[$key['id']]).'</a></td>
			<td class="date">'.$key['modify_date'].'</td>
			<td class="date">'.($key['inactive'] == 'true' ? '' : $key['datum_verfall']).'</td>
			<td>
				<a href="#" onclick="prompt_message('.$promptNumber.', \'\'); return false;" class="edit" title="Stelle bearbeiten"><img src="/gifs/alpha.gif" alt="Stelle bearbeiten" class="edit" /></a>
				<a href="/PREVIEW/PREVIEW-'.intval($key['id']).'.html" target="_blank" class="view" title="Stelle ansehen"><img src="/gifs/alpha.gif" alt="Stelle ansehen" class="view" /></a>
				<form action="/firmen/angeboteliste/" method="post">
					<fieldset>
						<input type="image" src="/gifs/alpha.gif" name="locked_2" class="duplicate" value="'.$promptNumber.'" onclick="prompt_message('.$promptNumber.', \'\'); return false;" title="Stelle duplizieren" />
						<input type="hidden" src="/gifs/alpha.gif" name="locked_2_number" value="'.$promptNumber.'" />
					</fieldset>
				</form>
				<a href="#" onclick="prompt_message('.$promptNumber.', \'\'); return false;" class="stats" title="Statistik ansehen"><img src="/gifs/alpha.gif" alt="Statistiken zur Stelle ansehen" class="stats" /></a>
				<form action="/firmen/angeboteliste/" method="post">
					<fieldset>
						<input type="image" src="/gifs/alpha.gif" name="delete_send" value="" class="delete" title="Stelle l&ouml;schen" />
						<input type="hidden" name="delete" value="'.($delete == true ? intval($key['id']) : '0').'" />
					</fieldset>
				</form>
			</td>
		</tr>
		';
	} elseif ($templateFitToLicense == false) {
		$tbody .= '
		<tr class="'.($key['inactive'] == 'true' ? 'inactive' : 'active').'">
			<td class="first">
				<form action="/firmen/angeboteliste/" method="post">
					<fieldset>
						<input type="image" src="/gifs/alpha.gif" name="locked_3" class="offline" value="'.$promptNumber.'" onclick="prompt_message('.$promptNumber.', \'\'); return false;" title="Stelle aktivieren" />
						<input type="hidden" src="/gifs/alpha.gif" name="locked_3_number" value="'.$promptNumber.'" />
					</fieldset>
				</form>
			</td>
			<td>'.$position.'.</td>
			<td'.($key['inactive'] == 'true' ? ' class="inactive"' : '').'><a href="'.(($locked == true || $freeHint == true) ? '#" onclick="'.$link : $link).'" title="Stelle bearbeiten">'.(empty($key['taetigkeit']) ? 'Kein Titel &raquo; Stelle bearbeiten' : stripcslashes($key['taetigkeit'])).'</a></td>
			<td>'.($_SESSION['s_firmenlevel'] == 0 ? '<a href="/firmen/angeboteliste/" onclick="prompt_message(1, \'\'); return false;" title="Statistik ansehen">?' : '<a href="/firmen/stelle/'.intval($key['id']).'/statistik-detail.html">'.$views[$key['id']]).'</a></td>
			<td class="date">'.$key['modify_date'].'</td>
			<td class="date">'.(($key['inactive'] == 'true' || $_SESSION['s_firmenlevel'] > 0) ? '' : $key['datum_verfall']).'</td>
			<td>
				<a href="'.(($locked == true || $freeHint == true) ? '#" onclick="'.$link : $link).'" class="edit" title="Stelle bearbeiten"><img src="/gifs/alpha.gif" alt="Stelle ansehen" class="edit" /></a>
				<a href="/PREVIEW/PREVIEW-'.intval($key['id']).'.html?h='.md5($_SESSION['s_firmenid']."#".$key['id']).'" target="_blank" class="view" title="Vorschau ansehen"><img src="/gifs/alpha.gif" class="view" alt="Vorschau ansehen" /></a>
				<form action="/firmen/angeboteliste/" method="post">
					<fieldset>
						<input type="image" src="/gifs/alpha.gif" name="duplicate_send" value="" class="duplicate"'.((isset($duplicate) && $duplicate == false) ? $duplicateMessage : '').' title="Stelle duplizieren" />
						<input type="hidden" name="duplicate" value="'.($duplicate == true ? intval($key['id']) : '0').'" />
					</fieldset>
				</form>
				'.($_SESSION['s_firmenlevel'] == 0 ? '<a href="/firmen/angeboteliste/" onclick="prompt_message(1, \'\'); return false;" title="Statistik ansehen" class="stats">' : '<a href="/firmen/stelle/'.intval($key['id']).'/statistik-detail.html" class="stats">').'<img src="/gifs/alpha.gif" alt="Statistiken zur Stelle ansehen" class="stats" title="Statistiken ansehen" /></a>
				<form action="/firmen/angeboteliste/" method="post">
					<fieldset>
						<input type="image" src="/gifs/alpha.gif" name="delete_send" value="" class="delete" title="Stelle l&ouml;schen" />
						<input type="hidden" name="delete" value="'.($delete == true ? intval($key['id']) : '0').'" />
					</fieldset>
				</form>
			</td>
		</tr>
		<tr>
			<td class="spacer" colspan="7"></td>
		</tr>
		';
    } else {
    	//speichern in Session, damit in der activate aktivierbar
    	$_SESSION['jobAds_aktivierbar'][] = intval($key['id']);
    	
		$tbody .= '
		<tr class="'.($key['inactive'] == 'true' ? 'inactive' : 'active').'">
			<td class="first">
				<form action="/firmen/angeboteliste/" method="post">
					<fieldset>
						<input type="image" src="/gifs/alpha.gif" name="activate_send" value="" class="'.($key['inactive'] == 'true' ? (($_SESSION['s_firmenlevel'] == 0 && $key['pending_status_free'] == 'waiting') ? 'online' : 'offline') : 'online').'"'.((isset($locked) && $locked == true) ? ' onclick="prompt_message(4, \''.$lockedDate.'\'); return false;"' : '').' title="Stelle '.($key['inactive'] == 'true' ? 'aktivieren' : 'deaktivieren').'" />
						<input type="hidden" name="set_'.($key['inactive'] == 'true' ? (($_SESSION['s_firmenlevel'] == 0 && $key['pending_status_free'] == 'waiting') ? 'offline' : 'online') : 'offline').((isset($freeHint) && $freeHint == true) ? '_hint' : '').'" value="'.intval($key['id']).'" />
						'.((isset($lockedDate) && $lockedDate !== false) ? '<input type="hidden" name="locked" value="'.$lockedDate.'" />' : '').'
					</fieldset>
				</form>
			</td>
			<td>'.$position.'.</td>
			<td'.($key['inactive'] == 'true' ? ' class="inactive"' : '').'><a href="'.(($locked == true || $freeHint == true) ? '#" onclick="'.$link : $link).'" title="Stelle bearbeiten">'.(empty($key['taetigkeit']) ? 'Kein Titel &raquo; Stelle bearbeiten' : stripcslashes($key['taetigkeit'])).'</a></td>
			<td>'.($_SESSION['s_firmenlevel'] == 0 ? '<a href="/firmen/angeboteliste/" onclick="prompt_message(1, \'\'); return false;" title="Statistik ansehen">?' : '<a href="/firmen/stelle/'.intval($key['id']).'/statistik-detail.html">'.$views[$key['id']]).'</a></td>
			<td class="date">'.$key['modify_date'].'</td>
			<td class="date">'.(($key['inactive'] == 'true' || $_SESSION['s_firmenlevel'] > 0) ? '' : $key['datum_verfall']).'</td>
			<td>
				<a href="'.(($locked == true || $freeHint == true) ? '#" onclick="'.$link : $link).'" class="edit" title="Stelle bearbeiten"><img src="/gifs/alpha.gif" alt="Stelle ansehen" class="edit" /></a>
				<a href="/PREVIEW/PREVIEW-'.intval($key['id']).'.html?h='.md5($_SESSION['s_firmenid']."#".$key['id']).'" target="_blank" class="view" title="Vorschau ansehen"><img src="/gifs/alpha.gif" class="view" alt="Vorschau ansehen" /></a>
				<form action="/firmen/angeboteliste/" method="post">
					<fieldset>
						<input type="image" src="/gifs/alpha.gif" name="duplicate_send" value="" class="duplicate"'.((isset($duplicate) && $duplicate == false) ? $duplicateMessage : '').' title="Stelle duplizieren" />
						<input type="hidden" name="duplicate" value="'.($duplicate == true ? intval($key['id']) : '0').'" />
					</fieldset>
				</form>
				'.($_SESSION['s_firmenlevel'] == 0 ? '<a href="/firmen/angeboteliste/" onclick="prompt_message(1, \'\'); return false;" title="Statistik ansehen" class="stats">' : '<a href="/firmen/stelle/'.intval($key['id']).'/statistik-detail.html" class="stats">').'<img src="/gifs/alpha.gif" alt="Statistiken zur Stelle ansehen" class="stats" title="Statistiken ansehen" /></a>
				<form action="/firmen/angeboteliste/" method="post">
					<fieldset>
						<input type="image" src="/gifs/alpha.gif" name="delete_send" value="" class="delete" title="Stelle l&ouml;schen" />
						<input type="hidden" name="delete" value="'.($delete == true ? intval($key['id']) : '0').'" />
					</fieldset>
				</form>
			</td>
		</tr>
		<tr>
			<td class="spacer" colspan="7"></td>
		</tr>
		';
    }
}

$stellplatzAnzahl = intval($stellen->getStellenAnzahl());

if ($stellplatzAnzahl == 0) {
	$tbody .= '
		<tr class="inactive">
			<td colspan="7">Sie haben noch keine Stellen angelegt.</td>
		</tr>
	';
}

if ($position == 0 && $stellplatzAnzahl != 0) {
	$tbody .= '
		<tr class="inactive">
			<td colspan="7">Es gibt derzeit keine Stellen in diese'.($filterCount == 1 ? 'r' : 'n').' Kategorie'.($filterCount == 1 ? '' : 'n').'.</td>
		</tr>
	';
}

switch ($_SESSION['s_firmenlevel']) {
	case 0:
		if ($stellplatzAnzahl > 1) {
			$savedAds = $stellplatzAnzahl - 1;
			$stellplaetze = 1;
		} else {
			$savedAds = 0;
			$stellplaetze = $stellplatzAnzahl;
		}
		break;
	case 1:
		if ($stellplatzAnzahl > 3) {
			$savedAds = $stellplatzAnzahl - 3;
			$stellplaetze = 3;
		} else {
			$savedAds = 0;
			$stellplaetze = $stellplatzAnzahl;
		}
		break;
	case 2:
		if ($stellplatzAnzahl > 20) {
			$savedAds = $stellplatzAnzahl - 20;
			$stellplaetze = 20;
		} else {
			$savedAds = 0;
			$stellplaetze = $stellplatzAnzahl;
		}
		break;
	case 3:
		$savedAds = 0;
		$stellplaetze = $stellplatzAnzahl;
		break;
}

$tbody .= '
	</tbody>
	';

$tfoot = '
	<tfoot>
		<tr>
			<td colspan="4" class="help"><a href="#" onclick="hideHelp(); return false;">Hilfe zur Stellenliste</a></td>
			<td colspan="3">Anzahl genutzter Stellpl&auml;tze: <strong>'.$stellplaetze.'</strong> von '.($_SESSION['s_firmenlevel'] == 0 ? '1' : ($_SESSION['s_firmenlevel'] == 1 ? '3' : ($_SESSION['s_firmenlevel'] == 2 ? '20' : ($_SESSION['s_firmenlevel'] == 3 ? 'unbegrenzt' : '')))).'</td>
		</tr>
	</tfoot>
	';

echo $tfoot;
echo $tbody;
?>
</table>

<div id="helpBox" class="hide">
	<h2>Hinweise zu Ihrer Stellentabelle</h2>
	<img src="/styles/images/companies/hilfe.png" alt="Hilfe zur Stellen&uuml;bersicht" />
</div>

<?php
$_SESSION['sidebar'] = '';
$_SESSION['servicecenter'] = '';
bodyoff();
?>