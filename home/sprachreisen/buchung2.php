<?php
require("/home/praktika/etc/config.inc.php");

$_SESSION['current_page'] = PAGE_LANGUAGE_HOLIDAY;
$_SESSION['current_user_group'] = USER_GROUP_CANDIDATES;
$_SESSION['sidebar'] = 'sprachreisen';

if (isset($_POST['save'])) {
	$error = 0;
	$dauerValid = true;
	$verbesserung = true;
	$update = '';
	
	if (!isset($_POST['konversation']) && !isset($_POST['grammatik']) && !isset($_POST['hoerverstaendnis']) && !isset($_POST['korrespondenz']) && !isset($_POST['aussprache']) && !isset($_POST['kon_telefon']) && empty($_POST['verbesserung_sonstiges'])) {
		$verbesserung = false;
		$error++;
	}

	if (!isset($_POST['konversation'])) {
		$_POST['konversation'] = 'false';
	}
	
	if (!isset($_POST['grammatik'])) {
		$_POST['grammatik'] = 'false';
	}
	
	if (!isset($_POST['hoerverstaendnis'])) {
		$_POST['hoerverstaendnis'] = 'false';
	}
	
	if (!isset($_POST['korrespondenz'])) {
		$_POST['korrespondenz'] = 'false';
	}
	
	if (!isset($_POST['aussprache'])) {
		$_POST['aussprache'] = 'false';
	}
	
	if (!isset($_POST['kon_telefon'])) {
		$_POST['kon_telefon'] = 'false';
	}
	
	foreach ($_POST as $key => $value) {
		if ($key == 'dauer' && $value != '-1' && !empty($_POST['andere_dauer'])) {
			$error++;
			$dauerValid = false;
		} else {
			if ($key != 'anmerkung_kurs' && $key != 'dauer' && $key != 'andere_dauer' && $key != 'verbesserung_sonstiges' && empty($value)) {
				$error++;
			} else {
				if ($key != 'save' && $key != 'dauer' && $key != 'andere_dauer' && $key != 'kursbeginn_view') {
					$update .= $key.' = \''.mysql_real_escape_string($value).'\', ';
				}
			}
		}

		$_SESSION['sprachreisen'][$key] = $value;
	}
	
	if ($error == 0) {
		if ($_POST['dauer'] == '-1') {
			$update .= 'kursdauer = \''.mysql_real_escape_string($_POST['andere_dauer']).'\'';
		} else {
			$update .= 'kursdauer = \''.mysql_real_escape_string($_POST['dauer']).'\'';
		}
		
		$sql = 'UPDATE sprachreisen_buchung SET '.$update.' WHERE email = \''.$_SESSION['sprachreisen']['email'].'\' AND id = '.$_SESSION['sprachreisen']['eintragsnummer'];
	
		mysql_select_db($database_programs);
		
		if (mysql_query($sql, $praktdbmaster) !== false) {
			header('Location: /sprachreisen/buchung3/');
			exit();
		} else {
			echo '<p class="hint error">Es ist ein Fehler aufgetreten: '.mysql_error().'</p>'."\n";
		}
	}
}

if (!isset($_POST['save']) && isset($_SESSION['sprachreisen']['stadt']) && !empty($_SESSION['sprachreisen']['stadt'])) {
	$_POST['save'] = '1';
	$dauerValid = true;
	$verbesserung = true;
	
	foreach ($_SESSION['sprachreisen'] as $key => $value) {
		$_POST[$key] = stripslashes($value);
	}
	
	if (isset($_SESSION['sprachreisen']['konversation']) || isset($_SESSION['sprachreisen']['grammatik']) || isset($_SESSION['sprachreisen']['hoerverstaendnis']) || isset($_SESSION['sprachreisen']['korrespondenz']) || isset($_SESSION['sprachreisen']['aussprache']) || isset($_SESSION['sprachreisen']['kon_telefon']) || !empty($_SESSION['sprachreisen']['verbesserung_sonstiges'])) {
		$verbesserung = true;
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
		$('#kursbeginn_view').datepicker({
			dateFormat: 'dd.mm.yy',
			altField: '#kursbeginn',
			altFormat: 'yy-mm-dd',
			changeMonth: true,
			changeYear: true,
			minDate: 0,
			beforeShowDay: function(date) { 
				var day = date.getDay();
				return [day == 1, ''];
			}
		});
	});
</script>

<form action="/sprachreisen/buchung2/" method="post" name="sprachreisenFormZwei" id="sprachreisen_buchung">
	<h4>Schritt 2 von 6: Kurse</h4>
	<fieldset>

<?php
if (isset($_POST['save']) && $error != 0) {
	echo '<p class="hint error">Bitte f&uuml;llen Sie alle rot hervorgehobenen Felder korrekt aus.</p>'."\n";
}
?>

		<p>
			<label for="stadt"<?php echo (isset($_POST['save']) && $_POST['stadt'] == '-1') ? ' class="error"' : ''; ?>>Stadt:<sup>*</sup></label>
			<select id="stadt" name="stadt">
				<option value="-1">----------------</option>

<?php
mysql_select_db($database_programs);

$staedte = mysql_query('SELECT id, name FROM sprachreisen ORDER BY id ASC', $praktdbslave);

if (mysql_num_rows($staedte) > 0) {
	while ($result = mysql_fetch_array($staedte)) {
		echo '				<option value="'.$result['id'].'"'.((isset($_POST['save']) && $_POST['stadt'] == $result['id']) ? ' selected="selected"' : '').'>'.str_replace('Sprachreise nach ', '', $result['name']).'</option>'."\n";
	}
}
?>			
			
			</select>
		</p>
		<p>
			<label for="kursart"<?php echo (isset($_POST['save']) && $_POST['kursart'] == '-1') ? ' class="error"' : ''; ?>>Kursart:<sup>*</sup></label>
			<select id="kursart" name="kursart">
				<option value="-1">----------------</option>
				<option value="standard"<?php echo (isset($_POST['save']) && $_POST['kursart'] == 'standard') ? ' selected="selected"' : ''; ?>>Standard</option>
				<option value="intensiv"<?php echo (isset($_POST['save']) && $_POST['kursart'] == 'intensiv') ? ' selected="selected"' : ''; ?>>Intensiv</option>
				<option value="business"<?php echo (isset($_POST['save']) && $_POST['kursart'] == 'business') ? ' selected="selected"' : ''; ?>>Business</option>
			</select>
		</p>
		<p>
			<label for="kursbeginn_view"<?php echo (isset($_POST['save']) && empty($_POST['kursbeginn'])) ? ' class="error"' : '' ?>>Beginn (immer montags):<sup>*</sup></label>
			<input type="text" id="kursbeginn_view" name="kursbeginn_view" value="<? echo (isset($_POST['save']) && !empty($_POST['kursbeginn_view'])) ? $_POST['kursbeginn_view'] : ''; ?>" />
			<input type="hidden" id="kursbeginn" name="kursbeginn" value="<? echo (isset($_POST['save']) && !empty($_POST['kursbeginn'])) ? $_POST['kursbeginn'] : ''; ?>" />
		</p>

		
<?php
if (isset($_POST['save']) && $dauerValid == false) {
	echo '<p class="hint error">Bitte geben Sie lediglich eine Dauer an!</p>';
}
?>

		<p>
			<label for="dauer"<?php echo (isset($_POST['save']) && (($_POST['dauer'] == '-1' && empty($_POST['andere_dauer'])) || $dauerValid == false)) ? ' class="error"' : ''; ?>>Dauer in Wochen:<sup>*</sup></label>
			<select id="dauer" name="dauer" class="html_controlled">
				<option value="-1">--</option>
				<option value="2"<?php echo (isset($_POST['save']) && $_POST['dauer'] == '2') ? ' selected="selected"' : ''; ?>>2</option>
				<option value="3"<?php echo (isset($_POST['save']) && $_POST['dauer'] == '3') ? ' selected="selected"' : ''; ?>>3</option>
				<option value="4"<?php echo (isset($_POST['save']) && $_POST['dauer'] == '4') ? ' selected="selected"' : ''; ?>>4</option>
				<option value="8"<?php echo (isset($_POST['save']) && $_POST['dauer'] == '8') ? ' selected="selected"' : ''; ?>>8</option>
				<option value="12"<?php echo (isset($_POST['save']) && $_POST['dauer'] == '12') ? ' selected="selected"' : ''; ?>>12</option>
			</select>
			<span id="andere">
				<label for="andere_dauer">andere Kursdauer:</label>
				<input type="text" id="andere_dauer" class="html_controlled" name="andere_dauer" size="2" maxlength="2" value="<?php echo (isset($_POST['save']) && !empty($_POST['andere_dauer'])) ? $_POST['andere_dauer'] : ''; ?>" /> Wochen
			</span>
		</p>
		<p>
			<label for="preislauttabelle"<?php echo (isset($_POST['save']) && empty($_POST['preislauttabelle'])) ? ' class="error"' : ''; ?>>Preis laut Tabelle:<sup>*</sup></label>
			<input type="text" id="preislauttabelle" class="html_controlled" name="preislauttabelle" size="8" maxlength="8" value="<?php echo (isset($_POST['save']) && !empty($_POST['preislauttabelle'])) ? $_POST['preislauttabelle'] : ''; ?>" /> &euro;
		</p>
		<p>
			<label for="konversation"<?php echo (isset($_POST['save']) && $verbesserung == false) ? ' class="error"' : ''; ?>>Was m&ouml;chten Sie verbessern:<sup>*</sup></label>
			<span id="verbesserungen">
				<label for="konversation"><input type="checkbox" id="konversation" name="konversation" value="true"<?php echo (isset($_POST['save']) && isset($_POST['konversation']) && $_POST['konversation'] == 'true') ? ' checked="checked"' : ''; ?> /> Konversation</label>
				<label for="grammatik"><input type="checkbox" id="grammatik" name="grammatik" value="true"<?php echo (isset($_POST['save']) && isset($_POST['grammatik']) && $_POST['grammatik'] == 'true') ? ' checked="checked"' : ''; ?> /> Grammatik</label>
				<label for="hoerverstaendnis"><input type="checkbox" id="hoerverstaendnis" name="hoerverstaendnis" value="true"<?php echo (isset($_POST['save']) && isset($_POST['hoerverstaendnis']) && $_POST['hoerverstaendnis'] == 'true') ? ' checked="checked"' : ''; ?> /> H&ouml;rverst&auml;ndnis</label>
				<label for="korrespondenz"><input type="checkbox" id="korrespondenz" name="korrespondenz" value="true"<?php echo (isset($_POST['save']) && isset($_POST['korrespondenz']) && $_POST['korrespondenz'] == 'true') ? ' checked="checked"' : ''; ?> /> Verfassen von Korrespondenz</label>
				<label for="aussprache"><input type="checkbox" id="aussprache" name="aussprache" value="true"<?php echo (isset($_POST['save']) && isset($_POST['aussprache']) && $_POST['aussprache'] == 'true') ? ' checked="checked"' : ''; ?> /> Aussprache</label>
				<label for="kon_telefon"><input type="checkbox" id="kon_telefon" name="kon_telefon" value="true"<?php echo (isset($_POST['save']) && isset($_POST['kon_telefon']) && $_POST['kon_telefon'] == 'true') ? ' checked="checked"' : ''; ?> /> Konversation am Telefon</label>
			</span>
			<span id="sonstiges">
				<label for="verbesserung_sonstiges">Sonstiges:</label>
				<input type="text" id="verbesserung_sonstiges" class="html_controlled" name="verbesserung_sonstiges" size="33" maxlength="255" value="<?php echo (isset($_POST['save']) && !empty($_POST['verbesserung_sonstiges'])) ? $_POST['verbesserung_sonstiges'] : ''; ?>" />
			</span>
		</p>
	</fieldset>
	<fieldset class="control_panel">
		<p>
			<a href="/sprachreisen/buchung/" class="button alternative small">zur&uuml;ck</a>
			<input type="submit" class="button small button2" name="save" value="weiter zu Schritt 3" />
		</p>
	</fieldset>
</form>

<?php

$_SESSION['sidebar'] = '';
bodyoff();

?>