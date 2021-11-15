<?
// Bereich 3

if(!isset($praktdbslave)) {
	require("/home/praktika/etc/gb_config.inc.php");
}

praktikaInclude(array("db.php","layout.php","passbild.php"));
$hDB = new Praktika_Db();

if($_POST["act"] == "deleteKandidat" && !empty($_POST["id"]) && md5($cryptbasis.$_POST["id"].$_SESSION["s_firmenid"].$cryptbasis) == $_POST["hashValue"]) {
	$dbstring = 'DELETE FROM firmenbookmark WHERE nutzerid='.intval($_POST['id']).' AND firmenid = '.intval($_SESSION['s_firmenid']);
	$hDB->query($dbstring, $praktdbmaster);
	
}

$directory = (int)$_SESSION["s_subbereich"];
$bereich = $_SESSION["s_bereich"];
$firmenid = (int)$_SESSION["s_firmenid"];
$currentPage = (int)$_POST["page"];
$spalteValues = array("","Praktikum","Bachelor-/Masterarbeit","Nebenjob","Ausbildung","Berufseinstieg","Projekt","Trainee");

// Kandidaten k�nnen keinem Bearbeiter zugewiesen werden

if($_POST["act"] == "sorting" && !empty($_POST["typ"])) {
	$_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung"] = $_POST["typ"];
	$_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung_asc"] = $_POST["sortMode"];
}

$_SESSION['s_firmenproparray']['bcenter'][$_SESSION['s_bereich']]['ordnerid'] = $directory;

$ordnerquery = ' AND ordner = '.$directory;

$sortOrder = !empty($_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung_asc"])?$_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung_asc"]:"DESC";

switch($_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung"]) {
	case "aufnahmedatum":
		
		$orderBy = "firmenbookmark.datum_eintrag ".$sortOrder;
		break;
	case "karrierestatus":
		$orderBy = "n_karrierestatus $sortOrder";
		break;
	case "name":
		$orderBy = "n_name $sortOrder, n_vname $sortOrder";
		break;
	default:
		$orderBy = "firmenbookmark.datum_eintrag $sortOrder";
		break;
}


$sql = '
			SELECT
				COUNT(*) as anzahl
			FROM
				firmenbookmark
			WHERE
				firmenid = '.intval($_SESSION['s_firmenid']).'
				'.$ordnerquery.'
		';
$numrows = $hDB->query($sql, $praktdbslave);
$countResult = mysql_fetch_assoc($numrows);

if($countResult["anzahl"] == 0) {
	echo "<br /><p style='text-align:center;'>Keine Eintr&auml;ge in dieser Rubrik vorhanden.</p>";	
	return;
}

// foto_nutzerid - Pr�ft ob Foto schon eingetragen ist
$sql = '
			SELECT
				firmenbookmark.id AS bookmarkid,
				firmenbookmark.nutzerid,
				firmenbookmark.art,
				UNIX_TIMESTAMP(firmenbookmark.datum_eintrag) AS datum,
				firmenbookmark.mzbezeichnung AS bezeichnung,
				firmenbookmark.stellenid,
				firmenbookmark.spalte,
				nutzer.id `n_id`,
				nutzer.name `n_name`,
				nutzer.vname `n_vname`,
				nutzer.anrede `n_anrede`,
				nutzer.plz `n_plz`,
				nutzer.ort `n_ort`,
				nutzer.strasse `n_strasse`,
				nutzer.geburtsdatum `n_geburtsdatum`,
				nutzer.hochschule `n_hochschule`,
				nutzer.hochschuletext `n_hochschuletext`,
				nutzer.tel `n_tel`,
				karierrestatus.german AS n_karrierestatus,				
				UNIX_TIMESTAMP(nutzer.datum_eintrag) `n_datum_eintrag`,
				einstellungen.lebenslauftemplate,
				bewerbungsfoto.nutzerid `foto_nutzerid`,
				bewerbungsfoto.url `foto_url`, 
				bewerbungsfoto.height `foto_height`, 
				bewerbungsfoto.width `foto_width`
			FROM
				prakt2.firmenbookmark `firmenbookmark`
				INNER JOIN prakt2.nutzer `nutzer` ON(nutzer.id = firmenbookmark.nutzerid AND inactive = "false")
				LEFT JOIN prakt2.karierrestatus `karierrestatus` ON(karierrestatus.id = nutzer.karierrestatus)
				LEFT JOIN prakt2_bprofil.einstellungen `einstellungen` ON(einstellungen.nutzerid = nutzer.id)
				LEFT JOIN prakt2.bewerbungsfoto `bewerbungsfoto` ON(bewerbungsfoto.nutzerid = nutzer.id)
			WHERE
				firmenid = '.intval($_SESSION['s_firmenid']).' 
				'.$ordnerquery.'
			GROUP BY
				nutzerid
			ORDER BY
				'.$orderBy.'
			LIMIT '.(($currentPage-1)*10).',10
		';

			
$results = $hDB->query($sql, $praktdbslave);
echo mysql_error();

$paging = Praktika_Layout::paging('containerLoadContent(\'/home/firmen/bcenter/messaging/mailbox.phtml\',\'page={page}&box=3&dir=0\',\'mailbox_container\');',$countResult["anzahl"], $currentPage, 10 /* $ds_pro_seite */);

?>
<p class='pageNumbers'>
	<? echo $paging."<br />"; ?>
</p>
<p class="sortingContainer">
	<img class="sortIcon" onclick="changeSorting('<?=$_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung"] ?>','ASC');" src="/gifs/stelle/bullet_arrow_up<?=($_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung_asc"]!='ASC'?"_trans":"") ?>.gif" alt="absteigend" title="absteigend" /><img src="/gifs/stelle/bullet_arrow_down<?=($_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung_asc"]!='DESC'?"_trans":"") ?>.gif" class="sortIcon" alt="absteigend" onclick="changeSorting('<?=$_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung"] ?>','DESC');"  title="absteigend" />
Sortiert nach: 
	<select name="sortedRow" onchange="changeSorting(this.value,'DESC');">
		<option value="aufnahmedatum" <?=$_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung"]=="aufnahmedatum"?"selected='selected'":"" ?>>Aufnahmedatum</option>
		<option value="karrierestatus" <?=$_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung"]=="karrierestatus"?"selected='selected'":"" ?>>Karrierestatus</option>
		<option value="name" <?=$_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung"]=="name"?"selected='selected'":"" ?>>Name</option>
	</select>


</p>
	<?

while ($kandidat = mysql_fetch_array($results)) {

	if (!empty($kandidat['n_tel'])) {
		if (strlen($kandidat['n_tel']) > 25) {
			$telefonnummer = substr($kandidat['n_tel'],0,25);
		} else {
			$telefonnummer = $kandidat['n_tel'];
		}
	}
	if ($kandidat['art'] == 1) {
		$ergebnisse =  'Ergebnisse: aus der Standardsuche'."\n";
	}

	if ($kandidat['art'] == 2) {
		$ergebnisse = 'Ergebnisse: aus der Statussuche'."\n";
	}

	if ($kandidat['art'] == 3) {
		$ergebnisse = 'Ergebnisse: aus der Profilsuche'."\n";
	}	

	// Bewerbungsbild
	if (!empty($kandidat["foto_nutzerid"])) {
		$url = $kandidat["foto_url"];

		$link = Praktika_Passbild::getLink($kandidat['nutzerid'], 80, $url);
		
		if($link !== false) {
			$photo = "<img src='".$link."' alt='Passbild' title='Passbild' />";
		} else {
			if($kandidat["anrede"] == "Herr") {
				$photo = '<img src="/styles/images/kcenter/profilbild_m.png" alt="Passbild" title="Passbild" />';
			} else {
				$photo = '<img src="/styles/images/kcenter/profilbild_w.png" alt="Passbild" title="Passbild" />';
			}
		}
	} else {
		$photo = '';
	}	
	?>
		<p class="bewerbungHeader">
			
			<?=$kandidat['n_anrede'].' '.$kandidat['n_vname'].' '.$kandidat['n_name'] ?>
			
		</p>
		<div class="bewerbungBody">

		<p style="float:right; text-align:left;">

			<a alt="Ansehen" title="Ansehen"  href="#" onclick="window.open('/kandidatenprofil/<?=(empty($kandidat['lebenslauflayout'])?'01':$kandidat['lebenslauflayout']).'/'.$kandidat['nutzerid'] ?>/', '', 'width=800,height=1000'); return false;">
				<img src="/styles/images/icons/lupe.png"  alt="Ansehen" title="Ansehen"/></a>&nbsp;&nbsp;
			<!--<a href="/recruiting/kontakt_nutzer/<?=$kandidat['nutzerid'] ?>/<?=$kandidat['stellenid'] ?>/<?=$kandidat['spalte'] ?>/" alt="Antworten" title="Antworten" onclick="return GB_showCenter('',this.href,700,700)">
				<img src="/styles/images/icons/mail.gif" alt="Antworten" title="Antworten" /></a>&nbsp;&nbsp;-->

			<a href="/recruiting/email_nutzer/<?=$kandidat['nutzerid'] ?>/<?=$kandidat['spalte'] ?>/" alt="Antworten" title="Antworten" onclick="window.open(this.href,'','width=600,height=800'); return false;">
				<img src="/styles/images/icons/mail.gif" alt="Antworten" title="Antworten">
			</a>
			&nbsp;
			<!--<a href="/recruiting/verschieben/<?=$kandidat['bookmarkid'] ?>/<?=$_SESSION['s_bereich'] ?>/" alt="Verschieben" title="Verschieben"  onclick="return GB_showCenter('',this.href,300,700)">
				<img alt="Verschieben" title="Verschieben" src="/styles/images/icons/ordner.png" /></a>
-->
			<a href="#" alt="L&ouml;schen" title="L&ouml;schen"  onclick="deleteKandidat(<?=$kandidat['nutzerid'] ?>,'<?=md5($cryptbasis.$kandidat['nutzerid'].$_SESSION["s_firmenid"].$cryptbasis) ?>'); return false;">
				<img src="/styles/images/icons/papierkorb.png" alt="L&ouml;schen" title="L&ouml;schen"  /></a>
			<p style="margin-top:25px;">
			<b>Mitglied seit:</b> <?=date("d.m.Y", $kandidat["n_datum_eintrag"]) ?><br />
			<b>Chiffre:</b> <?=$kandidat['nutzerid'] ?><br />
			<b>Bezeichnung:</b> <i><?=$kandidat['bezeichnung'] ?></i><br />
			<?=$ergebnisse ?>
			</p><br />
		</p>

			<? if(!empty($photo)) { ?><div class="bewerberFoto"><?=$photo ?></div><? } ?>
			<p class="bewerberInformation">

				<b>Bewerberinformation</b><br />&nbsp;<b><a href="/kandidatenprofil/01/<?=$kandidat["nutzerid"] ?>/" onclick="window.open(this.href, '', 'width=800,height=1000'); return false;"><?=$kandidat['n_anrede'].' '.$kandidat['n_vname'].' '.$kandidat['n_name'] ?></a></b><br />&nbsp;<?=($kandidat['n_strasse']) ?><br />&nbsp;<?=($kandidat['n_plz'].' '.$kandidat['n_ort']) ?><br /><br />
				<b>Telefon-Nr:</b> <?=$telefonnummer ?><br />
				<b>Karrierestatus:</b> <?=$kandidat['n_karrierestatus'] ?>
			</p>


			
	</div>	
	<?
}
