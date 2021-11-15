<?php
require_once('lebenslauf_g1.php');
	
class CV_G2 extends CV_G1
{		
	// all functions override same functions in parent class (defined in CV_G1)
		 
	function printPersonalData()
	{
		$dat = $this->getPersonalData();
		$this->setCols('personal');
		$this->printHeader($this->i18n('personalData'),'big');
		$y = $this->getY();
		$txt = trim($dat['titel'] . " " . $dat['vname'] . " " .$dat['name']);
		$txt .= "\n" . $this->i18n('birthDate') . ": " . $this->getBirthDate($dat) . "\n" . ($dat['famstand'] != '-1' ? $dat['famstand'] : '');
		$this->printCol($txt, 2);
		$this->mvY($this->conf['spaceBig']);
		$txt = $dat['strasse'] .  "\n" . $dat['plz'] . " " . $dat['ort'] .  "\n" . $dat['land'] .  "\n";
		if ($dat['tel']) {
	 	$txt .= $this->i18n('phone') . ": " . $dat['tel'] .  "\n"; }
		$txt .= $this->i18n('email') . ": " . $dat['email'];
		$this->printCol($txt, 2);
		$pw = $this->conf['photoWidth'];
		$y2 =$this->getY();
		$this->setY($y);
		$this->drawImage($this->getImagePath(), 0-$pw, 0, $pw);
		$this->setY($y2);
		$this->setCols();
	}
		
	function cbSchool($row)
	{ 
		$this->printDate($row);
		$txt = $row['schule'] . " " . $row['ort'] . ", " . $row['land'];
		$this->printCol($txt, 2);
		if ($row['abschluss']) {
			$this->printCol($row['abschluss']." (".$row[ergebnis].")", 2);
		}
	}
	 
	
	function _create()
	{
		if ($this->conf['printCVHeader']) {
			$key = ($this->conf['headerLangKeys']['cv']) ? $this->conf['headerLangKeys']['cv'] : 'cv'; 
			$this->printHeader($this->i18n($key),'cv');
		}
		$this->printPersonalData();
		$dat = $this->getSchoolData();
		$key = ($this->conf['headerLangKeys']['education']) ? $this->conf['headerLangKeys']['education'] : 'education'; 
		$key2 = ($this->conf['headerLangKeys']['school']) ? $this->conf['headerLangKeys']['school'] : 'schoolEducation';
		//$this->printColEntriesWithHeader($dat, array(&$this, 'cbSchool'),0,$this->i18n($key2),'std',$this->i18n($key),'big');
		if (!empty($dat[0]['schule']) || !empty($dat['abschluss']))$this->printHeader($this->i18n($key),'big');
		if (!empty($dat[0]['schule'])) $this->printColEntries($dat, array(&$this, 'cbSchool'),0,$this->i18n($key2),'std');
		$this->printProfEducationData();
		$this->printAcadEducationData();
		$this->printPraktikaData('big');
		$this->printProfessionalData('big');
		$dat = $this->getKnowledgeData();
		$key = ($this->conf['headerLangKeys']['skills']) ? $this->conf['headerLangKeys']['skills'] : 'skills'; 
		$key2 = ($this->conf['headerLangKeys']['knowledge']) ? $this->conf['headerLangKeys']['knowledge'] : 'knowledge';
		//$this->printColEntriesWithHeader($dat, array(&$this, 'cbKnowledge'),0,$this->i18n($key2),'std',$this->i18n($key),'big');
		$this->printHeader($this->i18n($key),'big');
		if (!empty($dat['beschreibungen'])) $this->printColEntries($dat, array(&$this, 'cbKnowledge'),0,$this->i18n($key2),'std');
		$this->printLanguageData();
		$this->printHobbyData('big');
		$this->printReferencesData('big');
	}
}
?>
