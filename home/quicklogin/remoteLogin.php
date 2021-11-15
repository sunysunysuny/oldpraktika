<?
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH']  != 'XMLHttpRequest') {
	header("Location:/login/");
}
ob_start();
require("/home/praktika/etc/gb_config.inc.php");

$fehler = false;


$loginidemail = $_POST["username"];
$passwort = $_POST["passwort"];

$result = Praktika_User::login($loginidemail, $passwort);
setcookie("c_lid", $loginidemail, time() + 31449600, "/");

if($result == false) {
    echo "false#0";
} else {
    echo "true#0";
}
// } else {
    // Praktika_User::loginById($result["id"], $result["mode"]);
// }
exit();
if ( $logincheck = sessionnutzerlogin( $database_db, $loginidemail, $passwort ) == "true" ) {
	setcookie("showContact", 1, (time() + 1296000), "/", ".praktika.de");
	
	require("/home/praktika/etc/login_bewerber.inc.php");
	
	
	if(!empty($_POST["getcontact"])) {
		$sql = "SELECT firmenkontakt FROM stellen WHERE id = '".(int)$_POST["getcontact"]."'";
		$data = mysql_fetch_assoc(mysql_db_query($database_db, $sql));
		echo html_entity_decode(nl2br($data["firmenkontakt"]));
	}
	
} else {
	echo "false#0";
}
