<?php
# UPDATE
require("/home/praktika/etc/gb_config.inc.php");


$platzhalter = array(
						'taetigkeit' => '*** Geben Sie hier den Titel Ihrer Stelle ein. ***', 
						'firmeninfosHead' => '*** Geben Sie hier eine &Uuml;berschrift zum Text Ihres Unternehmens ein. ***',
						'firmentaetigkeitHead' => '*** Geben Sie hier eine &Uuml;berschrift zur T&auml;tigkeitesbeschreibung ein. ***',
						'firmenqualiHead' => '*** Geben Sie hier eine &Uuml;berschrift zur Qualifikationsbeschreibung ein. ***',
						'firmenperspHead' => '*** Geben Sie hier eine &Uuml;berschrift zur Beschreibung der Perspektive ein. ***',
						'firmenkontaktHead' => '*** Geben Sie hier eine &Uuml;berschrift zur Kontaktaufnahme ein. ***',
						'firmeninfos' => '*** Geben Sie hier einen Text zu Ihrem Unternehmen ein. ***',
						'firmentaetigkeit' => '*** Geben Sie hier die T&auml;tigkeitsbeschreibung zur Stelle ein. ***',
						'firmenquali' => '*** Geben Sie hier die Qualit&auml;tsbeschreibung ein. ***',
						'firmenpersp' => '*** Geben Sie hier die m&ouml;glichen Perspektiven ein. ***',
						'firmenkontakt' => '*** Geben Sie hier Ihre Kontaktdaten ein. ***',
						'jobcode' => '*** Geben Sie hier einen Code f&uuml;r Ihre Stelle ein. ***',
						'subtitle' => '*** Geben Sie hier einen Untertitel zum Stellentitel ein. ***',
						'entgelt' => '*** Geben Sie einen Text zur Verg&uuml;tung ein. ***',
						'beschreibung' => '*** Geben Sie hier erweiterte Beschreibungen zur Stellen ein. ***',
						'kontakt2ort' => '*** Geben Sie hier Ihren Ort ein. ***',
						'kontakt2plz' => '*** Geben Sie hier Ihre Postleitzahl ein. ***',
						'kontakt2strasse' => '*** Geben Sie hier Ihre Stra&szlig;e ein. ***',
						'kontakt2tel' => '*** Geben Sie hier Ihre Telefonnummer ein. ***',
						'kontakt2fax' => '*** Geben Sie hier Ihre Faxnummer ein. ***',
						'kontakt2email' => '*** Geben Sie hier Ihre eMail-Adresse ein. ***',
						'kontakt2webseite' => '*** Geben Sie hier Ihre Webseite ein. ***',
						'kontakt2ansprechpartner' => '*** Geben Sie hier Ihren Ansprechpartner ein. ***'
					);

//im FREE-Account sind diese Kontaktmoeglichkeiten nicht vorhanden
if ($_SESSION['s_firmenlevel'] == 0) {
	$delOptions = array(
							'###KONTAKT2ORT###',
							'###KONTAKT2PLZ###',
							'###KONTAKT2STRASSE###',
							'###KONTAKT2TEL###',
							'###KONTAKT2FAX###',
							'###KONTAKT2EMAIL###',
							'###KONTAKT2WEBSEITE###'
						);
}

//im BASIS-Account sind diese Kontaktmoeglichkeiten nicht vorhanden
if ($_SESSION['s_firmenlevel'] == 1) {
	$delOptions = array(
							'###KONTAKT2WEBSEITE###'
						);
}

$rawModus = array('jobcode', 'flash', 'taetigkeit');
error_log("COUNT".intval(is_array($_SESSION['jobAd'])));
if (isset($_SESSION['neue_stelle']) && $_SESSION['neue_stelle'] == true) {
	$stelle = $_SESSION['jobAd'];
} else {
	$sqlRst = '
				SELECT
					firmen.firma,
					stellen.*,
					DATE_FORMAT(stellen.von_monat, \'%m\') AS monat,
					DATE_FORMAT(stellen.von_jahr, \'%Y\') AS jahr,
					DATE_FORMAT(stellen.datum_verfall, \'%d\') AS tagv,
					DATE_FORMAT(stellen.datum_verfall, \'%m\') AS monatv,
					DATE_FORMAT(stellen.datum_verfall, \'%Y\') AS jahrv
				FROM
					stellen
					LEFT JOIN firmen ON firmen.id = stellen.firmenid
				WHERE
					stellen.id = '.$_SESSION['jobAd']['stellenid'].'
					AND firmenid = '.$_SESSION['s_firmenid'];
	
	$stelle_Rst = mysql_db_query($database_db, $sqlRst, $praktdbslave);
	
	if ($stelle_Rst != false && mysql_num_rows($stelle_Rst) > 0) {
		$stelle = mysql_fetch_array($stelle_Rst);
	} else {
		$stelle['templateid'] == 0;
	}
}
	
//externe Seite?
if ($stelle['templateid'] != 0) {
	$objStelle = new Praktika_Stelle($_SESSION['jobAd']['stellenid']);
	$htmlBody = $objStelle->getTemplateBody($stelle['templateid']);
}

$htmlBody = str_replace("charset=iso-8859-1", "charset=utf-8", $htmlBody);

// Stelle einer Agentur?
if (isset($_SESSION['unternehmens_id'])) {
	$agentur = new Praktika_Firmen_Agentur($_SESSION['s_firmenid']);

	if ($agentur->hasLogo($_SESSION['unternehmens_id'])) {
		$logoPath = $agentur->getLogoUrl($_SESSION['unternehmens_id']);
	} else {
		$logoPath = '/styles/images/logo_upload.png';
	}
} else {
	if (Praktika_Firma::hasLogo($_SESSION['s_firmenid'])) {
		$logoPath = Praktika_Firma::getLogoUrl($_SESSION['s_firmenid']);
	} else {
		$logoPath = '/styles/images/logo_upload.png';
	}
}

/* if ($logo != false && mysql_num_rows($logo) > 0) {
	$row = mysql_fetch_assoc($logo);
		
	
} else {
	$logoPath = '/gifs/zentralelemente/logo.png';
} */
	
// Logo bekommt eigene Bearbeitungsfunktion
// Stelle einer Agentur?
if (isset($_SESSION['unternehmens_id'])) {
	$firmenlogoHTML = '<img src="'.$logoPath.'" class="logo" alt="Firmenlogo" /><br />';
} else {
	$firmenlogoHTML = '<img src="'.$logoPath.'" class="logo" alt="Firmenlogo" onclick="smallbox.loadFrame(\'/firmen/logo_upload/'.$_SESSION['jobAd']['stellenid'].'/\', {width:500, height:450, title:\'Bild hochladen\'});" /><br />';
}

$datumHTML = 'Aktuell seit: '.date('d.m.Y', strtotime($stelle['modify']));
// Flash bekommt eigene Bearbeitungsfunktion

$htmlBody = str_replace('###FIRMANAME###', $stelle['firma'], $htmlBody);

//Kontaktfelder ersetzen, falls Lizenz nicht vorhanden
if (isset($delOptions) && is_array($delOptions)) {
	foreach ($delOptions as $key) {
		$htmlBody = str_replace($key, '<a href="#" onclick="prompt_message('.($key === '###KONTAKT2WEBSEITE###' ? '2' : '1').');" class="link">'.($key === '###KONTAKT2WEBSEITE###' ? 'erst ab KOMFORT-Paket verf&uuml;gbar' : 'erst ab BASIS-Paket verf&uuml;gbar').'</a>', $htmlBody);
	}
}

preg_match_all('/###(.*)###/U', $htmlBody, $treffer);

$htmlBody = str_replace('###LOGO###', $firmenlogoHTML, $htmlBody);
$htmlBody = str_replace('###DATUM###', $datumHTML, $htmlBody);

//Flash-Video
$flash = mysql_db_query($database_db, 'SELECT flash FROM stellen WHERE id = '.$_SESSION['jobAd']['stellenid'], $praktdbslave);

if ($flash != false && mysql_num_rows($flash) > 0) {
	$flashCode = mysql_fetch_assoc($flash);

	if (strlen($flashCode['flash']) == 0) {
		$htmlBody = str_replace('###FLASH###', '<img src="/gifs/zentralelemente/video.png" class="flash" alt="Video einf&uuml;gen" title="Video einf&uuml;gen" onclick="smallbox.loadFrame(\'/firmen/flash/\', {width:500, height:450, title:\'Video einf&uuml;gen\'});" />', $htmlBody);
	} else {
		if (strpos($flashCode['flash'], '<object') !== false) {
			$htmlBody = str_replace('###FLASH###', stripslashes($flashCode['flash']).'<br /><a href="#" onclick="smallbox.loadFrame(\'/firmen/flash/\', {width:500, height:450, title:\'Video-Quelle bearbeiten\'});" class="link">Video-Quelle bearbeiten</a>', $htmlBody);
		} else {
			$flashCode['flash'] = '	<script type="text/javascript" src="/scripts/swfobject/swfobject.js"></script>
									<script type="text/javascript">
										var flashvars = {};
										flashvars.url = "'.trim($flashCode['flash']).'";
										var params = {wmode: \'transparent\'};
										var attributes = {};
										attributes.align = "middle";
										swfobject.embedSWF("/scripts/swfobject/player/praktika_player.swf", "video", "320", "240", "9.0.0", "expressInstall.swf", flashvars, params, attributes);
									</script>
									<div id="video"><a href="https://www.adobe.com/go/getflashplayer"><img src="https://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a></div>'."\n";
			
			$htmlBody = str_replace('###FLASH###', stripslashes($flashCode['flash']).'<br /><a href="#" onclick="smallbox.loadFrame(\'/firmen/flash/\', {width:500, height:450, title:\'Video-Quelle bearbeiten\'});" class="link">Video-Quelle bearbeiten</a>', $htmlBody);
		}
	}
} else {
	$htmlBody = str_replace('###FLASH###', '<img src="/gifs/zentralelemente/video.png" class="flash" alt="Video einf&uuml;gen" title="Video einf&uuml;gen" onclick="smallbox.loadFrame(\'/firmen/flash/\', {width:500, height:450, title:\'Video einf&uuml;gen\'});" />', $htmlBody);
}

$stelle['jobcode'] = 'Jobcode: '.$stelle['jobcode'];

for ($a = 0; $a < count($treffer[0]); $a++) {
	$feld = strtolower($treffer[1][$a]);
	if($treffer[1][$a] == "APPLICATIONLINK") {
		continue;
	}
	$inline = false;
	
/*	if ($feld == 'kontakt2plz' || $feld == 'kontakt2ort' || $feld == 'kontakt2strasse') {
		$inline = true;
	}*/

	if (strpos($feld, '_head') !== false) {
		$feld = substr($feld, 0, (strlen($feld)-5)).'Head';
	} elseif (strpos($feld, 'head') !== false) {
		$feld = substr($feld, 0, (strlen($feld)-4)).'Head';
	}
	
	$default = $platzhalter[$feld];
	
	if (in_array($feld, $rawModus)) {
		$type = 'raw';
	} else {
		$type = 'html';
	}
	
	$htmlBody = str_replace($treffer[0][$a], '<div default="'.$default.'" title="'.str_replace('PLATZHALTER ', '', $default).'" onblur="parent.resizeIframe();" id="field_'.$feld.'" class="editable" onmouseup="parent.resizeIframe(); setField(\'field_'.$feld.'\',\''.$type.'\');" onkeyup="// return parent.keypressed()" style="'.($inline == true ? 'display: inline' : '').'">'.(empty($stelle[$feld]) ? $default : nl2br(stripslashes($stelle[$feld]))).'</div>', $htmlBody);
	
	if ($feld != 'logo' && $feld != 'datum') {
		$initField[] = 'field_'.$feld;
	}
}

$isIE = Praktika_Browser::isIE("7.0", "<");

//Einfuegestring in den Header des Templates
$replaceString1 = '<head>
<meta http-equiv="X-UA-Compatible" content="IE=9,chrome=1" />';
$replaceString2 = '
	<script type="text/javascript">var isIE7 = false; var isIE = false; var IEversion = 0; </script>
	<!--[if lte IE 7]>
	<script type="text/javascript">var isIE7 = true; var isIE = true; var IEversion = 7; </script>
	<![endif]-->
	<!--[if IE 8]>
	<script type="text/javascript">var isIE7 = false; var isIE = true; var IEversion = 8; </script>
	<![endif]-->
	<!--[if gt IE 8]>
	<script type="text/javascript">var isIE7 = false; var isIE = true; var IEversion = 9; </script>
	<![endif]-->
	<link rel="stylesheet" type="text/css" href="/styles/stellenEditor/editor.css" />
	<link rel="stylesheet" type="text/css" href="/scripts/smallbox.css" />
	<!--[if IE]>
	 <style type="text/css">
		.smallboxBackground {
			position:absolute !important;
		}
	 </style>
	<![endif]-->
	<style type="text/css">
		.ieEditor {
			font-family:Arial;
			font-size:12px;
		}
		span.cke_skin_kama {
			padding:0px !important; 
		}
		.cke_skin_kama .cke_wrapper {
			padding:2px !important;
		}

		.cke_skin_kama .cke_toolgroup {
			margin-bottom:3px !important;
		}
		.cke_skin_kama {
			margin-left:-2px !important;
			margin-right:-2px !important;
		}
	</style>
	<link type="text/css" media="screen,print" rel="stylesheet" href="/min/?g=mainStyles" />
	
<!--[if lt IE 2]>
<script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE8.js"></script>
<![endif]-->
    <script type="text/javascript" src="/scripts/jquery/jquery-1.4.2.min.js"></script>
	<script type="text/javascript">
		jQuery.extend({
			scope: function(fn, scope) {
				return function() {
					return fn.apply(scope, arguments);
				}
			}
		});
	</script>

    
	<script type="text/javascript" src="/scripts/microajax.js"></script>
	<script type="text/javascript" src="/scripts/smallbox_2.js"></script>
	'.($isIE?'':'<script type="text/javascript" src="/scripts/ckeditor_3_3/ckeditor.js?3_6"></script>').'
	
	<script type="text/javascript">
		smallbox.deactivateAnimation();
		
		var editLock = \'\';
		var isIE = '.($isIE?"true":"false").';
		var hinweisText = {
			\'field_kontakt2ort\' :\'Bitte geben Sie den Einsatzort des Praktikanten ein!\',
			\'field_kontakt2plz\' :\'Bitte geben Sie die Postleitzahl zum Einsatzort ein!\',
			\'field_kontakt2strasse\' :\'Bitte geben Sie die Stra&szlig; zum Einsatzort ein!\',
			\'field_kontakt2tel\' :\'Bitte geben Sie Ihre Telefonnummer f&uuml;r Bewerber ein!\',
			\'field_kontakt2fax\' :\'Bitte geben Sie Ihre Faxnummer f&uuml;r Bewerber ein!\',
			\'field_kontakt2email\' :\'Bitte geben Sie Ihre Email f&uuml;r Bewerber ein!\',
			\'field_kontakt2webseite\' :\'Bitte geben Sie Ihre Webseite an!\',
			\'field_kontakt2ansprechpartner\' :\'Bitte geben Sie einen Ansprechpartner f&uuml;r Bewerbungen an!\'
		};

		String.prototype.br2nl =
		  function() {
		    return this.replace(/<br\s*\/?>/mgi,"\n");
		  };
		String.prototype.nl2br =
		  function() {
		    return this.replace(/\n/g, "<br />");
		  };
		String.prototype.clearNL =   
		  function() {
		    return this.replace(/\n/g, "");
		  };		
		function checkLastField() {
			if (editLock != \'\' && saved != true) {
				checker = confirm(unescape(\'Sie haben ein anderes Feld bearbeitet ohne zu speichern!\' + "\n" + \'M%F6chten sie dieses Feld jetzt speichern?\' + "\n\n" + \'Ihre %C4nderungen werden ansonsten nicht %FCbernommen!\'));
			
				if (checker == true) save(editor);
			}
		}
		
		var feldBez;
	    
		function setField(id, type) {
			if (editLock == id) return;

	 		if (editor) {
	 		
				var eventReturn = editor.fire("blur");
				
				save(editor);

			}


			
			checkLastField();
			parent.resizeIframe();
			saved = true;
			feldBez = \'\';

			if (hinweisText[id] != undefined) {
				feldBez = hinweisText[id];
			} else {
				feldBez = \'\';
			}
			
			editLock = id;
			type = \'html\';
			
			if (editLock.indexOf(\'Head\') != -1 || editLock.indexOf(\'_taetigkeit\') != -1 || editLock.indexOf(\'_subtitle\') != -1) {
				type = \'head\';
			} else if (editLock.indexOf(\'_jobcode\') != -1) {
				type = \'jobcode\';
			} else if (editLock.indexOf(\'kontakt2\') != -1) {
				type = \'contact\';
			}

			oldContent = document.getElementById(id).innerHTML;
			replaceDiv(document.getElementById(id), type);
		}
	
		var editor;
		var saved = true;
		var ieEditor = false;
		/**
		 *Alter Inhalt der Felder fuer zuruecksetzen Funktion
		 */
		var oldContent;
		var firmenlevel = '.intval($_SESSION['s_firmenlevel']).';
		function replaceDiv(div, type, value) {
			if (type == \'head\' || type == \'jobcode\' || type == \'contact\') {
				if (value == undefined) {
					stdValue = div.innerHTML;
				} else {
					stdValue = value;
				}

				if (type == \'head\') {
					promptText = \'Geben Sie einen neuen Inhalt f&uuml;r diesen Abschnitt ein!\';
				} else if (type == \'jobcode\') {
					promptText = \'Legen Sie einen neuen JobCode f&uuml;r diese Stelle fest!\' + \'<\' + \'br /\' + \'>\' + \'<\' + \'i\' + \'>\' + \'Dieser sollte f&uuml;r jede Stellenanzeige eindeutig sein,\' + \'<\' + \'br /\' + \'>\' + \'da er als Kennzeichnung in den Bewerbungen zu finden ist.\' + \'<\' + \'/i\' + \'>\';
					
					stdValue = stdValue.replace(\'Jobcode:\', \'\');
				} else if (type == \'contact\') {
					promptText = \'<\' + \'b\' + \'>\' + feldBez + \'<\' + \'/b\' + \'>\';
				}

				smallbox.prompt(promptText, trim(strip_tags(stdValue)), function(text) {
					if (text === false || text == null) { editLock = \'\'; return; }
					
					if (editLock.indexOf(\'_jobcode\') != -1) {
						text = \'Jobcode: \' + text;
					}
					
					if (text.replace(/\s/g, \'\') == \'\') {
						alert(unescape(\'Nicht genutze Textelemente werden in der Stellenanzeige ausgeblendet.\'));
					}
	
					text = text.replace(/"/g,"&quot;");
					text = text.replace(/\'/g,"&#39;");
					
					element = document.getElementById(editLock);
					value = trim(strip_tags(text).toLowerCase());
					defaults = element.getAttribute(\'default\');
					
					if (value == \'\') text = defaults;;
'.(intval($_SESSION['s_firmenlevel']) <= 1 ? '	
					if (div.id != \'field_kontakt2email\' && div.id != \'field_kontakt2webseite\' && (text.indexOf(\'@\') != -1 || text.indexOf(\'http://\') != -1 || text.indexOf(\'www.\') != -1)) {
						alert(unescape(\'Web- und eMail-Adressen sind lediglich im Kontaktfeld erlaubt.\'));
	
						text = text.replace(/([.a-zA-Z0-9_-]*)@([.a-zA-Z0-9_-]*)\.([a-zA-Z]{2,})/g, \'\');
						text = text.replace(/http:\/\/[A-Za-z0-9\.-]{3,}\.[A-Za-z]{3}/g, \'\');
						text = text.replace(/www\.[A-Za-z]{3}/g, \'\');
					}
	':'').'
					saveField(editLock, text);
					document.getElementById(editLock).innerHTML = text;
					editLock = \'\';
				});
				
				return;
			}
			
			if(isIE) {
				if(ieEditor != false) {
					value = confirm("'.utf8_decode("Sie haben ein anderes Feld bearbeitet ohne zu speichern.\\n\\nMöchten Sie Ihre letzten Änderungen jetzt speichern?").'");
					if(value == true) {
						saveIE();
						clearIEEditor();
					} else {
						clearIEEditor();
					}
				}
				
				ieEditor = div;
				div.innerHTML = "<textarea class=\"ieEditor\" style=\"width:95%; height:150px;\" id=\"textarea_" + div.id + "\">"+strip_tags(div.innerHTML.clearNL().br2nl())+"</textarea><br /><input type=\"submit\" name=\"save\" onclick=\"saveIE();clearIEEditor(); \" value=\"Speichern\" />";	
			
				return;
			}

			if(div.offsetWidth < 300) {
				toolbar = \'Small\';
			} else {
				toolbar = \'Full\';
			}
			
			editorHeight = div.offsetHeight + 70;
			
			CKEDITOR.config.toolbar_Full = ['.(intval($_SESSION['s_firmenlevel']) > 0 ? '[\'Save\'],[\'Cut\',\'Copy\',\'Paste\',\'PasteText\',\'PasteFromWord\'],[\'Bold\',\'Italic\',\'Underline\',\'Strike\'],[\'NumberedList\',\'BulletedList\'],[\'Outdent\',\'Indent\',\'-\',\'TextColor\']' : '[\'Save\',\'-\',\'PasteText\',\'PasteFromWord\']').'];
			CKEDITOR.config.toolbar_Small = CKEDITOR.config.toolbar_Full['.(intval($_SESSION['s_firmenlevel']) > 0 ? '[\'Save\',\'PasteText\',\'PasteFromWord\',\'Bold\',\'Italic\',\'Underline\']' : '[\'Save\',\'-\',\'PasteText\',\'PasteFromWord\']').'];
			
			if(div.innerHTML == div.getAttribute(\'default\')) {
				div.innerHTML = \'\';
			}

			// alert(div.id);
			editor = CKEDITOR.replace(div, {
				toolbar: toolbar,
				toolbarCanCollapse : false,
				resize_dir:"vertical",
				removePlugins: \'elementspath,save,scayt\',
				extraPlugins: \'mysave,keystrokes\',
				resize_enabled: true,
				height: editorHeight,
				resize_minWidth: 30,
				resize_maxWidth: div.offsetWidth
				}
			);
			
			CKEDITOR.on(\'key\', function(e) { saved = false; });
			CKEDITOR.on(\'instanceReady\', function(e) { parent.resizeIframe(); });
			
		}

		function saveIE() {
			saveField(ieEditor.id, document.getElementById("textarea_" + ieEditor.id).value.nl2br());
		}
		function clearIEEditor() {
			ieEditor.innerHTML = document.getElementById("textarea_" + ieEditor.id).value.nl2br();
			ieEditor = false;
		}
		
		function strip_tags(str, allowed_tags) {
			// http://kevin.vanzonneveld.net
			// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
			// +   improved by: Luke Godfrey
			// +      input by: Pul
			// +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
			// +   bugfixed by: Onno Marsman
			// +      input by: Alex
			// +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
			// +      input by: Marc Palau
			// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
			// +      input by: Brett Zamir (http://brettz9.blogspot.com)
			// +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
			// +   bugfixed by: Eric Nagel
			// +      input by: Bobby Drake
			// +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
			// *     example 1: strip_tags(\'<\' + \'p\' + \'>\' + \'Kevin\' + \'<\' + \'/p\' + \'>\' + \' <\' + \'br /\' + \'>\' + \'<\' + \'b\' + \'>\' + \'van\' + \'<\' + \'/b\' + \'>\' + \' <\' + \'i\' + \'>\' + \'Zonneveld\' + \'<\' + \'/i\' + \'>\', \'<\' + \'i\' + \'>\' + \'<\' + \'b\' + \'>\');
			// *     returns 1: \'Kevin \' + \'<\' + \'b\' + \'>\' + \'van\' + \'<\' + \'/b\' + \'>\' + \' <\' + \'i\' + \'>\' + \'Zonneveld\' + \'<\' + \'/i\' + \'>\'
			// *     example 2: strip_tags(\'<\' + \'p\' + \'>\' + \'Kevin\' + \' <\' + \'img src="someimage.png" onmouseover="someFunction()"\' + \'>\' + \'van\' + \' <\' + \'i\' + \'>\' + \'Zonneveld\' + \'<\' + \'/i\' + \'>\' + \'<\' + \'/p\' + \'>\', \'<\' + \'p\' + \'>\');
			// *     returns 2: \'<\' + \'p\' + \'>Kevin van Zonneveld\' + \'<\' + \'/p\' + \'>\'
			// *     example 3: strip_tags("\' + \'<\' + \'a href=\'http://kevin.vanzonneveld.net\'\' + \'>\' + \'Kevin van Zonneveld\' + \'<\' + \'/a\' + \'>\' + \'", "\' + \'<\' + \'a\' + \'>\' + \'");
			// *     returns 3: \'<\' + \'a href=\'http://kevin.vanzonneveld.net\'\' + \'>\' + \'Kevin van Zonneveld\' + \'<\' + \'/a\' + \'>\'
			// *     example 4: strip_tags(\'1 < 5 5 > 1\');
			// *     returns 4: \'1 < 5 5 > 1\'
			
			var key = \'\', allowed = false;
			var matches = [];
			var allowed_array = [];
			var allowed_tag = \'\';
			var i = 0;
			var k = \'\';
			var html = \'\';
			
			var replacer = function(search, replace, str) {
				return str.split(search).join(replace);
			};
			
			// Build allowes tags associative array
			if (allowed_tags) {
				allowed_array = allowed_tags.match(/([a-zA-Z]+)/gi);
			}
			
			str += \'\';
			
			// Match tags
			matches = str.match(/(<\/?[\S][^>]*>)/gi);
			
			// Go through all HTML tags
			for (key in matches) {
				if (isNaN(key)) {
					// IE7 Hack
					continue;
				}
		
				// Save HTML tag
				html = matches[key].toString();
				
				// Is tag not in allowed list? Remove from str!
				allowed = false;
				
				// Go through all allowed tags
				for (k in allowed_array) {
					// Init
					allowed_tag = allowed_array[k];
					i = -1;
					
					if (i != 0) {i = html.toLowerCase().indexOf(\'<\' + allowed_tag + \'>\');}
					if (i != 0) {i = html.toLowerCase().indexOf(\'<\' + allowed_tag + \' \');}
					if (i != 0) {i = html.toLowerCase().indexOf(\'</\' + allowed_tag);}
					
					// Determine
					if (i == 0) {
						allowed = true;
						break;
					}
				}
				
				if (!allowed) {
					str = replacer(html, \'\', str); // Custom replace. No regexing
				}
			}
			
			return str;
		}
		
		function saveField(key, value) {
			var poststr = \'value=\' + encodeURIComponent(value).replace(/&/g, \'%26\') + \'&part=\' + key;
			var url = window.location.protocol+\'//\' + window.location.host + \'/firmen/stelle/content_update/\';
			
			jQuery.ajax({
					data: poststr,
					type: "POST",
					url: url,
					timeout: 20000,
					contentType: "application/x-www-form-urlencoded;charset=utf-8"
			});
			// xhr(url, poststr);
		}
		
		var htmlEl = document.getElementsByTagName(\'html\')[0];
		htmlEl.onscroll = function() {parent.rePosCommandFrame;}
	</script>
	<script type="text/javascript">promptButtons = [\'<\' + \'a onclick="smallbox.hide(\\\'upgrade\\\')" class="button red small"\' + \'>\' + \'Ja\' + \'<\' + \'/a\' + \'>\',\'<\' + \'a onclick="smallbox.hide()" class="button red small"\' + \'>\' + \'Nein\' + \'<\' + \'/a\' + \'>\']</script>
	<script type="text/javascript">
		function prompt_message(message_number) {
			switch (message_number) {
				case 1:
					top.smallbox.confirm(\'Diese Funktion steht Ihnen erst ab dem Stellenpaket \' + \'<\' + \'strong\' + \'>\' + \'BASIS\' + \'<\' + \'/strong\' + \'>\' + \' zur Verf&uuml;gung.\' + \'<\' + \'br /\' + \'>\' + \'(Hinweis: im FREE-Paket erhalten Sie Ihre Bewerbung &uuml;ber den praktika.de Account)\' + \'<\' + \'br /\' + \'>\' + \'<\' + \'br /\' + \'>\' + \'M&ouml;chten Sie diese Funktion freischalten?\' + \'<\' + \'br /\' + \'>\', promptButtons, function(e) { if (e==\'upgrade\') {top.location.href = \'/firmen/stelle/paket.html\';}}); return true;
					break;
				case 2:
					top.smallbox.confirm(\'Diese Funktion steht Ihnen erst ab dem Stellenpaket \' + \'<\' + \'strong\' + \'>\' + \'KOMFORT\' + \'<\' + \'/strong\' + \'>\' + \' zur Verf&uuml;gung.\' + \'<\' + \'br /\' + \'>\' + \'<\' + \'br /\' + \'>\' + \'M&ouml;chten Sie diese Funktion freischalten?\' + \'<\' + \'br /\' + \'>\', promptButtons, function(e) { if (e==\'upgrade\') {top.location.href = \'/firmen/stelle/paket.html\';}}); return true;
					break;
			}
		}
	</script>
</head>
';

$htmlBody = str_replace('<head>', $replaceString1, $htmlBody);
$htmlBody = str_replace('</head>', $replaceString2, $htmlBody);

$htmlBody = preg_replace(
				array('/<body(.*)>/U', '/checkIfEmpty\(\'(.*)\'\);/','/<!DOCTYPE(.*)>/U'), 
//				array('<body onscroll="parent.rePosCommandFrame()" onload="parent.resizeIframe();" class="template">', ''),
				array('<body onload="parent.resizeIframe();" class="template">', '', '<!DOCTYPE html>'),
				$htmlBody
			);

echo $htmlBody;
