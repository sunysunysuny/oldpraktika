<?php
require("/home/praktika/etc/config.inc.php");

if (isset($_REQUEST['video_id']) && !empty($_REQUEST['video_id'])) {
	$video_id = intval($_REQUEST['video_id']);
} else {
	$video_id = 1;
}

if (isset($_REQUEST['page']) && intval($_REQUEST['page']) > 1) {
	$page = intval($_REQUEST['page']);
} else {
	$page = 1;
}
?>
	<div id="kommentar_abegeben">
<?php
$sql = '
	SELECT 
		id 
	FROM 
		bewerbungsfoto 
	WHERE 
		nutzerid = '.intval($_SESSION['s_loginid']);

$result = mysql_query($sql, $praktdbslave);

if (mysql_num_rows($result) > 0) {
	echo '			<img src="/community/passbild.php?id='.mysql_result($result, 0, 'id').'" width="50" alt="Foto von '.$_SESSION['s_vname'].' '.$_SESSION['s_name'].'" />'."\n";
} else {
	echo '			<img src="/styles/images/bewerbungstraining/no_image.png" width="50" height="50" alt="Foto von '.$_SESSION['s_vname'].' '.$_SESSION['s_name'].'" />'."\n";
}
?>	
		<form action="/bewerbungstraining/<?php echo $video_id; ?>/1/#kommentare" method="post">
			<textarea id="kommentar" name="kommentar" rows="2" cols="72"></textarea>
			<input type="hidden" name="video_id" value="<?php echo $video_id; ?>" />
			<input type="hidden" name="page" value="<?php echo $page; ?>" />
			<button type="submit" id="kommentar_senden" name="kommentar_senden" value="senden"><span><span><span>senden</span></span></span></button>
		</form>
	</div>
	<div id="kommentar_liste">
<?php
$sql = '
	SELECT
		*,
		UNIX_TIMESTAMP(datum_eintrag) AS datum
	FROM
		video_kommentare
	WHERE
		video = '.$video_id.'
	ORDER BY
		datum_eintrag DESC
	LIMIT
		'.($page == 1 ? '0' : (($page - 1) * 10)).', 10
';

$result = mysql_query($sql, $praktdbslave);

if (mysql_num_rows($result) > 0) {
	while($row = mysql_fetch_assoc($result)) {
		$sql_image = '
			SELECT 
				id 
			FROM 
				bewerbungsfoto 
			WHERE 
				nutzerid = '.intval($row['nutzer_id']);
		
		$result_image = mysql_query($sql_image, $praktdbslave);
		
		echo '		<p class="kommentar">'."\n";
		
		if (mysql_num_rows($result_image) > 0) {
			echo '			<img src="/community/passbild.php?id='.mysql_result($result_image, 0, 'id').'" width="50" alt="Foto von '.$row['first_name'].' '.$row['last_name'].'" />'."\n";
		} else {
			echo '			<img src="/styles/images/bewerbungstraining/no_image.png" width="50" height="50" alt="Foto von '.$row['first_name'].' '.$row['last_name'].'" />'."\n";
		}
		
		echo '				<span class="name_date"><a href="" title="Profil von '.$row['first_name'].' '.$row['last_name'].'"><strong>'.$row['first_name'].' '.substr($row['last_name'], 0, 1).'.</strong></a><br /><span class="date">'.date('d.m.Y, H:i', $row['datum']).'</span></span><span class="kommentar">'.stripslashes($row['kommentar']).'</span><span class="edge"></span>'."\n";
		echo '		</p>'."\n";
	}
}

/* paging BEGIN */
$sql = '
	SELECT
		COUNT(*) AS anzahl
	FROM
		video_kommentare
	WHERE
		video = '.$video_id;

$result = mysql_query($sql, $praktdbslave);

$num_rows = intval(mysql_result($result, 0, 'anzahl'));

if ($num_rows > 0) {

	$maxPage = ceil(mysql_result($result, 0, 'anzahl') / 10);
					
	if ($maxPage != 0) {
		if ($page > 1) {
			$pageTo = $page - 1;
			$prev = '&nbsp;<a href="http://'.$_SERVER['HTTP_HOST'].'/bewerbungstraining/'.$video_id.'/'.$pageTo.'/#kommentar_liste" title="zur&uuml;ck zu Seite '.$pageTo.'">zur&uuml;ck</a>&nbsp;';
							
			$first = '&nbsp;<a href="http://'.$_SERVER['HTTP_HOST'].'/bewerbungstraining/'.$video_id.'/1/#kommentar_liste" title="zur&uuml;ck zur ersten Seite">&laquo;</a>&nbsp;';
		} else {
			$prev = '&nbsp;zur&uuml;ck&nbsp;'; //page one - no previous link
			$first = '&nbsp;&laquo;&nbsp;'; //page one - no first link
		}
						
		if ($page < $maxPage) {
			$pageBack = $page + 1;
			$next = '&nbsp;<a href="http://'.$_SERVER['HTTP_HOST'].'/bewerbungstraining/'.$video_id.'/'.$pageBack.'/#kommentar_liste" title="weiter zu Seite '.$pageBack.'">n&auml;chste</a>&nbsp;';
				
			$last = '&nbsp;<a href="http://'.$_SERVER['HTTP_HOST'].'/bewerbungstraining/'.$video_id.'/'.$maxPage.'/#kommentar_liste" title="weiter zur letzten Seite">&raquo;</a>&nbsp;';
		} else {
			$next = '&nbsp;n&auml;chste&nbsp;'; //last page - no next link
			$last = '&nbsp;&raquo;&nbsp;'; //last page - no last link
		}
						
		//page navigation links
		echo '		<p id="paging">'.$first.$prev.' - Seite <strong>'.$page.'</strong> von <strong>'.$maxPage.'</strong> - '.$next.$last.'</p>'."\n";
	}
} else {
	echo '<p>Noch kein Kommentar abgegeben.</p>'."\n";
}
/* paging END */
?>
	</div>