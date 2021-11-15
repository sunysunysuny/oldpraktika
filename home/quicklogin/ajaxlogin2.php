<?php
ob_start();
$sprachbasisdatei = "/home/praktikanten/mitglied.phtml";
$sprachbasisdatei2="/home/quicklogin/index.phtml";
require("/home/praktika/etc/gb_config.inc.php");

$laender = Praktika_Listen::getLand(array("emptyText" => true));
$captchaWord = "prakt_de";

if(!empty($_POST["step"]) && $_POST["step"] == "2") {

    $_POST["email"] = $_POST["email_small"];
    $_POST["passwort"] = $_POST["passwort_small"];

    if(Praktika_Validate::checkMailForRegistration($_POST["email"]) > 0) {
        echo "false|eMail Adresse schon vergeben".Praktika_Validate::checkMailForRegistration($_POST["email"]);
        exit();
    } else {
        $user = Praktika_User::register($_POST);
        // Templatesystem vorbereiten
        $verifykeyresults = $hDB->query('SELECT anrede, name, vname, email, id_md5 AS verifykey FROM '.$database_db.'.nutzer WHERE id = '.$user, $praktdbmaster);

        $hDB->query("INSERT INTO prakt2_temp.register_variation SET nutzerid = '$user', variation = '".(int)$_POST["variation"]."'", $praktdbmaster);
        // datensaetze gefunden ? //
        $verifykeynum_rows = mysql_num_rows($verifykeyresults);

        if ($verifykeynum_rows > 0) {
                $verifykeydaten = mysql_fetch_array($verifykeyresults);
        }

        $emailobj = new emailtemplate(5);
        
        // neues Emailsystem nutzen
        $aktstunde = date('H');
        if ($aktstunde < 10) {
                $begruessung = 'Guten Morgen';
        } elseif ($aktstunde > 19) {
                $begruessung = 'Guten Abend';
        } else {
                $begruessung = 'Hallo';
        }

        $begruessung .= ' '.$verifykeydaten['anrede'].' '.$verifykeydaten['name'];
        
        // Ersetzungen durchf&uuml;hren
        $mailto = $verifykeydaten["email"];
        $emailobj->replace('{ansprache}',$begruessung);
        $emailobj->replace('{email}',$verifykeydaten["email"]);
        $emailobj->replace('{id}', $user);
        $emailobj->replace('{verifykey}',$verifykeydaten['verifykey']);

        if (!isset($_POST['passwd'])) {
            $emailobj->replace('{passwort}',$_POST["passwort"]);
	} else {
            $emailobj->replace('{passwort}',"nicht mitgesendet");
	}

        // Bestaetigunsmail verschicken
        
        $emailobj->send_email($mailto);
        echo $mailto;
        echo "true|$user|/home/quicklogin/ajaxlogin3.php";
    }

    exit();
}
// Ist Mail schon vorhanden
if(Praktika_Validate::checkMailForRegistration($_POST["email"]) > 0) {
    ?>
    <script type="text/javascript">
        function loadFirst() {
              smallboxManager.boxes[0].loadUrl('', '/home/quicklogin/ajaxlogin.php', '', {
                    title:'Um alle Funktionen zu nutzen - melde Dich an! Schritt 1 / 3'
              });
        }
    </script>
    <p class='error'>eMail Adresse schon vergeben</p>
    <br />
    <div style="text-align:center;">
        <button onclick='loadFirst();'><span><span><span>zur&uuml;ck</span></span></span></button>
    </div>
    <br />
    <?
   exit();
}

if (isset($_POST['land'])) {
    $landid = $_POST['land'];
} else {
    $landid = 68;
}
$bundeslaender = Praktika_Listen::getBundesland($landid, array("emptyText" => true));

if (count($bundeslaender) > 0 && !empty($bundesland)) {
    $grossraeume = Praktika_Listen::getGrossraum($bundesland, array("emptyText"=>true));

//		$grossraumselect = mysql_db_query($database_db,'SELECT grossraum,id FROM grossraum WHERE bundesland='.(isset($_POST['bundesland']) ? $_POST['bundesland'] : mysql_result($bundeslandselect,0,'id')).' ORDER BY grossraum',$praktdbslave);
//		$anz_grossraum = mysql_num_rows($grossraumselect);
}
	$karierrestats = mysql_query('SELECT '.$_SESSION['s_sprache'].',id FROM '.$database_db.'.karierrestatus ORDER BY anzeigeorder',$praktdbslave);
	$anz_karierrestatus = mysql_num_rows($karierrestats);

	$studiumsel = mysql_db_query($database_db,'SELECT studiengang,id FROM studiengaenge ORDER BY studiengang',$praktdbslave);
	$anz_studium = mysql_num_rows($studiumsel);

?>
<script type="text/javascript">
    function submits() {
        var required = ['variation', 'email_small','passwort_small','anrede','vname','name','strasse','plz','ort','land','bundesland','grossraum','karrierestatus','studiengang','hochschule','semester'];
        var optional = [];

        var query = [];
        var error = false;

        query.push("step=2");
        if($('passwd').checked) {
            query.push("passwd=1");
        }

        for(a=0;a<required.length;a++) {
            if($(required[a]).value == "") {
                if($('label_' + required[a]) != undefined) {
                    addClass($('label_' + required[a]),"small_error");
                    error = true;
                }
            } else {
                if($('label_' + required[a]) != undefined) {
                    if(hasClass($('label_' + required[a]),"small_error")) {
                        removeClass($('label_' + required[a]),"small_error");
                    }
                }

                query.push(required[a] + "=" + escape($(required[a]).value));
            }
        }
        for(a=0;a<optional.length;a++) {
            query.push(optional[a] + "=" + escape($(optional[a]).value));
        }

        if($('agbok').checked == false) {
            addClass($('label_agbok'), 'small_error');
            error = true;
        } else {
                
                if(hasClass($('label_agbok'),"small_error")) {
                    removeClass($('label_agbok'),"small_error");

                }

            query.push("agbok=1");
        }
        
        if($('geb_tag').value == "" || $('geb_monat').value == "" || $('geb_jahr').value == "") {
            addClass($('label_geb_tag'), 'small_error');
            error = true;
        } else {
                 if(hasClass($('label_geb_tag'),"small_error")) {
                    removeClass($('label_geb_tag'),"small_error");
                }
                query.push("geb_tag=" + escape($('geb_tag').value));
                query.push("geb_monat=" + escape($('geb_monat').value));
                query.push("geb_jahr=" + escape($('geb_jahr').value));
        }

        // Wenn Fehler => Nicht weitermachen
        if(error == true) return;

        try {
            if(pageTracker != undefined) {
                pageTracker._trackPageview("/smallbox/login/step3/<?=(int)$_POST["var"] ?>");
            }
        }catch(err) { }

        xhr("/home/quicklogin/ajaxlogin2.php", query.join("&"), function(text) {
            parts = text.split("|");

            if(parts[0] == "false") {
                $('smallbox_register_result').innerHTML = parts[1];
            } else {
                
                smallboxManager.boxes[0].loadUrl('', "/home/quicklogin/ajaxlogin3.php", 'u=' + parts[1], {
                    title:'Um alle Funktionen zu nutzen - melde Dich an! Schritt 1 / 3'
                });
            }
        });
    }
</script>
<style type="text/css">

</style>
        <div style="width:672px; height:415px; margin-left:20px;" class="smallbox_register_2">

        <form style="width:692px !important;" action="<?php echo $_POST["siteDest"]; ?>" method="post" onsubmit="return false;">
            <input type="hidden" id="passwort_small" name="passwort" value="<?=htmlentities($_POST["passwort"]) ?>" />
            <input type="hidden" id="email_small" name="email" value="<?=htmlentities($_POST["email"]) ?>" />
            <input type="hidden" id="variation" name="variation" value="<?=(int)$_POST["var"] ?>" />
            <div style="float:right; height:100%; width:260px; margin-right:25px;">
			<p class="checkboxes" style="margin:20px 0px; line-height:16px;">
				<input type="checkbox" value="true" id="agbok" name="agbok" <?=((isset($_POST['agbok']) && ($_POST['agbok'] == 'true')) ? ' checked="checked"' : '') ?> />
				<label style="width:90% !important;" id="label_agbok" for="agbok" <?=((isset($_POST['save']) && !isset($_POST['agbok'])) ? ' style="color: #ff0000 !important;"' : '') ?>>Um praktika.de nutzen zu k&ouml;nnen, m&uuml;ssen Sie die <a href="/cms/Nutzungsbedingungen.274.0.html" target="_blank">Nutzungsbedingungen</a> lesen und best&auml;tigen!</label>
			</p>
			<p class="checkboxes" style="margin:20px 0px; line-height:16px;">
				<input type="checkbox" value="true" checked="checked" id="passwd" name="passwd" <?=((isset($_POST['passwd']) && ($_POST['passwd'] == "true")) ? ' checked="checked"' : '') ?> />
				<label style="width:90% !important;"  for="passwd">Mein Passwort in der Best&auml;tigungsmail aus Sicherheitsgr&uuml;nden nicht mit senden. F&uuml;r diesen Fall merken Sie sich bitte Ihr Passwort!</label>
			</p>
                        <p id="smallbox_register_result" style="margin-top:100px; text-align:center; font-weight:bold; font-size:14px;">&nbsp;</p>
                        <div style="text-align:center; margin-top:90px;">
                            <button onclick="submits();"><span><span><span>Anmeldung abschicken</span></span></span></button>
                        </div>
            </div>

            <div style="padding:5px 16px 16px 5px; padding-left:13px; width:335px;">
                  <fieldset id="smallboxRegistration">
                      <legend></legend>
			<p>
				<label for="anrede" id="label_anrede" <?=((isset($_POST['save']) && isset($_POST['anrede']) && $_POST['anrede'] == '-1') ? ' class="error"' : '') ?>>Anrede:<sup>*</sup></label>
				<select id="anrede" name="anrede">
					<option value="">----------------</option>
					<option <?=((isset($_POST['anrede']) && $_POST['anrede'] == 'Herr') ? 'selected="selected" ' : '') ?>value="Herr">Herr</option>
					<option <?=((isset($_POST['anrede']) && $_POST['anrede'] == 'Frau') ? 'selected="selected" ' : '') ?>value="Frau">Frau</option>
				</select>
			</p>
			<p>
				<label for="vname" id="label_vname" <?=((isset($_POST['save']) && isset($_POST['vname']) && empty($_POST['vname'])) ? ' class="error"' : '') ?>>Vorname:<sup>*</sup></label>
				<input type="text" id="vname" name="vname" maxlength="50" value="<?=(isset($_POST['vname']) ? $_POST['vname'] : '') ?>" style="width:210px;" />
			</p>
			<p>
				<label for="name" id="label_name" <?=((isset($_POST['save']) && isset($_POST['name']) && empty($_POST['name'])) ? ' class="error"' : '') ?>>Nachname:<sup>*</sup></label>
				<input type="text" id="name" name="name" maxlength="50" value="<?=(isset($_POST['name']) ? $_POST['name'] : '') ?>" style="width:210px;" />
			</p>
			<p>
				<label for="strasse" id="label_strasse"<?=((isset($_POST['save']) && isset($_POST['strasse']) && empty($_POST['strasse'])) ? ' class="error"' : '') ?>>Stra&szlig;e:<sup>*</sup></label>
				<input type="text" id="strasse" name="strasse" maxlength="50" value="<?=(isset($_POST['strasse']) ? $_POST['strasse'] : '') ?>" style="width:210px;" /><span class="validatorIcon" id="result_check_street"></span>
			</p>
			<p id="smallbox_plz">
				<label for="plz"  id="label_plz"<?=((isset($_POST['save']) && ((isset($_POST['plz']) && empty($_POST['plz'])) || (isset($_POST['ort']) && empty($_POST['ort'])))) ? ' class="error"' : '') ?>>Postleitzahl:<sup>*</sup></label>
				<span id='adresse_ort'><input onkeyup="autoCompleter.get(this.value);" autocomplete="off" type="text" id="plz" style="width:210px !important; text-align:left !important;"  maxlength="50" value="<?=(isset($_POST['plz']) ? $_POST['plz'] : '') ?>" name="plz" maxlength="7" value="<?=(isset($_POST['plz']) ? $_POST['plz'] : '') ?>" /></span>
			</p>

                        <p>
				<label for="ort" id="label_ort"<?=((isset($_POST['save']) && ((isset($_POST['plz']) && empty($_POST['plz'])) || (isset($_POST['ort']) && empty($_POST['ort'])))) ? ' class="error"' : '') ?>>Ort:<sup>*</sup></label>
				<span class="validatorIcon" id="result_check_plz" style="display:none;"></span><span id='adresse_ort' ><input type="text"style="margin:0px !important; width:210px !important;" onkeyup="autoCompleter.get(this.value);" id="ort" name="ort" maxlength="50" value="<?=(isset($_POST['ort']) ? $_POST['ort'] : '') ?>" /></span>
			</p>

			<p id="captchaParagraph">
				<label for="captcha" <?=((isset($_POST['save']) && (isset($_POST['captcha']) && empty($_POST['captcha']))) ? ' class="error"' : '') ?>>Zum Spamschutz, geben Sie in das folgende Feld "<i><?=$captchaWord ?></i>" ein!.</label>
				<input type="text"  id="captcha" name="captcha" maxlength="50" value="<?=(isset($_GET['captcha']) ? $_GET['captcha'] : '') ?>" />
			</p><script type="text/javascript"> document.getElementById("captcha").value = "<?=$captchaWord ?>"; document.getElementById("captchaParagraph").style.display="none";</script>

                       	<p>
				<label for="land" id="label_land">Land:<sup>*</sup></label>
				<select id="land" name="land" style="width:230px;">
	<?
	for($a = 0; $a < count($laender); $a++) {
						echo '<option '.(($laender[$a]["id"] == $landid) ? 'selected="selected" ' : '').'value="'.$laender[$a]["id"].'">'.htmlentities($laender[$a]["title"]).'</option>';
	}
	?>
				</select>
			</p>
			<p>
				<label for="bundesland" id="label_bundesland">Bundesland:</label>
				<select id="bundesland" name="bundesland" style="width:230px;">
	<?
	for($a = 0; $a < count($bundeslaender); $a++) {
						echo '<option '.((isset($_POST['bundesland']) && $bundeslaender[$a]["id"] == $_POST['bundesland']) ? 'selected="selected" ' : '').'value="'.$bundeslaender[$a]["id"].'">'.htmlentities($bundeslaender[$a]["title"]).'</option>';
	}
	?>
				</select>
			</p>
			<p>
				<label for="grossraum" id="label_grossraum">Gro&szlig;raum:</label>
				<select id="grossraum" name="grossraum" style="width:230px;">
	<?
	for($a = 0; $a < count($grossraeume);$a ++) {
						echo '<option '.(($grossraeume[$a]["id"] == $_POST['grossraum']) ? 'selected="selected" ' : '').'value="'.$grossraeume[$a]["id"].'">'.htmlspecialchars($grossraeume[$a]["title"]).'</option>';
	}
	?>
		</select>
	</p>
			<p>
				<label for="geb_tag"  id="label_geb_tag"<?=((isset($_POST['save']) && ((isset($_POST['geb_tag']) && $_POST['geb_tag'] == '-1') || (isset($_POST['geb_monat']) && $_POST['geb_monat'] == '-1') || (isset($_POST['geb_jahr']) && $_POST['geb_jahr'] == '-1'))) ? ' class="error"' : '') ?>>	Geburtsdatum:<sup>*</sup></label>
				<select id="geb_tag" name="geb_tag" class="day">
					<option value="">--</option>

	<? for ($i = 1; $i <= 31; $i++) {
						echo' <option value="'.$i.'"';
		if (isset($_POST['geb_tag']) && $i == $_POST['geb_tag']) {
			 echo 'selected="selected"';
		}
		echo '>'.$i.'</option>';
	}
?>
				</select>
				<select id="geb_monat" name="geb_monat" class="month">
					<option value="">--</option>

<?	for ($i = 1; $i <= 12; $i++) {
		echo '<option value="'.$i.'"';
		if (isset($_POST['geb_monat']) && $i == $_POST['geb_monat']) {
			echo' selected="selected"';
		}
		echo '>'.$i.'</option>';
	} ?>

				</select>
				<select id="geb_jahr" name="geb_jahr" class="year">
					<option value="">--</option>

	<? for ($i = date("Y")-67; $i <= date("Y")-13; $i++) {
		echo '<option value="'.$i.'"';
		if (isset($_POST['geb_jahr']) && $i == $_POST['geb_jahr']) {
			echo 'selected="selected"';
		}
		echo '>'.$i.'</option>';
	} ?>

				</select>
			</p>
			<p>
				<label for="karrierestatus" id="label_karrierestatus">Karrierestatus:<sup>*</sup></label>
				<select id="karrierestatus" name="karierrestatus" style="width:230px;">
					<option value="">- - - Bitte ausw&auml;hlen - - -</option>
	<? $i = 0;
	while ($i < $anz_karierrestatus) {
		$html = htmlspecialchars(mysql_result($karierrestats,$i,$_SESSION['s_sprache']));
						echo '<option ';
		if (isset($_POST["karierrestatus"]) && mysql_result($karierrestats,$i,"id") == $_POST["karierrestatus"]) {
			echo 'selected="selected" ';
		} 
		echo "value='".mysql_result($karierrestats,$i,"id")."'>".$html.'</option>';
		$i++;
	} ?>
				</select>
			</p>
			<p>
				<label for="studiengang" id="label_studiengang">Studium:<sup>*</sup></label>
				<select id="studiengang" name="studiengang" style="width:230px;">
					<option value="">- - - Bitte ausw&auml;hlen - - -</option>
	<?
        $i = 0;
	while ($i < $anz_studium) {
		$html = htmlspecialchars(mysql_result($studiumsel,$i,'studiengang'));
				echo '<option ';
		if (isset($_POST["studiengang"]) && mysql_result($studiumsel,$i,"id") == $_POST["studiengang"]) {
			echo 'selected="selected" ';
		}
		echo "value='".mysql_result($studiumsel,$i,"id")."'>".$html.'</option>';
		$i++;
	} ?>
				</select>
			</p>
			<p>
				<label for="hochschule" id="label_hochschule">Hochschule:<sup>*</sup></label>
				<input type="text" id="hochschule" style="width:210px !important;"  name="hochschule" maxlength="50" value="<?=(isset($_POST['hochschule']) ? $_POST['hochschule'] : '') ?>" />
			</p>
			<p>
				<label for="semester" id="label_semester">aktuelles Semester:<sup>*</sup></label>
				<input type="text" id="semester" name="semester" maxlength="2" value="<?=(isset($_POST['semester']) ? $_POST['semester'] : '') ?>" />
			</p>
            </div>
        </form>
        </div>
<script type="text/javascript">

                        autoCompleter.initAutoComplete($('smallbox_plz'),"/admin/ajax/einsatzort.phtml", function(value) {
				parts = value.split("-");

				xhr("/admin/ajax/einsatzort_info.phtml","plz=" + trim(parts[0]) + "&ort="+ encodeURI(trim(parts[1].split(",")[0])) ,function(text) {
					data = JSON.parse(text);
					$('land').value = data["land"];
					$('bundesland').value = data["bundesland"];

					$('ort').value = data["ort"];
					$('plz').value = data["plz"];

					event = new Object();
					event.type = "change";
					event.target = new Object();
					event.target.value = data["bundesland"];

					bundesland.handleEvent(event);
				});
                        });

                        bundesland.init();
</script>