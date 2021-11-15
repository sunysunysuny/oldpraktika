<?
$checkings = 0;

// Prüfen alles Bereich nach neuen Bewerbungen
$checkbewerbungens1 = mysql_db_query($database_db,'SELECT bewerbung.id FROM bewerbung,nutzer WHERE bewerbung.firmenid='.intval($_SESSION['s_firmenid']).' AND bewerbung.spalte=1 AND bewerbung.preview=\'false\' AND bewerbung.inactive=\'false\' AND bewerbung.bewerber_lesebestaetigt=\'false\' AND nutzer.inactive=\'false\' AND nutzer.id=bewerbung.nutzerid ORDER BY bewerbung.modify DESC',$praktdbmaster);
$checkbewerbungens2 = mysql_db_query($database_db,'SELECT bewerbung.id FROM bewerbung,nutzer WHERE bewerbung.firmenid='.intval($_SESSION['s_firmenid']).' AND bewerbung.spalte=2 AND bewerbung.preview=\'false\' AND bewerbung.inactive=\'false\' AND bewerbung.bewerber_lesebestaetigt=\'false\' AND nutzer.inactive=\'false\' AND nutzer.id=bewerbung.nutzerid ORDER BY bewerbung.modify DESC',$praktdbmaster);
$checkbewerbungens3 = mysql_db_query($database_db,'SELECT bewerbung.id FROM bewerbung,nutzer WHERE bewerbung.firmenid='.intval($_SESSION['s_firmenid']).' AND bewerbung.spalte=3 AND bewerbung.preview=\'false\' AND bewerbung.inactive=\'false\' AND bewerbung.bewerber_lesebestaetigt=\'false\' AND nutzer.inactive=\'false\' AND nutzer.id=bewerbung.nutzerid ORDER BY bewerbung.modify DESC',$praktdbmaster);
$checkbewerbungens4 = mysql_db_query($database_db,'SELECT bewerbung.id FROM bewerbung,nutzer WHERE bewerbung.firmenid='.intval($_SESSION['s_firmenid']).' AND bewerbung.spalte=4 AND bewerbung.preview=\'false\' AND bewerbung.inactive=\'false\' AND bewerbung.bewerber_lesebestaetigt=\'false\' AND nutzer.inactive=\'false\' AND nutzer.id=bewerbung.nutzerid ORDER BY bewerbung.modify DESC',$praktdbmaster);
$checkbewerbungens5 = mysql_db_query($database_db,'SELECT bewerbung.id FROM bewerbung,nutzer WHERE bewerbung.firmenid='.intval($_SESSION['s_firmenid']).' AND bewerbung.spalte=5 AND bewerbung.preview=\'false\' AND bewerbung.inactive=\'false\' AND bewerbung.bewerber_lesebestaetigt=\'false\' AND nutzer.inactive=\'false\' AND nutzer.id=bewerbung.nutzerid ORDER BY bewerbung.modify DESC',$praktdbmaster);

$checkkontakte1 = mysql_db_query($database_db,'SELECT firmenkontakte.id FROM firmenkontakte WHERE firmenid='.intval($_SESSION['s_firmenid']).' AND spalte=1 AND firmenkontakte.gelesen=\'false\' AND inactive=\'false\' ORDER BY id DESC',$praktdbmaster);
$checkkontakte2 = mysql_db_query($database_db,'SELECT firmenkontakte.id FROM firmenkontakte WHERE firmenid='.intval($_SESSION['s_firmenid']).' AND spalte=2 AND firmenkontakte.gelesen=\'false\' AND inactive=\'false\' ORDER BY id DESC',$praktdbmaster);
$checkkontakte3 = mysql_db_query($database_db,'SELECT firmenkontakte.id FROM firmenkontakte WHERE firmenid='.intval($_SESSION['s_firmenid']).' AND spalte=3 AND firmenkontakte.gelesen=\'false\' AND inactive=\'false\' ORDER BY id DESC',$praktdbmaster);
$checkkontakte4 = mysql_db_query($database_db,'SELECT firmenkontakte.id FROM firmenkontakte WHERE firmenid='.intval($_SESSION['s_firmenid']).' AND spalte=4 AND firmenkontakte.gelesen=\'false\' AND inactive=\'false\' ORDER BY id DESC',$praktdbmaster);
$checkkontakte5 = mysql_db_query($database_db,'SELECT firmenkontakte.id FROM firmenkontakte WHERE firmenid='.intval($_SESSION['s_firmenid']).' AND spalte=5 AND firmenkontakte.gelesen=\'false\' AND inactive=\'false\' ORDER BY id DESC',$praktdbmaster);

$checkings = mysql_num_rows($checkbewerbungens1) + mysql_num_rows($checkbewerbungens2) + mysql_num_rows($checkbewerbungens3) + mysql_num_rows($checkbewerbungens4) + mysql_num_rows($checkbewerbungens5) + mysql_num_rows($checkkontakte1) + mysql_num_rows($checkkontakte2) + mysql_num_rows($checkkontakte3) + mysql_num_rows($checkkontakte4) + mysql_num_rows($checkkontakte5);

if ($checkings > 0) {
	echo '<table>'."\n";
	echo '	<tr>'."\n";
	echo '		<td colspan="4" valign="top"><h3>Neu im Posteingang:</h3></td>'."\n";
	echo '	</tr>'."\n";
	
	if (mysql_num_rows($checkbewerbungens1) || mysql_num_rows($checkkontakte1) > 0) {
		echo '	<tr>'."\n";
		echo '		<td width="20%">'.$language['strPraktikum'].':</td>'."\n";
		echo '		<td width="27%"><a href="index.phtml?spalte=1&bereich=1#elmentenliste">Bewerbungen ('.mysql_num_rows($checkbewerbungens1).')</a></td>'."\n";
		echo '		<td>/</td>'."\n";
		echo '		<td align="left"><a href="index.phtml?spalte=1&bereich=2#elmentenliste">Kurzmitteilungen ('.mysql_num_rows($checkkontakte1).')</a></td>'."\n";
		echo '	</tr>'."\n";
	}
	if (mysql_num_rows($checkbewerbungens2) || mysql_num_rows($checkkontakte2) > 0) {
		echo '	<tr>'."\n";
		echo '		<td width="20%">'.$language['strDiplom'].':</td>'."\n";
		echo '		<td width="27%"><a href="index.phtml?spalte=2&bereich=1#elmentenliste">Bewerbungen ('.mysql_num_rows($checkbewerbungens2).')</a></td>'."\n";
		echo '		<td>/</td>'."\n";
		echo '		<td><a href="index.phtml?spalte=2&bereich=2#elmentenliste">Kurzmitteilungen ('.mysql_num_rows($checkkontakte2).')</a></td>'."\n";
		echo '	</tr>'."\n";
	}
	if (mysql_num_rows($checkbewerbungens3) || mysql_num_rows($checkkontakte3) > 0) {
		echo '	<tr>'."\n";
		echo '		<td width="20%">'.$language['strNebenjob'].':</td>'."\n";
		echo '		<td width="27%"><a href="index.phtml?spalte=3&bereich=1#elmentenliste">Bewerbungen ('.mysql_num_rows($checkbewerbungens3).')</a></td>'."\n";
		echo '		<td>/</td>'."\n";
		echo '		<td><a href="index.phtml?spalte=3&bereich=2#elmentenliste">Kurzmitteilungen ('.mysql_num_rows($checkkontakte3).')</a></td>'."\n";
		echo '	</tr>'."\n";
	}
	if (mysql_num_rows($checkbewerbungens4) || mysql_num_rows($checkkontakte4) > 0) {
		echo '	<tr>'."\n";
		echo '		<td width="20%">'.$language['strAusbildung'].':</td>'."\n";
		echo '		<td width="27%"><a href="index.phtml?spalte=4&bereich=1#elmentenliste">Bewerbungen ('.mysql_num_rows($checkbewerbungens4).')</a></td>'."\n";
		echo '		<td>/</td>'."\n";
		echo '		<td><a href="index.phtml?spalte=4&bereich=2#elmentenliste">Kurzmitteilungen ('.mysql_num_rows($checkkontakte4).')</a></td>'."\n";
		echo '	</tr>'."\n";
	}
	if (mysql_num_rows($checkbewerbungens5) || mysql_num_rows($checkkontakte5) > 0) {
		echo '	<tr>'."\n";
		echo '		<td width="20%">'.$language['strAusbildung'].':</td>'."\n";
		echo '		<td width="27%"><a href="index.phtml?spalte=5&bereich=1#elmentenliste">Bewerbungen ('.mysql_num_rows($checkbewerbungens5).')</a></td>'."\n";
		echo '		<td>/</td>'."\n";
		echo '		<td><a href="index.phtml?spalte=5&bereich=2#elmentenliste">Kurzmitteilungen ('.mysql_num_rows($checkkontakte5).')</a></td>'."\n";
		echo '	</tr>'."\n";
	}
	
	echo '</table>'."\n";
}


?>