<?php
require("/home/praktika/etc/config.inc.php");

if (isset($_REQUEST['video_id']) && !empty($_REQUEST['video_id'])) {
	$video_id = intval($_REQUEST['video_id']);
} else {
	$video_id = 1;
}

$sql = '
	SELECT
		*
	FROM
		videos
	WHERE
		course = \'Bewerbungstraining Englisch\'';

$result = mysql_query($sql, $praktdbslave);

if (mysql_num_rows($result) > 1) {
	while ($row = mysql_fetch_assoc($result)) {
		$sql_watched = '
			SELECT
				*
			FROM
				videos_watched
			WHERE
				user_id = '.$_SESSION['s_loginid'].'
				AND video = '.$row['video_id'];

		$result_watched = mysql_query($sql_watched, $praktdbslave);

		if (intval($row['id']) != $video_id && intval($row['id']) != ($video_id + 1)) {
			$playlist .= '		<p><a href="/bewerbungstraining/'.$row['video_id'].'/1/" title="'.$row['headline'].' ansehen"><img src="/styles/images/bewerbungstraining/0'.$row['video_id'].'_small.jpg" alt="Vorschaubild zu '.$row['headline'].'" /></a><strong>Lektion '.$row['lesson'].'</strong><br /><a href="/bewerbungstraining/'.$row['video_id'].'/1/" title="'.$row['headline'].' ansehen">'.$row['headline'].'</a><br /><span>L&auml;nge: '.$row['duration'].'</span>'.(mysql_num_rows($result_watched) > 0 ? '<br /><span class="watched">bereits angesehen</span>' : '').'</p>';
		}
	}
	
	echo '		<h3>weitere Lektionen</h3>';
	echo $playlist;
}
?>