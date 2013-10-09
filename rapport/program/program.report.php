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
		
		if($this->report_extended == 'tekniske_prover')
			$this->navn = 'Tekniske prøver';
		else
			$this->navn = 'Program'; 
		$m = new monstring($this->pl_id);
		$ramme = $m->forestillinger('c_start',false);
		$r = $this->optGrp('h','Forestillinger');
		$this->det_finnes_ingen_monstringer = sizeof($ramme)==0;
		foreach($ramme as $con)
			$this->opt($r, 'c_'.$con['c_id'], $con['c_name']);
			
		$i = $this->optGrp('i','Info om innslaget');
		$this->opt($i, 'i_kategori', 'Kategori');
		$this->opt($i, 'i_sjanger', 'Sjanger');
		$this->opt($i, 'i_fylke', 'Fylke');
		$this->opt($i, 'i_kommune', 'Kommune');
		$this->opt($i, 'i_oppmote', 'Oppmøtetidspunkt');

		$t = $this->optGrp('t', 'Titler');
		$this->opt($t, 't_vis', 'Vis titler');
		$this->opt($t, 't_detaljer', 'Vis detaljert tittelinfo');
		
		$d = $this->optGrp('d', 'Deltakere');
		$this->opt($d, 'd_kontakt', 'Vis kontaktpersoner');
		$this->opt($d, 'd_vis', 'Vis deltakere');
		$this->opt($d, 'd_alder', 'Vis alder');
		$this->opt($d, 'd_mobil', 'Vis mobilnummer');
		$this->opt($d, 'd_funksjon', 'Vis rolle/funksjon/instrument');
		
		if($this->report_extended) {
			$this->opt($i, 'i_tekniske', 'Tekniske behov');
			$this->opt($i, 'i_konferansier', 'Konferansiertekster (beskrivelse)');
			$this->opt($i, 'i_varighet', 'Varighet');
		}
		if($this->report_extended == 'tekniske_prover') {
			$k = $this->formatGrp('l', 'Kommentarbokser', 'radio');
			$this->format($k, 'k_ingen', 'Ingen kommentarbokser');
			$this->format($k, 'k_liten', 'Små kommentarbokser');
			$this->format($k, 'k_stor', 'Store kommentarbokser');
			#$this->format($k, 'k_side', 'Vis ett innslag per ark');
			
			$b = $this->formatGrp('b', 'Hvilke bokser vil du ha?');
			$this->format($b, 'b_generell', 'Generell kommentar');
			$this->format($b, 'b_lyd', 'Lyd');
			$this->format($b, 'b_lys', 'Lys');
		}
		$p = $this->optGrp('p', 'Info om forestillingen');
		$this->opt($p, 'p_varig', 'Vis beregnet varighet');

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
		//$objPHPExcel = new PHPExcel();
		$this->excel_init('landscape');
		exSheetName('PROGRAM','6dc6c1');
		$concerts = $this->_program();
		$row = 1;
		if(!is_array($concerts) || sizeof($concerts)==0){
			echo exCell('A1:D1', 'Ingen forestillinger valgt');
		} else {
		
			exCell('A1', 'Hendelse', 'bold');
			exCell('B1', 'Starter', 'bold');
			exCell('C1', 'Sted', 'bold');

			exCell('D1', 'Rekkefølge', 'bold');
			exCell('E1', 'Innslag', 'bold');
			
			$col = 5;
			if($this->show('i_kategori')){
				$col++;
				exCell(i2a($col).'1', 'Kategori', 'bold');
			}
			if($this->show('i_sjanger')){
				$col++;
				exCell(i2a($col).'1', 'Sjanger', 'bold');
			}
			
			if($this->show('i_fylke')){
				$col++;
				exCell(i2a($col).'1', 'Fylke', 'bold');
			}
			if($this->show('i_kommune')){
				$col++;
				exCell(i2a($col).'1', 'Kommune', 'bold');
			}
			if($this->show('i_oppmote')) {
				$col++;
				exCell(i2a($col).'1', 'Oppmøte', 'bold');
			}
			if($this->show('i_varighet')) {
				$col++;
				exCell(i2a($col).'1', 'Varighet (sek)', 'bold');
			}
			if($this->show('d_kontakt')){
				$col++;
				exCell(i2a($col).'1', 'Kontaktperson', 'bold');
			}
			if($this->show('t_vis')){
				$col++;
				exCell(i2a($col).'1', 'Titler', 'bold');
			}
			if($this->show('d_vis')){
				$col++;
				exCell(i2a($col).'1', 'Deltakere', 'bold');
			}
			if($this->show('i_konferansier')){
				$col++;
				exCell(i2a($col).'1', 'Konferansiertekster', 'bold');
			}

			if($this->show('i_tekniske')){
				$col++;
				exCell(i2a($col).'1', 'Tekniske behov','bold');
			}

		
			foreach($concerts as $c){
				$program = $c->innslag();
				$order = 0;
				$oppmote = (int) $c->g('c_start') - ((int) $c->g('c_before')*60);
				$oppmote = $oppmote - ((int) $c->g('c_delay')*60);
				foreach($program as $inn) {
					$row++;
					$order++;
					$oppmote += ((int) $c->g('c_delay')*60);

					$i = new innslag($inn['b_id']);
					if(get_option('site_type')!='kommune')
						$i->videresendte(get_option('pl_id'));
					$i->loadGEO();
					
					exCell('A'.$row, $c->g('c_name'));
					exCell('B'.$row, $c->starter());
					exCell('C'.$row, $c->g('c_place'));

					exCell('D'.$row, $order);
					exCell('E'.$row, $i->g('b_name'));
					
					$col = 5;
					$i_kategori = $i->g('kategori');
					if(empty($i_kategori))
						$i_kategori = $i->g('bt_name');
						
					if($this->show('i_kategori')){
						$col++;
						exCell(i2a($col).$row, $i_kategori);
					}
					if($this->show('i_sjanger')){
						$col++;
						exCell(i2a($col).$row, $i->g('b_sjanger'));
					}
					
					if($this->show('i_fylke')){
						$col++;
						exCell(i2a($col).$row, $i->g('fylke'));
					}
					if($this->show('i_kommune')){
						$col++;
						exCell(i2a($col).$row, $i->g('kommune'));
					}
					if($this->show('i_oppmote')) {
						$col++;
						exCell(i2a($col).$row, date('H:i', $oppmote));
					}
					if($this->show('i_varighet')) {
						$col++;
						exCell(i2a($col).$row, $i->varighet(get_option('pl_id')));
					}
					if($this->show('d_kontakt')){
						$col++;
						$d = new person($i->g('b_contact'));
						exCell(i2a($col).$row, $d->g('name') 
											.' ('. $d->g('p_phone')
											.' - '. $d->g('p_email')
											.')'
										);
					}
					
					
					$deltakertext = $titteltext = '';

					if($this->show('t_vis') && !$i->tittellos()){
						$titler = $i->titler($this->pl_id);
						$counter = 0;
						foreach($titler as $t) {
							$counter++;
							$titteltext .= $t->g('tittel')
								.($this->show('t_detaljer')
									? ' ('. $t->g('detaljer').')'
									: ''
									)
								.($counter < sizeof($titler) ? ', ' : '')
								;
						}
						$col++;
						exCell(i2a($col).$row, $titteltext);
					}

					if($this->show('d_vis') && !$i->tittellos()){
						$counter = 0;
						if(get_option('site_type')!=='kommune')
							$i->videresendte($this->pl_id);

						$deltakere = $i->personer();
						foreach($deltakere as $delt) {
							$counter++;
							$d = new person($delt['p_id'], $i->g('b_id'));
							$deltakertext .= $d->g('name')
								.($this->show('d_alder')
									? ' - '. $d->alder().' år'
									: ''
									)
								.($this->show('d_funksjon')
									? ' ('. $d->g('instrument').')'
									: ''
									)
								.($this->show('d_mobil')
									? ' - mobil: '.$d->g('p_phone')
									: '')
								.($counter < sizeof($deltakere) ? ', ' : '')
								;
						}
						$col++;
						exCell(i2a($col).$row, $deltakertext);
					}
					
					if($this->show('i_konferansier')){
						$col++;
						exCell(i2a($col).$row, $i->g('td_konferansier'));
					}

					if($this->show('i_tekniske')){
						$col++;
						exCell(i2a($col).$row, $i->g('td_demand'));
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
		$section = $this->word_init();
		$concerts = $this->_program();

		if(!is_array($concerts) || sizeof($concerts)==0){
			echo woText($section, 'Ingen forestillinger valgt', 'grp');
		} else {
			foreach($concerts as $con){
				$sted = $con->g('c_place');
				if(!empty($sted))
					$sted = ' - '. $sted;
				woText($section, $con->g('c_name'), 'grp');
				woText($section, $con->starter(). $sted, 'h2');
				if($this->show('p_varig'))
				woText($section, 'Beregnet varighet: '. $con->tid());
				
				$program = $con->innslag();
				$order = 0;
				$oppmote = (int) $con->g('c_start') - ((int) $con->g('c_before')*60);
				$oppmote = $oppmote - ((int) $con->g('c_delay')*60);

				foreach($program as $inn) {
					$order++;
					$oppmote += ((int) $con->g('c_delay')*60);

					$i = new innslag($inn['b_id']);
					if(get_option('site_type')!='kommune')
						$i->videresendte(get_option('pl_id'));
					$i->loadGEO();
					
					woText($section, $order .'. '. $i->g('b_name'), 'h3');
					$katogsjan = $i->g('kategori_og_sjanger');
					if(empty($katogsjan))
						$katogsjan = $i->g('bt_name');
						
					if($this->show('i_kategori')&&$this->show('i_sjanger'))
						woText($section, '('. $katogsjan.')');
					elseif($this->show('i_kategori'))
						woText($section, '('. $i->g('kategori').')');
					elseif($this->show('i_sjanger'))
						woText($section, '('. $i->g('b_sjanger').')');
					
					
					if($this->show('i_fylke')&&$this->show('i_kommune'))
						woText($section, $i->g('fylke').' - '. $i->g('kommune'));
					elseif($this->show('i_fylke'))
						woText($section, $i->g('fylke'));
					elseif($this->show('i_kommune'))
						woText($section, $i->g('kommune'));
					
					if($this->show('i_oppmote')) {
						woText($section, 'Oppmøte: ', 'bold');
						woText($section, 'kl. '. date('H:i', $oppmote));
					}
					if($this->show('i_varighet')) {
						woText($section, 'Varighet: ', 'bold');
						woText($section, $i->tid(get_option('pl_id')));
					}
					if($this->show('d_kontakt')){
						$d = new person($i->g('b_contact'));
						woText($section, 'Kontaktperson:', 'bold');
						woText($section, $d->g('name') 
										.' - mobil: '. $d->g('p_phone')
										.' - e-post: '. $d->g('p_email')
										);
					}
					$deltakertext = $titteltext = '';

					if($this->show('t_vis') && !$i->tittellos()){
						woText($section, 'Titler: ', 'bold');
						
						$titler = $i->titler($this->pl_id);
						$counter = 0;
						foreach($titler as $t) {
							$counter++;
							$titteltext .= $t->g('tittel')
								.($this->show('t_detaljer')
									? 	' ('
									  . $t->g('detaljer')
									  . ($i->g('bt_id') == 3 ? ' - '.$t->g('beskrivelse') : '')
									  . ')'
									: ''
									)
								.($counter < sizeof($titler) ? ', ' : '')
								;
						}
						woText($section, $titteltext);
					}

					if($this->show('d_vis') && !$i->tittellos()){
						woText($section, 'Personer: ', 'bold');
						
						if(get_option('site_type')!=='kommune')
							$i->videresendte($this->pl_id);

						$deltakere = $i->personer();
						$counter = 0;
						foreach($deltakere as $delt) {
							$counter++;
							$d = new person($delt['p_id'], $i->g('b_id'));
							$deltakertext .= $d->g('name')
								.($this->show('d_alder')
									? ' - '. $d->alder().' år'
									: ''
									)
								.($this->show('d_funksjon')
									? ' ('. $d->g('instrument').')'
									: ''
									)
								.($this->show('d_mobil')
									? ' - mobil: '.$d->g('p_phone')
									: '')
								.($counter < sizeof($deltakere) ? ', ' : '')
								;
						}
						woText($section, $deltakertext);
					}
					
					
					
					///////
					
					
					if($this->show('i_konferansier')){
						woText($section, 'Konferansiertekster: ', 'bold');
						woText($section, $i->g('td_konferansier'));
					}

					if($this->show('i_tekniske')){
						woText($section, 'Tekniske behov: ', 'bold');
						woText($section, $i->g('td_demand'));
					}

					if($this->showFormat('b_generell')||$this->showFormat('b_lyd')||$this->showFormat('b_lys')){
						$tab = $section->addTable(array('align'=>'center'));
						
						if($this->showFormat('k_liten'))
							$tab->addRow(1500);
						elseif($this->showFormat('k_stor'))
							$tab->addRow(3000);
						else
							$tab->addRow();
						
						$bokser = 0;
						if($this->showFormat('b_generell'))
							$bokser++;
						if($this->showFormat('b_lyd'))
							$bokser++;
						if($this->showFormat('b_lys'))
							$bokser++;
						
						$box = array('borderTopSize'=>9, 'borderTopColor'=>'000000',
									 'borderRightSize'=>9, 'borderRightColor'=>'000000',
									 'borderBottomSize'=>9, 'borderBottomColor'=>'000000',
									 'borderLeftSize'=>9, 'borderLeftColor'=>'000000',
									 );

							
						if($this->showFormat('b_generell')) {
							$c = $tab->addCell((11000/$bokser), $box);
							woText($c, 'Generell kommentar','h1');
						}
						if($this->showFormat('b_lyd')) {
							$c = $tab->addCell((11000/$bokser), $box);
							woText($c, 'LYD-kommentarer','h1');
						}
						if($this->showFormat('b_lys')){
							$c = $tab->addCell((11000/$bokser), $box);
							woText($c, 'LYS-kommentarer','h1');
						}
					}
					
					if($this->report_extended == 'juryskjema_utskrift') {
						$jurybox = array('borderTopSize'=>9, 'borderTopColor'=>'000000',
									 'borderRightSize'=>9, 'borderRightColor'=>'000000',
									 'borderBottomSize'=>9, 'borderBottomColor'=>'000000',
									 'borderLeftSize'=>9, 'borderLeftColor'=>'000000',
									 );
						woText($section, 'Juryskjema: ', 'h2');

						$tab = $section->addTable(array('align'=>'center'));
						
						$tab->addRow(2000);
						$c = $tab->addCell(5400, $jurybox);
							woText($c, 'Originalitet','h2');
						$c = $tab->addCell(5400, $jurybox);
							woText($c, 'Kreativitet','h2');

						$tab->addRow(2000);
						$c = $tab->addCell(5400, $jurybox);
							woText($c, 'Publikumskontakt / formidling','h2');
						$c = $tab->addCell(5400, $jurybox);
							woText($c, 'Tekniske ferdigheter','h2');

						$tab->addRow(2000);
						$c = $tab->addCell(5400, $jurybox);
							woText($c, 'Scenefremtreden / fremføring','h2');
						$c = $tab->addCell(5400, $jurybox);
							woText($c, 'Kvalitet i forhold til forutsetninger','h2');

						woText($section, 'Basert på ovenstående vurderinger gjør jeg følgende midlertidig vurdering','h3');
						$tab = $section->addTable(array('align'=>'center'));
						$tab->addRow(2000);
						$c = $tab->addCell(11000, $jurybox);
							woText($c, 'Vurdering','h2');

						$section->addPageBreak();

					}

					
					///////
					
					
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
			echo '<h3>Ingen forestillinger valgt</h3>';
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
				<?php if($this->show('p_varig')) { ?>
				<div class="program-varighet">Beregnet varighet: <?= $c->tid() ?></div>
				<?php } ?>
				
				
				<ul class="program">
				<?php
				$oppmote = (int) $c->g('c_start') - ((int) $c->g('c_before')*60);
				$oppmote = $oppmote - ((int) $c->g('c_delay')*60);
				$program = $c->innslag();
				$order = 0;
				foreach($program as $inn) {
					$order++;
					$i = new innslag($inn['b_id']);
					if(get_option('site_type')!='kommune')
						$i->videresendte(get_option('pl_id'));
					$i->loadGEO();
					$oppmote += ((int) $c->g('c_delay')*60);
					$katogsjan = $i->g('kategori_og_sjanger');
					if(empty($katogsjan))
						$katogsjan = $i->g('bt_name');
					echo '<li>'
						.	'<div class="order">'. $order .'.</div>'
						.	'<div class="name">'. $i->g('b_name') .'</div>'
						.	($this->show('i_kategori')&&$this->show('i_sjanger')
								? '<div class="katogsjan">('. $katogsjan .')</div>'
								: ($this->show('i_kategori')
									? '<div class="katogsjan">('. $i->g('kategori').')</div>'
									: ($this->show('i_sjanger')
										? '<div class="katogsjan">('. $i->g('b_sjanger').')</div>'
										: ''
										)
									)
								)
						.	($this->show('i_fylke')&&$this->show('i_kommune')
								? '<div class="geografi">'. $i->g('fylke').' - '. $i->g('kommune') .'</div>'
								: ($this->show('i_fylke')
									? '<div class="geografi">'. $i->g('fylke').'</div>'
									: ($this->show('i_kommune')
										? '<div class="geografi">'. $i->g('kommune').'</div>'
										: ''
										)
									)
								)
						.	($this->show('i_oppmote') ? '<div class="titler"><div class="titler-label">Oppmøte: </div>kl. '. date('H:i', $oppmote) .'</div>' : '')
						.	($this->show('i_varighet') ? '<div class="titler"><div class="titler-label">Varighet: </div>'.$i->tid(get_option('pl_id')) .'</div>' : '')

						;
					if($this->show('d_kontakt')){
						$d = new person($i->g('b_contact'));
						echo '<div class="kontaktperson">'
							.'<div class="label">Kontaktperson:</div>'
							.''.$d->g('name').'</div>'
							.'<div class="detaljer"> - mobil: <span class="UKMSMS">'. $d->g('p_phone') .'</span></div>'
							.'<div class="detaljer"> - e-post: <a href="mailto:'.$d->g('p_email').'">'. $d->g('p_email').'</a></div>'
							.'</div>'
							;
					}
					if($this->report_extended == 'juryskjema') {
						$alle_vurderinger = jury_vurderinger_forestilling($c->g('c_id'));
						if(!is_array($alle_vurderinger[$i->g('b_id')])) {?>
							<div class="juryering"><h3>Innslaget er ikke vurdert</h3></div>
						<?php
						} else { ?>
						<div class="juryering">
							<table class="juryering_tall" cellpadding="0" cellspacing="0">
								<tr class="header">
									<th>Helhetsvurdering</th>
						<?php
						$header = true;
						#$sumsum = 0;
							foreach($alle_vurderinger[$i->g('b_id')] as $kriterie => $vurderinger) {
								if($kriterie !== 'helhet')
									continue;
									
								if($header) {
									foreach($alle_vurderinger[$i->g('b_id')][$kriterie] as $medlem => $data) { ?>
										<th class="medlem" alt="<?= jury_medlem($medlem)?>" alt="<?= jury_medlem($medlem)?>"><?= shortString(jury_medlem($medlem),10) ?></th>
									<?php
									}
									$header = false;
									?>
									<th class="sum">SUM</th>
									<th class="snitt">SNITT</th>
									</tr>
									<?php
								} ?>
									<tr>
										<th class="kriterie">Poeng</th>
								<?php
								$sum = 0;
								$snitt = 0;
								$ant_vurderinger = 0;
								foreach($vurderinger as $medlem => $vurdering) { ?>
										<td class="poeng"><?= $vurdering['poeng'] ?></td>
								<?php
									$sum += $vurdering['poeng'];
									$ant_vurderinger++;
									#$sumsum += $vurdering['poeng'];
								} ?>
									<td class="sum"><?= $sum ?></td>
									<td class="snitt"><?= round($sum / $ant_vurderinger, 2) ?></td>
								</tr>
							<?php
							}
							/*	<tr class="sum">
									<th class="kriterie">SUM</th>
									<th colspan="<?= $ant_vurderinger ?>"></th>
									<th class="sum"><?= $sumsum ?></th>
									<th class="snitt"><?= round($sumsum / $ant_vurderinger, 2) ?></th>
								</tr>
							*/ ?>
							</table>
	
								<table class="juryering_kommentarer" cellpadding="0" cellspacing="0">
									<tr class="header">
										<td class="spacer"></td>
										<th class="medlem">Jurymedlem</th>
										<th class="kommentar">Kommentar</th>
									</tr>
							<?php
							$header = true;
							foreach($alle_vurderinger[$i->g('b_id')] as $kriterie => $vurderinger) { ?>
									<tr class="kriterie">
										<th colspan="3"><?= $kriterie ?></th>
									</tr>
							<?php	
								foreach($vurderinger as $medlem => $vurdering) { ?>
									<tr>
										<td></td>
										<th><?= jury_medlem($medlem) ?></th>
										<td><?= utf8_encode($vurdering['kommentar']) ?></td>
									</tr>
	
								<?php
								} ?>
							<?php
							} ?>
							</table>
							</div>
							<div style="page-break-after: always;"></div>
							<?php
							#echo '<pre>'; var_dump($data); echo '</pre>';
						}
					}
					if($this->show('t_vis') && !$i->tittellos()){
						echo '<div class="titler">'
							.'<div class="titler-label">Titler:</div>';
						$titler = $i->titler($this->pl_id);
						$counter = 0;
						foreach($titler as $t) {
							$counter++;
							echo '<div class="name">'.$t->g('tittel').'</div>'
								.($this->show('t_detaljer')
									? '<div class="detaljer">('. $t->g('detaljer')									  . ($i->g('bt_id') == 3 ? ' - '.$t->g('beskrivelse') : '')
.')</div>'
									: ''
									)
								.($counter < sizeof($titler) ? '<div class="separator">,</div>' : '')
								;
						}
						echo '</div>';
					}

					if($this->show('d_vis') && !$i->tittellos()){
						echo '<div class="deltakere">'
							.'<div class="deltakere-label">Personer:</div>';
						if(get_option('site_type')!=='kommune')
							$i->videresendte($this->pl_id);
						$deltakere = $i->personer();
						$counter = 0;
						foreach($deltakere as $delt) {
							$counter++;
							$d = new person($delt['p_id'], $i->g('b_id'));
							echo '<div class="name">'.$d->g('name').'</div>'
								.($this->show('d_alder')
									? '<div class="alder"> - '. $d->alder().' år</div>'
									: ''
									)
								.($this->show('d_funksjon')
									? '<div class="detaljer">('. $d->g('instrument').')</div>'
									: ''
									)
								.($this->show('d_mobil')
									? '<div class="detaljer">- mobil: <span class="UKMSMS">'. $d->g('p_phone') .'</span></div>'
									: '')
								.($counter < sizeof($deltakere) ? '<div class="separator">,</div>' : '')

								;
						}
						echo '</div>';
					}
						
					if($this->show('i_konferansier')){
						echo '<div class="deltakere">'
							.'<div class="deltakere-label">Konferansiertekster:</div>'
							.$i->g('td_konferansier')
							.'</div>';
					}

					if($this->show('i_tekniske')){
						echo '<div class="deltakere">'
							.'<div class="deltakere-label">Tekniske behov:</div>'
							.$i->g('td_demand')
							.'</div>';
					}
					
					if($this->report_extended == 'juryskjema_utskrift') {
						echo '<div class="juryskjema">'
								.'<div class="deltakere-label">Juryskjema</div>'

								.'<div class="left">Originalitet</div>'
								.'<div class="right">Kreativitet</div>'
								.'<div class="clear-fix"></div>'
	
								.'<div class="left">Publikumskontakt / formidling</div>'
								.'<div class="right">Tekniske ferdigheter</div>'
								.'<div class="clear-fix"></div>'
								
								.'<div class="left">Scenefremtreden / fremføring</div>'
								.'<div class="right">Kvalitet i forhold til forutsetninger</div>'
								.'<div class="clear-fix"></div>'
								
								.'<h3>Basert på ovenstående vurderinger gjør jeg følgende midlertidig vurdering</h3>'
								.'<div class="wide">Vurdering</div>'
								.'<div class="clear-fix"></div>'
	
							.'</div>'
							.'<div style="page-break-after: always;"></div>';
						
					}


					if($this->showFormat('b_generell')||$this->showFormat('b_lyd')||$this->showFormat('b_lys')){
						if($this->showFormat('k_liten'))
							$class = 'liten';
						elseif($this->showFormat('k_stor'))
							$class = 'stor';
						else
							$class = 'ingen';
						
						$bokser = 0;
						if($this->showFormat('b_generell'))
							$bokser++;
						if($this->showFormat('b_lyd'))
							$bokser++;
						if($this->showFormat('b_lys'))
							$bokser++;
							
						switch($bokser) {
							case 3:
								$class .= ' tredjedel';
								break;
							case 2:
								$class .= ' halv';
								break;
							default:
								$class .= ' hel';
								break;
						}
							
						echo '<div class="bokser">';
						
						if($this->showFormat('b_generell'))
							echo '<div class="'.$class.'">Generell kommentar</div>';
						if($this->showFormat('b_lyd'))
							echo '<div class="'.$class.'">LYD-kommentarer</div>';
						if($this->showFormat('b_lys'))
							echo '<div class="'.$class.'">LYS-kommentarer</div>';
						
						echo '</div>';
					}

					echo '<div class="clear"></div>';

					echo '</li>';
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
	 	require_once('UKM/forestilling.class.php');
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