<?
# $sprachbasisdatei = "/home/firmen/myberwerb_weiterl.phtml";

require("/home/praktika/etc/gb_config.inc.php");

if (!isset($_SESSION['s_loggedin']) || $_SESSION['s_loggedin'] == false) {
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/login');
}

if (isset($_SESSION['backID'])) {
	unset($_SESSION['backID']);
}

if (isset($_SESSION['backSpalte'])) {
	unset($_SESSION['backSpalte']);
}

if (isset($_SESSION['datei'])) {
	unset($_SESSION['datei']);
}

$_SESSION['restricted'] = RESTRICTED_COMPANY;
$_SESSION['current_page'] = PAGE_RECRUITING_CENTER;
$_SESSION['current_user_group'] = USER_GROUP_COMPANIES;

if (isset($_GET['id'])) {
	$bid = intval($_GET['id']);
} else {
	$bid = intval($_POST['id']);
}

// Weiterleitung soll versendet werden!
if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest" && $_POST["act"] == "send") {
	if(empty($_POST["email"])) {
		echo "Wir brauchen eine g&uuml;ltige eMail Adresse";
		exit();
	}

	if(!empty($_POST["adressbuch"])) {
		$objAdressbuch = new Praktika_Firmen_Adressbuch();
		$record = $objAdressbuch->getRecordById($_POST["adressbuch"]);
		
		if($record["email"] == $_POST["email"]) {
			$_POST["name"] = $record["name"];
		}
	} else {
		$_POST["name"] = "";
	}
	
	$objBewerbung = new Praktika_Kcenter_Bewerbung($bid, $_SESSION["s_firmenid"]);

	if(!$objBewerbung->checkAuth()) {
		echo "Sie besitzen nicht die notwendigen Zugriffsrechte!";
		exit();
	}
	$result = $objBewerbung->weiterleitung($_POST["email"], $_POST["name"], $_POST["kommentar"]);
	
	if($result === true) {
		echo "ok";
	} else {
		if(is_string($result)) {
			echo $result;
		} else {
			echo "Es trat ein unvorhergesener Fehler auf!";
		}
	}
	exit();
}

// Wenn nicht per Ajax, dann bitte PageHeader ausgeben
if(!$_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
	pageheader(array('page_title' => ''));
} else {
?>
<div style="width:600px;" class="sb-weiterleitung">
<style type="text/css">
	div.sb-weiterleitung #emailselect {
		width:300px;
        margin-top:10px;
	}
	div.sb-weiterleitung #email,div.sb-weiterleitung #kommentar {
		width:290px;
        margin-top:5px;
	}
	div.sb-weiterleitung label {
		width:30% !important;	
	}

</style>
<?php 	
}

if (isset($_POST['send'])) {
	if (empty($_POST['email']) && empty($_POST['emailselect'])) {
#		echo '<p class="error">Bitte w&auml;hlen Sie einen Empf&auml;nger aus dem Adressbuch oder geben Sie die eMail-Adresse an.</p>'."\n";
#		echo '<p><a href="'.$_SERVER['PHP_SELF'].'">zur&uuml;ck</a></p>'."\n";
	} else {
		$nutzersel = mysql_query('SELECT bewerbung.nutzerid, bewerbung.firmenid, bewerbung.prakstellenid, bewerbung.spalte, bewerbung.b_schreiben, bewerbung.bewerber_lesebestaetigt, UNIX_TIMESTAMP(bewerbung.modify) AS modifyunix,
			firmen.firma, firmen.strasse, firmen.ort, firmen.plz
			FROM prakt2.bewerbung `bewerbung`
			LEFT JOIN prakt2.firmen `firmen` ON(firmen.id = '.$_SESSION['s_firmenid'].')
			
			WHERE bewerbung.id = '.$id.' AND bewerbung.spalte = '.$spalte.' AND bewerbung.firmenid = '.$_SESSION['s_firmenid'].' AND bewerbung.inactive = "false"',$praktdbslave);

		$eintragfetch = mysql_fetch_assoc($nutzersel);
		
		$nutzerid = $eintragfetch["nutzerid"]; // mysql_result($nutzersel, 0, 'nutzerid');
		$firmenid = $eintragfetch["firmenid"]; // mysql_result($nutzersel, 0, 'firmenid');
		$stellenid = $eintragfetch["prakstellenid"]; //mysql_result($nutzersel, 0, 'prakstellenid');
		// $firma = mysql_db_query($database_db,'SELECT firma, strasse, plz, ort FROM firmen WHERE id = '.$_SESSION['s_firmenid'],$praktdbslave);
		// $firmenfetch = mysql_fetch_array($firma);
	
		// $taetigkeitsel = mysql_db_query($database_db,'SELECT taetigkeit FROM stelle WHERE firmenid = '.$_SESSION['s_firmenid'].' AND id = '.$stellenid,$praktdbslave);
	
		/* if ($taetigkeitsel != false && mysql_num_rows($taetigkeitsel) > 0) {
			$stellenfetch = mysql_fetch_array($taetigkeitsel);
		} */
	
		/* Infomail an Firma basteln*/
		if (!empty($_POST['emailselect']) && $_POST['emailselect'] != '-1') { 
			$adbooksel = mysql_fetch_assoc(mysql_query('SELECT email, vname, name FROM prakt2.adressbuch WHERE id = '.intval($_POST['emailselect']).' AND loginid = '.$_SESSION['s_loginid'].' AND gruppe = "firma"', $praktdbslave));
			
			$to = $adbooksel['email'];
			$to_name = $adbooksel['vname'].' '.$adbooksel['name'];
		} else { 
			$to = $_POST['email']; 
			$to_name = 'Bewerbungsempf�nger';
		}
	
		// Absender ermitteln
		$querystring = 'SELECT id, anrede, vname, name, email FROM bearbeiter WHERE id = '.$_SESSION['s_loginid'].' AND firmenid = '.$_SESSION['s_firmenid'];

		if($to_name == "Bewerbungsempf�nger") {
			$empf = $_POST['email'];
		} else {
			$empf = $to_name." (".$_POST['email'].")";
		}
		$sql = "INSERT INTO prakt2.bewerbung_forward SET bid = ".$id.", email = '".mysql_real_escape_string($empf)."', date = NOW()";
		$hDB->query($sql, $praktdbslave);
		
		// datensatz abfragen //
		$result = mysql_db_query($database_db,$querystring,$praktdbslave);
	
		// array erzeugen //
		$queryresult = mysql_fetch_assoc($result);
	
		$from_name = $queryresult['vname'].' '.$queryresult['name'];
	
		$from = $queryresult['email'];
	
		$mailstring = '';
		
		if (!empty($kommentar)) {
			$mailstring .= 'Kommentar von '.$queryresult['vname'].' '.$queryresult['name'].':\n '.$kommentar."\n";
			$mailstring .= '----------------------------------------------------------------'."\n\n\n\n";
		}
	
		$extbewerb = 'yes';
		$mail = 'yes';
	
		$query = 'SELECT lebenslauflayout FROM prakt2.bewerbung WHERE nutzerid = '.$nutzerid.' AND id = '.$id;
		$rs = mysql_query($query, $praktdbslave);
		$layoutid = mysql_result($rs, 0,'lebenslauflayout');

		include('/home/praktika/public_html/home/praktikanten/profil/lebenslauf_pdf/lebenslauf_functions.inc.php');
	
		$pdffilename = getPDFFile($nutzerid, $layoutid); 
	
		$querystring = 'SELECT * FROM nutzer WHERE id = '.$nutzerid;
	
		// datensatz abfragen //
		$result = mysql_query($querystring,$praktdbslave);
		
		// array erzeugen //
		$fetch = mysql_fetch_array($result);
	
		$mailstring .= (!empty($fetch['titel']) ? $fetch['titel'].' ' : '').$fetch['vname'].' '.$fetch['name']."\n";
		$mailstring .= $fetch['strasse']."\n";
		$mailstring .= $fetch['plz'].' '.$fetch['ort']."\n";
		$mailstring .= $fetch['tel']."\n";
		$mailstring .= $fetch['email']."\n\n\n\n";
		
		$mailstring .= $firmenfetch['firma']."\n".$queryresult['anrede'].' '.$queryresult['vname'].' '.$queryresult['name']."\n";
		$mailstring .= $firmenfetch['strasse']."\n".$firmenfetch['plz'].' '.$firmenfetch['ort']."\n\n\n";
	
		if ($eintragfetch['spalte'] == SECTION_PRAKTIKUM) {
			$betreff = SECTION_PRAKTIKUM_ALPHA;
		} elseif ($eintragfetch['spalte'] == SECTION_DIPLOM) {
			$betreff = SECTION_DIPLOM_ALPHA;
		} elseif ($eintragfetch['spalte'] == SECTION_NEBENJOB) {
			$betreff = SECTION_NEBENJOB_ALPHA;
		} elseif ($eintragfetch['spalte'] == SECTION_AUSBILDUNG) {
			$betreff = SECTION_AUSBILDUNG_ALPHA;
		} elseif ($eintragfetch['spalte'] == SECTION_BERUFSEINSTIEG) {
			$betreff = SECTION_BERUFSEINSTIEG_ALPHA;
		} elseif ($eintragfetch['spalte'] == SECTION_PROJEKT) {
			$betreff = SECTION_PROJEKT_ALPHA;
		} elseif ($eintragfetch['spalte'] == SECTION_TRAINEE) {
			$betreff = SECTION_TRAINEE_ALPHA;
		}
	
		$mailstring .= 'Bewerbung um einen '.$betreff.' vom '.strftime('%x',$eintragfetch['modifyunix'])."\n";
	
		if (!empty($stellenfetch['jobcode'])) {
			$mailstring .= 'Jobcode: '.$stellenfetch['jobcode']."\n";
		}
		
		if (!empty($stellenfetch['taetigkeit'])) {
			$mailstring .= '('.$stellenfetch['taetigkeit'].')'."\n";
		}
	
		$mailstring .= "\n\n";
	
		$mailstring .= $eintragfetch['b_schreiben'];
	
		$subject = 'Bewerbung um einen '.$betreff.' bei '.$firmenfetch['firma'].' &uuml;ber praktika.de';
	
		if (!empty($stellenfetch['jobcode'])) {
			$subject .= ' - Jobcode: '.$stellenfetch['jobcode'];
		}
	
		$message = $mailstring;
	
		include "../email/mimemail.inc.php";
	
		$mailhinweis = $language['strMailHinweis'];
	
		$m = new CMIMEMail($to_name.' <'.$to.'>',$from_name.' <'.$from.'>',$subject);
		$m->mailbody($message."\n\n".$mailhinweis."\n");
	
		$modify = date("YmdHis");
	
		$m->attachFile_raw($pdffilename, 'Lebenslauf.pdf', 'application/pdf');
	
		$m->send();
	
		unlink($pdffilename);
	
		// Bewerber benachrichtigen
		if ($eintragfetch['bewerber_lesebestaetigt'] == 'false') {
			$update = mysql_db_query($database_db,'UPDATE bewerbung SET bewerber_lesebestaetigt = \'true\' WHERE id = '.$id.' AND spalte = '.$s_spalte.' AND firmenid = '.$_SESSION['s_firmenid'].' AND inactive = \'false\'',$praktdbmaster);
	
			/* Mail an den Bewerber */
			$beweber = mysql_db_query($database_db,'SELECT * FROM nutzer WHERE id = '.$nutzerid.' AND email != \'\' AND inactive = \'false\'',$praktdbslave);
	
			if (mysql_num_rows($beweber) != 0) {
				$bmail = mysql_result($beweber,0,'email');
				
				/* Best&auml;tigungsmail zusammenbauen */
				$subject = 'Wichtig - Ihre Bewerbung auf Praktika.de';
				if (mysql_result($beweber,0,'anrede') == 'Frau') {
					$Anrede = 'geehrte';
				} else {
					$Anrede = 'geehrter';
				}
				
				$mailstring = 'Sehr '.$Anrede.' '.mysql_result($beweber,0,'anrede').' '.mysql_result($beweber,0,'vname').' '.mysql_result($beweber,0,'name').','."\n\n";
				$mailstring = $mailstring. "nur praktika.de informiert Sie &uuml;ber Ihren Bewerbungsstatus!\n\n";
				$mailstring = $mailstring.'Sie haben sich &uuml;ber praktika.de bei dem Unternehmen "'.$firmenfetch['firma'].'" beworben.'."\n";
				$mailstring = $mailstring.'Soeben hat das Unternehmen Ihre Bewerbung gelesen.'."\n\n";
				$mailstring = $mailstring.'F&uuml;r die weitere Bearbeitung ist das Unternehmen verantwortlich. praktika.de kann ab diesem Zeitpunkt nicht mehr auf den Bearbeitungsprozess einwirken.'."\n\n";
				$mailstring = $mailstring.'Erfahrungsgem&auml;&szlig; kann es bis zu 8 Werktagen dauern, bevor Sie eine entsprechende Antwort von dem Unternehmen erhalten.'."\n\n";
				$mailstring = $mailstring.'Das praktika.de Team w&uuml;nscht Ihnen viel Erfolg.'."\n\n\n";
				$mailstring = $mailstring.'---------------------------'."\n";
				$mailstring = $mailstring.'praktika.de - die Karriereplattform f&uuml;r den Berufseinstieg'."\n".'Wir halten Unternehmen jung!'."\n\n";
				$mailstring = $mailstring.'praktika.de einen Freund empfehlen: http://praktika.de/home/sendtofriend/'."\n";
				$mailstring = $mailstring.'Newsletter abonnieren: http://praktika.de/home/newsletter/'."\n";
				$mailstring = $mailstring.'Hinweise und Anregungen schreiben: http://praktika.de/home/feedback/'."\n";
				
				/* Bestaetigunsmail verschicken */
				$replaymail = 'IhreBewerbung@praktika.de';
				
				mail($bmail, $subject, $mailstring, "From: $replaymail\nX-Mailer: praktika.de Mailscript PHP\nX-Priority: 1\nX-MSMail-Priority: High\nImportance: High"); 
			}
		}

		if (isset($_SESSION['delete_detail_refer']) && $_SESSION['delete_detail_refer'] == true) {
			unset($_SESSION['detail_refer']);
			unset($_SESSION['detail_refer_nutzer']);
			unset($_SESSION['detail_refer_id']);
			unset($_SESSION['detail_refer_spalte']);
			unset($_SESSION['delete_detail_refer']);
		}
	
		echo '<p class="success">'.$language['strVersendet'].'</p>'."\n";
		echo '<script type="text/javascript">'."\n";
		echo '	document.write = setTimeout(\'top.GB_hide()\', 3000);'."\n";
		echo '</script>'."\n";	
	}
} else {
?>
<form action="#" method="post">
<div style="padding:10px;">
<h2>Bewerbung weiterleiten</h2>
<div id='status-pending' style='text-align:center; display:none;'><img src='/styles/images/ajax/loading_2.gif' alt='Bewerbung wird versendet!' /></div>
<p class="hint error" style='display:none;' id='weiterleitung-result'></p><br />
	<fieldset style="margin:0px;">
<!--		<p>
			<label for="emailselect">
				Empf&auml;nger Adressbuch<br />
				<a onclick="return bcenter.showAdressbuch(<?php echo $_GET["id"]; ?>,'weiterleitung');" href="/recruiting/adressbuch/<?php echo (int)$_GET["id"]; ?>/">Adressbuch verwalten</a>
			</label>
			<select name="emailselect" id="emailselect" onchange="email_completion(this.value)">
				<option value="-1">---</option>
<?php 
/*	$rs = mysql_db_query($database_db,'SELECT id,vname,name,email FROM adressbuch WHERE loginid = '.$_SESSION['s_loginid'].' AND gruppe = \'firma\' ORDER BY name',$praktdbslave);
	$num = mysql_num_rows($rs);

	$emailNames = '';
	$i = 0;
	while ($i < $num) { 
		$emailNamesID = mysql_result($rs,$i,'id');
		$emailNamesVorname = mysql_result($rs,$i,'vname');
		$emailNamesName = mysql_result($rs,$i,'name');
		$emailNamesEmail = mysql_result($rs,$i,'email');
		
		echo '<option value="'.$emailNamesID.'">'.$emailNamesName.'</option>'."\n";
		$i++;

		$emailNames .= '	eMailNames['.$emailNamesID.'] = \''.$emailNamesEmail.'\';'."\n";
	}
	
	if ($num == 0) {
		$querystring = 'SELECT * FROM bearbeiter WHERE id = '.$_SESSION['s_loginid'].' AND firmenid = '.$_SESSION['s_firmenid'];
		$result = mysql_db_query($database_db,$querystring,$praktdbslave);
		$queryresult = mysql_fetch_array($result);
		$defaultmail = $queryresult['email'];
	}*/
?>	
<? #			</select>
#		</p> ?>-->
		<p style="clear: both; margin-top:15px;">
			<label for="email">Empf&auml;nger eMail</label>
			<input class="stdStyle" type="text" id="email" name="email" value="<?php echo (isset($defaultmail) ? $defaultmail : '') ?>" />
		</p>
		<p style="margin-top:5px;">
			<label for="kommentar">Kommentar</label>
			<textarea class="stdStyle" id="kommentar" name="kommentar" cols="20" rows="8"></textarea>
		</p>
	</fieldset>
	</div>
	<fieldset class="smallbox_footer" style="text-align:center;">
		<p>
			<a href='#' style='margin-left:0px;float:none;' onclick='bcenter.closeWeiterleitung();return false;' class='button red small'>Abbrechen</a>
            <a href='#' style="margin-left:20px;float:none;" onclick="return bcenter.sendWeiterleitung($('#email').val(),$('#emailselect').val(),$('#kommentar').val(),'<?php echo $bid ?>');return false;" class='button green small'>Bewerbung weiterleiten</a>
			<input type="hidden" name="id" value="'.$id.'" />
			<input type="hidden" name="spalte" value="'.$spalte.'" />
<?php 	
	if (isset($_SESSION['detail_refer']) && $_SESSION['detail_refer'] == true) {
		echo '			<button type="button" name="details" onclick="location.href=\'/recruiting/kontaktdetails/'.$_SESSION['detail_refer_nutzer'].'/'.$id.'/'.$spalte.'/\'" value="zur&uuml;ck"><span><span><span>ZUR&Uuml;CK</span></span></span></button>'."\n";
	}

	echo '		</p>'."\n";
	echo '	</fieldset>'."\n";
	echo '</form>'."\n";

	if ($i != 0) {
		echo '<script type="text/javascript">'."\n";
		echo '	var eMailNames = Array();'."\n";
		echo $emailNames;
		echo '	function email_completion(id) {'."\n";
		echo '		if(id != -1) document.getElementById(\'email\').value = eMailNames[id];'."\n";
		echo '	}'."\n";
		echo '</script>'."\n";
	}
}
?></div><?php 

if(!$_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
	bodyoff();
}
?>