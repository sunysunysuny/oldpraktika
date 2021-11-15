<?
$mwst_satz = 19;
$tax = $mwst_satz/100;

require("/home/praktika/etc/config.inc.php");

setlocale (LC_ALL, 'de_DE.UTF-8');

$fpdfpath = "/usr/share/php/fpdf/";

define('FPDF_FONTPATH',$fpdfpath.'font/');
require('/usr/share/php/fpdi/fpdi_protection.php');


require_once("DB.php");
//require_once("SOAP/Server.php");

$connectarray = array (
'persistent' => true,
'optimize' => 'performance',
'debug' => 0
);


$dbzugang = $dbarray[0];

// Data Source Name: This is the universal connection string
$dsn = "mysql://".$dbzugang["mysqluser"].":".$dbzugang["mysqlpassword"]."@".$dbzugang["host"]."/".$database_comm;

// DB::connect will return a PEAR DB object on success
// or an PEAR DB Error object on error

$db = DB::connect($dsn,$connectarray);

// With DB::isError you can differentiate between an error or
// a valid connection.
if (DB::isError($db)) {
	die ($db->getMessage());
}

$db->setFetchMode(DB_FETCHMODE_ASSOC);

$zahlung = $db->getRow("SELECT 
			firmen.land,
			land.german as land_german,
			firmen_prop_assign.*,
			zahlungen.*,
			zahlungen.datum_eintrag as zahldatum
		FROM $database_comm.zahlungen
			LEFT JOIN $database_db.firmen_prop_assign ON(firmen_prop_assign.firmenid = zahlungen.stammid)
			LEFT JOIN $database_db.firmen ON(firmen.id = zahlungen.stammid)
			LEFT JOIN $database_db.land ON(land.id = firmen.land)
		WHERE zahlungen.id = '".$_GET["id"]."'");

$countryarray2 = $db->getAssoc("SELECT `id`, `german` FROM `prakt2`.`land`");

$buchungen = $db->getAll("SELECT buchungen.*, land.english as land_english FROM
	$database_comm.buchungen LEFT JOIN prakt2.land ON(land.id = buchungen.land)
	WHERE buchungen.id IN (".$zahlung["bestellid"].")");

class FPDI_Protection_Table extends FPDI_Protection
{
	var $widths;
	var $aligns;
	
	function SetWidths($w)
	{
	    //Set the array of column widths
	    $this->widths=$w;
	}
	
	function SetAligns($a)
	{
	    //Set the array of column alignments
	    $this->aligns=$a;
	}
	
	function SetNoLines($l)
	{
	    //Set the array of column bottom line
	    $this->lines=$l;
	}

	function Row($data)
	{
	    //Calculate the height of the row
	    $nb=0;
	    for($i=0;$i<count($data);$i++)
	        $nb=max($nb, $this->NbLines($this->widths[$i], $data[$i]));
	    $h=4*$nb;
	    //Issue a page break first if needed
	    $this->CheckPageBreak($h);
	    //Draw the cells of the row
	    for($i=0;$i<count($data);$i++)
	    {
	        $w=$this->widths[$i];
	        $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
	        //Save the current position
	        $x=$this->GetX();
	        $y=$this->GetY();
	        //Draw the border
	        //$this->Rect($x, $y, $w, $h);
	        if ($this->lines[$i] != true) 
	        	$this->Line($x, $y+$h, $x+$w, $y+$h);
	        //Print the text
	        $this->MultiCell($w, 4, $data[$i], 0, $a);
	        //Put the position to the right of the cell
	        $this->SetXY($x+$w, $y);
	    }
	    //Go to the next line
	    $this->Ln($h);
	}
	
	function CheckPageBreak($h)
	{
	    //If the height h would cause an overflow, add a new page immediately
	    if($this->GetY()+$h>$this->PageBreakTrigger) {
	        $this->AddPage($this->CurOrientation);
	        $this->SetX(14);
	    }
	}
	
	function NbLines($w, $txt)
	{
	    //Computes the number of lines a MultiCell of width w will take
	    $cw=&$this->CurrentFont['cw'];
	    if($w==0)
	        $w=$this->w-$this->rMargin-$this->x;
	    $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
	    $s=str_replace("\r", '', $txt);
	    $nb=strlen($s);
	    if($nb>0 and $s[$nb-1]=="\n")
	        $nb--;
	    $sep=-1;
	    $i=0;
	    $j=0;
	    $l=0;
	    $nl=1;
	    while($i<$nb)
	    {
	        $c=$s[$i];
	        if($c=="\n")
	        {
	            $i++;
	            $sep=-1;
	            $j=$i;
	            $l=0;
	            $nl++;
	            continue;
	        }
	        if($c==' ')
	            $sep=$i;
	        $l+=$cw[$c];
	        if($l>$wmax)
	        {
	            if($sep==-1)
	            {
	                if($i==$j)
	                    $i++;
	            }
	            else
	                $i=$sep+1;
	            $sep=-1;
	            $j=$i;
	            $l=0;
	            $nl++;
	        }
	        else
	            $i++;
	    }
	    return $nl;
	}
}

$pdf=new FPDI_Protection_Table();
$pdf->SetProtection(array('print'),'','prakt2010r6awt54ctz');

$pagecount = $pdf->setSourceFile("template.pdf");

$tplidx = $pdf->ImportPage(1);

$pdf->addPage();
$pdf->useTemplate($tplidx);

$pdf->SetAutoPageBreak(FALSE);

$pdf->SetY(55);
$pdf->SetFont("Arial",'',10);
$pdf->SetTextColor(0,0,0);
//$pdf->SetTextColor(256,256,256);

$pdf->SetXY(16 + $s['w'], $pdf->y);
$pdf->Cell(0, 6, utf8_decode($buchungen[0]["firma"]));

if(isset($buchungen[0]["abteilung"]) && !empty($buchungen[0]["abteilung"])) {
	$pdf->SetFont("Arial",'',10);
	$pdf->SetXY(16 + $s['w'], $pdf->y + (5));
	$pdf->Cell(0, 6, "- ".utf8_decode($buchungen[0]["abteilung"]." -"));
}

$pdf->SetFont("Arial",'I',10);
$pdf->SetXY(16 + $s['w'], $pdf->y + (5));
$pdf->Cell(0, 6, utf8_decode($buchungen[0]["ansprechpartner"]));

$pdf->SetFont("Arial",'I',10);
$pdf->SetXY(16 + $s['w'], $pdf->y + (5));
$pdf->Cell(0, 6, utf8_decode($buchungen[0]["strasse"]));

$pdf->SetFont("Arial",'B',10);
$pdf->SetXY(16 + $s['w'], $pdf->y + (5));
$pdf->Cell(0, 6, utf8_decode($buchungen[0]["plz"]." ".$buchungen[0]["ort"]));

if(!empty($buchungen[0]["land"]) && $buchungen[0]["land"] != "68") {
$pdf->SetFont("Arial",'B',10);
$pdf->SetXY(16 + $s['w'], $pdf->y + (4));
$pdf->Cell(0, 7, utf8_decode($buchungen[0]["land_english"]));
}

/* if($zahlung["land"] != "68") {
	$pdf->SetFont("Arial",'',10);
	$pdf->SetXY(16 + $s['w'], $pdf->y + (4));
	$pdf->Cell(0, 7, $zahlung["land_german"]);
} */

$pdf->SetFont("Arial",'',7);
$pdf->SetTextColor(0,0,0);
$pdf->SetXY(14 + $s['w'], $pdf->y + (18));
// Wenn rechnungsdatum vor dem 1.5.2010 liegt, dann datum_eintrag als Rechnungsdatum nutzen. Ansonsten das Buchungsdatum
if(strtotime($zahlung["zahldatum"]) < 1272690000) {
	$pdf->Cell(0, 6, "Datum: ".strftime("%x", strtotime($zahlung["zahldatum"])));
} else {
	$pdf->Cell(0, 6, "Datum: ".strftime("%x", $buchungen[0]["zeitraum_von"]));
}

$pdf->SetFont("Arial",'B',12);
$pdf->SetTextColor(0,0,0);
$pdf->SetXY(14 + $s['w'], $pdf->y + (7));
$pdf->Cell(0, 6, "Rechnung Nr. ".$zahlung["rechnungsnummer"]." / Kunden Nr. ".$zahlung["lexware_kn"]);

$abstand = 9;
#if(!empty($buchungen[0]["buchungsnummer"])) {
#	$pdf->SetFont("Arial",'I',8);
#	$pdf->SetTextColor(0,0,0);
#	$pdf->SetXY(14 + $s['w'], $pdf->y + (7));
#	$pdf->Cell(0, 6, "Auftragsnummer: ".$buchungen[0]["buchungsnummer"]);
#	$abstand = 4;
#}

$pdf->SetFont("Arial",'',8);
$pdf->SetTextColor(0,0,0);
$pdf->SetXY(14 + $s['w'], $pdf->y + $abstand);
$pdf->Cell(0, 6, "Sehr geehrte Damen und Herren,");

$pdf->SetXY(14 + $s['w'], $pdf->y + (7));
$pdf->Cell(0, 6, "wir bedanken uns für die Buchung unserer Serviceleistungen bzw. Produkte.");

$pdf->SetXY(14 + $s['w'], $pdf->y + (4));
$pdf->Cell(0, 6, "Wir erlauben uns, diese nachfolgend in Rechnung zu stellen.");

$pdf->SetXY(14 + $s['w'], $pdf->y + (8));

//Set width fro table columns
$pdf->SetFont('Arial', '', 8);
$pdf->SetWidths(array(10, 15, 82, 20, 20));
$pdf->SetAligns(array("C", "C", "L", "C", "C"));

$header=array('Pos.','Anzahl','Beschreibung','Einzelpreis','Gesamt');
$pdf->Row($header);

$itemnum=0;

//Data loading
foreach ($buchungen as $buchkey => $buchrow) {
    $produkte=mysql_db_query($database_comm,"select * from produkte where id='".$buchrow["produktid"]."'",$praktdbslave);

	if ($buchrow["zeitraum_von"] > 0) {
    		$bezeichnung=(mysql_result($produkte,0,"bezeichnung"))."\n".mysql_result($produkte,0,"beschreibung")."\nZeitraum: ".strftime("%x",$buchrow["zeitraum_von"])." - ".strftime("%x",$buchrow["zeitraum_bis"])."\ninkl. Rabattstaffel";
	} else {
    		$bezeichnung=mysql_result($produkte,0,"bezeichnung")."\n".mysql_result($produkte,0,"beschreibung")."\n".$buchrow["buchmenge"]." ".$buchrow["mengenbezeichnung"]."\ninkl. Rabattstaffel";
	}

    $einzelpreis="".number_format(mysql_result($produkte,0,"preis"), 2, ',', '.');

    if ($buchrow["storno"] == "true") $buchrow["buchmenge"]=$buchrow["buchmenge"]*(-1);

	if($buchrow["produktid"] == 54) {
		$aboText = "Vertragslaufzeit ist jeweils ein Monat ab Buchungsdatum. Danach verlängert sich der Vertrag automatisch (Abonnement) jeweils um einen weiteren Monat, es sei denn er wurde mindestens 7 Tage vor dem Ende der Laufzeit schriftlich gekündigt.";
	}
	if($buchrow["produktid"] == 55) {
		$aboText = "Vertragslaufzeit ist jeweils 3 Monate ab Buchungsdatum. Danach verlängert sich der Vertrag automatisch (Abonnement) jeweils um 3 Monate, es sei denn er wurde mindestens 30 Tage vor dem Ende der Laufzeit schriftlich gekündigt.";
	}
	if($buchrow["produktid"] == 56) {
		$aboText = "Vertragslaufzeit ist jeweils 6 Monate ab Buchungsdatum. Danach verlängert sich der Vertrag automatisch (Abonnement) jeweils um 6 Monate, es sei denn er wurde mindestens 30 Tage vor dem Ende der Laufzeit schriftlich gekündigt.";
	}
	if($buchrow["produktid"] == 57) {
		$aboText = "Vertragslaufzeit ist jeweils 6 Monate ab Buchungsdatum. Danach verlängert sich der Vertrag automatisch (Abonnement) jeweils um 6 Monate, es sei denn er wurde mindestens 30 Tage vor dem Ende der Laufzeit schriftlich gekündigt.";
	}
	
    if ($buchrow["produktid"] == 18 && $buchrow["preis"] == 100) $abopreis=100;
    elseif ($buchrow["produktid"] == 18 && $buchrow["preis"] == 125) $abopreis=125;
    elseif ($buchrow["produktid"] == 15 && $buchrow["preis"] == 750) $abopreis=750;
    else {
    	if ($buchrow["produktid"] == 18 || $buchrow["produktid"] == 15)
    	$abopreis=$buchrow["preis"];
    }
    
    
	$itemnum++;
	$data[0] = $itemnum;
	$data[1] = $buchrow["buchmenge"];
	$data[2] = utf8_decode($bezeichnung);
	$data[3] = number_format(mysql_result($produkte,0,"preis"), 2, ',', '')." €";
	$data[4] = number_format($priceline = ((($buchrow['buchmenge'] > 0) ? $buchrow['preis'] * $buchrow['buchmenge'] : $buchrow["preis"])), 2, ',', '').' €';
	$totalprice += sprintf("%1.2f",$priceline);
	$taxprice += sprintf("%1.2f",$priceline);
	
	$pdf->SetXY(14 + $s['w'], $pdf->y + (2));
	
	$pdf->SetFont('Arial', '', 8);
	$pdf->SetWidths(array(10, 15, 82, 20, 20));
	$pdf->SetAligns(array("C", "C", "L", "R", "R"));
	
	$pdf->Row($data);
}
unset ($data);

$data[0] = "Gesamt Netto";
$data[1] = "";
$data[2] = number_format($totalprice, 2, ',', '')." €";

$pdf->SetXY(14 + $s['w'], $pdf->y + (2));
$pdf->SetFont('Arial', '', 8);
$pdf->SetWidths(array(107, 20, 20));
$pdf->SetAligns(array("L", "R", "R"));
$pdf->Row($data);

if(isset($taxprice) && $totalprice-$taxprice > 0) {
	$data[0] = "steuerfrei";
	$data[1] = number_format($totalprice-$taxprice, 2, ',', '')." €";
	$data[2] = "";
	
	$pdf->SetXY(14 + $s['w'], $pdf->y + (2));
	$pdf->SetFont('Arial', '', 8);
	$pdf->Row($data);

} elseif(isset($taxprice) && $taxprice > 0) {
	$data[0] = "zzgl. 19,00 % USt. auf";
	$data[1] = number_format($taxprice, 2, ',', '')." €";
	$data[2] = number_format($taxsum = $taxprice*($tax), 2, ',', '')." €";
	
	$pdf->SetXY(14 + $s['w'], $pdf->y + (2));
	$pdf->SetFont('Arial', '', 8);
	$pdf->Row($data);
}

$data[0] = "Gesamtbetrag";
$data[1] = "";
$data[2] = number_format($totalprice+$taxsum, 2, ',', '')." €";

$pdf->SetXY(14 + $s['w'], $pdf->y + (2));
$pdf->SetFont('Arial', 'B', 8);
$pdf->Row($data);

$pdf->SetXY(14 + $s['w'], $pdf->y + (4));
$pdf->SetFont('Arial', '', 8);
$pdf->SetWidths(array(147));
$pdf->SetAligns(array("L"));
$pdf->SetNoLines(array(true));

unset ($data);

if ($zahlung["zahlungsart"]=="lastschrift") {
	$data[0] = "Der Betrag wird innerhalb der nächsten Tage per Lastschrift von Ihrem Konto: ".$zahlung["ktonr"].", BLZ: ".$zahlung["blz"]." abgebucht.";
	$pdf->Row($data);
} elseif ($zahlung["zahlungsart"]=="rechnung") {
	$data[0] = "Zahlbar innerhalb von 7 Tagen nach Erhalt der Rechnung.";
	$pdf->Row($data);
} elseif ($zahlung["zahlungsart"]=="kreditkarte") {
	$data[0] = "Die Zahlung erfolgt durch Kreditkarte mit der Nummer: ".$zahlung["kartennummer"]." gültig bis: ".$zahlung["monat"]."/".$zahlung["jahr"].".";
	$pdf->Row($data);
} elseif ($zahlung["zahlungsart"]=="paypal") {
	$data[0] = "Die Zahlung erfolgte über paypal.com";
	$pdf->Row($data);
}

if(!empty($aboText)) {
	$data[0] = $aboText;
}
if (!empty($aboText) || isset($abopreis) && $abopreis > 0) {

	$pdf->SetXY(14 + $s['w'], $pdf->y + (4));
	$pdf->SetFont('Arial', '', 8);
	$pdf->SetWidths(array(147));
	$pdf->SetAligns(array("L"));
	$pdf->SetNoLines(array(true));
	if ($abopreis == 100){
		$data[0] = "Vertragslaufzeit ist jeweils 12 Monate ab Buchungsdatum. Danach verlängert sich der Vertrag automatisch (Abonnement) jeweils um weitere 12 Monate, es sei denn er wurde mindestens 30 Tage vor dem Ende der Laufzeit schriftlich gekündigt.
Für das nächste Rechnungsjahr erhöht sich der Nettobetrag um 25 Euro.";
	}
	elseif ($abopreis > 100 ){
		$data[0] = "Vertragslaufzeit ist jeweils 12 Monate ab Buchungsdatum. Danach verlängert sich der Vertrag automatisch (Abonnement) jeweils um weitere 12 Monate, es sei denn er wurde mindestens 30 Tage vor dem Ende der Laufzeit schriftlich gekündigt.";
	}

	$pdf->Row($data);
}

$pdf->SetXY(14 + $s['w'], $pdf->y + (4));
$pdf->SetFont('Arial', '', 8);
$pdf->SetWidths(array(147));
$pdf->SetAligns(array("L"));
$pdf->SetNoLines(array(true));
$data[0] = "Rechnungsdatum = Liefer- und Leistungsdatum. 
Bei Überschreitung der Fälligkeit berechnen wir Verzugszinsen gemäß den gesetzlichen Vorschriften. Als vereinbart gelten unsere AGB.";
$pdf->Row($data);

if ($zahlung["zahlungsart"]=="rechnung") {
	$pdf->SetXY(14 + $s['w'], $pdf->y + (4));
	$pdf->SetFont('Arial', '', 8);
	$pdf->SetWidths(array(147));
	$pdf->SetAligns(array("L"));
	$pdf->SetNoLines(array(true));
	$data[0] = "Überweisung an: 
Commerzbank AG
Kto: 1 902 829 00
BLZ: 860 800 00

Verwendungszweck: ".$zahlung["rechnungsnummer"].", ".$zahlung["lexware_kn"];
	$pdf->Row($data);
}

$pdf->SetXY(14 + $s['w'], $pdf->y + (4));
$pdf->SetFont('Arial', '', 8);
$pdf->SetWidths(array(147));
$pdf->SetAligns(array("L"));
$pdf->SetNoLines(array(true));
$data[0] = "Fragen zu dieser Rechnung bitte unter +49 (0) 341 2252029. Wir bedanken uns für Ihr Vertrauen und freuen uns auch in Zukunft auf eine weitere gute Zusammenarbeit).
";
$pdf->Row($data);

/*
if ($user["productid "] != "4" && $user["productid"] != "6") {
	$tplidx = $pdf->ImportPage(2);
	$pdf->addPage();
	$pdf->useTemplate($tplidx);
}
//$pdf->SetXY(15 + $s['w'], $pdf->y + (247 + $verschiebung2));
//$pdf->Cell(0, 6, "Bonn, ".strftime("%x", time()));
*/

if ($sendemail == true) {
	$pdfcontent = $pdf->Output("Rechnung.pdf", "S");
	$db->query("UPDATE zahlungen SET zugestellt = 'true' WHERE id = '".$_GET["id"]."' LIMIT 1");
} else {
	$pdf->Output("newpdf.pdf","I");
	$pdf->_closeParsers();
}


if ($sendemail == true) {
	require_once('Mail.php');
	
	require_once('Mail/mime.php');
	
	// email address of the recipient
	$to = $buchungen[0]["email"];
	
	// email address of the sender
	$from = "service@praktika.de";
	
	// subject of the email 
	$subject = "praktika.de: Ihre Rechnung zur Buchung"; 
	
	// email header format complies the PEAR's Mail class
	// this header includes sender's email and subject
	$headers = array('From' => $from, 'Return-Path' => $from, 'Subject' => $subject); 
		
	// We will send this email as HTML format
	// which is well presented and nicer than plain text
	// using the heredoc syntax
	// REMEMBER: there should not be any space after PDFMAIL keyword
$textMessage = "Sehr geehrte Damen und Herren,";

$textMessage .= "

vielen Dank für Ihre Stellenausschreibung auf praktika.de

Anbei sende Ich Ihnen die Rechnung zum selbst ausdrucken für Ihre Buchhaltung.
Für Fragen stehe ich Ihnen jederzeit zur Verfügung.

Mit freundlichen Grüßen

Ihr praktika.de Team

---------------------------------
PRAKTIKA GmbH
Petersstraße 28, 04109 Leipzig
Tel.: 0341-2252029
Fax: 0341-2252059
 
Büro- und Telefonzeiten: Mo.-Fr. 9:00 Uhr - 18:00 Uhr, danach AB
 
Pflichtangaben und Impressum finden Sie hier: https://www.praktika.de/cms/Impressum.21.0.html"; 

	
	// create a new instance of the Mail_Mime class
	$mime = new Mail_Mime(); 
	
	// set HTML content
	$mime->setTXTBody($textMessage); 
	
	// IMPORTANT: add pdf content as attachment
	$mime->addAttachment($pdfcontent, 'application/pdf', 'Rechnung.pdf', false, 'base64');

	// IMPORTANT: add AGB pdf content as attachment
	$mime->addAttachment("/home/praktika/public_html/cms/fileadmin/AGB_pdf/agb_fuer_unternehmen.pdf", 'application/pdf', 'Unsere_AGB.pdf', true, 'base64');

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
	
	echo "<script type=\"text/javascript\">alert('Die Rechnung wurde an \'".$to."\' versendet!');setTimeout('window.close()',500);</script>";
	}
?>