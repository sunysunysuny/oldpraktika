<?php
header("Content-Type: text/xml");

        $replace = array (
          "ä" => "&auml;",
          "ö" => "&ouml;",
          "ü" => "&uuml;",
          "Ä" => "&Auml;",
          "Ö" => "&Ouml;",
          "Ü" => "&Uuml;",
          "ß" => "&szlig;");
        
$string = isset($_GET['q']) ? $_GET['q'] : '';
$type = $_GET["t"];
require("/home/praktika/etc/gb_config.inc.php");

srand ((double)microtime()*1000000);
$num = rand(0,(count($dbarray)-1));

$sql = sprintf("SELECT
					*
				FROM
					".$database_temp.".tmp_stellensuche_keywords
				WHERE
					keyword RLIKE '^%s\w*'
				LIMIT
					7",
				$string);
$result = $hDB->query($sql, $praktdbslave);

$responseArray = array();
$responseArray[0] = $_GET["q"];

while ($row = mysql_fetch_assoc($result)) {
    $responseArray[1][] = utf8_encode($row["keyword"]);
    $responseArray[2][] = $row["anzahl"]." Treffer";




    #$responseArray[3][] = "https://www.praktika.de/suche/".strtr($row["keyword"],$replace);
#    $results .= '  <rs id="'.$row['id'].'" info=" ('.$row['anzahl'].' Treffer)">'.$row['keyword'].'</rs>'."\n";
}

if($type == "json") {
    echo json_encode($responseArray);
}
?>