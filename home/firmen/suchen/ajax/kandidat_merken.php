<?php
require("/home/praktika/etc/gb_config.inc.php");


$nutzerid = intval($_REQUEST['nutzerid']);

if (isset($nutzerid) && !empty($nutzerid)) {
	mysql_db_query($database_db,'INSERT INTO firmenbookmark SET firmenid = '.$_SESSION['s_firmenid'].', nutzerid = '.$nutzerid.', art = 3, mzbezeichnung = \'Kandidat '.date('d.m.y').'\', datum_eintrag = NOW()',$praktdbmaster);
}

echo 'merken###'.$nutzerid;
?>