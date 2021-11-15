<?php
require("/home/praktika/etc/config.inc.php");

if (!isset($_SESSION['s_loggedin']) || $_SESSION['s_loggedin'] == false) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/login');
	exit();
}

if (isset($_POST['deny'])) {
	header('Location: /firmen/angeboteliste_agentur/');
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
unset($_SESSION['unternehmens_id']);

$agentur = new Praktika_Firmen_Agentur($_SESSION['s_firmenid']);

pageheader(array('page_title' => 'Stellenangebote verwalten', 'grid_system' => '6-0'));

Praktika_Static::__(array('job_ad_list', 'job_ad_list_agentur'));

$stellenAnzahl = $agentur->getAnzahlStellen();
$views = $agentur->getViews();

$duplicateReload = true;

if ($stellenAnzahl >= 20) {
	$duplicateReload = false;
}

?>

<script type="text/javascript" src="/scripts/agentur.js"></script>

<?php

/**
 * Auswertung der geposteten Aktionen -- Begin
 */

$notes = '';

//Löschen eines Unternehmens
if (isset($_GET['loeschen']) && intval($_GET['loeschen']) === 1 && is_int(intval($_GET['unternehmens_id']))) {
	echo '<p class="hint warning"><strong>Möchten Sie dieses Unternehmen wirklich löschen? Dabei werden auch alle Stellen des Unternehmens unwiderruflich entfernt.</strong></p>' . "\n";
	echo '<form action="/firmen/angeboteliste_agentur/" method="post" name="deleteUnternehmenForm" class="hint">' . "\n";
	echo '	<fieldset>' . "\n";
	echo '		<p>' . "\n";
	echo '			<a href="#" class="button red small first" onclick="document.deleteUnternehmenForm.submit(); return false;">Ja</a>' . "\n";
	echo '			<a href="/firmen/angeboteliste_agentur/" class="button red small">Nein</a>' . "\n";
	echo '			<input type="hidden" name="unternehmen-loeschen-confirm" value="1" />' . "\n";
	echo '			<input type="hidden" name="unternehmens_id" value="' . intval($_GET['unternehmens_id']) . '" />' . "\n";
	echo '		</p>' . "\n";
	echo '	</fieldset>' . "\n";
	echo '</form>' . "\n";
}
if (isset($_POST['unternehmen-loeschen-confirm']) && intval($_POST['unternehmen-loeschen-confirm']) === 1 && is_int(intval($_POST['unternehmens_id']))) {
	$result = $agentur->unternehmenLoeschen(intval($_POST['unternehmens_id']));

	if ($result !== false) {
		$notes .= '	<script type="text/javascript">' . "\n";
		$notes .= '      flashMessenger(\'#button-add-company\', \'Das ausgewählte Unternehmen wurde erfolgreich gelöscht.\', \'success\');' . "\n";
		$notes .= '	</script>' . "\n";
	}
}

//Aktivierung einer Stelle
if (isset($_POST['set_online']) && is_int(intval($_POST['set_online']))) {
	$result = $agentur->stelleAktivieren(intval($_POST['set_online']));

	if ($result == true) {
		$notes .= '	<script type="text/javascript">' . "\n";
		$notes .= '      flashMessenger(\'#button-add-company\', \'Die ausgewählte Stellenanzeige wurde erfolgreich aktiviert.<br /><br />Ihre Stelle erscheint innerhalb der nächsten Stunde in den Suchergebnissen.\', \'success\');' . "\n";
		$notes .= '	</script>' . "\n";
	} else {
		$notes .= '	<script type="text/javascript">' . "\n";
		$notes .= '      flashMessenger(\'#button-add-company\', \'Beim Aktivieren der Stelle ist ein Fehler aufgetreten.\', \'error\');' . "\n";
		$notes .= '	</script>' . "\n";
	}
} elseif (isset($_POST['set_online'])) {
	$notes .= '	<script type="text/javascript">' . "\n";
	$notes .= '      flashMessenger(\'#button-add-company\', \'Beim Aktivieren der Stelle ist ein Fehler aufgetreten.\', \'error\');' . "\n";
	$notes .= '	</script>' . "\n";
}

//Deaktivierung einer Stelle
if (isset($_POST['set_offline']) && is_int(intval($_POST['set_offline']))) {
	$result = $agentur->stelleDeaktivieren(intval($_POST['set_offline']));

	if ($result == true) {
		$notes .= '	<script type="text/javascript">' . "\n";
		$notes .= '      flashMessenger(\'#button-add-company\', \'Die ausgewählte Stellenanzeige wurde erfolgreich deaktiviert.\', \'success\');' . "\n";
		$notes .= '	</script>' . "\n";
	} else {
		$notes .= '	<script type="text/javascript">' . "\n";
		$notes .= '      flashMessenger(\'#button-add-company\', \'Beim Deaktivieren der Stelle ist ein Fehler aufgetreten.\', \'error\');' . "\n";
		$notes .= '	</script>' . "\n";
	}
} elseif (isset($_POST['set_offline'])) {
	$notes .= '	<script type="text/javascript">' . "\n";
	$notes .= '      flashMessenger(\'#button-add-company\', \'Beim Deaktivieren der Stelle ist ein Fehler aufgetreten.\', \'error\');' . "\n";
	$notes .= '	</script>' . "\n";
}

//Duplizieren einer Stelle
if (isset($_POST['duplicate']) && (intval($_POST['duplicate']) == 0 || $duplicateReload == false)) {
	$notes .= '	<script type="text/javascript">' . "\n";
	$notes .= '      flashMessenger(\'#button-add-company\', \'Sie besitzen lediglich zwanzig Anzeigenstellplätze. Bitte löschen Sie zuerst eine Stelle, bevor Sie fortfahren.\', \'info\');' . "\n";
	$notes .= '	</script>' . "\n";
} elseif (isset($_POST['duplicate']) && is_int(intval($_POST['duplicate'])) && $duplicateReload == true) {
	$stelle = $agentur->duplicateStelle(intval($_POST['duplicate']), intval($_POST['unternehmens_id']));

	if ($stelle == true) {
		$notes .= '	<script type="text/javascript">' . "\n";
		$notes .= '      flashMessenger(\'#button-add-company\', \'Ihre Stelle wurde erfolgreich dupliziert.\', \'success\');' . "\n";
		$notes .= '	</script>' . "\n";
	} else {
		$notes .= '	<script type="text/javascript">' . "\n";
		$notes .= '      flashMessenger(\'#button-add-company\', \'Beim Duplizieren Ihrer Stelle ist ein Fehler aufgetreten.\', \'error\');' . "\n";
		$notes .= '	</script>' . "\n";
	}
} elseif (isset($_POST['duplicate'])) {
	$notes .= '	<script type="text/javascript">' . "\n";
	$notes .= '      flashMessenger(\'#button-add-company\', \'Beim Duplizieren Ihrer Stelle ist ein Fehler aufgetreten.\', \'error\');' . "\n";
	$notes .= '	</script>' . "\n";
}

//Loeschen einer Stelle
if (isset($_POST['delete']) && is_int(intval($_POST['delete']))) {
	echo '<p class="hint warning"><strong>Möchten Sie diese Stelle wirklich löschen?</strong></p>' . "\n";
	echo '<form action="/firmen/angeboteliste_agentur/" method="post" name="deleteForm" class="hint">' . "\n";
	echo '	<fieldset>' . "\n";
	echo '		<p>' . "\n";
	echo '			<a href="#" class="button red small first" onclick="document.deleteForm.submit(); return false;">Ja</a>' . "\n";
	echo '			<a href="/firmen/angeboteliste_agentur/" class="button red small">Nein</a>' . "\n";
	echo '			<input type="hidden" name="delete_ad" value="' . intval($_POST['delete']) . '" />' . "\n";
	echo '			<input type="hidden" name="unternehmens_id" value="' . intval($_POST['unternehmens_id']) . '" />' . "\n";
	echo '		</p>' . "\n";
	echo '	</fieldset>' . "\n";
	echo '</form>' . "\n";
} elseif (isset($_POST['delete_ad']) && is_int(intval($_POST['delete_ad']))) {
	$loeschen = $agentur->stelleLoeschen(intval($_POST['delete_ad']), intval($_POST['unternehmens_id']));

	if ($loeschen == true) {
		$notes .= '	<script type="text/javascript">' . "\n";
		$notes .= '      flashMessenger(\'#button-add-company\', \'Ihre Stelle wurde erfolgreich gelöscht.\', \'success\');' . "\n";
		$notes .= '	</script>' . "\n";
	} else {
		$notes .= '	<script type="text/javascript">' . "\n";
		$notes .= '      flashMessenger(\'#button-add-company\', \'Beim Löschen der Stelle ist ein Fehler aufgetreten.\', \'error\');' . "\n";
		$notes .= '	</script>' . "\n";
	}
} elseif (isset($_POST['delete'])) {
	$notes .= '	<script type="text/javascript">' . "\n";
	$notes .= '      flashMessenger(\'#button-add-company\', \'Beim Löschen der Stelle ist ein Fehler aufgetreten.\', \'error\');' . "\n";
	$notes .= '	</script>' . "\n";
}
/**
 * Auswertung der geposteten Aktionen -- End
 */

//$stats = $firma->getStatistik();
//$stats2 = $firma->getCountActiveJobs();
$alleUnternehmen = $agentur->findeUnternehmen();
?>

<p id="button-add-company"><a href="#" class="button green" onclick="return false;">Unternehmen hinzufügen</a></p>
<div id="add-company">
	<form action="#" method="post">
		<fieldset>
			<label for="company-name">Name des Unternehmens:</label>
			<input type="text" id="company-name" name="company" value="" />
		</fieldset>
		<fieldset class="control_panel">
			<p>
				<input type="submit" id="add-company-submit" name="add-company" class="button small" value="Unternehmen hinzufügen" />
			</p>
		</fieldset>
	</form>
</div>

<?= $notes; ?>

<?php if ($alleUnternehmen !== false) : ?>
<?php	foreach ($alleUnternehmen as $unternehmen) : ?>
<?php		$unternehmensDaten = $agentur->findeEinzelnesUnternehmen($unternehmen['id']); ?>
<?php		$profilAusgefuellt = (empty($unternehmensDaten['strasse']) || empty($unternehmensDaten['plz']) || empty($unternehmensDaten['ort']) || !filter_var($unternehmensDaten['email'], FILTER_VALIDATE_EMAIL)) ? false : true; ?>
<?php
			if ($profilAusgefuellt === true) {
				if (intval($stellenAnzahl) >= 200) {
					$linkNewAd = 'flashMessenger(this, \'Sie besitzen lediglich zwanzig Anzeigenstellplätze. Bitte löschen Sie zuerst eine Stelle, bevor Sie fortfahren.\', \'info\'); return false;';
					$linkNewAdPdf = 'flashMessenger(this, \'Sie besitzen lediglich zwanzig Anzeigenstellplätze. Bitte löschen Sie zuerst eine Stelle, bevor Sie fortfahren.\', \'info\'); return false;';
				} else {
					$linkNewAd = 'location.href = \'/firmen/agentur/stelle/0/' . $unternehmen['id'] . '/content.html\';';
					$linkNewAdPdf = 'location.href = \'/firmen/agentur/stelle/0/' . $unternehmen['id'] . '/content_pdf.html\';';
				}
			} else {
				$linkNewAd = 'flashMessenger(this, \'Bevor Sie eine Stelle für dieses Unternehmen anlegen können, müssen die Unternehmensdaten bearbeitet werden.\', \'info\'); return false;';
				$linkNewAdPdf = 'flashMessenger(this, \'Bevor Sie eine Stelle für dieses Unternehmen anlegen können, müssen die Unternehmensdaten bearbeitet werden.\', \'info\'); return false;';
			}
?>

<script type="text/javascript" src="/scripts/jquery/jquery.form.js"></script>

<img src="<?= $agentur->hasLogo($unternehmen['id']) ? $agentur->getLogo($unternehmen['id'], "header") : '/styles/images/logo_upload.png'; ?>" alt="<?= $unternehmen['name']; ?>" id="logo-unternehmen-<?= $unternehmen['id']; ?>" class="logo-unternehmen" lang="<?= $unternehmen['id']; ?>" title="Logo hochladen" />
<form action="/home/firmen/agentur/logo-upload.php" method="post" enctype="multipart/form-data" id="form-<?= $unternehmen['id']; ?>" class="form-logo">
	<input type="hidden" name="MAX_FILE_SIZE" value="140000" />
	<input type="hidden" name="id" value="<?= $unternehmen['id']; ?>" />
	<input type="file" id="logo-<?= $unternehmen['id']; ?>" class="logo-company" name="logo-<?= $unternehmen['id']; ?>" value="" />
</form>
<h3 class="agentur-company"><?= stripslashes($unternehmen['name']); ?></h3>
<p class="new-position">
	<a href="#" onclick="<?= $linkNewAd; ?>" class="button small red" lang="<?= $unternehmen['id']; ?>" title="Neue Stelle anlegen">Neue Stelle</a>
	<a href="#" onclick="<?= $linkNewAdPdf; ?>" class="button small red" lang="<?= $unternehmen['id']; ?>" title="Neue Stelle anlegen">Neue PDF-Stelle</a>
	<a href="/firmen/agentur/unternehmensdaten/<?= $unternehmen['id']; ?>/" class="button small red" lang="<?= $unternehmen['id']; ?>" title="Daten des Unternehmen bearbeiten">Unternehmensdaten bearbeiten</a>
	<a href="/firmen/agentur/unternehmen-loeschen/<?= $unternehmen['id']; ?>/" class="button small alternative" lang="<?= $unternehmen['id']; ?>" title="Unternehmen mit allen zugehörigen Stellen löschen">Unternehmen löschen</a>
</p>
<table id="jobAdList" class="job-ad-list" cellpadding="0" cellspacing="0">
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
	<tbody>

<?php		$stellen = $agentur->findeStellenUnternehmen($unternehmen['id']); ?>
<?php		$position = 1; ?>
<?php		if ($stellen !== false) : ?>
<?php			foreach ($stellen as $stelle) : ?>
<?php				$stellenDetails = $agentur->getStellenDetails($stelle); ?>
		
		<tr class="<?= ($stellenDetails['inactive'] == 'true' ? 'inactive' : 'active'); ?>">
			<td class="first">
				<form action="/firmen/angeboteliste_agentur/" method="post">
					<fieldset>
						<input type="image" src="/gifs/alpha.gif" name="activate_send" value="" class="<?= ($stellenDetails['inactive'] == 'true' ? 'offline' : 'online'); ?>" title="Stelle <?= ($stellenDetails['inactive'] == 'true' ? 'aktivieren' : 'deaktivieren'); ?>" />
						<input type="hidden" name="set_<?= ($stellenDetails['inactive'] == 'true' ? 'online' : 'offline'); ?>" value="<?= intval($stellenDetails['id']); ?>" />
					</fieldset>
				</form>
			</td>
			<td><?= $position; ?>.</td>
			<td<?= ($stellenDetails['inactive'] == 'true' ? ' class="inactive"' : ''); ?>><a href="/firmen/agentur/stelle/<?= $stellenDetails['id']; ?>/<?= $unternehmen['id']; ?>/content.html" title="Stelle bearbeiten"><?= (empty($stellenDetails['taetigkeit']) ? 'Kein Titel &raquo; Stelle bearbeiten' : stripcslashes($stellenDetails['taetigkeit'])); ?></a></td>
			<td><a href="/firmen/stelle/<?= intval($stellenDetails['id']); ?>/statistik-detail.html"><?= $views[$stellenDetails['id']]; ?></a></td>
			<td class="date"><?= date('d.m.Y', strtotime($stellenDetails['modify'])); ?></td>
			<td class="date">&nbsp;</td>
			<td>
				<a href="/firmen/agentur/stelle/<?= $stellenDetails['id']; ?>/<?= $unternehmen['id']; ?>/content.html" class="edit" title="Stelle bearbeiten"><img src="/gifs/alpha.gif" alt="Stelle ansehen" class="edit" /></a>
				<a href="/PREVIEW/PREVIEW-<?= intval($stellenDetails['id']); ?>.html?h=<?=md5($stellenDetails['firmenid']."#".$stellenDetails['id']) ?>" target="_blank" class="view" title="Vorschau ansehen"><img src="/gifs/alpha.gif" class="view" alt="Vorschau ansehen" /></a>
				<form action="/firmen/angeboteliste_agentur/" method="post">
					<fieldset>
						<input type="image" src="/gifs/alpha.gif" name="duplicate_send" value="" class="duplicate"<?= ((isset($duplicate) && $duplicate == false) ? $duplicateMessage : ''); ?> title="Stelle duplizieren" />
						<input type="hidden" name="duplicate" value="<?= $stellenAnzahl <= 200 ? intval($stellenDetails['id']) : '0'; ?>" />
						<input type="hidden" name="unternehmens_id" value="<?= $unternehmen['id']; ?>" />
					</fieldset>
				</form>
				<a href="/firmen/stelle/<?= intval($stellenDetails['id']); ?>/statistik-detail.html" class="stats"><img src="/gifs/alpha.gif" alt="Statistiken zur Stelle ansehen" class="stats" title="Statistiken ansehen" /></a>
				<form action="/firmen/angeboteliste_agentur/" method="post">
					<fieldset>
						<input type="image" src="/gifs/alpha.gif" name="delete_send" value="" class="delete" title="Stelle l&ouml;schen" />
						<input type="hidden" name="delete" value="<?= intval($stellenDetails['id']); ?>" />
						<input type="hidden" name="unternehmens_id" value="<?= $unternehmen['id']; ?>" />
					</fieldset>
				</form>
			</td>
		</tr>
		<tr>
			<td class="spacer" colspan="7"></td>
		</tr>

<?php				$position++; ?>
<?php			endforeach; ?>
<?php		else : ?>

		<tr>
			<td colspan="7">Es wurden noch keine Stellen für dieses Unternehmen angelegt</td>
		</tr>

<?php		endif; ?>
	</tbody>
</table>

<?php	endforeach; ?>

<div id="helpBox" class="hide">
	<h2>Hinweise zu Ihrer Stellentabelle</h2>
	<img src="/styles/images/companies/hilfe.png" alt="Hilfe zur Stellen&uuml;bersicht" />
</div>

<p><a href="#" onclick="hideHelp(); return false;">Hilfe zur Stellenliste</a></p>
<p>Anzahl genutzter Stellplätze: <strong><?= $agentur->getAnzahlStellen() === false ? '0' : $agentur->getAnzahlStellen(); ?></strong> von 20</p>

<?php else : ?>

<p class="hint info">Es wurden noch keine Unternehmen angelegt.</p>

<?php endif; ?>

<?php
$_SESSION['sidebar'] = '';
$_SESSION['servicecenter'] = '';
bodyoff();
?>