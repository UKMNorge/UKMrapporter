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
		
		$this->navn = 'Kontaktlister';
		
		$g = $this->optGrp('h','Vis hvem?');
		$this->opt($g, 'h_kontaktp', 'Vis kontaktperson');
		$this->opt($g, 'h_vis', 'Vis alle deltakere');

		$g = $this->optGrp('p','Info om enkeltpersoner');
		$this->opt($g, 'p_alder', 'Vis alder');
		$this->opt($g, 'p_mobil', 'Vis mobilnummer');
		$this->opt($g, 'p_rolle', 'Vis instrument / rolle');
		$this->opt($g, 'p_epost', 'Vis e-post');
		$this->opt($g, 'p_adresse', 'Vis postadresse (hvis oppgitt)');

		$g = $this->optGrp('i','Info om innslaget');
		$this->opt($g, 'i_type', 'Type innslag');
		$this->opt($g, 'i_fylke', 'Vis fylke');
		$this->opt($g, 'i_kommune', 'Vis kommune');

		$g = $this->formatGrp('g', 'Gruppering', 'radio');
		$this->format($g, 'g_ingen', '<b>Alfabetisk etter innslag</b>'
								.	 '<br /> &nbsp; &nbsp; &nbsp; <b>Gruppert:</b> Ingen gruppering'
								.	 '<br /> &nbsp; &nbsp; &nbsp; <b>Sortert:</b> Alfabetisk etter navn på innslag, deretter personer'
						);

		$this->format($g, 'g_type', '<b>Type, sortert etter innslag</b>'
								.	 '<br /> &nbsp; &nbsp; &nbsp; <b>Gruppert:</b> Type'
								.	 '<br /> &nbsp; &nbsp; &nbsp; <b>Sortert:</b> Alfabetisk etter navn på innslag, deretter personer'
						);

		if(get_option('site_type')!='land') {
			$this->format($g, 'g_kommune', '<b>Kommune, sortert etter innslag</b>'
									.	 '<br /> &nbsp; &nbsp; &nbsp; <b>Gruppert:</b> Kommune'
									.	 '<br /> &nbsp; &nbsp; &nbsp; <b>Sortert:</b> Alfabetisk etter navn på innslag, deretter personer'
							);
		} else {
			$this->format($g, 'g_fylke', '<b>Fylke, sortert etter innslag</b>'
									.	 '<br /> &nbsp; &nbsp; &nbsp; <b>Gruppert:</b> Fylke'
									.	 '<br /> &nbsp; &nbsp; &nbsp; <b>Sortert:</b> Alfabetisk etter navn på innslag, deretter personer'
							);

			$this->format($g, 'g_fylke_type', '<b>Fylke og type, sortert etter innslag</b>'
									.	 '<br /> &nbsp; &nbsp; &nbsp; <b>Gruppert:</b> Fylke og type innslag'
									.	 '<br /> &nbsp; &nbsp; &nbsp; <b>Sortert:</b> Alfabetisk etter navn på innslag, deretter personer'
							);
		}
		
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
		$this->excel_init('landscape');
		exSheetName('INNSLAG','6dc6c1');

		$rad = $col = 1;
		$grupperte_innslag = $this->_innslag();
		if(!is_array($grupperte_innslag)){
			exCell('A1:D1', 'Ingen informasjon');
		} else {
			if (key($grupperte_innslag) !== 0) {
				$col++; // Skip en kolonne
			}
			exCell(i2a($col).'1', 'Navn', 'bold');
			$col++;
			exCell(i2a($col).'1', 'Fornavn', 'bold');
			$col++;
			exCell(i2a($col).'1', 'Etternavn', 'bold');
			if($this->show('p_alder')){
				$col++;
				exCell(i2a($col).'1', 'Alder', 'bold');
			}
			if($this->show('p_mobil')){
				$col++;
				exCell(i2a($col).'1', 'Mobil', 'bold');
			}
			if($this->show('p_rolle')){
				$col++;
				exCell(i2a($col).'1', 'Instrument/rolle', 'bold');
			}
			if($this->show('p_epost')){
				$col++;
				exCell(i2a($col).'1', 'E-post', 'bold');
			}
			
			$col++;
			exCell(i2a($col).'1', 'Innslag', 'bold');
			if($this->show('i_type')){
				$col++;
				exCell(i2a($col).'1', 'Type', 'bold');
			}
			if($this->show('i_fylke')){
				$col++;
				exCell(i2a($col).'1', 'Fylke', 'bold');
			}
			if($this->show('i_kommune')){
				$col++;
				exCell(i2a($col).'1', 'Kommune', 'bold');
			}
			if($this->show('p_adresse')) {
				$col++;
				exCell(i2a($col).$rad, 'Adresse', 'bold');
				$col++;
				exCell(i2a($col).$rad, 'Postnr', 'bold');
				$col++;
				exCell(i2a($col).$rad, 'Poststed', 'bold');
			}


			foreach($grupperte_innslag as $grp => $innslag){
				foreach($innslag as $inn) {
					$kontakt = $inn->kontaktperson();
					## LAST INN GEOGRAFI KUN HVIS NØDVENDIG
					if($this->show('i_fylke')||$this->show('i_kommune'))
						$inn->loadGeo();
						
					### KONTAKTPERSON
					if($this->show('h_kontaktp')) {
						$p = $inn->kontaktperson();
						$rad++;
						$col = 1;						
						if($grp !== 0) {
							exCell(i2a($col).'1', 'Gruppering', 'bold');
							exCell(i2a($col).$rad, $grp, 'bold');
							$col++;
						}
						exCell(i2a($col).$rad, $p->g('name'), 'bold');
						$col++;
						exCell(i2a($col).$rad, $p->g('p_firstname'), 'bold');
						$col++;
						exCell(i2a($col).$rad, $p->g('p_lastname'), 'bold');
						
						if($this->show('p_alder')){
							$col++;
							exCell(i2a($col).$rad, $p->alder(), 'bold');
						}
						if($this->show('p_mobil')){
							$col++;
							exCell(i2a($col).$rad, $p->g('p_phone'), 'bold');
						}
						if($this->show('p_rolle')){
							$col++;
							exCell(i2a($col).$rad, 'Kontaktperson', 'bold');
						}
						if($this->show('p_epost')){
							$col++;
							exCell(i2a($col).$rad, $p->g('p_email'), 'bold');
						}
						
						$col++;
						exCell(i2a($col).$rad, $inn->g('b_name'), 'bold');
						if($this->show('i_type')){
							$col++;
							exCell(i2a($col).$rad, $inn->g('bt_name'), 'bold');
						}
						if($this->show('i_fylke')){
							$col++;
							exCell(i2a($col).$rad, $inn->g('fylke'), 'bold');
						}
						if($this->show('i_kommune')){
							$col++;
							exCell(i2a($col).$rad, $inn->g('kommune'), 'bold');
						}
						if($this->show('p_adresse')) {
							$col++;
							exCell(i2a($col).$rad, $p->g('p_adress'), 'bold');
							$col++;
							exCell(i2a($col).$rad, $p->g('p_postnumber'), 'bold');
							$col++;
							exCell(i2a($col).$rad, UKMN_poststed($p->g('p_postnumber')), 'bold');
						}

					}
					## DELTAKERE
					if($this->show('h_vis')) {
						$personer = $inn->personer();
						foreach($personer as $pers) {
							$p = new person($pers['p_id'], $inn->g('b_id'));							
							$rad++;
							$col = 1;						
							if($grp !== 0) {
								exCell(i2a($col).'1', 'Gruppering', 'bold');
								exCell(i2a($col).$rad, $grp);
								$col++;
							}
							exCell(i2a($col).$rad, $p->g('name'));
							$col++;
							exCell(i2a($col).$rad, $p->g('p_firstname'));
							$col++;
							exCell(i2a($col).$rad, $p->g('p_lastname'));
						
							if($this->show('p_alder')){
								$col++;
								exCell(i2a($col).$rad, $p->alder());
							}
							if($this->show('p_mobil')){
								$col++;
								exCell(i2a($col).$rad, $p->g('p_phone'));
							}
							if($this->show('p_rolle')){
								$col++;
								exCell(i2a($col).$rad, $p->g('instrument'));
							}
							if($this->show('p_epost')){
								$col++;
								exCell(i2a($col).$rad, $p->g('p_email'));
							}
							
							$col++;
							exCell(i2a($col).$rad, $inn->g('b_name'));
							if($this->show('i_type')){
								$col++;
								exCell(i2a($col).$rad, $inn->g('bt_name'));
							}
							if($this->show('i_fylke')){
								$col++;
								exCell(i2a($col).$rad, $inn->g('fylke'));
							}
							if($this->show('i_kommune')){
								$col++;
								exCell(i2a($col).$rad, $inn->g('kommune'));
							}
							if($this->show('p_adresse')) {
								$col++;
								exCell(i2a($col).$rad, $p->g('p_adress'));
								$col++;
								exCell(i2a($col).$rad, $p->g('p_postnumber'));
								$col++;
								exCell(i2a($col).$rad, UKMN_poststed($p->g('p_postnumber')));
							}
						}
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
		global $PHPWord;		
		$section = $this->word_init('landscape');

		$grupperte_innslag = $this->_innslag();

		if(!is_array($grupperte_innslag)){
			woText($section, 'Ingen informasjon');
		} else {
			foreach($grupperte_innslag as $grp => $innslag){
				if($grp !== 0)
					woText($section, $grp, 'grp');
				
				$tab = $section->addTable(array('align'=>'center'));

				foreach($innslag as $inn) {
					$kontakt = $inn->kontaktperson();
					## LAST INN GEOGRAFI KUN HVIS NØDVENDIG
					if($this->show('i_fylke')||$this->show('i_kommune'))
						$inn->loadGeo();
						
					### KONTAKTPERSON
					if($this->show('h_kontaktp')) {
						$tab->addRow();
						$p = $inn->kontaktperson();
						
						woCell($tab, 3000, $p->g('name'), 'bold');
						if($this->show('p_alder'))
							woCell($tab, 800, $p->alder(), 'bold');
						if($this->show('p_mobil'))
							woCell($tab, 800, $p->g('p_phone'), 'bold');
						if($this->show('p_rolle'))
							woCell($tab, 800, $p->g('instrument'), 'bold');
						if($this->show('p_epost'))
							woCell($tab, 800, $p->g('p_email'), 'bold');

						woCell($tab, 800, $inn->g('b_name'), 'bold');

						if($this->show('i_type'))
							woCell($tab, 800, $inn->g('bt_name'), 'bold');
						if($this->show('i_fylke'))
							woCell($tab, 800, $inn->g('fylke').($this->show('i_kommune')?' - '.$inn->g('kommune'):''), 'bold');
						if($this->show('i_kommune')&&!$this->show('i_fylke'))
							woCell($tab, 800, $inn->g('kommune'), 'bold');
					}
					## DELTAKERE
					if($this->show('h_vis')) {
						$personer = $inn->personer();
						foreach($personer as $pers) {
							$p = new person($pers['p_id'], $inn->g('b_id'));
							$tab->addRow();
	
							woCell($tab, 3000, $p->g('name'));
							if($this->show('p_alder'))
								woCell($tab, 800, $p->alder());
							if($this->show('p_mobil'))
								woCell($tab, 800, $p->g('p_phone'));
							if($this->show('p_rolle'))
								woCell($tab, 800, $p->g('instrument'));
							if($this->show('p_epost'))
								woCell($tab, 800, $p->g('p_email'));
	
							woCell($tab, 800, $inn->g('b_name'));
	
							if($this->show('i_type'))
								woCell($tab, 800, $inn->g('bt_name'));
							if($this->show('i_fylke'))
								woCell($tab, 800, $inn->g('fylke').($this->show('i_kommune')?' - '.$inn->g('kommune'):''));
							if($this->show('i_kommune')&&!$this->show('i_fylke'))
								woCell($tab, 800, $inn->g('kommune'));
							if($this->show('p_adresse') && $p->g('p_adress') !== '')
								woCell($tab, 800, $p->g('p_adress').', '. $p->g('p_postnumber').' '.UKMN_poststed($p->g('p_postnumber')));
						}
					}
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
		$grupperte_innslag = $this->_innslag();
		echo $this->html_init('Kontaktlister');
		?>
		<ul class="kontaktlister">
		<?php
		if(!is_array($grupperte_innslag)){
			echo '<strong>Ingen innslag</strong>';
		} else {
			$width = 500;	// NAVN + INNSLAG + 50px
			if($this->show('p_alder'))
				$width += 40;
			if($this->show('p_mobil'))
				$width += 70;
			if($this->show('p_rolle'))
				$width += 180;
			if($this->show('p_epost'))
				$width += 250;
			if($this->show('p_adresse'))
				$width += 250;
			if($this->show('i_type'))
				$width += 100;
			if($this->show('i_fylke') || $this->show('i_kommune'))
				$width += 160;


			foreach($grupperte_innslag as $grp => $innslag){
				if($grp !== 0)
					echo '<li class="grp">'.$grp.'</li>';
				foreach($innslag as $inn) {
					$kontakt = $inn->kontaktperson();
					## LAST INN GEOGRAFI KUN HVIS NØDVENDIG
					if($this->show('i_fylke')||$this->show('i_kommune'))
						$inn->loadGeo();
						
					### KONTAKTPERSON
					if($this->show('h_kontaktp')) {
						$p = $inn->kontaktperson();
						$epost = $p->g('p_email');
						if(!empty($epost))
							$epost = '<a href="mailto:'.$epost.'" class="UKMMAIL">'.$epost.'</a>';
							
						echo '<li class="kontaktpers" style="width:'.$width.'px;">'
							.'	<div class="navn">'. $p->g('name').'</div>'
							.($this->show('p_alder')
								? '<div class="alder">'.$p->alder().' år</div>'
								: '')
							.($this->show('p_mobil')
								? '<div class="mobil UKMSMS">'. $p->g('p_phone') .'&nbsp;</div>'
								: '')
							.($this->show('p_rolle')
								? '<div class="rolle">Kontaktperson</div>'
								: '')
							.($this->show('p_epost')
								? '<div class="epost">'.$epost.'&nbsp;</div>'
								: '')
							
							.'<div class="innslag">'.$inn->g('b_name').'&nbsp;</div>'
							.($this->show('i_type')
								? '<div class="type">'.$inn->g('bt_name').'</div>'
								: '')
							.($this->show('i_fylke')
								? '<div class="geo">'.$inn->g('fylke').($this->show('i_kommune')?' - '.$inn->g('kommune'):'').'</div>'
								: '')
							.($this->show('i_kommune')&&!$this->show('i_fylke')
								? '<div class="geo">'.$inn->g('kommune').'</div>'
								: '')
							.($this->show('p_adresse') && $p->g('p_adress') !== ''
								? '<div class="adresse">'.$p->g('p_adress').', '. $p->g('p_postnumber').' '.UKMN_poststed($p->g('p_postnumber')).'</div>'
								: '')
							.'</li>'
						;
					}
					## DELTAKERE
					if($this->show('h_vis')) {
						$personer = $inn->personer();
						foreach($personer as $pers) {
							$p = new person($pers['p_id'], $inn->g('b_id'));
							$epost = $p->g('p_email');
							if(!empty($epost))
								$epost = '<a href="mailto:'.$epost.'" class="UKMMAIL">'.$epost.'</a>';
								
							echo '<li class="pers" style="width:'.$width.'px;">'
								.'	<div class="navn">'. $p->g('name').'&nbsp;</div>'
								.($this->show('p_alder')
									? '<div class="alder">'.$p->alder().' år</div>'
									: '')
								.($this->show('p_mobil')
									? '<div class="mobil UKMSMS">'. $p->g('p_phone') .'&nbsp;</div>'
									: '')
								.($this->show('p_rolle')
									? '<div class="rolle">'.$p->g('instrument').'&nbsp;</div>'
									: '')

								.($this->show('p_epost')
									? '<div class="epost">'.$epost.'&nbsp;</div>'
									: '')
								
								.'<div class="innslag">'.$inn->g('b_name').'</div>'
								.($this->show('i_type')
									? '<div class="type">'.$inn->g('bt_name').'</div>'
									: '')
								.($this->show('i_fylke')
									? '<div class="geo">'.$inn->g('fylke').($this->show('i_kommune')?' - '.$inn->g('kommune'):'').'</div>'
									: '')
								.($this->show('i_kommune')&&!$this->show('i_fylke')
									? '<div class="geo">'.$inn->g('kommune').'</div>'
									: '')
								.($this->show('p_adresse') && $p->g('p_adress') !== ''
									? '<div class="adresse">'.$p->g('p_adress').', '. $p->g('p_postnumber').' '.UKMN_poststed($p->g('p_postnumber')).'</div>'
									: '')
								.'<div class="clear clearfix clear-fix"></div>'
								.'</li>'
							;
						}
					}
				}
			}
		} ?>
		</ul>
		<?php
	}
	
	/********************************************************************************/
	/*								PRIVATE Nøkkeltall								*/
	/********************************************************************************/
	/**
	 * _nokkeltall function
	 * 
	 * Genererer et array med innslags-statistikk
	 *
	 * @access private
	 * @return array
	 */	
	private function _nokkeltall($grupperte_innslag){
		// Loop alle innslag
		$unike = array();
		$nt = array('typer'=>array('Scene'=>array('inn'=>0,'pers'=>0),
								   'Film'=>array('inn'=>0,'pers'=>0),
								   'Utstilling'=>array('inn'=>0,'pers'=>0),
								   'Andre innslag'=>array('inn'=>0,'pers'=>0)));
		foreach($this->innslag as $inn){
			$btid = $inn->g('bt_id');
			// Antall innslag
			switch($btid){
				case 1:
				case 2:
				case 3:
						$key = $inn->g('bt_name');		break;
						break;
				default:
						$key = 'Andre innslag';			break;
			}
			$nt['typer'][$key]['inn']++;
			
			// Antall personer
			$personer = $inn->personer();
			
			foreach($personer as $p_arr)
				$unike[$p_arr['p_id']] = true;

			$nt['typer'][$key]['pers'] += sizeof($personer);
			$nt['personer'] += sizeof($personer);
			$nt['innslag']++;
			
			if($btid==1) {
				$nt['scenekat'][ucfirst($inn->g('b_kategori'))]['inn']++;
				$nt['scenekat'][ucfirst($inn->g('b_kategori'))]['pers'] += sizeof($personer);
			}
			if($key == 'Andre innslag') {
				$nt['annetkat'][ucfirst($inn->g('bt_name'))]['inn']++;
				$nt['annetkat'][ucfirst($inn->g('bt_name'))]['pers'] += sizeof($personer);
			}
		}
		$nt['unike'] = sizeof($unike);


		return $nt;
	}

	/********************************************************************************/
	/*								PRIVATE Innslag									*/
	/********************************************************************************/
	/**
	 * _innslag function
	 * 
	 * Genererer et array med innslag som loopes i rapporten basert på rapportinnstillinger
	 * for sortering.
	 *
	 * @access private
	 * @return array
	 */	
	private function _innslag(){
		if($this->showFormat('g_type'))
			return $this->_innslag_gruppert_type();
		if($this->showFormat('g_kommune'))
			return $this->_innslag_gruppert_geo('kommune');
		if($this->showFormat('g_fylke'))
			return $this->_innslag_gruppert_geo('fylke');
		if($this->showFormat('g_fylke_type'))
			return $this->_innslag_gruppert_geo('fylke_type');
		return $this->_innslag_sortert_navn();
	}
	
	/**
	 * _innslag_sortert_navn function
	 * 
	 * Lager en liste over alle innslag på mønstringen, sortert etter navn
	 *
	 * @access private
	 * @return array
	 */	
	private function _innslag_sortert_navn(){
		$m = new monstring($this->pl_id);
		
		$innslag = $m->innslag();
		foreach($innslag as $inn_array){
			$inn = new innslag($inn_array['b_id']);
			if(get_option('site_type')!='kommune')
				$inn->videresendte(get_option('pl_id'));
			$this->innslag[$inn->g('b_id')] = $inn;
			$innslagene[$inn_array['b_name']] = $inn;
		}
		$this->_deep_ksort($innslagene);
		
		return array(0=>$innslagene);
	}

	/**
	 * _innslag_gruppert_type function
	 * 
	 * Lager en liste over alle innslag på mønstringen, gruppert etter bt_name, sortert alfabetisk
	 *
	 * @access private
	 * @return array
	 */	
	private function _innslag_gruppert_type(){
		$m = new monstring($this->pl_id);
		
		$innslag = $m->innslag();
		foreach($innslag as $inn_array) {
			$inn = new innslag($inn_array['b_id']);
			if(get_option('site_type')!='kommune')
				$inn->videresendte(get_option('pl_id'));
			$this->innslag[$inn->g('b_id')] = $inn;
			$innslagene[$inn->g('bt_name')][$inn->g('b_name')] = $inn;
		}
		$this->_deep_ksort($innslagene);
		return $innslagene;
	}

	/**
	 * _innslag_gruppert_geo function
	 * 
	 * Lager en liste over alle innslag på mønstringen, gruppert etter geografi (kommune/fylke), sortert alfabetisk
	 *
	 * @access private
	 * @param field kommune/fylke
	 * @return array
	 */	
	private function _innslag_gruppert_geo($field='kommune'){
		$m = new monstring($this->pl_id);
		
		$innslag = $m->innslag();
		foreach($innslag as $inn_array) {
			$inn = new innslag($inn_array['b_id']);
			if(get_option('site_type')!='kommune')
				$inn->videresendte(get_option('pl_id'));
			$inn->loadGEO();
			$this->innslag[$inn->g('b_id')] = $inn;

			if( $field == 'fylke_type') {
				$innslagene[ $inn->g('fylke').': '. $inn->g('kategori') ][ $inn->g('b_name') ] = $inn;
			} else {
				$innslagene[$inn->g($field)][$inn->g('b_name')] = $inn;
			}
		}
		$this->_deep_ksort($innslagene);
		return $innslagene;
	}
}
?>