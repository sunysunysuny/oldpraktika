<?
ob_start();
require_once("/home/praktika/etc/gb_config.inc.php");

$objFirma = new Praktika_Firma((int)$_GET["firmenid"]);
$logoUrl = $objFirma->getLogo("header");
$stellen = $objFirma->getAllActiveJobs();

$zView->assign("data", $objFirma->getData());
$zView->assign("logo", $logoUrl);

$zView->assign("stellen", $stellen);

echo $zView->render("allestellen.php");