<?php

require("/home/praktika/etc/config.inc.php");

if (!isset($_SESSION['s_loggedin']) || $_SESSION['s_loggedin'] == false) {
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
			kommentar = \''.mysql_real_escape_string($_POST['kommentar']).'\',
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

$result = mysql_query($sql, $praktdbslave);

pageheader(array('page_title' => mysql_result($result, 0, 'headline')));

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
?>

<script type="text/javascript" src="/scripts/jquery/jquery-1.4.2.js"></script>
<script type="text/javascript" src="/scripts/flowplayer/flowplayer-3.2.2.min.js"></script>

<!--<script type="text/javascript" src="/scripts/swfobject/swfobject.js"></script>
<script type="text/javascript">
	var flashvars = {};
	flashvars.file = '/videos/bewerbungstraining/0' + <?php echo $video_id; ?> + '.flv';
	flashvars.image = '/styles/images/bewerbungstraining/0' + <?php echo $video_id; ?> + '.jpg';
	flashvars.backcolor = '0x000000';
	flashvars.frontcolor = '0xffffff';
	flashvars.lightcolor = '0xffffff';
	var params = {};
	params.bgcolor = '#000000';
	var attributes = {};
	attributes.align = 'middle';
	swfobject.embedSWF('/scripts/swfobject/player/flvplayer.swf', 'video', '660', '360', '9.0.0', 'expressinstall.swf', flashvars, params, attributes);
</script>-->
<script type="text/javascript">
	function setButton1() {
		document.getElementById('kommentare').style.display = 'block';
		document.getElementById('experten_fragen').style.display = 'none'; 
		document.getElementById('mein_cv').style.display = 'none';
		document.getElementById('button1').className = 'switchButton active';
		document.getElementById('button2').className = 'switchButton';
		document.getElementById('button3').className = 'switchButton';
	}

	function setButton3() {
		document.getElementById('kommentare').style.display = 'none';
		document.getElementById('experten_fragen').style.display = 'none';
		document.getElementById('mein_cv').style.display = 'block';
		document.getElementById('button1').className = 'switchButton';
		document.getElementById('button2').className = 'switchButton';
		document.getElementById('button3').className = 'switchButton active';
	}

	function setButton2() {
		document.getElementById('kommentare').style.display = 'none';
		document.getElementById('experten_fragen').style.display = 'block'; 
		document.getElementById('mein_cv').style.display = 'none';
		document.getElementById('button1').className = 'switchButton';
		document.getElementById('button2').className = 'switchButton active';
		document.getElementById('button3').className = 'switchButton';
	}
</script>

<div id="bewerbungsvideo">
	<a href="/videos/bewerbungstraining/0<?php echo $video_id; ?>.flv" id="player"><img src="/styles/images/bewerbungstraining/0<?php echo $video_id; ?>.jpg" alt="praktika.de Bewerbungstraining Englisch &quot;<?php echo mysql_result($result, 0, 'headline'); ?>&quot;" /></a>
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
						mute: 'Ton aus',	
						unmute: 'Ton an',	
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
					// load data from server. supply a few parameters.
					$('#video_info').load('/bewerbungstraining/get-data/' + (clip.index + 1) + '/');
					$('#playlist').load('/bewerbungstraining/get-playlist/' + (clip.index + 1) + '/');
					$('#kommentare').load('/bewerbungstraining/get-comments/' + (clip.index + 1) + '/');
					$('#content h1').load('/bewerbungstraining/get-headline/' + (clip.index + 1) + '/');

					/*
						you can do different things for different clips. as an example
						we change the wrapper's background color
					*/
					if (clip.index == 1) {
						$("#video_info").css({backgroundColor: '#347'});
					}
				},
				
				onFinish: function(clip) {		// set an event handler in the configuration
					load('/bewerbungstraining/save-data/' + (clip.index + 1) + '/');
				}				
			},
			
			// playlist of four clips
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

		// get all links that are inside div#clips
		var links = document.getElementById('playlist').getElementsByTagName('a');

		// loop those links and alter their click behaviour
		for (var i = 0; i < links.length; i++) {
			links[i].onclick = function() {
				
				// play the clip specified in href- attribute with Flowplayer
				$f().play(this.getAttribute('href', 2));
				
				// by returning false normal link behaviour is skipped
				return false;
			}
		}
	</script>
<!--	<div id="video"><a href="https://www.adobe.com/go/getflashplayer"><img src="https://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a></div>-->
</div>

<h2>Downloads zu diesem Training</h2>

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
	<div id="button1" class="switchButton active" onclick="setButton1();">Kommentare</div>
	<div id="button2" class="switchButton" onclick="setButton2();">Experten fragen</div>
	<div id="button3" class="switchButton" onclick="setButton3();">Mein CV</div>
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
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>#kommentare" method="post">
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
		
		echo '		<hr />'."\n";
		echo '		<p class="kommentar">'."\n";
		
		if (mysql_num_rows($result_image) > 0) {
			echo '			<img src="/community/passbild.php?id='.mysql_result($result_image, 0, 'id').'" width="50" alt="Foto von '.$row['first_name'].' '.$row['last_name'].'" />'."\n";
		} else {
			echo '			<img src="/styles/images/bewerbungstraining/no_image.png" width="50" height="50" alt="Foto von '.$row['first_name'].' '.$row['last_name'].'" />'."\n";
		}
		
		echo '				<span class="kommentar"><a href="">'.$row['first_name'].' '.$row['last_name'].':</a> '.stripslashes($row['kommentar']).'</span>'."\n";
		echo '		</p>'."\n";
		echo '		<p class="datum">'.date('d.m.Y, H:i', $row['datum']).'</p>'."\n";
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
</div>

<div id="experten_fragen" class="display_not">
	<p>Der Chat ist derzeit nicht aktiv.</p>
</div>

<div id="mein_cv" class="display_not">
	<ol>
		<li><a href="/lebenslauf/ausbildung/schule">Schooling (education)</a></li>
		<li><a href="/lebenslauf/ausbildung/beruf">Schooling (personal career)</a></li>
		<li><a href="/lebenslauf/ausbildung/studium">Studies</a></li>
		<li><a href="/lebenslauf/praktika">Internships and Part Time Jobs</a></li>
		<li><a href="/lebenslauf/berufserfahrung">Professional Experience</a></li>
		<li><a href="/lebenslauf/sprachen">Language Skills &amp; Further Skills</a></li>
		<li><a href="/lebenslauf/hobbies">Hobbies / Interests</a></li>
		<li><a href="/lebenslauf/referenzen">References</a></li>
		<li><a href="/lebenslauf/video">Video Resume</a></li>
		<li><a href="/lebenslauf/dateien">Attachments</a></li>
	</ol>
	<ol>
		<li><a href="/lebenslauf/leistung">Experience Profile</a></li>
		<li><a href="/lebenslauf/zukunft">Your Future Plan</a></li>
	</ol>
</div>

<?php 
bodyoff();
?>