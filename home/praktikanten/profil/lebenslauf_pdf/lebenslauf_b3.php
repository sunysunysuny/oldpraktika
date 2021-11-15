<?php
require_once('lebenslauf_b2.php');
	
class CV_B3 extends CV_B2
{
	// all functions override same functions in parent class (defined in CV_G1)
	 
 	function printPersonalData()
	{
		$dat = $this->getPersonalData();
			
		$txt = trim($dat['titel'] . " " . $dat['vname'] . " " .$dat['name']);
		$this->setFont('h1');
		$this->printCell($txt, 0,0,'C','C');
		$this->ln();
		$this->setFont();
		$this->printCell($dat['strasse'], 0, 0,'C','C');
		$this->printCell($dat['plz'] . " " . $dat['ort'], 0, 0,'C','C');
		$this->printCell($this->i18n('phone') . ": " . $dat['tel'], 0, 0,'C','C');
		$this->printCell($this->i18n('email') . ": " . $dat['email'], 0, 0,'C','C');
		$this->mvY($this->conf['spaceBig']);
		$y = $this->getY();
		$this->printCell($this->i18n('nationality') . ": " . $dat['nationality']);
		$this->setY($y);
		$this->printCell($this->i18n('birthDate') . ": " . $this->getBirthDate($dat), 0, 0,'R','R');
		$this->setCols();
	}

	function cbLanguage($x,$dat)
	{	
		if ($dat[0] && $x == $dat[0]) {
			$this->printCol($this->i18n('languageSkills') . ":",1,'L');
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
		$this->printCol($this->i18n('skills') . ":",1,'L');
		$this->printCol($dat['beschreibungen'], 2);
	}
		
	function cbReferences($row)
	{
		$this->printCol($row['ansprechpartner'],1);
		$txt = $row['ort'] . ", " . $this->i18n('phone') . ": " . $row['tel'];
		$this->printCol($txt,2);
	}
}
?>
