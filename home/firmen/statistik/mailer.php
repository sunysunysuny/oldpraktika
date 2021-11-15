<?php
require_once("/home/praktika/etc/gb_config.inc.php");

// a = action
if(!empty($_GET["a"])) {
	
	switch($_GET["a"]) {
		case "add":
			if(!empty($_GET["m"]) && !empty($_GET["s"])) {
				$ids = explode(",",$_GET["s"]);
				for($a = 0;$a < count($ids);$a++) {
					$ids[$a] = (int)$ids[$a];
				}
				$sql = "INSERT INTO prakt2.statistik_mail SET firmenid = '".$_SESSION["s_firmenid"]."', stellenids = '".implode(",", $ids)."', mail = '".mysql_real_escape_string($_GET["m"])."'";
				$hDB->query($sql, $praktdbmaster);
			}
			break;
	}

}