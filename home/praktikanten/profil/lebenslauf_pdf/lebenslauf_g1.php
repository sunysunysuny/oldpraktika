<?php
 require_once('lebenslauf_base.php');

 class CV_G1 extends CVBase
 {
	 
		// helper-function, print the date string according to entries in $row and configuration-settings
		// dateAlign and dateFontName can be configured
	 	function printDate($row)
		{
			$da = ($this->conf['dateAlign']) ? $this->conf['dateAlign'] : 'L';
			$df = ($this->conf['dateFontName']) ? $this->conf['dateFontName'] : 'std';
			$of = $this->setFont($df);
			$this->printCol($this->getDateStr($row),1,$da);
			$this->setFont($of);
		}
		
	 // helper-function, print details according to configuration-settings
	 // spaceSmall, detailDescAlign can be configured
	 // $desc - description
	 // $txt - detail-text
	 function printDetails($desc, $txt)
	 {
		 $txt = convertPDFString(trim($txt));
		 if (! 	empty($txt)) {
			 $this->mvY($this->conf['spaceSmall']);
			 $oc = $this->setCols('detail');
			 $align = ($this->conf['detailDescAlign']) ? $this->conf['detailDescAlign'] : 'L';
			 if ($align == 'L') {
				 $this->printCol($desc, 1,$align,0);
			 } else {
				  $this->printCol($desc, 1,$align);
			 }
			 $this->printCol($txt, 2);
			 $this->setCols($oc);
		 }
	 }
		  
	 function printPersonalData()
	 {
		 $persDat = $this->getPersonalData();
		 $this->setFont('h1');
		 $txt = trim($persDat['titel'] . " " . $persDat['vname'] . " " . $persDat['name']);
		 $this->printTxt($txt);
		 $this->ln();
		 $this->setFont();
		 $txt = trim($persDat['strasse'] . ", " . $persDat['plz'] . " " . $persDat['ort']);

		 if ($persDat['tel']) {
		 $txt .= ", " . $persDat['tel'];}
		 $availWidth = $this->innerWidth - $this->conf['photoWidth'] - $this->conf['photoLeftMargin'];
		 
		 if ($persDat['email']) {
		 
			 $txt2 = $txt . ", " . $persDat['email'];

			 if ($this->stringWidth($txt2) > $availWidth) {
				 $this->printTxt($txt);
				 # var_dump($txt);
				 $this->ln();
				 $this->printTxt($persDat['email']);
			 } else {
				 $this->printTxt($txt2);
			 }
			 $this->ln();
		 }
		 $this->mvY(1);
		 $this->drawLineXY(0, $availWidth);
		 
		 $this->printHeader($this->i18n('personalData'));		 
		 $txt = $this->i18n('bornOn') . " " . $this->getBirthDate($persDat) . " " . $this->i18n('in') . " " . $persDat['gebort'];
		 $this->printCell($txt);
		 
		 if ($this->i18n($persDat['famstand']) != '-1') {
			 $this->printCell($this->i18n($persDat['famstand']));
		 }
		 
		 $this->printCell($persDat['land']);
		 
		 $y = $this->getY();
		 $this->setY(0);
		 $pp = $this->getImagePath();
		 $pw = $this->conf['photoWidth'];
		 $ph = $this->getImageHeight($pp);
		 $this->drawImage($pp, 0-$pw, 0, $pw, $ph);
		 $this->setY(max($ph,$y));
	 }
	 
	 // callback, called by printSchoolData for each entry
	 function cbSchool($row)
	 {
		 $this->printDate($row);
		 $txt = $row['schule'] . " " . $row['ort'] . ", " . $row['land'];
		 $this->printCol($txt, 2);
		 if ($row['abschluss']) {
			 $this->printCol($row['abschluss']." (".$row[ergebnis].")", 2);
		 }
		 
	 }
	 
	 function printSchoolData($header='std')
	 {	
		 $key = ($this->conf['headerLangKeys']['school']) ? $this->conf['headerLangKeys']['school'] : 'schoolEducation';
		 $dat = $this->getSchoolData();
		 return $this->printColEntries($dat, array(&$this,'cbSchool'),0,$this->i18n($key),$header);
	 }
	 	 
	 function cbProfEdu($row)
	 {
		 $this->printDate($row);
		 $txt = $row['beruf'] . ", " . $row['bildungsmassnahme'];
		 $this->printCol($txt, 2);
		 $txt = $row['ausbort'] . ", " . $row['land'];
		 $this->printCol($txt, 2);
		 if ($row['b_abschluss']) {
			 $txt = $this->i18n('result') . ": " . $row['b_abschluss'];
			 $this->printCol($txt, 2);
		 }
		 $key = ($this->conf['detailLangKeys']['profEdu']) ? $this->conf['detailLangKeys']['profEdu'] : 'educationFocus';
		 $this->printDetails($this->i18n($key) . ":", $row['b_werdegang']);
	 }
	 
	 function printProfEducationData($header='std')
	 {
 		 $key = ($this->conf['headerLangKeys']['profEdu']) ? $this->conf['headerLangKeys']['profEdu'] : 'professionalEducation';
		 $dat = $this->getProfEducationData();
		 return $this->printColEntries($dat, array(&$this, 'cbProfEdu'),0,$this->i18n($key),$header);
	 }
	 
	 function cbAcadEdu($row)
	 {		 
		 $this->printDate($row);
		 $txt = $this->i18n('primSubject') . " " . $row['studiengang'] . ", " .$row['hochschule'] . ", " . $row['land'];
		 $this->printCol($txt, 2);
		 if ($this->langID==2) {
			 if ($row['diplom'] =="nein" && $row['vordiplom'] =="ja") {
				 $txt = $this->i18n('prediplomaWith') . " " . $row['abschlussnote'];
				 $this->printCol($txt, 2);
			 }
			 if ($row['diplom'] =="ja") {
				 $txt = $row['degree'] . " " . $this->i18n('with') . " " . $this->i18n('degree') . " " . $row['abschlussnote'];
				 $this->printCol($txt, 2);
			 }
		 }
		 $key = ($this->conf['detailLangKeys']['acadEdu']) ? $this->conf['detailLangKeys']['acadEdu'] : 'studyFocus';
		 $this->printDetails($this->i18n($key) . ":", $row['sschwerpunkte']);
	 }
	 
	 function printAcadEducationData($header='std')
	 {
 		 $key = ($this->conf['headerLangKeys']['acadEdu']) ? $this->conf['headerLangKeys']['acadEdu'] : 'academicEducation';
		 $dat = $this->getAcadEducationData();
		 return $this->printColEntries($dat, array(&$this, 'cbAcadEdu'),0,$this->i18n($key),$header);
	 }
	 
	 function cbPraktika($row)
	 {

		 $this->printDate($row);
		 $of = $this->setFont($this->conf['expFirstFontName']);
		 $txt = $row['unternehmen'] . " - " . $row['ort'] . ", " . $row['land'];
		 $this->printCol($txt, 2);
		 $this->setFont($of);
		 if ($row['artdertaettigkeit'] == 'praktikum') $txt = $this->i18n('internship');
		 if ($row['artdertaettigkeit'] == 'nebentaetigkeit') $txt = $this->i18n('partTimeWork');
		 $this->printCol($txt, 2);
		 $key = ($this->conf['detailLangKeys']['praktika']) ? $this->conf['detailLangKeys']['praktika'] : 'duties';
 		 $this->printDetails($this->i18n($key) . ":", convertPDFString($row['taetigkeit']));
	 }
	 
	 function printPraktikaData($header='std')
	 {
 		 $key = ($this->conf['headerLangKeys']['praktika']) ? $this->conf['headerLangKeys']['praktika'] : 'internships';
		 $dat = $this->getPraktikaData();
		 return $this->printColEntries($dat, array(&$this, 'cbPraktika'),0,$this->i18n($key),$header);
	 }
	 
	 function cbProfessional($row)
	 {
		 $this->printDate($row);
		 $of = $this->setFont($this->conf['expFirstFontName']);
		 $txt = $row['unternehmen'] . " - " . $row['ort'] . ", " . $row['land'];
		 $this->printCol($txt, 2);
		 $this->setFont($of);
		 $this->printCol($row['beruf'], 2);
		 $key = ($this->conf['detailLangKeys']['professional']) ? $this->conf['detailLangKeys']['professional'] : 'jobDescription';
 		 $this->printDetails($this->i18n($key) . ":", convertPDFString($row['taetigkeit']));
	 }
	 
	 function printProfessionalData($header='std')
	 {
 		 $key = ($this->conf['headerLangKeys']['professional']) ? $this->conf['headerLangKeys']['professional'] : 'profExperience';
		 $dat = $this->getProfessionalData();
		 return $this->printColEntries($dat, array(&$this, 'cbProfEssional'),0,$this->i18n($key),$header);
	 }
	 
	 function cbKnowledge($dat)
	 {
		$this->printCol(utf8_decode($dat['beschreibungen']), 2);
	 }
	 
	 function printKnowledgeData($header='std')
	 {
 		 $key = ($this->conf['headerLangKeys']['knowledge']) ? $this->conf['headerLangKeys']['knowledge'] : 'skills';
		 $dat = $this->getKnowledgeData();
		 if (!empty($dat['beschreibungen']))  return $this->printColEntries($dat, array(&$this, 'cbKnowledge'),0,$this->i18n($key),$header);
	 }
	 
/*	 function cbLanguage($x,$dat)
	 {	
		 if ($dat[0] && $x == $dat[0]) {
			 foreach ($dat as $row) {
				 if ($row['language']) $txt .= $row['language'] . " - " . $row['level'] . "\n";
				 if ($row['more']) $more .= $row['more'] . " ";
			 }
			 if ($txt) $this->printCol($txt, 2);
			 if ($more) {
				 $this->mvY($this->conf['spaceSmall']);
				 $this->printCol($more, 2);
			 }
		 }
	 }
*/	 
	 function printLanguageData($header='std')
	 {
 		$key = ($this->conf['headerLangKeys']['language']) ? $this->conf['headerLangKeys']['language'] : 'languageSkills';
		$dat = $this->getLanguageData();
 		//return $this->printColEntries($dat, array(&$this, 'cbLanguage'),array($dat),$this->i18n($key),$header);

		if (count($dat)>0) {		
			$this->startBlock();
			do {
				 $txt = "";
				 $more = "";
				 $this->printHeader($this->i18n($key), $header);
				 foreach ($dat as $row) {
					 if ($row['language']) $txt .= $row['language'] . " - " . $row['level'] . "\n";
					 if ($row['more']) $more .= $row['more'] . " ";
				 }
				 if ($txt) $this->printCol($txt, 2);
				 if ($more) {
					 $this->mvY($this->conf['spaceSmall']);
					 $this->printCol($more, 2);
				 }
			} while($this->endBlock());
		}
	 }
	 
	 function cbHobby($dat)
	 {
		 $this->printCol($dat['hobby'], 2);
	 }
	 
	 function printHobbyData($header='std')
	 {
 		 $key = ($this->conf['headerLangKeys']['hobbies']) ? $this->conf['headerLangKeys']['hobbies'] : 'hobbies';
		 $dat = $this->getHobbyData();
		 return $this->printColEntries($dat, array(&$this, 'cbHobby'),0,$this->i18n($key),$header);
	 }
	 
	 function cbReferences($row)
	 {
		  $txt = $row['ansprechpartner'] . ", " . $row['ort'] . ", " . $this->i18n('phone') . ": " . $row['tel'];
			$this->printCol($txt,2);
	 }
	 
	 function printReferencesData($header='std')
	 {
		 $printed = 0;
 		 $key = ($this->conf['headerLangKeys']['references']) ? $this->conf['headerLangKeys']['references'] : 'references';
		 $this->startBlock();
		 do {
			 $dat = $this->getReferencesData();
			 $printed = $this->printColEntries($dat, array(&$this, 'cbReferences'),0,$this->i18n($key),$header);	 
		 }while($this->endBlock());
		 return $printed;
	 }
	 
	 // main-method
	 function _create()
	 {
		 $this->printPersonalData();
		 $this->setCols();
		 if ($this->langName() == 'german') { 
			 $this->printSchoolData();
			 $this->printProfEducationData();
			 $this->printAcadEducationData();
			 $this->printPraktikaData();
			 $this->printProfessionalData();
		 } else {
			 $this->printHeader($this->i18n('education'));
			 $this->printAcadEducationData(0);
			 $this->printProfEducationData(0);
			 $this->printSchoolData(0);
			 $this->printProfessionalData();
			 $this->printPraktikaData();
		 }
		 $this->printKnowledgeData();
		 $this->printLanguageData();
		 $this->printHobbyData();
		 $this->printReferencesData();
		}
		
 }

?>
