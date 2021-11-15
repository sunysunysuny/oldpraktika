<?
require("../../etc/gb_config.inc.php");

$sql = '	UPDATE
				praktberichte
			SET
				rating = ((rating*votes + '.(int)$_POST['bewertung'].') / (votes + 1)),
				votes = votes + 1
			WHERE
				id = \''.intval($_POST['bID']).'\'';

				$hDB->query($sql, $praktdbmaster);
