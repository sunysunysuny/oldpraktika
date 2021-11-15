<?php
require("/home/praktika/etc/config.inc.php");

$_SESSION['current_page'] = PAGE_LANGUAGE_HOLIDAY;
$_SESSION['current_user_group'] = USER_GROUP_CANDIDATES;
$_SESSION['sidebar'] = 'sprachreisen';

if (isset($_POST['save'])) {
	$error = 0;
	$unterkunft = true;
	$update = '';
	
	if (!isset($_POST['unterkunft'])) {
		$error++;
		$unterkunft = false;
	}

	foreach ($_POST as $key => $value) {
		if (($key == 'unterkunft_beginn_tag' || $key == 'unterkunft_beginn_monat' || $key == 'unterkunft_beginn_jahr') && $value == '-1') {
			$error++;
		} else {
			if ($key != 'wunsch' && $key != 'anmerkung_unterkunft' && empty($value)) {
				$error++;
			} else {
				if ($key != 'save' && $key != 'unterkunft_beginn_view') {
					$update .= $key.' = \''.mysql_real_escape_string($value).'\', ';
				}
			}
		}
		
		$_SESSION['sprachreisen'][$key] = $value;
	}

	if ($error == 0) {
		$update = substr($update, 0, (strlen($update) - 2));
	
		$sql = 'UPDATE sprachreisen_buchung SET '.$update.' WHERE email = \''.$_SESSION['sprachreisen']['email'].'\' AND id = '.$_SESSION['sprachreisen']['eintragsnummer'];
	
		mysql_select_db($database_programs);
		
		if (mysql_query($sql, $praktdbmaster) !== false) {
			header('Location: /sprachreisen/buchung4/');
			exit();
		} else {
			echo '<p class="hint error">Es ist ein Fehler aufgetreten: '.mysql_error().'</p>'."\n";
		}
	}
}

if (!isset($_POST['save']) && isset($_SESSION['sprachreisen']['unterkunft']) && !empty($_SESSION['sprachreisen']['unterkunft'])) {
	$_POST['save'] = '1';
	$unterkunft = true;
	
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
		$('#unterkunft_beginn_view').datepicker({
			dateFormat: 'dd.mm.yy',
			altField: '#unterkunft_beginn',
			altFormat: 'yy-mm-dd',
			changeMonth: true,
			changeYear: true,
			minDate: '+1m',
			defaultDate: '+1m',
			yearRange: '0:+4'
		});
	});
</script>

<form action="/sprachreisen/buchung3/" method="post" name="sprachreisenFormDrei" id="sprachreisen_buchung">
	<h4>Schritt 3 von 6: Unterkunft</h4>
	<fieldset>

<?php
if (isset($_POST['save']) && $error != 0) {
	echo '<p class="hint error">Bitte f&uuml;llen Sie alle rot hervorgehobenen Felder korrekt aus.</p>'."\n";
}
?>

		<p>
			<label for="unterkunft1"<?php echo (isset($_POST['save']) && $unterkunft == false) ? ' class="error"' : ''; ?>>Art der Unterkunft:<sup>*</sup></label>
			<span id="unterkunft">
				<label for="unterkunft1"><input type="radio" id="unterkunft1" name="unterkunft" value="hotel"<?php echo (isset($_POST['save']) && $_POST['unterkunft'] == 'hotel') ? ' checked="checked"' : ''; ?> /> Hotel / Einzelzimmer (Selbstversorger)</label>
				<label for="unterkunft2"><input type="radio" id="unterkunft2" name="unterkunft" value="familie"<?php echo (isset($_POST['save']) && $_POST['unterkunft'] == 'familie') ? ' checked="checked"' : ''; ?> /> Familie / Einzelzimmer (Halbpension)</label>
				<label for="unterkunft3"><input type="radio" id="unterkunft3" name="unterkunft" value="false"<?php echo (isset($_POST['save']) && $_POST['unterkunft'] == 'false') ? ' checked="checked"' : ''; ?> /> ohne Unterkunft</label>
			</span>
			<span id="other_stay">
				<label for="wunsch">Wunsch:</label>
				<input type="text" id="wunsch" class="html_controlled" name="wunsch" size="28" maxlength="255" value="<?php echo (isset($_POST['save']) && !empty($_POST['wunsch'])) ? $_POST['wunsch'] : ''; ?>" />
			</span>
		</p>
		<p>
			<label for="unterkunft_beginn_view"<?php echo (isset($_POST['save']) && empty($_POST['unterkunft_beginn'])) ? ' class="error"' : '' ?>>Beginn der Unterkunft:<sup>*</sup></label>
			<input type="text" id="unterkunft_beginn_view" name="unterkunft_beginn_view" value="<? echo (isset($_POST['save']) && !empty($_POST['unterkunft_beginn_view'])) ? $_POST['unterkunft_beginn_view'] : ''; ?>" />
			<input type="hidden" id="unterkunft_beginn" name="unterkunft_beginn" value="<? echo (isset($_POST['save']) && !empty($_POST['unterkunft_beginn'])) ? $_POST['unterkunft_beginn'] : ''; ?>" />
		</p>
		<p>
			<label for="unterkunft_dauer"<?php echo (isset($_POST['save']) && empty($_POST['unterkunft_dauer'])) ? ' class="error"' : ''; ?>>Dauer in Wochen:<sup>*</sup></label>
			<input type="text" id="unterkunft_dauer" name="unterkunft_dauer" class="html_controlled" size="2" maxlength="2" value="<?php echo (isset($_POST['save']) && !empty($_POST['unterkunft_dauer'])) ? $_POST['unterkunft_dauer'] : ''; ?>" /> Wochen
		</p>
		<p>
			<label for="anmerkung_unterkunft">Zusatzinformationen (Allergien, Vegetarier, Raucher etc.):</label>
			<textarea id="anmerkung_unterkunft" name="anmerkung_unterkunft" cols="5" rows="5"><?php echo (isset($_POST['save']) && !empty($_POST['anmerkung_unterkunft'])) ? $_POST['anmerkung_unterkunft'] : ''; ?></textarea>
		</p>
	</fieldset>
	<fieldset class="control_panel">
		<p>
			<a href="/sprachreisen/buchung2/" class="button alternative small">zur&uuml;ck</a>
			<input type="submit" class="button small button2" name="save" value="weiter zu Schritt 4" />
		</p>
	</fieldset>
</form>

<?php

$_SESSION['sidebar'] = '';
bodyoff();

?>