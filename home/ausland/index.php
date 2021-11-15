<?php
require("/home/praktika/etc/config.inc.php");

$_SESSION['current_page'] = PAGE_ABROAD;
$_SESSION['current_user_group'] = USER_GROUP_CANDIDATES;
$_SESSION['sidebar'] = 'ausland';

pageheader(array('page_title' => 'Auslandspraktikum - Wir organisieren<br /> Ihr Praktikum im Ausland'));

Praktika_Static::__('page_ausland');
?>

<script type="text/javascript" src="/scripts/ajax/ausland.js"></script>

<div class="grid_1 alpha">
	<div id="auslandsbox" class="box">
	  <h3>F&uuml;r Interessenten:</h3>
		<p>
		<a href="/cms/Organisationsablauf.335.0.html">Organisationsablauf</a><br />
		<a href="/cms/Inhalte-des-Praktikumsprogramms.1024.0.html">Was wir bieten</a><br />
		<a href="/cms/Ihre-Vorteile.1011.0.html">Ihre Vorteile</a><br />
		<a href="/cms/Ueber-unser-Team.971.0.html">&Uuml;ber unser Team</a><br />
		<a href="/cms/Kostenlose-Unterlagen-anfordern.968.0.html">Weitere Informationen</a><br />
		<a href="/cms/FAQ.1009.0.html">FAQ</a><br />
		<a href="/downloads/auslandsprogramm/Preisuebersicht.pdf">Preis&uuml;bersicht</a><br />
		<a href="/cms/Erfahrungsberichte_Auslandspraktikum.973.0.html">Erfahrungsberichte</a><br />
		<a href="/cms/fileadmin/download/auslandsprogramm/Anmeldeunterlagen.pdf"><strong>Anmeldeunterlagen</strong></a><br />
		<a href="/cms/Kontakt.966.0.html">Kontakt</a><br />
		</p>
	</div>
</div>
<div class="grid_3 omega">
	<p>Wir bieten individuelle Praktikumsvermittlung, bei der Sie auf unser langj&auml;hriges Know-How setzen k&ouml;nnen. Sagen Sie uns einfach nur, was Sie sich f&uuml;r Ihr Praktikum im Ausland vorstellen und lehnen Sie sich entspannt zur&uuml;ck, w&auml;hrend wir Ihnen ein ma&szlig;geschneidertes Auslandspraktikum organisieren.</p>
	<p>&raquo; Hier finden Sie hunderte aktuelle <a href="/unternehmenssuche/6896/INTERNSCOUT">Praktikumstellen im Ausland.</a><br /><br />&raquo; Wir informieren Sie auch gern zum <a href="/auslandsstudium/">Auslandsstudium.</a><br /></p><!--&raquo; Offene Stellen: <a href="/cms/praktikanten-gesucht.1564.0.html"><strong>Kurzfristig zu besetzen!</strong></a> (<? echo date('d.m.Y', time()); ?>)-->

<div class="prefix_">
 <div class="shortnotes">
  <p><strong>Praktikanten gesucht!</strong></p>
  <? 
  $sql = "SELECT * FROM prakt2.stellen WHERE firmenid = 6896 AND topstelle = 1 AND inactive = 'false'  AND deleted = 'false' ORDER BY RAND() LIMIT 3";
  $result = $hDB->query($sql, $praktdbslave);
  
  while($row = mysql_fetch_assoc($result)) {
  ?>
  <p><a href="<?=Praktika_Stelle::getURL($row) ?>"><strong><?=$row["taetigkeit"] ?></strong></a>
  </p>
  <? } ?>
   </div>
   
</div>	
</div>
<div class="clear"></div>


<h2>Informieren Sie sich jetzt unverbindlich &uuml;ber ein Auslandspraktikum:</h2>
<div id="programmvorschau">
	<ul>
		<li><a href="/cms/Praktikum_USA.1042.0.html"><img src="/styles/images/ausland/us_big.gif" alt="Auslandspraktikum USA" /></a><a href="/cms/Praktikum_USA.1042.0.html">Praktikum in den USA</a><p>Bis zu 18 Monate lang den "American Way of Life" leben</p></li>
		<li><a href="/cms/US_Hoteljob.962.0.html"><img src="/styles/images/ausland/ushotel_big.gif" alt="Auslandspraktikum USA Hotelpraktikum" /></a><a href="/cms/US_Hoteljob.962.0.html">US Hotelprogramm</a><p>Verg&uuml;tete Hoteljobs<br /> f&uuml;r Fachkr&auml;fte der Hotelbranche</p></li>
		<li><a href="/cms/Praktikum_Kanada.1087.0.html"><img src="/styles/images/ausland/can_big.gif" alt="Auslandspraktikum Kanada" /></a><a href="/cms/Praktikum_Kanada.1087.0.html">Praktikum in Kanada</a><p>F&uuml;r Naturliebhaber und Gro&szlig;stadtcowboys immer die 1. Wahl</p></li>
		<li class="last"><a href="/cms/Praktikum_England.1544.0.html"><img src="/styles/images/ausland/eng_big.gif" alt="Auslandspraktikum England" /></a><a href="/cms/Praktikum_England.1544.0.html">Praktikum in England</a><p>Gleich nach dem Abi ab ins Ausland!</p></li>
	</ul>
	<ul>
		<li><a href="/cms/Praktikum-New-York-City.1590.0.html"><img src="/styles/images/ausland/nyc_big.png" alt="Auslandspraktikum New York City" /></a><a href="/cms/Praktikum-New-York-City.1590.0.html">Praktikum in<br /> New York City</a><p>Ihr Praktikum in der Stadt, die niemals schl&auml;ft</p></li>
		<li><a href="/cms/Praktikum_Kfz-Mechaniker-USA.1591.0.html"><img src="/styles/images/ausland/car_big.png" alt="Auslandspraktikum USA KFZ" /></a><a href="/cms/Praktikum_Kfz-Mechaniker-USA.1591.0.html">Praktikum f&uuml;r Kfz-Profis</a><p>Das USA-Praktikum f&uuml;r alle Kfz- und Automotive Profis</p></li>
		<li><a href="/cms/Praktikum_Neuseeland.1593.0.html"><img src="/styles/images/ausland/nz_big.png" alt="Auslandspraktikum Neuseeland" /></a><a href="/cms/Praktikum_Neuseeland.1593.0.html">Praktikum in Neuseeland</a><p>Abenteurer aufgepasst: Praktikanten in Auckland gesucht</p></li>
		<li class="last"><a href="/cms/Praktikum_Suedafrika.853.0.html"><img src="/styles/images/ausland/sa_big.gif" alt="Auslandspraktikum S&uuml;dafrika" /></a><a href="/cms/Praktikum_Suedafrika.853.0.html">Praktikum in S&uuml;dafrika</a><p>Praktikum und Abenteuer am Kap der guten Hoffnung</p></li>
	</ul>
	<ul>				
		<li><a href="/cms/Praktikum_Grossbritannien.1041.0.html"><img src="/styles/images/ausland/uk_big.gif" alt="Auslandspraktikum Gro&szlig;britannien" /></a><a href="/cms/Praktikum_Grossbritannien.1041.0.html">Praktikum in Gro&szlig;britannien</a><p>Der absolute Klassiker und immer ein voller Erfolg!</p></li>
		<li><a href="/cms/Praktikum_Irland.1438.0.html"><img src="/styles/images/ausland/ire_big.gif" alt="Auslandspraktikum Irland" /></a><a href="/cms/Praktikum_Irland.1438.0.html">Praktikum in Irland</a><p>Die gr&uuml;ne Insel als reizvolle Alternative zu England</p></li>
		<li><a href="/cms/Praktikum_Singapur.1411.0.html"><img src="/styles/images/ausland/sing_big.gif" alt="Auslandspraktikum Singapur" /></a><a href="/cms/Praktikum_Singapur.1411.0.html">Praktikum in Singapur</a><p>Asia light im hochentwickelten Tigerstaat Asiens</p></li>
		<li class="last"><a href="/cms/Praktikum_China.319.0.html"><img src="/styles/images/ausland/cn_big.gif" alt="Auslandspraktikum China" /></a><a href="/cms/Praktikum_China.319.0.html">Praktikum in China</a><p>Jahrtausende alte Kultur mit ungeahntem Wirtschaftsboom</p></li>
	</ul>
	<ul>				
		<li><a href="/cms/Praktikum_Indien.1412.0.html"><img src="/styles/images/ausland/ind_big.gif" alt="Auslandspraktikum Indien" /></a><a href="/cms/Praktikum_Indien.1412.0.html">Praktikum in Indien</a><p>Mumbai, Bangalore oder Neu Delhi? Sie haben die Wahl!</p></li>
		<li><a href="/cms/Spanien_Hotelpraktikum.140.0.html"><img src="/styles/images/ausland/es_big.gif" alt="Auslandspraktikum Spanien Hotelpraktikum" /></a><a href="/cms/Spanien_Hotelpraktikum.140.0.html">Hotelpraktikum<br /> in Spanien</a><p>Ihr Hotelpraktikum in Europas beliebtester Ferienregion</p></li>
		<li><a href="/cms/Praktikum-Spanien.1560.0.html"><img src="/styles/images/ausland/spanien_big.gif" alt="Auslandspraktikum Spanien" /></a><a href="/cms/Praktikum-Spanien.1560.0.html">Praktikum in Spanien</a><p>Wir organisieren Ihr Auslandspraktikum in Barcelona</p></li>
		<li class="last"><a href="/cms/Praktikum_Dubai.956.0.html"><img src="/styles/images/ausland/vae_big.gif" alt="Auslandspraktikum Dubai" /></a><a href="/cms/Praktikum_Dubai.956.0.html">Praktikum in Dubai</a><p>Die Orient Metropole er&ouml;ffnet Ihnen un- geahnte M&ouml;glichkeiten</p></li>
	</ul>
	<ul>				
		<li><a href="/cms/Praktikum_Frankreich.139.0.html"><img src="/styles/images/ausland/fr_big.gif" alt="Auslandspraktikum Frankreich" /></a><a href="/cms/Praktikum_Frankreich.139.0.html">Praktikum in Frankreich</a><p>Leider bieten wir aktuell keine Vermittlung nach Frankreich an</p></li>
		<li><a href="/cms/Praktikum_Sydney.1413.0.html"><img src="/styles/images/ausland/aus_big.gif" alt="Auslandspraktikum Australien" /></a><a href="/cms/Praktikum_Sydney.1413.0.html">Praktikum in Australien</a><p>Sprachkurs? Praktikum? Demi Pair? Alles ist m&ouml;glich</p></li>
		<li><a href="/cms/Praktikum_Chile.809.0.html"><img src="/styles/images/ausland/chile_big.gif" alt="Auslandspraktikum Chile" /></a><a href="/cms/Praktikum_Chile.809.0.html">Praktikum<br /> in Chile</a><p>Leider bieten wir aktuell keine Vermittlung nach Chile an</p></li>
		<li class="last"></li>
	</ul>
</div>

<div class="clear"></div>

<h2>Aktuelle Auslandsstellen</h2>
<p>Sie suchen konkrete Stellen f&uuml;r ein Praktikum im Ausland? Dann benutzen Sie doch unsere Suche. Dort haben Sie die M&ouml;glichkeit unter Verwendung des Filters &quot;Auslandspraktikum&quot; sich Praktikumsstellen im Ausland anzeigen zu lassen. <a href="/unternehmenssuche/6896/INTERNSCOUT">Auslandspraktika suchen</a> </p>

<div class="clear"></div>

<h3>Artikel zum Thema Auslandspraktikum:</h3>
<div id="feed_ausland">
<?php echo file_get_contents('/home/praktika/etc/autowork/ausland/ausland.txt'); ?>
</div>

<?php
$_SESSION['sidebar'] = '';
bodyoff();
?>