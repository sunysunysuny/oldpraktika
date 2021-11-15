<?php
require_once("/home/praktika/etc/gb_config.inc.php");
ini_set("display_errors", 1);
function showAfterLogin($value, $key) {
	if ($key == 'kontakt2email') {
		return '<a href="mailto:'.$value.'" title="Eine eMail an die Kontaktperson senden.">eMail an Kontakt senden</a>'."\n";
	} elseif ($key == 'kontakt2webseite') {
		return '<a href="'.$value.'" target="_blank" title="Webseite des Unternehmens aufrufen.">Link zur Firmenwebseite</a>';
	} else {
		return $value;
	}
}
function crossLink($value) {

    $from = array(
        "Leipzig",
        "Elektrotechnik",
        "Marketing",
        "Fernsehen",
        "Berlin"
    );
    $to = array(
        "<a class=\"crossLink\" alt=\"Andere Angebote aus Leipzig\"  title=\"Andere Angebote aus Leipzig\" href='https://www.praktika.de/praktikum/leipzig.html'>Leipzig</a>",
        "<a class=\"crossLink\" alt=\"Andere Angebote aus dem Bereich Elektrotechnik\"  title=\"Andere Angebote aus dem Bereich Elektrotechnik\"  href='https://www.praktika.de/praktika/praktikum_elektronik-elektrotechnik.php'>Elektrotechnik</a>",
        "<a class=\"crossLink\" alt=\"Andere Angebote im Bereich Marketing\"  title=\"Andere Angebote im Bereich Marketing\"  href='https://www.praktika.de/praktika/praktikum_marketing-werbung-pr.php'>Marketing</a>",
        "<a class=\"crossLink\" alt=\"Andere Angebote im Bereich Fernsehen/Medien\"  title=\"Andere Angebote im Bereic/h Fernsehen/Medien\"  href='https://www.praktika.de/praktika/praktikum_film-fernsehen.php'>Fernsehen</a>",
        "<a class=\"crossLink\" alt=\"Andere Angebote aus Berlin\"  title=\"Andere Angebote aus Berlin\"  href='https://www.praktika.de/praktika/praktikum_berlin.php'>Berlin</a>"
    );

    return str_replace($from, $to, $value);
}

$stellenid = intval($_GET["i"]);

$objStelle = new Praktika_Stelle($stellenid);

$resultRst = $hDB->query('SELECT 
				stellen.*,
				firmen.firma,
				firmen_prop_assign.firmenlevel,
				UNIX_TIMESTAMP(stellen.datum_verfall) as datum_verfall, 
				UNIX_TIMESTAMP(stellen.modify) AS datum_unix,
				berufsfelder.german AS berufsfeld_german,
				bundesland.german AS bundesland_german,
				land.german AS land_german,
				grossraum.grossraum AS grossraum_german,
				branchen.german AS branche_german,
				firmenprofil.id `firmenprofil`,
				firmen_prop_assign.stelle_border as firma_stelle_border,
				firmen_prop_assign.stelle_width,
				firmen_prop_assign.stelle_height
			FROM 
				prakt2.stellen `stellen`
			LEFT JOIN 
				prakt2.firmen `firmen` ON firmen.id = stellen.firmenid
			LEFT JOIN 
				prakt2.firmen_prop_assign `firmen_prop_assign` ON firmen.id = firmen_prop_assign.firmenid
			LEFT JOIN
				prakt2.firmenprofil `firmenprofil` ON (firmenprofil.firmenid = stellen.firmenid AND firmenprofil.inactive = \'false\')
			LEFT JOIN
				prakt2.berufsfelder ON berufsfelder.id = stellen.berufsfeld
			LEFT JOIN
				prakt2.bundesland ON bundesland.id = stellen.bundesland
			LEFT JOIN 
				prakt2.land ON land.id = stellen.land
			LEFT JOIN 
				prakt2.grossraum ON grossraum.id = stellen.grossraum
			LEFT JOIN
				prakt2.branchen ON branchen.id = stellen.branche
			WHERE 
				stellen.id = '.$stellenid, $praktdbslave);

$stelle = mysql_fetch_assoc($resultRst);
	
$stelle["template"] = $objStelle->getTemplateBody($stelle['templateid'], 1);

	$markerArray = array(
							'###LOGO###',
							'###FIRMENINFOS_HEAD###',
							'###FIRMENTAETIGKEIT_HEAD###',
							'###FIRMENQUALI_HEAD###',
							'###FIRMENPERSP_HEAD###',
							'###FIRMENKONTAKT_HEAD###',
							'###FIRMENINFOS###',
							'###FIRMENTAETIGKEIT###',
							'###FIRMENQUALI###',
							'###FIRMENPERSP###',
							'###FIRMENKONTAKT###',
							'###JOBCODE###',
							'###TAETIGKEIT###',
							'###SUBTITLE###',
							'###ENTGELT###',
							'###BESCHREIBUNG###',
							'###DATUM###',
							'###FLASH###',
							'###APPLICATIONLINK###',
							'###KONTAKT2STRASSE###',
							'###KONTAKT2PLZ###',
							'###KONTAKT2ORT###',
							'###FIRMANAME###',
							'###KONTAKT2TEL###',
							'###KONTAKT2FAX###',
							'###KONTAKT2EMAIL###',
							'###KONTAKT2WEBSEITE###',
							'###KONTAKT2ANSPRECHPARTNER###',
							'###EINSATZORT###',
							'###STARTDATUM###',
							'###ZEITRAUM###',
							'###PLAINJOBCODE###',
							'###DATUMEINTRAG###',
							'###BERUFSFELD###',
					);

	if (!empty($stelle['entgelt']) && preg_match('/[^0-9]/', $stelle['entgelt']) == 0) {
		$stelle['entgelt'] = 'Verg&uuml;tung: '.$stelle['entgelt'].' EUR';				
	}
	
	if ($stelle['entgelt'] === '0') {
		$stelle['entgelt'] = '';
	}

	// Stelle einer Agentur?
	$agentur = new Praktika_Firmen_Agentur($stelle['firmenid']);
	$unternehmensId = $agentur->gehoertStelleZurAgentur($stelle['id']);

	if ($unternehmensId !== false) {
		if ($agentur->hasLogo($unternehmensId)) {
			$stelle['logoURL'] = $agentur->getLogoUrl($unternehmensId);
		} else {
			$stelle['logoURL'] = '/gifs/zentralelemente/logo.gif';
		}
	} else {
		$stelle['logoURL'] = Praktika_Firma::getLogoUrl($stelle['firmenid'], "stelle");
	}

	if ($stelle['logoURL'] != false) {
		$logoPath = $stelle['logoURL'];
		$firmenlogoHTML = '<img src="'.$logoPath.'" alt="Logo '.$stelle['firma'].'" />'."\n";
	} else {
		$logoPath = '/gifs/zentralelemente/logo.gif';
		$firmenlogoHTML = ''."\n";
	}
	
	if ($objStelle->isVideoTemplate($stelle['templateid'])) {
		if (preg_match('/^http:\/\//', $stelle['flash']) == 1) {
			$stelle['flash'] = '
				<script type="text/javascript">
					var flashvars = {};
					flashvars.url = "'.trim($stelle['flash']).'";
					var params = {};
					var attributes = {};
					attributes.align = "middle";
					swfobject.embedSWF("/scripts/swfobject/player/praktika_player.swf", "video", "320", "240", "9.0.0", "expressInstall.swf", flashvars, params, attributes);
				</script><div id="video"><a href="https://www.adobe.com/go/getflashplayer"><img src="https://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a></div>'."\n";
		}
		
		$defaultFLASH = '
						<script type="text/javascript">
							var flashvars = {};
							flashvars.url = "/scripts/swfobject/player/praktika.flv";
							var params = {};
							var attributes = {};
							attributes.align = "middle";
							swfobject.embedSWF("/scripts/swfobject/player/240x180.swf", "video", "240", "180", "9.0.0", "expressInstall.swf", flashvars, params, attributes);
						</script><div id="video"><a href="https://www.adobe.com/go/getflashplayer"><img src="https://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a></div>'."\n";
	}

	$monat = array("","Januar", "Februar", "M&auml;rz", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober","November","Dezember");
	
	#if($_COOKIE["stefanDebug"] == "1") { var_dump(Praktika_String::removeWordChars(crossLink(nl2br($stelle['beschreibung'])))); ob_flush(); }
	$replaceArray = array(
							$firmenlogoHTML,
							$stelle['firmeninfosHead'],
							$stelle['firmentaetigkeitHead'],
							$stelle['firmenqualiHead'],
							$stelle['firmenperspHead'],
							$stelle['firmenkontaktHead'],
							crossLink(stripslashes(nl2br($stelle['firmeninfos']))),
							crossLink(stripslashes(nl2br($stelle['firmentaetigkeit']))),
							Praktika_String::removeWordChars(crossLink(stripslashes(nl2br($stelle['firmenquali'])))),
							crossLink(stripslashes(nl2br($stelle['firmenpersp']))),
							showAfterLogin(stripslashes(nl2br($stelle['firmenkontakt'])), "firmenkontakt"),
							((!empty($stelle['jobcode'])) ? 'Jobcode: '.$stelle['jobcode'] : ''),
							stripslashes(strip_tags($stelle['taetigkeit'])),
							stripslashes($stelle['subtitle']),
							showAfterLogin($stelle['entgelt'], 'entgelt'),
							Praktika_String::removeWordChars(crossLink(nl2br($stelle['beschreibung']))),
							'Zuletzt bearbeitet: '.date('d.m.Y', strtotime($stelle['modify'])),
							!empty($stelle['flash']) ? htmlspecialchars_decode(str_replace('\\', '', $stelle['flash'])) : $defaultFLASH,
							empty($stelle['link_bewerbung']) ? '' : '/redirects/'.Praktika_Redirect::getUrlId($stelle['link_bewerbung'], $stelle['firmenid'], $stelle['id'])."/",
							showAfterLogin($stelle['kontakt2strasse'], 'kontakt2strasse'),
							showAfterLogin($stelle['kontakt2plz'], 'kontakt2plz'),
							showAfterLogin($stelle['kontakt2ort'], 'kontakt2ort'),
							$stelle['firma'],
							showAfterLogin($stelle['kontakt2tel'], 'kontakt2tel'),
							showAfterLogin($stelle['kontakt2fax'], 'kontakt2fax'),
							showAfterLogin($stelle['kontakt2email'], 'kontakt2email'),
							showAfterLogin($stelle['kontakt2webseite'], 'kontakt2webseite'),
							showAfterLogin($stelle['kontakt2ansprechpartner'], 'kontakt2ansprechpartner'),
							$stelle['einsatzort'],
							$monat[$stelle["von_monat"]]." ".$stelle["von_jahr"],
							!empty($stelle["zeitraum"]) ? "<strong>Praktikumszeitraum:</strong> ".$stelle["zeitraum"]. " Monate":"",
							$stelle['jobcode'],
							date("d.m.Y", strtotime($stelle['datum_eintrag'])),
							$stelle["berufsfeld_german"],
						);

	$stellenBody = str_replace($markerArray, $replaceArray, $stelle['template']);	

	// Minify_HTML auf PageCache anwenden
	#set_include_path($incPath.PATH_SEPARATOR.SERVER_ROOT."/public_html/min/lib/");
	#require_once(SERVER_ROOT."/public_html/min/lib/Minify/HTML.php");
	#require_once(SERVER_ROOT."/public_html/min/lib/Minify/CSS.php");
	#$stellenBody = Minify_HTML::minify($stellenBody, array("cssMinifier" => "Minify_CSS::minify"));
	#set_include_path($incPath);	
	
echo $stellenBody;
