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
		
		UKM_loader('api/forestilling.class');
		$this->navn = 'Kunstkatalog';

		$m = new monstring($this->pl_id);
		$ramme = $m->forestillinger('c_start',false);
		$u = $this->optGrp('u','Kunsthendelser');
		foreach($ramme as $con) {
			$f = new forestilling($con['c_id']);
			#if($f->er_utstilling())
				$this->opt($u, 'c_'.$con['c_id'], $con['c_name']);
#			else
#				$this->opt($a, 'c_'.$con['c_id'], $con['c_name']); 
		}

		$i = $this->optGrp('i','Utfyllende informasjon');
		$this->opt($i, 'i_type', 'Vis type');
		$this->opt($i, 'i_fylke', 'Vis fylke');
		$this->opt($i, 'i_kommune', 'Vis kommune');
		$this->opt($i, 'i_beskrivelse', 'Vis beskrivelser');

		$d = $this->optGrp('d', 'Deltakere');
		$this->opt($d, 'd_alder', 'Vis alder');
		$this->opt($d, 'd_funksjon', 'Vis rolle/funksjon');
		
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
		UKM_loader('excel');
		global $objPHPExcel;
		$objPHPExcel = new PHPExcel();
		$this->excel_init('landscape');

		exSheetName('KUNSTKAT','6dc6c1');

		$concerts = $this->_program();
		$row = 1;
		if(!is_array($concerts) || sizeof($concerts)==0){
			exCell('A1:D1', 'Ingen forestillinger valgt');
		} else {
			exCell('A1', 'Hendelse', 'bold');
			exCell('B1', 'Sted', 'bold');
			exCell('C1', 'Starter', 'bold');
			exCell('D1', 'Rekkefølge', 'bold');
			exCell('E1', 'Tittel', 'bold');
			$col=5;
			if($this->show('i_fylke')){
				$col++;
				exCell(i2a($col).'1', 'Fylke', 'bold');
			}
			if($this->show('i_kommune')){
				$col++;
				exCell(i2a($col).'1', 'Kommune', 'bold');
			}
			$col++;
			exCell(i2a($col).'1', 'Personer', 'bold');
			if($this->show('i_type')){
				$col++;
				exCell(i2a($col).'1', 'Type', 'bold');
			}
			
			foreach($concerts as $c){
				$program = $c->innslag();
				$counter = 0;
				foreach($program as $inn) {
					$order++;
					if($inn['bt_id']!=3)
						continue;
					
					$i = new innslag($inn['b_id']);
					$i->loadGEO();

					$titler = $i->titler($this->pl_id);
					$deltakere = $i->personer();

					foreach($titler as $t) {
						$counter++;
						$row++;

						exCell('A'.$row, $c->g('c_name'));
						exCell('B'.$row, $c->g('c_place'));
						exCell('C'.$row, $c->starter());
				
						exCell('D'.$row, $counter);
						exCell('E'.$row, $t->g('tittel'));
						
						$col=5;
						if($this->show('i_fylke')) {
							$col++;
							exCell(i2a($col).$row, $i->g('fylke'));
						}
						if($this->show('i_kommune')){
							$col++;
							exCell(i2a($col).$row, $i->g('kommune'));
						}
						
						$pers = 0;
						$perstext = '';
						foreach($deltakere as $delt) {
							$pers++;
							$d = new person($delt['p_id'], $i->g('b_id'));
							$perstext .= $d->g('name')
								.($this->show('d_alder')
									? ' '. $d->alder().' år'
									: ''
									)
								.($this->show('d_funksjon')
									? ' ('. $d->g('instrument').')'
									: ''
									)
								.($pers<sizeof($deltakere)
									? ', '
									: '')
								;
						}
						$col++;
						exCell(i2a($col).$row, $perstext);
						if($this->show('i_type')){
							$col++;
							exCell(i2a($col).$row, ucfirst($t->g('type')));
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
		UKM_loader('word');
		global $PHPWord;
		$section = $this->word_init();

		$concerts = $this->_program();

		if(!is_array($concerts) || sizeof($concerts)==0){
			woText($section, 'Ingen forestillinger valgt');
		} else {
			foreach($concerts as $c){
				$sted = $c->g('c_place');
				if(!empty($sted))
						$sted = ' - '. $sted;
				woText($section, $c->g('c_name'), 'grp');
				woText($section, $c->starter(). $sted, 'h2');
				
				$program = $c->innslag();
				$counter = 0;
				foreach($program as $inn) {
					$order++;
					if($inn['bt_id']!=3)
						continue;
					
					$i = new innslag($inn['b_id']);
					$i->loadGEO();

					$titler = $i->titler($this->pl_id);
					$deltakere = $i->personer();

					foreach($titler as $t) {
						$counter++;
						
						woText($section, $counter.'. '. $t->g('tittel'), 'h3');

						if($this->show('i_fylke')&&$this->show('i_kommune'))
							woText($section, $i->g('fylke').' - '. $i->g('kommune'));
						elseif($this->show('i_fylke'))
							woText($section, $i->g('fylke'));
						elseif($this->show('i_kommune'))
							woText($section, $i->g('kommune'));
						

						$pers = 0;
						$perstext = 'Av: ';
						foreach($deltakere as $delt) {
							$pers++;
							$d = new person($delt['p_id'], $i->g('b_id'));
							$perstext .= $d->g('name')
								.($this->show('d_alder')
									? ' '. $d->alder().' år'
									: ''
									)
								.($this->show('d_funksjon')
									? ' ('. $d->g('instrument').')'
									: ''
									)
								.($pers<sizeof($deltakere)
									? ', '
									: '')
								;
						}
						woText($section, $perstext);

						if($this->show('i_type'))
							woText($section, 'Type: '. ucfirst($t->g('type')));
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
		$concerts = $this->_program();
		echo $this->html_init();

		if(!is_array($concerts) || sizeof($concerts)==0){
			echo '<h3>Ingen hendelser valgt</h3>';
			if($this->det_finnes_ingen_monstringer)
				echo '<div class="error"><strong>For at denne rapporten skal fungere må du sette et program for mønstringen din.</strong> <br />Dette gjør du ved å velge program i menyen til venstre</div>';
		} else {
			foreach($concerts as $c){
				$sted = $c->g('c_place');
				if(!empty($sted))
						$sted = ' - '. $sted;
			 ?>
			 	<div class="clear"></div>
				<h2 id="program-header"><?= $c->g('c_name')?></h2>
				<div class="program-data">
					<div class="tid"><?= $c->starter()?></div>
					<div class="sted"><?= $sted?></div>
				</div>
				
				<ul class="kunstkatalog">
				<?php
				$program = $c->innslag();
				$counter = 0;
				foreach($program as $inn) {
					$order++;
					if($inn['bt_id']!=3)
						continue;
					
					$i = new innslag($inn['b_id']);
					$i->loadGEO();
					if(get_option('site_type')!='kommune')
						$i->videresendte(get_option('pl_id'));
					$titler = $i->titler($this->pl_id);
					$deltakere = $i->personer();

					foreach($titler as $t) {
						$counter++;
						?>
						<li>
							<div class="order"><?= $counter ?>.</div>
							<div class="name"><?= $t->g('tittel') ?></div>

							<?= ($this->show('i_fylke')&&$this->show('i_kommune')
								? '<div class="geografi">'. $i->g('fylke').' - '. $i->g('kommune') .'</div>'
								: ($this->show('i_fylke')
									? '<div class="geografi">'. $i->g('fylke').'</div>'
									: ($this->show('i_kommune')
										? '<div class="geografi">'. $i->g('kommune').'</div>'
										: ''
										)
									)
								) ?>
							<div class="av"><strong>Av: </strong>
							<?php
								$pers = 0;
								foreach($deltakere as $delt) {
									$pers++;
									$d = new person($delt['p_id'], $i->g('b_id'));
									echo $d->g('name')
										.($this->show('d_alder')
											? '<span class="alder">'. $d->alder().' år</span>'
											: ''
											)
										.($this->show('d_funksjon')
											? '<span class="funksjon">('. $d->g('instrument').')</span>'
											: ''
											)
										.($pers<sizeof($deltakere)
											? ', '
											: '')
										;
									
								}
							?>
							</div>
							<?php
							if($this->show('i_type')) { ?>
							<div class="type"><strong>Type: </strong>
								<?= ucfirst($t->g('type')) ?>
							</div>
							<?php
							}
							if($this->show('i_beskrivelse')) { ?>
								<div class="av"><strong>Beskrivelse: </strong><?= $i->g('b_description') ?> <br /> <?= $t->g('beskrivelse') ?></div>
							<?php
							} ?>
						</li>
						<?php
					}
				} ?>
				</ul>
				<div class="clear"></div>
				<?php
			}
		}
	}
		/**
	 * generate function
	 * 
	 * Genererer selve rapporten i HTML-visning
	 *
	 * @access public
	 * @return void
	 */	
	 public function _program(){
	 	UKM_loader('api/forestilling.class');
		$m = new monstring($this->pl_id);
		$ramme = $m->forestillinger('c_start',false);

		foreach($ramme as $con){
			if(!$this->show('c_'.$con['c_id']))
				continue;
			
			$concerts[] = new forestilling($con['c_id']);
		}
		return $concerts;
	 }
}
?>