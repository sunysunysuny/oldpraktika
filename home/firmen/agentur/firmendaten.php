<?php
/**
 * Created by JetBrains PhpStorm.
 * User: fackermann
 * Date: 03.01.12
 * Time: 14:36
 * To change this template use File | Settings | File Templates.
 */
require("/home/praktika/etc/config.inc.php");

if (!isset($_SESSION['s_loggedin']) || $_SESSION['s_loggedin'] == false) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/login');
	exit();
}

pageheader(array('page_title' => 'Firmenstammdaten verwalten', 'grid_system' => '6-0'));

if (isset($_POST['save'])) {
	if (!isset($_POST['name']) || !isset($_POST['strasse']) || !isset($_POST['plz']) || !isset($_POST['ort']) || empty($_POST['name']) || empty($_POST['strasse']) || empty($_POST['plz']) || empty($_POST['ort']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		echo '<p class="hint error">Ihre Änderungen wurden nicht übernommen.<br />Die rot markierten Felder müssen korrekt ausgefüllt werden.</p>'."\n";
	} else {
		/* Check ob http:// vor der Homepage steht (falls eine angegeben wurde) */
		if (isset($_POST['webseite']) && !empty($_POST['webseite'])) {
			if (strlen($_POST['webseite']) > 7) {
				$anfang = substr($_POST['webseite'], 0, 7);
			} else {
				$anfang = '';
			}

			if ($anfang != 'http://') {
				$_POST['webseite'] = 'http://' . $_POST['webseite'];
			}
		}

		$sql = 'UPDATE
					' . $database_db . '.firmen_agentur
				SET
					name = \'' . mysql_real_escape_string($_POST['name']) . '\',
					branche = \'' . mysql_real_escape_string($_POST['branche']) . '\',
					strasse = \'' . mysql_real_escape_string($_POST['strasse']) . '\',
					plz = \'' . mysql_real_escape_string($_POST['plz']) . '\',
					ort = \'' . mysql_real_escape_string($_POST['ort']) . '\',
					bundesland = ' . intval($_POST['bundesland']) . ',
					land = ' . intval($_POST['land']) . ',
					grossraum = ' . intval($_POST['grossraum']) . ',
					telefon = \'' . mysql_real_escape_string(isset($_POST['telefon']) ? $_POST['telefon'] : '') . '\',
					fax = \'' . mysql_real_escape_string(isset($_POST['fax']) ? $_POST['fax'] : '') . '\',
					email = \'' . mysql_real_escape_string($_POST['email']) . '\',
					webseite = \'' . mysql_real_escape_string(isset($_POST['webseite']) ? $_POST['webseite'] : '') . '\',
					host = \'' . mysql_real_escape_string($host) . '\',
					ip = \'' . mysql_real_escape_string($ip) . '\',
					modify = NOW()
				WHERE id = ' . intval($_POST['id']);

		mysql_query($sql, $praktdbmaster);

		echo '<p class="hint success">Ihre Änderung wurden erfolgreich übernommen.</p>'."\n";
	}
}

$agentur = new Praktika_Firmen_Agentur($_SESSION['s_firmenid']);

$id = intval($_REQUEST['id']);

$firmenDaten = isset($_POST['save']) ? $_POST : $agentur->findeEinzelnesUnternehmen($id);

$landid = isset($_POST['save']) ? $_POST['land'] : $firmenDaten['land'];

$branchen = Praktika_Listen::getBranche();
$landselect = Praktika_Listen::getLand();
$bundeslandselect = Praktika_Listen::getBundesland($landid);

if (count($bundeslandselect) > 0) {
	$bundeslandsel = $bundeslandselect[0]['id'];

	if (isset($_POST['bundesland']) && empty($_POST['bundesland'])) {
		$_POST['bundesland'] = $bundeslandsel;
	}

	$grossraumselect = Praktika_Listen::getGrossraum(isset($_POST['bundesland']) ? intval($_POST['bundesland']) : htmlspecialchars($firmenDaten['bundesland']));
}
?>
<p><a href="/firmen/angeboteliste_agentur/">&laquo; Zurück zur Stellenübersicht</a></p>
<form action="/firmen/agentur/unternehmensdaten/<?= $id; ?>/" method="post" name="stammdaten">
	<fieldset>
		<p>
			<label for="name"<?= (isset($_POST['save']) && isset($_POST['name']) && empty($_POST['name'])) ? ' class="error"' : ''; ?>>Unternehmensname (*):</label>
			<input type="text" id="name" name="name" maxlength="100" value="<?= htmlspecialchars(stripslashes($firmenDaten['name'])); ?>" />
		</p>
		<p>
			<label for="branche">Branche:</label>
			<select id="branche" name="branche">

<?php for ($a = 0; $a < count($branchen); $a++) : ?>
				<option<?= (htmlspecialchars($firmenDaten['branche']) == $branchen[$a]['id'] ? ' selected="selected"' : ''); ?> value="<?= $branchen[$a]['id']; ?>"><?= $branchen[$a]['title']; ?></option>
<?php endfor; ?>

			</select>
		</p>
		<p>
			<label for="strasse"<?= (isset($_POST['save']) && isset($_POST['strasse']) && empty($_POST['strasse'])) ? ' class="error"' : ''; ?>>Straße (*):</label>
			<input type="text" id="strasse" name="strasse" value="<?= $firmenDaten['strasse']; ?>" />
		</p>
		<p>
			<label for="plz"<?= (isset($_POST['save']) && ((isset($_POST['plz']) && empty($_POST['plz'])) || (isset($_POST['ort']) && empty($_POST['ort'])))) ? ' class="error"' : ''; ?>>PLZ / Ort (*):</label>
			<input type="text" id="plz" name="plz" value="<?= $firmenDaten['plz']; ?>" /><input type="text" id="ort" name="ort" value="<?= $firmenDaten['ort']; ?>" />
		</p>
		<p>
			<label for="land">Land:</label>
			<select id="land" name="land">

<?php for ($a = 0; $a < count($landselect); $a++) : ?>
				<option<?= ($landselect[$a]['id'] == $landid) ? ' selected="selected"' : ''; ?> value="<?= $landselect[$a]['id']; ?>"><?= $landselect[$a]['title']; ?></option>
<?php endfor; ?>

			</select>
		</p>
		<p>
			<label for="bundesland">Bundesland:</label>
			<select id="bundesland" name="bundesland">

<?php for ($a = 0; $a < count($bundeslandselect); $a++) : ?>
				<option <?= ($bundeslandselect[$a]['id'] == htmlspecialchars($firmenDaten['bundesland'])) ? 'selected="selected" ' : ''; ?>value="<?= $bundeslandselect[$a]['id']; ?>"><?= $bundeslandselect[$a]['title']; ?></option>
<?php endfor; ?>

			</select>
		</p>
		<p>
			<label for="grossraum">Gro&szlig;raum:</label>
			<select id="grossraum" name="grossraum">

<?php for ($a = 0; $a < count($grossraumselect); $a++) : ?>
				<option <?= ($grossraumselect[$a]['id'] == htmlspecialchars($firmenDaten['grossraum'])) ? 'selected="selected" ' : ''; ?>value="<?= $grossraumselect[$a]['id']; ?>"><?= $grossraumselect[$a]['title']; ?></option>
<?php endfor; ?>

			</select>
		</p>
		<p>
			<label for="telefon">Telefonnummer:</label>
			<input type="text" id="telefon" name="telefon" maxlength="50" value="<?= htmlspecialchars($firmenDaten['telefon']); ?>" />
		</p>
		<p>
			<label for="fax">Faxnummer:</label>
			<input type="text" id="fax" name="fax" maxlength="50" value="<?= htmlspecialchars($firmenDaten['fax']); ?>" />
		</p>
		<p>
			<label for="email"<?= (isset($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) ? 'class="error"' : ''; ?>>eMail-Adresse (*):</label>
			<input type="text" id="email" name="email" maxlength="50" value="<?= htmlspecialchars($firmenDaten['email']); ?>" />
		</p>
		<p>
			<label for="webseite">Homepage-Adresse:</label>
			<input type="text" id="webseite" name="webseite" maxlength="50" value="<?= htmlspecialchars($firmenDaten['webseite']); ?>" />
		</p>
	</fieldset>
	<fieldset class="control_panel">
		<p>
			<input type="hidden" name="id" value="<?= $id ?>" />
			<input type="submit" name="save" value="Speichern" class="button red" />
		</p>
	</fieldset>
</form>

<p class="hint info">Die mit '(*)'-markierten Felder müssen ausgefüllt werden.</p>

<?php
bodyoff();
?>