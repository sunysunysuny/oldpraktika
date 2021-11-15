<?php
	require_once('lebenslauf_g1.php');
	
	class CV_G3 extends CV_G1
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
				 $this->printCol($row['abschluss']. " " . $this->i18n('withResult') . " " . $row[ergebnis], 2);
			 }
		 }
			
	 function _create()
	 {
		 $this->printPersonalData();
		 $this->setCols();
		 $this->printSchoolData();
		 $this->printProfEducationData();
		 $this->printAcadEducationData();
		 $this->printPraktikaData();
		 $this->printProfessionalData();
		 $this->printKnowledgeData();
		 $this->printLanguageData();
		 $this->printHobbyData();
		 $this->printReferencesData();
		}		
	}
	 
?>
