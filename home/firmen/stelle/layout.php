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

if (isset($_SESSION['jobAdExtern']) && $_SESSION['jobAdExtern'] == true) {
	header('Location: /firmen/stelle/'.$_SESSION['jobAd']['stellenid'].'/category.html');
	exit();
}

if (isset($_REQUEST['cat'])) {
	$_SESSION['jobAd_sidebar']['layout'] = true;
	
	header('Location: /firmen/stelle/'.$_SESSION['jobAd']['stellenid'].'/category.html');
	exit();
}

//wenn content.php vorueber, dann hat selbst eine neue Stelle bereits den Bearbeitenstatus
unset($_SESSION['neue_stelle']);

$_SESSION['sidebar'] = 'stelle';
$_SESSION['restricted'] = RESTRICTED_COMPANY;
$_SESSION['current_user_group'] = USER_GROUP_COMPANIES;
$_SESSION['current_page'] = PAGE_PUBLISH_JOBS;

// Template-Id bestimmen
$sql = 'SELECT link_anzeige_abfr, templateid FROM stellen WHERE id = '.$_SESSION['jobAd']['stellenid'];
$result = mysql_db_query($database_db, $sql, $praktdbslave);
$row = mysql_fetch_assoc($result);

$extern = $row['link_anzeige_abfr'];
$templateid = intval($row['templateid']);

// Art des Templates bestimmen
$sql = 'SELECT isStandard, isVideo, isEigenes FROM stellentemplates WHERE id = '.$templateid;
$result = mysql_db_query($database_db, $sql, $praktdbslave);
$row = mysql_fetch_assoc($result);

if ($row['isStandard'] == 'true') {
	$type = 1;
} elseif ($row['isVideo'] == 'true') {
	$type = 2;
} elseif ($row['isEigenes'] == 'true') {
	$type = 3;
} else {
	$type = 1;
}

$firmenlevel = intval($_SESSION['s_firmenlevel']);

pageheader(array('page_title' => 'Layout ausw&auml;hlen'));

Praktika_Static::__('job_ad');
?>

<script type="text/javascript">promptButtons = ['<' + 'a onclick="smallbox.hide()" class="button red small"' + '>' + 'OK' + '<' + '/a' + '>','<' + 'a onclick="smallbox.hide(\'upgrade\')" class="button red small"' + '>' + 'Packetupgrade' + '<' + '/a' + '>']</script>
<div id="stellenLayout">
	<form action="/firmen/stelle/<?php echo $_SESSION['jobAd']['stellenid']; ?>/layout.html" method="post" name="layout">
		<div id="chooseType">
			<span class="layoutTypeButton">Template<br />Kategorie:</span>
			<span class="layoutTypeButton"><a href="#1" onclick="show(1)" title="Kategorie &quot;Standard Templates&quot; w&auml;hlen"><img border="0" id="templateIcon_1" src="/gifs/bcenter/standard_template_icon.gif" alt="Kategorie &quot;Standard Templates&quot; w&auml;hlen" />Standard Templates</a></span>
			<span class="layoutTypeButton"><a href="#2" onclick="show(2)" title="Kategorie &quot;Video Templates&quot; w&auml;hlen"><img border="0" id="templateIcon_2" src="/gifs/bcenter/video_template_icon.gif" alt="Kategorie &quot;Video Templates&quot; w&auml;hlen" />Video<br />Templates</a></span>
			<span class="layoutTypeButton"><a href="#3" onclick="show(3)" title="Kategorie &quot;Eigene Templates&quot; w&auml;hlen"><img border="0" id="templateIcon_3" src="/gifs/bcenter/eigene_template_icon.gif" alt="Kategorie &quot;Eigene Templates&quot; w&auml;hlen" />Eigene<br />Templates</a></span>
		</div>
		<div id="setTemplate">
			<h4 id="layout"><span id="typeText"></span></h4>
			<table id="preview_standard">
				<colgroup>
					<col width="25%" />
					<col width="25%" />
					<col width="25%" />
					<col width="25%" />
				</colgroup>
				<tr>

<?php
$sql = 'SELECT id FROM stellentemplates WHERE isStandard = \'true\' ORDER BY sortOrder';
$result = mysql_db_query($database_db, $sql, $praktdbslave);

$i = 1;

while ($row = mysql_fetch_array($result)) {
	// not available?
	$na = (($firmenlevel == 0 && $row['id'] != 109) || ($firmenlevel < 2 && in_array($row['id'], range(2, 101)))) ? true : false;
	$naLiz = $row['id'] == 1 ? 'BASIS' : 'KOMFORT';
	
	if ($na == true) {
		$onmouseout = ' onmouseout="this.style.filter = \'alpha(opacity=60)\'; this.style.opacity = \'0.6\';"';
		$update = 'smallbox.confirm(\'Dieses Template ist erst ab einer <b>'.$naLiz.'-Lizenz</b> verf&uuml;gbar!\', promptButtons, function(e) { if (e==\'upgrade\') {location.href = \'/firmen/stelle/paket.html\';}}); return true; GB_showCenter(\'Hinweis\', \'/home/firmen/stelle/notice.phtml?id='.$_SESSION['jobAd']['stellenid'].'\', 180, 350)';
		$title = 'Auswahl erst ab '.$naLiz.' m&ouml;glich';
		$class = ' class="na"';
		
		$_SESSION['buchungsseite'] = 'layout';
	} else {
		$onmouseout = '';
		$title = 'Template ausw&auml;hlen';
		$update = 'update('.$_SESSION['jobAd']['stellenid'].', '.$row['id'].');';
		$class = '';
	}
	
	echo '
					<td>
						<p>
							<img id="template'.$row['id'].'" src="/gifs/templates/'.sprintf('%03d', $row['id']).'/preview.gif" onclick="'.$update.'" onmouseover="this.style.cursor=\'pointer\'; this.style.filter=\'alpha(opacity=100)\'; this.style.opacity=\'1\'"'.$onmouseout.' alt="'.$title.'" title="'.$title.'"'.$class.' /><br />
						</p>
						<p>
							<a href="#" onclick="smallbox.loadImage(\'/home/firmen/stelle/preview_template_images/'.$row['id'].'.png'.'\', 750, 758);" title="gr&ouml&szlig;eres Bild ansehen">Ansehen</a>
						</p>
					</td>
		';

	if (($i++ % 4) == 0) {
		echo '
				</tr>
				<tr>
		';
	}
}

if ($firmenlevel < 2) {
	echo '
				</tr>
				<tr>
					<td colspan="4"><strong>Anmerkung</strong>: Die Templates 3 - 13 sind erst ab Tarif KOMFORT verf&uuml;gbar!</td>
		';
}
?>

				</tr>
			</table>
			
			<table id="preview_video">
				<colgroup>
					<col width="25%" />
					<col width="25%" />
					<col width="25%" />
					<col width="25%" />
				</colgroup>
				<tr>

<?php
$sql = 'SELECT id FROM stellentemplates WHERE isVideo = \'true\'';
$result = mysql_db_query($database_db, $sql, $praktdbslave);

$i = 1;

while ($row = mysql_fetch_array($result)) {
	// not available?
	$na = ($firmenlevel == 0 || ($firmenlevel < 2 && $row['id'] > 11)) ? true : false;
	$naLiz = $row['id'] == 11 ? 'BASIS' : 'KOMFORT';

	if ($na == true) {
		$onmouseout = ' onmouseout="this.style.filter = \'alpha(opacity=60)\'; this.style.opacity = \'0.6\';"';
		$update = 'smallbox.confirm(\'Dieses Template ist erst ab einer <b>'.$naLiz.'-Lizenz</b> verf&uuml;gbar!\',promptButtons,function(e) { if (e==\'upgrade\') {location.href = \'/firmen/stelle/'.$_SESSION['jobAd']['stellenid'].'/paket.html\';}}); return true; GB_showCenter(\'Hinweis\', \'/home/firmen/stelle/notice.phtml?id='.$_SESSION['jobAd']['stellenid'].'\', 180, 350)';
		$title = 'Auswahl erst ab '.$naLiz.' m&ouml;glich';
		$class = ' class="na"';

		$_SESSION['buchungsseite'] = 'layout';
	} else {
		$onmouseout = '';
		$title = 'Template ausw&auml;hlen';
		$update = 'update('.$_SESSION['jobAd']['stellenid'].', '.$row['id'].');';
		$class = '';
	}
	
	echo '
					<td>
						<p>
							<img id="template'.$row['id'].'" src="/gifs/templates/'.sprintf('%03d', $row['id']).'/preview.gif" onclick="'.$update.'" onmouseover="this.style.cursor=\'pointer\'; this.style.filter=\'alpha(opacity=100)\'; this.style.opacity=\'1\'"'.$onmouseout.' alt="'.$title.'" title="'.$title.'"'.$class.' /><br />
						</p>
						<p>
							<a href="#" onclick="smallbox.loadImage(\'/home/firmen/stelle/preview_template_images/'.$row['id'].'.png'.'\', 750, 758);" title="gr&ouml&szlig;eres Bild ansehen">Ansehen</a>
						</p>
					</td>
		';
		
	if (($i++ % 4) == 0) {
		echo '
				</tr>
				<tr>
    	';
	}
}
?>

				</tr>
				<tr>
					<td colspan="4"><a href="#" onclick="parent.GB_showCenter('','/firmen/flash/',500,450); return false;">Video-Quelle bearbeiten</a></td>
				</tr>
			</table>
			
			<table id="preview_eigene">
				<colgroup>
					<col width="25%" />
					<col width="25%" />
					<col width="25%" />
					<col width="25%" />
				</colgroup>
				<tr>

<?php
// unter Paket Premium? oder ist FirmenID von Bauerfeind -> Zusatzfeature
if ($firmenlevel < 3 && $_SESSION['s_firmenid'] != 24483 && $_SESSION['s_firmenid'] != 21945 && $_SESSION['s_firmenid'] != 21945 && $_SESSION['s_firmenid'] != 27666 && $_SESSION['s_firmenid'] != 20817 && $_SESSION['s_firmenid'] != 28842 && $_SESSION['s_firmenid'] != 7978 && $_SESSION['s_firmenid'] != 666 && $_SESSION['s_firmenid'] != 8499 && $_SESSION['s_firmenid'] != 20644 && $_SESSION['s_firmenid'] != 19429 && empty($_SESSION["s_agenturid"])) {
	echo '
					<td colspan="4">Diese Funktion ist erst im Paket Premium verf&uuml;gbar!</td>
		';
} else {
	$sql = 'SELECT id, templatename FROM stellentemplates WHERE firmenid = '.intval($_SESSION['s_firmenid']).' AND isEigenes = \'true\'';
	$result = mysql_db_query($database_db, $sql, $praktdbslave);
	$num_rows = mysql_num_rows($result);
	
	
	
	if ($num_rows > 0) {
		$i = 1;
	
		while ($row = mysql_fetch_array($result)) {
			echo '
					<td>
						<p>
							<img id="template'.$row['id'].'" src="/gifs/templates/eigene/preview.gif" onclick="update('.$_SESSION['jobAd']['stellenid'].', '.$row['id'].');" onmouseover="this.style.cursor=\'pointer\';" alt="'.$row['templatename'].' ausw&auml;hlen" /><br />
							'.$row['templatename'].'
						</p>
					</td>
			';
	
			if (($i++ % 4) == 0) {
				echo '
				</tr>
				<tr>
				';
			}
		}
	} else {
		echo '
					<td>
						<p>
							<img id="template'.$row['id'].'" src="/gifs/templates/eigene/preview.gif" onclick="update('.$_SESSION['jobAd']['stellenid'].', 1)" onmouseover="this.style.cursor=\'pointer\';" alt="'.$row['templatename'].' ausw&auml;hlen" /><br />
							Basis-Template
						</p>
					</td>
				</tr>
				<tr>
					<td colspan="4"><i>Anmerkung:</i> Da zurzeit kein Template von Ihnen existiert, wird bis zur Fertigstellung Ihres Templates das Standardtemplate verwendet, um Ihnen die M&ouml;glichkeit zu geben, den Fortschritt Ihrer Stellenanzeige zu verfolgen</td>
		';
	}
}
?>

				</tr>
			</table>
			<fieldset class="control_panel">
				<p>
					<input id="cat" name="cat" type="hidden" value="<?php echo $type; ?>" />
					<input type="submit" name="save_layout" id="save_layout" value="WEITER" class="button red" />
				</p>
			</fieldset>
		</div>
	</form>
</div>

<script type="text/javascript" src="/scripts/ajax/layout.js"></script>

<?php
if ((isset($templateid) && $templateid != 0) || $type == 3) {
?>

<script type="text/javascript">
<!--
	show(<?php echo $type; ?>);
	document.getElementById('template' + <?php echo $templateid; ?>).style.border = '3px solid #f27000';
-->
</script>

<?php
} else {
?>

<script type="text/javascript">
<!--
	show(<?php echo $type; ?>);
	alert(unescape('Bitte w%E4hlen Sie ein Template durch Klick auf das entsprechende Vorschaubild aus.'));
-->
</script>

<?php
}

$_SESSION['sidebar'] = '';

bodyoff();
?>