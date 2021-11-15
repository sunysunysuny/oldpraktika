<?php
/**
 * Created by JetBrains PhpStorm.
 * User: fackermann
 * Date: 29.12.11
 * Time: 10:51
 * To change this template use File | Settings | File Templates.
 */

require("/home/praktika/etc/config.inc.php");

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
	if (isset($_POST['name']) && strlen($_POST['name']) > 3) {
		$agentur = new Praktika_Firmen_Agentur($_SESSION['s_firmenid']);

		$insertUnternehmen = $agentur->createUnternehmen(mysql_real_escape_string($_POST['name']));

		echo $insertUnternehmen;
	} else {
		echo 'false';
	}
} else {
	echo 'false';
}
