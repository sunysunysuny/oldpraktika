<?php
header("Content-Type: text/xml");

        $replace = array (
          "�" => "&auml;",
          "�" => "&ouml;",
          "�" => "&uuml;",
          "�" => "&Auml;",
          "�" => "&Ouml;",
          "�" => "&Uuml;",
          "�" => "&szlig;");

$string = isset($_GET['q']) ? $_GET['q'] : '';
$type = $_GET["t"];
require("/home/praktika/etc/gb_config.inc.php");

srand ((double)microtime()*1000000);
$num = rand(0,(count($dbarray)-1));

$sql = sprintf("SELECT
					firmen.firma, firmen_prop_assign.firmenlevel
				FROM
					".$database_db.".firmen
                                LEFT JOIN
                                        ".$database_db.".firmen_prop_assign
                                              ON(firmen_prop_assign.firmenid = firmen.id)
				WHERE
					firma RLIKE '^%s\w*'
                                ORDER BY
                                        firmenlevel DESC, firma
				LIMIT
					7",
				$string);
$result = $hDB->query($sql, $praktdbslave);

$responseArray = array();
$responseArray[0] = $_GET["q"];

while ($row = mysql_fetch_assoc($result)) {
    $responseArray[1][] = $row["firma"];
    switch($row["firmenlevel"]) {
        case "0":
            $lizenz = "Free";
            break;
        case "1":
            $lizenz = "Basis";
            break;
        case "2":
            $lizenz = "Konfort";
            break;
        case "3":
            $lizenz = "Premium";
            break;

    }
    $responseArray[2][] = $lizenz;

    #$responseArray[3][] = "https://www.praktika.de/suche/".strtr($row["keyword"],$replace);
#    $results .= '  <rs id="'.$row['id'].'" info=" ('.$row['anzahl'].' Treffer)">'.$row['keyword'].'</rs>'."\n";
}

if($type == "json") {
    echo json_encode($responseArray);
}
?>