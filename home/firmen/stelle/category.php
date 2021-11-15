<?php

require("/home/praktika/etc/config.inc.php");

if (!isset($_SESSION['s_loggedin']) || $_SESSION['s_loggedin'] == false) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/login');
	exit();
}

if (!isset($_SESSION['jobAd']['stellenid'])) {
	header('Location: /firmen/angeboteliste/');
	exit();
}

//wenn content.php vorueber, dann hat selbst eine neue Stelle bereits den Bearbeitenstatus
unset($_SESSION['neue_stelle']);

$_SESSION['sidebar'] = 'stelle';
$_SESSION['restricted'] = RESTRICTED_COMPANY;
$_SESSION['current_user_group'] = USER_GROUP_COMPANIES;
$_SESSION['current_page'] = PAGE_PUBLISH_JOBS;

$err = array();

for ($i = 0; $i <= 13; $i++) {
	$err[] = false;
}

$checked = false;

// Validierung nach Submit
if (isset($_POST['save_category'])) {
	if (empty($_POST['stichwort']) || preg_match('/^z. B./', $_POST['stichwort'])) {
		$err[0] = true;
	}
	
	if (empty($_POST['qualifikation'])) {
		$err[1] = true;
	}
	
	if ($_POST['spalte'] == 1) {
		if (!isset($_POST['schuelerprakt']) && !isset($_POST['hochschulprakt']) && !isset($_POST['wprakt']) && !isset($_POST['sonstprakt'])) {
			$err[2] = true;
		}
		
		$_POST['schuelerprakt'] = isset($_POST['schuelerprakt']) ? $_POST['schuelerprakt'] : 'false';
		$_POST['hochschulprakt'] = isset($_POST['hochschulprakt']) ? $_POST['hochschulprakt'] : 'false';
		$_POST['wprakt'] = isset($_POST['wprakt']) ? $_POST['wprakt'] : 'false';
		$_POST['sonstprakt'] = isset($_POST['sonstprakt']) ? $_POST['sonstprakt'] : 'false';
	}
	
	if ($_POST['spalte'] == 1 || $_POST['spalte'] == 2) {
		if (empty($_POST['studienrichtung']) && $_POST['studienrichtung'] !== '0') {
			$err[3] = true;
		}
	}
	
	if (isset($_POST['branche']) && empty($_POST['branche'])) {
		$err[4] = true;
	}
	
	if (isset($_POST['berufsfeld']) && empty($_POST['berufsfeld'])) {
		$err[5] = true;
	}
	
	if ($_POST['spalte'] == 5) {
		if (empty($_POST['beschaeftigung'])) {
			$err[6] = true;
		}
		
		if (empty($_POST['position'])) {
			$err[7] = true;
		}
	}
	
	if ($_POST['spalte'] != 2) {
		if (empty($_POST['von_monat']) || empty($_POST['von_jahr'])) {
			$err[8] = true;
		}
				
		if ($_POST['von_jahr'] < date('Y') || ($_POST['von_jahr'] == date('Y') && $_POST['von_monat'] < date('m'))) {
			$err[8] = true;
		}
		
		if ($_SESSION['s_firmenid'] == 6896) {
			$err[8] = false;
		}
	}

	if ($_POST['spalte'] != 3 && $_POST['spalte'] != 4 && $_POST['spalte'] != 5) {
		if (empty($_POST['zeitraum'])) {
			$err[9] = true;
		}
	}
	
	if (empty($_POST['land'])) {
		$err[10] = true;
	}

	if (empty($_POST['einsatzort'])) {
		$err[13] = true;
	}

	$checked = true;

	for ($i = 0; $i < count($err); $i++) {
		if ($err[$i]) {
			$checked = false;
		}
	}
} else {
	$sql = 'SELECT * FROM '.$database_db.'.stellen WHERE id = '.intval($_SESSION['jobAd']['stellenid']);
	$result = $hDB->query($sql, $praktdbslave);
	$row = mysql_fetch_assoc($result);

	foreach ($row as $key => $value) {
		$_POST[$key] = $value;
	}

	$sql = 'SELECT land, bundesland, grossraum, ort AS einsatzort, branche FROM '.$database_db.'.firmen WHERE id = '.intval($_SESSION['s_firmenid']);

	$result = $hDB->query( $sql, $praktdbslave);
	$row = mysql_fetch_assoc($result);

	foreach ($row as $key => $value) {
		if (!isset($_POST[$key])) {
			$_POST[$key] = $value;
		}
	}
}

//echo '<pre>'.var_dump($_POST).'</pre>';
// Wenn Seite betreten wird, oder Seite mit Fehlern abgeschlossen wird
if ($checked == false) {
	pageheader(array('page_title' => 'Stellenart und Kategorie'));
	
	Praktika_Static::__('job_ad');

?>

<div id="stellenCategory">
	<form action="<? echo 'http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '').'://'.$_SERVER['HTTP_HOST'].'/firmen/stelle/'.$_SESSION['jobAd']['stellenid'].'/category.html'; ?>" method="post" name="category">
		<div id="categoryContent">
		 	<h4 id="category">Kategorie - <span id="type"></span></h4>
<?php
if ($checked == false && isset($_POST['save_category'])) {
	echo '<p class="hint error">Bitte f&uuml;llen Sie die rot markierten Felder aus. Achten Sie darauf, dass diese Angaben wichtig f&uuml;r eine gute Platzierung in den Suchergebnissen sind.</p>'."\n";
}
?>
			<p class="hint info">Die folgenden Angaben dienen der Stellensuche durch den Bewerber und erscheinen <strong>nicht</strong> in der Anzeige selbst. Vollst&auml;ndige Angaben f&uuml;hren zu besseren Suchergebnissen.</p>
			
			<fieldset>
		<div id="chooseCategory" style="margin-bottom:15px;height:95px;">
			<span style="line-height:25px;" class="categoryTypeButton">W&auml;hlen Sie die Kategorie Ihrer Stellenanzeige:</span>
			<p style="text-align:left;">
				<label style="margin:5px;line-height:20px;display:inline-block;width:130px;text-align:left;" for="praktikum"><input type="radio" id="praktikum" name="spalte" value="1" onclick="show(1)"<?= !isset($_POST['save_category']) ? ' checked="checked"' : ($_POST['spalte'] == '1' ? ' checked="checked"' : ''); ?> />&nbsp;Praktikum</label>&nbsp;&nbsp;
				<label style="margin:5px;line-height:20px;display:inline-block;width:220px;text-align:left;" for="bachelor"><input type="radio" id="bachelor" name="spalte" value="2" onclick="show(2)"<?= (isset($_POST['spalte']) && $_POST['spalte'] == '2') ? ' checked="checked"' : ''; ?> />&nbsp;Bachelor-/ Masterarbeit</label>&nbsp;&nbsp;
				<label style="margin:5px;line-height:20px;display:inline-block;width:130px;text-align:left;" for="nebenjob"><input type="radio" id="nebenjob" name="spalte" value="3" onclick="show(3)"<?= (isset($_POST['spalte']) && $_POST['spalte'] == '3') ? ' checked="checked"' : ''; ?> />&nbsp;Nebenjob</label>&nbsp;&nbsp;
				<label style="margin:5px;line-height:20px;display:inline-block;width:130px;text-align:left;" for="ausbildung"><input type="radio" id="ausbildung" name="spalte" value="4" onclick="show(4)"<?= (isset($_POST['spalte']) && $_POST['spalte'] == '4') ? ' checked="checked"' : ''; ?> />&nbsp;Ausbildung</label>
				<label style="margin:5px;line-height:20px;clear:both;display:inline-block;width:130px;text-align:left;"  for="einsteiger"><input type="radio" id="einsteiger" name="spalte" value="5" onclick="show(5)"<?= (isset($_POST['spalte']) && $_POST['spalte'] == '5') ? ' checked="checked"' : ''; ?> />&nbsp;Berufseinstieg</label>
				<label style="margin:5px;line-height:20px;display:inline-block;width:220px;text-align:left;" for="projekt"><input type="radio" id="projekt" name="spalte" value="6" onclick="show(6)"<?= (isset($_POST['spalte']) && $_POST['spalte'] == '6') ? ' checked="checked"' : ''; ?> />&nbsp;Projekt</label>&nbsp;&nbsp;
				<label style="margin:5px;line-height:20px;display:inline-block;width:130px;text-align:left;" for="trainee"><input type="radio" id="trainee" name="spalte" value="7" onclick="show(7)"<?= (isset($_POST['spalte']) && $_POST['spalte'] == '7') ? ' checked="checked"' : ''; ?> />&nbsp;Trainee</label>&nbsp;&nbsp;
			</p>
		</div>
				<p id="qualification" class="hide">
					<label for="qualifikation" <?php echo $err[1] ? ' class="error" ' : '' ?>>Mindestqualifikation:</label>
					<select id="qualifikation" name="qualifikation">
						<option value="">- - - - BITTE AUSW&Auml;HLEN - - - -</option>

<?php
	$result = mysql_db_query($database_db, 'SELECT qualifikation, id FROM qualifikation ORDER BY folge', $praktdbslave);
	$num_rows = ($result != false) ? mysql_num_rows($result) : 0;

	$i = 0;
	
	while ($i < $num_rows) {
		$html = htmlspecialchars(mysql_result($result, $i, 'qualifikation'));
		
		echo '						<option'.((isset($_POST['qualifikation']) && (mysql_result($result, $i, 'id') == $_POST['qualifikation'])) ? ' selected="selected"' : '').' value="'.mysql_result($result, $i, 'id').'">'.$html.'</option>'."\n";

		$i++;
	}
?>

					</select>
				</p>
				<p id="internship" class="hide">
					<label for="schuelerprakt" class="checkbox_labeltext<?php echo $err[2] ? ' error' : '' ?>">Art des Praktikums:</label>
					<span class="checklist">
						<label for="schuelerprakt" class="komplette_breite"><input type="checkbox" id="schuelerprakt" name="schuelerprakt" value="true"<?php echo ( isset($_POST['schuelerprakt']) && $_POST['schuelerprakt'] == 'true' ) ? ' checked="checked"' : '' ?> /> Sch&uuml;lerpraktikum</label>
						<label for="hochschulprakt" class="komplette_breite"><input type="checkbox" id="hochschulprakt" name="hochschulprakt" value="true"<?php echo ( isset($_POST['hochschulprakt']) && $_POST['hochschulprakt'] == 'true' ) ? ' checked="checked"' : '' ?> /> Hochschulpraktikum</label>
						<label for="wprakt" class="komplette_breite"><input type="checkbox" id="wprakt" name="wprakt" value="true"<?php echo ( isset($_POST['wprakt']) && $_POST['wprakt'] == 'true' ) ? ' checked="checked"' : '' ?> /> Weiterbildungspraktikum</label>
						<label for="sonstprakt" class="komplette_breite"><input type="checkbox" id="sonstprakt" name="sonstprakt" value="true"<?php echo ( isset($_POST['sonstprakt']) && $_POST['sonstprakt'] == 'true' ) ? ' checked="checked"' : '' ?> /> sonstiges Praktikum</label>
					</span>
				</p>
				<p id="study" class="hide">
					<label for="studienrichtung"<?php echo $err[3] ? ' class="error"' : '' ?>>Studienrichtung:</label>
					<select id="studienrichtung" name="studienrichtung">
						<option value="">- - - - BITTE AUSW&Auml;HLEN - - - -</option>

<?php
	$result = mysql_db_query($database_db, 'SELECT * FROM studienrichtungen ORDER BY id', $praktdbslave);
	$num_rows = ($result != false) ? mysql_num_rows($result) : 0;
		
	$i = 0;
	
	while ($i < $num_rows) {
		$html = htmlspecialchars(mysql_result($result, $i, 'german'));

		echo '						<option'.((isset($_POST['studienrichtung']) && (mysql_result($result, $i, 'id') == $_POST['studienrichtung'])) ? ' selected="selected"' : '').' value="'.mysql_result($result, $i, 'id').'">'.$html.'</option>'."\n";

		$i++;
	}
?>

					</select>
				</p>
				<p id="sector" class="hide">
					<label for="branche"<?php echo $err[4] ? ' class="error"' : ''; ?>>Branche:</label>
					<select id="branche" name="branche">
						<option value="">- - - - BITTE AUSW&Auml;HLEN - - - -</option>

<?php
	$result = mysql_db_query($database_db, 'SELECT * FROM branchen WHERE '.$_SESSION['s_sprache'].' <> \'\' ORDER BY '.$_SESSION['s_sprache'], $praktdbslave);
	$num_rows = ($result != false) ? mysql_num_rows($result) : 0;
	
	$i = 0;

	while ($i < $num_rows) {
		$html = htmlspecialchars(mysql_result($result, $i, $_SESSION['s_sprache']));

		echo '						<option'.((isset($_POST['branche']) && (mysql_result($result, $i, 'id') == $_POST['branche'])) ? ' selected="selected"' : '').' value="'.mysql_result($result, $i, 'id').'">'.$html.'</option>'."\n";

		$i++;
	}
?>

					</select>
				</p>
				<p id="field" class="hide">
					<label for="berufsfeld"<?php echo $err[5] ? ' class="error"' : '' ?>>Berufsfeld / Bereich:</label>
					<select id="berufsfeld" name="berufsfeld">
						<option value="">- - - - BITTE AUSW&Auml;HLEN - - - -</option>

<?php
	$result = mysql_db_query($database_db, 'SELECT * FROM berufsfelder WHERE '.$_SESSION['s_sprache'].' <> \'\' ORDER BY '.$_SESSION['s_sprache'], $praktdbslave);
	$num_rows = ($result != false) ? mysql_num_rows($result) : 0;
	
	$i = 0;

	while ($i < $num_rows) {
		$html = htmlspecialchars(mysql_result($result, $i, $_SESSION['s_sprache']));
		
		echo '						<option'.((isset($_POST['berufsfeld']) && (mysql_result($result, $i, 'id') == $_POST['berufsfeld'])) ? ' selected="selected"' : '').' value="'.mysql_result($result, $i, 'id').'">'.$html.'</option>'."\n";

		$i++;
	}
?>

					</select>
				</p>
				<div id="job" class="hide">
					<p>
						<label for="beschaeftigung"<?php echo $err[6] ? ' class="error"' : '' ?>>Art der Anstellung:</label>
						<select id="beschaeftigung" name="beschaeftigung">
							<option value="">- - - - BITTE AUSW&Auml;HLEN - - - -</option>

<?php
	$beschaeftigungArray = array(
		'Trainee',
		'Einstiegspostion',
		'Position mit Berufserfahrung',
		'Freier Mitarbeiter'
	);
	
	foreach ($beschaeftigungArray as $key => $value) {
		echo '							<option value="'.$value.'"'.((isset($_POST['beschaeftigung']) && $value == $_POST['beschaeftigung']) ? ' selected="selected"' : '').'>'.$value.'</option>'."\n";
	}
?>

						</select>
					</p>
					<p>
						<label for="position"<?php echo $err[7] ? ' class="error"' : '' ?>>Position:</label>
						<select id="position" name="position">
							<option value="">- - - - BITTE AUSW&Auml;HLEN - - - -</option>

<?php
	$position = mysql_db_query($database_db, 'SELECT '.$_SESSION['s_sprache'].',id FROM berufseinstieg_position ORDER BY id', $praktdbslave);
	
	if ($position != false) {
		$anz_position = mysql_num_rows($position);
	} else {
		$anz_position = 0;
	}
	
	$i = 0;
	
	while ($i < $anz_position) {
		$html = htmlspecialchars(mysql_result($position, $i, $_SESSION['s_sprache']));
		
		echo '							<option'.((isset($_POST['position']) && mysql_result($position,$i,'id') == $_POST['position']) ? ' selected="selected"' : '').' value="'.mysql_result($position,$i,'id').'">'.$html.'</option>'."\n";
	
		$i++;
	}
?>

						</select>
					</p>
				</div>
				<p id="date" class="hide">
					<label for="datum"<?php echo $err[8] ? ' class="error"' : '' ?>>Beginn der T&auml;tigkeit:</label>
					<select class="html_controlled" id="datum" name="von_monat">

<?php
	for ($i = 1; $i <= 12; $i++) {
		echo '						<option'.(((!isset($_POST['von_monat']) && $i == date('m')) || (isset($_POST['von_monat']) && $i == $_POST['von_monat'])) ? ' selected="selected"': '').' value="'.sprintf('%02d', $i).'">'.sprintf('%02d', $i).'</option>'."\n";
	}
?>

					</select> / 
					<select class="html_controlled" name="von_jahr">

<?php
	for ($i = date('Y'); $i <= date('Y') + 3; $i++) {
		echo '						<option'.(((!isset($_POST['von_jahr']) && $i == date('Y')) || (isset($_POST['von_jahr']) && $i == $_POST['von_jahr'])) ? ' selected="selected"': '').' value="'.sprintf('%4d', $i).'">'.sprintf('%4d', $i).'</option>'."\n";
	}
?>

					</select><br />
				</p>

				<p id="duration" class="hide">
					<label for="zeitraum"<?php echo $err[9] ? ' class="error"' : '' ?>>Dauer der T&auml;tigkeit:</label>
					<select id="zeitraum" name="zeitraum">
						<option value="">- - - - BITTE AUSW&Auml;HLEN - - - -</option>

<?php
	for ($i = 1; $i <= 24; $i++) {
		echo '						<option'.((isset($_POST['zeitraum']) && $i == $_POST['zeitraum']) ? ' selected="selected"': '').' value="'.$i.'">'.$i.' Monat'.($i > 1 ? 'e': '').'</option>'."\n";
	}
?>

					</select>
				</p>
				<p id="keywords">
					<label for="stichwort"<?php echo $err[0] ? ' class="error"' : '' ?>>Stichworte f&uuml;r Suche:</label>
					<textarea cols="25" rows="5" id="stichwort" name="stichwort" onclick="if(this.value.search(/^z. B./) != -1) this.value = '';"><?php echo (isset($_POST['stichwort']) ? $_POST['stichwort'] : 'z. B. Deutsch, Englisch, Web-Programmierer, MySQL, PHP, ...'); ?></textarea>
				</p>
				<div id="location" class="hide">
					<p>
						<label <?php echo $err[10] ? 'class="error" ' : '' ?>for="land">Land:</label>
						<select id="land" name="land">
							<option value="">- - - - BITTE AUSW&Auml;HLEN - - - -</option>

<?php
	$result = mysql_db_query($database_db, 'SELECT '.$_SESSION['s_sprache'].', id FROM land ORDER BY '.$_SESSION['s_sprache'], $praktdbslave);
	$num_rows = $result != false ? mysql_num_rows($result) : 0;
	$landid = (isset($_POST['land']) && !empty($_POST['land'])) ? intval($_POST['land']) : 68;
	
	$i = 0;	
	
	while ($i < $num_rows) {
		$html = htmlspecialchars(mysql_result($result, $i, $_SESSION['s_sprache']));
		
		echo '						<option'.((mysql_result($result, $i, 'id') == $landid) ? ' selected="selected"' : '').' value="'.mysql_result($result,$i,'id').'">'.$html.'</option>'."\n";

		$i++;
	}
?>

						</select>
					</p>
					<p>
						<label for="bundesland"<?php echo $err[11] ? ' class="error"' : '' ?>>Bundesland:</label>
						<select id="bundesland" name="bundesland">
							<option value="">- - - - BITTE AUSW&Auml;HLEN - - - -</option>

<?php
	$bundeslandselect = mysql_db_query($database_db, 'SELECT * FROM bundesland WHERE landid = '.$landid.' ORDER BY '.$_SESSION['s_sprache'], $praktdbslave);

	if ($bundeslandselect == true) {
		$anz_bundesland = mysql_num_rows($bundeslandselect);
	}
	
	if (isset($anz_bundesland) && $anz_bundesland > 0) {
		$bundeslandsel = mysql_result($bundeslandselect, 0, 'id');

		if (!isset($_POST['bundesland'])) {
			$_POST['bundesland'] = $bundeslandsel;
		}

		$grossraumselect = mysql_db_query($database_db, 'SELECT grossraum, id FROM grossraum WHERE bundesland = '.intval($_POST['bundesland']).' ORDER BY grossraum', $praktdbslave);
		$anz_grossraum = mysql_num_rows($grossraumselect);
		
		if (!isset($_POST['grossraum'])) {
			$_POST['grossraum'] = mysql_result($grossraumselect, 0, 'id');
		}
	}
	
	if ($landid == 68) {
		echo '							<option'.($_POST['bundesland'] == '2030' ? ' selected="selected"' : '').' value="2030">Bundesweit</option>';
	}
	
	$i = 0;
	
	while ($i < $anz_bundesland) {
		$html = htmlspecialchars(mysql_result($bundeslandselect, $i, $_SESSION['s_sprache']));
		
		echo '							<option'.((mysql_result($bundeslandselect, $i, 'id') == $_POST['bundesland']) ? ' selected="selected"' : '').' value="'.mysql_result($bundeslandselect, $i, 'id').'">'.$html.'</option>'."\n";

		$i++;
	}
?>

						</select>
					</p>
					<p>
						<label for="grossraum"<?php echo $err[12] ? ' class="error"' : '' ?>>Gro&szlig;raum:</label>
						<select id="grossraum" name="grossraum">
							<option value="">- - - - BITTE AUSW&Auml;HLEN - - - -</option>
	
<?php
	$i = 0;
	
	while ($i < $anz_grossraum) {
		$html = htmlspecialchars(mysql_result($grossraumselect, $i));
		echo '							<option'.((mysql_result($grossraumselect,$i,'id') == $_POST['grossraum']) ? ' selected="selected"' : '').' value="'.mysql_result($grossraumselect, $i, 'id').'">'.$html.'</option>'."\n";

		$i++;
	}
?>

						</select>
					</p>
					<p>
						<label for="einsatzort"<?php echo $err[13] ? ' class="error"' : '' ?>>Ort:</label>
						<input type="text" id="einsatzort" maxlength="50" name="einsatzort" value="<?php echo (isset($_POST['einsatzort']) ? $_POST['einsatzort'] : '') ?>" />
					</p>
				</div>
			</fieldset>
			<fieldset class="control_panel">
				<p>
					<input type="hidden" name="lat" id='lat' value="<?php echo $_POST['lat']; ?>" />
					<input type="hidden" name="lng" id='lng' value="<?php echo $_POST['lng']; ?>" />
					<input type="submit" id="save_category" name="save_category" value="WEITER" class="button red" />      
				</p>
			</fieldset>
		</div>	
	</form>
</div>

<script type="text/javascript" src="/scripts/smallbox.js"></script>
<script type="text/javascript">
	var img = '', path = '/gifs/bcenter/', tmp = '';
	var oldVal = -1;
	var types;

	// Anzeige bestimmter Felder in Abhaengigkeit der gew�hlten Stellenart
	function show (id) {
		if (oldVal != -1) {
			switch (oldVal) {
				case <?php echo SECTION_PRAKTIKUM; ?>: // Praktikum
					tmp = 'praktikum';
					types = 'Praktikum';
					break;
				case <?php echo SECTION_DIPLOM; ?>: // Bachelor- / Masterarbeit
					tmp = 'bachelor_master';
					types = 'Bachelor- / Masterarbeit';
					break;
				case <?php echo SECTION_NEBENJOB; ?>:
					tmp = 'nebenjob';
					types = 'Nebenjob';
					break;
				case <?php echo SECTION_AUSBILDUNG; ?>:
					tmp = 'ausbildung';
					types = 'Ausbildung';
					break;
				case <?php echo SECTION_BERUFSEINSTIEG; ?>:
					tmp = 'berufseinstieg';
					types = 'Berufseinstieg';
					break;
				case <?php echo SECTION_PROJEKT; ?>:
					tmp = 'projekt';
					types = 'Projekt';
					break;
				case <?php echo SECTION_TRAINEE; ?>:
					tmp = 'trainee';
					types = 'Trainee';
					break;
			}
			
			//document.getElementById('categoryIcon_' + oldVal).src = path + tmp + '.gif';
			oldVal = -1;
		}
		
		//document.getElementById('categoryIcon_' + id).src = path + 'stellenart_ausgewaehlt.gif';
		oldVal = id;
		
		// H4 mit Text
		document.getElementById('category').className = 'show';
		// Stichworte
		document.getElementById('keywords').className = 'show';
		// Mindestqualifikation
		document.getElementById('qualification').className = 'show';
		// Art des Praktikums
		document.getElementById('internship').className = 'hide';
		// Studienrichtung
		document.getElementById('study').className = 'hide';
		// Branche
		document.getElementById('sector').className = 'show';
		// Berufsfeld
		document.getElementById('field').className = 'show';
		// 
		document.getElementById('job').className = 'hide';
		// Datum
		document.getElementById('date').className = 'show';
		// Dauer
		document.getElementById('duration').className = 'show';
		// Angaben zum Einsatzort
		document.getElementById('location').className = 'show';
		// Weiter-Button
		document.getElementById('save_category').className = 'button red show';
		
		switch (id) {
			case <?php echo SECTION_PRAKTIKUM; ?>: // Praktikum
				document.getElementById('internship').className = 'show'; // nur hier
				document.getElementById('study').className = 'show';
				tmp = 'praktikum';
				types = 'Praktikum';
				typeID = 1;
				break;
			case <?php echo SECTION_DIPLOM; ?>: // Bachelor- / Masterarbeit
				document.getElementById('study').className = 'show';
				document.getElementById('date').className = 'hide'; // nur hier
				tmp = 'bachelor';
				types = 'Bachelor- / Masterarbeit';
				typeID = 2;
				break;
			case <?php echo SECTION_NEBENJOB; ?>:
				tmp = 'nebenjob';
				types = 'Nebenjob';
				typeID = 3;
				break;
			case <?php echo SECTION_AUSBILDUNG; ?>:
				document.getElementById('duration').className = 'hide';
				tmp = 'ausbildung';
				types = 'Ausbildung';
				typeID = 4;
				break;
			case <?php echo SECTION_BERUFSEINSTIEG; ?>:
				document.getElementById('job').className = 'show'; // nur hier
				document.getElementById('duration').className = 'hide';
				tmp = 'berufseinstieg';
				types = 'Berufseinstieg';
				typeID = 5;
				break;
			case <?php echo SECTION_PROJEKT; ?>:
				tmp = 'projekt';
				types = 'Projekt';
				typeID = 6;
				break;
			case <?php echo SECTION_TRAINEE; ?>:
				tmp = 'trainee';
				types = 'Trainee';
				typeID = 7;
				break;
		}
		
		// Stellenart
		document.getElementById('type').innerHTML = types;
	}

	function out() {
		if (img == '') {
			img = 'praktikum_inaktiv.gif';
		}
		
		document.getElementById('img').src = path + img;
	}

	// Vorschau auf die Bilder
	function preview (type) {
		switch (type) {
			case 1:
				tmp = 'praktikum';
				break;
			case 2:
				tmp = 'bachelor';
				break;
			case 3:
				tmp = 'nebenjob';
				break;
			case 4:
				tmp = 'ausbildung';
				break;
			case 5:
				tmp = 'berufseinstieg';
				break;
			case 6:
				tmp = 'projekt';
				break;
			case 7:
				tmp = 'trainee';
				break;
			default:
				tmp = 'praktikum';
				break;
		}
		
		document.getElementById('img').src = path + tmp + '_inaktiv.gif';
	}
</script>

<?php
	if (isset($_POST['spalte'])) {
?>

<script type="text/javascript">
<!--
	show(<?php echo $_POST['spalte']; ?>);
-->
</script>

<?php
	}
} else {
	$sqlString = 'UPDATE stellen SET ';
	
	$sqlString .= 'bearbeiterid = '.intval($_SESSION['s_loginid']).', ';
	$sqlString .= 'firmenid = '.intval($_SESSION['s_firmenid']).', ';
	$sqlString .= 'sprache = '.intval($_SESSION['s_sprachid']).', ';
	
	foreach ($_POST as $key => $value) {
		if (substr_count($value,'@') > 0 || substr_count($value,'(at)') > 0 || substr_count($value,'www.') > 0) {
			$deny++;
		}
	
		if ($key != 'save_category') {
			if ($key != 'stellenid') {
				$sqlString .= $key.' = \''.mysql_real_escape_string($value).'\', ';
			}
		}
	}
	
	$sqlString .= 'host = \''.mysql_real_escape_string($host).'\', ';
	$sqlString .= 'ip = \''.mysql_real_escape_string($ip).'\', ';
	$sqlString .= 'datum_eintrag = NOW()';
	
	$sqlString .= ' WHERE id = '.$_SESSION['jobAd']['stellenid'];

	mysql_db_query($database_db, $sqlString, $praktdbmaster);
        
	$dateiname = '/home/praktika/cache/stellen/stelle_'.$_SESSION['jobAd']['stellenid'].'.html';
	@unlink($dateiname);
	
	$objStelle = new Praktika_Stelle($_SESSION['jobAd']['stellenid']);
	$objStelle->cleanCache();
	
	$_SESSION['jobAd_sidebar']['category'] = true;
	
	header('Location: http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '').'://'.$_SERVER['HTTP_HOST'].'/firmen/stelle/'.$_SESSION['jobAd']['stellenid'].'/activate.html');
	exit();
}

$_SESSION['sidebar'] = '';

bodyoff();
?>