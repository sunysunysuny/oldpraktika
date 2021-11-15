<?
// Bereich 2

if(!isset($praktdbslave)) {
	require("/home/praktika/etc/gb_config.inc.php");
}

praktikaInclude(array("db.php","layout.php","String.php"));
$hDB = new Praktika_Db();

if($_POST["act"] == "deleteMessage" && !empty($_POST["id"]) && md5($cryptbasis.$_POST["id"].$_SESSION["s_firmenid"].$cryptbasis) == $_POST["hashValue"]) {

	$hDB->query("UPDATE firmenkontakte SET inactive = 'true' WHERE id = ".(int)$_POST["id"]." AND firmenid = ".$_SESSION["s_firmenid"],$praktdbmaster);
	
}

$directory = (int)$_SESSION["s_subbereich"];
$bereich = $_SESSION["s_bereich"];
$firmenid = (int)$_SESSION["s_firmenid"];
$currentPage = (int)$_POST["page"];
$spalteValues = array("","Praktikum","Bachelor-/Masterarbeit","Nebenjob","Ausbildung","Berufseinstieg","Projekt","Trainee");

$_SESSION['s_firmenproparray']['bcenter'][$_SESSION['s_bereich']]['ordnerid'] = $directory;

if(!empty($_SESSION['s_firmenproparray']['bcenter']['aktbearbeiter']['val']) && $_SESSION['s_firmenproparray']['bcenter']['aktbearbeiter']['val'] != "%") {
	$bearbeiterSQL = 'AND firmenkontakte.bearbeiterid = '.(int)$_SESSION['s_firmenproparray']['bcenter']['aktbearbeiter']['val'];
}
if($_POST["act"] == "sorting" && !empty($_POST["typ"])) {
	$_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung"] = $_POST["typ"];
	$_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung_asc"] = $_POST["sortMode"];
}

$ordnerquery = ' AND ordner = '.$directory;

$sql = '
			SELECT
				COUNT(*) as anzahl
			FROM
				firmenkontakte
			WHERE
				firmenid = '.intval($_SESSION['s_firmenid']).'
				'.$ordnerquery.'
				AND inactive = \'false\'
				'.$bearbeiterSQL.'
		';

$numrows = $hDB->query($sql, $praktdbslave);
$countResult = mysql_fetch_assoc($numrows);

if($countResult["anzahl"] == 0) {
	echo "<br /><p style='text-align:center;'>Keine Eintr&auml;ge in dieser Rubrik vorhanden.</p>";	
	return;
}

$sortOrder = !empty($_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung_asc"])?$_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung_asc"]:"DESC";

switch($_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung"]) {
	case "eingangsdatum":
		
		$orderBy = "firmenkontakte.datum ".$sortOrder;
		break;
	case "absender":
		$orderBy = "p_name $sortOrder,p_vname $sortOrder";
		break;
	case "betreff":
		$orderBy = "firmenkontakte.betreff $sortOrder";
		break;
	default:
		$orderBy = "firmenkontakte.datum $sortOrder";
		break;
}

$sql = '
			SELECT
				firmenkontakte.id AS mitteilungsid,
				firmenkontakte.inactive,
				firmenkontakte.nutzerid,
				firmenkontakte.bearbeiterid,
				firmenkontakte.gelesen,
				firmenkontakte.betreff,
				UNIX_TIMESTAMP(firmenkontakte.datum) AS datum,
				firmenkontakte.spalte,
				nutzer.anrede `p_anrede`, 
				nutzer.name `p_name`,
				nutzer.vname `p_vname`,
				nutzer.ort `p_ort`,
				nutzer.plz `p_plz`,
				nutzer.inactive `p_inactive`,
				bearbeiter.anrede `b_anrede`, 
				bearbeiter.name `b_name`,
				bearbeiter.vname `b_vname`,
				einstellungen.lebenslauftemplate `lebenslauftemplate`
			FROM
				prakt2.firmenkontakte `firmenkontakte`
				LEFT JOIN prakt2.nutzer `nutzer` ON(nutzer.id = firmenkontakte.nutzerid)
				LEFT JOIN prakt2.bearbeiter `bearbeiter` ON(bearbeiter.id = firmenkontakte.bearbeiterid)
				LEFT JOIN prakt2_bprofil.einstellungen `einstellungen` ON(einstellungen.nutzerid = firmenkontakte.nutzerid AND sprachid = 2)
			WHERE
				firmenkontakte.firmenid = '.intval($_SESSION['s_firmenid']).'
				AND firmenkontakte.ordner = '.intval($_SESSION['s_firmenproparray']['bcenter'][$_SESSION['s_bereich']]['ordnerid']).'
				AND firmenkontakte.inactive = "false"
				'.$bearbeiterSQL.'
			ORDER BY
				'.$orderBy.'
			LIMIT '.(($currentPage-1)*10).',10
		';


$results = $hDB->query($sql, $praktdbmaster);
echo mysql_error();

$paging = Praktika_Layout::paging('containerLoadContent(\'/home/firmen/bcenter/messaging/mailbox.phtml\',\'page={page}&box=3&dir=0\',\'mailbox_container\');',$countResult["anzahl"], $currentPage, 10 /* $ds_pro_seite */);

?>
<div class="sortingContainer">
<img class="sortIcon" onclick="changeSorting('<?=$_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung"] ?>','ASC');" src="/styles/images/bullet_arrow_up<?=($_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung_asc"]!='ASC'?"_trans":"") ?>.gif" alt="absteigend" title="absteigend" /><img src="/styles/images/bullet_arrow_down<?=($_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung_asc"]!='DESC'?"_trans":"") ?>.gif" class="sortIcon" alt="absteigend" onclick="changeSorting('<?=$_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung"] ?>','DESC');"  title="absteigend" />	
Sortiert nach: 
	<select name="sortedRow" onchange="changeSorting(this.value,'DESC');">
		<option value="eingangsdatum" <?=$_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung"]=="eingangsdatum"?"selected='selected'":"" ?>>Eingangsdatum</option>
		<option value="absender" <?=$_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung"]=="absender"?"selected='selected'":"" ?>>Absendername</option>
		<option value="betreff" <?=$_SESSION["s_firmenproparray"]["bcenter"]["postein_sortierung"]=="betreff"?"selected='selected'":"" ?>>Betreff</option>
	</select>
	
</div>
<?
echo $paging."<br />";


	
while ($message = mysql_fetch_array($results)) {
?>
		<div class="bewerbungHeader">
			<div class="bewerberStatus"><? if($bewerbung['gelesen']=="false") { ?>Status: <img src="/styles/images/icons/neu1.png" alt="neue Bewerbung" title="neue Bewerbung" /><? } ?></div>
			<a href="/recruiting/kontaktdetails/<?=$message["nutzerid"].'/'.$message['mitteilungsid'].'/'.$message['spalte'] ?>/" onclick="smallbox.loadUrl('', this.href); return false;"><?=stripslashes($message['betreff']) ?></a>
			
		</div>
		<div class="bewerbungBody">
		<div class="actionButtons">
			<a href="/recruiting/kontaktdetails/<?=$message["nutzerid"].'/'.$message['mitteilungsid'].'/'.$message['spalte'] ?>/" onclick="smallbox.loadUrl('', this.href); return false;">
				<img src="/styles/images/icons/lupe.png"  alt="Ansehen" title="Ansehen"/>
			</a>&nbsp;&nbsp;
			<a href="/recruiting/email_antwort/<?=$message['nutzerid'] ?>/<?=$message['spalte'] ?>/" alt="Antworten" title="Antworten" onclick="window.open(this.href,'','width=600,height=800'); return false;">
				<img src="/styles/images/icons/mail.gif" alt="Antworten" title="Antworten" />
			</a>&nbsp;&nbsp;
			<!--<a href="/recruiting/weiterleitung_kurzmitteilung/<?=$message['mitteilungsid'].'/'.$message['spalte'] ?>/" alt="Weiterleiten" title="Weiterleiten"  onclick="return GB_showCenter('',this.href,400,700)">
				<img alt="Weiterleiten" title="Weiterleiten"  src="/styles/images/icons/weiterleiten.png" />
			</a>&nbsp;&nbsp;-->
			<!--<a href="/recruiting/verschieben/<?=$message['mitteilungsid'] ?>/<?=$_SESSION['s_bereich'] ?>/" alt="Verschieben" title="Verschieben"  onclick="return GB_showCenter('',this.href,300,700)">
				<img alt="Verschieben" title="Verschieben" src="/styles/images/icons/ordner.png" />
			</a>-
			&nbsp;&nbsp;-->
			<a href="#" alt="Löschen" title="Löschen"  onclick="deleteMessage(<?=$message['mitteilungsid'] ?>,'<?=md5($cryptbasis.$message['mitteilungsid'].$_SESSION["s_firmenid"].$cryptbasis) ?>'); return false;">
				<img src="/styles/images/icons/papierkorb.png" alt="Löschen" title="Löschen"  />
			</a>
</div>			
			<p class="bewerberInformation"><b>Bewerberinformation</b><br />&nbsp;&nbsp;<b><a href="/kandidatenprofil/01/<?=$message["nutzerid"] ?>/" onclick="window.open(this.href, '', 'width=800,height=1000'); return false;"><?=$message['p_anrede'].' '.$message['p_vname'].' '.$message['p_name'] ?></a></b><br />&nbsp;&nbsp;Ort: <?=$message['p_plz'].' '.$message['p_ort'] ?></p>
			<br />
			<p style="float:right;">Mitteilung f&uuml;r: <?=(!empty($message["b_anrede"])?$message["b_anrede"]." ".$message["b_vname"]." ".$message["b_name"]:"Alle") ?></p>
			<p>Mitteilung vom: <?=date("d.m.Y",$message["datum"]) ?></p>

			<br />
		</div>
		<?
}