<?php
require("/home/praktika/etc/config.inc.php");

$_SESSION['current_page'] = PAGE_LANGUAGE_HOLIDAY;
$_SESSION['current_user_group'] = USER_GROUP_CANDIDATES;
$_SESSION['sidebar'] = 'sprachreisen';

if (isset($_POST['save'])) {
	$error = 0;
	$auswahl = true;
	$update = '';
	
	if (!isset($_POST['flug_buchung'])) {
		$error++;
		$auswahl = false;
	} else {
		foreach ($_POST as $key => $value) {
			if ($_POST['flug_buchung'] == 'true' && ($key == 'abflug' || $key == 'rueckflug') && empty($value)) {
				$error++;
			} elseif ($_POST['flug_buchung'] == 'true' && ($key == 'abflughafen' || $key == 'zielflughafen') && empty($value)) {
				$error++;
			} elseif ($_POST['flug_buchung'] == 'true' && $key != 'save' && $key != 'abflug_view' && $key != 'rueckflug_view') {
				$update .= $key.' = \''.mysql_real_escape_string($value).'\', ';
			} else {
				if ($key != 'save' && $key != 'abflug_view' && $key != 'rueckflug_view') {
					$update .= $key.' = \''.mysql_real_escape_string($value).'\', ';
				}
			}
			
			$_SESSION['sprachreisen'][$key] = $value;
		}
	}

	if ($error == 0) {
		$update = substr($update, 0, (strlen($update) - 2));

		$sql = 'UPDATE sprachreisen_buchung SET '.$update.' WHERE email = \''.$_SESSION['sprachreisen']['email'].'\' AND id = '.$_SESSION['sprachreisen']['eintragsnummer'];

		mysql_select_db($database_programs);
		
		if (mysql_query($sql, $praktdbmaster) !== false) {
			header('Location: /sprachreisen/buchung5/');
			exit();
		} else {
			echo '<p class="hint error">Es ist ein Fehler aufgetreten: '.mysql_error().'</p>'."\n";
		}
	}
}

if (!isset($_POST['save']) && isset($_SESSION['sprachreisen']['flug_buchung'])) {
	$_POST['save'] = '1';
	$emailValid = true;
	
	foreach ($_SESSION['sprachreisen'] as $key => $value) {
		$_POST[$key] = stripslashes($value);
	}
} elseif (isset($_POST['save'])) {
	foreach ($_POST as $key => $value) {
		$_POST[$key] = stripslashes($value);
	}
}

pageheader(array('page_title' => 'Sprachreise buchen', 'jqueryui' => 'true'));

Praktika_Static::__('sprachreisen');
?>

<script type="text/javascript">
	$(document).ready(function() {
		$('#abflug_view').datepicker({
			dateFormat: 'dd.mm.yy',
			altField: '#abflug',
			altFormat: 'yy-mm-dd',
			changeMonth: true,
			changeYear: true,
			minDate: '+1m',
			defaultDate: '+1m',
			yearRange: '0:+4'
		});
		$('#rueckflug_view').datepicker({
			dateFormat: 'dd.mm.yy',
			altField: '#rueckflug',
			altFormat: 'yy-mm-dd',
			changeMonth: true,
			changeYear: true,
			minDate: '+1m',
			defaultDate: '+1m',
			yearRange: '0:+4'
		});
	});

	function flugbuchung() {
		if (document.getElementById('flug_buchung2').checked == true) {
			document.getElementById('flugdaten1').style.display = 'none';
			document.getElementById('flugdaten2').style.display = 'block';
		} else if (document.getElementById('flug_buchung1').checked == true) {
			document.getElementById('flugdaten2').style.display = 'none';
			document.getElementById('flugdaten1').style.display = 'block';
		}
	}
	
	function flugtransfer() {
		if (document.getElementById('flug_transfer').checked == true) {
			document.getElementById('transferdaten').style.display = 'block';
		} else if (document.getElementById('flug_transfer').checked == false) {
			document.getElementById('transferdaten').style.display = 'none';
		}
	}
</script>
<form action="/sprachreisen/buchung4/" method="post" name="sprachreisenFormVier" id="sprachreisen_buchung">
	<h4>Schritt 4 von 6: Anreise</h4>
	<fieldset>

<?php
if (isset($_POST['save']) && $error != 0) {
	echo '<p class="hint error">Bitte f&uuml;llen Sie alle rot hervorgehobenen Felder korrekt aus.</p>'."\n";
}
?>
		
		<p class="checkboxes">
			<label class="komplette_breite" for="flug_buchung1"><input type="radio" id="flug_buchung1" name="flug_buchung" value="false" onclick="flugbuchung();"<?php echo (isset($_POST['save']) && $_POST['flug_buchung'] == 'false') ? ' checked="checked"' : ''; ?> /> Ich reise selber an</label>
			<label class="komplette_breite" for="flug_buchung2"><input type="radio" id="flug_buchung2" name="flug_buchung" value="true" onclick="flugbuchung();"<?php echo (isset($_POST['save']) && $_POST['flug_buchung'] == 'true') ? ' checked="checked"' : ''; ?> /> Ja, PRAKTIKA soll einen Flug f&uuml;r mich buchen.</label>
		</p>
		<div id="flugdaten1" class="box_gray<?php echo (isset($_POST['save']) && $_POST['flug_buchung'] == 'false') ? ' show' : ' hide'; ?>">
			<p>
				<label for="daten_flug">Meine Fludaten:</label>
				<textarea id="daten_flug" name="daten_flug" cols="5" rows="5"><?php echo (isset($_POST['save']) && !empty($_POST['daten_flug'])) ? $_POST['daten_flug'] : ''; ?></textarea>
			</p>
		</div>
		<div id="flugdaten2" class="box_gray<?php echo (isset($_POST['save']) && $_POST['flug_buchung'] == 'true') ? ' show' : ' hide'; ?>">
			<p>
				<label for="abflughafen"<?php echo (isset($_POST['save']) && empty($_POST['abflughafen'])) ? ' class="error"' : ''; ?>>Abflughafen:<sup>*</sup></label>
				<input type="text" id="abflughafen" name="abflughafen" value="<?php echo (isset($_POST['save']) && !empty($_POST['abflughafen'])) ? $_POST['abflughafen'] : ''; ?>" />
			</p>
			<p>
				<label for="zielflughafen"<?php echo (isset($_POST['save']) && empty($_POST['zielflughafen'])) ? ' class="error"' : ''; ?>>Zielflughafen:<sup>*</sup></label>
				<input type="text" id="zielflughafen" name="zielflughafen" value="<?php echo (isset($_POST['save']) && !empty($_POST['zielflughafen'])) ? $_POST['zielflughafen'] : ''; ?>" />
			</p>
			<p>
				<label for="abflug_view"<?php echo (isset($_POST['save']) && empty($_POST['abflug'])) ? ' class="error"' : '' ?>>Abflugdatum:<sup>*</sup></label>
				<input type="text" id="abflug_view" name="abflug_view" value="<? echo (isset($_POST['save']) && !empty($_POST['abflug_view'])) ? $_POST['abflug_view'] : ''; ?>" />
				<input type="hidden" id="abflug" name="abflug" value="<? echo (isset($_POST['save']) && !empty($_POST['abflug'])) ? $_POST['abflug'] : ''; ?>" />
			</p>
			<p>
				<label for="rueckflug_view"<?php echo (isset($_POST['save']) && empty($_POST['rueckflug'])) ? ' class="error"' : '' ?>>R&uuml;ckflugdatum:<sup>*</sup></label>
				<input type="text" id="rueckflug_view" name="rueckflug_view" value="<? echo (isset($_POST['save']) && !empty($_POST['rueckflug_view'])) ? $_POST['rueckflug_view'] : ''; ?>" />
				<input type="hidden" id="rueckflug" name="rueckflug" value="<? echo (isset($_POST['save']) && !empty($_POST['rueckflug'])) ? $_POST['rueckflug'] : ''; ?>" />
			</p>
			<p>
				<label for="flug_max_preis">max. Preis f&uuml;r Hin- und R&uuml;ckflug:</label>
				<input type="text" id="flug_max_preis" name="flug_max_preis" class="html_controlled" size="4" maxlength="4" value="<?php echo (isset($_POST['save']) && !empty($_POST['flug_max_preis'])) ? $_POST['flug_max_preis'] : ''; ?>" /> &euro;
			</p>
			<p class="checkboxes">
				<label class="komplette_breite" for="flug_transfer"><input type="checkbox" id="flug_transfer" name="flug_transfer" value="true" onclick="flugtransfer();"<?php echo (isset($_POST['save']) && $_POST['flug_transfer'] == 'true') ? ' checked="checked"' : ''; ?> /> Ja, PRAKTIKA soll einen Flugtransfer zur Unterkunft f&uuml;r mich buchen.</label>
			</p>
			<div id="transferdaten" class="<?php echo (isset($_POST['save']) && $_POST['flug_transfer'] == 'true') ? 'show' : 'hide'; ?>">
				<p>
					<label for="anmerkung_transfer">Anmerkungen:</label>
					<textarea id="anmerkung_transfer" name="anmerkung_transfer" cols="5" rows="5"><?php echo (isset($_POST['save']) && !empty($_POST['anmerkung_transfer'])) ? $_POST['anmerkung_transfer'] : ''; ?></textarea>
				</p>
			</div>
		</div>
	</fieldset>
	<fieldset class="control_panel">
		<p>
			<a href="/sprachreisen/buchung3/" class="button alternative small">zur&uuml;ck</a>
			<input type="submit" class="button small button2" name="save" value="weiter zu Schritt 5" />
		</p>
	</fieldset>
</form>

<?php

$_SESSION['sidebar'] = '';
bodyoff();

?>