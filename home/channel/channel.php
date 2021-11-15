<?
ob_start();
require_once("../../../etc/gb_config.inc.php");

$objChannel = new Praktika_Channel($_GET["key"]);

echo file_get_contents($objChannel->getFile());
