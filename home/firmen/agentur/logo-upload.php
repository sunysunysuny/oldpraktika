<?php
/**
 * Created by JetBrains PhpStorm.
 * User: fackermann
 * Date: 30.12.11
 * Time: 11:50
 * To change this template use File | Settings | File Templates.
 */

require('/home/praktika/etc/config.inc.php');

if (isset($_FILES)) {
	$agentur = new Praktika_Firmen_Agentur($_SESSION['s_firmenid']);

	$logoUpload = $agentur->uploadLogo($_POST['id'], $_FILES['logo-' . $_POST['id']]);

	if ($logoUpload === true) {
		echo $agentur->getLogo($_POST['id'], "header");
	} else {
		echo 'false/' . $_POST['id'];
	}
} else {
	echo 'false/' . $_POST['id'];
}