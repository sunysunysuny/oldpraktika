<?php
$mwst_satz = 19;
$tax = $mwst_satz / 100;

require("/home/praktika/etc/config.inc.php");

setlocale(LC_ALL, 'de_DE.UTF-8');

$fpdfpath = '/usr/local/share/fpdf/';

define('FPDF_FONTPATH', $fpdfpath.'font/');
require('/usr/local/share/fpdi/fpdi_protection.php');

require_once('DB.php');
//require_once("SOAP/Server.php");

$connectarray = array (
	'persistent' => true,
	'optimize' => 'performance',
	'debug' => 0
);

$dbzugang = $dbarray[0];

// Data Source Name: This is the universal connection string
$dsn = 'mysql://'.$dbzugang['mysqluser'].':'.$dbzugang['mysqlpassword'].'@'.$dbzugang['host'].'/'.$database_comm;

// DB::connect will return a PEAR DB object on success
// or an PEAR DB Error object on error
$db = DB::connect($dsn,$connectarray);

// With DB::isError you can differentiate between an error or
// a valid connection.
if (DB::isError($db)) {
	die ($db->getMessage());
}

$db->setFetchMode(DB_FETCHMODE_ASSOC);

$sql = '	SELECT
				DATE_FORMAT(kuendigung, \'%d.%m.%Y\') as lizenzablauf
			FROM
				'.$database_db.'.firmen_prop_assign
			WHERE
				firmenid = '.$_SESSION['s_firmenid'];

$kuendigung = $db->getRow($sql);

$sql = '	SELECT
				gekuendigte_firmenlizenz,
				kuendigungsdatum
			FROM
				'.$database_db.'.kuendigungen
			WHERE
				firmenid = '.$_SESSION['s_firmenid'].'
			ORDER BY
				id
			LIMIT 1';

$lizenz = $db->getRow($sql);

$sql = '	SELECT
				firma,
				strasse,
				plz,
				ort
			FROM
				'.$database_db.'.firmen
			WHERE
				id = '.$_SESSION['s_firmenid'];

$firma = $db->getRow($sql);

class FPDI_Protection_Table extends FPDI_Protection {
	var $widths;
	var $aligns;
	
	function SetWidths($w) {
		//Set the array of column widths
		$this->widths = $w;
	}
	
	function SetAligns($a) {
		//Set the array of column alignments
		$this->aligns = $a;
	}
	
	function SetNoLines($l) {
		//Set the array of column bottom line
		$this->lines = $l;
	}
	
	function Row($data) {
		//Calculate the height of the row
		$nb = 0;
		
		for ($i = 0; $i < count($data); $i++) {
			$nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
		}
		
		$h = 4 * $nb;
		
		//Issue a page break first if needed
		$this->CheckPageBreak($h);
		
		//Draw the cells of the row
		for ($i = 0; $i < count($data); $i++) {
			$w = $this->widths[$i];
			$a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
			
			//Save the current position
			$x = $this->GetX();
			$y = $this->GetY();
			
			//Draw the border
			//$this->Rect($x, $y, $w, $h);
			if ($this->lines[$i] != true) {
				$this->Line($x, $y+$h, $x+$w, $y+$h);
			}
			
			//Print the text
			$this->MultiCell($w, 4, $data[$i], 0, $a);
			
			//Put the position to the right of the cell
			$this->SetXY($x + $w, $y);
		}
		
		//Go to the next line
		$this->Ln($h);
	}
	
	function CheckPageBreak($h) {
		//If the height h would cause an overflow, add a new page immediately
		if (($this->GetY() + $h) > $this->PageBreakTrigger) {
			$this->AddPage($this->CurOrientation);
			$this->SetX(14);
		}
	}
	
	function NbLines($w, $txt) {
		//Computes the number of lines a MultiCell of width w will take
		$cw = &$this->CurrentFont['cw'];
		
		if ($w == 0) {
			$w = $this->w-$this->rMargin-$this->x;
		}
		
		$wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
		$s = str_replace("\r", '', $txt);
		$nb = strlen($s);
		
		if ($nb>0 and $s[$nb-1] == "\n") {
			$nb--;
		}
		
		$sep = -1;
		$i = 0;
		$j = 0;
		$l = 0;
		$nl = 1;
		
		while ($i < $nb) {
			$c = $s[$i];
			
			if ($c == "\n") {
				$i++;
				$sep = -1;
				$j = $i;
				$l = 0;
				$nl++;
				continue;
			}
			
			if ($c == ' ') {
				$sep = $i;
			}
			
			$l += $cw[$c];
			
			if ($l > $wmax) {
				if ($sep == -1) {
					if ($i == $j) {
						$i++;
					}
				} else {
					$i = $sep + 1;
				}
				
				$sep = -1;
				$j = $i;
				$l = 0;
				$nl++;
			} else {
				$i++;
			}
		}
		return $nl;
	}
}

$pdf = new FPDI_Protection_Table();
$pdf->SetProtection(array('print'));

$pagecount = $pdf->setSourceFile('template.pdf');

$tplidx = $pdf->ImportPage(1);

$pdf->addPage();
$pdf->useTemplate($tplidx);

$pdf->SetAutoPageBreak(FALSE);

$pdf->SetY(55);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0);
//$pdf->SetTextColor(256,256,256);

$pdf->SetXY(16 + $s['w'], $pdf->y);
$pdf->Cell(0, 6, $firma['firma']);

$pdf->SetFont('Arial', 'I', 10);
$pdf->SetXY(16 + $s['w'], $pdf->y + (5));
$pdf->Cell(0, 6, $firma['strasse']);

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetXY(16 + $s['w'], $pdf->y + (5));
$pdf->Cell(0, 6, $firma['plz'].' '.$firma['ort']);

$pdf->SetFont('Arial', '', 7);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(14 + $s['w'], $pdf->y + (18));
$pdf->Cell(0, 6, 'Datum: '.strftime('%x', strtotime($lizenz['kuendigungsdatum'])));

if ($lizenz['gekuendigte_firmenlizenz'] == 1) {
	$lizenzbezeichnung = 'Basis';
} elseif ($lizenz['gekuendigte_firmenlizenz'] == 2) {
	$lizenzbezeichnung = 'Komfort';
} elseif ($lizenz['gekuendigte_firmenlizenz'] == 3) {
	$lizenzbezeichnung = 'Premium';
}

$pdf->SetFont('Arial','B',12);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(14 + $s['w'], $pdf->y + (7));
$pdf->Cell(0, 6, 'Kündigung der Lizenz '.$lizenzbezeichnung);

$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(14 + $s['w'], $pdf->y + (19));
$pdf->Cell(0, 6, 'Sehr geehrte Damen und Herren,');

$pdf->SetXY(14 + $s['w'], $pdf->y + (7));
$pdf->Cell(0, 6, 'gemäß Ihres Vertrages bestätigen wir Ihnen die Kündigung Ihrer '.$lizenzbezeichnung.'-Lizenz zum '.$kuendigung['lizenzablauf'].'.');

$pdf->SetXY(14 + $s['w'], $pdf->y + (4));
$pdf->Cell(0, 6, 'Für Rückfragen stehen wir Ihnen gern zur Verfügung.');

$pdf->SetXY(14 + $s['w'], $pdf->y + (17));
$pdf->Cell(0, 6, 'Mit freundlichen Grüßen');
 
$pdf->SetXY(14 + $s['w'], $pdf->y + (7));
$pdf->Cell(0, 6, 'Ihr Praktika-Team');

$pdf->Output('Kuendigung.pdf', 'I');
?>