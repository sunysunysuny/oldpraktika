<?php
ob_start();
require_once("../../etc/gb_config.inc.php");

$objFM = new Praktika_Filemanager("comp",$_SESSION["s_firmenid"]);


$sql = "SELECT firma FROM firmen WHERE id = ".$_SESSION["s_firmenid"];
$result = $hDB->query($sql, $praktdbslave);
$firmenname = mysql_result($result,0,"firma");
if($_SESSION['s_firmenid'] == 5361) {
	$firmenname = 'PRAKTIKA';
}
$objFM->addFile($_FILES["file"], $firmenname, array(
    "max_size" => 1024 * 1024 * 2,
    "filename" => $_POST["filename"]
));

if($objFM->getError()) {
    die($objFM->getError());
}
?>