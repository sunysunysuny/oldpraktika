<?php
ob_start();
header( "Content-type: image/gif");

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

require("/home/praktika/etc/gb_config.inc.php");
$stellenid = (int)$_GET["s"];

$objStelle = new Praktika_Stelle($stellenid);
$objStelle->addView();

readfile("/home/praktika/public_html/gifs/alpha.gif");