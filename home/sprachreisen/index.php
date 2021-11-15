<?php
require("/home/praktika/etc/config.inc.php");

$_SESSION['current_page'] = PAGE_LANGUAGE_HOLIDAY;
$_SESSION['current_user_group'] = USER_GROUP_CANDIDATES;
$_SESSION['sidebar'] = 'sprachreisen';

$htmlPageTitle = '
	<p class="white">Wir bieten Standard-Kurse, Intensiv-Kurse<br />und Kurse f&uuml;r Business-Englisch an.</p>
	<p class="blue">Fragen zu Sprachreisen?<br />Wir beraten Sie gern!<br /><span class="bold">0341 2252030</span><br /><span class="normal">eMails an: programme@praktika.de</span></p>
	</p>
	<div class="grid_2 alpha">
		<p>
			<a href="/cms/fileadmin/pdfs/katalog_praktika.pdf" rel="nofollow"><img src="/styles/images/sprachreisen/katalog_mini.png" alt="Praktika Sprachreisen Katalog" title="Praktika Sprachreisen Katalog" border="0" /></a> 
			PRAKTIKA-Katalog mit Sprachreisen:<br />
			<a href="/katalog/" rel="nofollow">ansehen</a> |
			<a href="/cms/fileadmin/pdfs/katalog_praktika.pdf" rel="nofollow">downloaden</a>
		</p>
	</div>
	<div class="grid_2">
		<p>
			<a href="#ausland_aktuell" rel="nofollow"><img src="/styles/images/sprachreisen/email_mini.png" alt="Praktika Sprachreisen Katalog" title="Praktika Sprachreisen Katalog" border="0" /></a> 
			Immer auf dem neuesten Stand:<br />
			<a href="#ausland_aktuell" rel="nofollow">Ausland aktuell</a>
		</p>
	</div>
	<div class="grid_2 omega">
		<p>
			<a href="/cms/Auslandskrankenversicherung.871.0.html"><img src="/styles/images/sprachreisen/auslandskrankenversicherung_mini.png" alt="Auslandskrankenversicherung" title="Auslandskrankenversicherung" border="0" /></a>
			Richtig versichert im Ausland!<br />Top <a href="/cms/Auslandskrankenversicherung.871.0.html">Auslandskrankenversicherung</a>
		</p>
	</div>
';

$pageArray = array(
	'page_title' => 'PRAKTIKA Sprachreisen in Englisch',
	'h1_page_title' => 'PRAKTIKA Sprachreisen in Englisch',
	'html_page_title' => $htmlPageTitle,
	'grid_system' => '6-0',
	'page_image' => true
);

pageheader($pageArray);

Praktika_Static::__('sprachreisen');

if (!empty($_POST['email'])) {
	#$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
	$email = $_POST['email'];
	
	if ($email !== false) {
		$sql = 'INSERT INTO prakt2_programs.sprachreisen_emails SET email = \''.mysql_real_escape_string($_POST['email']).'\'';
		$hDB->query($sql, $praktdbmaster);
		
		$infoMailOk = true;
		
		mail('programme@praktika.de', 'Neue eMail-Adresse: Informationen zu Sprachreisen', 'Neutrale eMail mit Infos zu den Sprachreisen an "'.$_POST['email'].'" senden.', 'From: '.$_POST['email']);
	}
}
?>
<h2>Sprachreisen in die USA / Kanada</h2>
<p>Weitere M&ouml;glichkeiten Nord-Amerika zu entdecken: <a href="/cms/auslandsstudium_usa.1519.0.html">Studieren in den USA</a> | <a href="/cms/Praktikum_USA.1042.0.html">Praktikum in den USA</a> und <a href="/cms/auslandsstudium_kanada.1517.0.html">Studieren in Kanada</a> | <a href="/cms/Praktikum_Kanada.1087.0.html">Praktikum in Kanada</a></p>

<div id="sprachreise_vorschau">
	<ul>
		<li><a href="/cms/Sprachreisen_New-York.1526.0.html"><img src="/styles/images/sprachreisen/citys/sprachreise_new_york.jpg" alt="Sprachreise USA: New York" title="Englische Sprachreisen nach New York" /></a><p><a href="/cms/Sprachreisen_New-York.1526.0.html">Sprachreisen nach<br /><span>New York</span></a><br />Kommen Sie auf den Geschmack des "Big Apple".</p></li>
		<li><a href="/cms/Sprachreisen_San-Diego.1528.0.html"><img src="/styles/images/sprachreisen/citys/sprachreise_san_diego.jpg" alt="Sprachreise USA: San Diego" title="Englische Sprachreisen nach San Diego" /></a><p><a href="/cms/Sprachreisen_San-Diego.1528.0.html">Sprachreisen nach<br /><span>San Diego</span></a><br />Im kalifornischen Surferparadies spielend Englisch lernen.</p></li>
		<li><a href="/cms/Sprachreisen_San-Francisco.1529.0.html"><img src="/styles/images/sprachreisen/citys/sprachreise_san_francisco.jpg" alt="Sprachreise USA: San Francisco" title="Englische Sprachreisen nach San Francisco" /></a><p><a href="/cms/Sprachreisen_San-Francisco.1529.0.html">Sprachreisen nach<br /><span>San Francisco</span></a><br />In Frisco auf den Spuren der Flower Power-Generation.</p></li>
		<li class="last"><a href="/cms/Sprachreisen_Los-Angeles.1527.0.html"><img src="/styles/images/sprachreisen/citys/sprachreise_los_angeles.jpg" alt="Sprachreise USA: Los Angeles" title="Englische Sprachreisen nach Los Angeles" /></a><p><a href="/cms/Sprachreisen_Los-Angeles.1527.0.html">Sprachreisen nach<br /><span>Los Angeles</span></a><br />Stars und Sternchen am Hollywoodboulevard.</p></li>
	</ul>
	<ul>
		<li><a href="/cms/Sprachreisen_Fort-Lauderdale.1530.0.html"><img src="/styles/images/sprachreisen/citys/sprachreise_fort_lauderdale.jpg" alt="Sprachreise USA: Fort Lauderdale" title="Englische Sprachreisen nach Fort Lauderdale" /></a><p><a href="/cms/Sprachreisen_Fort-Lauderdale.1530.0.html">Sprachreisen nach<br /><span>Fort Lauderdale</span></a><br />Im "Venedig der USA" den Charme Floridas erleben.</p></li>
		<li><a href="/cms/Sprachreisen_Vancouver.1535.0.html"><img src="/styles/images/sprachreisen/citys/sprachreise_vancouver.jpg" alt="Sprachreise Kanada: Vancouver" title="Englische Sprachreisen nach Vancouver" /></a><p><a href="/cms/Sprachreisen_Vancouver.1535.0.html">Sprachreisen nach<br /><span>Vancouver</span></a><br />Kanadische Weltmetropole einen Steinwurf von den USA entfernt.</p></li>
		<li class="empty"></li>
		<li class="last empty"></li>
	</ul>
	<h2>Sprachreisen nach England</h2>
	<p>Weitere Bildungsm&ouml;glichkeiten in England: <a href="/cms/auslandsstudium_england.1518.0.html">Studieren in England</a> oder ein <a href="/cms/Praktikum_England.1544.0.html">Praktikum in England</a></p>
	<ul>
		<li><a href="/cms/Sprachreisen_London.1524.0.html"><img src="/styles/images/sprachreisen/citys/sprachreise_london.jpg" alt="Sprachreise England: London" title="Englische Sprachreisen nach London" /></a><p><a href="/cms/Sprachreisen_London.1524.0.html">Sprachreisen nach<br /><span>London</span></a><br />Lernen, was es hei&szlig;t, "very british" zu sein.</p></li>
		<li><a href="/cms/Sprachreisen_Cambridge.1523.0.html"><img src="/styles/images/sprachreisen/citys/sprachreise_cambridge.jpg" alt="Sprachreise England: Cambridge" title="Englische Sprachreisen nach Cambridge" /></a><p><a href="/cms/Sprachreisen_Cambridge.1523.0.html">Sprachreisen nach<br /><span>Cambridge</span></a><br />Den Geist der altehrw&uuml;rdigen Universit&auml;tsstadt atmen.</p></li>
		<li><a href="/cms/Sprachreisen_Oxford.1525.0.html"><img src="/styles/images/sprachreisen/citys/sprachreise_oxford.jpg" alt="Sprachreise England: Oxford" title="Englische Sprachreisen nach Oxford" /></a><p><a href="/cms/Sprachreisen_Oxford.1525.0.html">Sprachreisen nach<br /><span>Oxford</span></a><br />Vom Weltruhm der &auml;ltesten englischen Bildungsst&auml;tte profitieren.</p></li>
		<li class="last"><a href="/cms/Sprachreisen-Bournemouth.1589.0.html"><img src="/styles/images/sprachreisen/citys/sprachreise_bournemouth.jpg" alt="Sprachreise England: Bournemouth" title="Englische Sprachreisen nach Bournemouth" /></a><p><a href="/cms/Sprachreisen-Bournemouth.1589.0.html">Sprachreisen nach<br /><span>Bournemouth</span></a><br />Erleben Sie das j&auml;hrliche Air Festival hautnah am Strand!</p></li>
	</ul>
	<h2>Sprachreisen nach Australien / Weltweit</h2>
	<p>Weitere Praktikumsprogramme und Studiumsm&ouml;glichkeiten: <a href="/cms/auslandsstudium_australien.1520.0.html">Studieren in Australien</a> | <a href="/cms/Praktikum_Sydney.1413.0.html">Praktikum in Australien</a> | &Uuml;bersicht aller <a href="/auslandspraktikum/">Auslandspraktika</a>.</p>
	<ul>
		<li><a href="/cms/Sprachreisen_Sydney.1534.0.html"><img src="/styles/images/sprachreisen/citys/sprachreise_sydney.jpg" alt="Sprachreise Australien: Sydney" title="Englische Sprachreisen nach Sydney" /></a><p><a href="/cms/Sprachreisen_Sydney.1534.0.html">Sprachreisen nach<br /><span>Sydney</span></a><br />Down Under das Land der Aboriginis und K&auml;ngurus entdecken.</p></li>
		<li><a href="/cms/Sprachreisen_Perth.1533.0.html"><img src="/styles/images/sprachreisen/citys/sprachreise_perth.jpg" alt="Sprachreise Australien: Perth"  title="Englische Sprachreisen nach Perth" /></a><p><a href="/cms/Sprachreisen_Perth.1533.0.html">Sprachreisen nach<br /><span>Perth</span></a><br />Morgens Sprachkurs, abends Party am Indischen Ozean.</p></li>
		<li><a href="/cms/Sprachreisen_Kapstadt.1531.0.html"><img src="/styles/images/sprachreisen/citys/sprachreise_kapstadt.jpg" alt="Sprachreise S&uuml;dafrika: Kapstadt"  title="Englische Sprachreisen nach Kapstadt" /></a><p><a href="/cms/Sprachreisen_Kapstadt.1531.0.html">Sprachreisen nach<br /><span>Kapstadt</span></a><br />Mit guter Hoffnung im internationalen S&uuml;dafrika Englisch-Profi werden.</p></li>
		<li class="last"><a href="/cms/Sprachreisen_Malta.1532.0.html"><img src="/styles/images/sprachreisen/citys/sprachreise_malta.jpg" alt="Sprachreise Malta" title="Englische Sprachreisen nach Malta" /></a><p><a href="/cms/Sprachreisen_Malta.1532.0.html">Sprachreisen nach<br /><span>Malta</span></a><br />An t&uuml;rkisfarbenem Meer beim Sonnenbaden Vokabeln lernen.</p></li>
	</ul>
</div>

<div class="grid_3 alpha">
	<h2 id="ausland_aktuell">Ausland aktuell</h2>
	<p>
		Sie m&ouml;chten gern informiert bleiben, aber nicht jeden Tag nachsehen ob es neue Preise, 
		Angebote oder Specials gibt? Dann nutzen Sie unseren Auslandsticker und wir informieren Sie wenn es neue Angebote und Informationen rund um das Thema Sprachreisen und Bildung im Ausland gibt.
	</p>
</div>

<div class="grid_3 omega">
	<h2>PRAKTIKA Qualit&auml;t</h2>
	<p>
		<img src="/styles/images/sprachreisen/qual.png" alt="PRAKTKA Qualit&auml;t" style="float:right;" />Erleben Sie PRAKTIKA Sprachreisen mit Qualit&auml;tssiegel, denn nur Sprachschulen, die modernsten Standards entsprechen, werden in unser Programm aufgenommen. Mit uns verbringen Sie eine unvergessliche Zeit im Ausland und lernen dabei an den sch&ouml;nsten Flecken der Erde Englisch - garantiert!
		<br /><br /><a href="/cms/praktika-qualitaet.1561.0.html">&raquo; Weitere Informationen dazu</a>
	</p>
</div>

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
		pageNumber: 28,
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
<div class="grid_4 alpha">
	<div id="catalogue"></div>
</div>
<div class="grid_2 omega">
	<p>
		<a href="http://praktika.de/cms/Katalog-kostenfrei-bestellen.1433.0.html" rel="nofollow">&raquo; Katalog bestellen</a><br />
		<a href="https://www.praktika.de/cms/fileadmin/pdfs/katalog_praktika.pdf" target="_blank" rel="nofollow">&raquo; Katalog als Pdf downloaden</a><br />
		<a href="https://www.praktika.de/home/sprachreisen/Anmeldeunterlagen_Sprachreisen.pdf" target="_blank" rel="nofollow">&raquo; Anmeldeunterlagen</a>
	</p>
</div>

<?php
$_SESSION['sidebar'] = '';
bodyoff();
?>