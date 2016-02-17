<?php
require_once('UKM/innslag.class.php');
require_once('UKM/monstring.class.php');

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

		$this->navn = 'Videresendte fra min mønstring';
		$this->m = new monstring(get_option('pl_id'));


		$g = $this->optGrp('h','Vis hvem?');
		$this->opt($g, 'h_kontaktp', 'Vis kontaktperson');
		$this->opt($g, 'h_vis', 'Vis alle deltakere');

		$this->opt($g, 'p_mobil', 'Vis mobilnummer');
		
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
	#public function generateExcel(){

	#}

	/**
	 * generateWord function
	 * 
	 * Genererer et word-dokument med rapporten.
	 *
	 * @access public
	 * @return String download-URL
	 */	
	public function generateWord(){
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
		echo ($this->show('p_mobil') || $this->show('h_kontaktp')) ? '<td>Mobilnummer</td>' : '';
		echo ($this->show('h_kontaktp')) ? '<td>E-post</td>' : '';
		echo ($this->show('i_kommune')) ? '<td>Kommune</td>' : '';
		echo '</tr>';

		foreach ($videresendte as $v) {

			#var_dump($v);
			$innslag = new innslag($v['b_id']);
			if ($this->show('i_kommune')) {
				$innslag->loadGEO();
			}
		#### Kontaktperson
			$kontaktperson = $innslag->kontaktperson();
			#var_dump($kontaktperson);


			## Innslag
			echo '<tr class="">';
			echo '<td class="">'.$innslag->get('b_name').'</td>';
			echo ($this->show('h_kontaktp')) ? '<td class="">'.$kontaktperson->get('p_firstname').' '.$kontaktperson->get('p_lastname').'</td>' : '';
			echo ($this->show('h_vis') && !$this->show('h_kontaktp')) ? '<td></td>' : '';
			if ($this->show('h_kontaktp')) { 
				echo '<td class="mobil UKMSMS">'.$kontaktperson->get('p_phone').'</td>';
			} elseif ( !$this->show('h_kontaktp') && $this->show('p_mobil')) { 
				#echo '<td class="mobil UKMSMS">'.$kontaktperson->get('p_phone').'</td>';
				echo '<td></td>';
			}
			else {
				#echo '<td></td>';
			}
			echo ($this->show('h_kontaktp')) ? '<td class="epost">'.$kontaktperson->get('p_email').'</td>' : '';
			echo ($this->show('i_kommune')) ? '<td class="kommune">'.$innslag->get('kommune').'</td>' : '';
			echo '</tr>';
	
			## Personer i innslaget
			if ($this->show('h_vis')) {
				$personer = $innslag->personer();
				foreach($personer as $person) {
					echo '<tr class="">';
					echo '<td class=""></td>';
					echo '<td class="name">'.$person['p_firstname'].' '.$person['p_lastname'].'</td>';
					echo ($this->show('p_mobil') ) ? '<td class="mobil UKMSMS">'.$person['p_phone'].'</td>' : '<td></td>';
					echo '</tr>';
				}
			}
		}

		echo '</ul>';
	}


}