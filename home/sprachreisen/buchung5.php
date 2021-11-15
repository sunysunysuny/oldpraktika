<?php
require("/home/praktika/etc/config.inc.php");

$_SESSION['current_page'] = PAGE_LANGUAGE_HOLIDAY;
$_SESSION['current_user_group'] = USER_GROUP_CANDIDATES;
$_SESSION['sidebar'] = 'sprachreisen';

if (isset($_POST['save'])) {
	$error = 0;
	$update = '';

	if (!isset($_POST['akv'])) {
		$_POST['akv'] = 'false';
	}
	
	if (!isset($_POST['rkv'])) {
		$_POST['rkv'] = 'false';
	}

	foreach ($_POST as $key => $value) {
		if ($key != 'save') {
			$update .= $key.' = \''.mysql_real_escape_string($value).'\', ';
		}
		
		$_SESSION['sprachreisen'][$key] = $value;
	}

	if ($error == 0) {
		$sql = 'UPDATE sprachreisen_buchung SET '.substr($update, 0, (strlen($update) - 2)).' WHERE email = \''.$_SESSION['sprachreisen']['email'].'\' AND id = '.$_SESSION['sprachreisen']['eintragsnummer'];
	
		mysql_select_db($database_programs);
		
		if (mysql_query($sql, $praktdbmaster) !== false) {
			header('Location: /sprachreisen/buchung6/');
			exit();
		} else {
			echo '<p class="hint error">Es ist ein Fehler aufgetreten: '.mysql_error().'</p>'."\n";
		}
	}
}

if (!isset($_POST['save']) && isset($_SESSION['sprachreisen']['akv']) || isset($_SESSION['sprachreisen']['rkv'])) {
	$_POST['save'] = '1';
	
	foreach ($_SESSION['sprachreisen'] as $key => $value) {
		$_POST[$key] = stripslashes($value);
	}
} elseif (isset($_POST['save'])) {
	foreach ($_POST as $key => $value) {
		$_POST[$key] = stripslashes($value);
	}
}

pageheader(array('page_title' => 'Sprachreise buchen'));

Praktika_Static::__('sprachreisen');
?>

<form action="/sprachreisen/buchung5/" method="post" name="sprachreisenFormFuenf" id="sprachreisen_buchung">
	<h4>Schritt 5 von 6: Auslandskrankenversicherung</h4>
	<fieldset>
		<p>Ja, ich m&ouml;chte Informationen &uuml;ber eine g&uuml;nstige Versicherung haben.</p>
		<p class="checkboxes">
			<label class="komplette_breite" for="akv"><input type="checkbox" id="akv" name="akv" value="true"<?php echo (isset($_POST['save']) && isset($_POST['akv']) && $_POST['akv'] == 'true') ? ' checked="checked"' : ''; ?> /> Auslandskrankenversicherung (Information zur <a href="https://www.praktika.de/cms/Auslandskrankenversicherung.871.0.html" target="_blank">Auslandskrankenversicherung</a>)</label>
		</p>
		<p class="checkboxes">
			<label class="komplette_breite" for="rkv"><input type="checkbox" id="rkv" name="rkv" value="true"<?php echo (isset($_POST['save']) && isset($_POST['rkv']) && $_POST['rkv'] == 'true') ? ' checked="checked"' : ''; ?> /> Reiser&uuml;cktrittskostenversicherung</label>
		</p>
	</fieldset>
	<fieldset class="control_panel">
		<p>
			<a href="/sprachreisen/buchung4/" class="button alternative small">zur&uuml;ck</a>
			<input type="submit" class="button small button2" name="save" value="weiter zu Schritt 6" />
		</p>
	</fieldset>
</form>

<?php

$_SESSION['sidebar'] = '';
bodyoff();

?>