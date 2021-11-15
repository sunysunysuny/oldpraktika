<?php

/* This is a refactoring of previewlebenslauf_pdf.php
 * Author: Matthias Lehmann
 * Datum: September 2003
 * Version: 0.1
 */
 require_once('pdf/fpdf.php');
 define('FPDF_FONTPATH','/home/praktika/public_html/home/praktikanten/profil/lebenslauf_pdf/pdf/font/');
 define('PX2MM',0.35); //factor for conversion from pixels to mm 
 
 class CVBase
 {
	 /* class variables */
	 // path to base configuration file
	 var $baseConfFile = 'lebenslauf_conf.inc.php';
	 // prefix for i18n files, the langName gets appended and then '.inc.php' 
	 var $i18nFilePrefix = 'lebenslauf_i18n_';
	 // mapping between langID and langName
	 var $langMap = array(1 => 'english', 2 => 'german');
	 
	 /* private members */	 
	 // instance of FPDF
	 var $pdf;
	 // used for block pagebreak-checking
	 var $pdfBak;
	 // used for block pagebreak-checking
	 var $_blockStart = 0;
	 var $_blockArr;
	 var $_blockNum;
	 // instance of mysql-wrapper
	 var $db;
	 // configuration-data
	 var $conf = array();
	 // translation strings
	 var $i18nDat = array();
	 // width of page
	 var $pageWidth = 210;
	 // height of page
	 var $pageHeight = 297;
	 // width of printable Area 
	 var $innerWidth;
	 // heigt of printable area
	 var $innerHeight;
	 // line height, dependent of current font
	 var $lineHeight;
	 // font-size of current font
	 var $fontSize;
	 // font-name of current font
	 var $fontName;
	 // color-name of current colors
	 var $colorName;
	 // currently set column-layout
	 var $colsName;
	 // font-size used in first column
	 var $col1FontSize;
	 // line-height used in first column
	 var $col1LineHeight;
	 
	 // configuration of column-layout
	 // where does first column start (mm) 
	 var $colFirstStart;
	 // where does the second column start
	 var $colSecStart;
	 // where does first column end
	 var $colFirstEnd;
	 // where does second column end
	 var $colSecEnd;
	 
	 // line-width used for borders etc.
	 var $lineWidth;
	 
	 
	 var $temp_pic = 0;
	 
	 // ID of user
	 var $userID;
	 // ID of selected language
	 var $langID;
	 
	 /* public methods */
	 
	 
	 // Constructor
	 function CVBase($db, $confFile, $langPath)
	 {
		 $this->i18nFilePrefix = $langPath . $this->i18nFilePrefix;
		 $this->db = $db;
		 $this->_readConf($confFile);
		 $this->pdf = new FPDF('P','mm','A4');
		 $this->pdf->SetMargins($this->conf['marginLeft'], $this->conf['marginTop'], $this->conf['marginRight']);
		 $this->pdf->SetAutoPageBreak(true, ($this->conf['marginBottom']+10));
		 $this->pdf->AliasNbPages($this->conf['aliasNbPages']);
		 $this->setFont(); //set standard font, configured by font-std
		 $this->setCols(); //set standard column-layout, configured by cols-std
		 $this->setColors(); //set standard colors, configured by colors-std
		 $this->setLineWidth($this->conf['lineWidth']);
		 $this->innerWidth = $this->pageWidth - $this->conf['marginLeft'] - $this->conf['marginRight'];
		 $this->innerHeight = $this->pageHeight - $this->conf['marginTop'] - $this->conf['marginBottom'];
		 $this->pdf->Open();
		 $this->pdf->AddPage();
	 }
		 
	 // creates the PDF-document for user with ID userID and in language with langID
	 function create($userID, $langID)
	 {
		 $this->userID = $userID;
		 $this->langID = $langID;
		 $this->_readI18nDat(); // read language string-tabel according to langID
		 $this->_create(); // do actual creation of pdf
		 if ($this->temp_pic != 0) @unlink($this->temp_pic);
	 }
	 
	 // sends the document to the client
	 function output($file="", $download=true)
	 {
		 $this->pdf->Output($file, $download);
	 }

	 // set the font according to configuration-data with key 'font-$name'
	 // family, style, size and lineHeight can be configured
	 // if no $name given or no configuration for $name can be found, 'std' is used
	 // returns name of font used before	 
	 function setFont($name='std')
	 {
		 $of = $this->fontName;
		 $fontDat = $this->conf['font-'.$name];
		 if (empty($fontDat)) $fontDat = $this->conf['font-std'];
		 $this->pdf->SetFont($fontDat['family'], $fontDat['style'], $fontDat['size']);
		 $lh = $fontDat['lineHeight'];
		 $this->lineHeight = max($lh, $fontDat['size']*PX2MM);
		 $this->fontSize = $fontDat['size'];
		 $this->fontName = $name;
		 return  $of;
	 }
	 
	 // set the column-layout according to configuration-data with key 'cols-$name'
	 // firstStart, firstEnd, secStart and secEnd can be configured
	 // if no $name given or no configuration for $name can be found, 'std' is used
	 // returns name of column-layout used before	
	 function setCols($name='std')
	 {
		 $oc = $this->colsName;
		 $colDat = $this->conf['cols-'.$name];
		 if (empty($colDat)) $colDat = $this->conf['cols-std'];
		 $this->colFirstStart = ($colDat['firstStart']) ? $colDat['firstStart'] : 0;
		 $this->colSecStart = ($colDat['secStart']) ? $colDat['secStart'] : ($this->innerWidth/2) + 5 ;
		 $this->colFirstEnd = ($colDat['firstEnd']) ? $colDat['firstEnd'] : $this->colSecStart - 10;
		 $this->colSecEnd = ($colDat['secEnd']) ? $colDat['secEnd'] : $this->innerWidth;
		 $this->colsName = $name;
		 return $oc;
	 }
	 
	 // set the colors according to configuration-data with key 'colors-$name'
	 // text, draw and fill -colors can be configured, each either by gray-value or RGB
	 // if no $name given or no configuration for $name can be found, 'std' is used
	 // returns name of color-setting used before	
	 function setColors($name='std')
	 {
		 $oc = $this->colorName;
		 $colDat = $this->conf['colors-'.$name];
		 if (empty($colDat)) $colDat = $this->conf['colors-std'];
		 // text-color
		 $r = ($colDat['textR']) ? $colDat['textR']: 0;
		 $g = ($colDat['textG']) ? $colDat['textG']: 0;
		 $b = ($colDat['textB']) ? $colDat['textB']: 0;
		 if ($r == 0 && $g == 0 && $b == 0) {
			 $g = ($colDat['textGray']) ? $colDat['textGray']: 0;
			 $this->pdf->SetTextColor($g);
		 } else {
			 $this->pdf->SetTextColor($r, $g, $b);
		 }
		 // draw-color
		 $r = ($colDat['drawR']) ? $colDat['drawR']: 0;
		 $g = ($colDat['drawG']) ? $colDat['drawG']: 0;
		 $b = ($colDat['drawB']) ? $colDat['drawB']: 0;
		 if ($r == 0 && $g == 0 && $b == 0) {
			 $g = ($colDat['drawGray']) ? $colDat['drawGray']: 0;
			 $this->pdf->SetDrawColor($g);
		 } else {
			 $this->pdf->SetDrawColor($r, $g, $b);
		 }
		 // fill-color
		 $r = ($colDat['fillR']) ? $colDat['fillR']: 0;
		 $g = ($colDat['fillG']) ? $colDat['fillG']: 0;
		 $b = ($colDat['fillB']) ? $colDat['fillB']: 0;
		 if ($r == 0 && $g == 0 && $b == 0) {
			 $g = ($colDat['fillGray']) ? $colDat['fillGray']: 255;
			 $this->pdf->SetFillColor($g);
		 } else {
			 $this->pdf->SetFillColor($r, $g, $b);
		 }
		 $this->colorName = $name;
		 return $oc;
	 }
	 
	 // print a header with text $txt according to configuration 'header-$name'
	 // spaceBefore, spaceAfter and fontName can be configured as well as
	 // cellWidth, cellHeight, cellAlign, textAlign, colorName, cellFill, cellBorder
	 function printHeader($txt,$name='std')
	 {
		 $dat = $this->conf['header-'.$name];
		 if (empty($dat)) $dat = $this->conf['header-std'];
		 $w = ($dat['cellWidth']) ? $dat['cellWidth'] : 0;
		 $h = ($dat['cellHeight']) ? $dat['cellHeight'] : 0;
		 $ca = ($dat['cellAlign']) ? $dat['cellAlign'] : 'L';
		 $ta = ($dat['textAlign']) ? $dat['textAlign'] : 'L';
		 $col = ($dat['colorsName']) ? $dat['colorsName'] : false;
		 $fill = ($dat['cellFill']) ? $dat['cellFill'] : 0;
		 $border = ($dat['cellBorder']) ? $dat['cellBorder'] : 0;
		 if ($dat['upperCase']) $txt = $this->toUpper($txt);
		 $oldFont = $this->fontName;
		 $oldColor = $this->colorName;
		 if ($this->getY() != 0) $this->mvY($dat['spaceBefore']);
		 $this->setFont($dat['fontName']);
		 if ($col) $this->setColors($col);
		 $this->printCell($txt, $w, $h, $ta, $ca, $border, $fill);
		 $this->setFont($oldFont);
		 if ($col) $this->setColors($oldColor);
		 $this->mvY($dat['spaceAfter']);
		 return 1;
	 }
	 
	 // print an entry of a column, prevents page-breaks within entries and between header and first entry
	 // $dat - array with data
	 // $callback - callback (array('classname', 'methodname')) wich will be called to print column-content
	 // $callbackParams - (array) callback will be called with each entry of $dat (if $dat is array of arrays or with $dat otherwise) as first argument and $callbackParams as additional arguemnts
	 // $headerTxt - text for header to print above all entries, 0 if not header should be printed
	 // $headerStyle - header-conf to use for header
	 function printColEntries($dat, $callback, $callbackParams=0, $headerTxt=false, $headerStyle='std')
	 {
		 if (!is_array($dat)) return 0;
		 $printed = 0;
		 reset($dat);
		 if (count($dat)>0) {
			 if ($headerTxt && $headerStyle) $first = true;
			 if (!is_array(current($dat))) {
				 $this->startBlock();
				 do {
					 if ($headerTxt && $headerStyle) $this->printHeader($headerTxt, $headerStyle);
					 if (!is_array($callbackParams)) $callbackParams = array(); 
					 array_unshift($callbackParams, $dat);
					 call_user_func_array($callback,$callbackParams);
				 } while($this->endBlock());
				 $printed = 1;
			 } else {
				 foreach($dat as $row) {

					 $this->startBlock();
					 do {
						 if ($first) {
							 if ($headerTxt && $headerStyle) $this->printHeader($headerTxt, $headerStyle);
						 } else {
							 $this->mvY($this->conf['spaceBig']);
						 }
						 if (!is_array($callbackParams)) $callbackParams = array();
						 array_unshift($callbackParams, $row);
						 call_user_func_array($callback,$callbackParams);
						 $printed = 1;
					 } while($this->endBlock());
					 if ($first) $first = false;
				 }
			 }
		 }
		 return $printed;
	 }
	 
	 // same as printColEntries, but calls $preCallback with arguments $preCallbackParams before creation of entries
	 // and prevents page-break between output of $preCallback and first entry
	 function printColEntriesWithPrefix($dat, $colCallback, $colCallbackParams, $headerTxt=false, $headerStyle,  $preCallback, $preCallbackParams=0)
	 {
		 $more = true;
		 $dat2 = $dat;
		 $printed = 0;
		 $this->startBlock();
		 do {
			 $dat = $dat2;
			 if (!is_array($preCallbackParams)) $preCallbackParams = array();
			 call_user_func_array($preCallback,$preCallbackParams);
			 if (is_array($dat)) {
					$firstDat = array_shift($dat);
					if (!is_array($firstDat)) {
						$firstDat = $dat;
						$more = false;
					} else {
						$firstDat = array($firstDat);
					}
					$printed = $this->printColEntries($firstDat, $colCallback, $colCallbackParams, $headerTxt, $headerStyle);
				} else {
					$more = false;
				}
		 } while($this->endBlock());
		 if ($more) {
			 $printed = $this->printColEntries($dat, $colCallback, $colCallbackParams);
		 }
		 return $printed;
	 }
	 
	 // shorthand for printColEntriesWithPrefix for an additional (big) header before the entries
	 function printColEntriesWithHeader($dat, $colCallback, $colCallbackParams, $headerTxt, $headerStyle, $preHeaderTxt, $preHeaderStyle='std')
	 {
		 return $this->printColEntriesWithPrefix($dat, $colCallback, $colCallbackParams, $headerTxt, $headerStyle, array(&$this, 'printHeader'), array($preHeaderTxt, $preHeaderStyle));
	 }
	 
	 // draws a line, length and width of which are read from configuration with key 'line-$name'
	 // start, end, width, spaceBefore, spaceAfter can be configured
	 function drawLine($name='std')
	 {
		 $lineDat = $this->conf['line-'.$name];
		 if (empty($lineDat)) $lineDat = $this->conf['line-std'];
		 $start = ($lineDat['start']) ? $lineDat['start'] : 0;
		 $end  = ($lineDat['end']) ? $lineDat['end'] : $this->innerWidth;
		 $w = ($lineDat['width']) ? $lineDat['width'] : 0.2;
		 $lw = $this->lineWidth;
		 if ($lineDat['spaceBefore']) $this->mvY($lineDat['spaceBefore']);
		 $this->pdf->SetLineWidth($w);
		 $this->drawLineXY($start, $end);
		 $this->pdf->SetLineWidth($lw);
		 if ($lineDat['spaceAfter']) $this->mvY($lineDat['spaceAfter']);
	 }
	 
	 // draws a horizontal line, $start and $end are relative to inner-area (without page-margins)
	 function drawLineXY($start, $end)
	 {
		 $start += $this->conf['marginLeft'];
		 $end += $this->conf['marginLeft'];
		 $y = $this->pdf->GetY();
		 $this->pdf->Line($start, $y, $end, $y);
	 }
	 
	 // outputs an image, coordinates are relatiev to inner-area
	 // if $x or $y are negative, they are measured from the right or bottom margin 
	 function drawImage($file, $x, $y, $w, $h=0)
	 {
		 if (empty($file)) return;
		 $x = ($x<0) ? ($this->pageWidth - $this->conf['marginRight'] + $x) : ($this->conf['marginLeft'] + $x);
		 $y = ($y<0) ? ($this->pageHeight - $this->conf['marginBottom'] + $y) : ($this->conf['marginTop'] + $y);
		 if ($h>0) {
		 	$this->pdf->Image($file,$x, $y, $w, $h);
		 } else {
		 	$this->pdf->Image($file, $x, $y, $w);
		 }
	 }
	 
	 // set line width used for borders etc.
	 function setLineWidth($width=0.2)
	 {
		 $ol = $this->lineWidth;
		 $this->lineWidth = $width;
		 $this->pdf->SetLineWidth($width);
		 return $ol;
	 }
	 
	 // move current position by $x horizontally
	 function mvX($x)
	 {
		 $this->pdf->SetX($this->pdf->GetX() + $x);
	 }
	 
	 // move current position by $y vertically
	 function mvY($y)
	 {
		 $this->pdf->SetY($this->pdf->GetY() + $y);
	 }
	 
	 // set current position to $x, $x is measured from inner area
	 // if $x is negative it is measured from the right margin
	 function setX($x)
	 {
		 if ($x>=0) {
			 $this->pdf->SetX($this->conf['marginLeft'] + $x);
		 } else {
			 $this->pdf->SetX($this->pageWidth - $this->conf['marginRight'] + $x);
		 }
	 }
	 
	 // set current position to $y (analog to setX())
	 function setY($y)
	 {
		 if ($y>=0) {
			 $this->pdf->SetY($this->conf['marginTop'] + $y);
		 } else {
			 $this->pdf->SetY($this->pageHeight - $this->conf['marginBottom'] + $y);
		 }
	 }
	 
	 // return current x-position relatie to inner-Area
	 // if $fromRight is 1, value is measured from right (negative)
	 function getX($fromRight=0)
	 { 
		 $x = $this->pdf->getX() - $this->conf['marginLeft'];
		 return ($fromRigt) ? $x - $this->innerWidth  : $x; 
	 }
	 
	 // return current y-position (analog to getX())
	 function getY($fromBottom=0)
	 {
		 $y = $this->pdf->getY() - $this->conf['marginTop'];
		 return ($fromBottom) ? $y - $this->innerHeight : $y;
	 }
	 
	 // return string-width of $txt
	 function stringWidth($txt)
	 {
		 $strArr = explode("\n",$txt);
		 $lenArr = array();
		 foreach ($strArr as $str) {
			 $lenArr[] = $this->pdf->GetStringWidth($str);
		 }
		 return max($lenArr);
	 }
	 
	 // print a cell
	 // $txt - text to print
	 // $w - width of cell, if 0 then cell goes until end of line
	 // $h - height of cell, if 0 then current line-height is used
	 // $talign - alignment of text in cell (L|R|C)
	 // $calign - alignment of cell on page (L|R|C|N)
	 // $border - flag, whether border is to be drawn around cell (0|1|combination of R,T,L,B)
	 // $fill - flag, whether cell should be filled with fill-color
	 function printCell($txt, $w=0, $h=0, $talign='L', $calign='N' , $border='', $fill=0)
	 {
		 if ($w < $this->stringWidth($txt)) {
				 $width = $this->stringWidth($txt) + 2.5;
		 } else {
			 $width = $w;
		 }
		 $height = max($this->lineHeight, $h);
		 if ($calign == 'R') { //align cell to right of page
			 $this->setX($this->innerWidth - $width);
		 }
		 if ($calign == 'C') { // align cell to center of page
			 $this->setX(($this->innerWidth - $width)/2);
		 }
		 if ($calign == 'L') { // align cell to left of page
			 $this->setX(0);
		 }
		 $this->pdf->MultiCell($width, $height, $txt, $border, $talign, $fill);
	 }
	 
	 // print in column
	 // $txt - text to be printed
	 // $col - column-number (1|2)
	 // $align - alignment of text in column
	 // $y - y-position
	 // $break - if 1 and $col = 1, text will berak at begin of second column, otherwise it will go until end of page
	 function printCol($txt, $col=1, $align='L', $break=true, $y=false)
	 {
		 $txt = Praktika_String::getISOString($txt);
		 if (!($y===false)) {
			 $this->setY($y);
		 }
		 if ($col == 1) {
			 $y = $this->getY();
			 $this->col1FontSize = $this->fontSize;
			 $this->col1LineHeight = $this->lineHeight;
			 $this->setX($this->colFirstStart);
			 $width = $this->colFirstEnd - $this->colFirstStart;
			 $h = $this->lineHeight;
			 $w = ($break) ? $width : 0;
			 if (!$break && ($this->stringWidth($txt) < $width)) $break = true;
		 } else {
			 $this->setX($this->colSecStart);
			 $w = $this->colSecEnd - $this->colSecStart;
			 $h = max($this->lineHeight, $this->col1LineHeight);
		 }
		 $this->pdf->MultiCell($w, $h, $txt, 0, $align);
		 if ($col == 1 && $break) $this->setY($y);
	 }
	 
	 // print text
	 // $txt - text to print
	 function printTxt($txt, $h=0)
	 {
		 $h = max($h, $this->lineHeight);
		 $h = 4;
		 $this->pdf->Write($h, Praktika_String::getISOString($txt));
	 }
	 
	 // newline
	 function ln()
	 {
		 $this->col1FontSize = 0;
		 $this->col1LineHeight = 0;
		 $this->pdf->Ln();
	 }
	 
	 
	 // return the textual name for $this->langID
	 function langName()
	 {
		 return $this->langMap[$this->langID];
	 }
	 
	 // return language-specific string for key
	 // if there is no entry in the language string-tabel with given key, key is returned
	 function i18n($key)
	 {
		 $v = Praktika_String::getISOString($this->i18nDat[$key]);
		 return (empty($v)) ? $key : $v;
	 }
	 
	 
	 // *** the following functions fetch data from the db and usually return assoc-arrays with data ***
	 
	 // return name of country with $id in current language
	 function getCountryName($id)
	 {
		 $query="SELECT ". $this->langName() ." FROM land WHERE id=". $id.";";
		 $this->db->query($query);
		 return $this->db->get(0,$this->langName());
	 }
	 
	 function getNationality($id)
	 {
		 $query="SELECT nationality_". $this->langName() ." FROM land WHERE id=". $id.";";
		 $this->db->query($query);
		 return $this->db->get(0,"nationality_".$this->langName());
	 }
	 
	 // keys: landID, land, titel, vname, name, strasse, plz, ort, tel, email, tag, monat, jahr, gebort, famstand, datum
	 function getPersonalData()
	 {
		 $query = "SELECT *,date_format(geburtsdatum, '%d') AS tag, date_format(geburtsdatum, '%m') AS monat, date_format(geburtsdatum, '%Y') AS jahr, date_format(modify, '%d.%m.%Y') AS datum FROM nutzer WHERE id=". $this->userID.";";
		 $this->db->query($query);
		 $dat =  $this->db->fetch();
		 $dat['landID'] = $dat['land'];
		 $dat['land'] = $this->getCountryName($dat['landID']);
		 $dat['nationality'] = $this->getNationality($dat['landID']);
		 return $dat;
	 }
	 
	 // array, each entry has keys: mbis, jbis, mvon, jvon, ort, schule, land, abschluss, ergebnis
	 function getSchoolData()
	 {
		 $query="SELECT schularray FROM prakt2_bprofil.profil_schule WHERE nutzerid=". $this->userID ." AND sprachid=". $this->langID .";";
		 if($this->db->query($query)) {
		 	if($this->db->get(0,'schularray') != "")
		 	$dat = wddx_deserialize(($this->db->get(0,'schularray')));
		 	# $dat = htmlspecialchars_array($dat);
		 }
		 if (!is_array($dat)) $dat = array();
		 return previewlebenslauf($dat, $this->langID, "jvon", "schule");
	 }
	 
	 // array, each entry has keys: mbis, jbis, mvon, jvon, beruf, bildungsmassnahme, ausbort,land, b_abschluss, b_werdegang
	 function getProfEducationData()
	 {
		 $query="SELECT berufsarray FROM prakt2_bprofil.profil_berufsausbildung WHERE nutzerid=". $this->userID ." AND sprachid=". $this->langID .";";
		 if($this->db->query($query)) {
		 	if($this->db->get(0,'berufsarray') != "")
		 	$dat = wddx_deserialize(($this->db->get(0,'berufsarray')));
		 	# $dat = htmlspecialchars_array($dat);
		 }
		 if (!is_array($dat)) $dat = array();
		 return previewlebenslauf($dat, $this->langID, "jvon", "beruf");
	 }
	 
	 // array, each entry has keys: mbis, jbis, mvon, jvon, studiengang, hochschule, diplom, vordiplom, abschlussnote, sschwerpunkt, degree
	 function getAcadEducationData()
	 {
		 $query="SELECT studiumarray FROM prakt2_bprofil.profil_studium WHERE nutzerid=". $this->userID ." AND sprachid=". $this->langID .";";
		 if($this->db->query($query)) {
		 	if($this->db->get(0,'studiumarray') != "")
		 	$dat = wddx_deserialize(($this->db->get(0,'studiumarray')));
		 	# $dat = htmlspecialchars_array($dat);
		 }
		 if (!is_array($dat)) $dat = array();
		 return previewlebenslauf($dat, $this->langID, "jvon", "studiengang");
	 }
	 
	 // array, each entry has keys: mbis, jbis, mvon, jvon, unternehmen, ort, land, artdertaettigkeit, taetigkeit
	 function getPraktikaData()
	 {
		 $query = "SELECT praktikumarray FROM prakt2_bprofil.profil_praktika WHERE nutzerid=".$this->userID. " AND sprachid=". $this->langID.";";
		 if($this->db->query($query)) {
		 	if($this->db->get(0,'praktikumarray') != "")
		 	$dat = wddx_deserialize(($this->db->get(0,'praktikumarray')));
		 	#$dat = htmlspecialchars_array($dat);
		 }
		 if (!is_array($dat)) $dat = array();
		 return previewlebenslauf($dat, $this->langID, "jvon", "unternehmen");
	 }
	 
	 // array, each entry has keys: mbis, jbis, mvon, jvon, unternehmen, ort, land, beruf, taetigkeit
	 function getProfessionalData()
	 {
		 $query = "SELECT berufserfahrungsarray FROM prakt2_bprofil.profil_beruferfahrung WHERE nutzerid=". $this->userID ." AND sprachid=". $this->langID .";";
		 if($this->db->query($query)) {
		 	if($this->db->get(0,'berufserfahrungsarray') != "")
		 	$dat = wddx_deserialize(($this->db->get(0,'berufserfahrungsarray')));
		 	#$dat = htmlspecialchars_array($dat);
		 }
		 if (!is_array($dat)) $dat = array();
		 for ($i=0;$i<count($dat);$i++) {
			 if ($dat[$i]['land']) {
					 $dat[$i]['land'] = $this->getCountryName($dat[$i]['land']);
			 }
		 }
		 return previewlebenslauf($dat, $this->langID, "jvon", "unternehmen");
	 }
	 
	 // keys: beschreibungen
	 function getKnowledgeData()
	 {
		 $query = "SELECT * FROM prakt2_bprofil.profil_leistung WHERE nutzerid=". $this->userID ." AND sprachid=". $this->langID .";";
		 $this->db->query($query);
		 return $this->db->fetch();
	 }
	 
	 // keys: hobby
	 function getHobbyData()
	 {
		 $query = "SELECT hobby FROM prakt2_bprofil.profil_hobby WHERE nutzerid=". $this->userID ." and sprachid=" .$this->langID .";";
		 $this->db->query($query);
		 $result = $this->db->fetch();
		 //print_r($result);
		 if (!empty($result[0])) return $result;
		 else return null;
		 
	 }
	 
	 // array, entries 0..2 with keys language and level, entry 3 with key more
	 function getLanguageData()
	 {
		 $knowDat = $this->getKnowledgeData();
		 $langDat = array();
		 $langName = $this->langName();
		 for ($num=1;$num<=3;$num++) {
			 if (($lang = $knowDat['sprachkenntnisse'.$num]) != 0) {
				 $dat = array();
				 $query = "SELECT ". $langName ." FROM prakt2.sprachen WHERE id=". $lang .";";
				 $this->db->query($query);
				 $row = $this->db->fetch();
				 $dat['language'] = $row[$langName];
				 $lvl = $knowDat['sprachbegabung'.$num];
				 $dat['level'] = ($lvl>0) ? $this->i18n('langLevel'.$lvl) : "";
				 $langDat[] = $dat;
			 }
		 }
		 if ($knowDat["weiteresprachen"]) {
		 	$langDat[3] = array('more' => $knowDat['weiteresprachen']);
		 }
		 return $langDat;
	 }
	 
	 // array, each entry has keys: ansprechpartner, ort, tel
	 function getReferencesData()
	 {
		 $query = "SELECT referenzarray FROM prakt2_bprofil.profil_referenzen WHERE nutzerid=". $this->userID ." AND sprachid=". $this->langID .";";
		 if($this->db->query($query)) {
		 	if($this->db->get(0,'referenzarray') != "")
			$dat = wddx_deserialize(($this->db->get(0,'referenzarray')));
		 	#$dat = htmlspecialchars_array($dat);
		 }
		 if (!is_array($dat)) $dat = array();
		 return previewlebenslauf($dat, $this->langID, "ansprechpartner", "ansprechpartner");
	 }
	 
	 // return path to applicants-image
	 function getImagePath()
	 {
    $query="SELECT url,height,width FROM bewerbungsfoto WHERE nutzerid=". $this->userID .";";
    $this->db->query($query);
    $eintraege = $this->db->num_rows();
    if ($eintraege > 0) {
			  $dat = $this->db->fetch();
        $photo = "/home/praktika/public_html". $dat["url"];
        $width = $dat["width"];
        $height = $dat["height"];
				if (!file_exists($photo)) return "";
				$type = strtolower(end(explode('.',$photo)));
				if ($type != 'jpg' && $type != 'jpeg' && $type != 'png') {
					$new = '/home/praktika/public_html/webtemp/' . time() . '_' . rand(0,100) . '.jpg';
					exec("/usr/bin/convert $photo $new");
					if (!file_exists($new)) die("unable to convert image file");
					$photo = $new;
					$this->temp_pic = $photo;
				}
        /*if ($width>0) {
            $CWidth = 150;
            $picture_src = $url;
            $pic=explode(".",$picture_src);
						$thumb_image = "/webtemp/".time().".".end($pic);
						$thumb_dest = "/home/praktika/public_html".$thumb_image;
            $picture_src = "".$picture_src;
            exec("/ur/local/bin/convert -geometry $CWidth -colors 256 -colorspace yuv $picture_src $thumb_dest");
            $photo=$picture_src;
        }*/
    }else{
	   $photo="";
    }
    return $photo; 
	 }
	 
	 // return height of applicants image according to image-file and photoWidth configuration-setting
	 // $path - path to image, if not given, $this->getIamgePath() will be called
	 function getImageHeight($path=false)
	 {
		 if (!$path) $path = $this->getImagePath();
		 $idat = getimagesize($path);
		 $width = ($this->conf['photoWidth']) ? $this->conf['photoWidth'] : $idat[0] * PX2MM;
		 $height = ($idat[0]!=0) ? ($idat[1]/$idat[0]) * $width : 0;
		 return $height;
	 }
	 
	 // return period-string according to data in $row and $this->langID
	 function getDateStr($row)
	 {
		 $d = '/';// ($this->langName() == 'german') ? '.' : '/';
		 $ausgabe = sprintf("%02.0f",$row['mvon']) . $d . $row['jvon'];
     if ($row['mvon']) {
			 $ausgabe .= " - ";
			 $ausgabe .= (!$row['mbis']) ? $this->i18n('present') : sprintf("%02.0f",$row['mbis']) . $d . $row['jbis'];
     }
     return $ausgabe;
	 }
	 
	 // convert $txt to upper-case, including umlaute which are not converted by strtoupper because of wrong locale
	 function toUpper($txt)
	 {
		 $txt = strtoupper(Praktika_String::getUtf8String($txt));
		 return Praktika_String::getISOString(str_replace(array('ä','ü','ö'), array('Ä','Ü','Ö'), $txt));
	 }
	 
	 // return date-string according to current language
	 function getBirthDate($dat)
	 {
		 if ($this->langName() == 'german') {
			 $date = $dat['tag'] . "." . $dat['monat'] . "." . $dat['jahr'];
		 } else {
			 $date = $dat['monat'] . "/" . $dat['tag'] . "/" . $dat['jahr'];
		 }
		 return $date;
	 }
	 
	 /* startBlock and endBlock need to be called in conjunction
	 	  you have to use them like this:
		
			 $this->startBlock();
			 do {
				 ...
			 } while($this->endBlock());
	 
	 		everything between will be printed on one page (if it fits one page),
			so there will not be a page break in between
			if more of these blocks are nested, the outward block takes precedence
	 */
	 function startBlock()
	 {
		 if ($this->_blockNum == 0) $this->_pdfBak = $this->pdf; //backup fpdf-object
		 $this->_blockPage = $this->pdf->PageNo(); //memorize current page-no
		 $this->_blockNum++;
		 $this->_blockArr[$this->_blockNum] = true;
	 }
	 
	 function endBlock()
	 {
		 if ($this->_blockArr[$this->_blockNum]) {
			 $this->_blockArr[$this->_blockNum] = false;
			 $this->_blockNum--;
			 if ($this->_blockNum == 0) {
				 if ($this->pdf->PageNo() > $this->_blockPage) { //page-break occured
					 //$this->pdf = $this->_pdfBak;
					 //$this->pdf->AddPage();
					 //return 1; //repeat everything between startBlock();do { ... } while(endBlock();
				 } else { // no page-break
					 $this->pdfBak = 0;
				 }
			 }
		 }
		 return 0; // no repeat
	 }
	 
	function AcceptPageBreakTest() 
	{ 
		if($this->col<2) 
		{
			//Go to next column 
			$this->SetCol($this->col+1);
			$this->SetY(10);
			return false; 
		} 
		else 
		{ 
			//Go back to first column and issue page break 
			$this->SetCol(0); 
			return true; 
		} 
	} 


	 /* private methods */
	 // do the actual creation of the pdf
	 function _create()
	 {
		 // abstract method
	 }
	 
	 // read in the configuration
	 function _readConf($confFile)
	 {
		  $cvConf = array();
		 	include($this->baseConfFile);
			# echo getcwd();
			# var_dump($confFile);
			# set_include_path(SERVER_ROOT."/public_html/home/praktikanten/profil/lebenslauf_pdf/".PATH_SEPARATOR.get_include_path());
			# var_dump(get_include_path());
			# $confFile = SERVER_ROOT."/public_html/home/praktikanten/profil/lebenslauf_pdf/".$confFile;
			
			if (file_exists($confFile)) include($confFile);
			else die("conf-file $confFile not found");
			$this->conf = $cvConf;
	 }
	 
	 // read in the language-specific string-table
	 function _readI18nDat()
	 {
		 $i18n = array();
		 $file = $this->i18nFilePrefix . $this->langName() . '.inc.php';
		 if (file_exists($file))	 include($file);
		 else die("lang-file $file not found");
		 $this->i18nDat = $i18n;
	 }
 }
 

 
?>
