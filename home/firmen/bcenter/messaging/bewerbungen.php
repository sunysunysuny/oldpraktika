<?
// Wird includiert ==> Funktionen aus der Lib sind schon vorhanden

$directory = (int)$_SESSION["s_subbereich"];
$bereich = $_SESSION["s_bereich"];
$firmenid = (int)$_SESSION["s_firmenid"];
$currentPage = (int)$_POST["page"];
$spalteValues = array("","Praktikum","Bachelor-/Masterarbeit","Nebenjob","Ausbildung","Berufseinstieg","Projekt","Trainee");

if($_POST["act"] == "deleteBewerbung" && !empty($_POST["id"]) && md5($cryptbasis.$_POST["id"].$_SESSION["s_firmenid"].$cryptbasis) == $_POST["hashValue"]) {
	$hDB->query("UPDATE prakt2.bewerbung SET inactive = 'true' WHERE id = ".(int)$_POST["id"]." AND firmenid = ".$_SESSION["s_firmenid"],$praktdbmaster);
    exit();
}

$objFirma = new Praktika_Firma($firmenid);

if(!empty($_SESSION['s_firmenproparray']['bcenter']['aktbearbeiter']['val']) && $_SESSION['s_firmenproparray']['bcenter']['aktbearbeiter']['val'] != "%") {
	$bearbeiterSQL = 'AND bewerbung.bearbeiterid = '.(int)$_SESSION['s_firmenproparray']['bcenter']['aktbearbeiter']['val'];
}


if($_POST["act"] == "sorting" && !empty($_POST["typ"])) {
	$_SESSION["s_firmenproparray"]["bcenter"]["bew_sortierung"] = $_POST["typ"];
	$_SESSION["s_firmenproparray"]["bcenter"]["bew_sortierung_asc"] = $_POST["sortMode"];
}

$anzahlBewerbungen = $objFirma->countBewerbungen($directory, (int)$_SESSION['s_firmenproparray']['bcenter']['aktbearbeiter']['val']);

// Wenn keine Bewerbungen gefunden, dann Meldung ausgeben
if($anzahlBewerbungen == 0) {
	echo "<br /><p style='text-align:center;'>Keine Eintr&auml;ge in dieser Rubrik vorhanden.</p>";	
	return;
}

$sortOrder = !empty($_SESSION["s_firmenproparray"]["bcenter"]["bew_sortierung_asc"])?$_SESSION["s_firmenproparray"]["bcenter"]["bew_sortierung_asc"]:"DESC";

switch($_SESSION["s_firmenproparray"]["bcenter"]["bew_sortierung"]) {
	case "status":
		if($sortOrder == "ASC") { $sortOrder = "DESC"; } else { $sortOrder = "ASC"; }
		
		$orderBy = "bewerbung.gelesen $sortOrder, bewerbung.bearbeitung $sortOrder, bewerbung.beantwortet $sortOrder, bewerbung.rueckantwort $sortOrder, bewerbung.zusage $sortOrder, bewerbung.absage $sortOrder";
		break;
	case "datum":
		$orderBy = "bewerbung.d_eintrag $sortOrder";
		break;
	case "bewerber":
		$orderBy = "bewerbung.name $sortOrder, bewerbung.vname $sortOrder";
		break;
	case "jobcode":
		$orderBy = "stellen.jobcode $sortOrder";
		break;
	default:
		$orderBy = "bewerbung.modify $sortOrder";
		break;
}

$bewerbungen = $objFirma->getBewerbungen($directory, $_SESSION['s_firmenproparray']['bcenter']['aktbearbeiter']['val'], array($_SESSION["s_firmenproparray"]["bcenter"]["bew_sortierung"],!empty($_SESSION["s_firmenproparray"]["bcenter"]["bew_sortierung_asc"])?$_SESSION["s_firmenproparray"]["bcenter"]["bew_sortierung_asc"]:"DESC"), array(($currentPage - 1) * 10, 10));

$num_rows = $anzahlBewerbungen;
Praktika_Static::__("adressbuch");

$paging = Praktika_Layout::paginator("#", $num_rows, $currentPage, 10, 'jQueryLoadContent(\'/home/firmen/bcenter/messaging/mailbox.phtml\',\'page=(page)&box=1&dir=0\',\'mailbox_container\');return false;'); 

                                     #'containerLoadContent(\'/home/firmen/bcenter/messaging/mailbox.phtml\',\'page={page}&box=1&dir=0\',\'mailbox_container\');',, , 10 /* $ds_pro_seite */);
# $paging = Praktika_Layout::paging('containerLoadContent(\'/home/firmen/bcenter/messaging/mailbox.phtml\',\'page={page}&box=3&dir=0\',\'mailbox_container\');',$countResult["anzahl"], $currentPage, 10 /* $ds_pro_seite */);

?>
<p class="sortingContainer">
<img class="sortIcon" onclick="changeSorting('<?=$_SESSION["s_firmenproparray"]["bcenter"]["bew_sortierung"] ?>','ASC');" src="/styles/images/bullet_arrow_up<?=($_SESSION["s_firmenproparray"]["bcenter"]["bew_sortierung_asc"]!='ASC'?"_trans":"") ?>.gif" alt="absteigend" title="absteigend" /><img src="/styles/images/bullet_arrow_down<?=($_SESSION["s_firmenproparray"]["bcenter"]["bew_sortierung_asc"]!='DESC'?"_trans":"") ?>.gif" class="sortIcon" alt="absteigend" onclick="changeSorting('<?=$_SESSION["s_firmenproparray"]["bcenter"]["bew_sortierung"] ?>','DESC');"  title="absteigend" />	
Sortiert nach: 
	<select name="sortedRow" onchange="changeSorting(this.value,'DESC');">
		<option value="status" <?=$_SESSION["s_firmenproparray"]["bcenter"]["bew_sortierung"]=="status"?"selected='selected'":"" ?>>Status</option>
		<option value="bewerber" <?=$_SESSION["s_firmenproparray"]["bcenter"]["bew_sortierung"]=="bewerber"?"selected='selected'":"" ?>>Bewerber</option>
		<option value="datum" <?=$_SESSION["s_firmenproparray"]["bcenter"]["bew_sortierung"]=="datum"?"selected='selected'":"" ?>>Eingangsdatum</option>
		<option value="jobcode" <?=$_SESSION["s_firmenproparray"]["bcenter"]["bew_sortierung"]=="jobcode"?"selected='selected'":"" ?>>Jobcode</option>		
	</select>
	
</p>
<?
echo $paging;

$statusListe = array("gelesen","","","R&uuml;ckantwort","Zusage","Absage");
# echo $sql;# var_dump(mysql_num_rows($results));
if ($num_rows > 0) {

	foreach($bewerbungen as $objBewerbung) {
        $log = $objBewerbung->getLog();

		#$sql = "SELECT *, UNIX_TIMESTAMP(datum_eintrag) as ts FROM prakt2.bewerbung_log WHERE mode = 2 AND bid = '".$bewerbung['bewerbid']."' ORDER BY datum_eintrag DESC LIMIT 5";
		#$forwards = $hDB->query($sql, $praktdbslave);
        ?>
        <div id='bewerbung_<? echo $objBewerbung->id ?>' class="clearfix">
            <p class="bewerbungHeader">

				<a href="/Bewerbungsmappe/<? echo $objBewerbung->bewerber["id"]."/".$objBewerbung->id."/".$objBewerbung->layout ?>/" onclick="smallbox.loadUrl('', this.href,'', {border:false,cutBody:true}); return false;"><? echo $objBewerbung->bewerber["anrede"]." ".(!empty($objBewerbung->bewerber["title"])?$objBewerbung->bewerber["title"]." ":"").$objBewerbung->bewerber["vname"]." ".$objBewerbung->bewerber["name"] ?></a> <? if(!empty($objBewerbung->stelle->jobcode)) { ?><span class="jobcode">(Jobcode: <?=Praktika_String::cutLength($objBewerbung->stelle->jobcode, 300) ?>)</span><? } ?>

				<span class="bewerberStatus">
                    <?
					error_log("STATUS: [".$objBewerbung->id."] ".$objBewerbung->status);
                    switch($objBewerbung->status) {
                        case Praktika_Kcenter_Bewerbung::STATUS_NEU:
                            echo "<b>Neue Bewerbung</b>";
                            break;
                        case Praktika_Kcenter_Bewerbung::STATUS_BEANTWORTET:
                            echo "Antwort gesendet";
                            break;
                        case Praktika_Kcenter_Bewerbung::STATUS_ZUSAGE:
                            echo "Zusage gesendet";
                            break;
                        case Praktika_Kcenter_Bewerbung::STATUS_ABSAGE:
                            echo "Absage gesendet";
                            break;
                        case Praktika_Kcenter_Bewerbung::STATUS_GELESEN:
                            echo "gelesen";
                            break;
                    }
                    ?>
                </span>
            </p>

            <ol class="actionButtons">
                <li class="actionButton"><img src="/styles/images/icons/lupe.png" /> <a href="/Bewerbungsmappe/<? echo $objBewerbung->bewerber["id"]."/".$objBewerbung->id."/".$objBewerbung->layout ?>/" onclick="smallbox.loadUrl('', this.href,'', {border:false,cutBody:true}); return false;">Ansehen</a></li>
                <li class="actionButton"><img src="/styles/images/icons/mail.gif" /> <a href="/recruiting/antwort/<? echo $objBewerbung->id ?>/" onclick="smallbox.loadUrl('', this.href); return false;GB_showCenter('',this.href,700,700)">Antworten</a></li>
                <li class="actionButton"><img src="/styles/images/icons/weiterleiten.png" /> <a onclick='return bcenter.showWeiterleitung(<?=$objBewerbung->id ?>);' href="/recruiting/weiterleitung/<?=$objBewerbung->id ?>/<?=$objBewerbung->stelle->spalte ?>/">Als eMail</a></li>
                <li class="actionButton"><img src="/styles/images/icons/ordner.png" /> <a href="/recruiting/verschieben/<?=$objBewerbung->id."/".$_SESSION['s_bereich'] ?>/" onclick="smallbox.loadUrl('', this.href);return false;">Verschieben</a></li>
                <li class="actionButton"><img src="/styles/images/icons/papierkorb.png" /> <a href="#" onclick="deleteBewerbung(<?=$objBewerbung->id ?>,'<?=md5($cryptbasis.$objBewerbung->id.$_SESSION["s_firmenid"].$cryptbasis) ?>'); return false;">L&ouml;schen</a></li>
            </ol>


        <table class="bewerbungBody">
                <colgroup>
                  <col width="100">
                  <col width="400">
                </colgroup>
                <tr>
                    <td>Stelle:</td>
                    <td><? echo $objBewerbung->stelle->taetigkeit ?><br /><br /></td>
                </tr>
                <tr>
                    <td>Bewerber:</td>
                    <td><? echo $objBewerbung->bewerber["anrede"]." ".(!empty($objBewerbung->bewerber["title"])?$objBewerbung->bewerber["title"]." ":"").$objBewerbung->bewerber["vname"]." ".$objBewerbung->bewerber["name"] ?></td>
                </tr>
                <tr>
                    <td>Wohnort:</td>
                    <td><?=$objBewerbung->bewerber["plz"]." ".$objBewerbung->bewerber["ort"] ?></td>
                </tr>
                <tr>
                    <td>Bereich:</td>
                    <td><?=$spalteValues[$objBewerbung->stelle->spalte] ?></td>
                </tr>
                <tr>
                    <td>betreut von:</td>
                    <td><?=$objBewerbung->bearbeiter["vname"]." ".$objBewerbung->bearbeiter["name"] ?></td>
                </tr>
                <tr>
                    <td>Eingang:</td>
                    <td><?=date("d.m.Y", $objBewerbung->datum_versand) ?></td>
                </tr>
				<tr id='openBearbeitungsverlauf_<? echo $objBewerbung->id ?>' <? echo count($log)>0?"":"style='display:none'" ?>>
					<td colspan="2" onclick="$('#bearbeitungsverlauf_<? echo $objBewerbung->id ?>').fadeIn('fast');this.innerHTML = '';" style="cursor:pointer;padding-top:10px;"><strong>Bearbeitungsverlauf öffnen</strong></td>
				</tr>
            </table>
            <div id='bearbeitungsverlauf_<? echo $objBewerbung->id ?>' style="display:none;">
				<p style="font-size:11px;">
					<strong>Bearbeitungsverlauf:</strong><br />
					<? for($a = 0;$a < count($log);$a++) {
						echo "<em>".$log[$a]["datum"]."</em> - ".$log[$a]["text"]."<br />";
					} ?>
				</p>
			</div>
			
            <p class="bewerbungControl" style="display:<? echo count($log)>0?"":"none" ?>">

            </p>
        </div>

    <? /*if($bewerbung["antwort_date"] != "0") { ?>
		&nbsp;Antwort versendet am: <em><?=date("d.m.Y", $bewerbung["antwort_date"]) ?></em><br />
		<? } ?>
		<? if($bewerbung["rueckruf_date"] != "0") { ?>
		&nbsp;Rückruf erbeten am: <em><?=date("d.m.Y", $bewerbung["rueckruf_date"]) ?></em><br />
		<? } ?>
		<? if($bewerbung["einladung_date"] != "0") { ?>
		&nbsp;Einladung gesendet am: <em><?=date("d.m.Y", $bewerbung["einladung_date"]) ?></em><br />
		<? } ?>
		<? // require("/home/praktika/etc/bearbeitungsstatus.inc.php"); 
		if($bewerbung["status_4"] == "true") {
			echo "&nbsp;Zusage am: <em>".date("d.m.Y",$bewerbung["end_date"])."</em><br /><br />";
		} else if($bewerbung["status_5"] == "true") {
			echo "&nbsp;Absage am: <em>".date("d.m.Y",$bewerbung["end_date"])."</em><br /><br />";
		} /* else {
			for($a = 0;$a < count($statusListe);$a++) { if(!empty($statusListe[$a])) {
	?>
				<div class="statusButton <?=($bewerbung["status_".$a]=="true"?"activeButton":"") ?>" id="bewstatus_<?=$a ?>_<?=$bewerbung["bewerbid"] ?>" <?=($bewerbung["status_".$a]!="true"?'onclick="setBewerbungsStatus('.$bewerbung["bewerbid"].",".$a.');"':"") ?>>
					<a href="/recruiting/statusmail/<?=$a."/".$bewerbung["nutzerid"] ?>/<?=$bewerbung["bewerbid"] ?>/"  onclick="return GB_showCenter('',this.href,500,450)"><?=$statusListe[$a] ?></a>
				</div>
	<?
			} }
		}
		if(mysql_num_rows($forwards) > 0) {
			echo "<br />Weitergeleitet an:<br />";
			$counter = 1;
			while($forw = mysql_fetch_assoc($forwards)) {
				echo '<label class="label">&nbsp;'.$forw["value"].'</label> - <em>'.date("d.m.Y", $forw["ts"]).'</em><br />';
				if($counter == 4) { 
					echo "&nbsp;<b>...</b>";
					break;
				}
				$counter++;
			}
			
		} */
		?><?
	}
}
	
/*		$firmenkontaktarray[$i] = $result_row;
		$nutzerid = $firmenkontaktarray[$i]['nutzerid'];
		$nutzer = mysql_db_query($database_db,'SELECT id,name,vname,anrede FROM nutzer WHERE id='.intval($nutzerid).' AND inactive=\'false\'',$praktdbslave);
		$nutzerarray = mysql_fetch_array($nutzer);
		$firmenkontaktarray[$i][8] = $nutzerarray['name'];
		$firmenkontaktarray[$i]['bewerber'] = $nutzerarray['name'];
		$bearbeiterid = $firmenkontaktarray[$i]['bearbeiterid'];
		$bearbeiter = mysql_db_query($database_db,'SELECT id,name,vname,anrede FROM bearbeiter WHERE id='.intval($bearbeiterid),$praktdbslave);
		
		if (mysql_numrows($bearbeiter) == 0) {
			$bearbeiter = mysql_db_query($database_db,'SELECT id,name,vname,anrede FROM bearbeiter WHERE id='.intval($_SESSION['s_loginid']),$praktdbslave);
		}
		
		$bearbeiterarray = mysql_fetch_array($bearbeiter);
		$firmenkontaktarray[$i][9] = $bearbeiterarray['anrede'].' '.$bearbeiterarray['vname'].' '.$bearbeiterarray['name'];
		$firmenkontaktarray[$i]['bearbeiter'] = $bearbeiterarray['anrede'].' '.$bearbeiterarray['vname'].' '.$bearbeiterarray['name'];
		
		$i++; 
	}
}

if ($num_rows > 0) {
	// erste seite? //
	if (!isset($ds_count)) {
		$ds_count = 0;
	}
	$ds_value = $ds_count;

	// datensaetze pro seite ueberpruefen und setzen //
	if (!(($ds_pro_seite == 5) || ($ds_pro_seite == 10) || ($ds_pro_seite == 20))) {
		$ds_pro_seite = 10;
	}

	if ($num_rows == $ds_count) {
		$ds_count = $ds_count - $ds_pro_seite;
		$ds_value = $ds_value - $ds_pro_seite;
	}

	$bearbeiter = mysql_db_query($database_db,'SELECT id,name,vname,anrede FROM bearbeiter WHERE firmenid='.intval($_SESSION['s_firmenid']),$praktdbslave);

	echo '		<form name="bearbeiterfilterwahl" method="post" action="/recruiting/">'."\n";
	echo '			<fieldset>'."\n";
	echo '				<p>'."\n";
	echo '					<label for="bearbeiterfilter">Anzahl der Bewerbungen: '.$num_rows.'</label>'."\n";
	echo '					<select id="bearbeiterfilter" name="bearbeiterfilter" onchange="this.form.submit();">'."\n";
	echo '						<option value="%">alle</option>'."\n";
	
	while ($bearbeiter_row = mysql_fetch_array($bearbeiter)) {
		echo '						<option value="'.$bearbeiter_row['id'].'"';
		
		if ($_SESSION['s_firmenproparray']['bcenter']['aktbearbeiter']['val'] == $bearbeiter_row['id']) {
			echo ' selected="selected"';
		}
		
		echo '>'.$bearbeiter_row['anrede'].' '.$bearbeiter_row['vname'].' '.$bearbeiter_row['name'].'</option>'."\n";
	}
	
	echo '					</select>'."\n";
	
	echo '				</p>'."\n";
	echo seitenausgabe($num_rows,$ds_pro_seite,$_SESSION['s_bereich'],$ds_count);
	echo '				<input type="hidden" name="ds_count" value="0" />'."\n";
	echo '			</fieldset>'."\n";
	echo '		</form>'."\n";

	$ds_fest = $ds_count;

	$rueckgabe = sortierung($firmenkontaktarray,$_SESSION['s_firmenproparray']['bcenter'][$_SESSION['s_bereich']]['sort'],$_SESSION['s_firmenproparray']['bcenter'][$_SESSION['s_bereich']]['ordnerid']);
	
	echo $rueckgabe[1];
	
	$firmenkontaktarray = $rueckgabe[0];

	/* Datensaetze anzeigen
	$end_ds = min(($ds_count + $ds_pro_seite), ($num_rows));

	echo '		<table>'."\n";
	echo '			<colgroup>'."\n";
	echo '				<col width="5%" />'."\n";
	echo '				<col width="90%" />'."\n";
	echo '				<col width="5%" />'."\n";
	echo '			</colgroup>'."\n";
	echo '			<tr>'."\n";
	echo '				<th>Status</th>'."\n";
	echo '				<th>Bewerberinformation</th>'."\n";
	echo '				<th>&nbsp;</th>'."\n";
	echo '			</tr>'."\n";

	for ($i = $ds_count; $i < $end_ds; $i++) {
		$result_row = $firmenkontaktarray[$i];
		$stellenid = $result_row['prakstellenid'];

		/* Nutzer herausbekommen
		$nutzerid = $result_row['nutzerid'];
		$nutzer = mysql_db_query($database_db,'SELECT anrede,name,vname FROM nutzer WHERE id='.intval($nutzerid).' AND inactive=\'false\'',$praktdbslave);

		/* Stellentitel herausbekommen
		$stelle = mysql_db_query($database_db,'SELECT jobcode, taetigkeit FROM stellen WHERE id='.intval($stellenid).' AND inactive=\'false\' AND spalte ='.$result_row['spalte'],$praktdbslave);

		$result_stelle = mysql_fetch_array($stelle);

		$lebenslauf = mysql_db_query($database_bprofil,'SELECT lebenslauftemplate FROM einstellungen WHERE nutzerid = '.intval($nutzerid).' AND sprachid=2',$praktdbslave);
		if (mysql_num_rows($lebenslauf) > 0) {
			$row = mysql_fetch_array($lebenslauf);
			$lebenslauftemplate = $row['lebenslauftemplate'];
		} else {
			$lebenslauftemplate = '01';
		}
		
		echo '			<tr'.((($end_ds - $i) % 2 == 0) ? ' class="even' : ' class="odd').'">'."\n";
		echo '				<td style="text-align: center;">'.($result_row['gelesen'] == 'false' ? '<img src="/styles/images/icons/neu1.png" alt="neu" title="neu" width="16" height="16" />' : '').'</td>'."\n";
		echo '				<td>Bewerbung von: <a href="/bewerbungsmappe_ansehen/'.$nutzerid.'/'.$result_row['bewerbid'].'/'.$lebenslauftemplate.'/" onclick="return PB_showFullScreen(\'\', this.href)">'.mysql_result($nutzer,0,'anrede').' '.mysql_result($nutzer,0,'vname').' '.mysql_result($nutzer,0,'name').'</a><br />'.$language['strOrt'].': '.$result_row['plz'].' '.$result_row['ort'];
		echo '<br />'.$language['strBewerbungvom'].': '.strftime('%x',$result_row['datum']);

		// Ausgabe des Bereichs
		if ($result_row['spalte'] == SECTION_PRAKTIKUM) {
			$bereichsname = SECTION_PRAKTIKUM_ALPHA;
		} elseif ($result_row['spalte'] == SECTION_DIPLOM) {
			$bereichsname = SECTION_DIPLOM_ALPHA;
		} elseif ($result_row['spalte'] == SECTION_NEBENJOB) {
			$bereichsname = SECTION_NEBENJOB_ALPHA;
		} elseif ($result_row['spalte'] == SECTION_AUSBILDUNG) {
			$bereichsname = SECTION_AUSBILDUNG_ALPHA;
		} elseif ($result_row['spalte'] == SECTION_BERUFSEINSTIEG) {
			$bereichsname = SECTION_BERUFSEINSTIEG_ALPHA;
		} elseif ($result_row['spalte'] == SECTION_PROJEKT) {
			$bereichsname = SECTION_PROJEKT_ALPHA;
		} elseif ($result_row['spalte'] == SECTION_TRAINEE) {
			$bereichsname = SECTION_TRAINEE_ALPHA;
		}
		
		echo '<br />Bereich: '.$bereichsname;
	    
		if ($result_row['bearbeiterid'] != 0) {
			$bearbeiteridval = $result_row['bearbeiterid'];
			$bearbeitersel = mysql_db_query($database_db,'SELECT vname,name FROM bearbeiter WHERE id='.intval($bearbeiteridval).' AND firmenid='.intval($_SESSION['s_firmenid']).' AND inactive=\'false\'',$praktdbslave);
			$vorname = mysql_result($bearbeitersel,0,'vname');
			
			if (($bearbeitersel != false) && mysql_num_rows($bearbeitersel) > 0) {
				if (($bearbeitersel != false) && mysql_num_rows($bearbeitersel) > 0 && !empty($vorname)) {
					echo '<br />betreut von: '.mysql_result($bearbeitersel,0,'vname').' '.mysql_result($bearbeitersel,0,'name')."\n";
				}
			}
		}

		echo '</td>'."\n";
		echo '				<td rowspan="2">'."\n";
		echo '					<p class="icons_vertical">'."\n";
		echo '						<span><a href="/bewerbungsmappe_ansehen/'.$nutzerid.'/'.$result_row['bewerbid'].'/'.$lebenslauftemplate.'/" onclick="return PB_showFullScreen(\'\', this.href)"><img src="/styles/images/icons/lupe.png" alt="'.$language['strBewerbungansehen'].'" title="'.$language['strBewerbungansehen'].'" /></a><br /></span>'."\n";
		echo '						<span><a href="/recruiting/antwort/'.$nutzerid.'/'.$result_row['bewerbid'].'/'.$result_row['spalte'].'/" onclick="return GB_showCenter(\'\',this.href,700,700)"><img src="/styles/images/icons/mail.png" alt="Antworten" title="Antworten" /></a><br /></span>'."\n";
    	echo '						<span><a href="/recruiting/weiterleitung/'.$result_row['bewerbid'].'/'.$result_row['spalte'].'/" onclick="return GB_showCenter(\'\',this.href,700,700)"><img src="/styles/images/icons/weiterleiten.png" alt="'.$language['strBewerbungweiterleiten'].'" title="'.$language['strBewerbungweiterleiten'].'" /></a><br /></span>'."\n";
    	echo '						<span><a href="/recruiting/verschieben/'.$result_row['bewerbid'].'/'.$_SESSION['s_bereich'].'/" onclick="return GB_showCenter(\'\',this.href,700,700)"><img src="/styles/images/icons/ordner.png" alt="Diese Bewerbung verschieben" title="Diese Bewerbung verschieben" /></a><br /></span>'."\n";
	    echo '						<span><a href="/recruiting/loeschen/'.$result_row['bewerbid'].'/'.$_SESSION['s_bereich'].'/'.$ds_fest.'/'.$ds_pro_seite.'/" onclick="return confirm(\'Wollen Sie diese Bewerbung wirklich l&ouml;schen?\');"><img src="/styles/images/icons/papierkorb.png" alt="'.$language['strBewerbungloeschen'].'" title="'.$language['strBewerbungloeschen'].'" /></a></span>'."\n";
		echo '					</p>'."\n";
		echo '				</td>'."\n";
		echo '			</tr>'."\n";
		echo '			<tr'.((($end_ds - $i) % 2 == 0) ? ' class="even"' : ' class="odd"').'>'."\n";
		echo '				<td>&nbsp;</td>'."\n";
		echo '				<td><strong>Stelle: '.$result_stelle['taetigkeit'].'</strong><br />Stellencode: '.$result_stelle['jobcode'].'</td>'."\n";
		echo '			</tr>'."\n";
		echo '			<tr'.((($end_ds - $i) % 2 == 0) ? ' class="even bewerber' : ' class="odd bewerber').'">'."\n";
		echo '				<td>&nbsp;</td>'."\n";
		echo '				<td colspan="2">'."\n";
		require("/home/praktika/etc/bearbeitungsstatus.inc.php");
		echo '				</td>'."\n";
		echo '			</tr>'."\n";
		$ds_count += 1;
	}

	echo '		</table>'."\n"; 
	*/