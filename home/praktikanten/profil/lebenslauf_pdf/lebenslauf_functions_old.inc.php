<?php

	$classpath = '/home/praktika/public_html/home/praktikanten/profil/lebenslauf_pdf/'; // path, where the class-files live
	$confpath = '/home/praktika/public_html/home/praktikanten/profil/lebenslauf_pdf/';  // path,where the configuration-files live
	$langpath = '/home/praktika/public_html/home/praktikanten/profil/lebenslauf_pdf/';  // path, where the language string files live
	
	$temppath = '/home/praktika/temp/'; // path, where pdf-files for download are temporarily created

	require_once('/home/praktika/etc/mysql_class.inc.php');
	
	function getDefaultLayout($userID) {
		global $praktdbslave;
		$query = "SELECT lebenslauftemplate FROM prakt2_bprofil.einstellungen WHERE nutzerid=$userID";
		$rs = mysql_query($query, $praktdbslave);
		return mysql_result($rs, 0,'lebenslauftemplate');
		
	}
	
	function lebenslaufPdfMain() {
		global $s_nutzerid,$mail,$temppath;
		
		$userID = $s_nutzerid; 
		if (!$userID) die("No UserID found."); 
		
		$layoutID = $_REQUEST["aplic_id"];
		if (empty($layoutID)) $layoutID = getDefaultLayout($userID);
		
		$langID = ($_REQUEST['sprachid']) ? $_REQUEST['sprachid'] : 0;
		$langID = checkLang($langID, $layoutID);
		
		if ($_REQUEST['download']) $action = 'download';
		elseif ($_REQUEST['mail']) $action = 'mail';
		elseif ($mail == "yes") $action = 'extbewerb';
		else $action = 'view';

		switch($action) {
			case 'download' :
				downloadPDFFile($userID, $layoutID, $langID);
			break;
			case 'extbewerb' :
				$pdffilename = getPDFFile($userID, $layoutID, $langID);
				return $pdffilename;
			break;
			case 'mail' :
				mailSelfPDFFile($userID, $layoutID, $langID);
				echo "Ihr Lebenslauf wurde versendet!"; 
			break;
			case 'view' :
			default :
				viewPDF($userID, $layoutID, $langID);

		}
	}
	
	function checkLang($langID, $layoutID) {
		if ($langID==0) {
			$layoutID = strtolower(trim("$layoutID"));
			if ($layoutID[0] == '0' || $layoutID[0] == 'g' || empty($layoutID)) $langID = 2;
			else $langID = 1;
		}
		return $langID;
	}
 
 function mailSelfPdfFile($userID, $layoutID, $langID=0) {
	 global $database_db,$ip;
	 $langID = checkLang($langID, $layoutID);
	 $file = getPDFFile($userID, $layoutID, $langID);
	 include ("/home/praktika/public_html/home/email/mimemail.inc.php");
	 $querystring="SELECT * FROM nutzer WHERE id = $userID";
	 // datensatz abfragen //
	 $result = mysql($database_db,$querystring);
	 // array erzeugen //
	 $queryresult = mysql_fetch_array($result);
	 
	 $subject = "Ihr Lebenslauf von praktika.de";
	 $message = "Anbei erhalten Sie Ihren Lebenslauf von praktika.de";
	 $from_name=$queryresult["vname"]." ".$queryresult["name"];
	 $to_name="Empfänger";
	 $to = $queryresult["email"];
	 $from = $queryresult["email"];
	 
	 $m = new CMIMEMail("$to_name <$to>","$from_name <$from>",$subject);
	 $m->mailbody($message."\n\n-----------------------------------------------------------\n
								Diese Mail wurde gesendet von praktika.de (https://www.praktika.de/)\nMessage sent from $host ($ip) to $to_name <$to>\n\n");

	 $m->attachFile_raw($file,"Lebenslauf.pdf","application/pdf");
	 $m->send();
	 unlink($file);
	 //$m->sendto("emailwatch@praktika.de");
 }
 
 function getPDFFile($userID, $layoutID, $langID=0) {
	 global $temppath;
	 $langID = checkLang($langID, $layoutID);
	 $tempfile = $temppath . time() . '_' . rand(0,100) . '.pdf';
	 $pdf = getLayoutRenderer($layoutID);
	 $pdf->create($userID, $langID);
	 $pdf->output($tempfile, false);
	 return $tempfile;
 }
 
 function downloadPDFFile($userID, $layoutID, $langID=0) {
	 $langID = checkLang($langID, $layoutID);
	 $pdf = getLayoutRenderer($layoutID);
	 $pdf->create($userID, $langID);
	 $pdf->output('lebenslauf.pdf', true);
 }
 
 function viewPDF($userID, $layoutID, $langID=0) {
	 $langID = checkLang($langID, $layoutID);
	 $pdf = getLayoutRenderer($layoutID);
	 $pdf->create($userID, $langID);
	 $pdf->output();
 }
 
 function getLayoutRenderer($layoutID) {
	  global $praktdbslave, $confpath, $classpath, $langpath;
		$layoutID = "$layoutID";
		// $praktdbslave comes from the config.inc.php
		if (!$praktdbslave) die("No DB-Connection available.");
		$db = new DB($praktdbslave);
		$pdf = "";

		switch ($layoutID) {		
			case 'g2' :
			case '02' :
				include_once($classpath.'lebenslauf_g2.php');
				$pdf = new CV_G2($db,$confpath.'lebenslauf_g2_conf.inc.php',	$langpath);
				break;
			case 'g3' :
			case '03' :
				include_once($classpath.'lebenslauf_g3.php');
				$pdf = new CV_G3($db,$confpath.'lebenslauf_g3_conf.inc.php',	$langpath);
				break;
			case 'b2' :
			case '11' :
				include_once($classpath.'lebenslauf_b2.php');
				$pdf = new CV_B2($db,$confpath.'lebenslauf_b2_conf.inc.php',	$langpath);
				break;
			case 'b3' :
			case '12' :
				include_once($classpath.'lebenslauf_b3.php');
				$pdf = new CV_B3($db,$confpath.'lebenslauf_b3_conf.inc.php',	$langpath);
				break;
			case 'u2' :
			case '21' :
				include_once($classpath.'lebenslauf_u2.php');
				$pdf = new CV_U2($db,$confpath.'lebenslauf_u2_conf.inc.php',	$langpath);
				break;
			case 'u3' :
			case '22' :
				include_once($classpath.'lebenslauf_u3.php');
				$pdf = new CV_U3($db,$confpath.'lebenslauf_u3_conf.inc.php',	$langpath);
				break;
			case 'b1' :
			case 'u1' :
			case '10' :
			case '20' :
				$langID = 1;
			case 'g1' :
			case '01' :
			default : 
				include_once($classpath.'lebenslauf_g1.php');
				$pdf = new CV_G1($db,$confpath.'lebenslauf_g1_conf.inc.php',	$langpath);
		}
		return $pdf;
 }
 
?>
