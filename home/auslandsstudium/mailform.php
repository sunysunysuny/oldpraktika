<?php
require("/home/praktika/etc/config.inc.php");

$_SESSION['current_page'] = PAGE_ABROAD_STUDIES;
$_SESSION['current_user_group'] = USER_GROUP_CANDIDATES;
$_SESSION['sidebar'] = 'auslandsstudium';

pageheader(array('page_title' => 'Studieren im Ausland '));

if (isset($_POST['send_it'])) {
	$error = 0;
	$emailNotValid = false;
	
	if (!empty($_POST['email']) && preg_match('/^([a-zA-Z0-9\._\+-]+)\@((\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,7}|[0-9]{1,3})(\]?))$/', $_POST['email']) == false) {
		$error++;
		$emailNotValid = true;
	}
	
	if (!isset($_POST['gender']) || empty($_POST['last_name']) || empty($_POST['email']) || empty($_POST['phone'])) {
		$error++;
		echo '<p class="hint error">Das Versenden Ihrer Nachricht ist fehlgeschlagen.<br />Bitte vervollst&auml;ndigen Sie die rot markierten Felder.</p>'."\n";
		echo $emailNotValid == true ? '<p class="error">Die von Ihnen eingegebene eMail-Adresse hat den Test auf Validit&auml;t nicht bestanden. &Uuml;berpr&uuml;fen Sie bitte die korrekte Schreibweise (Bsp.: info@example.com).</p>'."\n" : '';
	} else {
		$subject = $_POST['gender'].' '.(empty($_POST['first_name']) ? '' : $_POST['first_name'].' ').$_POST['last_name'].' interessiert sich für ein Auslandsstudium';

		$sql = mysql_db_query('SELECT * FROM '.$database_programs.'.auslandsstudium WHERE id = '.intval($_POST['university']), $praktdbslave);
	
		if (mysql_num_rows($sql) > 0) {
			$row = mysql_fetch_row($sql);
			$university = ' an "'.strip_tags($row['3']).'".';
		} elseif ($_POST['university'] == '0') {
			$university = ' im Allgemeinen.';
		} elseif ($_POST['university'] == '-1') {
			$university = ', hat im Formular allerdings keine Universität ausgewählt.';
		}
		
		$message = $_POST['gender'].' '.(empty($_POST['first_name']) ? '' : $_POST['first_name'].' ').$_POST['last_name'].' interessiert sich für ein Auslandsstudium'.$university."\n\n";
		
		if (isset($_POST['bachelor']) && $_POST['bachelor'] == 'true') {
			$message .= $_POST['gender'].' '.(empty($_POST['first_name']) ? '' : $_POST['first_name'].' ').$_POST['last_name'].' interessiert sich zusätzlich für "Bachlor im Ausland".'."\n";
		}
		
		if (isset($_POST['master']) && $_POST['master'] == 'true') {
			$message .= $_POST['gender'].' '.(empty($_POST['first_name']) ? '' : $_POST['first_name'].' ').$_POST['last_name'].' interessiert sich außerdem für "Master im Ausland".'."\n";
		}
		
		$message .= "\n".'eMail: '.$_POST['email']."\n";
		$message .= 'Telefon: '.$_POST['phone']."\n\n";
		$message .= 'Mitteilung: '.(empty($_POST['comment']) ? $_POST['gender'].' '.(empty($_POST['first_name']) ? '' : $_POST['first_name'].' ').$_POST['last_name'].' hat keine Mitteilung hinterlassen.' : $_POST['comment']);

		$header = 'From: '.$_POST['gender'].' '.(empty($_POST['first_name']) ? '' : $_POST['first_name'].' ').$_POST['last_name'].' <'.$_POST['email'].'>'."\r\n";
		
		mail('pfleming@gaccca.org',$subject,$message,$header);
		
		$insertQuery = 'INSERT INTO auslandsstudium_interessenten SET ';
		
		foreach ($_POST as $key => $value) {
			if ($key != 'send_it') {
				$insertQuery .= $key.' = \''.mysql_real_escape_string($value).'\', ';
			}
		}
		
		$insertQuery = substr($insertQuery, 0, (strlen($insertQuery) - 2));
		
		$sql = mysql_db_query($database_programs,$insertQuery,$praktdbmaster);
		
		echo '<p class="hint success">Ihre Anfrage wurde erfolgreich versendet.</p>'."\n";
		echo '<p class="control_panel"><button type="button" name="beratung" onclick="location.href = \'/home/auslandsstudium/\';"><span><span><span>zur&uuml;ck</span></span></span></button></p>'."\n";
	}
}

if (!isset($_POST['send_it']) || (isset($_POST['send_it']) && $error != 0)) {
?>
<p>Sie haben Fragen zum Thema studieren im Ausland?<br />Wir senden Ihnen gern Informationen zu.</p>
<form method="post" action="/auslandsstudium/nachricht/" name="studierenauslandForm">
	<fieldset>
		<p>
			<label for="university">Universit&auml;t:</label>
			<select id="university" name="university">
				<option value="-1"<?php echo (isset($_POST['send_it']) && $_POST['university'] == '-1') ? ' selected="selected"' : ''; ?>>Bitte w&auml;hlen ...</option>
				<option value="0"<?php echo (isset($_POST['send_it']) && $_POST['university'] == '0') ? ' selected="selected"' : ''; ?>>Allgemeine Information</option>
<?php
	$sql = mysql_query('SELECT * FROM '.$database_programs.'.auslandsstudium WHERE inactive = \'false\' ORDER BY sort_order',$praktdbslave);
	
	if (mysql_num_rows($sql) > 0) {
		while ($row = mysql_fetch_assoc($sql)) {
			echo '				<option value="'.$row['id'].'"'.((isset($_POST['send_it']) && $_POST['university'] == $row['id']) ? ' selected="selected"' : '').'>'.strip_tags($row['name']).'</option>'."\n";
		}
	}
?>
			</select>
		</p>
		<p class="checkboxes">
			<label for="bachelor" class="first_text">Ich interessiere mich auch f&uuml;r:</label>
			<span>
				<label for="bachelor" class="second_text"><input type="checkbox" id="bachelor" name="bachelor" value="true"<?php echo (isset($_POST['send_it']) && $_POST['bachelor'] == 'true') ? ' checked="checked"' : ''; ?> />Bachelor im Ausland</label>
				<label for="master" class="second_text sub"><input type="checkbox" id="master" name="master" value="true"<?php echo (isset($_POST['send_it']) && $_POST['master'] == 'true') ? ' checked="checked"' : ''; ?> />Master im Ausland</label>
			</span>
		</p>
		<p class="radios">
			<label for="herr" class="first_text<?php echo (isset($_POST['send_it']) && !isset($_POST['gender'])) ? ' error' : ''; ?>">Anrede:<sup>*</sup></label>
			<span>
				<label for="herr"><input type="radio" id="herr" name="gender" value="Herr"<?php echo (isset($_POST['send_it']) && $_POST['gender'] == 'Herr') ? ' selected="selected"' : ''; ?> />Herr</label>
				<label for="frau"><input type="radio" id="frau" name="gender" value="Frau"<?php echo (isset($_POST['send_it']) && $_POST['gender'] == 'Frau') ? ' selected="selected"' : ''; ?> />Frau</label>
			</span>
		</p>
		<p>
			<label for="first_name">Vorname:</label>
			<input id="first_name" name="first_name" maxlength="50" value="<?php echo isset($_POST['send_it']) ? $_POST['first_name'] : ''; ?>" type="text" />
		</p>
		<p>
			<label for="last_name"<?php echo (isset($_POST['send_it']) && empty($_POST['last_name'])) ? ' class="error"' : ''; ?>>Nachname:<sup>*</sup></label>
			<input id="last_name" name="last_name" maxlength="50" value="<?php echo isset($_POST['send_it']) ? $_POST['last_name'] : ''; ?>" type="text" />
		</p>
		<p>
			<label for="email"<?php echo (isset($_POST['send_it']) && empty($_POST['email'])) ? ' class="error"' : ''; ?>>eMail-Adresse:<sup>*</sup></label>
			<input id="email" name="email" maxlength="50" value="<?php echo isset($_POST['send_it']) ? $_POST['email'] : ''; ?>" type="text" />
		</p>
		<p>
			<label for="phone"<?php echo (isset($_POST['send_it']) && empty($_POST['phone'])) ? ' class="error"' : ''; ?>>Telefon:<sup>*</sup></label>
			<input id="phone" name="phone" maxlength="50" value="<?php echo isset($_POST['send_it']) ? $_POST['phone'] : ''; ?>" type="text" />
		</p>
	</fieldset>
	<fieldset>
		<p>
			<label for="comment">Mitteilung:</label>
			<textarea id="comment" name="comment" rows="5" cols="10"><?php echo isset($_POST['send_it']) ? $_POST['comment'] : ''; ?></textarea>
		</p>
	</fieldset>
	<fieldset class="control_panel">
		<p>
			<a href="#" class="button small" onclick="document.studierenauslandForm.submit(); return false;">senden</a>
			<input type="hidden" id="send_it" name="send_it" value="1" />
		</p>
	</fieldset>
</form> 

<?php
}

$_SESSION['sidebar'] = '';
bodyoff();

?>