<?php
//  praktika.de ajax File - KANDIDATENAUSGABE -  Warenkorbfile

header("Content-Type: text/xml");

require("/home/praktika/etc/dbzugang.inc.php");

// Start *************************************

$praktdbmaster = @mysql_pconnect($dbarray[0]["host"],$dbarray[0]["mysqluser"],$dbarray[0]["mysqlpassword"]);

session_name(praktika_id);
session_save_path("/home/praktika/sessions");
session_start();

if (!$s_firmenproparray["kandidatensuche"]["warenkorb"]) $s_firmenproparray["kandidatensuche"]["warenkorb"] = array();

if ($_REQUEST["nutzerid"]) {
	if ($s_firmenproparray["kandidatensuche"]["warenkorb"][$_REQUEST["nutzerid"]]) {
		unset ($s_firmenproparray["kandidatensuche"]["warenkorb"][$_REQUEST["nutzerid"]]);
		$result = "unset";
	} else {
		$s_firmenproparray["kandidatensuche"]["warenkorb"][$_REQUEST["nutzerid"]] = true;
		$result = "set";
	}
}

$profilsuche = implode(",",array_keys($s_firmenproparray["kandidatensuche"]["warenkorb"]));

if ($s_loggedin=="yes") {
	$ks_wk_query="SELECT id FROM kswarenkorb WHERE bearbeiterid = '$s_loginid' AND firmenid = '$s_firmenid' AND gedruckt = 'false' ORDER BY id DESC";
	$ks_wk_results=mysql($database_comm,$ks_wk_query,$praktdbmaster);
	if (mysql_num_rows($ks_wk_results) > 0) {
		$query = "UPDATE kswarenkorb SET standardsuche='$standardsuche' ,statussuche='$statussuche' ,profilsuche='$profilsuche' ,session='".session_id()."' ,modify=NOW() WHERE firmenid = '$s_firmenid' AND gedruckt = 'false' AND id = '".mysql_result($ks_wk_results,0,"id")."'";
		$sqlabfrage = mysql($database_comm,$query,$praktdbmaster);
		$s_firmenproparray["kandidatensuche"]["warenkorb_id"] = mysql_result($ks_wk_results,0,"id");
	} else  {
		$query = "INSERT INTO kswarenkorb (bearbeiterid ,firmenid ,standardsuche ,statussuche ,profilsuche ,session ,modify) VALUES ('$s_loginid','$s_firmenid','$standardsuche','$statussuche','$profilsuche','".session_id()."',NOW())";
		$sqlabfrage = mysql($database_comm,$query,$praktdbmaster);
		$s_firmenproparray["kandidatensuche"]["warenkorb_id"] = mysql_insert_id($praktdbmaster);
	}
} else {
 	$ks_wk_query="SELECT id FROM kswarenkorb WHERE session = '".session_id()."' AND gedruckt = 'false'";
	$ks_wk_results=mysql($database_comm,$ks_wk_query,$praktdbmaster);
	if (($ks_wk_results != false) && (mysql_num_rows($ks_wk_results) > 0)) {
 		$query = "UPDATE kswarenkorb SET standardsuche='$standardsuche' ,statussuche='$statussuche' ,profilsuche='$profilsuche' ,session='".session_id()."' ,modify=NOW() WHERE session = '".session_id()."' AND gedruckt = 'false'";
		$sqlabfrage = mysql($database_comm,$query,$praktdbmaster);
		$s_firmenproparray["kandidatensuche"]["warenkorb_id"] = mysql_result($ks_wk_results,0,"id");
	} else  {
		if ($c_lid) { $bearbeiterabfrage = mysql($database_db,"SELECT id,firmenid FROM bearbeiter WHERE id = $c_lid",$praktdbmaster); $akt_firmenid = mysql_result($bearbeiterabfrage,0,"firmenid"); }
		$query = "INSERT INTO kswarenkorb (bearbeiterid ,firmenid ,standardsuche ,statussuche ,profilsuche ,session ,modify) VALUES ('$c_lid','$akt_firmenid','$standardsuche','$statussuche','$profilsuche','".session_id()."',NOW())";
		$sqlabfrage = mysql($database_comm,$query,$praktdbmaster);
		$s_firmenproparray["kandidatensuche"]["warenkorb_id"] = mysql_insert_id($praktdbmaster);
	}
}


echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
echo "<root>\n";
echo "<htmlinhalte>\n";
echo "<result>".$result."</result>\n";
echo "<wkid>".$s_firmenproparray["kandidatensuche"]["warenkorb_id"]."</wkid>\n";
echo "<wkcount>".count($s_firmenproparray["kandidatensuche"]["warenkorb"])."</wkcount>\n";
echo "</htmlinhalte>\n";
echo "</root>";

?>