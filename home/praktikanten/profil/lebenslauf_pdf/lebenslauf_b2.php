<?php
	require_once('lebenslauf_g1.php');
	
class CV_B2 extends CV_G1
{
	function printLine()
	{
		if ($this->conf['linesBetween']) {
			$this->drawLine($this->conf['linesBetween']);
		}
	}
	
	// all following functions override same functions in parent class (defined in CV_G1)
	
	function printPersonalData()
	{
		$dat = $this->getPersonalData();
		$this->setCols('personal');
		$this->printHeader($this->i18n('personalData'),'big');
		$y = $this->getY();
		$txt = trim($dat['titel'] . " " . $dat['vname'] . " " .$dat['name']);
		$this->printCol($txt, 1);
		$txt = $dat['strasse'] .  "\n" . $dat['plz'] . " " . $dat['ort']  .  "\n";
		$txt .= $this->i18n('phone') . ": " . $dat['tel'] .  "\n" . $this->i18n('email') . ": " . $dat['email'];
		$this->printCol($txt, 2);
		$this->mvY($this->conf['spaceBig']);
		$txt = $this->getBirthDate($dat) .  "\n" . $dat['land'];
		$this->printCol($txt, 1);
		$this->ln();	
		
		$pw = $this->conf['photoWidth'];
		$y2 =$this->getY();
		$this->setY($y);
		$pp = $this->getImagePath();
		$this->drawImage($pp, 0-$pw, $y, $pw);
		$this->setY(max($y+$this->getImageHeight($pp),$y2));
		$this->setCols();
	}
	
	function cbSchool($row)
	{ 
		$this->printDate($row);
		$txt = $row['schule'] . " " . $row['ort'] . ", " . $row['land'];
		$this->printCol($txt, 2);
		if ($row['abschluss']) {
			$this->printCol($row['abschluss']." (". $this->i18n('withResult') . " " . $row[ergebnis].")", 2);
		}
	}
	 
	function cbLanguage($x,$dat)
	{	
		if ($dat[0] && $x == $dat[0]) {
			$this->printCol($this->i18n('languageSkills') . ":",1,'R');
			foreach ($dat as $row) {
				if ($row['language']) $txt .= $row['language'] . " - " . $row['level'] . "\n";
				if (isset($row['more']) && !empty($row['more'])) $more .= $row['more'] . " ";
			}
			if ($txt) $this->printCol($txt, 2);
			if ($more) {
				$this->mvY($this->conf['spaceSmall']);
				$this->printCol($more, 2);
			}
		}
	}
	
	function cbKnowledge($dat)
	{
		$this->printCol($this->i18n('skills') . ":",1,'R');
		$this->printCol($dat['beschreibungen'], 2);
	}
	
	function _create()
	{
		$p = 0;
		if ($this->conf['printCVHeader']) {
			$key = ($this->conf['headerLangKeys']['cv']) ? $this->conf['headerLangKeys']['cv'] : 'cv';
			$this->printHeader($this->i18n($key),'cv');
		}
		$this->printPersonalData();
		$this->printLine();
		 
		$key = $key = ($this->conf['headerLangKeys']['education']) ? $this->conf['headerLangKeys']['education'] : 'education';
		$dat = $this->getAcadEducationData();
		$p = $this->printColEntries($dat, array(&$this, 'cbAcadEdu'), 0, $this->i18n($key));
		$p += $this->printProfEducationData(0);
		$p += $this->printSchoolData(0);
		if ($p>0) $this->printLine();
		
		$p = 0;
		$key = $key = ($this->conf['headerLangKeys']['experience']) ? $this->conf['headerLangKeys']['experience'] : 'experience';
		$dat = $this->getProfessionalData();
		if (count($dat)>0) {
			$p += $this->printColEntries($dat, array(&$this, 'cbProfessional'), 0, $this->i18n($key));
			$p += $this->printPraktikaData(0);
		} else {
			$dat = $this->getPraktikaData();
			$p += $this->printColEntries($dat, array(&$this, 'cbPraktika'), 0, $this->i18n($key));
		}
		if ($p>0) $this->printLine();
		 
		$p = 0;
		$key = $key = ($this->conf['headerLangKeys']['skills']) ? $this->conf['headerLangKeys']['skills'] : 'skills';
		$dat = $this->getKnowledgeData();
		if (count($dat)>0) {
			if (!empty($dat['beschreibungen'])) $p += $this->printColEntries($dat, array(&$this, 'cbKnowledge'), 0, $this->i18n($key));
			$p += $this->printLanguageData(0);			
		} else {
			$dat = $this->getLanguageData();
			$p += $this->printColEntries($dat, array(&$this, 'cbLanguage'), 0, $this->i18n($key));
		}
		if ($p>0) $this->printLine();
		 
		$p = $this->printHobbyData();
		if ($p>0) $this->printLine();
		 
		$p = $this->printReferencesData();
		if ($p>0) $this->printLine();
	}
}
?>
