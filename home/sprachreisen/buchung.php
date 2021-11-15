<?php
require("/home/praktika/etc/config.inc.php");

$_SESSION['current_page'] = PAGE_LANGUAGE_HOLIDAY;
$_SESSION['current_user_group'] = USER_GROUP_CANDIDATES;
$_SESSION['sidebar'] = 'sprachreisen';

if (isset($_POST['save'])) {
	$error = 0;
	$emailValid = true;
	$insert = '';

	foreach ($_POST as $key => $value) {
		if (($key == 'anrede' || $key == 'sprachkenntnisse') && $value == '-1') {
			$error++;
		} elseif ($key == 'email' && empty($value)) {
			$error++;
		} elseif ($key == 'email' && filter_var(urldecode($_POST['email']), FILTER_VALIDATE_EMAIL) == false) {
			$error++;
			$emailValid = false;
		} else {
			if (empty($value)) {
				$error++;
			} else {
				if ($key != 'save' && $key != 'birthdate_view') {
					if ($_SESSION['sprachreisen']['update'] == true && $key == 'email' && $_SESSION['sprachreisen']['email'] == $value) {
						//do nothing
					} else {
						$insert .= $key.' = \''.mysql_real_escape_string($value).'\', ';
					}
				}
			}
		}
		
		$_SESSION['sprachreisen'][$key] = $value;
	}

	if ($error == 0) {
		$insert .= 'datum_eintrag = NOW()';

		if ($_SESSION['sprachreisen']['update'] == true) {
			$sql = 'UPDATE sprachreisen_buchung SET '.$insert.' WHERE id = '.$_SESSION['sprachreisen']['eintragsnummer'];
		} else {
			$sql = 'INSERT INTO sprachreisen_buchung SET '.$insert;
		}
	
		mysql_select_db($database_programs);
		
		if (mysql_query($sql, $praktdbmaster) !== false) {
			$_SESSION['sprachreisen']['eintragsnummer'] = isset($_SESSION['sprachreisen']['eintragsnummer']) ? $_SESSION['sprachreisen']['eintragsnummer'] : mysql_insert_id();
			
			header('Location: /sprachreisen/buchung2/');
			exit();
		} else {
			echo '<p class="hint error">Es ist ein Fehler aufgetreten: '.mysql_error().'</p>'."\n";
		}
	}
}

if (strpos($_SERVER['HTTP_REFERER'], 'buchung2') !== false) {
	$_POST['save'] = 'weiter';
	$emailValid = true;
	
	foreach ($_SESSION['sprachreisen'] as $key => $value) {
		$_POST[$key] = stripslashes($value);
	}
} elseif (isset($_POST['save'])) {
	foreach ($_POST as $key => $value) {
		$_POST[$key] = stripslashes($value);
	}
}

if (isset($_SESSION['sprachreisen']['eintragsnummer']) && !empty($_SESSION['sprachreisen']['eintragsnummer'])) {
	$_SESSION['sprachreisen']['update'] = true;
} else {
	$_SESSION['sprachreisen']['update'] = false;
}

pageheader(array('page_title' => 'Sprachreise buchen', 'jqueryui' => 'true'));

Praktika_Static::__('sprachreisen');
?>

<script type="text/javascript">
	$(document).ready(function() {
		$('#birthdate_view').datepicker({
			dateFormat: 'dd.mm.yy',
			altField: '#birthdate',
			altFormat: 'yy-mm-dd',
			changeMonth: true,
			changeYear: true,
			minDate: '-70y',
			defaultDate: '-20y',
			yearRange: '-70:-1'
		});
	});
</script>

<form action="/sprachreisen/buchung/" method="post" name="sprachreisenFormEins">
	<h4>Schritt 1 von 6: Pers&ouml;nliche Angaben (wie im Reisepass angeben)</h4>
	<fieldset>

<?php
if (isset($_POST['save']) && $error != 0) {
	echo '<p class="hint error">Bitte f&uuml;llen Sie alle rot hervorgehobenen Felder korrekt aus.</p>'."\n";
}
?>
		<p>
			<label for="anrede"<?php echo (isset($_POST['save']) && $_POST['anrede'] == '-1') ? ' class="error"' : ''; ?>>Anrede:<sup>*</sup></label>
			<select id="anrede" name="anrede">
				<option value="-1">----------------</option>
				<option value="Herr"<?php echo (isset($_POST['save']) && $_POST['anrede'] == 'Herr') ? ' selected="selected"' : ''; ?>>Herr</option>
				<option value="Frau"<?php echo (isset($_POST['save']) && $_POST['anrede'] == 'Frau') ? ' selected="selected"' : ''; ?>>Frau</option>

			</select>
		</p>
		<p>
			<label for="vorname"<?php echo (isset($_POST['save']) && empty($_POST['vorname'])) ? ' class="error"' : ''; ?>>Vorname:<sup>*</sup></label>
			<input type="text" id="vorname" name="vorname" value="<?php echo (isset($_POST['save']) && !empty($_POST['vorname'])) ? $_POST['vorname'] : ''; ?>" />
		</p>
		<p>
			<label for="nachname"<?php echo (isset($_POST['save']) && empty($_POST['nachname'])) ? ' class="error"' : ''; ?>>Nachname:<sup>*</sup></label>
			<input type="text" id="nachname" name="nachname" value="<?php echo (isset($_POST['save']) && !empty($_POST['nachname'])) ? $_POST['nachname'] : ''; ?>" />
		</p>
		<p>
			<label for="birthdate_view"<?php echo (isset($_POST['save']) && empty($_POST['geb_datum'])) ? ' class="error"' : '' ?>>Geburtsdatum:<sup>*</sup></label>
			<input type="text" id="birthdate_view" name="birthdate_view" value="<? echo (isset($_POST['save']) && !empty($_POST['birthdate_view'])) ? $_POST['birthdate_view'] : ''; ?>" />
			<input type="hidden" id="birthdate" name="geb_datum" value="<? echo (isset($_POST['save']) && !empty($_POST['geb_datum'])) ? $_POST['geb_datum'] : ''; ?>" />
		</p>
		<p>
			<label for="geb_ort"<?php echo (isset($_POST['save']) && empty($_POST['geb_ort'])) ? ' class="error"' : ''; ?>>Geburtsort:<sup>*</sup></label>
			<input type="text" id="geb_ort" name="geb_ort" value="<?php echo (isset($_POST['save']) && !empty($_POST['geb_ort'])) ? $_POST['geb_ort'] : ''; ?>" />
		</p>
		<p>
			<label for="staatsbuergerschaft"<?php echo (isset($_POST['save']) && empty($_POST['staatsbuergerschaft'])) ? ' class="error"' : ''; ?>>Staatsb&uuml;rgerschaft:<sup>*</sup></label>
			<input type="text" id="staatsbuergerschaft" name="staatsbuergerschaft" value="<?php echo (isset($_POST['save']) && !empty($_POST['staatsbuergerschaft'])) ? $_POST['staatsbuergerschaft'] : ''; ?>" />
		</p>
		<p>
			<label for="strasse"<?php echo (isset($_POST['save']) && empty($_POST['strasse'])) ? ' class="error"' : ''; ?>>Stra&szlig;e:<sup>*</sup></label>
			<input type="text" id="strasse" name="strasse" value="<?php echo (isset($_POST['save']) && !empty($_POST['strasse'])) ? $_POST['strasse'] : ''; ?>" />
		</p>
		<p>
			<label for="plz"<?php echo (isset($_POST['save']) && ((isset($_POST['plz']) && empty($_POST['plz'])) || (isset($_POST['ort']) && empty($_POST['ort'])))) ? ' class="error"' : ''; ?>>PLZ / Ort:<sup>*</sup></label>
			<input type="text" id="plz" name="plz" value="<?php echo (isset($_POST['save']) && !empty($_POST['plz'])) ? $_POST['plz'] : ''; ?>" /> <input type="text" id="ort" name="ort" value="<?php echo (isset($_POST['save']) && !empty($_POST['ort'])) ? $_POST['ort'] : ''; ?>" />
		</p>
		<p>
			<label for="telefon"<?php echo (isset($_POST['save']) && empty($_POST['telefon'])) ? ' class="error"' : ''; ?>>Telefon / Mobil:<sup>*</sup></label>
			<input type="text" id="telefon" name="telefon" value="<?php echo (isset($_POST['save']) && !empty($_POST['telefon'])) ? $_POST['telefon'] : ''; ?>" />
		</p>

<?php
if (isset($_POST['save']) && $emailValid == false) {
	echo '<p class="error">Die angegebene eMail-Adresse hat die Vailidit&auml;tspr&uuml;fung nicht bestanden</p>';
}
?>
		
		<p>
			<label for="email"<?php echo (isset($_POST['save']) && (empty($_POST['email']) || $emailValid == false)) ? ' class="error"' : ''; ?>>eMail:<sup>*</sup></label>
			<input type="text" id="email" name="email" value="<?php echo (isset($_POST['save']) && !empty($_POST['email'])) ? $_POST['email'] : ''; ?>" />
		</p>
		<p>
			<label for="sprachkenntnisse"<?php echo (isset($_POST['save']) && $_POST['sprachkenntnisse'] == '-1') ? ' class="error"' : ''; ?>>Sprachkenntnisse:<sup>*</sup></label>
			<select id="sprachkenntnisse" name="sprachkenntnisse">
				<option value="-1">----------------</option>
				<option value="a1"<?php echo (isset($_POST['save']) && $_POST['sprachkenntnisse'] == 'a1') ? ' selected="selected"' : ''; ?>>A1 - Anf&auml;nger</option>
				<option value="a2"<?php echo (isset($_POST['save']) && $_POST['sprachkenntnisse'] == 'a2') ? ' selected="selected"' : ''; ?>>A2 - Grundkenntnisse</option>
				<option value="b1"<?php echo (isset($_POST['save']) && $_POST['sprachkenntnisse'] == 'b1') ? ' selected="selected"' : ''; ?>>B1 - Mittelm&auml;&szlig;ig</option>
				<option value="b2"<?php echo (isset($_POST['save']) && $_POST['sprachkenntnisse'] == 'b2') ? ' selected="selected"' : ''; ?>>B2 - Gut</option>
				<option value="c1"<?php echo (isset($_POST['save']) && $_POST['sprachkenntnisse'] == 'c1') ? ' selected="selected"' : ''; ?>>C1 - Sehr gut</option>
				<option value="c2"<?php echo (isset($_POST['save']) && $_POST['sprachkenntnisse'] == 'c2') ? ' selected="selected"' : ''; ?>>C2 - Perfekt</option>
			</select>
		</p>
	</fieldset>
	<fieldset class="control_panel">
		<p>
			<input type="submit" class="button small" name="save" value="weiter zu Schritt 2" />
		</p>
	</fieldset>
</form>


<?php

$_SESSION['sidebar'] = '';
bodyoff();

?>