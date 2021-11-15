<? return; ?>
	<? Praktika_Static::__("kcenter"); ?>
        <link type="text/css" media="screen" rel="stylesheet" href="/styles/v3/module/kcenter.css" />
	<div id="prakt_navi">
		<ol style="list-style-type:none; padding:0px;margin:0px;">
			<li id="tab_1" style="margin:0px; margin-left:-5px;"class="naviButton rightBorder <?=$parts[0]=="messaging"?"active":"" ?>">
				<a href="/Karriere_Center/messaging" title="Bewerbungen">
					<img src='/gifs/kcenter/kcenter_mail.gif' alt="Bewerbungen verwalten" title="Meine Bewerbungen"  />	
				</a>
				<a href="/Karriere_Center/messaging" title="Bewerbungen">
					<span class="naviLabel">Bewerbungen</span>
				</a>
			</li>
			<li id="tab_1" style="margin:0px;"class="naviButton rightBorder <?=$parts[0]=="lebenslauf"?"active":"" ?>">
				<a href="/lebenslauf" title="Bewerbungen">
					<img src='/gifs/kcenter/kcenter_lebenslauf.gif' alt="Bewerbungen verwalten" title="Meine Bewerbungen"  />	
				</a>
				<a href="/lebenslauf" title="Bewerbungen">
					<span class="naviLabel">Mein Lebenslauf</span>
				</a>
			</li>			
			<li id="tab_2" style="margin:0px;"class="naviButton leftBorder rightBorder <?=$parts[0]=="daten"?"active":"" ?>">
				<a href="/Karriere_Center/daten" title="Bewerbungen">
					<img src='/gifs/kcenter/kcenter_personal.gif' alt="Kontaktdaten eintragen" title="Meine Daten"  />	
				</a>
				<a href="/Karriere_Center/daten" title="Bewerbungen">
					<span class="naviLabel">Pers&ouml;nliche Daten</span>
				</a>				
			</li>			
			<li id="tab_3" style="margin:0px;" class="naviButton rightBorder leftBorder <?=$parts[0]=="gesuche"?"active":"" ?>">
				<a href="/Karriere_Center/gesuche" title="Bewerbungen">
					<img src='/gifs/kcenter/kcenter_gesuche.gif' alt="Gesuche eintragen" title="Meine Gesuche" />
				</a>
				<a href="/Karriere_Center/gesuche" title="Bewerbungen">
					<span class="naviLabel">Gesuche</span>
				</a>				
			</li>
			<li id="tab_4" style="margin:0px;"class="naviButton rightBorder leftBorder <?=$parts[0]=="passbild"?"active":"" ?>">
				<a href="/Karriere_Center/passbild" title="Bewerbungen">
					<img src='/gifs/kcenter/kcenter_foto.gif' alt="Passfoto einstellen" title="Ihr Passbild" />
				</a>
				<a href="/Karriere_Center/passbild" title="Bewerbungen">
					<span class="naviLabel">Passbild</span>
				</a>				
			</li>
		</ol>
				
	</div>
	<span id="kcenter_hinweis">
		<div id="hinweis_arrow"  class="notiz_arrow" style="padding-left:10px;display:none;"><img src="/gifs/bcenter/hinweis_arrow.gif" alt="Hinweismarker" title="Hinweismarker" /></div>
		<div id="hinweis"style="display:none;"  class="hinweis">Achtung: Sie m�ssen das und das noch erledigen</div>
	</span>
<style>
	.hinweis_text_achtung {
		float:none !important;
	}
</style>
<script type="text/javascript">
	function setHinweisHide() {
		document.getElementById('hinweis').style.display="none";
		document.getElementById('hinweis_arrow').style.display="none";
	}
	function setHinweis(typ, text) {
		document.getElementById('hinweis').style.display="";
		document.getElementById('hinweis_arrow').style.display="";
		
		var arrowLeft = 0;
		text = text.replace(/Achtung:/,"<span class='hinweis_text_achtung'>Achtung:</span>")
		text = text.replace(/Hinweis:/,"<span class='hinweis_text_achtung'>Hinweis:</span>")
		text = text.replace(/(.*)<a(.*)<\/a>/,"<span class='hinweis_text_achtung text'>$1</span><span class='hinweis_text_achtung link'><a$2</a></span>");
		/* " */
		
		document.getElementById('hinweis').innerHTML = text;
		switch(typ) {
			case 'messaging':
			arrowLeft = "4em";
			break;
			case 'firmendaten':
			arrowLeft = "15em";
			break;
			case 'firmenprofil':
			arrowLeft = "26em";
			break;
			case 'bearbeiter':
			arrowLeft = "40em";
			break;
			case 'logo':
			arrowLeft = "54em";
			break;
			case 'kc_daten':
				arrowLeft = "19em";
				break;
			case 'kc_logo':
				arrowLeft = "47em";
				break;
			case 'kc_gesuch':
				arrowLeft = "40em";
				break;
			case 'kc_messaging':
				arrowLeft = "5em";
				break;
				
				
		}
		document.getElementById('hinweis_arrow').style.paddingLeft = arrowLeft;
	}	
	<? 
	$hinweis = "";

	
	$results1 = mysql_query('SELECT id FROM prakt2.praktikanten WHERE inactive=\'false\' AND nutzerid='.(int)$_SESSION['s_loginid'].'',$praktdbslave);
	$results2 = mysql_query('SELECT id FROM prakt2.diplomgesuch WHERE inactive=\'false\' AND nutzerid='.(int)$_SESSION['s_loginid'].'',$praktdbslave);
	$results3 = mysql_query('SELECT id FROM prakt2.nebenjobgesuch WHERE inactive=\'false\' AND nutzerid='.(int)$_SESSION['s_loginid'].'',$praktdbslave);
	$results4 = mysql_query('SELECT id FROM prakt2.ausbildungsgesuch WHERE inactive=\'false\' AND nutzerid='.(int)$_SESSION['s_loginid'].'',$praktdbslave);
	$results5 = mysql_query('SELECT id FROM prakt2.berufseinstieggesuch WHERE inactive=\'false\' AND nutzerid='.(int)$_SESSION['s_loginid'].'',$praktdbslave);
	
	if ((mysql_num_rows($results1) > 0) || 
	(mysql_num_rows($results2) > 0) || 
	(mysql_num_rows($results3) > 0) || 
	(mysql_num_rows($results4) > 0) || 
	(mysql_num_rows($results5) > 0)) {
		# $profilquali += 25;
	} else {
		$hinweis = array("kc_gesuch","Hinweis: Sie k&ouml;nnen Gesuche erstellen um Unternehmen auf sich aufmerksam zu machen. <a href='/Karriere_Center/gesuche/anlegen'> Jetzt ein Gesuch erstellen?</a>");
	}
	
	if(empty($hinweis)) {
		
		$passbild = mysql_query("SELECT
	                    url
	                FROM
	                    prakt2.bewerbungsfoto
	                WHERE 
	                    nutzerid = ".(int)$_SESSION['s_nutzerid'], $praktdbslave);
						
		$hinweis = array();
		if(mysql_num_rows($passbild) == 0) { 
			$hinweis = array("kc_logo","Hinweis: Wenn sie ein Passbild hochladen, geben Sie ihrem Eintrag in der Bewerber&uuml;bersicht der Unternehmen eine individuelle Note. <a href='/Karriere_Center/passbild'> Jetzt ein Passbild hochladen</a>");
		} 
	}
	
	if(empty($hinweis)) {	
		// Gesuch eingestellt
		$results = mysql_query('SELECT modifyunix FROM prakt2.nutzer WHERE id = '.(int)$_SESSION['s_nutzerid'].' AND modifyunix < '.(time()-(60*60*24*60)), $praktdbslave);

		if(mysql_num_rows($results) > 0) {
				$hinweis = array("kc_daten",'Hinweis: Wenn Sie ihr Profil aktuell halten, wird Ihr Eintrag in der Kandidatenliste der Unternehmen weiter vorn dargestellt.<br /><a href="/Karriere_Center/daten"> Zu Ihren Profildaten</a>');		
		}
	}
	
	if(empty($hinweis)) {
		// Hat der User Bewerbungen im Vorschaumodus
		# $sql = "SELECT COUNT(nutzerid) `num` FROM prakt2.bewerbung WHERE nutzerid = ".$_SESSION["s_nutzerid"]." AND inactive = 'false' AND ninactive = 'false' AND preview = 'true' AND prakstellenid != 0 GROUP BY nutzerid";
		$sql = "SELECT COUNT(bewerbung.nutzerid) `num` FROM prakt2.nutzerbookmark LEFT JOIN prakt2.bewerbung ON(bewerbung.nutzerid = ".(int)$_SESSION["s_nutzerid"]." AND bewerbung.prakstellenid = nutzerbookmark.stellenid) WHERE nutzerbookmark.nutzerid = ".(int)$_SESSION["s_nutzerid"]." AND bewerbung.inactive = 'false' AND bewerbung.ninactive = 'false' AND bewerbung.preview = 'true' AND bewerbung.prakstellenid != 0 GROUP BY nutzerbookmark.nutzerid";
		$result = $hDB->query($sql, $praktdbslave);
		
		if(mysql_num_rows($result) > 0) $anzahl_preview_bewerbung = mysql_result($result,0,"num");
		if(!empty($anzahl_preview_bewerbung) && (mysql_num_rows($result) == 0 || $anzahl_preview_bewerbung > 0)) {
			$hinweis = array("kc_messaging",'Hinweis: Sie haben '.$anzahl_preview_bewerbung.' Bewerbungen im Vorschau-Modus und noch <b>NICHT</b> versendet. Die Unternehmen k&ouml;nnen diese noch nicht einsehen.<a href="/Karriere_Center/Messaging/Merkzettel/0/"> Zu diesen Bewerbungen wechseln</a>');
		}
	}
	
	if(empty($hinweis)) {
		// Hat der User Bewerbungen im Vorschaumodus
		$results = mysql_query('SELECT id FROM prakt2.nutzer WHERE id = '.(int)$_SESSION['s_nutzerid'].' AND profilquali < 80', $praktdbslave);

		if($anzahl_preview_bewerbung > 0) {
			$hinweis = array("kc_messaging",'Hinweis: Mit einer h&ouml;heren Profilqualit&auml;t werden Sie von den Unternehmen st�rker beachtet. Au�erdem erm�glicht ein ausgef�llter Lebenslauf die Ausgabe in Form als PDF zur weiteren Verwendung.<a href="/lebenslauf"> Zum Lebenslauf wechseln</a>');
		}
	}
	
		if(!empty($hinweis)) {
			echo "setHinweis('".addslashes($hinweis[0])."','".addslashes($hinweis[1])."');";
		} else {
			echo "setHinweisHide();";
		}
?>
</script>
<div style="display:none;margin-top:3px;" id="kcenter_success" class="success"></div>	