<?php
class valgt_rapport extends rapport {
	/**
	 * class constructor
	 * 
	 * Initierer parent og sier hvilke options som er mulig å velge i rapporten
	 *
	 * @access public
	 * @param rapport navn på rapport
	 * @param kategori navn på sorteringskategori for rapport
	 * @return class object
	 */
	public function __construct($rapport, $kategori){
		parent::__construct($rapport, $kategori);
		
		$this->navn = 'Innslag som turnerer fylket';

	}

	public function generateExcel() {
		UKM_loader('excel');
		global $objPHPExcel;
		$objPHPExcel = new PHPExcel();
		$this->excel_init('landscape');
		exSheetName('KONTAKTP','6dc6c1');

		$rad = $col = 1;

		$looped = array('monstringer'=>array(), 'innslag'=>array());
		$contacts = array();
		$bands = array();
		$monstring = new monstring(get_option('pl_id'));
		$lokalmonstringer = $monstring->hent_lokalmonstringer();
		
		foreach($lokalmonstringer as $lokal) {
			if(in_array($lokal, $looped['monstringer']))
				continue;
			$looped['monstringer'][] = $lokal;
			
			$lm = new monstring( $lokal );
			
			foreach( $lm->innslag() as $b_id => $band ) {
				$innslag = new innslag($band['b_id']);
				
				# HVIS KONTAKTPERSONEN IKKE HAR NOEN INNSLAG, ELLER HVIS KONTAKTPERSONEN IKKE HAR DETTE INNNSLAGET
				if((is_array($contacts[$innslag->g('b_contact')]) && !in_array($band['b_id'], $contacts[$innslag->g('b_contact')])) || !is_array($contacts[$innslag->g('b_contact')]))
					$contacts[$innslag->g('b_contact')][] = $band['b_id'];
					
				$bands[$innslag->g('b_name')][] = $innslag->g('b_id');
			}
		}
		
		arsort($contacts);

		exCell('A1', 'Navn på innslag', 'bold');
		exCell('B1', 'Kontaktperson', 'bold');
		exCell('C1', 'Type innslag', 'bold');
		exCell('D1', 'Kommune', 'bold');
		$row = 1;
		foreach($contacts as $b_contact => $contact_array) {
			if( sizeof($contact_array) <= 1 )
				continue;
			
			$contact = new person($b_contact);
			$first = true;
			$all_same_kommune = true;
			$collect_infos = array();
			foreach( $contact_array as $b_id ) {
				$looped['innslag'][] = $b_id;
				$band = new innslag($b_id);
				if($first)
					$kommune = $band->g('b_kommune');
				elseif($band->g('b_kommune') !== $kommune)
						$all_same_kommune = false;
				$first = false;
				
				$collect_infos[] = array('bname' => $band->g('b_name'),
										'contact' => $contact->g('name'),
										'bt_name' => $band->g('bt_name'),
										'kommune' => utf8_encode(UKMN_kommune($band->g('b_kommune')))
										);
			}
			if(!$all_same_kommune) {
				foreach($collect_infos as $info) {
					$row++;
					exCell('A'.$row, $info['bname']);
					exCell('B'.$row, $info['contact']);
					exCell('C'.$row, $info['bt_name']);
					exCell('D'.$row, $info['kommune']);
				}
			}
		}
		
		
		arsort($bands);
		
		$objPHPExcel->createSheet(1);
		$objPHPExcel->setActiveSheetIndex(1);
		exSheetName('LIKE_NAVN','f3776f');

		$row = 1;
		exCell('A'.$row, 'Navn på innslag', 'bold');
		exCell('B'.$row, 'Kontaktperson', 'bold');
		exCell('C'.$row, 'Type innslag', 'bold');
		exCell('D'.$row, 'Kommune', 'bold');

		foreach( $bands as $b_name => $b_id_array ) {
			if(sizeof($b_id_array) <= 1)
				continue;

			$first = true;
			$all_same_kommune = true;
			$collect_infos = array();
			foreach( $b_id_array as $b_id ) {
				if(in_array($b_id, $looped['innslag']))
					continue;
					
				$band = new innslag($b_id);
				if($first)
					$kommune = $band->g('b_kommune');
				elseif($band->g('b_kommune') !== $kommune)
						$all_same_kommune = false;
				$first = false;
				$contact = new person($band->g('b_contact'));
	
				$collect_infos[] = array('bname' => $band->g('b_name'),
										'contact' => $contact->g('name'),
										'bt_name' => $band->g('bt_name'),
										'kommune' => utf8_encode(UKMN_kommune($band->g('b_kommune')))
										);
			}
			if(!$all_same_kommune) {
				foreach($collect_infos as $info) {
					$row++;
					exCell('A'.$row, $info['bname']);
					exCell('B'.$row, $info['contact']);
					exCell('C'.$row, $info['bt_name']);
					exCell('D'.$row, $info['kommune']);
				}
			}

		}
		return $this->exWrite();
	}
	
	
	public function generateWord() {
		UKM_loader('word');
		global $PHPWord;		
		$section = $this->word_init();


		$looped = array('monstringer'=>array(), 'innslag'=>array());
		$contacts = array();
		$bands = array();
		$monstring = new monstring(get_option('pl_id'));
		$lokalmonstringer = $monstring->hent_lokalmonstringer();
		
		foreach($lokalmonstringer as $lokal) {
			if(in_array($lokal, $looped['monstringer']))
				continue;
			$looped['monstringer'][] = $lokal;
			
			$lm = new monstring( $lokal );
			
			foreach( $lm->innslag() as $b_id => $band ) {
				$innslag = new innslag($band['b_id']);
				
				# HVIS KONTAKTPERSONEN IKKE HAR NOEN INNSLAG, ELLER HVIS KONTAKTPERSONEN IKKE HAR DETTE INNNSLAGET
				if((is_array($contacts[$innslag->g('b_contact')]) && !in_array($band['b_id'], $contacts[$innslag->g('b_contact')])) || !is_array($contacts[$innslag->g('b_contact')]))
					$contacts[$innslag->g('b_contact')][] = $band['b_id'];
					
				$bands[$innslag->g('b_name')][] = $innslag->g('b_id');
			}
		}
		
		arsort($contacts);

		woText($section, 'Kontaktpersoner som har flere innslag...', 'grp');
		woText($section, '...og hvor minst to av innslagene deltar i forskjellige kommuner', 'bold');
		
		$tab = $section->addTable(array('align'=>'center'));
		$tab->addRow();
		woCell($tab, 4000, 'Navn på innslag', 'bold');
		woCell($tab, 3000, 'Kontaktperson', 'bold');
		woCell($tab, 2000, 'Type innslag', 'bold');
		woCell($tab, 2000, 'Kommune', 'bold');
		
		foreach($contacts as $b_contact => $contact_array) {
			if( sizeof($contact_array) <= 1 )
				continue;
			
			$contact = new person($b_contact);
			$first = true;
			$all_same_kommune = true;
			$collect_infos = array();
			foreach( $contact_array as $b_id ) {
				$looped['innslag'][] = $b_id;
				$band = new innslag($b_id);
				if($first)
					$kommune = $band->g('b_kommune');
				elseif($band->g('b_kommune') !== $kommune)
						$all_same_kommune = false;
				$first = false;
				
				$collect_infos[] = array('bname' => $band->g('b_name'),
										'contact' => $contact->g('name'),
										'bt_name' => $band->g('bt_name'),
										'kommune' => utf8_encode(UKMN_kommune($band->g('b_kommune')))
										);
			}
			if(!$all_same_kommune) {
				foreach($collect_infos as $info) {
					$tab->addRow();
					woCell($tab, 4000, $info['bname']);
					woCell($tab, 3000, $info['contact']);
					woCell($tab, 2000, $info['bt_name']);
					woCell($tab, 2000, $info['kommune']);
				}
			}
		}
		
		
		arsort($bands);
		woText($section, 'Innslag med samme navn...', 'grp');
		woText($section, '..hvor minst to av innslagene deltar i forskjellige kommune og innslaget ikke allerede er listet i tabellen ovenfor', 'bold');
		
		$tab = $section->addTable(array('align'=>'center'));
		$tab->addRow();
		woCell($tab, 4000, 'Navn på innslag', 'bold');
		woCell($tab, 3000, 'Kontaktperson', 'bold');
		woCell($tab, 2000, 'Type innslag', 'bold');
		woCell($tab, 2000, 'Kommune', 'bold');

		foreach( $bands as $b_name => $b_id_array ) {
			if(sizeof($b_id_array) <= 1)
				continue;

			$first = true;
			$all_same_kommune = true;
			$collect_infos = array();
			foreach( $b_id_array as $b_id ) {
				if(in_array($b_id, $looped['innslag']))
					continue;
					
				$band = new innslag($b_id);
				if($first)
					$kommune = $band->g('b_kommune');
				elseif($band->g('b_kommune') !== $kommune)
						$all_same_kommune = false;
				$first = false;
				$contact = new person($band->g('b_contact'));
	
				$collect_infos[] = array('bname' => $band->g('b_name'),
										'contact' => $contact->g('name'),
										'bt_name' => $band->g('bt_name'),
										'kommune' => utf8_encode(UKMN_kommune($band->g('b_kommune')))
										);
			}
			if(!$all_same_kommune) {
				foreach($collect_infos as $info) {
					$tab->addRow();
					woCell($tab, 4000, $info['bname']);
					woCell($tab, 3000, $info['contact']);
					woCell($tab, 2000, $info['bt_name']);
					woCell($tab, 2000, $info['kommune']);
				}
			}
		}
		return $thiswoWrite();
	}
	
	
	public function generate() {
		$looped = array('monstringer'=>array(), 'innslag'=>array());
		$contacts = array();
		$bands = array();
		$monstring = new monstring(get_option('pl_id'));
		$lokalmonstringer = $monstring->hent_lokalmonstringer();
		
		echo $this->html_init('Duplikate deltakere i fylket');

		
		foreach($lokalmonstringer as $lokal) {
			if(in_array($lokal, $looped['monstringer']))
				continue;
			$looped['monstringer'][] = $lokal;
			
			$lm = new monstring( $lokal );
			
			foreach( $lm->innslag() as $b_id => $band ) {
				$innslag = new innslag($band['b_id']);
				
				# HVIS KONTAKTPERSONEN IKKE HAR NOEN INNSLAG, ELLER HVIS KONTAKTPERSONEN IKKE HAR DETTE INNNSLAGET
				if((is_array($contacts[$innslag->g('b_contact')]) && !in_array($band['b_id'], $contacts[$innslag->g('b_contact')])) || !is_array($contacts[$innslag->g('b_contact')]))
					$contacts[$innslag->g('b_contact')][] = $band['b_id'];
					
				$bands[$innslag->g('b_name')][] = $innslag->g('b_id');
			}
		}
		
		arsort($contacts);
		echo '<h1>Kontaktpersoner som har flere innslag...</h1>'
			.'<strong>...og hvor minst to av innslagene deltar i forskjellige kommune</strong>'
			.'<br />'
			.'<table class="rapport_turneliste_kontaktpersoner">'
			.	'<tr>'
			.		'<th>Navn på innslag</th>'
			.		'<th>Kontaktperson</th>'
			.		'<th>Type innslag</th>'
			.		'<th>Kommune</th>'
			.	'</tr>'
			;
		foreach($contacts as $b_contact => $contact_array) {
			if( sizeof($contact_array) <= 1 )
				continue;
			
			$contact = new person($b_contact);
			$first = true;
			$all_same_kommune = true;
			$collect_infos = '';
			foreach( $contact_array as $b_id ) {
				$looped['innslag'][] = $b_id;
				$band = new innslag($b_id);
				if($first)
					$kommune = $band->g('b_kommune');
				elseif($band->g('b_kommune') !== $kommune)
						$all_same_kommune = false;
				$first = false;
				
				$collect_infos .= '<tr>'
								.	'<td>'.$band->g('b_name').' &nbsp;</td>'
								.	'<td>'.$contact->g('name').' &nbsp;</td>'
								.	'<td>'.$band->g('bt_name').' &nbsp;</td>'
								.	'<td>'.utf8_encode(UKMN_kommune($band->g('b_kommune'))).' &nbsp;</td>'
								.'</tr>';
			}
			if(!$all_same_kommune)
				echo $collect_infos
					.'<tr>'
						.'<td colspan="4">&nbsp;</td>'
					.'</tr>';
		}
		echo '</table>';
		
		
		arsort($bands);
		echo '<h1>Innslag med samme navn...</h1>'
			.'<strong>...hvor minst to av innslagene deltar i forskjellige kommune og innslaget ikke allerede er listet i tabellen ovenfor</strong>'
			.'<br />'
			.'<table class="rapport_turneliste_kontaktpersoner">'
			.	'<tr>'
			.		'<th>Navn på innslag</th>'
			.		'<th>Kontaktperson</th>'
			.		'<th>Type innslag</th>'
			.		'<th>Kommune</th>'
			.	'</tr>'
			;

		foreach( $bands as $b_name => $b_id_array ) {
			if(sizeof($b_id_array) <= 1)
				continue;

			$first = true;
			$all_same_kommune = true;
			$collect_infos = '';
			foreach( $b_id_array as $b_id ) {
				if(in_array($b_id, $looped['innslag']))
					continue;
					
				$band = new innslag($b_id);
				if($first)
					$kommune = $band->g('b_kommune');
				elseif($band->g('b_kommune') !== $kommune)
						$all_same_kommune = false;
				$first = false;
				$contact = new person($band->g('b_contact'));
	
				$collect_infos .= '<tr>'
								.	'<td>'.$band->g('b_name').' &nbsp;</td>'
								.	'<td>'.$contact->g('name').' &nbsp;</td>'
								.	'<td>'.$band->g('bt_name').' &nbsp;</td>'
								.	'<td>'.utf8_encode(UKMN_kommune($band->g('b_kommune'))).' &nbsp;</td>'
								.'</tr>';
			}
			if(!$all_same_kommune)
					echo $collect_infos
						.'<tr>'
							.'<td colspan="4">&nbsp;</td>'
						.'</tr>';
		}
		echo '</table>';
	}
}