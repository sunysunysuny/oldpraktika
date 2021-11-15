<?php
require_once('lebenslauf_b2.php');

class CV_U2 extends CV_B2
{
	// all functions override same functions in parent class (defined in CV_G1)


	function printPersonalData()
	{
		$dat = $this->getPersonalData();
		$txt = trim($dat['titel'] . " " . $dat['vname'] . " " .$dat['name']);
		$this->printHeader($txt,'cv');
		$txt = $dat['strasse'] .  "\n" . $dat['plz'] . " " . $dat['ort'];
		$y = $this->getY();
		$this->printCell($txt);
		$this->ln();
		$this->setY($y);
		
		$txt = $this->getBirthDate($dat) .  "\n" .$this->i18n('phone') . ": " . $dat['tel'] .  "\n" . $this->i18n('email') . ": " . $dat['email'];
		$this->printCell($txt, 0,0,'R','R');
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
	
	function cbHobby($dat)
	{
	 $this->printCell($dat['hobby']);
	}
	
	function cbReferences($row)
	{
	  $txt = $row['ansprechpartner'] . ", " . $row['ort'] . ", " . $this->i18n('phone') . ": " . $row['tel'];
		$this->printCell($txt);
	}
		
}
?>
