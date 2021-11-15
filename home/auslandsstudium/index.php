<?php
require("/home/praktika/etc/config.inc.php");

$_SESSION['current_page'] = PAGE_ABROAD_STUDIES;
$_SESSION['current_user_group'] = USER_GROUP_CANDIDATES;
$_SESSION['sidebar'] = 'auslandsstudium';

pageheader(array('page_title' => 'Auslandsstudium & Auslandssemester'));

Praktika_Static::__('page_ausland');
?>

<p>PRAKTIKA hat Universit&auml;ten mit den besten Bachelor-, MBA- und Doktorprogrammen besucht, getestet und bewertet. Kosten, Qualit&auml;t, Dauer des Auslandsstudium, Akkreditierung und Anerkennung der Kurse in Deutschland waren die wichtigsten Auswahlkriterien.</p>
<p><strong>Wo m&ouml;chten Sie im Ausland studieren?</strong><span class="auslandslink"><a href="/auslandspraktikum/">zum Auslandspraktikum</a></span></p>

<div id="programmvorschau" class="clearfix">
	<ul>
		<li><a href="/cms/auslandsstudium_kanada.1517.0.html"><img src="/styles/images/auslandsstudium/studium_kanada.gif" alt="studieren kanada" /></a><a href="/cms/auslandsstudium_kanada.1517.0.html">Studieren in Vancouver</a><p>Bachelor-,<br /> Master-, Zertifikat- &amp; Austauschprogramm</p></li>
<!--		<li><a href="/cms/auslandsstudium_england.1518.0.html"><img src="/styles/images/auslandsstudium/studium_england.gif" alt="studieren england" /></a><a href="/cms/auslandsstudium_england.1518.0.html">Studieren in London</a><p>Bachelor, MBA, &amp; Studienvorbereitungs- kurse</p></li> -->
		<li><a href="/cms/auslandsstudium_usa.1519.0.html"><img src="/styles/images/auslandsstudium/studium_usa.gif" alt="studieren usa" /></a><a href="/cms/auslandsstudium_usa.1519.0.html">Studieren in<br /> San Diego</a><p>Bachelor, MBA, MSIM, Doktoprogramm, Auslandssemester</p></li>
		<li><a href="/cms/auslandsstudium_australien.1520.0.html"><img src="/styles/images/auslandsstudium/studium_australien.gif" alt="studieren australien" /></a><a href="/cms/auslandsstudium_australien.1520.0.html">Studieren in Sydney</a><p>Bachelor, MCom, MIB, Austauschsemester &amp; Studienvorbereitung</p></li>
		<li class="last"><a href="/cms/auslandsstudium_usa.1610.0.html"><img src="/styles/images/auslandsstudium/studium_usa_alliant.gif" alt="studieren usa" /></a><a href="/cms/auslandsstudium_usa.1610.0.html">Studieren in<br /> San Diego</a><p>Bachelor, MBA, MSIM, Doktoprogramm, Auslandssemester</p></li>
	</ul>
</div>

<h2>Fragen zum Studium im Ausland?</h2>
<p>Sie haben weitere Fragen zum Thema &quot;Studieren im Ausland&quot;? Dann kontaktieren Sie uns einfach, denn wir beraten Sie gern ausf&uuml;hrlich und pers&ouml;nlich!</p>
<p class="control"><a href="/auslandsstudium/nachricht/" class="button">Jetzt kostenfrei Informationen anfordern!</a></p>

<div class="clear"></div>

<script type="text/javascript" src="/scripts/swfobject/swfobject.js"></script>
<script type="text/javascript">
	var attributes = {
	  id: 'catalogue'
	};
	
	var params = {
		allowfullscreen: 'true',
		allowScriptAccess: 'always',
		menu: 'false'
	};
 
	var flashvars = {
		jsAPIClientDomain: 'www.praktika.de',
		mode: 'embed',
		folderId: 'c444d060-685a-42a3-aeaa-f3ecceb3caf4',
		layout: 'http%3A%2F%2Fskin.issuu.com%2Fv%2Flight%2Flayout.xml',
		backgroundColor: 'ffffff',
		showFlipBtn: 'true',
		pageNumber: 46,
		/*documentId: '100528114347-f6b4bff2f5b44e0298cfaa94baf92035',
		docName: 'katalog_web',*/
		username: 'PRAKTIKA',
		loadingInfoText: 'PRAKTIKA%20-%20Auslandspraktikum%20und%20Sprachreisen',
		et: '1275491549319',
		er: '46'
	};
 
	swfobject.embedSWF("http://static.issuu.com/webembed/viewers/style1/v1/IssuuViewer.swf", "catalogue", "650", "460", "9.0.0","swfobject/expressInstall.swf", flashvars, params, attributes);
</script>

<h2>Online Katalog ansehen</h2>
<div id="catalogue"></div>

<div style="text-align:center;">
	<a href="https://www.praktika.de/cms/fileadmin/download/auslandsprogramm/katalog_web.pdf" target="_blank">&raquo; Katalog als PDF downloaden</a>
</div>

<?php
$_SESSION['sidebar'] = '';
bodyoff();
?>