<?php
require('/home/praktika/etc/gb_config.inc.php');

if (!isset($_SESSION['s_loggedin']) || $_SESSION['s_loggedin'] == false) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/login');
}

$_SESSION['restricted'] = RESTRICTED_COMPANY;
$_SESSION['current_user_group'] = USER_GROUP_COMPANIES;

pageheader(array('page_title' => 'Video bearbeiten'));

if (isset($_POST['save'])) {
	$error = 0;
	$onlyOne = false;
	$allEmpty = false;
	$javaScript = false;
	
	if (strlen($_POST['flash1']) != 0 && strlen($_POST['flash2']) != 0) {
		$error++;
		$onlyOne = true;
		
		$flash1 = $_POST['flash1'];
		$flash2 = stripslashes($_POST['flash2']);
	} elseif (strlen($_POST['flash1']) == 0 && strlen($_POST['flash2']) == 0) {
		$error++;
		$allEmpty = true;
	} else {
		$flash = strlen($_POST['flash1']) != 0 ? $_POST['flash1'] : $_POST['flash2'];
	}
	
	if (isset($_POST['flash2'])) {
		$flash = preg_replace('/><embed/', '><param name=\"wmode\" value=\"transparent\"></param><embed', $flash);
		$flash = preg_replace('/><\/embed>/', ' wmode=\"transparent\"></embed>', $flash);
	}
	if (strpos($flash, '<script') !== false) {
		$error++;
		$javaScript = true;
	}
	
	if ($error == 0) {
		//INSERT
		$result = mysql_db_query($database_db, 'UPDATE stellen SET flash = \''.mysql_real_escape_string($flash).'\' WHERE firmenid = '.$_SESSION['s_firmenid'].' AND id = '.$_SESSION['jobAd']['stellenid'], $praktdbslave);

		echo '<script type="text/javascript">top.location.reload();</script>';
	}
} elseif (isset($_POST['delete'])) {
	$result = mysql_db_query($database_db, 'UPDATE stellen SET flash = \'\' WHERE firmenid = '.$_SESSION['s_firmenid'].' AND id = '.$_SESSION['jobAd']['stellenid'], $praktdbslave);

	echo '<script type="text/javascript">top.location.reload();</script>';
} else {
	$result = mysql_db_query($database_db, 'SELECT flash FROM stellen WHERE firmenid = '.$_SESSION['s_firmenid'].' AND id = '.$_SESSION['jobAd']['stellenid'], $praktdbslave);

	if (mysql_num_rows($result) > 0) {
		$resultRow = mysql_result($result, 0, 'flash');
		
		if (strpos($resultRow, '<object') !== false) {
			$flash2 = stripslashes($resultRow);
		} else {
			$flash1 = $resultRow;
		}
	} else {
		$flash1 = '';
		$flash2 = '';
	}
}
?>

<p>Bitte geben Sie entweder den Link zum Video an, oder tragen Sie den HTML-Code f&uuml;r das Video ein.</p>
<p class="error">
<?php echo (isset($onlyOne) && $onlyOne == true) ? 'Bitte f&uuml;llen Sie nur eins der beiden Felder aus.' : ''; ?>
<?php echo (isset($allEmpty) && $allEmpty == true) ? 'Bitte f&uuml;llen Sie eins der beiden Felder aus.' : ''; ?>
<?php echo (isset($javaScript) && $javaScript == true) ? 'JavaScript ist nicht erlaubt.' : ''; ?>
</p>

<form action="/firmen/flash/" method="post">
	<fieldset>
		<p>
			<label>Link zum Video:</label>
			<input type="text" name="flash1" value="<?php echo $flash1; ?>" />
		</p>
		<p>
			<label>HTML-Code zum Video:</label>
			<textarea name="flash2" cols="5" rows="5"><?php echo $flash2; ?></textarea>
		</p>
	</fieldset>
	<fieldset class="control_panel">
		<p>
			<button type="submit" name="save" value="speichern"><span><span><span>speichern</span></span></span></button>
		</p>
	</fieldset>
</form>

<?php
if (!empty($flash1) || !empty($flash2)) {
?>

<form action="/firmen/flash/" method="post">
	<fieldset class="control_panel">
		<p>
			<button type="submit" name="delete" value="Video l&ouml;schen"><span><span><span>bisheriges Video l&ouml;schen</span></span></span></button>
		</p>
	</fieldset>
</form>

<?php
}

bodyoff();
?>