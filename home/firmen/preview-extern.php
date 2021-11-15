<?
require_once("/home/praktika/etc/gb_config.inc.php");

$stellenID = intval($_GET["i"]);
global $firmenid, $stellenID;

function getUrlId($url) {
	global  $firmenID, $stellenID;
	if(substr($url[2], 0, 7) == "mailto:") return $url[0];

	if(strlen($url[0]) > 340) return $url[0];

	#var_dump(htmlentities($url[0]));
	$urlId = Praktika_Redirect::getUrlId($url[2], $stelle['firmenid'], $stelle['id']);
	
	$urlPos = strrpos($url[2], '/');

	if ($url != false) {
		$urlSuffix = substr($url[2], $urlPos);
	} else {
		$urlSuffix = '/index.html';
	}
		
	$url[0] = str_replace($url[2], "https://www.praktika.de/redirects/".$urlId.$urlSuffix, $url[0]);
	
	return $url[0];
}
function get_data($url)
{
  $ch = curl_init();
  $timeout = 5;
  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}

$sql = "SELECT * FROM prakt2.stellen WHERE id = ".$stellenID;
$result = $hDB->query($sql, $praktdbslave);

#pageheader();

if(mysql_num_rows($result) == 0) {
	echo "<p class='hint error'>Stelle konnte in unserer Datenbank leider nicht gefunden werden!</p>";
	exit();
}

$stelle = mysql_fetch_assoc($result);

if($stelle["link_anzeige_abfr"] == "false") {
	echo "<p class='hint error'>Ein unvorhergesehener Fehler ist aufgetreten.<br><b>Das Praktika.de Team wurde informiert.</b></p><p><em>Bitte probieren Sie dies in einigen Minuten nochmals.</em></p>";
	mail("swarnat@praktika.de", "Stelle ".$stellenID." nutzt preview-extern nicht korrekt", $_SERVER);
	exit();
}

$page = get_data($stelle["link_anzeige"]);
/*
$filestream = fopen($stelle["link_anzeige"], "r"); 
var_dump($filestream);
exit();
$page = "";
while(!feof($filestream)) { 
    $buffer = fgets($filestream, 4096); 
    $page .= $buffer; 
} 
fclose($filestream); 
*/
if(strpos($page, "<base") === false) {
	if(strpos($page, "<head>") !== false) {
		$page = str_replace("<head>", '<head><base href="'.($stelle["link_anzeige"]).'" target="_blank" />', $page);
	} else {
		$page = str_replace("<body", '<base href="'.($stelle["link_anzeige"]).'" target="_blank" /><body', $page);
	}
}
$applicationUrl = '/redirects/'.Praktika_Redirect::getUrlId($stelle['link_bewerbung'], $stelle['firmenid'], $stelle['id'])."/application";

#$page = str_replace($stelle['link_bewerbung'], $applicationUrl, $page);

$firmenID = $stelle['firmenid'];

$page = preg_replace_callback('!(<a\s*[^>]*)href="([^"]+)"!', "getUrlId", $page);

echo $page;
?>
<style>
body {
	overflow:hidden !Important;
}
</style>
