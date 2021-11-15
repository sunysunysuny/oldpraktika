<?
require("/home/praktika/etc/gb_config.inc.php");

switch($_POST["status"]) {
	case 0:
		$row = "gelesen";
		$date = "gelesen_date";
		break;
	case 1:
		$row = "bearbeitung";		
		$date = "antwort_date";	
		break;
	case 2:
		$row = "beantwortet";		
		$date = "antwort_date";	
		break;
	case 3:
		$row = "rueckantwort";	
		$date = "antwort_date";	
		break;
	case 4:
		$row = "zusage";
		$date = "end_date";		
		break;
	case 5:
		$row = "absage";		
		$date = "end_date";				
		break;
}


$sql = "UPDATE bewerbung SET ".$row." = 'true', ".$date." = NOW() WHERE id = ".$_POST["bID"]." AND firmenid = ".$_SESSION["s_firmenid"];

mysql_query($sql, $praktdbmaster);

echo "bewstatus_".$_POST["status"]."_".$_POST["bID"];