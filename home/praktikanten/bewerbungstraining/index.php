<?php

require("/home/praktika/etc/config.inc.php");

if (!isset($_SESSION['s_loggedin']) || $_SESSION['s_loggedin'] == false) {
	$_SESSION['s_samesite'] = true;
	$_SESSION['s_samesite_url'] = $_SERVER["REDIRECT_URL"];
	
	header('Location: http://'.$_SERVER['HTTP_HOST'].'/login');
	exit();
}

if (isset($_POST['kommentar_senden'])) {
	$sql = '
		INSERT INTO
			video_kommentare
		SET
			nutzer_id = '.$_SESSION['s_loginid'].',
			first_name = \''.$_SESSION['s_vname'].'\',
			last_name = \''.$_SESSION['s_name'].'\',
			kommentar = \''.mysql_real_escape_string(strip_tags($_POST['kommentar'])).'\',
			video = '.intval($_POST['video_id']).',
			bereich = \'bewerbungstraining\',
			datum_eintrag = NOW()
	';

	$query = mysql_query($sql, $praktdbmaster);
	
	header('Location: /bewerbungstraining/'.intval($_POST['video_id']).'/1/#kommentare');
}

$_SESSION['sidebar'] = 'bewerbungstraining';
$_SESSION['restricted'] = RESTRICTED_CANDIDATES;
$_SESSION['current_user_group'] = USER_GROUP_CANDIDATES;
$_SESSION['current_page'] = PAGE_MY_PRAKTIKA;

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

$result_head = mysql_query($sql, $praktdbslave);

pageheader(array('page_title' => mysql_result($result_head, 0, 'headline'), 'jqueryui' => true));

Praktika_Static::__('bewerbungstraining');

if (isset($_REQUEST['page']) && intval($_REQUEST['page']) > 1) {
	$page = intval($_REQUEST['page']);
} else {
	$page = 1;
}

if (isset($query) && $query == false) {
	echo '<p class="error">Es ist ein Fehler beim Senden des Kommentars aufgetreten.</p>';
} elseif (isset($query) && $query != false) {
	echo '<p class="success">Ihr Kommentar wurde erfolgreich hinzugef&uuml;gt.</p>';
}

$sql = '
	SELECT
		COUNT(*) AS anzahl
	FROM
		videos
	WHERE
		course = \'Bewerbungstraining Englisch\'';

$result = mysql_query($sql, $praktdbslave);

$num_videos = mysql_result($result, 0, 'anzahl');
?>

<script type="text/javascript" src="/scripts/flowplayer/flowplayer-3.2.2.min.js"></script>

<script type="text/javascript">
	$(document).ready(function() {
		$("#umschalterdiv").tabs();
	});
</script>

<div id="bewerbungsvideo">
	<a href="/videos/bewerbungstraining/0<?php echo $video_id; ?>.flv" id="player" onclick="$f('player').play(<?php echo ($video_id - 1); ?>);"><img src="/styles/images/bewerbungstraining/0<?php echo $video_id; ?>.jpg" alt="praktika.de Bewerbungstraining Englisch &quot;<?php echo mysql_result($result_head, 0, 'headline'); ?>&quot;" /></a>
	<script type="text/javascript">	
		$f('player', '/scripts/flowplayer/flowplayer-3.2.2.swf', {
			screen:	{
				bottom: 0	// make the video take all the height
			},

			// change the default controlbar to modern
			plugins: {
				controls: {
					url: 'flowplayer.controls-3.2.1.swf',
					playlist: true,

					tooltips: {
						buttons: true,
						fullscreen: 'Vollbildmodus starten',
						fullscreenexit: 'Vollbildmodus verlassen',
						play: 'Video abspielen',
						next: 'n&auml;chstes Video',
						previous: 'vorheriges Video',
						mute: 'Ton aus',
						unmute: 'Ton an'
					},
					
					buttonColor: 'rgba(255, 255, 255, 1)',
					buttonOverColor: '#f27000',
					backgroundColor: 'rgba(0, 0, 0, 1)',
					backgroundGradient: 'medium',
					sliderColor: '#ffffff',
					sliderBorder: '1.5px solid rgba(255, 255, 255, 0.5)',
					volumeSliderColor: '#ffffff',
					volumeBorder: '1.5px solid rgba(255, 255, 255, 0.5)',

					timeColor: '#ffffff',
					durationColor: 'rgba(242, 112, 0, 1)',

					tooltipColor: 'rgba(255, 255, 255, 1)',
					tooltipTextColor: '#000000'
				},
				
				gatracker: {
					url: 'flowplayer.analytics-3.2.0.swf',
					labels: {
						start: 'Start',
						play: 'Play',
						pause: 'Pause',
						resume: 'Resume',
						seek: 'Seek',
						stop: 'Stop',
						finish: 'Finish',
						mute: false,
						unmute: false,
						fullscreen: 'Vollbildmodus starten',
						fullscreenexit: 'Vollbildmodus verlassen'
					},
					debug: false,
					trackingMode: 'AS3',
					googleId: 'UA-69651-1' // your Google Analytics id here
				}
			},
			
			clip: {
				autoPlay: true,
				autoBuffering: true,
				baseUrl: '/videos/bewerbungstraining/',
				
				onStart: function(clip) {
					//alert(clip.length);
					var start = parseInt(clip.url.length - 5);
					var end =  parseInt(clip.url.length - 4);

					// load data from server. supply a few parameters.
					$('#video_info').load('/bewerbungstraining/get-data/' + clip.url.substring(start, end) + '/');
					$('#next_video').load('/bewerbungstraining/get-next-video/' + clip.url.substring(start, end) + '/');
					$('#playlist').load('/bewerbungstraining/get-playlist/' + clip.url.substring(start, end) + '/');
					$('#kommentare').load('/bewerbungstraining/get-comments/' + clip.url.substring(start, end) + '/');
					$('#content h1').load('/bewerbungstraining/get-headline/' + clip.url.substring(start, end) + '/');
					
					//alert(clip.url.substring(start,end);
					//replacePlaylist(clip.url.substring(start, end));
				},
				
				onFinish: function(clip) {		// set an event handler in the configuration
					$.get('/bewerbungstraining/save-data/' + clip.url.substring(1,2) + '/');
				}				
			},
			
			playlist: [
				{ url: '01.flv', scaling: 'orig' },
				{ url: '02.flv', scaling: 'orig' },
				{ url: '03.flv', scaling: 'orig' },
				{ url: '04.flv', scaling: 'orig' },
				{ url: '05.flv', scaling: 'orig' },
				{ url: '06.flv', scaling: 'orig' },
				{ url: '07.flv', scaling: 'orig' },
				{ url: '08.flv', scaling: 'orig' }
			]
		});
	</script>
</div>

<div id="allianz_ad">
	<!-- praktika_bewerbungsvideos_580x47 -->
	<div id='div-gpt-ad-1352107739825-0' style='width:580px; height:47px;'>
		<script type='text/javascript'>
			googletag.cmd.push(function() { googletag.display('div-gpt-ad-1352107739825-0'); });
		</script>
	</div>
	<a href="https://www.facebook.com/AllianzKarriere" target="_blank"><img src="/styles/images/bewerbungstraining/facebook_allianz.png" alt="Facebook Allianz" /></a>
	<a href="http://twitter.com/#!/allianzkarriere" target="_blank"><img src="/styles/images/bewerbungstraining/twitter_allianz.png" alt="Twitter Allianz" /></a>
</div>

<h2>Seminarunterlagen</h2>

<table id="bewerbungsdokumente">
	<colgroup>
		<col width="50%" />
		<col width="50%" />
	</colgroup>
	<tbody>
		<tr>
			<td><a href="/downloads/bewerbungstraining/finding_a_job_or_internship.pdf" target="_blank" class="pdf" title="Finding a Job / Internship ansehen">Finding a Job / Internship</a></td>
			<td><a href="/downloads/bewerbungstraining/sample_cv.pdf" target="_blank" class="pdf" title="CV-Sample ansehen">CV-Sample</a></td>
		</tr>
		<tr>
			<td><a href="/downloads/bewerbungstraining/writing_your_application.pdf" target="_blank" class="pdf" title="Writing your Application ansehen">Writing your Application</a></td>
			<td><a href="/downloads/bewerbungstraining/personal_and_professional_references_worksheet.pdf" target="_blank" class="pdf" title="References ansehen">References</a></td>
		</tr>
		<tr>
			<td><a href="/downloads/bewerbungstraining/sample_cover_letter_usa.pdf" target="_blank" class="pdf" title="Sample Cover Letter USA ansehen">Sample Cover Letter USA</a></td>
			<td><a href="/downloads/bewerbungstraining/more_type_of_letter.pdf" target="_blank" class="pdf" title="More Type of Letters ansehen">More Type of Letters</a></td>
		</tr>
		<tr>
			<td><a href="/downloads/bewerbungstraining/sample_cover_letter_uk.pdf" target="_blank" class="pdf" title="Sample Cover Letter UK ansehen">Sample Cover Letter UK</a></td>
			<td><a href="/downloads/bewerbungstraining/interviews_part1.pdf" target="_blank" class="pdf" title="Interviews Part 1, face-to-face Interviews ansehen">Interviews Part 1, face-to-face Interviews</a></td>
		</tr>
		<tr>
			<td><a href="/downloads/bewerbungstraining/resume_and_cv.pdf" target="_blank" class="pdf" title="Resume &amp; Curriculum Vitae ansehen">Resume &amp; Curriculum Vitae</a></td>
			<td><a href="/downloads/bewerbungstraining/interviews_part2.pdf" target="_blank" class="pdf" title="Interviews Part 2, Phone Interviews ansehen">Interviews Part 2, Phone Interviews</a></td>
		</tr>
		<tr>
			<td><a href="/downloads/bewerbungstraining/sample_resume.pdf" target="_blank" class="pdf" title="Resume-Sample ansehen">Resume-Sample</a></td>
			<td>&nbsp;</td>
		</tr>
	</tbody>
</table>

<div id="umschalterdiv" class="clearfix">
	<ul>	
		<li><a href="#kommentare">Kommentare</a></li>
		<li><a href="#experten_fragen">Experten fragen</a></li>
		<li><a href="#mein_cv">Mein Lebenslauf</a></li>
	</ul>
</div>

<div id="kommentare">
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
		<form action="<?= $_SERVER['PHP_SELF']; ?>#kommentare" method="post">
			<fieldset>
				<p>
					<textarea id="kommentar" name="kommentar" rows="2" cols="72" class="html_controlled"></textarea>
				</p>
			</fieldset>
			<fieldset class="control_panel">
				<p>
					<input type="hidden" name="video_id" value="<?= $video_id; ?>" />
					<input type="hidden" name="page" value="<?= $page; ?>" />
					<input type="submit" id="kommentar_senden" name="kommentar_senden" value="senden" class="button small" />
				</p>
			</fieldset>
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

		if ($maxPage != 0 && $maxPage != 1) {
			//page navigation links
			echo '		<p id="paging">'.$first.$prev.' - Seite <strong>'.$page.'</strong> von <strong>'.$maxPage.'</strong> - '.$next.$last.'</p>'."\n";
		}
	}
} else {
	echo '<p>Noch kein Kommentar abgegeben.</p>'."\n";
}
/* paging END */
?>
	</div>
</div>

<div id="experten_fragen">
	<p>Diese Funktion ist demn&auml;chst verf&uuml;gbar.</p>
</div>

<div id="mein_cv">
	<p>Bearbeiten Sie Ihren englischen Lebenslauf parallel in einem zweiten Fenster, w&auml;hrend Sie sich die einzelnen Lektionen ansehen.</p>
	<p class="control"><a href="/lebenslauf" target="_blank" class="button">Zum englischen Lebenslauf</a></p>
</div>

<?php 
bodyoff();
?>