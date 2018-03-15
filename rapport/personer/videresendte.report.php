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
		
		$navn = 'Videresendte';
		$section = $this->word_init('portrait', $navn);
		$monstring = new monstring_v2( get_option('pl_id') );
		$videresend_til = $this->getVideresendTil( $monstring );
		$videresendte = $this->getVideresendte();

		// HEADERS
		exCell(i2a($col).$rad, 'Innslagsnavn', 'bold'); $col++;
		if( $this->show('h_vis') || $this->show('h_kontaktp') ) {
			exCell(i2a($col).$rad, 'Personer', 'bold');
			$col++;
		}
		if( $this->show('p_mobil') ) {
			exCell(i2a($col).$rad, 'Mobilnummer', 'bold');
			$col++;
		}
		if( $this->show('p_epost') ) {
			exCell(i2a($col).$rad, 'E-post', 'bold');
			$col++;
		}
		if( $this->show('i_kommune') ) {
			exCell(i2a($col).$rad, 'Kommune', 'bold');
			$col++;
		}
			
		// Innslag
		foreach ($videresendte as $innslag) {
			#### Kontaktperson
			if( $monstring->getType() == 'kommune' ) {
				$personer = $innslag->getPersoner()->getAllVideresendt( $videresend_til[ $innslag->getFylke()->getId() ] );
			} else {
				$personer = $innslag->getPersoner()->getAllVideresendt( array_pop( $videresend_til ) );
			}
			
			// Hvis kontaktperson skal vises
			if( $this->show('h_kontaktp') ) {
				$rad++;
				#### Kontaktperson
				$kontaktperson = $innslag->getKontaktperson();
				$col = 1;
				exCell(i2a($col).$rad, $innslag->getNavn()); 
				$col++;
				exCell(i2a($col).$rad, ( $kontaktperson->getNavn()
										. ($this->show('h_vis') ? ' (kontaktperson)' : '')
										)

				); // Kontaktperson-navn
				$col++;
				if( $this->show('p_mobil') ) {
					exCell(i2a($col).$rad, $kontaktperson->getMobil());
					$col++;
				}
				if( $this->show('p_epost') ) {
					exCell(i2a($col).$rad, $kontaktperson->getEpost());
					$col++;
				}
				if( $this->show('i_kommune') ) {
					exCell(i2a($col).$rad, $innslag->getKommune()->getNavn());
				}
			}

			## Personer i innslaget
			if ($this->show('h_vis')) {
				foreach($personer as $person) {
					$col = 1; 
					$rad++;
					exCell(i2a($col).$rad, $innslag->getNavn()); 
					$col++;
					exCell(i2a($col).$rad, $person->getNavn());
					$col++;

					if( $this->show('p_mobil') ) {
						exCell(i2a($col).$rad, $person->getMobil());
						$col++;
					}
					if( $this->show('p_epost') ) {
						exCell(i2a($col).$rad, $person->getEpost());
						$col++;
					}
					if( $this->show('i_kommune') ) {
						exCell(i2a($col).$rad, $innslag->getKommune()->getNavn());
					}
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
		global $objPHPExcel;
		$navn = 'Videresendte';
		$section = $this->word_init('portrait', $navn);
		$monstring = new monstring_v2( get_option('pl_id') );
		$videresend_til = $this->getVideresendTil( $monstring );
		$videresendte = $this->getVideresendte();

		foreach ($videresendte as $innslag) {
		#### Kontaktperson
			$col = 1;
			$rad++;
			$kontaktperson = $innslag->getKontaktperson();
			if( $monstring->getType() == 'kommune' ) {
				$personer = $innslag->getPersoner()->getAllVideresendt( $videresend_til[ $innslag->getFylke()->getId() ] );
			} else {
				$personer = $innslag->getPersoner()->getAllVideresendt( array_pop( $videresend_til ) );
			}

			// Lag header
			$text = $innslag->getNavn();
			if ($this->show('i_kommune'))
				$text .= ' ('.$innslag->getKommune()->getNavn().')';

			woText($section, $text, 'h2');
			// Lag paragraf
			if ($this->show('h_kontaktp')) {
				$text = 'Kontaktperson: '.$kontaktperson->getNavn(). ', '. $kontaktperson->getMobil().', '.$kontaktperson->getEpost().'.';
				woText($section, $text);
			}
			// List opp deltakere
			if ($this->show('h_vis')) {
				foreach($personer as $person) {
					$pText = $person->getNavn().', '.$person->getMobil().', '.$person->getEpost().'.';
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
		$monstring = new monstring_v2( get_option('pl_id') );
		$videresend_til = $this->getVideresendTil( $monstring );
		$videresendte = $this->getVideresendte();
		
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

		foreach ($videresendte as $innslag) {
		#### Kontaktperson
			$kontaktperson = $innslag->getKontaktperson();

			## Innslag
			echo '<tr>';
			echo '<td>'.$innslag->getNavn().'</td>';

			// Hvis kontaktperson skal vises
			if( $this->show('h_kontaktp') ) {
				echo '<td>'
					.  $kontaktperson->getNavn()
					. ($this->show('h_vis') ? ' <small>(kontaktperson)</small>' : '')
					. '</td>';
				echo $this->show('p_mobil') ? '<td class="mobil UKMSMS">'.$kontaktperson->getMobil().'</td>' : '';
				echo $this->show('p_epost') ? '<td class="UKMMAIL epost">'.$kontaktperson->getEpost().'</td>' : '';
			// Hvis kontaktpersonen ikke skal vises
			} else { 
				echo $this->show('h_vis') ?'<td></td>' : '';
				echo $this->show('p_mobil') ? '<td></td>' : '';
				echo $this->show('p_epost') ? '<td></td>' : '';
			}
			echo ($this->show('i_kommune')) ? '<td class="kommune">'.$innslag->getKommune()->getNavn().'</td>' : '';

			echo '</tr>';
	
			## Personer i innslaget
			if ($this->show('h_vis')) {
				
				if( $monstring->getType() == 'kommune' ) {
					$personer = $innslag->getPersoner()->getAllVideresendt( $videresend_til[ $innslag->getFylke()->getId() ] );
				} else {
					$personer = $innslag->getPersoner()->getAllVideresendt( array_pop( $videresend_til ) );
				}

				foreach($personer as $person) {
					echo '<tr>';
					echo '<td></td>'; // ikke vis innslagsnavnet per person
					echo '<td class="name">'.$person->getNavn() .'</td>';
					echo ($this->show('p_mobil') ) ? '<td class="mobil UKMSMS">'. $person->getMobil() .'</td>' : '';
					echo ($this->show('p_epost') ) ? '<td class="epost UKMMAIL">'. $person->getEpost() .'</td>' : '';
					echo ($this->show('i_kommune')) ? '<td class="kommune">'. $innslag->getKommune()->getNavn() .'</td>' : '';
					echo '</tr>';
				}
			}
		}

		echo '</ul>';
	}

	public function getVideresendte() {
		$monstring = new monstring_v2( get_option('pl_id') );
		$videresend_til = $this->getVideresendTil( $monstring );
	
		$alle_innslag = [];
		foreach( $videresend_til as $monstring_videre) {
			$alle_videresendte = $monstring->getInnslag()->getVideresendte( $monstring_videre );
			
			foreach( $alle_videresendte as $innslag ) {
				$alle_innslag[] = $innslag;
			}
			
		}

		return $alle_innslag;
	}

	public function getVideresendTil( $monstring ) {	
		if( $monstring->getType() == 'kommune' ) {
			$fylkesmonstringer = $monstring->getFylkesMonstringer();
			
			foreach( $fylkesmonstringer as $fylkesmonstring ) {
				$videresend_til[ $fylkesmonstring->getFylke()->getId() ] = $fylkesmonstring;
			}
		} else {
			$videresend_til = [ stat_monstringer_v2::land( $monstring->getSesong() ) ];
		}
		
		return $videresend_til;
	}

}