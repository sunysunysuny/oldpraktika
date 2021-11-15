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
		video_id = '.$video_id.'
		AND course = \'Bewerbungstraining Englisch\'';

$result = mysql_query($sql, $praktdbslave);

if (mysql_num_rows($result) == 1) {
	$row = mysql_fetch_row($result);
?>
		<h3><?php echo Praktika_String::getUtf8String($row[6]); ?></h3>
		<table>
			<colgroup>
				<col width="30%" />
				<col width="70%" />
			</colgroup>
			<thead>
				<tr>
					<th>Lektion <?php echo $row[4]; ?>:</th>
					<th><?php echo Praktika_String::getUtf8String($row[2]); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="2"><?php echo Praktika_String::getUtf8String($row[3]); ?></td>
				</tr>
				<tr>
					<td><strong>L&auml;nge:</strong></td>
					<td><?php echo $row[5]; ?></td>
				</tr>
			</tbody>
		</table>
<?php
}
?>