<?php
require_once('UKM/innslag.class.php');
require_once('UKM/monstring.class.php');
require_once('UKM/inc/toolkit.inc.php');

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
		if (UKM_HOSTNAME == 'ukm.dev') {
			define('EXCEL_WRITE_PATH', '/tmp/');
			define('WORD_WRITE_PATH', '/tmp/');
		}
		parent::__construct($rapport, $kategori);

		$this->navn = 'Videresendte fra min mønstring';
		$this->m = new monstring(get_option('pl_id'));


		$g = $this->optGrp('h','Vis hvem?');
		$this->opt($g, 'h_kontaktp', 'Vis kontaktperson');
		$this->opt($g, 'h_vis', 'Vis alle deltakere');

		$this->opt($g, 'p_mobil', 'Vis mobilnummer');
		$this->opt($g, 'p_epost', 'Vis e-post');
		
		$g = $this->optGrp('i','Info om innslaget');
		$this->opt($g, 'i_kommune', 'Vis kommune');

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

		$navn = 'Videresendte fra '.$this->m->get('pl_name');
		global $objPHPExcel;
		$this->excel_init('landscape');
		
		exSheetName('INNSLAG', '6dc6c1');
		
		$rad = $p2rad = $p3rad = 1;
		$headerRad = 1;
		$col = 1;
		$videresendte = $this->m->videresendte();

		// Innslagsnavn, personer, mobilnummer, e-post, kommune
		exCell(i2a($col).$rad, 'Innslagsnavn', 'bold'); $col++;
		if ($this->show('h_vis') || $this->show('h_kontaktp')) 
			exCell(i2a($col).$rad, 'Personer', 'bold'); $col++;
		if ($this->show('p_mobil') || $this->show('h_kontaktp'))
			exCell(i2a($col).$rad, 'Mobilnummer', 'bold'); $col++;
		if ($this->show('h_kontaktp'))
			exCell(i2a($col).$rad, 'E-post', 'bold'); $col++;
		if ($this->show('i_kommune'))
			exCell(i2a($col).$rad, 'Kommune', 'bold'); $col++;

		foreach ($videresendte as $v) {
			// Prep
			$innslag = new innslag($v['b_id']);
			$col = 1;
			$rad++;
			$innslag->loadGEO();
			$personer = $innslag->personer();
			$kontaktperson = $innslag->kontaktperson();

			// Fill cells
			exCell(i2a($col).$rad, $v['b_name']); 
			$col++;
			if ($this->show('h_kontaktp')) {
				exCell(i2a($col).$rad, $kontaktperson->get('p_firstname'). ' '. $kontaktperson->get('p_lastname')); // Kontaktperson-navn
				$col++;
			}
			elseif (!$this->show('h_kontaktp') && $this->show('h_vis')) {
				$col++;
			}

			if ($this->show('h_kontaktp')) {
				exCell(i2a($col).$rad, $kontaktperson->get('p_phone'));
				$col++;
			}
			elseif ( !$this->show('h_kontaktp') && $this->show('p_mobil')) {
				$col++;
			}

			if ($this->show('h_kontaktp')) {
				exCell(i2a($col).$rad, $kontaktperson->get('p_email'));
				$col++;
			} elseif ( !$this->show('h_kontaktp') && $this->show('p_mobil')) { 
				$col++;
			}

			if ($this->show('i_kommune'))
				exCell(i2a($col).$rad, $innslag->get('kommune'));

			foreach ($personer as $person) {
				$rad++;
				$col = 2;
				
				exCell(i2a($col).$rad, $person['p_firstname'].' '.$person['p_lastname']);
				$col++;
				exCell(i2a($col).$rad, $person['p_phone']);
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
		global $objPHPExcel;
		$navn = 'Videresendte';
		$section = $this->word_init('portrait', $navn);
		$videresendte = $this->m->videresendte();

		foreach ($videresendte as $v) {
			// Prep
			$innslag = new innslag($v['b_id']);
			$col = 1;
			$rad++;
			$innslag->loadGEO();
			$personer = $innslag->personer();
			$kontaktperson = $innslag->kontaktperson();

			// Lag header
			$text = $innslag->get('b_name');
			if ($this->show('i_kommune'))
				$text .= ' ('.$innslag->get('kommune').')';

			woText($section, $text, 'h2');
			// Lag paragraf
			if ($this->show('h_kontaktp')) {
				$text = 'Kontaktperson: '.$kontaktperson->get('p_firstname'). ' '. $kontaktperson->get('p_lastname'). ', '. $kontaktperson->get('p_phone').', '.$kontaktperson->get('p_email').'.';
				woText($section, $text);
			}
			// List opp deltakere
			if ($this->show('h_vis')) {
				foreach ($personer as $person) {
					#var_dump($person);
					$pText = $person['p_firstname'].' '.$person['p_lastname'].', '.$person['p_phone'];
					$pText = htmlspecialchars("\t").$pText;
					woText($section, $pText);
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
		echo '<h3>Videresendte fra '.$this->m->get('pl_name').'</h3>';
		$videresendte = $this->m->videresendte();
	
	#### Formatted output:

		if(empty($videresendte)){
			echo '<strong>Ingen innslag</strong>';
			return;
		}

		echo '<table  style="width: '.$width.'">';
		#Headers:
		echo '<tr class="headers" style="font-weight: bold;">';
		echo '<td>Innslagsnavn</td>';
		echo ($this->show('h_vis') || $this->show('h_kontaktp')) ? '<td>Personer</td>' : '';
		echo ($this->show('p_mobil')) ? '<td>Mobilnummer</td>' : '';
		echo ($this->show('p_epost')) ? '<td>E-post</td>' : '';
		echo ($this->show('i_kommune')) ? '<td>Kommune</td>' : '';
		echo '</tr>';

		foreach ($videresendte as $v) {

			$innslag = new innslag($v['b_id']);
			if ($this->show('i_kommune')) {
				$innslag->loadGEO();
			}
		#### Kontaktperson
			$kontaktperson = $innslag->kontaktperson();


			## Innslag
			echo '<tr>';
			echo '<td>'.$innslag->get('b_name').'</td>';

			// Hvis kontaktperson skal vises
			if( $this->show('h_kontaktp') ) {
				echo '<td>'.$kontaktperson->get('p_firstname').' '.$kontaktperson->get('p_lastname').'</td>';
				echo $this->show('p_mobil') ? '<td class="mobil UKMSMS">'.$kontaktperson->get('p_phone').'</td>' : '';
				echo $this->show('p_epost') ? '<td class="UKMMAIL epost">'.$kontaktperson->get('p_email').'</td>' : '';
			// Hvis kontaktpersonen ikke skal vises
			} else { 
				echo '<td></td>';
				echo $this->show('p_mobil') ? '<td></td>' : '';
				echo $this->show('p_epost') ? '<td></td>' : '';
			}
			echo ($this->show('i_kommune')) ? '<td class="kommune">'.$innslag->get('kommune').'</td>' : '';

			echo '</tr>';
	
			## Personer i innslaget
			if ($this->show('h_vis')) {
				$personer = $innslag->personObjekter();
				foreach($personer as $person) {
					echo '<tr>';
					echo '<td></td>'; // ikke vis innslagsnavnet per person
					echo '<td class="name">'.$person->get('firstname').' '.$person->get('lastname').'</td>';
					echo ($this->show('p_mobil') ) ? '<td class="mobil UKMSMS">'.$person->get('phone').'</td>' : '<td></td>';
					echo ($this->show('p_epost') ) ? '<td class="epost UKMMAIL">'.$person->get('email').'</td>' : '<td></td>';
					echo '</tr>';
				}
			}
		}

		echo '</ul>';
	}


}