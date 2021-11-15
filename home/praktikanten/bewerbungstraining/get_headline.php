<?php
require("/home/praktika/etc/config.inc.php");

if (isset($_REQUEST['video_id']) && !empty($_REQUEST['video_id'])) {
	$video_id = intval($_REQUEST['video_id']);
} else {
	$video_id = 1;
}

$sql = '
	SELECT
		headline
	FROM
		videos
	WHERE
		video_id = '.$video_id.'
		AND course = \'Bewerbungstraining Englisch\'';

$result = mysql_query($sql, $praktdbslave);

if (mysql_num_rows($result) == 1) {
	echo mysql_result($result, 0, 'headline');
}
?>