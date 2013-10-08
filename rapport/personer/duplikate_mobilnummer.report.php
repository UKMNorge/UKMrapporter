<?php
require_once('UKM/innslag.class.php');
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
		
		$this->navn = 'Duplikate mobilnummer';
		
		$this->_postConstruct();	
	}

	/**
	 * generateExcel function
	 * 
	 * Genererer et excel-dokument med rapporten.
	 *
	 * @access public
	 * @return String download-URL
	 */		
	public function generateExcel(){
		global $objPHPExcel;
		$objPHPExcel = new PHPExcel();
		$this->excel_init('landscape');
		exSheetName('DUPLIKATE','6dc6c1');
		$mobilnummer = $this->_findDuplicates();
		if(!is_array($mobilnummer) || sizeof($mobilnummer)==0){
			exCell('A1:I1',  'Ingen mobilnummer på din mønstring brukes av mer enn én person', 'bold');
		} else {
			$row = 1;
			exCell('A1', 'Nummer', 'bold');
			exCell('B1', 'Navn', 'bold');
			exCell('C1', 'Rolle/instrument', 'bold');
			exCell('D1', 'Innslag', 'bold');
			exCell('E1', 'Evt. e-post', 'bold');
			foreach($mobilnummer as $mobil => $infos){				
				foreach($infos as $p_id => $pinfo) {
					$row++;
					exCell('A'.$row, $mobil);
					exCell('B'.$row, $pinfo['pers']->g('name').' ('.$pinfo['pers']->alder().' år)');
					exCell('C'.$row, ($pinfo['type']=='kontaktperson' ? 'KONTAKTPERSON' : '') . $pinfo['pers']->g('instrument'));
					exCell('D'.$row, $pinfo['inns']->g('b_name'));
					exCell('E'.$row, $pinfo['pers']->g('p_email'));
				}
			}
		}
		return $this->exWrite();
	}
		
	/**
	 * generateWord function
	 * 
	 * Genererer et word-dokument med rapporten.
	 *
	 * @access public
	 * @return String download-URL
	 */	
	public function generateWord(){
		global $PHPWord;
		$section = $this->word_init('landscape');

		$mobilnummer = $this->_findDuplicates();

		if(!is_array($mobilnummer) || sizeof($mobilnummer)==0){
			woText($section, 'Ingen mobilnummer på din mønstring brukes av mer enn én person');
		} else {
			foreach($mobilnummer as $mobil => $infos){
				woText($section, $mobil, 'grp');
				$tab = $section->addTable();
				$tab->addRow();
				woCell($tab, 4000, 'Navn', 'bold');
				woCell($tab, 3000, 'Rolle/instrument', 'bold');
				woCell($tab, 3000, 'Innslag', 'bold');
				woCell($tab, 4000, 'Evt. e-post', 'bold');
				
				foreach($infos as $p_id => $pinfo) {
					$tab->addRow();
					woCell($tab, 4000, $pinfo['pers']->g('name').' ('.$pinfo['pers']->alder().' år)');
					woCell($tab, 3000, ($pinfo['type']=='kontaktperson' ? 'KONTAKTPERSON' : '') . $pinfo['pers']->g('instrument'));
					woCell($tab, 3000, $pinfo['inns']->g('b_name'));
					woCell($tab, 4000, $pinfo['pers']->g('p_email'));
				}
			}
		}
		return $this->woWrite();
	}
	
	
	/**
	 * generate function
	 * 
	 * Genererer selve rapporten i HTML-visning
	 *
	 * @access public
	 * @return void
	 */	
	public function generate() {
		$mobilnummer = $this->_findDuplicates();	
		echo $this->html_init('Duplikate mobilnummer');
		?>
		<?php
		if(!is_array($mobilnummer) || sizeof($mobilnummer)==0){
			echo '<strong>Ingen mobilnummer på din mønstring brukes av mer enn én person</strong>';
		} else {
			foreach($mobilnummer as $mobil => $infos){ ?>
				<h3 class="duplikate_mobilnummer"><?= contact_sms($mobil) ?></h3>
				<ul class="duplikate_mobilnummer">
					<li class="header">
						<div class="name">Navn</div>
						<div class="type">Rolle/instrument</div>
						<div class="band">Innslag</div>
						<div class="mail">Evt. e-post</div>
					</li>
				<?php
				foreach($infos as $p_id => $pinfo) { ?>
					<li>
						<div class="name"><?= $pinfo['pers']->g('name')?> (<?= $pinfo['pers']->alder()?> år)</div>
						<div class="type"><?= ($pinfo['type']=='kontaktperson' ? 'KONTAKTPERSON' : '') . $pinfo['pers']->g('instrument')?></div>
						<div class="band"><?= $pinfo['inns']->g('b_name')?></div>
						<div class="mail"><?= contact_mail($pinfo['pers']->g('p_email'))?></div>
					</li>
				<?php
				} ?>
				</ul>
				<?php
			}
		}
	}
	
	/**
	 * _findDuplicates function
	 * 
	 * Sammenligner alle deltakere på mønstringens mobilnummer, og ser hvem som bruker det samme
	 *
	 * @access public
	 * @return void
	 */	
	private function _findDuplicates() {
		$innslagene = $this->m->innslag();
		foreach($innslagene as $innslag) {
			$inn = new innslag($innslag['b_id']);
			if(get_option('site_type')!='kommune')
				$inn->videresendte(get_option('pl_id'));
			$personer = $inn->personer();
			$kontakt = $inn->kontaktperson();
			$this->mobilnummer[$kontakt->g('p_phone')][$kontakt->g('p_id')] = array('type'=>'kontaktperson',
																					'pers'=> $p,
																					'inns'=>$inn);
			foreach($personer as $person){
				$p = new person($person['p_id'], $inn->g('b_id'));
				$this->mobilnummer[$p->g('p_phone')][$p->g('p_id')] = array('type'=>'deltaker',
																					'pers'=> $p,
																					'inns'=>$inn);
			}
		}
		
		$this->_cleanDuplicateList();
		
		return $this->mobilnummer;
	}
	
	/**
	 * _cleanDuplicateList function
	 * 
	 * Går gjennom listen av mobilnummer og fjerner de som ikke har duplikate personer
	 *
	 * @access public
	 * @return void
	 */	
	private function _cleanDuplicateList() {
		foreach($this->mobilnummer as $mobil => $personer) {
			if(sizeof($personer)<2)
				unset($this->mobilnummer[$mobil]);
		}
	}
}
?>