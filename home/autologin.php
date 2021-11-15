<?php
ob_start();
require("/home/praktika/etc/gb_config.inc.php");

$return = Praktika_Autologin::login(trim($_GET["i"]));

if(!is_int($return) && ($return === "wrong" || $return === "expired" || $return === "empty")) {
	header("Location: /login/");
	exit();
}

if(is_int($return)) {
	$sql = "SELECT id FROM prakt2.bearbeiter WHERE firmenid = ".$return." AND inactive = 'false' ORDER BY admin = 'true' DESC";
	$result = $hDB->query($sql, $praktdbslave);
	
	if(mysql_num_rows($result) == 0){
		header("Location: /login/");
		exit();
	}

	$row = mysql_fetch_assoc($result);

	Praktika_User::loginById($row["id"], LOGIN_COMPANIES);
#usleep(1000);
	header("Location: /firmen/angeboteliste/");
	exit();
}
	header("Location: /firmen/angeboteliste/");
	exit();