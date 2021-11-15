<?php
	require_once('Mail.php');
	
	require_once('Mail/mime.php');
	
function mail_att($to, $subject, $message, $anhang) {

	// email address of the sender
	$from = "info@www.praktika.de";
	
	// subject of the email 
	$subject = "Fragebogen Klimatest"; 
	
	// email header format complies the PEAR's Mail class
	// this header includes sender's email and subject
	$headers = array('From' => $from, 'Return-Path' => $from, 'Subject' => $subject); 
		
	// We will send this email as HTML format
	// which is well presented and nicer than plain text
	// using the heredoc syntax
	// REMEMBER: there should not be any space after PDFMAIL keyword
$textMessage = $message;


	// create a new instance of the Mail_Mime class
	$mime = new Mail_Mime(); 
	
	// set HTML content
	$mime->setTXTBody($message); 
	
	// IMPORTANT: add pdf content as attachment
	$mime->addAttachment($anhang['data'], 'application/pdf', 'Fragebogen.pdf', false, 'base64');
	
	// build email message and save it in $body
	$body = $mime->get();
	$body = preg_replace('/\r\n|\r/', "\n", $body);
	
	// build header
	$hdrs = $mime->headers($headers);
	$hdrs = preg_replace('/\r\n|\r/', "\n", $hdrs);
	
	// create Mail instance that will be used to send email later
	$mail = &Mail::factory('mail'); 
	
	// Sending the email, according to the address in $to,
	// the email headers in $hdrs,
	// and the message body in $body.
	$mail->send($to, $hdrs, $body);
	return true;
/*
	$absender = 'Fragebogen Klimatest';
	$absender_mail = 'info@www.praktika.de';
	$reply = 'info@www.praktika.de';
	
	$mime_boundary = '-----='.md5(uniqid(mt_rand(), 1));
	
	$header  = 'From:'.$absender.'<'.$absender_mail.'>'."\n";
	$header .= 'Reply-To: '.$reply."\n";
	
	$header .= 'MIME-Version: 1.0'."\r\n";
	$header .= 'Content-Type: multipart/mixed;'."\r\n";
	$header .= ' boundary="'.$mime_boundary.'"'."\r\n";
	
	$content = 'This is a multi-part message in MIME format.'."\r\n\r\n";
	$content.= '--'.$mime_boundary."\r\n";
	$content.= 'Content-Type: text/html charset="iso-8859-1"'."\r\n";
	$content.= 'Content-Transfer-Encoding: 8bit'."\r\n\r\n";
	$content.= $message."\r\n";
	
	//$anhang ist ein Mehrdimensionals Array
	//$anhang enthält mehrere Dateien
	if (is_array($anhang) && is_array(current($anhang))) {
		foreach ($anhang as $dat) {
			$data = chunk_split(base64_encode($anhang['data']));
			$content .= '--'.$mime_boundary."\r\n";
			$content .= 'Content-Disposition: attachment;'."\r\n";
			$content .= '\tfilename="'.$dat['name'].'";'."\r\n";
			$content .= 'Content-Length: .'.$dat['size'].';'."\r\n";
			$content .= 'Content-Type: '.$dat['type'].'; name="'.$dat['name'].'"'."\r\n";
			$content .= 'Content-Transfer-Encoding: base64'."\r\n\r\n";
			$content .= $data."\r\n";
		}
		
		$content .= '--'.$mime_boundary.'--'; 
	} else { //nur eine Datei als Anhang
		$data = chunk_split(base64_encode($anhang['data']));
		$content .= '--'.$mime_boundary."\r\n";
		$content .= 'Content-Disposition: attachment;'."\r\n";
		$content .= '\tfilename="'.$anhang['name'].'";'."\r\n";
		$content .= 'Content-Length: .'.$anhang['size'].';'."\r\n";
		$content .= 'Content-Type: '.$anhang['type'].'; name="'.$anhang['name'].'"'."\r\n";
		$content .= 'Content-Transfer-Encoding: base64'."\r\n\r\n";
		$content .= $data."\r\n";
	}
	
	if (@mail($to, $subject, $content, $header)) {
		return true;
	} else {
		return false;
	}
	*/
}

if (isset($_POST['send'])) {
	$anhang = array();
	$anhang['name'] = $_FILES['anhang']['name'];
	$anhang['size'] = $_FILES['anhang']['size'];
	$anhang['type'] = $_FILES['anhang']['type'];
	$anhang['data'] = implode('',file($_FILES['anhang']['tmp_name']));

	if (mail_att('chlosta@chlosta-consult.de', 'Fragebogen Klimatest', 'Im Anhang finden Sie den Fragebogen zum Klimatest bei der PRAKTIKA GmbH.', $anhang) == true) {
//	if (mail_att('info@chlosta-consult.de', 'Fragebogen Klimatest', 'Im Anhang finden Sie den Fragebogen zum Klimatest bei der PRAKTIKA GmbH.', $anhang) == true) {
//	if (mail_att('frank.ackermann@gmx.com', 'Fragebogen Klimatest', 'Im Anhang finden Sie den Fragebogen zum Klimatest bei der PRAKTIKA GmbH.', $anhang) == true) {
		echo '<p>Das Dokument wurde erfolgreich versandt.</p>'."\n";
	} else {
		echo '<p>Beim Versenden des Dokuments ist ein Fehler aufgetreten.</p>'."\n";
	}
	
}
?>

<form enctype="multipart/form-data" action="<?php echo htmlspecialchars ($_SERVER['PHP_SELF']); ?>" method="post">
	<legend>Chlosta Klimatest</legend>
	<fieldset>
		<p>
			<label>PDF-Dokument:</label>
			<input type="file" name="anhang" value="" />
		</p>
	</fieldset>
	<fieldset>
		<p>
			<input type="submit" name="send" value="eMail senden" />
			<input type="hidden" name="max_file_size" value="2000">
		</p>
	</fieldset>
</form>