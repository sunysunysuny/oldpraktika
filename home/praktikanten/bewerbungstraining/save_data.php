<?php
require("/home/praktika/etc/config.inc.php");

if (isset($_REQUEST['video_id']) && !empty($_REQUEST['video_id'])) {
	$video_id = intval($_REQUEST['video_id']);
} else {
	$video_id = 1;
}

$sql = '
	INSERT INTO
		videos_watched
	SET
		user_id = '.$_SESSION['s_loginid'].',
		video = '.$video_id;

mysql_query($sql, $praktdbmaster);
?>