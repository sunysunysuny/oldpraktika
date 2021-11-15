<?php
require_once("/home/praktika/etc/gb_config.inc.php");

$firstDate = 1258956000;

pageheader(array("page_title" => "", "page_hideHeader" => true));

$stellenid = (int)$_GET["stellenid"];

$objStelle = new Praktika_Stelle($stellenid);
$data = $objStelle->getData();

if(!empty($_GET["d"])) {
	$dateA = $dateB = strtotime($_GET["d"]);
} else {
	$dateB = time();
	$dateA = $firstDate;
	$hideDate = true;
}
$log = $objStelle->getLog($dateA, $dateB,0,20);
$logR = array();

for($a = 0;$a < count($log);$a++) {
	switch($log[$a]["mode"]) {
		case 1:
			if($log[$a]["value"] == "1") {
				$text = "Stelle wurde aktiviert";
			} else {
				$text = "Stelle wurde deaktiviert";
			}
			break;
		case 2:
			$platzhalter = array("Stellentitel", "UnserUnternehmen_Kopf","IhreAufgaben_Kopf","IhrProfil_Kopf","UnsereLeistungen_Kopf","Kontakt_Kopf","UnserUnternehmen","IhreAufgaben","IhrProfil","UnsereLeistungen","Kontakt","Jobcode","Untertitel","Entgelt","Beschreibung","Flash");
			$text = "Feld <i>'".$platzhalter[$log[$a]["value"]]."'</i> bearbeitet.";
			break;
	}

	$logR[] = array(date("d.m.Y H:i", $log[$a]["date"]),$text, $log[$a]["anrede"]." ".$log[$a]["name"]);
}


$zView->assign("log", $logR);
$zView->assign("stelle", $data);
$zView->assign("datumStr", $hideDate?"":($dateA!=$dateB?date("d.m.Y", $dateA)."-".date("d.m.Y", $dateB):date("d.m.Y", $dateA)));
echo $zView->render("stellenhistory.php");

bodyoff();