<?php
require("/home/praktika/etc/config.inc.php");

$_SESSION['current_page'] = PAGE_LANGUAGE_HOLIDAY;
$_SESSION['current_user_group'] = USER_GROUP_CANDIDATES;
$_SESSION['sidebar'] = 'sprachreisen';

if (isset($_POST['save'])) {
	$error = 0;
	$update = '';
	$emailText = '';
	$suchmaschine = true;
	$uni = true;
	$andere = true;

	if (isset($_POST['aufmerksam_geworden'])) {
		if ($_POST['aufmerksam_geworden'] == '1') {
			if (empty($_POST['aufmerksam_suchmaschine'])) {
				$error++;
				$suchmaschine = false;
			} else {
				$update .= 'aufmerksam_geworden = \''.mysql_real_escape_string($_POST['aufmerksam_geworden']).'\', ';
				$update .= 'aufmerksam_suchmaschine = \''.mysql_real_escape_string($_POST['aufmerksam_suchmaschine']).'\', ';
			}
		} elseif ($_POST['aufmerksam_geworden'] == '2') {
			if (empty($_POST['aufmerksam_uni'])) {
				$error++;
				$uni = false;
			} else {
				$update .= 'aufmerksam_geworden = \''.mysql_real_escape_string($_POST['aufmerksam_geworden']).'\', ';
				$update .= 'aufmerksam_uni = \''.mysql_real_escape_string($_POST['aufmerksam_uni']).'\', ';
			}
		} elseif ($_POST['aufmerksam_geworden'] == '3') {
			if (empty($_POST['aufmerksam_andere'])) {
				$error++;
				$andere = false;
			} else {
				$update .= 'aufmerksam_geworden = \''.mysql_real_escape_string($_POST['aufmerksam_geworden']).'\', ';
				$update .= 'aufmerksam_andere = \''.mysql_real_escape_string($_POST['aufmerksam_andere']).'\', ';
			}
		} else {
			$update .= 'aufmerksam_geworden = \''.mysql_real_escape_string($_POST['aufmerksam_geworden']).'\', ';
		}
	} else {
		$error++;
	}
	
	if (!isset($_POST['reisebedingung'])) {
		$error++;
	} else {
		$update .= 'reisebedingung = \''.mysql_real_escape_string($_POST['reisebedingung']).'\'';
	}

	if ($error == 0) {
		//Datenbankeintrag
		$sql = 'UPDATE sprachreisen_buchung SET '.$update.' WHERE email = \''.$_SESSION['sprachreisen']['email'].'\' AND id = '.$_SESSION['sprachreisen']['eintragsnummer'];
	
		mysql_select_db($database_programs);
		
		if (mysql_query($sql, $praktdbmaster) !== false) {
			$staedte = mysql_query('SELECT name FROM sprachreisen WHERE id = '.$_SESSION['sprachreisen']['stadt'], $praktdbslave);

			if (mysql_num_rows($staedte) > 0) {
				$stadt = mysql_result($staedte, 0, 'name');
			} else {
				$stadt = '';
			}			

			$_SESSION['sprachreisen']['stadt'] = $stadt;
			
			$name = $_SESSION['sprachreisen']['anrede'].' '.$_SESSION['sprachreisen']['vorname'].' '.$_SESSION['sprachreisen']['nachname'];
			
			foreach ($_SESSION['sprachreisen'] as $key => $value) {
				$emailText .= ucfirst($key).': '.$value."\n";
			}
			
			$subject = $name.' hat eine '.$stadt.' gebucht';
			
			$header = 'From: '.$name.' <'.$_SESSION['sprachreisen']['email'].'>'."\n";    
			$header .= 'Reply-To: '.$name.' <'.$_SESSION['sprachreisen']['email'].'>'."\r\n";
			$header .= 'X-Mailer: PHP/'. phpversion()."\n";
			$header .= 'X-Sender-IP: '.$_SERVER['REMOTE_ADDR']."\n";
			$header .= 'Content-type: text/plain; charset=UTF-8'."\n"; 
			
			mail('programme@praktika.de', $subject, $emailText, $header);
			
			header('Location: /sprachreisen/buchung_erfolgreich/');
			exit();
		} else {
			echo '<p class="hint error">Es ist ein Fehler aufgetreten: '.mysql_error().'</p>'."\n";
		}
	}
}

if (isset($_POST['save'])) {
	foreach ($_POST as $key => $value) {
		$_POST[$key] = stripslashes($value);
	}
}

pageheader(array('page_title' => 'Sprachreise buchen'));

Praktika_Static::__('sprachreisen');
?>

<script type="text/javascript">
	function showTextField() {
		if (document.getElementById('aufmerksam_geworden1').checked == true) {
			document.getElementById('aufmerksam_suchmaschine').disabled = false;
			document.getElementById('aufmerksam_suchmaschine').focus();
			document.getElementById('aufmerksam_uni').disabled = true;
			document.getElementById('aufmerksam_andere').disabled = true;
			document.getElementById('aufmerksam_uni').value = '';
			document.getElementById('aufmerksam_andere').value = '';
		} else if (document.getElementById('aufmerksam_geworden2').checked == true) {
			document.getElementById('aufmerksam_suchmaschine').disabled = true;
			document.getElementById('aufmerksam_uni').disabled = false;
			document.getElementById('aufmerksam_uni').focus();
			document.getElementById('aufmerksam_andere').disabled = true;
			document.getElementById('aufmerksam_suchmaschine').value = '';
			document.getElementById('aufmerksam_andere').value = '';
		} else if (document.getElementById('aufmerksam_geworden3').checked == true) {
			document.getElementById('aufmerksam_suchmaschine').disabled = true;
			document.getElementById('aufmerksam_uni').disabled = true;
			document.getElementById('aufmerksam_andere').disabled = false;
			document.getElementById('aufmerksam_andere').focus();
			document.getElementById('aufmerksam_suchmaschine').value = '';
			document.getElementById('aufmerksam_uni').value = '';
		} else {
			document.getElementById('aufmerksam_suchmaschine').disabled = true;
			document.getElementById('aufmerksam_uni').disabled = true;
			document.getElementById('aufmerksam_andere').disabled = true;
			document.getElementById('aufmerksam_suchmaschine').value = '';
			document.getElementById('aufmerksam_uni').value = '';
			document.getElementById('aufmerksam_andere').value = '';
		}
	}
</script>

<form action="/sprachreisen/buchung6/" method="post" name="sprachreisenFormSechs" id="sprachreisen_buchung">
	<h4>Schritt 6 von 6: In eigener Sache</h4>
	<fieldset>
		<p>
			<label for="aufmerksam_geworden1" class="komplette_breite<?php echo (isset($_POST['save']) && !isset($_POST['aufmerksam_geworden'])) ? ' error' : '' ?>">Wie haben Sie von unseren Sprachreisen erfahren?:</label>
			<span class="aufmerksam">
				<label for="aufmerksam_geworden1" class="komplette_breite<?php echo (isset($_POST['save']) && $_POST['aufmerksam_geworden'] == '1' && $suchmaschine == false) ? ' error' : '' ?>"><input type="radio" id="aufmerksam_geworden1" class="radio" name="aufmerksam_geworden" onchange="showTextField();" value="1"<?php echo (isset($_POST['save']) && $_POST['aufmerksam_geworden'] == '1') ? ' checked="checked"' : '' ?> /> Website / Suchmaschine: <input type="text" id="aufmerksam_suchmaschine" name="aufmerksam_suchmaschine" class="html_controlled" value="<?php echo (isset($_POST['save']) && $_POST['aufmerksam_geworden'] == '1' && !empty($_POST['aufmerksam_suchmaschine'])) ? $_POST['aufmerksam_suchmaschine'] : '' ?>"<?php echo (isset($_POST['save']) && $_POST['aufmerksam_geworden'] == '1') ? '' : ' disabled="disabled"' ?> /></label>
				<label for="aufmerksam_geworden2" class="komplette_breite<?php echo (isset($_POST['save']) && $_POST['aufmerksam_geworden'] == '2' && $uni == false) ? ' error' : '' ?>"><input type="radio" id="aufmerksam_geworden2" class="radio" name="aufmerksam_geworden" onchange="showTextField();" value="2"<?php echo (isset($_POST['save']) && $_POST['aufmerksam_geworden'] == '2') ? ' checked="checked"' : '' ?> /> Universit&auml;t / Schule: <input type="text" id="aufmerksam_uni" name="aufmerksam_uni" class="html_controlled" value="<?php echo (isset($_POST['save']) && $_POST['aufmerksam_geworden'] == '2' && !empty($_POST['aufmerksam_uni'])) ? $_POST['aufmerksam_uni'] : '' ?>"<?php echo (isset($_POST['save']) && $_POST['aufmerksam_geworden'] == '2') ? '' : ' disabled="disabled"' ?> /></label>
				<label for="aufmerksam_geworden3" class="komplette_breite<?php echo (isset($_POST['save']) && $_POST['aufmerksam_geworden'] == '3' && $andere == false) ? ' error' : '' ?>"><input type="radio" id="aufmerksam_geworden3" class="radio" name="aufmerksam_geworden" onchange="showTextField();" value="3"<?php echo (isset($_POST['save']) && $_POST['aufmerksam_geworden'] == '3') ? ' checked="checked"' : '' ?> /> Andere: <input type="text" id="aufmerksam_andere" name="aufmerksam_andere" class="html_controlled" value="<?php echo (isset($_POST['save']) && $_POST['aufmerksam_geworden'] == '3' && !empty($_POST['aufmerksam_andere'])) ? $_POST['aufmerksam_andere'] : '' ?>"<?php echo (isset($_POST['save']) && $_POST['aufmerksam_geworden'] == '3') ? '' : ' disabled="disabled"' ?> /></label>
				<label for="aufmerksam_geworden4" class="komplette_breite"><input type="radio" id="aufmerksam_geworden4" name="aufmerksam_geworden" class="radio" value="4"<?php echo (isset($_POST['save']) && $_POST['aufmerksam_geworden'] == '4') ? ' checked="checked"' : '' ?> /> Katalog</label>
				<label for="aufmerksam_geworden5" class="komplette_breite"><input type="radio" id="aufmerksam_geworden5" name="aufmerksam_geworden" class="radio" value="5"<?php echo (isset($_POST['save']) && $_POST['aufmerksam_geworden'] == '5') ? ' checked="checked"' : '' ?> /> Anzeigen</label>
				<label for="aufmerksam_geworden6" class="komplette_breite"><input type="radio" id="aufmerksam_geworden6" name="aufmerksam_geworden" class="radio" value="6"<?php echo (isset($_POST['save']) && $_POST['aufmerksam_geworden'] == '6') ? ' checked="checked"' : '' ?> /> Empfehlung</label>
			</span>
		</p>
		<p>
			<label for="bemerkungen" class="bemerkungen">Bemerkungen und W&uuml;nsche:</label>
			<textarea id="bemerkungen" name="bemerkungen" cols="5" rows="5"><?php echo (isset($_POST['save']) && !empty($_POST['bemerkungen'])) ? $_POST['bemerkungen'] : ''; ?></textarea>
		</p>
		<p class="checkboxes">
			<label class="komplette_breite<?php echo (isset($_POST['save']) && !isset($_POST['reisebedingung'])) ? ' error' : ''; ?>" for="reisebedingung"><input type="checkbox" id="reisebedingung" name="reisebedingung" value="true"<?php echo (isset($_POST['save']) && isset($_POST['reisebedingung'])) ? ' checked="checked"' : ''; ?> /> Ich habe die <a href="/cms/reisebedingungen.1543.0.html" target="_blank">Allgemeinen Reisebedingungen der PRAKTIKA GmbH</a> zur Kenntnis genommen und bin damit einverstanden. Mit der Buchungsbest&auml;tigung erhalten Sie ihren Reisesicherungsschein, der Ihren Reisepreis absichert.</label>
		</p>
	</fieldset>
	<fieldset class="control_panel">
		<p>
			<a href="/sprachreisen/buchung5/" class="button alternative small">zur&uuml;ck</a>
			<input type="submit" class="button small button2" name="save" value="Buchung abschlie&szlig;en" />
		</p>
	</fieldset>
</form>

<?php

$_SESSION['sidebar'] = '';
bodyoff();

?>