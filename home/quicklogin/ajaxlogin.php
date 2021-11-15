<?php
ob_start();
$sprachbasisdatei = "/home/praktikanten/mitglied.phtml";
$sprachbasisdatei2="/home/quicklogin/index.phtml";
require("/home/praktika/etc/gb_config.inc.php");

//pageheader(array('page_title' => 'praktika.de - Login'));

// POST-Variablen
$login = isset($_POST["login"]) ? $_POST["login"] : "";
$password = isset($_POST["password"]) ? $_POST["password"] : "";

$err = array();
for ($i = 0; $i <= 1; $i++) {
	$err[] = false;
}
$checked = false;
$fehler = true;

if ( isset($_POST['send']) ) {
	// Checken der Variablen
	if ( empty($_POST['login']) ) $err[0] = true;
	if ( empty($_POST['password']) ) $err[1] = true;
	$checked = true;
	for ( $i = 0; $i < count($err); $i++ ) {
		if ($err[$i]) $checked = false;
	}
}

if ($checked) {
	if ( $logincheck = sessionnutzerlogin( $database_db, $login, $password ) == "true" ) {
		$fehler = false;
		$loginidemail = $login;
		$passwort = $password;
		
		require("/home/praktika/etc/login_bewerber.inc.php");

		if(!empty($_POST["siteDest"])) {
			header("location:".$_POST["siteDest"]);
			exit();
		}

			
		if (isset ($opener_ref)) {
			$zielurl = "'".$opener_ref."'";
		} else {
			$zielurl = "window.opener.parent.location.href";
		}

		//agge: kommt der login von einer unserer includes (besondere behandlung wegen firefox und opera)
		if (isset($_REQUEST['include'])) {
			$sonstige_headertags = '
			<script type="text/javascript">
			<!--
				window.top.location.href = top.location.href;
			//-->
			</script>';
		} else {
			$sonstige_headertags = '
			<script type="text/javascript">
			<!--
				setTimeout(\'top.location.reload()\', 2000);
			//-->
			</script>';
		}

		pageheader(array('page_title' => 'praktika.de - Login'));
?>
    <p><b>Ihr Login war erfolgreich!</b></p>
    <p><b>Das Fenster schlie&szlig;t sich automatisch!</b></p>
<?php
	} elseif ( $logincheck = sessionlogin( $database_db, $login, $password )  == "true" ) {
		$fehler = false;
		$_SESSION['s_loginid'] = $login;

		require("/home/praktika/etc/login_firmen.inc.php");

		if (isset($_POST['includeLogin'])) {
			$sonstige_headertags ='<script type="text/javascript">
			<!--
				function setLoc() {
					top.location.href = \'/recruiting/\';
				}
				
				setTimeout(setLoc(), 2000);
			//-->
			</script>';
		} else {
			$sonstige_headertags ='<script type="text/javascript">
			<!--
				setTimeout(\'top.location.reload()\', 2000);
			//-->
			</script>';
		}
		
		if(!empty($_POST["siteDest"])) {
			header("location:".$_POST["siteDest"]);
			exit();
		}
				
		pageheader(array('page_title' => 'praktika.de - Login'));
?>
    <p><b>Ihr Login war erfolgreich!</b></p>
    <p><b>Das Fenster schlie&szlig;t sich automatisch!</b></p>
<?php
	} else {
		$fehler = true;
	}

		if(!empty($_POST["siteDest"])) {
			header("location:".$_POST["siteDest"]);
			exit();
		}	
}

if ($fehler == true) {

// TODO - entfernen
if ($fehler == true && isset($_SESSION["s_loggedin"]) && $_SESSION["s_loggedin"] != true) {
	//pageheader(array('page_title' => 'praktika.de - Login'));

	// bei Fehlern
	
	if (isset($logincheck) && $logincheck == "false") $keinlogin = "yes";
	if (isset($logincheck) && $logincheck == "passwdfalsch") $passwdfalsch = "yes";
	if (isset($logincheck) && $logincheck == "gesperrt") $gesperrt = "yes";
	if (isset($passwdfalsch) && $passwdfalsch == "yes") $ausgabe = '<p class="error">'.$language["strpasswdfalsch"].'</p>';
	if (isset($gesperrt) && $gesperrt == "yes") $ausgabe = '<p class="error">'.$language["strGesperrt"].'</p>';
	if (isset($keinlogin) && $keinlogin == "yes") $ausgabe = '<p class="error">'.$language["strKeinLogin"].'</p>';
?>
            <?php if (isset($ausgabe)) echo $ausgabe; } ?>
<script type="text/javascript">

    function switchLoginOption(value) {
        value = ($('newmember').checked == true?2:1);
        if(value == 1) {
            $('passwort2').value = "Passwort";

            $('span_passwort2').style.display = "none";
            $('passwort_remember').style.display = "";
            $('smallboxLoginButton').innerHTML = "Einloggen";

            $('agb_ok').style.display = "none";

            $('sayPW').innerHTML = "Passwort eingeben";
        }
        if(value == 2) {
            $('passwort2').value = "Passwort";
            $('passwort_wdh').value = "Passwort";
            
            $('span_passwort2').style.display="";
            $('passwort_remember').style.display="none";

            $('smallboxLoginButton').innerHTML = "Registrieren";
            $('sayPW').innerHTML = "Lege Dein Passwort fest";
            $('agb_ok').style.display = "";
        }
    }

</script>
   
<div style="width:677px !important; height:415px; margin-right:20px; background-image:url(/styles/images/login/teaserB.png);">
    <form action="<?php echo $_POST["siteDest"]; ?>" method="post" onsubmit="return false;">
        <div style="float:right; height:100%; width:272px;">
            <p style="padding:21px; padding-bottom:10px; padding-left:0px;">
                &Uuml;ber 250.000 Studenten profitieren<br />bereits von ihrem PRAKTIKA Account!<br /><br />
                Werde auch Du jetzt Mitglied!
            </p>
            <div class='loginStatus' id="loginStatus" style="text-align:center; text-align:center; font-size:12px; font-weight:bold;">&nbsp;</div>
            <div style="padding:0px; width:260px;">
                <fieldset style="padding:5px; padding-right:0px !important; padding-left:2px;">
                    <legend></legend>
                    <b style="line-height:35px;">Wie lautet Ihre E-Mail-Adresse?</b>

                    <input type="text" onblur="if(this.value=='') { this.style.color='#999'; this.value='Email Adresse'; }" onfocus="if(this.value=='Email Adresse') { this.style.color='#000'; this.value = ''; }" name="username" id="username" value="Email Adresse" style="margin-left:25px;width:200px; color:#999; font-size:16px;" /><br />

                    <p style="line-height:20px;  margin-top:5px;">
                        <div style="width:266px; line-height:33px;">
                            <b>Haben Sie ein Passwort für praktika.de?</b><br />
                        </div>
                        <input onclick="switchLoginOption();" type="radio" name="state" id="newmember" value="2" /> <label onclick="switchLoginOption();" style="float:none; display:inline; width:auto;"for="newmember">Nein, ich bin ein neues Mitglied.</label>
                    </p>
                    <p style="line-height:33px; padding-bottom:4px;">
                        <input onclick="switchLoginOption();" checked="checked" type="radio" name="state" id="oldmember" value="1" /> <label onclick="switchLoginOption();" style="float:none; display:inline; width:auto;" for="oldmember">Ja, ich habe ein Passwort:</label>
                    </p>

                    <div id="span_passwort" style="font-size:9px; padding-left:25px;color:#777;">
                        <span id="sayPW">Lege Dein Passwort fest</span>:<br />
                        <input type="password" name="passwort2" onblur="if(this.value=='') { this.style.color = '#999';  this.value = 'Passwort';}" onfocus="if(this.value=='Passwort') { this.style.color='#000'; this.value = ''; }" id="passwort2" value="Passwort" style="width:200px; color:#999; font-size:16px;" /><br />
                    </div>

                    <div style="margin-top:5px; padding-left:25px;color:#777;" class="clearfix">
                        <span id="span_passwort2" style="font-size:9px;">
                            Wiederhole Dein Passwort:<br />
                            <input type="password"onblur="if(this.value=='') { this.style.color = '#999';  this.value = 'Passwort'; }" onfocus="if(this.value=='Passwort') {  this.style.color='#000'; this.value = ''; }" name="passwort" id="passwort_wdh" value="" style="width:200px; color:#999; font-size:16px;" />
                        </span>
                        <span style="float:right; line-height:30px; padding-bottom:24px;" id="passwort_remember">
                            <a class="stdStyle" rel="gb_page_center[500,250]" href="/home/quicklogin/passwdwindow.phtml">Passwort vergessen?</a>
                        </span>
                    </div>
                </fieldset>
            </div>
            <p class="checkboxes" id="agb_ok" style="display:none; margin:0px !important; line-height:16px; margin-left:-10px !important; padding-top:8px; font-size:10px;">
		<input type="checkbox" value="true" id="agbok" name="agbok" <?=((isset($_POST['agbok']) && ($_POST['agbok'] == 'true')) ? ' checked="checked"' : '') ?> />
		<label style="width:90% !important;" id="label_agbok" for="agbok" <?=((isset($_POST['save']) && !isset($_POST['agbok'])) ? ' style="color: #ff0000 !important;"' : '') ?>>Bitte best&auml;tigen Sie die <a href="/cms/Nutzungsbedingungen.274.0.html" target="_blank">Nutzungsbedingungen</a>!</label>
            </p>
            <p style="text-align:center; margin-top:5px; width:236px;">
                <button type="submit" name="send" onclick="ajaxlogin(); return false;" value="Einloggen"><span><span><span id="smallboxLoginButton">Anmelden</span></span></span></button>
            </p>

        </div>
        <div style="padding:16px; padding-left:13px;">

        </div>
    </form>
</div>
            <script type="text/javascript">
                switchLoginOption();

                function ajaxlogin() {
                        if($('oldmember').checked == true) {

                            document.getElementById('loginStatus').innerHTML = "<img src='/styles/images/ajax/loading_2.gif' alt='Lade' />";
                            document.body.style.cursor="wait";

                            xhr('/home/quicklogin/remoteLogin.php', 'username=' + document.getElementById('username').value + '&passwort=' + document.getElementById('passwort2').value , function(text){
                                    document.body.style.cursor="default";
                                    if (text != "false") {
                                        smallbox.hide("true");
                                    }
                                    else {
                                        document.getElementById('loginStatus').innerHTML = "Benutzername/Passwort nicht korrekt";
                                    }
                            });

                                try {
                                    if(pageTracker != undefined) {
                                        pageTracker._trackPageview("/smallbox/login/ver2/loggedin");
                                }
                                }catch(err) { }
                         } else {
                            if($('agbok').checked == false || $('username').value == '' || $('username').value == 'Email Adresse' || $('passwort2').value == '' || $('passwort2').value == 'Passwort'  || $('passwort2').value != $('passwort_wdh').value) {
                                document.getElementById('loginStatus').innerHTML = "Bitte alle Felder ausf&uuml;llen";
                            } else {
                                try {
                                    if(pageTracker != undefined) {
                                        pageTracker._trackPageview("/smallbox/login/ver2/register");
                                    }
                                }catch(err) { }
                                xhr("/home/quicklogin/ajaxlogin4.php", "step=2&m="+escape($('username').value)+"&p=" + escape($('passwort2').value), function(text) {
                                    parts = text.split("|");

                                    if(parts[0] == "false") {
                                        $('loginStatus').innerHTML = parts[1];
                                    } else {

                                        smallboxManager.boxes[0].loadUrl('', "/home/quicklogin/ajaxlogin4.php", 'u=' + parts[1], {
                                            title:'Um alle Funktionen zu nutzen - melde Dich an! Schritt 1 / 3'
                                        });
                                    }
                                });




                            }
                         }
                }
                top.decoGreyboxLinks();
            </script>
<!--
		<div id="ajaxlogin">
			<h2>praktika.de - Login</h2>
			<p style='margin-bottom:10px;'><?=$_POST["loginText"] ?></p>
		    <input name="siteDest" type="hidden" value="<?php echo $_POST["siteDest"]; ?>" />
		      <fieldset style="clear:none;">
		        <p>
		          <label <?= $err[0] ? 'class="error"' : ''; ?> for="login">Loginkennung:</label>
		          <input id="username" name="username" type="text" value="<?= $login ?>" />
		        </p>
		        <p>
		          <label <?= $err[1] ? 'class="error"' : ''; ?> for="password">Passwort:</label>
		          <input id="password" name="password" type="password" value=""/>
		        </p>
		      </fieldset>
		      
			  <fieldset class="control_panel">
		        <p>
		          <a class="stdStyle" rel="gb_page_center[500,250]" href="/home/quicklogin/passwdwindow.phtml">Passwort vergessen? - Jetzt anfordern!</a>
		        </p>
		        <p>
		          <a class="stdStyle"  href="javascript:top.location.href='/neuanmeldung/'">Kein Login? - Neu registrieren!</a>
		        </p>
		      </fieldset>
		<div class='loginStatus' id="loginStatus"></div>
		    
		</div>
		
		<fieldset class="login_control_panel smallbox_footer">
			<p>
				  <button type="submit" name="send" onclick="ajaxlogin(); return false;" value="Einloggen"><span><span><span>Einloggen</span></span></span></button>
				  <?php echo isset($_REQUEST['include']) ? '<input type="hidden" name="include" value="1" />' : '' ?>
			</p>
		</fieldset>
		</div>
	</div>
</form>-->
<?php
}
?>