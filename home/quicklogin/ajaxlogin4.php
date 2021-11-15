<?php
ob_start();
require("/home/praktika/etc/gb_config.inc.php");

$userid = (int)$_POST["u"];
$row = array();

if(!empty($_POST["step"]) && $_POST["step"] == "2") {

    $_POST["email"] = $_POST["m"];
    $_POST["passwort"] = $_POST["p"];

    if(Praktika_Validate::checkMailForRegistration($_POST["email"]) > 0) {
        echo "eMail Adresse schon vergeben".Praktika_Validate::checkMailForRegistration($_POST["email"]);
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

        # $begruessung .= ' '.$verifykeydaten['anrede'].' '.$verifykeydaten['name'];

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

if($_POST["close"] == "1") {

    if($_POST["mA"] == "1") {
        $row[] = "praktika_ausland = 'true'";
        $sql = "SELECT email, name, anrede FROM prakt2.nutzer WHERE id = ".$userid;
        $nutzer = $hDB->query($sql, $praktdbmaster);
        $nutzer = mysql_fetch_assoc($nutzer);

        //Zeitbezug einstellen
        $aktstunde = date('H');
        if ($aktstunde < 10) {
                $begruessung = 'Guten Morgen';
        } elseif ($aktstunde > 19) {
                $begruessung = 'Guten Abend';
        } else {
                $begruessung = 'Guten Tag';
        }

        $begruessung .= ' '.$nutzer['anrede'].' '.$nutzer['name'];

        /* infomail zusammenbauen */
        // Templatesystem vorbereiten
        $emailobj = new emailtemplate(90);

        // Ersetzungen durchführen
        $emailobj->replace('{id}',$insert_id);
        $emailobj->replace('{ansprache}',$begruessung);
        $emailobj->replace('{anrede}',$nutzer['anrede']);
        $emailobj->replace('{name}',$nutzer['name']);

        // infomail verschicken
        $emailobj->send_email($nutzer["email"]);

        // Emailobjekt löschen
        unset($emailobj);
    }
    if($_POST["mB"] == "1") {
        $row[] = "praktika_sprachreise = 'true'";
    }

    $sql = "INSERT INTO ".$database_partner.".nutzer_reg_afilli SET ".implode(",",$row).", nutzerid = ".$userid;
    $hDB->query($sql, $praktdbmaster);

    Praktika_User::loginById($userid, LOGIN_CANDIDATES);
    exit();
}

?>
<div style="width:672px; height:415px; text-align:center; margin:20px;" class="smallbox_register_2">
    <p style="font-size:16px; font-weight:bold;">Vielen Dank f&uuml;r Ihre Registrierung auf praktika.de</p><br />
    Sie haben soeben eine E-Mail mit dem Betreff "<b>Willkommen bei PRAKTIKA!</b>" erhalten.<br />Sollten Sie keine E-Mail in Ihrem Posteingang finden, schauen Sie bitte auch in den Spam-Ordner. <br />
    <br />
<span style="color:red;">
    Folgen Sie bitte den Anweisungen in der E-Mail zur Aktivierung Ihres PRAKTIKA-Accounts. Nach der Aktivierung können Sie sich mit Ihren Zugangsdaten einloggen. <br />
</span>
<br />
<script type="text/javascript">
    function fertig() {
        modesA = $('affilli_praktika_ausland').checked==true?1:0;
        modesB = $('affilli_praktika_sprachreise').checked==true?1:0;

        xhr('/home/quicklogin/ajaxlogin3.php','close=1&u=<?=$userid ?>&mA=' + modesA + '&mB=' + modesB);

        window.setTimeout("location.reload();",1000);
    }
</script>
Viel Spaß!<br />
Ihr PRAKTIKA Team<br />
    <br />
    <h4>Unser kostenfreies Dankesch&ouml;n f&uuml;r Ihre Anmeldung</h4>
        <p class="checkboxes" style=" width:40%; float:right;">
            <input type="checkbox" value="true" id="affilli_praktika_sprachreise" name="affilli[praktika_sprachreise]" <?=((isset($_POST['affilli']['praktika_sprachreise']) && ($_POST['affilli']['praktika_sprachreise'] == "true")) ? ' checked="checked"' : '') ?> />
            <label style="width:90% !important;" for="affilli_praktika_sprachreise">Ja, Bitte senden Sie mir den aktuellen Sprachreisekatalog <?=(date('Y') + 1)?> kostenlos zu.<br /><br /><br /><img src="/styles/images/home/sprachreisen_mini.gif" alt="" /></label>
        </p>
        <p class="checkboxes clearfix" style="width:40%;">
            <input type="checkbox" value="true" id="affilli_praktika_ausland" name="affilli[praktika_ausland]" <?=((isset($_POST['affilli']['praktika_ausland']) && ($_POST['affilli']['praktika_ausland'] == 'true')) ? ' checked="checked"' : '') ?> />
            <label  style="width:90% !important;"for="affilli_praktika_ausland">Ja, ich interessiere mich f&uuml;r ein Praktikum im Ausland, bitte senden Sie mir dazu kostenlose Informationen zu.<br /><br /><img src="/styles/images/home/praktika_mini.jpg" alt="" /></label>
        </p>
        <div style="text-align:center;"><button onclick="fertig(); smallbox.hide(false);"><span><span><span>Fertigstellen</span></span></span></button></div>
</div>
