<?php
require("/home/praktika/etc/gb_config.inc.php");

$stellenid = (int)$_POST["sID"];
$firmenid = (int)$_SESSION['s_firmenid'];
$firmenlevel = (int)$_SESSION['s_firmenlevel'];
if($_SESSION["chosen_firmenlevel"] > $firmenlevel) $firmenlevel = $_SESSION["chosen_firmenlevel"];

$objStelle = new Praktika_Stelle($stellenid);
$objStelle->checkAccess(array("firmenid" => $firmenid, "firmenlevel" => $firmenlevel));

// Premium-Kunden werden immer durchgelassen
if($firmenlevel < 3) {
	// Alle aktiven Stellen werden gezählt
	$countStellen = mysql_db_query($database_db,'SELECT COUNT(*) as counter FROM stellen WHERE firmenid = '.$_SESSION['s_firmenid'].' AND inactive = "false"', $praktdbmaster);

	if (($countStellen != false) && (mysql_num_rows($countStellen) > 0)) {
		$countStellen = mysql_fetch_array($countStellen);	
		$countStellen = $countStellen["counter"];
		
		$hasto_firmenlevel = 0;
		// Wenn mehr als oder 20 Stellen angelegt sind, wird Premium benötigt
		if($countStellen >= 20) {
			$hasto_firmenlevel = 3;				
		// Wenn mehr als oder 3 Stellen angelegt sind, wird Komfort benötigt
		} elseif($countStellen >= 3) {
			$hasto_firmenlevel = 2;		
		// Wenn mehr als oder 1 Stelle angelegt sind, wird Basis benötigt		
		} elseif($countStellen >= 1) {
			$hasto_firmenlevel = 0;
		// Es wurde noch keine
		} 	

		// Wenn mein Firmenlevel nicht ausreicht um diese Stellen online zu schalten, dann -> Buchung
		if($firmenlevel < $hasto_firmenlevel) {
			echo "packet_upgrade";
			exit();		
		}
	}
} 

switch($_POST["newStatus"]) {
	case 'true':
		$newStatus = "true";
		break;
	case 'false':
		$newStatus = "false";
		break;
	default:
		break;
}

if(empty($newStatus)) exit();

if($objStelle->checkAccess()) {
	// Setzt Inactive = false und speichert automatisch (true)
	$objStelle->setInactive($newStatus, true);
}
	
#$updatestring = mysql_db_query($database_db, 'UPDATE stellen SET inactive = "'.$newStatus.'" WHERE id = '.$stellenid.' AND firmenid = '.$firmenid,$praktdbmaster);
echo "is_".$newStatus;

$dateiname = '/home/praktika/cache/stellen/stelle_'.$stellenid.'.html';
@unlink($dateiname);

?>