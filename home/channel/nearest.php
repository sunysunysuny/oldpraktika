<?php
ob_start();
require_once("/home/praktika/etc/gb_config.inc.php");
/*
 * Gibt als JSON - codiert die Stellen in n�chster Umgebung des Aufrufenden zur�ck
 */

if(empty($_POST["lng"]) || empty($_POST["lat"])) {
    $posTMP = Praktika_Geolocate::locateIp($_SERVER["REMOTE_ADDR"]); #$_SERVER["REMOTE_ADDR"]);
    $pos = array("lat" => (string)$posTMP["latitude"], "lng" => (string)$posTMP["longitude"]);
} else {
    $pos = $_POST;
}

$pi = 3.1415;
$rad_l = $pos["lng"] / 180 * $pi;
$rad_b = $pos["lat"] / 180 * $pi;

$rad_l_cos = cos($rad_l);
$rad_l_sin = sin($rad_l);
$rad_b_cos = cos($rad_b);

$sql = "SELECT grossraum, id 
			FROM prakt2.grossraum
			LEFT JOIN prakt2_temp.grossraum_temp ON(grossraum_temp.grossraumID = grossraum.id)
            WHERE
                6368 * SQRT(2*(1-cos(RADIANS(lat)) *
                    $rad_b_cos * (sin(RADIANS(lng)) *
                    $rad_l_sin + cos(RADIANS(lng)) *
                    $rad_l_cos) - sin(RADIANS(lat)) * sin(".$rad_b."))) < 100 AND anzahl_stellen > 1
            ORDER BY
                SQRT(2*(1-cos(RADIANS(lat)) *
                    $rad_b_cos * (sin(RADIANS(lng)) *
                    $rad_l_sin + cos(RADIANS(lng)) *
                    $rad_l_cos) - sin(RADIANS(lat)) * sin(".$rad_b."))) LIMIT 14";
$data = $hDB->query($sql, $praktdbslave);

if(mysql_num_rows($data) == 0) exit();

while($row = mysql_fetch_assoc($data)) {
	if($row["id"] == 5517) {
		$row["id"] = 2;
		$row["grossraum"] = "Berlin";
	}

	$objChannel = new Praktika_Channel();
    $objChannel->setCond("grossraum", $row["id"]);
    $stellen = $objChannel->getStellen(5, true);

    if(count($stellen) > 1) break;
}

for($a = 0;$a < count($stellen);$a++) {
    $stellen[$a]["url"] = Praktika_Stelle::getURL($stellen[$a]);
	$stellen[$a]["taetigkeit"] = utf8_encode($stellen[$a]["taetigkeit"]);
	$stellen[$a]["firma"] = Praktika_String::cutWord($stellen[$a]["firma"], 46);
}
$result = array();
$result["data"] = $stellen;
$result["grossraum"] = $row["grossraum"];

echo json_encode($result);
?>
