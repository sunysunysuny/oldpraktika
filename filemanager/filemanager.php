<?php
ob_start();
require_once("../../etc/gb_config.inc.php");

if($_POST["act"] == "delete" && !empty($_POST["fileid"])) {
	$sql = "SELECT filename FROM prakt2.filemanager WHERE id = ".(int)$_POST["fileid"];
	$result = mysql_fetch_assoc($hDB->query($sql, $praktdbmaster));

	$objFM = new Praktika_Filemanager("comp", $_SESSION["s_firmenid"]);
	$objFM->deleteFile($_POST["fileid"]);

	$sql = "DELETE FROM prakt2.filemanager WHERE id = ".(int)$_POST["fileid"];
	$hDB->query($sql, $praktdbmaster);

	exit();
}

$result = $hDB->query("SELECT *, UNIX_TIMESTAMP(datum_eintrag) AS datum FROM filemanager WHERE cat = 'comp' AND tid = ".(int)$_SESSION["s_firmenid"]." ORDER BY datum_eintrag DESC", $praktdbslave);

pageheader(array('page_title' => '', 'page_hideHeader' => true));
?>

<script src="/scripts/microajax.js" type="text/javascript"></script>
<script type="text/javascript">
	function startCallback() {
		// make something useful before submit (onStart)
		document.getElementById('statusbar').innerHTML = 'Datei wird hochgeladen ... Bitte warten';
		document.getElementById('statusbar').style.display = '';

		document.getElementsByTagName("body")[0].style.cursor = 'wait';
		return true;
	}

	function completeCallback(response) {
		window.location.reload();
		document.getElementsByTagName("body")[0].style.cursor = 'default';
		
		if (response == "") {
			document.getElementById('statusbar').innerHTML = 'Datei wurde erfolgreich hochgeladen';
		}
		
		// make something useful after (onComplete)
		// document.getElementById('nr').innerHTML = parseInt(document.getElementById('nr').innerHTML) + 1;
		// document.getElementById('r').innerHTML = response;
	}
</script>

<script type="text/javascript">
	AIM = {
		frame : function(c) {
			var n = 'f' + Math.floor(Math.random() * 99999);
			var d = document.createElement('DIV');
			d.innerHTML = '<iframe style="display:none" src="about:blank" id="'+n+'" name="'+n+'" onload="AIM.loaded(\''+n+'\')"></iframe>';
			document.body.appendChild(d);

			var i = document.getElementById(n);
			if (c && typeof(c.onComplete) == 'function') {
				i.onComplete = c.onComplete;
			}

			return n;
		},

		form : function(f, name) {
			f.setAttribute('target', name);
		},

		submit : function(f, c) {

			AIM.form(f, AIM.frame(c));
			if (c && typeof(c.onStart) == 'function') {
				return c.onStart();
			} else {
				return true;
			}
		},

		loaded : function(id) {
			var i = document.getElementById(id);
			if (i.contentDocument) {
				var d = i.contentDocument;
			} else if (i.contentWindow) {
				var d = i.contentWindow.document;
			} else {
				var d = window.frames[id].document;
			}
			if (d.location.href == "about:blank") {
				return;
			}

			if (typeof(i.onComplete) == 'function') {
				i.onComplete(d.body.innerHTML);
			}
		}
	}
	
	function deleteFile(fileid) {
		xhr("/filemanager.html","act=delete&fileid=" + fileid, function() {
			document.getElementById('file_' + this.fileid).parentNode.removeChild(document.getElementById('file_' + this.fileid));
		}.bind({fileid:fileid}));
	}
	
	function writeFileName(value) {
		if (value.indexOf("\\") != -1) {
			valuePos = value.lastIndexOf("\\");
			value = value.substr(valuePos + 1);
		}

		document.getElementById('filename').value = value;
	}
</script>

<link href="/styles/v3/module/filemanager.css" rel="stylesheet" media="screen" type="text/css" />

<h1>Neue Datei hochladen</h1>

<div class="filemanager">
	<div class="fileupload">
		<form enctype="multipart/form-data" method="post" action="/filemanager/upload.php" onsubmit="return AIM.submit(this, {'onStart' : startCallback, 'onComplete' : completeCallback})">
			<fieldset>
				<p>
					<label for="filename">Name:</label>
					<input id="filename" class="stdStyle" type="text" name="filename" />
				</p>
				<p>
					<label for="file">Datei (max. 1 MB):</label>
					<input type="file" id="file" name="file" onchange="writeFileName(this.value);" />
				</p>
			</fieldset>
			<fieldset class="control_panel">
				<p>
					<input type="submit" class="button red small" name="send" value="Datei hochladen" />
				</p>
			</fieldset>
		</form>
    </div>

	<hr />
	
<?php
if (mysql_num_rows($result) == 0) {
	echo '<p class="hint info">Es wurden noch keine Dateien hochgeladen</p>'."\n";
} else {
?>
	<table class="filelist">
		<colgroup>
			<col width="250" />
			<col width="150" />
			<col width="150" />
		</colgroup>
<?php
	while ($file = mysql_fetch_assoc($result)) {
		$filename = $file['filename'];
		if(preg_match('/PRA[0-9]{3}-(.+)\.extract\/(.*)/', $filename, $matches)) {
			$filename = $matches[1];
		}
		if(preg_match('/PRA[0-9]{3}-(.+)/', $filename, $matches)) {
			$filename = $matches[1];
		}
		
?>
		<tr id="file_<?= $file['id']; ?>">
			<td alt="<?php echo $filename; ?>" title="<?= $filename ?>"><img onclick="deleteFile(<?=$file["id"] ?>)" src="/styles/images/icons/papierkorb.png" alt="Datei l&ouml;schen" title="Datei l&ouml;schen" /><a href="https://www.praktika.de<?= $file['url']; ?>" target="_blank"><?= Praktika_String::cutWord($filename, 25); ?></a></td>
			<td class="fileinfo"><strong><?= Praktika_String::formatFilesize($file['size']); ?></strong><br /><span>Upload am <?= date('d.m.Y', $file['datum']); ?></span></td>
			<td><a type="button" class="button red small" onclick="top.smallbox.hide('<?= $file['url']; ?>');" />Datei ausw&auml;hlen</a></td>
		</tr>
<?php
	}
?>
	</table>
<?php
}
?>
</div>
<?= bodyoff(); ?>