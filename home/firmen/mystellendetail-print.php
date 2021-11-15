<?php
#$restricted = "yes";
#$gruppe = "firmen";
require("/home/praktika/etc/gb_config.inc.php");

$stellenid = $_GET['stellenid'];
$firmenid = $_SESSION['s_firmenid'];
$_SESSION['sidebar'] = 'none';
$_SESSION['restricted'] = RESTRICTED_COMPANY;
$_SESSION['current_user_group'] = USER_GROUP_COMPANIES;

pageheader(array('page_title' => 'Stellenstatistik auf praktika.de'));

$monatsArray = array("Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember");

$objFirma = new Praktika_Firma($_SESSION["s_firmenid"]);
$stellen = $objFirma->getAllExistingJobs();

$checkboxes = array();
$checkboxes[] = array("id" => "Alle", "title" => "Alle Stellen");

# Statistik-CSS-Modul wird hinzugefügt
Praktika_Static::__("statistik");

# Layout.php wird geladen (Wegen CheckBoxList-Funktion)
$layout = new Praktika_Layout();

?>
<!--[if IE]><script language="javascript" type="text/javascript" src="../excanvas.min.js"></script><![endif]-->

<link type="text/css" media="screen" rel="stylesheet" href="/styles/5ed22b856cd083b344452774cd129598/generateCSS.css" />
<style type="text/css">
input[type="checkbox"] {
	width: 20px !important;
	border: 1px solid #bbb !important;
	background: #f8f8f8 !important;
	/*padding: 1px 0 0;3px 0;*/
}
.chooser div {
	margin-bottom:1em;
}
table tr td {
	width:auto;
}
/**
 * Clearfix hack
 */
.clearfix:after {
	content: ".";
	display: block;
	height: 0;
	clear: both;
	visibility: hidden;
}

/* Hide from IE Mac \*/
.clearfix {
	display: block;
}

table tr th {
	width:auto;
}
.print_date {
	font-weight:bold;
}
.dataTable {
	width:100% !important;
}
</style>
<!--[if IE]>
<script language="javascript" type="text/javascript" src="/scripts/excanvas.compiled.js"></script>
<![endif]-->
<link rel="stylesheet" href="/scripts/jquery/datepicker/css/datepicker.css" type="text/css" />

<script language="javascript" type="text/javascript" src="/scripts/framework/base.js"></script>
<script language="javascript" type="text/javascript" src="/scripts/jquery/jquery-1.4.2.js"></script>
<script language="javascript" type="text/javascript" src="/scripts/jquery/charts/chartv2.js"></script>
<script language="javascript" type="text/javascript" src="/scripts/jquery/charts/flot/jquery.flot.js"></script>
<script language="javascript" type="text/javascript" src="/scripts/jquery/charts/flot/jquery.flot.selection.js"></script>

<script type="text/javascript" src="/scripts/jquery/datepicker/js/datepicker.js"></script>
<script type="text/javascript" src="/scripts/jquery/datepicker/js/eye.js"></script>

<script type="text/javascript" src="/scripts/jquery/datepicker/js/utils.js"></script>
<script type="text/javascript" src="/scripts/jquery/datepicker/js/layout.js?ver=1.0.2"></script>

<script type="text/javascript">
    P_Framework.addModule("ajax");
	P_Framework.addModule("display");
	P_Framework.addModule("string");
    var Wochentag = new Array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
	var flot_overview;
    var flot_plot;
    var aktDataSet;
    var statDays = 0;
    var startDate = 0;
	var previousPoint = null;
	var stellen = {};
	var writeTablePerm = false;

	stellen["Alle"] = "Alle Stellen des Unternehmens";
<?
for($a = 0;$a < count($stellen);$a++) {
	$checkboxes[] = array("id" => $stellen[$a]["stellenid"], "title" => "<span class='stelle_".($stellen[$a]["inactive"]=="true"?"inactive":"active")."'>".$stellen[$a]["stellenid"]." - ".stripslashes($stellen[$a]["taetigkeit"])."</span>");
	echo 'stellen['.$stellen[$a]["stellenid"].'] = "'.str_replace('"',"'",strip_tags($stellen[$a]["taetigkeit"])).'";'."\n";
}
?>
</script>
<img src="/styles/images/logo.png" style="float:right;margin-top:-60px;" alt="Praktika.de Logo" /><br />
    <div class="itemChooser" id="itemChooser" style="display:none;">
        <?
		$checked = explode(",",$_GET["stellenids"]);
		
        echo Praktika_Layout::generateCheckableList("itemChooser", $checkboxes, array("class" => "chooser", "height" => "400px", "width" => "360px", "other" => "padding:5px;font-size:10px !important;"), $checked, array("onclick" => "loadData();"));
        ?>
    </div>
    <div style="text-align:right; margin-bottom:10px;">
        Zeitraum:
        <span class="print_date"><?=htmlentities($_GET["from"]) ?></span> -
        <span class="print_date"><?=htmlentities($_GET["to"]) ?></span>
        <input type="text" style="display:none;"maxlength="10" id="time_from" value="<?=htmlentities($_GET["from"]) ?>" />
        <input type="text" style="display:none;" maxlength="10" style="" id="time_to" value="<?=htmlentities($_GET["to"]) ?>" />

    </div>

	<div class="clearfix statContainer" style="margin-top:20px !important;">
		<div id="placeholder" style="width:80%;height:300px; margin-bottom:20px;">
<br /><br /><div style='text-align:center;font-weight:bold;'>Grafik wird generiert<br /><br /><img src='/styles/images/ajax/loading_2.gif' alt='Lade' /><br /><br />Bitte warten ...</div>
		</div>
		<div id="statLegend"></div>
		<div id="overview" style="display:none; visibility:hidden; margin-left:50px;margin-top:20px;width:400px;height:50px"></div>

		<span id="x" style="display:none;">0</span><span id="y" style="display:none;">0</span>
    </div>
		<div class="dataTable">
			<input type="checkbox" onclick="writeTable(aktDataSet);" id="komplexVersion" name="komplexVersion" checked="checked" /> Erweiterte Ansicht
			<div id="dataTable_data">
				<table id="data_table" style="font-size:smaller;">
					<tr id="data_head_year"></tr>
					<tr id="data_head_month"></tr>
					<tr id="data_head_tr"></tr>
				</table>
				* = (Sucheinblendungen / Merkzetteleinträge / Bewerbungen), ** = Noch keine Daten erfasst
			</div>

		</div>
    <script type="text/javascript">P_Framework.write();</script>
<script type="text/javascript">
	writeTablePerm = true;
	printVersion = true;
</script>
<style type="text/css" media="print">
	table#data_table {
		font-size:10px !important;
}
</style>
<? bodyoff(); ?>